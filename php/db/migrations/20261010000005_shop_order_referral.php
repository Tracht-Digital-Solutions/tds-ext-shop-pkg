<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Who recommended the shop, frozen into the order like every other fact of the
 * purchase: the partner code the buyer arrived with (`?ref=` link or typed at
 * the checkout) and what they wrote under "Wer hat dich empfohlen?".
 *
 * The commission itself is not the shop's business — the order only records
 * the claim, and the payment webhook reports it through the contract's
 * `Commerce\SaleEvents` to whichever module keeps the ledger.
 */
final class ShopOrderReferral extends AbstractMigration
{
    public function change(): void
    {
        $this->table('shop_order')
            ->addColumn('referral_code', 'string', ['limit' => 40, 'null' => true])
            ->addColumn('referral_via', 'string', ['limit' => 10, 'null' => true])
            ->addColumn('referred_by_note', 'string', ['limit' => 500, 'null' => true])
            ->update();
    }
}
