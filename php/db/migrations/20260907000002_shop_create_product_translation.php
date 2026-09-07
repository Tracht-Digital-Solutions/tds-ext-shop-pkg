<?php
declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

/**
 * The language-dependent half of a product: slug, prose and SEO fields.
 *
 * `unique (lang, slug)` rather than `unique (slug)`: the German and English
 * entries for one product are free to have different slugs, because they are
 * paired through `product_id`. That is a real improvement over the blog's
 * arrangement, where the two languages must mirror slugs 1:1 and hreflang is
 * derived by swapping a path prefix. Here the alternate is looked up, so a
 * German slug can read like German.
 *
 * `body` holds either markdown or a blocks JSON document, the same dual format
 * `blog_post.body` uses, flagged by `body_format`. Storing it opaquely is
 * deliberate: there is no PHP mirror of the block validator anywhere in the
 * platform any more (it lived in the archived tds-content-api), so the zod
 * schema in tds-shared is the single validator and this column is just bytes.
 */
final class ShopCreateProductTranslation extends AbstractMigration
{
    public function change(): void
    {
        $this->table('shop_product_translation', [
            'id' => true,
            'engine' => 'InnoDB',
            'collation' => 'utf8mb4_unicode_ci',
        ])
            ->addColumn('product_id', 'integer', ['signed' => false])
            ->addColumn('lang', 'string', ['limit' => 2, 'default' => 'de'])
            ->addColumn('slug', 'string', ['limit' => 120])
            ->addColumn('title', 'string', ['limit' => 200])
            ->addColumn('teaser', 'string', ['limit' => 400, 'default' => ''])
            ->addColumn('body', 'text', ['limit' => MysqlAdapter::TEXT_MEDIUM, 'null' => true])
            ->addColumn('body_format', 'string', ['limit' => 16, 'default' => 'blocks'])
            ->addColumn('meta_description', 'string', ['limit' => 300, 'null' => true])
            ->addColumn('machine_translated', 'boolean', ['default' => false])
            ->addColumn('created_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('updated_at', 'datetime', ['default' => 'CURRENT_TIMESTAMP', 'update' => 'CURRENT_TIMESTAMP'])
            ->addIndex(['lang', 'slug'], ['unique' => true, 'name' => 'uniq_shop_translation_slug'])
            ->addIndex(['product_id', 'lang'], ['unique' => true, 'name' => 'uniq_shop_translation_lang'])
            ->addForeignKey('product_id', 'shop_product', 'id', ['delete' => 'CASCADE'])
            ->create();
    }
}
