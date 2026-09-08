<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Domain;

use PDO;

/**
 * Orders for TDS's own digital service packages.
 *
 * Two things here are load-bearing rather than plumbing: the price is computed
 * from the database and never from the request, and the webhook is idempotent.
 */
final class OrderRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * The sellable offer behind a product slug, with its stored net price.
     *
     * Returns null when the product has no own offer, is not published, or has
     * no sale terms — all three mean "not for sale here", and telling them
     * apart would only help somebody probing the catalogue.
     */
    public function sellable(string $slug, string $lang): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.id AS product_id, o.id AS offer_id, t.title, t.slug,'
            . ' s.net_cents, s.vat_rate_bp, s.fulfilment, o.currency'
            . ' FROM shop_product p'
            . ' JOIN shop_product_translation t ON t.product_id = p.id AND t.lang = :lang'
            . " JOIN shop_offer o ON o.product_id = p.id AND o.kind = 'own' AND o.disabled_at IS NULL"
            . ' JOIN shop_own_product s ON s.offer_id = o.id'
            . " WHERE t.slug = :slug AND p.status = 'published' AND p.published_at IS NOT NULL"
            . ' ORDER BY o.position ASC, o.id ASC LIMIT 1',
        );
        $stmt->execute(['slug' => $slug, 'lang' => $lang]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    /**
     * Gross from net, in integer cents.
     *
     * Rounded half-up on the tax, not on the gross: computing the gross first
     * and deriving the tax back out of it loses a cent on roughly a third of
     * amounts, and it is the net figure a VAT return is built from.
     *
     * @return array{net:int,tax:int,gross:int}
     */
    public static function price(int $netCents, int $vatRateBp): array
    {
        $tax = (int) round($netCents * $vatRateBp / 10000);
        return ['net' => $netCents, 'tax' => $tax, 'gross' => $netCents + $tax];
    }

    /**
     * Open a pending order.
     *
     * The withdrawal wording is copied in verbatim, not referenced: § 356
     * Abs. 4 BGB makes the *sentence the customer agreed to* the thing that has
     * to be provable later, and that sentence will be edited over the years.
     *
     * @param  array<string,mixed> $offer a row from {@see sellable()}
     * @return array{id:int,token:string,orderNo:string,gross:int}
     */
    public function open(
        array $offer,
        string $email,
        ?string $name,
        string $withdrawalText,
        string $country = 'DE',
    ): array {
        $price = self::price((int) $offer['net_cents'], (int) $offer['vat_rate_bp']);
        $token = bin2hex(random_bytes(16));
        $orderNo = 'TDS-' . gmdate('Ymd') . '-' . strtoupper(substr($token, 0, 6));

        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO shop_order (token, order_no, email, name, status, net_cents,'
                . ' tax_cents, gross_cents, tax_rate_bp, currency, country,'
                . ' withdrawal_consent_at, withdrawal_consent_text)'
                . " VALUES (:token, :no, :email, :name, 'pending', :net, :tax, :gross,"
                . ' :rate, :currency, :country, UTC_TIMESTAMP(), :consent)',
            );
            $stmt->execute([
                'token' => $token,
                'no' => $orderNo,
                'email' => $email,
                'name' => $name,
                'net' => $price['net'],
                'tax' => $price['tax'],
                'gross' => $price['gross'],
                'rate' => (int) $offer['vat_rate_bp'],
                'currency' => (string) ($offer['currency'] ?? 'EUR'),
                'country' => $country,
                'consent' => $withdrawalText,
            ]);
            $orderId = (int) $this->pdo->lastInsertId();

            $this->pdo->prepare(
                'INSERT INTO shop_order_item (order_id, product_id, offer_id, title, slug,'
                . ' quantity, net_cents, tax_cents, gross_cents, fulfilment)'
                . ' VALUES (:order, :product, :offer, :title, :slug, 1, :net, :tax, :gross, :fulfilment)',
            )->execute([
                'order' => $orderId,
                'product' => (int) $offer['product_id'],
                'offer' => (int) $offer['offer_id'],
                'title' => (string) $offer['title'],
                'slug' => (string) $offer['slug'],
                'net' => $price['net'],
                'tax' => $price['tax'],
                'gross' => $price['gross'],
                'fulfilment' => (string) ($offer['fulfilment'] ?? 'manual'),
            ]);

            $this->pdo->commit();
            return ['id' => $orderId, 'token' => $token, 'orderNo' => $orderNo, 'gross' => $price['gross']];
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Record which provider is handling this order, and its id for the attempt.
     *
     * `stripe_session_id` is written as well, for Stripe only, and nothing
     * reads it. It is one release of overlap so that rolling the
     * `shop_order_payment_provider` migration back does not strand the rows
     * created in between — this table records money, and a rollback that loses
     * the reference loses the ability to reconcile a payment. The follow-up
     * migration drops the column and this line goes with it.
     */
    public function attachPayment(int $orderId, string $provider, string $reference): void
    {
        $this->pdo->prepare(
            'UPDATE shop_order SET payment_provider = :p, provider_session_id = :r,'
            . " stripe_session_id = CASE WHEN :p2 = 'stripe' THEN :r2 ELSE stripe_session_id END"
            . ' WHERE id = :id',
        )->execute([
            'p' => $provider,
            'r' => $reference,
            'p2' => $provider,
            'r2' => $reference,
            'id' => $orderId,
        ]);
    }

    /**
     * Mark an order paid, once.
     *
     * Every provider retries a webhook until it gets a 2xx, so the same "it is
     * paid" arrives repeatedly — and a second delivery must not produce a
     * second fulfilment. The `status = 'pending'` guard in the WHERE clause is
     * the idempotency: the first delivery updates one row, every later one
     * updates zero, and the caller can tell which happened. Keep it there. A
     * SELECT-then-UPDATE would reintroduce the race this avoids.
     *
     * ### Why the token is preferred over the provider's reference
     *
     * `$token` is OUR identifier, echoed back by the provider (Stripe metadata,
     * PayPal `custom_id`). `$reference` is theirs. Where both are available the
     * token wins, because the provider's own id is not always in the event that
     * matters: PayPal's capture carries the order id only under
     * `supplementary_data`, a field its documentation calls supplementary and
     * therefore not something to key a payment state machine on.
     *
     * @return bool true when THIS call was the one that marked it paid
     */
    public function markPaid(
        string $provider,
        ?string $reference,
        ?string $paymentRef,
        ?string $token = null,
    ): bool {
        if ($token !== null && $token !== '') {
            $sql = "UPDATE shop_order SET status = 'paid', provider_payment_ref = :pr"
                . " WHERE token = :key AND payment_provider = :p AND status = 'pending'";
            $key = $token;
        } elseif ($reference !== null && $reference !== '') {
            $sql = "UPDATE shop_order SET status = 'paid', provider_payment_ref = :pr"
                . " WHERE provider_session_id = :key AND payment_provider = :p AND status = 'pending'";
            $key = $reference;
        } else {
            // A verified event that identifies no order. Not an error to raise
            // at the provider — it would only retry — but nothing to act on.
            return false;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['pr' => $paymentRef, 'key' => $key, 'p' => $provider]);
        return $stmt->rowCount() === 1;
    }

    /** Same two-identifier rule as {@see markPaid()}, and the same one-shot guard. */
    public function markRefunded(string $provider, ?string $paymentRef, ?string $token = null): bool
    {
        if ($paymentRef !== null && $paymentRef !== '') {
            $sql = "UPDATE shop_order SET status = 'refunded'"
                . " WHERE provider_payment_ref = :key AND payment_provider = :p AND status = 'paid'";
            $key = $paymentRef;
        } elseif ($token !== null && $token !== '') {
            $sql = "UPDATE shop_order SET status = 'refunded'"
                . " WHERE token = :key AND payment_provider = :p AND status = 'paid'";
            $key = $token;
        } else {
            return false;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['key' => $key, 'p' => $provider]);
        return $stmt->rowCount() === 1;
    }

    /** The customer-facing order view. The token IS the authorisation. */
    public function byToken(string $token): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM shop_order WHERE token = :t LIMIT 1');
        $stmt->execute(['t' => $token]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($order === false) {
            return null;
        }
        $items = $this->pdo->prepare('SELECT * FROM shop_order_item WHERE order_id = :id');
        $items->execute(['id' => (int) $order['id']]);

        // Never leaked to the customer view: the provider ids are operational
        // detail, and the token is already in their hands. `payment_provider`
        // stays — the customer may reasonably see which method they paid with.
        unset(
            $order['stripe_session_id'],
            $order['stripe_payment_intent'],
            $order['provider_session_id'],
            $order['provider_payment_ref'],
            $order['note'],
        );
        $order['items'] = $items->fetchAll(PDO::FETCH_ASSOC) ?: [];
        return $order;
    }

    /** The panel list. */
    public function recent(int $limit = 100): array
    {
        $limit = max(1, min(500, $limit));
        $stmt = $this->pdo->query(
            'SELECT o.*, (SELECT GROUP_CONCAT(i.title SEPARATOR ", ") FROM shop_order_item i'
            . ' WHERE i.order_id = o.id) AS items'
            . " FROM shop_order o ORDER BY o.created_at DESC LIMIT {$limit}",
        );
        return $stmt === false ? [] : ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);
    }

    public function markFulfilled(int $orderId, ?string $note): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE shop_order SET fulfilled_at = UTC_TIMESTAMP(), note = :note'
            . " WHERE id = :id AND status = 'paid'",
        );
        $stmt->execute(['note' => $note, 'id' => $orderId]);
        return $stmt->rowCount() === 1;
    }

    /** Counts for the dashboard. */
    public function summary(): array
    {
        $row = $this->pdo->query(
            'SELECT'
            . " (SELECT COUNT(*) FROM shop_order WHERE status = 'paid') AS paid,"
            . " (SELECT COUNT(*) FROM shop_order WHERE status = 'paid' AND fulfilled_at IS NULL) AS open,"
            . " (SELECT COALESCE(SUM(gross_cents),0) FROM shop_order WHERE status = 'paid'"
            . '   AND created_at >= (UTC_TIMESTAMP() - INTERVAL 30 DAY)) AS gross30',
        )?->fetch(PDO::FETCH_ASSOC) ?: [];

        return [
            'paid' => (int) ($row['paid'] ?? 0),
            // Paid but not yet delivered — the number that is somebody's to act on.
            'open' => (int) ($row['open'] ?? 0),
            'gross30Cents' => (int) ($row['gross30'] ?? 0),
        ];
    }
}
