<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Multi-line orders, physical goods, and a delivery address.
 *
 * `shop_order_item` was already a separate table with a `quantity`, so the
 * schema could always hold a basket — the checkout simply never wrote more than
 * one row. What was missing is everything that follows from selling a THING
 * rather than a service:
 *
 * - which lines have to be shipped at all,
 * - what the shipping costs, kept apart from the goods so a VAT return and a
 *   credit note can both see it,
 * - where to send it.
 *
 * ### Why shipping is three columns and not one
 *
 * Shipping is an ancillary service (Nebenleistung): it does not carry its own
 * VAT rate, it takes the rate of the goods it delivers (Abschn. 3.10 UStAE).
 * With a basket at one rate that is arithmetic nobody notices; with a mixed
 * basket the shipping charge has to be apportioned across the rates by net
 * value and taxed in parts. Storing net, tax and gross separately is what makes
 * that computation auditable after the fact instead of a number nobody can
 * reproduce.
 *
 * ### Why the address is on the order and not in a customer table
 *
 * This is a guest checkout — the token is the whole authorisation, there is no
 * account. The address is a fact about this order, frozen at the moment it was
 * placed, exactly like the item titles beside it.
 */
final class ShopCartAndShipping extends AbstractMigration
{
    public function change(): void
    {
        // Whether a purchase of this offer has to be delivered. On the sale
        // terms rather than the product: the same product can be a service
        // package (nothing to ship) and a boxed thing (something to ship), and
        // an offer is already what answers "how is this bought".
        $this->table('shop_own_product')
            ->addColumn('requires_shipping', 'boolean', [
                'default' => false,
                'after' => 'fulfilment',
                'comment' => 'physical goods — needs an address, shipping cost and the 14-day withdrawal',
            ])
            ->update();

        $this->table('shop_order')
            ->addColumn('shipping_net_cents', 'integer', ['signed' => false, 'default' => 0, 'after' => 'gross_cents'])
            ->addColumn('shipping_tax_cents', 'integer', ['signed' => false, 'default' => 0, 'after' => 'shipping_net_cents'])
            ->addColumn('shipping_gross_cents', 'integer', ['signed' => false, 'default' => 0, 'after' => 'shipping_tax_cents'])
            // Null for a basket with nothing to deliver, which is the common
            // case here and must not be filled with empty strings: "we did not
            // ask" and "they left it blank" are different facts.
            ->addColumn('ship_name', 'string', ['limit' => 200, 'null' => true])
            ->addColumn('ship_line1', 'string', ['limit' => 200, 'null' => true])
            ->addColumn('ship_line2', 'string', ['limit' => 200, 'null' => true])
            ->addColumn('ship_postcode', 'string', ['limit' => 16, 'null' => true])
            ->addColumn('ship_city', 'string', ['limit' => 120, 'null' => true])
            ->addColumn('ship_country', 'string', ['limit' => 2, 'null' => true])
            ->update();

        // A snapshot, like the title and the price beside it: whether THIS line
        // was shipped is part of what the order was, and it decides which
        // withdrawal regime applied to it. Re-deriving that from the catalogue
        // years later would read today's answer, not the one that was given.
        $this->table('shop_order_item')
            ->addColumn('requires_shipping', 'boolean', ['default' => false, 'after' => 'fulfilment'])
            ->update();
    }
}
