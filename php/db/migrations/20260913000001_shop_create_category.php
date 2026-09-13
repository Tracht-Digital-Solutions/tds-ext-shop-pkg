<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Display names for categories, in German and English.
 *
 * ### The slug stays on the product
 *
 * `shop_product.category` keeps holding the slug, and the slug keeps being the
 * category's identity: it is part of the shop's address (`/kategorie/netzwerk`,
 * `/en/category/netzwerk`). This table only NAMES a slug. No product row is
 * touched, and a slug without a row here still renders — as the slug with a
 * capital first letter, exactly as before this table existed.
 *
 * Before it, the English shop showed German category names, because nothing
 * anywhere knew an English one: the category was a free text field in the
 * panel and the API served it verbatim.
 *
 * ### Keyed on the slug, and the key is explicitly NOT NULL
 *
 * A numeric id would be a second identity for something that already has one,
 * and every read would join through it. The string primary key carries
 * `'null' => false` on purpose: Phinx makes every column nullable by default,
 * MariaDB (dev, CI) quietly corrects a nullable primary key, and MySQL 8 on the
 * production host refuses it with 1171 — at install time, on a customer's host.
 */
final class ShopCreateCategory extends AbstractMigration
{
    public function change(): void
    {
        $this->table('shop_category', [
            'id' => false,
            'primary_key' => ['slug'],
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('slug', 'string', ['limit' => 60, 'null' => false])
            // Both nullable: a name nobody has written yet falls back rather
            // than rendering empty. English falls back to German first.
            ->addColumn('name_de', 'string', ['limit' => 80, 'null' => true])
            ->addColumn('name_en', 'string', ['limit' => 80, 'null' => true])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->create();
    }
}
