<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * The slots the other properties reference by key.
 *
 * Seeded rather than created on demand because the consuming code names these
 * keys in markup: `tds-blog-frontend` asks for `blog-article-end`, the portal
 * widget asks for `panel-dashboard`. A missing row is not an error there — the
 * placement endpoint answers with an empty slot and the section renders nothing
 * — so without a seed the feature would ship looking like it works and showing
 * nothing, on every property at once. Seeding them makes the slots visible and
 * editable in the panel from the first deploy.
 *
 * They start `active = 1` but empty and on the `auto` strategy, which resolves
 * to recently published products. An empty catalogue therefore still renders
 * nothing — the difference is that the operator can now see why.
 */
final class ShopSeedPlacements extends AbstractMigration
{
    private const PLACEMENTS = [
        [
            'key' => 'blog-article-end',
            'label' => 'Blog — unter dem Artikel',
            'surface' => 'blog',
            'heading_de' => 'Passend zum Thema',
            'heading_en' => 'Related products',
            'max_items' => 3,
        ],
        [
            'key' => 'blog-sidebar',
            'label' => 'Blog — Seitenleiste',
            'surface' => 'blog',
            'heading_de' => 'Empfehlung',
            'heading_en' => 'Recommended',
            'max_items' => 1,
        ],
        [
            'key' => 'panel-dashboard',
            'label' => 'Kundenportal — Dashboard',
            'surface' => 'panel',
            'heading_de' => 'Werkzeuge für Ihren Betrieb',
            'heading_en' => 'Tools for your business',
            'max_items' => 3,
        ],
        [
            'key' => 'shop-home',
            'label' => 'TDShop — Startseite',
            'surface' => 'shop',
            'heading_de' => 'Empfohlen',
            'heading_en' => 'Featured',
            'max_items' => 6,
        ],
    ];

    public function up(): void
    {
        $rows = [];
        foreach (self::PLACEMENTS as $p) {
            $rows[] = $p + ['strategy' => 'auto', 'selector' => null, 'active' => 1];
        }
        $this->table('shop_placement')->insert($rows)->saveData();
    }

    public function down(): void
    {
        $keys = implode("','", array_column(self::PLACEMENTS, 'key'));
        $this->execute("DELETE FROM shop_placement WHERE `key` IN ('{$keys}')");
    }
}
