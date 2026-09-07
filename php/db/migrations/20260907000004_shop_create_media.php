<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Product images.
 *
 * `source` is the whole reason this table has two shapes:
 *
 * - `remote` — the URL is served as-is. For Amazon this is not a shortcut but
 *   the rule: the licence requires product images to come from the API response
 *   and be served from Amazon's CDN. Mirroring or editing them is a breach, so
 *   there is nothing to store and `storage_path` stays null.
 * - `upload` — bytes we own, for TDS's own products, written through
 *   `Support\ShopMediaStorage`. That follows the pattern already proven in
 *   tds-ext-documents-pkg (`DocumentStorage`): a mime allow-list, a size cap, a
 *   path outside any document root, and only metadata in the database.
 *
 * One table rather than two because every consumer asks the same question —
 * "give me the cover" — and should not have to know which half answered.
 */
final class ShopCreateMedia extends AbstractMigration
{
    public function change(): void
    {
        $this->table('shop_media', [
            'id' => true,
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('product_id', 'integer', ['signed' => false])
            ->addColumn('role', 'string', ['limit' => 16, 'default' => 'cover'])
            ->addColumn('source', 'string', ['limit' => 16, 'default' => 'remote'])
            ->addColumn('url', 'text', ['null' => true])
            ->addColumn('storage_path', 'string', ['limit' => 500, 'null' => true])
            ->addColumn('mime_type', 'string', ['limit' => 100, 'null' => true])
            ->addColumn('size_bytes', 'integer', ['signed' => false, 'null' => true])
            ->addColumn('alt', 'string', ['limit' => 300, 'default' => ''])
            ->addColumn('sort', 'integer', ['signed' => false, 'default' => 0])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['product_id', 'role', 'sort'], ['name' => 'idx_shop_media_product'])
            ->addForeignKey('product_id', 'shop_product', 'id', ['delete' => 'CASCADE'])
            ->create();
    }
}
