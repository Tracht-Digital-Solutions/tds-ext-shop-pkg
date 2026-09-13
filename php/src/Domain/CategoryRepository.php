<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Domain;

use PDO;

/**
 * The names of categories, for the panel.
 *
 * The public reads resolve names inside `ProductRepository` (see
 * `categoryNames()` there), because they already hold the rows and must stay
 * fail-soft. This class is the staff side: list what exists, write a name.
 */
final class CategoryRepository
{
    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Every category the panel should offer: the ones products actually use
     * AND the ones that have a name but no product yet, each with its product
     * count across all statuses.
     *
     * A union rather than only `shop_category`, because a category comes into
     * being by typing it on a product — requiring someone to register it first
     * would add a step nobody would remember, and the panel would show a list
     * that disagrees with the catalogue beside it.
     *
     * @return list<array{slug: string, nameDe: ?string, nameEn: ?string, products: int}>
     */
    public function adminList(): array
    {
        $stmt = $this->pdo->query(
            'SELECT u.slug, c.name_de, c.name_en, COALESCE(n.total, 0) AS total'
            . ' FROM (SELECT category AS slug FROM shop_product UNION SELECT slug FROM shop_category) u'
            . ' LEFT JOIN shop_category c ON c.slug = u.slug'
            . ' LEFT JOIN (SELECT category, COUNT(*) AS total FROM shop_product GROUP BY category) n'
            . ' ON n.category = u.slug'
            . ' ORDER BY u.slug ASC',
        );
        $out = [];
        foreach (($stmt === false ? [] : $stmt->fetchAll(PDO::FETCH_ASSOC)) ?: [] as $row) {
            $out[] = [
                'slug' => (string) $row['slug'],
                'nameDe' => $row['name_de'] !== null ? (string) $row['name_de'] : null,
                'nameEn' => $row['name_en'] !== null ? (string) $row['name_en'] : null,
                'products' => (int) $row['total'],
            ];
        }
        return $out;
    }

    /**
     * Write both names of one category.
     *
     * Both empty removes the row: a category without names renders by its slug
     * anyway, and keeping an empty row would only make "named" and "not named"
     * two states that look the same.
     *
     * Select-then-write instead of `ON DUPLICATE KEY UPDATE ... VALUES()`:
     * `VALUES()` is deprecated on MySQL 8 (production) and its replacement, the
     * row alias, does not exist on MariaDB (dev and CI).
     */
    public function save(string $slug, ?string $nameDe, ?string $nameEn): void
    {
        if ($nameDe === null && $nameEn === null) {
            $this->pdo->prepare('DELETE FROM shop_category WHERE slug = :slug')->execute(['slug' => $slug]);
            return;
        }

        $this->pdo->beginTransaction();
        try {
            $exists = $this->pdo->prepare('SELECT 1 FROM shop_category WHERE slug = :slug');
            $exists->execute(['slug' => $slug]);
            $sql = $exists->fetchColumn() !== false
                ? 'UPDATE shop_category SET name_de = :de, name_en = :en WHERE slug = :slug'
                : 'INSERT INTO shop_category (slug, name_de, name_en) VALUES (:slug, :de, :en)';
            $this->pdo->prepare($sql)->execute(['slug' => $slug, 'de' => $nameDe, 'en' => $nameEn]);
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
