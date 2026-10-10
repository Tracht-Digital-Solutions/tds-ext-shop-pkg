<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Domain;

use PDO;
use Tds\Ext\Shop\Support\PairList;

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
     * @return list<array{slug: string, nameDe: ?string, nameEn: ?string, introDe: ?string, introEn: ?string,
     *   faqDe: list<array<string,string>>, faqEn: list<array<string,string>>, products: int}>
     */
    public function adminList(): array
    {
        $stmt = $this->pdo->query(
            'SELECT u.slug, c.name_de, c.name_en, c.intro_de, c.intro_en, c.faq_de, c.faq_en,'
            . ' COALESCE(n.total, 0) AS total'
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
                'introDe' => $row['intro_de'] !== null ? (string) $row['intro_de'] : null,
                'introEn' => $row['intro_en'] !== null ? (string) $row['intro_en'] : null,
                'faqDe' => PairList::decode($row['faq_de'] ?? null, 'q', 'a'),
                'faqEn' => PairList::decode($row['faq_en'] ?? null, 'q', 'a'),
                'products' => (int) $row['total'],
            ];
        }
        return $out;
    }

    /**
     * Write both names of one category, and — when the request carries them —
     * its intro and FAQ in both languages.
     *
     * Everything empty removes the row: a category without names renders by
     * its slug anyway, and keeping an empty row would only make "named" and
     * "not named" two states that look the same.
     *
     * Select-then-write instead of `ON DUPLICATE KEY UPDATE ... VALUES()`:
     * `VALUES()` is deprecated on MySQL 8 (production) and its replacement, the
     * row alias, does not exist on MariaDB (dev and CI).
     *
     * @param array{introDe?: ?string, introEn?: ?string, faqDe?: mixed, faqEn?: mixed} $copy
     *        only the keys present are written; an older panel build sends none
     */
    public function save(string $slug, ?string $nameDe, ?string $nameEn, array $copy = []): void
    {
        $columns = [];
        foreach (['introDe' => 'intro_de', 'introEn' => 'intro_en'] as $key => $col) {
            if (array_key_exists($key, $copy)) {
                $text = is_string($copy[$key]) ? trim($copy[$key]) : '';
                $columns[$col] = $text === '' ? null : mb_substr($text, 0, 4000);
            }
        }
        foreach (['faqDe' => 'faq_de', 'faqEn' => 'faq_en'] as $key => $col) {
            if (array_key_exists($key, $copy)) {
                $columns[$col] = PairList::encode(PairList::clean($copy[$key], 'q', 'a'));
            }
        }

        $this->pdo->beginTransaction();
        try {
            $exists = $this->pdo->prepare('SELECT intro_de, intro_en, faq_de, faq_en FROM shop_category WHERE slug = :slug');
            $exists->execute(['slug' => $slug]);
            $current = $exists->fetch(PDO::FETCH_ASSOC);

            $after = array_merge(
                ['intro_de' => null, 'intro_en' => null, 'faq_de' => null, 'faq_en' => null],
                $current === false ? [] : $current,
                $columns,
            );
            if ($nameDe === null && $nameEn === null && array_filter($after, static fn ($v) => $v !== null) === []) {
                $this->pdo->prepare('DELETE FROM shop_category WHERE slug = :slug')->execute(['slug' => $slug]);
                $this->pdo->commit();
                return;
            }

            $sql = $current !== false
                ? 'UPDATE shop_category SET name_de = :de, name_en = :en, intro_de = :intro_de, intro_en = :intro_en,'
                    . ' faq_de = :faq_de, faq_en = :faq_en WHERE slug = :slug'
                : 'INSERT INTO shop_category (slug, name_de, name_en, intro_de, intro_en, faq_de, faq_en)'
                    . ' VALUES (:slug, :de, :en, :intro_de, :intro_en, :faq_de, :faq_en)';
            $this->pdo->prepare($sql)->execute(['slug' => $slug, 'de' => $nameDe, 'en' => $nameEn] + $after);
            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }
}
