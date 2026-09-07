<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Domain;

use PDO;

/**
 * Affiliate click counting — daily totals, nothing per visitor.
 *
 * No IP, no cookie, no user id, no time of day. That is not an omission to fill
 * in later: a per-click row would be a processing of visitor behaviour that
 * needs a legal basis and an erasure path, to answer a question ("what is
 * working?") that a daily total answers just as well.
 */
final class ClickRepository
{
    /** @var list<string> */
    private const SOURCES = ['shop', 'blog', 'customer'];

    public function __construct(private readonly PDO $pdo)
    {
    }

    /**
     * Count one click. Idempotent per (day, offer, source, placement, language),
     * so a retried or duplicated beacon costs nothing.
     */
    public function record(
        int $offerId,
        int $productId,
        string $source,
        ?string $placementKey,
        string $lang,
    ): void {
        $stmt = $this->pdo->prepare(
            'INSERT INTO shop_click (day, offer_id, product_id, source, placement_key, lang, clicks)'
            . ' VALUES (CURDATE(), :offer, :product, :source, :placement, :lang, 1)'
            . ' ON DUPLICATE KEY UPDATE clicks = clicks + 1',
        );
        $stmt->execute([
            'offer' => $offerId,
            'product' => $productId,
            'source' => in_array($source, self::SOURCES, true) ? $source : 'shop',
            'placement' => $placementKey,
            'lang' => $lang,
        ]);
    }

    /** Clicks per product over a window, for the panel. */
    public function topProducts(int $days = 30, int $limit = 10): array
    {
        $days = max(1, min(365, $days));
        $limit = max(1, min(50, $limit));
        $stmt = $this->pdo->prepare(
            'SELECT c.product_id, t.title, SUM(c.clicks) AS clicks'
            . ' FROM shop_click c'
            . " LEFT JOIN shop_product_translation t ON t.product_id = c.product_id AND t.lang = 'de'"
            . " WHERE c.day >= (CURDATE() - INTERVAL $days DAY)"
            . ' GROUP BY c.product_id, t.title ORDER BY clicks DESC'
            . " LIMIT $limit",
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }
}
