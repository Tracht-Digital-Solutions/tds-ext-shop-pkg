<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\Shop\Support\CatalogueSeed;

/**
 * Fifty more affiliate products (`php/db/seed/affiliate-3.php`), as DRAFTS,
 * and the new categories `buero` and `content` they partly live in.
 *
 * Categories are re-applied from `categories.php`: `CatalogueSeed::categories()`
 * only inserts what is missing and fills only empty fields, so earlier
 * categories and anything edited in the panel stay as they are.
 */
final class ShopSeedAffiliateThird extends AbstractMigration
{
    public function up(): void
    {
        $pdo = $this->getAdapter()->getConnection();
        CatalogueSeed::categories($pdo, CatalogueSeed::load('categories.php'));
        CatalogueSeed::products($pdo, CatalogueSeed::load('affiliate-3.php'));
    }

    public function down(): void
    {
        CatalogueSeed::removeProducts($this->getAdapter()->getConnection(), CatalogueSeed::load('affiliate-3.php'));
    }
}
