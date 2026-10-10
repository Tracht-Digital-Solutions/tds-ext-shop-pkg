<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\Shop\Support\CatalogueSeed;

/**
 * Twenty affiliate products (Amazon) with their own assessment, as DRAFTS.
 *
 * Each carries only its ASIN: price, partner-tagged link and Amazon's image
 * arrive with the sync (`OfferSync::apply()`), and until they have, the publish
 * gate refuses — so nothing goes live without a fresh price and a picture.
 * Data in `php/db/seed/affiliate.php`, rules in `Support\CatalogueSeed`.
 */
final class ShopSeedAffiliate extends AbstractMigration
{
    public function up(): void
    {
        CatalogueSeed::products($this->getAdapter()->getConnection(), CatalogueSeed::load('affiliate.php'));
    }

    public function down(): void
    {
        CatalogueSeed::removeProducts($this->getAdapter()->getConnection(), CatalogueSeed::load('affiliate.php'));
    }
}
