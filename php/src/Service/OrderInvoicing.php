<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Service;

use DateTimeImmutable;
use Psr\Container\ContainerInterface;
use Tds\Ext\Shop\Domain\OrderRepository;
use Throwable;

/**
 * Invoices a paid order through Lexware Office.
 *
 * ### Lexware is the leading system
 *
 * It assigns the number, renders the PDF and keeps the archive. This module
 * records what it decided (`shop_order.invoice_*`) and never invents an
 * invoice number of its own — § 14 UStG wants one unbroken sequence, and a
 * sequence has one author.
 *
 * ### Why the client is fetched by name and not injected
 *
 * `LexwareClient` belongs to `tds-ext-lexware-pkg`, a **separate package that
 * may not be installed**. A constructor argument would make it a hard
 * dependency and this extension would stop booting without it. So it is
 * resolved from the container by its fully-qualified name, guarded by
 * `class_exists()`.
 *
 * `class_exists()` and NOT `$c->has()`. PHP-DI answers `has()` out of its
 * definition sources and autowiring is one of them, so for any concrete class
 * the answer is `true` whether or not anything was ever bound — the trap that
 * cost this platform six modules' bindings for a release, documented in every
 * `*Module.php` here. `class_exists()` asks the question that actually matters:
 * is the package on disk.
 *
 * ### Nothing here may throw at its caller
 *
 * The caller is the payment webhook. A provider retries anything that is not a
 * 2xx, and an exception escaping into that handler would turn a successful
 * payment into an endless redelivery — or worse, into a refund. Every failure
 * is written to the order and swallowed. The customer's money is already
 * taken; the invoice is a follow-up, and a follow-up must not endanger it.
 *
 * ### It is idempotent, because the webhook is not
 *
 * Providers deliver the same event more than once by design. `invoice()`
 * refuses an order that is not `paid`, one already carrying an invoice, and one
 * that has exhausted its attempts. That last guard is what stops a payload
 * Lexware permanently rejects — a malformed address, a rate it will not take —
 * from being retried for as long as the provider keeps calling.
 */
final class OrderInvoicing
{
    /** The Lexware client's FQCN. A string, because the class may not exist. */
    public const LEXWARE_CLIENT = 'Tds\\Ext\\Lexware\\Service\\LexwareClient';

    /**
     * How often one order may be offered to Lexware before it is left alone.
     *
     * Generous enough to ride out an outage across several webhook
     * redeliveries, low enough that a payload Lexware will never accept stops
     * being sent.
     */
    public const MAX_ATTEMPTS = 5;

    public function __construct(
        private readonly OrderRepository $orders,
        private readonly OrderInvoiceBuilder $builder,
        private readonly ?ContainerInterface $container,
    ) {
    }

    /**
     * Is invoicing available at all — package installed, client configured?
     *
     * Distinct from "did it work". A shop running without the Lexware
     * extension is a valid deployment, not a broken one, and it should say so
     * rather than record a failure on every order it takes.
     */
    public function isAvailable(): bool
    {
        return $this->client() !== null;
    }

    /**
     * Invoice one order. Returns the outcome for a caller that wants to report
     * it; the webhook ignores it.
     *
     * The whole attempt is wrapped, and that is structural rather than
     * decorative: the first thing {@see attempt()} does is read the order, and
     * a database that is gone would otherwise throw straight into the payment
     * webhook — the one place in this module where an exception costs money. A
     * test pins it, because the guarantee is invisible when it holds.
     *
     * @return array{status:string,number:?string,id:?string,error:?string}
     */
    public function invoice(int $orderId): array
    {
        try {
            return $this->attempt($orderId);
        } catch (Throwable $e) {
            return self::result('failed', error: self::readable($e));
        }
    }

