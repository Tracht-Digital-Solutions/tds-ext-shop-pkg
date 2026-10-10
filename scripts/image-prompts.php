<?php
declare(strict_types=1);

/**
 * Writes docs/product-image-prompts.md from the seed data: one DALL-E prompt
 * per own product, all in the same style so the catalogue reads as one set.
 *
 *   php scripts/image-prompts.php
 */
require dirname(__DIR__) . '/vendor/autoload.php';

use Tds\Ext\Shop\Support\CatalogueSeed;

const STYLE = 'Flat editorial illustration, calm and modern, deep navy blue and warm bordeaux red accents '
    . 'on a soft warm off-white background, subtle paper grain, rounded shapes, gentle soft shadows, '
    . 'generous empty space around the subject, centred composition, no text, no letters, no logos, '
    . 'no real brand marks, no people\'s faces in detail. Wide 16:10 format.';

$files = [
    'own-webauftritt.php', 'own-seo-sichtbarkeit.php', 'own-wartung-betrieb.php', 'own-recht-datenschutz.php',
    'own-e-mail-domain.php', 'own-digitalisierung.php', 'own-schulung.php',
];

$out = "# Produktbilder: DALL-E-Prompts\n\n"
    . "Generiert von `php scripts/image-prompts.php` aus `php/db/seed/own-*.php`. Nicht von Hand bearbeiten.\n\n"
    . "## So geht's\n\n"
    . "1. Jeden Prompt unten vollständig in DALL-E einfügen (Stil + Motiv stehen schon zusammen).\n"
    . "2. Bild im Format 16:10 erzeugen, als WebP mit 1600 × 1000 px speichern.\n"
    . "3. Dateiname = angegebener Name, abgelegt in `tds-shop-frontend/public/images/products/`.\n"
    . "4. Wenn alle Bilder liegen: Bescheid geben. Eine Migration stellt die Titelbilder dann von den\n"
    . "   Platzhaltern (Leistungsbilder der Landingpage) auf die neuen Dateien um.\n\n"
    . "Einheitlicher Stil (steckt in jedem Prompt):\n\n> " . STYLE . "\n";

$n = 0;
foreach ($files as $file) {
    $products = CatalogueSeed::load($file);
    $category = $products[0]['category'];
    $out .= "\n## " . $category . "\n";
    foreach ($products as $p) {
        $n++;
        $out .= "\n### {$n}. {$p['de']['title']}\n\n"
            . "Datei: `{$p['de']['slug']}.webp` · Alt-Text: {$p['alt']}\n\n"
            . "```text\n" . 'Subject: ' . $p['prompt'] . '. ' . STYLE . "\n```\n";
    }
}

file_put_contents(dirname(__DIR__) . '/docs/product-image-prompts.md', $out);
echo "{$n} prompts written to docs/product-image-prompts.md\n";
