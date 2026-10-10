<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Tds\Ext\Shop\Support\CatalogueSeed;

/**
 * The prepared catalogue: twelve categories with intro and FAQ, and fifty own
 * service products in seven of them — every one a DRAFT, complete enough to
 * pass the publish gate, so the operator only has to press "Freigeben".
 *
 * The data lives in `php/db/seed/*.php`; the rules for writing it (draft,
 * idempotent, fill-only-empty, no `price_checked_at` on own offers) live in
 * `Support\CatalogueSeed`. `ShopCatalogueSeedTest` holds every row against the
 * publish gate without a database.
 *
 * Also completes the six packages of `20260915000001` with "Kurz gesagt",
 * facts and FAQ, and corrects their "zuzüglich 19 %" — the shop shows the
 * gross price next to it.
 *
 * Runs after `20261010000001`, which adds the columns written here.
 */
final class ShopSeedCatalogue extends AbstractMigration
{
    public const OWN_FILES = [
        'own-webauftritt.php',
        'own-seo-sichtbarkeit.php',
        'own-wartung-betrieb.php',
        'own-recht-datenschutz.php',
        'own-e-mail-domain.php',
        'own-digitalisierung.php',
        'own-schulung.php',
    ];

    public function up(): void
    {
        $pdo = $this->getAdapter()->getConnection();

        CatalogueSeed::categories($pdo, CatalogueSeed::load('categories.php'));
        foreach (self::OWN_FILES as $file) {
            CatalogueSeed::products($pdo, CatalogueSeed::load($file));
        }

        $answers = CatalogueSeed::load('service-packages-answers.php');
        CatalogueSeed::answers($pdo, $answers);
        CatalogueSeed::grossWording($pdo, array_merge(array_keys($answers['de']), array_keys($answers['en'])));
    }

    public function down(): void
    {
        $pdo = $this->getAdapter()->getConnection();
        foreach (self::OWN_FILES as $file) {
            CatalogueSeed::removeProducts($pdo, CatalogueSeed::load($file));
        }
        // Categories stay: removing one a product still uses would leave its
        // page unnamed, and the names are harmless without products.
    }
}
