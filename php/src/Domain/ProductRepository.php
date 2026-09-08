<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Domain;

use PDO;
use Tds\Ext\Shop\Support\PriceFreshness;

/**
 * Catalogue data access — plain PDO, no ORM, matching the other extensions.
 *
 * Two audiences, and the difference matters: the `public*` methods answer the
 * shop site, the journal and the portal, and they enforce the publication and
 * price rules; the `admin*` methods answer the panel and show everything.
 * Mixing them behind one method with a `$includeDrafts` flag is how a draft
 * eventually reaches a reader, so they are separate entry points.
 */
final class ProductRepository
{
    /** Products a visitor may see. Both conditions are required. */
    private const PUBLISHED = "p.status = 'published' AND p.published_at IS NOT NULL";

    /**
     * @param string $shopBaseUrl origin of the public shop, no trailing slash.
     *                            Absolute URLs are built here rather than in the
     *                            consumers because two of the three render on a
     *                            different origin, and a relative link would
     *                            silently point at the journal.
     */
    public function __construct(
        private readonly PDO $pdo,
        private readonly string $shopBaseUrl = 'https://shop.tracht-digital.de',
    ) {
    }

    /**
     * One page of the public catalogue.
     *
     * Keyset pagination on `(published_at, id)` rather than OFFSET: a catalogue
     * that gains a product while somebody is paging would otherwise show them
     * one row twice and skip another.
     *
     * @return array{products: list<array<string,mixed>>, nextCursor: ?string}
     */
    public function publicList(
        string $lang,
        int $limit = 12,
        ?string $cursor = null,
        ?string $category = null,
        ?string $tag = null,
    ): array {
        $limit = max(1, min(48, $limit));
        $where = [self::PUBLISHED, 't.lang = :lang'];
        $args = ['lang' => $lang];

        if ($category !== null && $category !== '') {
            $where[] = 'p.category = :category';
            $args['category'] = $category;
        }
        if ($tag !== null && $tag !== '') {
            // Comma-separated list; the delimiters on both sides stop "nas"
            // matching "nas-gehaeuse".
            $where[] = "CONCAT(',', REPLACE(COALESCE(p.tags, ''), ' ', ''), ',') LIKE :tag";
            $args['tag'] = '%,' . str_replace(' ', '', $tag) . ',%';
        }
        [$cursorSql, $cursorArgs] = self::cursorClause($cursor);
        if ($cursorSql !== null) {
            $where[] = $cursorSql;
            $args += $cursorArgs;
        }

        $sql = 'SELECT p.id, p.kind, p.category, p.tags, p.brand, p.published_at, p.editorial_status,'
            . ' t.slug, t.title, t.teaser, t.meta_description, t.machine_translated'
            . ' FROM shop_product p'
            . ' JOIN shop_product_translation t ON t.product_id = p.id'
            . ' WHERE ' . implode(' AND ', $where)
            . ' ORDER BY p.published_at DESC, p.id DESC'
            . ' LIMIT ' . ($limit + 1);

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($args);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $next = null;
        if (count($rows) > $limit) {
            $last = $rows[$limit - 1];
            $next = self::encodeCursor((string) $last['published_at'], (int) $last['id']);
            $rows = array_slice($rows, 0, $limit);
        }

        return ['products' => $this->hydrate($rows, $lang), 'nextCursor' => $next];
    }

