<?php
/**
 * API documentation for TDShop's routes — consumed through `ApiDocSource` and
 * rendered in the admin frontend's API reference (`GET /wiki.json`).
 *
 * `php/tests/ShopApiDocsTest.php` asserts both directions: a mounted route
 * without an entry here and an entry here without a mounted route both fail the
 * suite. `pattern` must therefore match the Slim pattern in `register()`
 * VERBATIM, inline regex included — it is the join key, so a prettified path
 * produces an orphan doc AND an undocumented route rather than an error.
 */

declare(strict_types=1);

return [
    /* --- public catalogue ------------------------------------------------ */
    [
        'method' => 'GET',
        'pattern' => '/content/shop',
        'summary' => 'Öffentlicher Produktkatalog, seitenweise',
        'description' => 'Liefert veröffentlichte Produkte in einer Sprache. Blättert per '
            . '`cursor` (Keyset auf `published_at`,`id`) statt per Offset, damit ein '
            . 'zwischenzeitlich veröffentlichtes Produkt keine Zeile doppelt zeigt und '
            . 'keine überspringt. Preise sind bereits auf Frische geprüft: ein Angebot, '
            . 'dessen Kurs älter als 24 Stunden ist, verlässt den Server ohne Preis.',
        'auth' => 'public',
        'tag' => 'Katalog',
        'params' => [
            ['name' => 'lang', 'in' => 'query', 'description' => '`de` (Vorgabe) oder `en`.'],
            ['name' => 'limit', 'in' => 'query', 'description' => '1–48, Vorgabe 12.'],
            ['name' => 'cursor', 'in' => 'query', 'description' => '`nextCursor` der vorigen Antwort.'],
            ['name' => 'category', 'in' => 'query', 'description' => 'Auf eine Kategorie einschränken.'],
            ['name' => 'tag', 'in' => 'query', 'description' => 'Auf ein Schlagwort einschränken.'],
        ],
        'responses' => [
            ['status' => 200, 'description' => '`{products: [...], nextCursor: string|null}` — '
                . 'auch bei einem Datenbankfehler, dann leer statt 500.'],
        ],
    ],
    [
        'method' => 'GET',
        'pattern' => '/content/shop/categories',
        'summary' => 'Kategorien mit Anzahl veröffentlichter Produkte',
        'auth' => 'public',
        'tag' => 'Katalog',
        'params' => [['name' => 'lang', 'in' => 'query', 'description' => '`de` oder `en`.']],
        'responses' => [['status' => 200, 'description' => '`{categories: [{category, total}]}`']],
    ],
    [
        'method' => 'GET',
        'pattern' => '/content/shop/{slug:[a-z0-9-]+}',
        'summary' => 'Ein Produkt mit Text und Angeboten',
        'description' => 'Der Slug ist sprachabhängig: DE und EN dürfen verschiedene Slugs '
            . 'haben, weil die Paarung über `product_id` läuft und nicht über Slug-Spiegelung.',
        'auth' => 'public',
        'tag' => 'Katalog',
        'params' => [
            ['name' => 'slug', 'in' => 'path', 'description' => 'Sprachabhängiger Produkt-Slug.'],
            ['name' => 'lang', 'in' => 'query', 'description' => '`de` oder `en`.'],
        ],
        'responses' => [
            ['status' => 200, 'description' => 'Das Produkt inkl. `offers`.'],
            ['status' => 404, 'description' => 'Kein veröffentlichtes Produkt unter diesem Slug.'],
        ],
    ],
    [
        'method' => 'GET',
        'pattern' => '/content/shop/placement/{key:[a-z0-9-]+}',
        'summary' => 'Einen Werbeplatz auflösen',
        'description' => 'Die EINE Quelle für alle drei Platzierungsmechanismen — manuell '
            . 'bestückt, automatisch nach Kategorie/Schlagwort, oder fester Slot. Der '
            . 'Aufrufer erfährt nicht, welche Strategie geantwortet hat; die redaktionelle '
            . 'Entscheidung bleibt dadurch im Panel statt in drei Frontends. Ein '
            . 'unbekannter oder inaktiver Schlüssel liefert einen leeren Slot, keinen Fehler.',
        'auth' => 'public',
        'tag' => 'Platzierungen',
        'params' => [
            ['name' => 'key', 'in' => 'path', 'description' => 'z.B. `blog-article-end`.'],
            ['name' => 'lang', 'in' => 'query', 'description' => '`de` oder `en`.'],
            ['name' => 'category', 'in' => 'query', 'description' => 'Kontext der aufrufenden Seite; '
                . 'schlägt den Selektor der Platzierung, weil er das genauere Signal ist.'],
        ],
        'responses' => [['status' => 200, 'description' => '`{key, heading, label, products}` — '
            . '`label` ist die Werbekennzeichnung und wird mitgeliefert, damit sie keine '
            . 'der drei Oberflächen vergessen kann.']],
    ],
    [
        'method' => 'GET',
        'pattern' => '/content/shop/offer/{id:[0-9]+}/target',
        'summary' => 'Ziel eines Affiliate-Angebots auflösen und Klick zählen',
        'description' => 'Wird von der `/go/{id}`-Route der Shop-Site aufgerufen. Der Umweg '
            . 'hält den Partner-Tag an genau einer Stelle — direkt gesetzte Links würden ihn '
            . 'in jedem Artikel und jeder Produktzeile einbacken. Gezählt wird pro Tag '
            . 'aggregiert: keine IP, kein Cookie, keine Benutzer-ID.',
        'auth' => 'public',
        'tag' => 'Platzierungen',
        'params' => [
            ['name' => 'id', 'in' => 'path', 'description' => 'Angebots-ID.'],
            ['name' => 'source', 'in' => 'query', 'description' => '`shop`, `blog` oder `customer`.'],
            ['name' => 'placement', 'in' => 'query', 'description' => 'Schlüssel des Werbeplatzes.'],
            ['name' => 'lang', 'in' => 'query', 'description' => '`de` oder `en`.'],
        ],
        'responses' => [
            ['status' => 200, 'description' => '`{url}` — das Weiterleitungsziel.'],
            ['status' => 404, 'description' => 'Angebot unbekannt oder Produkt nicht veröffentlicht.'],
        ],
    ],

    /* --- panel ------------------------------------------------------------ */
    [
        'method' => 'GET',
        'pattern' => '/shop/summary',
        'summary' => 'Kennzahlen für das Dashboard-Widget',
        'description' => '`unwritten` zählt Produkte ohne eigenen Einschätzungstext — die '
            . 'Zahl, die darüber entscheidet, ob der Katalog als Fachbeitrag oder als '
            . 'Linksammlung gelesen wird. `stalePrices` zählt Angebote, deren Kurs älter '
            . 'als 24 Stunden ist und deshalb nicht mehr angezeigt wird.',
        'auth' => 'permission',
        'permission' => 'shop:read',
        'tag' => 'Verwaltung',
        'responses' => [['status' => 200, 'description' => '`{published, drafts, unwritten, offers, stalePrices}`']],
    ],
    [
        'method' => 'GET',
        'pattern' => '/shop/products',
        'summary' => 'Alle Produkte, Entwürfe eingeschlossen',
        'description' => 'Eigener Einstiegspunkt statt eines `includeDrafts`-Schalters auf der '
            . 'öffentlichen Liste: ein Bool, der den Veröffentlichungsfilter abschaltet, ist '
            . 'eine unachtsame Vorgabe davon entfernt, Entwürfe auszuliefern.',
        'auth' => 'permission',
        'permission' => 'shop:read',
        'tag' => 'Verwaltung',
        'responses' => [['status' => 200, 'description' => '`{products: [{id, kind, status, translations}]}`']],
    ],
    [
        'method' => 'GET',
        'pattern' => '/shop/products/{id:[0-9]+}',
        'summary' => 'Ein Produkt für den Editor',
        'description' => 'Liefert die ROHEN Angebotszeilen mit echten Preisen und Zeitstempeln, '
            . 'nicht die auf Frische geprüfte öffentliche Form. Ein Redakteur muss sehen '
            . 'können, dass ein Preis existiert, aber zu alt zum Anzeigen ist — genau das '
            . 'ist der Zustand, den er beheben soll.',
        'auth' => 'permission',
        'permission' => 'shop:read',
        'tag' => 'Verwaltung',
        'params' => [['name' => 'id', 'in' => 'path', 'description' => 'Produkt-ID.']],
        'responses' => [
            ['status' => 200, 'description' => '`{product, translations, offers}`'],
            ['status' => 404, 'description' => 'Unbekannte ID.'],
        ],
    ],
    [
        'method' => 'POST',
        'pattern' => '/shop/products',
        'summary' => 'Produkt anlegen',
        'description' => '`published_at` wird beim ersten Wechsel auf `published` gesetzt und '
            . 'danach nie mehr verschoben — sonst würde eine Tippfehlerkorrektur den Katalog '
            . 'umsortieren und die `lastmod` der Sitemap neu schreiben.',
        'auth' => 'permission',
        'permission' => 'shop:write',
        'tag' => 'Verwaltung',
        'params' => [
            ['name' => 'lang', 'in' => 'body', 'description' => 'Sprache dieser Übersetzung.'],
            ['name' => 'slug', 'in' => 'body', 'description' => 'Kleinbuchstaben, Ziffern, Bindestriche.'],
            ['name' => 'title', 'in' => 'body', 'description' => 'Pflicht.'],
            ['name' => 'status', 'in' => 'body', 'description' => '`draft`, `published` oder `archived`.'],
            ['name' => 'editorialStatus', 'in' => 'body', 'description' => '`none`, `stub` oder `published` — '
                . 'nur `published` kommt in die Sitemap und wird indexiert.'],
        ],
        'responses' => [
            ['status' => 201, 'description' => '`{id}`'],
            ['status' => 422, 'description' => 'Slug ungültig oder reserviert, oder Titel fehlt.'],
        ],
    ],
    [
        'method' => 'PUT',
        'pattern' => '/shop/products/{id:[0-9]+}',
        'summary' => 'Produkt und eine Sprache davon bearbeiten',
        'auth' => 'permission',
        'permission' => 'shop:write',
        'tag' => 'Verwaltung',
        'params' => [
            ['name' => 'id', 'in' => 'path', 'description' => 'Produkt-ID.'],
            ['name' => 'lang', 'in' => 'body', 'description' => 'Welche Übersetzung geschrieben wird.'],
        ],
        'responses' => [
            ['status' => 200, 'description' => '`{ok: true}`'],
            ['status' => 404, 'description' => 'Unbekannte ID.'],
            ['status' => 422, 'description' => 'Ungültige Eingabe.'],
        ],
    ],
    [
        'method' => 'DELETE',
        'pattern' => '/shop/products/{id:[0-9]+}',
        'summary' => 'Produkt löschen',
        'description' => 'Übersetzungen, Angebote, Medien und Platzierungseinträge hängen per '
            . 'CASCADE daran und verschwinden mit.',
        'auth' => 'permission',
        'permission' => 'shop:write',
        'tag' => 'Verwaltung',
        'params' => [['name' => 'id', 'in' => 'path', 'description' => 'Produkt-ID.']],
        'responses' => [
            ['status' => 200, 'description' => '`{ok: true}`'],
            ['status' => 404, 'description' => 'Unbekannte ID.'],
        ],
    ],
    [
        'method' => 'PUT',
        'pattern' => '/shop/products/{id:[0-9]+}/offers',
        'summary' => 'Angebote eines Produkts ersetzen',
        'description' => 'Ein von Hand eingetragener AFFILIATE-Preis bekommt bewusst KEINEN '
            . 'Zeitstempel und wird deshalb nicht angezeigt, bis der Abgleich ihn bestätigt — '
            . 'die Lizenz verlangt einen Preis aus der API. Ein eigener Preis ist unser eigener '
            . 'und gilt sofort.',
        'auth' => 'permission',
        'permission' => 'shop:write',
        'tag' => 'Verwaltung',
        'params' => [
            ['name' => 'id', 'in' => 'path', 'description' => 'Produkt-ID.'],
            ['name' => 'offers', 'in' => 'body', 'description' => 'Vollständige Liste in Anzeigereihenfolge.'],
        ],
        'responses' => [['status' => 200, 'description' => '`{ok: true}`']],
    ],
    [
        'method' => 'GET',
        'pattern' => '/shop/placements',
        'summary' => 'Alle Werbeplätze',
        'auth' => 'permission',
        'permission' => 'shop:write',
        'tag' => 'Platzierungen',
        'responses' => [['status' => 200, 'description' => '`{placements: [...]}`']],
    ],
    [
        'method' => 'PUT',
        'pattern' => '/shop/placements/{key:[a-z0-9-]+}',
        'summary' => 'Einen Werbeplatz bearbeiten',
        'description' => '`productIds` ersetzt die manuelle Bestückung vollständig und in '
            . 'der übergebenen Reihenfolge — in einer Transaktion, weil eine halb '
            . 'angewandte Umsortierung schlimmer ist als eine abgelehnte.',
        'auth' => 'permission',
        'permission' => 'shop:write',
        'tag' => 'Platzierungen',
        'params' => [
            ['name' => 'key', 'in' => 'path', 'description' => 'Schlüssel des Werbeplatzes.'],
            ['name' => 'strategy', 'in' => 'body', 'description' => '`manual`, `category`, `tag` oder `auto`.'],
            ['name' => 'productIds', 'in' => 'body', 'description' => 'Nur bei `manual`: Produkt-IDs in Anzeigereihenfolge.'],
        ],
        'responses' => [
            ['status' => 200, 'description' => '`{ok: true}`'],
            ['status' => 404, 'description' => 'Kein aktiver Werbeplatz unter diesem Schlüssel.'],
        ],
    ],
    [
        'method' => 'GET',
        'pattern' => '/shop/clicks',
        'summary' => 'Meistgeklickte Produkte im Zeitraum',
        'auth' => 'permission',
        'permission' => 'shop:read',
        'tag' => 'Verwaltung',
        'params' => [['name' => 'days', 'in' => 'query', 'description' => '1–365, Vorgabe 30.']],
        'responses' => [['status' => 200, 'description' => '`{products: [{product_id, title, clicks}]}`']],
    ],
];
