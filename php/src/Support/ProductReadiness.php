<?php
declare(strict_types=1);

namespace Tds\Ext\Shop\Support;

/**
 * Is a product complete enough to go live? A pure function over what the
 * repository already read, so the panel's traffic light and the server's
 * refusal to publish are one rule, not two that drift.
 *
 * Publishing a product the shop cannot render properly is silent: the page
 * comes out without text, without a price or without a picture and nothing
 * errors. That is why publishing is refused rather than warned about.
 *
 * Each problem is a stable code plus a German sentence. The panel shows the
 * sentence; tests and any later automation compare the code.
 */
final class ProductReadiness
{
    public const META_MIN = 80;
    public const META_MAX = 160;
    public const TITLE_MAX = 65;

    /**
     * @param array{
     *   kind: string,
     *   category: string,
     *   categoryNamed: bool,
     *   hasCover: bool,
     *   ownPrice: bool,
     *   freshAffiliatePrice: bool,
     *   translations: array<string, array{title: string, bodyLength: int, bodyFormat: string,
     *     metaDescription: ?string, metaTitle: ?string}>
     * } $p
     * @return list<array{code: string, message: string}>
     */
    public static function problems(array $p): array
    {
        $out = [];
        foreach (['de' => 'Deutsch', 'en' => 'Englisch'] as $lang => $name) {
            $t = $p['translations'][$lang] ?? null;
            if ($t === null) {
                $out[] = ['code' => "translation_{$lang}", 'message' => "Übersetzung {$name} fehlt."];
                continue;
            }
            // `blocks` with a body renders NOTHING on the shop site; only
            // markdown does. A filled-in body in the wrong format is the same
            // empty page as no body.
            if ($t['bodyLength'] === 0 || $t['bodyFormat'] !== 'markdown') {
                $out[] = ['code' => "body_{$lang}", 'message' => "Produkttext ({$name}) fehlt."];
            }
            $meta = mb_strlen(trim((string) ($t['metaDescription'] ?? '')));
            if ($meta < self::META_MIN || $meta > self::META_MAX) {
                $out[] = [
                    'code' => "meta_{$lang}",
                    'message' => "Meta-Beschreibung ({$name}) hat {$meta} Zeichen, nötig sind "
                        . self::META_MIN . '–' . self::META_MAX . '.',
                ];
            }
            $title = trim((string) ($t['metaTitle'] ?? '')) !== '' ? (string) $t['metaTitle'] : $t['title'];
            if (mb_strlen($title) > self::TITLE_MAX) {
                $out[] = [
                    'code' => "title_{$lang}",
                    'message' => "Seitentitel ({$name}) ist länger als " . self::TITLE_MAX . ' Zeichen; Meta-Titel setzen.',
                ];
            }
        }
        if (!$p['hasCover']) {
            $out[] = ['code' => 'cover', 'message' => 'Titelbild fehlt.'];
        }
        if (!$p['categoryNamed']) {
            $out[] = ['code' => 'category', 'message' => "Kategorie „{$p['category']}“ hat keinen Namen."];
        }
        if ($p['kind'] === 'digital') {
            if (!$p['ownPrice']) {
                $out[] = ['code' => 'price', 'message' => 'Kein Festpreis hinterlegt.'];
            }
        } elseif (!$p['freshAffiliatePrice']) {
            // The sync fills it. Until then a published affiliate product shows
            // "Preis beim Händler" — legal, but not "fertig".
            $out[] = ['code' => 'price', 'message' => 'Noch kein aktueller Partnerpreis (Amazon-Abgleich abwarten).'];
        }
        return $out;
    }
}