    /** One public product, or null when it does not exist in this language. */
    public function publicOne(string $slug, string $lang): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.id, p.kind, p.category, p.tags, p.brand, p.published_at, p.updated_at, p.editorial_status,'
            . ' t.slug, t.title, t.teaser, t.body, t.body_format, t.meta_description, t.machine_translated'
            . ' FROM shop_product p'
            . ' JOIN shop_product_translation t ON t.product_id = p.id'
            . ' WHERE ' . self::PUBLISHED . ' AND t.lang = :lang AND t.slug = :slug'
            . ' LIMIT 1',
        );
        $stmt->execute(['lang' => $lang, 'slug' => $slug]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row === false) {
            return null;
        }
        $hydrated = $this->hydrate([$row], $lang);
        $out = $hydrated[0];
        $out['body'] = (string) ($row['body'] ?? '');
        $out['bodyFormat'] = (string) ($row['body_format'] ?? 'blocks');
        $out['updatedAt'] = self::iso($row['updated_at'] ?? null);
        return $out;
    }

    /**
     * The products for a resolved placement.
     *
     * `manual` reads the pinned list in order; every other strategy falls back
     * to the most recent published products, optionally narrowed by the
     * selector. A placement that matches nothing returns an empty list rather
     * than a broader guess — a slot showing something unrelated is worse than a
     * slot showing nothing.
     *
     * @param  array<string,mixed> $placement a `shop_placement` row
     * @return list<array<string,mixed>>
     */
    public function forPlacement(array $placement, string $lang, ?string $category = null): array
    {
        $limit = max(1, min(12, (int) ($placement['max_items'] ?? 3)));

        if (($placement['strategy'] ?? 'auto') === 'manual') {
            $stmt = $this->pdo->prepare(
                'SELECT p.id, p.kind, p.category, p.tags, p.brand, p.published_at, p.editorial_status,'
                . ' t.slug, t.title, t.teaser, t.meta_description, t.machine_translated'
                . ' FROM shop_placement_item i'
                . ' JOIN shop_product p ON p.id = i.product_id'
                . ' JOIN shop_product_translation t ON t.product_id = p.id AND t.lang = :lang'
                . ' WHERE i.placement_id = :pid AND ' . self::PUBLISHED
                . ' ORDER BY i.position ASC, i.id ASC'
                . ' LIMIT ' . $limit,
            );
            $stmt->execute(['pid' => (int) $placement['id'], 'lang' => $lang]);
            return $this->hydrate($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [], $lang);
        }

        // The automatic strategies. `category` may come from the placement's own
        // selector or from the calling page (a blog article passes its own), and
        // the caller's context wins — it is the more specific signal.
        $selector = self::decodeSelector($placement['selector'] ?? null);
        $cat = $category ?? (isset($selector['category']) ? (string) $selector['category'] : null);
        $tag = isset($selector['tag']) ? (string) $selector['tag'] : null;

        $page = $this->publicList($lang, $limit, null, $cat, $tag);
        if ($page['products'] === [] && $cat !== null) {
            // Narrowing found nothing; widen once to "recent" rather than
            // rendering an empty slot on every article in a new category.
            $page = $this->publicList($lang, $limit, null, null, null);
        }
        return $page['products'];
    }

    /** Distinct categories with their published counts, for the shop's navigation. */
    public function publicCategories(string $lang): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT p.category, COUNT(*) AS total'
            . ' FROM shop_product p JOIN shop_product_translation t ON t.product_id = p.id'
            . ' WHERE ' . self::PUBLISHED . ' AND t.lang = :lang'
            . ' GROUP BY p.category ORDER BY total DESC, p.category ASC',
        );
        $stmt->execute(['lang' => $lang]);
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $out[] = ['category' => (string) $row['category'], 'total' => (int) $row['total']];
        }
        return $out;
    }

    /** One offer with its product, for the click redirect. Null when unknown. */
    public function offerTarget(int $offerId): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT o.id, o.product_id, o.url, o.kind, o.network FROM shop_offer o'
            . ' JOIN shop_product p ON p.id = o.product_id'
            . ' WHERE o.id = :id AND ' . self::PUBLISHED . ' LIMIT 1',
        );
        $stmt->execute(['id' => $offerId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    /** Counts for the dashboard widget. */
    public function summary(): array
    {
        $sql = 'SELECT'
            . " (SELECT COUNT(*) FROM shop_product WHERE status = 'published') AS published,"
            . " (SELECT COUNT(*) FROM shop_product WHERE status = 'draft') AS drafts,"
            . " (SELECT COUNT(*) FROM shop_product WHERE editorial_status <> 'published') AS unwritten,"
            . ' (SELECT COUNT(*) FROM shop_offer) AS offers,'
            . ' (SELECT COUNT(*) FROM shop_offer WHERE price_checked_at IS NULL'
            . '   OR price_checked_at < (NOW() - INTERVAL 24 HOUR)) AS stale_prices';
        $row = $this->pdo->query($sql)?->fetch(PDO::FETCH_ASSOC) ?: [];
        return [
            'published' => (int) ($row['published'] ?? 0),
            'drafts' => (int) ($row['drafts'] ?? 0),
            // Products that render but stay out of the index for want of a
            // write-up — the number that decides whether the catalogue reads as
            // a resource or as a link farm.
            'unwritten' => (int) ($row['unwritten'] ?? 0),
            'offers' => (int) ($row['offers'] ?? 0),
            'stalePrices' => (int) ($row['stale_prices'] ?? 0),
        ];
    }

    /* --- panel ------------------------------------------------------------ */

    /**
     * Every product, drafts included, with its translations folded in.
     *
     * Separate from {@see publicList()} rather than a flag on it: a boolean
     * that switches the publication filter off is one careless default away
     * from serving drafts to readers.
     */
    public function adminList(int $limit = 200): array
    {
        $limit = max(1, min(500, $limit));
        $stmt = $this->pdo->query(
            'SELECT p.*, t.lang, t.slug, t.title, t.teaser, t.meta_description'
            . ' FROM shop_product p'
            . ' LEFT JOIN shop_product_translation t ON t.product_id = p.id'
            . ' ORDER BY p.updated_at DESC, p.id DESC, t.lang ASC'
            . ' LIMIT ' . ($limit * 2),
        );
        $rows = $stmt === false ? [] : ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: []);

        // Collapse the join: one entry per product, its languages nested.
        $byId = [];
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            if (!isset($byId[$id])) {
                $byId[$id] = [
                    'id' => $id,
                    'kind' => (string) $row['kind'],
                    'status' => (string) $row['status'],
                    'editorialStatus' => (string) $row['editorial_status'],
                    'category' => (string) $row['category'],
                    'tags' => self::splitTags($row['tags'] ?? null),
                    'brand' => $row['brand'] !== null ? (string) $row['brand'] : null,
                    'publishedAt' => self::iso($row['published_at'] ?? null),
                    'translations' => [],
                ];
            }
            if ($row['lang'] !== null) {
                $byId[$id]['translations'][(string) $row['lang']] = [
                    'slug' => (string) $row['slug'],
                    'title' => (string) $row['title'],
                    'teaser' => (string) ($row['teaser'] ?? ''),
                    'metaDescription' => $row['meta_description'] !== null
                        ? (string) $row['meta_description'] : null,
                ];
            }
        }
        return array_values($byId);
    }

    /** One product for the editor, with translations and offers. */
    public function adminOne(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM shop_product WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($product === false) {
            return null;
        }

        $tr = $this->pdo->prepare('SELECT * FROM shop_product_translation WHERE product_id = :id');
        $tr->execute(['id' => $id]);
        $translations = [];
        foreach ($tr->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $translations[(string) $row['lang']] = $row;
        }

        $of = $this->pdo->prepare('SELECT * FROM shop_offer WHERE product_id = :id ORDER BY position ASC, id ASC');
        $of->execute(['id' => $id]);

        return [
            'product' => $product,
            'translations' => $translations,
            // The panel sees the RAW offer rows — real prices and timestamps,
            // not the freshness-stripped public shape. An editor needs to see
            // that a price exists but is too old to show; that is exactly the
            // state they are there to fix.
            'offers' => $of->fetchAll(PDO::FETCH_ASSOC) ?: [],
        ];
    }

    /**
     * Create or update a product and one language of it.
     *
     * `published_at` is stamped on the first transition to `published` and never
     * moved afterwards: it is the date the piece went out, and letting a later
     * edit push it forward would reorder the catalogue and rewrite the sitemap's
     * `lastmod` for a typo fix.
     *
     * @param  array<string,mixed> $data
     * @return int the product id
     */
    public function upsert(?int $id, string $lang, array $data): int
    {
        $this->pdo->beginTransaction();
        try {
            $status = in_array($data['status'] ?? '', ['draft', 'published', 'archived'], true)
                ? (string) $data['status'] : 'draft';

            if ($id === null) {
                $stmt = $this->pdo->prepare(
                    'INSERT INTO shop_product (kind, status, editorial_status, category, tags, brand, published_at)'
                    . ' VALUES (:kind, :status, :editorial, :category, :tags, :brand, :published)',
                );
                $stmt->execute([
                    'kind' => self::oneOf($data['kind'] ?? null, ['affiliate', 'digital'], 'affiliate'),
                    'status' => $status,
                    'editorial' => self::oneOf($data['editorialStatus'] ?? null, ['none', 'stub', 'published'], 'none'),
                    'category' => (string) ($data['category'] ?? 'allgemein'),
                    'tags' => isset($data['tags']) ? (string) $data['tags'] : null,
                    'brand' => isset($data['brand']) ? (string) $data['brand'] : null,
                    'published' => $status === 'published' ? gmdate('Y-m-d H:i:s') : null,
                ]);
                $id = (int) $this->pdo->lastInsertId();
            } else {
                $stmt = $this->pdo->prepare(
                    'UPDATE shop_product SET kind = :kind, status = :status, editorial_status = :editorial,'
                    . ' category = :category, tags = :tags, brand = :brand,'
                    // Stamp only on the first publish; never move it afterwards.
                    . " published_at = CASE WHEN :status2 = 'published' AND published_at IS NULL"
                    . ' THEN UTC_TIMESTAMP() ELSE published_at END'
                    . ' WHERE id = :id',
                );
                $stmt->execute([
                    'kind' => self::oneOf($data['kind'] ?? null, ['affiliate', 'digital'], 'affiliate'),
                    'status' => $status,
                    'status2' => $status,
                    'editorial' => self::oneOf($data['editorialStatus'] ?? null, ['none', 'stub', 'published'], 'none'),
                    'category' => (string) ($data['category'] ?? 'allgemein'),
                    'tags' => isset($data['tags']) ? (string) $data['tags'] : null,
                    'brand' => isset($data['brand']) ? (string) $data['brand'] : null,
                    'id' => $id,
                ]);
            }

            $tr = $this->pdo->prepare(
                'INSERT INTO shop_product_translation'
                . ' (product_id, lang, slug, title, teaser, body, body_format, meta_description, machine_translated)'
                . ' VALUES (:pid, :lang, :slug, :title, :teaser, :body, :format, :meta, 0)'
                . ' ON DUPLICATE KEY UPDATE slug = VALUES(slug), title = VALUES(title),'
                . ' teaser = VALUES(teaser), body = VALUES(body), body_format = VALUES(body_format),'
                // A hand-edited translation stops being a machine translation.
                . ' meta_description = VALUES(meta_description), machine_translated = 0',
            );
            $tr->execute([
                'pid' => $id,
                'lang' => in_array($lang, ['de', 'en'], true) ? $lang : 'de',
                'slug' => (string) ($data['slug'] ?? ''),
                'title' => (string) ($data['title'] ?? ''),
                'teaser' => (string) ($data['teaser'] ?? ''),
                'body' => isset($data['body']) ? (string) $data['body'] : null,
                'format' => self::oneOf($data['bodyFormat'] ?? null, ['markdown', 'blocks'], 'blocks'),
                'meta' => isset($data['metaDescription']) ? (string) $data['metaDescription'] : null,
            ]);

            $this->pdo->commit();
            return $id;
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM shop_product WHERE id = :id');
        return $stmt->execute(['id' => $id]) && $stmt->rowCount() > 0;
    }

    /**
     * Replace a product's offers.
     *
     * Deliberately does NOT preserve `price_checked_at` across a rewrite: a
     * hand-edited price has never been confirmed by an API, so it must not
     * inherit a timestamp that would let it be displayed as a fetched quote.
     * The sync stamps it when it fetches.
     *
     * @param list<array<string,mixed>> $offers
     */
    public function setOffers(int $productId, array $offers): void
    {
        $this->pdo->beginTransaction();
        try {
            $this->pdo->prepare('DELETE FROM shop_offer WHERE product_id = :id')
                ->execute(['id' => $productId]);

            $ins = $this->pdo->prepare(
                'INSERT INTO shop_offer (product_id, kind, network, external_id, merchant, url,'
                . ' price_cents, currency, price_checked_at, availability, position)'
                . ' VALUES (:pid, :kind, :network, :ext, :merchant, :url,'
                . ' :price, :currency, :checked, :avail, :pos)',
            );
            $pos = 0;
            foreach ($offers as $offer) {
                $kind = self::oneOf($offer['kind'] ?? null, ['own', 'affiliate'], 'affiliate');
                $price = isset($offer['priceCents']) && $offer['priceCents'] !== ''
                    ? (int) $offer['priceCents'] : null;
                $ins->execute([
                    'pid' => $productId,
                    'kind' => $kind,
                    'network' => self::oneOf(
                        $offer['network'] ?? null,
                        ['amazon', 'awin', 'belboon', 'digistore', 'direct'],
                        'direct',
                    ),
                    'ext' => isset($offer['externalId']) && $offer['externalId'] !== ''
                        ? (string) $offer['externalId'] : null,
                    'merchant' => (string) ($offer['merchant'] ?? ''),
                    'url' => (string) ($offer['url'] ?? ''),
                    'price' => $price,
                    'currency' => (string) ($offer['currency'] ?? 'EUR'),
                    // Our own price is ours to state, so it is current by
                    // definition. A manually typed affiliate price is not: it
                    // gets no timestamp and therefore is not displayed until
                    // the sync confirms it.
                    'checked' => ($kind === 'own' && $price !== null) ? gmdate('Y-m-d H:i:s') : null,
                    'avail' => self::oneOf(
                        $offer['availability'] ?? null,
                        ['in_stock', 'out_of_stock', 'unknown'],
                        'unknown',
                    ),
                    'pos' => $pos++,
                ]);
            }

            // Keep the sort helper in step with the offers it summarises.
            $this->pdo->prepare(
                'UPDATE shop_product SET sort_price_cents ='
                . ' (SELECT MIN(price_cents) FROM shop_offer WHERE product_id = :id AND price_cents IS NOT NULL)'
                . ' WHERE id = :id2',
            )->execute(['id' => $productId, 'id2' => $productId]);

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    /* --- shaping ---------------------------------------------------------- */

    /**
     * Turn catalogue rows into the public read model, attaching offers and
     * media in one extra query each rather than one per product.
     *
     * @param  list<array<string,mixed>> $rows
     * @return list<array<string,mixed>>
     */
    private function hydrate(array $rows, string $lang): array
    {
        if ($rows === []) {
            return [];
        }
        $ids = array_map(static fn (array $r): int => (int) $r['id'], $rows);
        $offers = $this->offersFor($ids);
        $covers = $this->coversFor($ids);

        $out = [];
        foreach ($rows as $row) {
            $id = (int) $row['id'];
            $slug = (string) $row['slug'];

            // An own offer is bought here, not somewhere else, so its link goes
            // to this site's checkout rather than through the click redirect.
            // Done here because the checkout path needs the product's
            // language-dependent slug, which the offer query does not see.
            $offersForProduct = array_map(
                function (array $offer) use ($slug, $lang): array {
                    if ($offer['kind'] === 'own') {
                        $offer['url'] = $this->checkoutUrl($slug, $lang);
                    }
                    return $offer;
                },
                $offers[$id] ?? [],
            );

            $out[] = [
                'slug' => $slug,
                'lang' => $lang,
                'url' => $this->productUrl($slug, $lang),
                'title' => (string) $row['title'],
                'teaser' => (string) ($row['teaser'] ?? ''),
                'category' => (string) ($row['category'] ?? ''),
                'tags' => self::splitTags($row['tags'] ?? null),
                'imageUrl' => $covers[$id] ?? null,
                'kind' => (string) ($row['kind'] ?? 'affiliate'),
                'editorialStatus' => (string) ($row['editorial_status'] ?? 'none'),
                'metaDescription' => isset($row['meta_description'])
                    ? (string) $row['meta_description'] : null,
                'machineTranslated' => (bool) ($row['machine_translated'] ?? false),
                'publishedAt' => self::iso($row['published_at'] ?? null),
                'offers' => $offersForProduct,
            ];
        }
        return $out;
    }

    /**
     * Offers per product, with stale prices already stripped.
     *
     * This is the server-side floor under the 24-hour rule: the price never
     * leaves the process once it is too old, so a consumer that forgets to
     * check cannot show one. See {@see PriceFreshness}.
     *
     * @param  list<int> $productIds
     * @return array<int, list<array<string,mixed>>>
     */
    private function offersFor(array $productIds): array
    {
        $in = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = $this->pdo->prepare(
            'SELECT o.id, o.product_id, o.kind, o.network, o.merchant, o.url, o.price_cents,'
            . ' o.currency, o.price_checked_at, o.availability, o.position,'
            . ' s.net_cents, s.vat_rate_bp'
            . ' FROM shop_offer o'
            // Our own sale terms, when this offer is one of ours. LEFT so an
            // affiliate offer still comes back.
            . ' LEFT JOIN shop_own_product s ON s.offer_id = o.id'
            . " WHERE o.disabled_at IS NULL AND o.product_id IN ($in)"
            . ' ORDER BY o.product_id ASC, o.position ASC, o.id ASC',
        );
        $stmt->execute($productIds);

        $now = time();
        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $own = $row['net_cents'] !== null;

            // For OUR OWN offer the sale terms are the single source of truth
            // and the gross is derived from them. `shop_offer.price_cents`
            // would otherwise be a second price for the same thing, and two
            // prices for one product is a question of which one is charged.
            //
            // An own price is also never stale: it is ours to state, so it
            // skips the freshness check entirely. The 24-hour rule is about
            // quoting somebody else's price, not our own.
            if ($own) {
                $terms = OrderRepository::price((int) $row['net_cents'], (int) $row['vat_rate_bp']);
                $price = $terms['gross'];
                $checkedAt = null;
            } else {
                $checkedAt = $row['price_checked_at'] !== null ? (string) $row['price_checked_at'] : null;
                $price = PriceFreshness::publishablePrice(
                    $row['price_cents'] === null ? null : (int) $row['price_cents'],
                    $checkedAt,
                    $now,
                );
            }

            $out[(int) $row['product_id']][] = [
                'id' => (int) $row['id'],
                'kind' => (string) $row['kind'],
                'network' => (string) $row['network'],
                'merchant' => (string) $row['merchant'],
                // The click redirect, NOT the affiliate URL. Every consumer
                // links through `/go/{id}`, so the partner tag lives in exactly
                // one place — the offer row — instead of being baked into every
                // article and product listing that ever mentioned it. Changing
                // a tag then costs one UPDATE rather than a search-and-replace
                // across three repositories. The real target is resolved by
                // `offerTarget()` behind that redirect.
                // The click redirect. An OWN offer's link is rewritten in
                // `hydrate()` to point at its checkout — that is where the
                // product's language-dependent slug is known; this query only
                // sees offer rows.
                'url' => $this->shopBaseUrl . '/go/' . (int) $row['id'],
                'priceCents' => $price,
                'currency' => (string) ($row['currency'] ?? 'EUR'),
                // Cleared alongside the price. A timestamp left behind on a
                // stripped price would read as "checked, and free". An own
                // price carries none because it never expires.
                'priceCheckedAt' => $own ? null : ($price === null ? null : self::iso($checkedAt)),
                'availability' => (string) ($row['availability'] ?? 'unknown'),
                'position' => (int) ($row['position'] ?? 0),
                // The VAT split, so a checkout page can show the mandatory
                // breakdown without a second request. Null for an affiliate
                // offer — somebody else's tax is not ours to state.
                'netCents' => $own ? (int) $row['net_cents'] : null,
                'vatRateBp' => $own ? (int) $row['vat_rate_bp'] : null,
            ];
        }
        return $out;
    }

    /**
     * The cover image URL per product.
     *
     * @param  list<int> $productIds
     * @return array<int, string>
     */
    private function coversFor(array $productIds): array
    {
        $in = implode(',', array_fill(0, count($productIds), '?'));
        $stmt = $this->pdo->prepare(
            'SELECT product_id, source, url, storage_path FROM shop_media'
            . " WHERE role = 'cover' AND product_id IN ($in)"
            . ' ORDER BY product_id ASC, sort ASC, id ASC',
        );
        $stmt->execute($productIds);

        $out = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) ?: [] as $row) {
            $id = (int) $row['product_id'];
            if (isset($out[$id])) {
                continue; // first by sort order wins
            }
            // An uploaded file is addressed through the media route; a remote
            // one is served from its own host, which for Amazon is required
            // rather than merely convenient.
            $out[$id] = (string) $row['source'] === 'upload'
                ? '/content/shop/media/' . $id
                : (string) ($row['url'] ?? '');
        }
        return array_filter($out, static fn (string $u): bool => $u !== '');
    }

    /* --- small helpers ---------------------------------------------------- */

    /**
     * The canonical product URL on the shop site.
     *
     * The path segments differ per language, the way they do on the journal
     * (`kategorie` ↔ `category`). Kept here as well as in the site's own route
     * table because this string is what the journal and the portal link to —
     * they have no route table of the shop's to consult.
     */
    private function productUrl(string $slug, string $lang): string
    {
        $segment = $lang === 'en' ? '/en/product/' : '/produkt/';
        return $this->shopBaseUrl . $segment . $slug;
    }

    /** Where our own offer is actually bought. */
    private function checkoutUrl(string $slug, string $lang): string
    {
        $segment = $lang === 'en' ? '/en/checkout/' : '/kasse/';
        return $this->shopBaseUrl . $segment . $slug;
    }

    /**
     * Constrain an incoming value to a known set.
     *
     * Unknown values fall back rather than being rejected: these are enum-ish
     * columns whose vocabulary grows, and a 400 on an unrecognised network name
     * would make adding one a breaking change for every older client.
     *
     * @param list<string> $allowed
     */
    private static function oneOf(mixed $value, array $allowed, string $fallback): string
    {
        $v = is_string($value) ? strtolower(trim($value)) : '';
        return in_array($v, $allowed, true) ? $v : $fallback;
    }

    /** @return list<string> */
    private static function splitTags(mixed $raw): array
    {
        $parts = array_filter(array_map('trim', explode(',', (string) ($raw ?? ''))));
        return array_values($parts);
    }

    private static function iso(mixed $value): ?string
    {
        if (!is_string($value) || trim($value) === '') {
            return null;
        }
        $ts = strtotime($value);
        return $ts === false ? null : gmdate('Y-m-d\TH:i:s\Z', $ts);
    }

    /** @return array{0: ?string, 1: array<string,mixed>} */
    private static function cursorClause(?string $cursor): array
    {
        if ($cursor === null || $cursor === '') {
            return [null, []];
        }
        $raw = base64_decode(strtr($cursor, '-_', '+/'), true);
        if ($raw === false || !str_contains($raw, '|')) {
            // A malformed cursor restarts the listing rather than 500ing; the
            // visitor sees page one, which is the recoverable outcome.
            return [null, []];
        }
        [$date, $id] = explode('|', $raw, 2);
        return [
            '(p.published_at < :cur_date OR (p.published_at = :cur_date AND p.id < :cur_id))',
            ['cur_date' => $date, 'cur_id' => (int) $id],
        ];
    }

    private static function encodeCursor(string $publishedAt, int $id): string
    {
        return rtrim(strtr(base64_encode($publishedAt . '|' . $id), '+/', '-_'), '=');
    }

    /** @return array<string,mixed> */
    private static function decodeSelector(mixed $raw): array
    {
        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }
        try {
            $decoded = json_decode($raw, true, 8, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return [];
        }
        return is_array($decoded) ? $decoded : [];
    }
}