    /** @return array{status:string,number:?string,id:?string,error:?string} */
    private function attempt(int $orderId): array
    {
        $order = $this->orders->byId($orderId);
        if ($order === null) {
            return self::result('failed', error: 'Bestellung nicht gefunden.');
        }

        // Already done. Not an error and not worth a second call — the webhook
        // asks this question on every redelivery.
        if (($order['invoice_status'] ?? 'none') === 'ok') {
            return self::result(
                'ok',
                number: self::str($order['invoice_number'] ?? null),
                id: self::str($order['invoice_lexware_id'] ?? null),
            );
        }

        // Only a paid order is invoiced. A pending one has no money behind it
        // and a refunded one must not be given a document that says it does.
        if (($order['status'] ?? '') !== 'paid') {
            return self::result('skipped', error: 'Bestellung ist nicht bezahlt.');
        }

        if ((int) ($order['invoice_attempts'] ?? 0) >= self::MAX_ATTEMPTS) {
            return self::result('failed', error: 'Zu viele Fehlversuche — Rechnung wird nicht erneut versucht.');
        }

        $client = $this->client();
        if ($client === null) {
            // No Lexware extension, or no API key. Deliberately NOT recorded as
            // a failure: nothing is wrong with this order, the feature is off.
            return self::result('skipped', error: 'Lexware ist nicht eingerichtet.');
        }

        $this->orders->beginInvoice($orderId);

        try {
            $items = $this->orders->itemsFor($orderId);
            if ($items === []) {
                throw new \RuntimeException('Bestellung hat keine Positionen.');
            }

            $payload = $this->builder->build($order, $items, $this->voucherDate($order));

            // `finalize: true` — a draft carries no number and no PDF, and the
            // whole point of this is the numbered document.
            $created = $client->createInvoice($payload, true);
            $lexwareId = self::str($created['id'] ?? null);
            if ($lexwareId === null) {
                throw new \RuntimeException('Lexware lieferte keine Rechnungs-ID.');
            }

            // The number and the PDF need a second and third call; neither is
            // allowed to undo the first. The invoice EXISTS at this point —
            // losing its number to a timeout must not make us create it twice.
            $number = null;
            $fileId = null;
            try {
                if (method_exists($client, 'getInvoice')) {
                    $number = self::str(($client->getInvoice($lexwareId))['voucherNumber'] ?? null);
                }
                if (method_exists($client, 'invoiceDocumentFileId')) {
                    $fileId = $client->invoiceDocumentFileId($lexwareId);
                }
            } catch (Throwable) {
                // An older tds-ext-lexware without these methods, or a hiccup
                // reading back. The invoice stands; the number can be looked up
                // in Lexware by hand.
            }

            $this->orders->recordInvoice($orderId, $lexwareId, $number, $fileId);
            return self::result('ok', number: $number, id: $lexwareId);
        } catch (Throwable $e) {
            // Recording the reason is itself allowed to fail — if the database
            // is what broke, this write breaks too, and the outer wrapper in
            // `invoice()` is what keeps that from reaching the webhook.
            $this->orders->failInvoice($orderId, self::readable($e));
            return self::result('failed', error: self::readable($e));
        }
    }

    /**
     * The invoice date: when the order was paid, not when this code ran.
     *
     * A webhook redelivered days later, or a retry after an outage, must not
     * date the document to the day the retry happened — the tax point is the
     * payment. Falls back to now only when the row carries no usable timestamp.
     *
     * @param array<string,mixed> $order
     */
    private function voucherDate(array $order): DateTimeImmutable
    {
        foreach (['paid_at', 'updated_at', 'created_at'] as $column) {
            $raw = self::str($order[$column] ?? null);
            if ($raw === null) {
                continue;
            }
            try {
                return new DateTimeImmutable($raw);
            } catch (Throwable) {
                // Unparseable timestamp — try the next, then now.
            }
        }
        return new DateTimeImmutable();
    }

    /**
     * The Lexware client, or `null` when the feature is unavailable.
     *
     * Three separate reasons to get `null`, all of them ordinary: the package
     * is not installed, the container has no usable binding, or no API key is
     * configured.
     */
    private function client(): ?object
    {
        if ($this->container === null || !class_exists(self::LEXWARE_CLIENT)) {
            return null;
        }
        try {
            $client = $this->container->get(self::LEXWARE_CLIENT);
        } catch (Throwable) {
            return null;
        }
        if (!is_object($client)) {
            return null;
        }
        // Its own answer to "am I usable", when it offers one.
        if (method_exists($client, 'isConfigured') && !$client->isConfigured()) {
            return null;
        }
        return method_exists($client, 'createInvoice') ? $client : null;
    }

    /** A message a human can act on, never a stack trace. */
    private static function readable(Throwable $e): string
    {
        $message = trim($e->getMessage());
        return $message === '' ? $e::class : mb_substr($message, 0, 500);
    }

    /** @return array{status:string,number:?string,id:?string,error:?string} */
    private static function result(
        string $status,
        ?string $number = null,
        ?string $id = null,
        ?string $error = null,
    ): array {
        return ['status' => $status, 'number' => $number, 'id' => $id, 'error' => $error];
    }

    private static function str(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }
        $trimmed = trim($value);
        return $trimmed === '' ? null : $trimmed;
    }
}
