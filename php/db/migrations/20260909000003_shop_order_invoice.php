<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * What Lexware Office made of a paid order.
 *
 * ### Lexware is the leading system, so almost nothing here is ours
 *
 * The invoice number, the PDF and the archive live in Lexware Office. § 14
 * UStG wants one unbroken sequence of numbers, and a sequence has exactly one
 * author — so these columns are a RECORD of what Lexware decided, never a
 * second source of it. Nothing in this module ever invents an invoice number,
 * and nothing recomputes one.
 *
 * ### The columns live on `shop_order`, not in a table of their own
 *
 * An order has at most one invoice and an invoice belongs to exactly one
 * order. A 1:1 side table would buy a join on every order read and give
 * nothing back. It would also make "is this order invoiced?" — the question
 * the payment webhook asks on every single delivery — an outer join instead of
 * a column read.
 *
 * ### `invoice_status` is not derivable from `invoice_number`
 *
 * A missing number could mean four different things: never attempted, in
 * flight, refused by Lexware, or created as a draft that carries no number
 * yet. They call for different handling, so the state is written down rather
 * than guessed from the absence of a value. `invoice_error` keeps the last
 * reason in readable German — a failure nobody can read is a failure nobody
 * fixes.
 *
 * ### `invoice_attempts` exists so a loop cannot form
 *
 * Invoicing is retried by the payment provider's own webhook redeliveries, and
 * a permanently rejected payload (a malformed address, a tax rate Lexware
 * refuses) would otherwise be retried for as long as the provider keeps
 * calling. The counter is the ceiling.
 */
final class ShopOrderInvoice extends AbstractMigration
{
    public function change(): void
    {
        $this->table('shop_order')
            // none | pending | ok | failed
            ->addColumn('invoice_status', 'string', [
                'limit' => 16,
                'default' => 'none',
                'after' => 'fulfilled_at',
                'comment' => 'none|pending|ok|failed — set by OrderInvoicing, never by hand',
            ])
            // Lexware's own identifiers. Both nullable: an order that was never
            // invoiced has neither, and an order invoiced as a draft has the id
            // but no number until it is finalised.
            ->addColumn('invoice_lexware_id', 'string', [
                'limit' => 64,
                'null' => true,
                'after' => 'invoice_status',
            ])
            ->addColumn('invoice_number', 'string', [
                'limit' => 40,
                'null' => true,
                'after' => 'invoice_lexware_id',
            ])
            // The file id of the rendered PDF in Lexware, not a URL: the bytes
            // are behind the API key and a link would be either useless or a
            // credential.
            ->addColumn('invoice_file_id', 'string', [
                'limit' => 64,
                'null' => true,
                'after' => 'invoice_number',
            ])
            ->addColumn('invoice_error', 'text', [
                'null' => true,
                'after' => 'invoice_file_id',
            ])
            ->addColumn('invoice_attempts', 'integer', [
                'signed' => false,
                'default' => 0,
                'after' => 'invoice_error',
            ])
            ->addColumn('invoiced_at', 'datetime', [
                'null' => true,
                'after' => 'invoice_attempts',
            ])
            // The webhook's question on every delivery is "paid, and not yet
            // invoiced?" — that pair, in that order.
            ->addIndex(['status', 'invoice_status'], ['name' => 'idx_shop_order_invoice'])
            ->update();
    }
}
