<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\Shop\Support\CatalogueSeed;

/**
 * Thirty more affiliate products (`php/db/seed/affiliate-2.php`), as DRAFTS,
 * and the new category `unterwegs` they partly live in.
 *
 * Categories are re-applied from `categories.php`: `CatalogueSeed::categories()`
 * only inserts what is missing and fills only empty fields, so the categories of
 * `20261010000002` and anything edited in the panel stay as they are.
 *
 * Several of these products are embedded in journal articles seeded by
 * tds-ext-blog-cms (`{{produkt:<slug>}}`); they appear there once released.
 */
final class ShopSeedAffiliateSecond extends AbstractMigration
{
    public function up(): void
    {
        $pdo = $this->getAdapter()->getConnection();
        CatalogueSeed::categories($pdo, CatalogueSeed::load('categories.php'));
        CatalogueSeed::products($pdo, CatalogueSeed::load('affiliate-2.php'));
    }

    public function down(): void
    {
        CatalogueSeed::removeProducts($this->getAdapter()->getConnection(), CatalogueSeed::load('affiliate-2.php'));
    }
}
