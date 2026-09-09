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
    /* --- Kauf eigener Leistungen ------------------------------------------- */
    [
        'method' => 'POST',
        'pattern' => '/shop/quote',
        'summary' => 'Warenkorb bepreisen, ohne zu bestellen',
        'description' => 'Die Warenkorbseite braucht die Summe, die gleich abgebucht wird, und '
            . 'darin stecken die Versandkosten — abhaengig vom Inhalt, von der '
            . 'Versandkostenfreigrenze und von einer Aufteilung der Steuer auf die Steuersaetze '
            . 'der Ware. Das laesst sich im Browser aus dem Katalog nicht berechnen, und ein '
            . 'Warenkorb, der eine andere Zahl zeigt als die Kasse, ist schlimmer als einer, der '
            . 'gar keine zeigt. Geld wird deshalb an genau einer Stelle berechnet, und das hier '
            . 'ist ein Lesezugriff darauf: nichts wird geschrieben, nichts reserviert, danach '
            . 'existiert keine Bestellung. Antwortet ausserdem, welche Widerrufsbloecke die Kasse '
            . 'zeigen muss und ob ueberhaupt eine Zustimmung einzuholen ist.',
        'auth' => 'public',
        'tag' => 'Kauf',
        'params' => [
            ['name' => 'items', 'in' => 'body', 'description' => '`[{slug, quantity}]`. Ein '
                . 'einzelnes `slug` wird weiterhin akzeptiert.'],
            ['name' => 'lang', 'in' => 'body', 'description' => '`de` | `en`.'],
        ],
        'responses' => [
            ['status' => 200, 'description' => '`{lines, shipping, netCents, taxCents, grossCents, '
                . 'withdrawalRegime, withdrawalConsentRequired, addressRequired}`'],
            ['status' => 404, 'description' => 'Mindestens eine Position ist nicht verkaeuflich.'],
        ],
    ],
    [
        'method' => 'POST',
        'pattern' => '/shop/checkout',
        'summary' => 'Zahlung starten (Anbieter waehlbar)',
        'description' => 'Vom Browser des Besuchers aufgerufen, deshalb NICHT site-key-'
            . 'geschuetzt. Der Preis wird aus der Datenbank gelesen, nie aus der Anfrage — ein '
            . 'gesendeter Preis ist ein Preis, den der Kunde gewaehlt hat. Zwei Ablehnungen sind '
            . 'keine Formalitaeten: ohne Widerrufsbestaetigung (§ 356 Abs. 4 BGB) erlischt das '
            . 'Widerrufsrecht nicht, und ausserhalb der erlaubten Laender entstuende eine '
            . 'OSS-Pflicht (§ 3a Abs. 5 UStG). Der Bestellknopf mit "Zahlungspflichtig '
            . 'bestellen" steht auf UNSERER Seite (§ 312j Abs. 3 BGB) — der Anbieter ist nur der '
            . 'Zahlungsschritt danach. `provider` waehlt zwischen den konfigurierten Anbietern; '
            . 'ein nicht konfigurierter wird mit 503 abgewiesen, auch wenn er registriert ist.',
        'auth' => 'public',
        'tag' => 'Kauf',
        'params' => [
            ['name' => 'slug', 'in' => 'body', 'description' => 'Produkt-Slug.'],
            ['name' => 'email', 'in' => 'body', 'description' => 'Pflicht, wird validiert.'],
            ['name' => 'name', 'in' => 'body', 'description' => 'Optional.'],
            ['name' => 'country', 'in' => 'body', 'description' => 'ISO-2, Vorgabe `DE`.'],
            ['name' => 'provider', 'in' => 'body', 'description' => 'Zahlungsart aus '
                . '`GET /shop/payment-methods`. Leer = der erste konfigurierte Anbieter.'],
            ['name' => 'withdrawalConsent', 'in' => 'body', 'description' => 'Muss `true` sein.'],
            ['name' => 'withdrawalText', 'in' => 'body', 'description' => 'Der exakt angezeigte '
                . 'Wortlaut; wird in der Bestellung mitgespeichert, nicht nur referenziert.'],
        ],
        'responses' => [
            ['status' => 200, 'description' => '`{url, token}` — `url` fuehrt zum Anbieter.'],
            ['status' => 404, 'description' => 'Kein verkaeufliches Angebot unter diesem Slug.'],
            ['status' => 422, 'description' => 'E-Mail, Widerrufsbestaetigung oder Land.'],
            ['status' => 502, 'description' => 'Der Anbieter hat die Zahlung abgelehnt.'],
            ['status' => 503, 'description' => 'Kein Anbieter konfiguriert.'],
        ],
    ],
    [
        'method' => 'GET',
        'pattern' => '/shop/payment-methods',
        'summary' => 'Verfuegbare Zahlungsarten',
        'description' => 'Was der Shop gerade wirklich abschliessen kann, in Anzeigereihenfolge. '
            . 'Die Kasse rendert diese Liste statt einer fest verdrahteten — das ist der Grund, '
            . 'warum ein unfertiger Adapter ungefaehrlich im Baum liegen kann: Wero ist '
            . 'registriert, meldet aber `isConfigured() === false` und taucht deshalb hier nicht '
            . 'auf. Sobald ein PSP hinterlegt ist, erscheint es ohne Frontend-Aenderung.',
        'auth' => 'public',
        'tag' => 'Kauf',
        'params' => [],
        'responses' => [
            ['status' => 200, 'description' => '`{methods: [{id, label}]}` — moeglicherweise leer.'],
        ],
    ],
    [
        'method' => 'POST',
        'pattern' => '/shop/payment/{provider:[a-z]+}/webhook',
        'summary' => 'Zahlungs-Webhook (signaturgeprueft)',
        'description' => 'Bewusst AUSSERHALB von `/content/shop`: kein Anbieter kennt einen '
            . 'Site-Key, und SiteKeyMiddleware vergleicht segmentweise — unter dem Praefix wuerde '
            . 'jeder Aufruf abgewiesen. Jeder Anbieter prueft sein EIGENES Verfahren: Stripe eine '
            . 'HMAC ueber den rohen Body, PayPal ueber einen Rueckruf an seinen '
            . 'Verifikationsendpunkt. Der rohe Body wird unveraendert durchgereicht, sonst '
            . 'verifiziert Stripe nicht. `markPaid()` ist ueber seine WHERE-Klausel idempotent, '
            . 'weil jeder Anbieter bis zu einer 2xx-Antwort wiederholt. Ein verifiziertes, aber '
            . 'nicht behandeltes Ereignis wird ebenfalls mit 200 quittiert. Bei PayPal wird auf '
            . '`CHECKOUT.ORDER.APPROVED` hin abgebucht — eine Freigabe ist noch kein Geld.',
        'auth' => 'token',
        'tag' => 'Kauf',
        'params' => [
            ['name' => 'provider', 'in' => 'path', 'description' => '`stripe` | `paypal` | `wero`.'],
            ['name' => 'Stripe-Signature', 'in' => 'header', 'description' => 'Von Stripe gesetzt.'],
            ['name' => 'Paypal-Transmission-Sig', 'in' => 'header', 'description' => 'Von PayPal '
                . 'gesetzt, zusammen mit `-Id`, `-Time`, `Paypal-Cert-Url` und `Paypal-Auth-Algo`.'],
        ],
        'responses' => [
            ['status' => 200, 'description' => '`{received: true}`'],
            ['status' => 400, 'description' => 'Signatur ungueltig — ohne Begruendung.'],
            ['status' => 404, 'description' => 'Unbekannter Anbieter.'],
            ['status' => 502, 'description' => 'Folgeaufruf beim Anbieter fehlgeschlagen (Capture).'],
            ['status' => 503, 'description' => 'Kein Webhook-Secret konfiguriert — faellt bewusst zu.'],
        ],
    ],
    [
        'method' => 'POST',
        'pattern' => '/shop/stripe/webhook',
        'summary' => 'Stripe-Webhook (Altpfad, identisches Verhalten)',
        'description' => 'Dieselbe Behandlung wie `/shop/payment/stripe/webhook`. Bleibt '
            . 'bestehen, weil diese Adresse im Stripe-Dashboard hinterlegt ist und dort '
            . 'Live-Ereignisse empfaengt: eine Route umzubenennen, die ein Dritter aufruft, ist '
            . 'ein Weg, Zahlungen still zu verlieren — Stripe wiederholt drei Tage lang gegen '
            . 'einen 404 und gibt dann auf. Faellt weg, sobald das Dashboard umgestellt und die '
            . 'Protokolle ruhig sind.',
        'auth' => 'token',
        'tag' => 'Kauf',
        'params' => [
            ['name' => 'Stripe-Signature', 'in' => 'header', 'description' => 'Von Stripe gesetzt.'],
        ],
        'responses' => [
            ['status' => 200, 'description' => '`{received: true}`'],
            ['status' => 400, 'description' => 'Signatur ungueltig.'],
            ['status' => 503, 'description' => 'Kein Webhook-Secret konfiguriert.'],
        ],
    ],
    [
        'method' => 'GET',
        'pattern' => '/shop/order/{token:[a-f0-9]{32}}',
        'summary' => 'Eigene Bestellung ansehen',
        'description' => 'Das Token IST die Berechtigung — Gastkauf ohne Konto. Stripe-IDs und '
            . 'interne Notizen werden aus der Antwort entfernt.',
        'auth' => 'public',
        'tag' => 'Kauf',
        'params' => [['name' => 'token', 'in' => 'path', 'description' => '32 Hex-Zeichen.']],
        'responses' => [
            ['status' => 200, 'description' => 'Bestellung inkl. Positionen.'],
            ['status' => 404, 'description' => 'Unbekanntes Token.'],
        ],
    ],
    [
        'method' => 'GET',
        'pattern' => '/shop/orders',
        'summary' => 'Bestellungen im Panel',
        'auth' => 'permission',
        'permission' => 'shop:orders',
        'tag' => 'Kauf',
        'responses' => [['status' => 200, 'description' => '`{orders: [...]}`']],
    ],
    [
        'method' => 'POST',
        'pattern' => '/shop/orders/{id:[0-9]+}/fulfil',
        'summary' => 'Bestellung als erbracht markieren',
        'description' => 'Diese Produkte sind LEISTUNGEN, keine Downloads — es gibt keine Datei '
            . 'auszuliefern. Erbracht wird von Hand, und dieser Aufruf haelt fest, wann.',
        'auth' => 'permission',
        'permission' => 'shop:orders',
        'tag' => 'Kauf',
        'params' => [
            ['name' => 'id', 'in' => 'path', 'description' => 'Bestell-ID.'],
            ['name' => 'note', 'in' => 'body', 'description' => 'Interne Notiz, optional.'],
        ],
        'responses' => [
            ['status' => 200, 'description' => '`{ok: true}`'],
            ['status' => 409, 'description' => 'Nicht bezahlt oder unbekannt.'],
        ],
    ],
    [
        'method' => 'POST',
        'pattern' => '/shop/orders/{id:[0-9]+}/invoice',
        'summary' => 'Rechnung ueber Lexware Office erstellen',
        'description' => 'Lexware ist das fuehrende System: es vergibt die Rechnungsnummer, '
            . 'rendert das PDF und fuehrt das Archiv — hier wird nur festgehalten, was es '
            . 'entschieden hat. Der Zahlungs-Webhook ruft dasselbe automatisch auf, sobald eine '
            . 'Bestellung bezahlt ist; dieser Endpunkt existiert, weil der automatische Weg '
            . 'ausfallen kann. Mehrfachaufrufe sind unschaedlich: eine bereits fakturierte '
            . 'Bestellung liefert ihre vorhandene Rechnung zurueck statt eine zweite zu erzeugen.',
        'auth' => 'permission',
        'permission' => 'shop:orders',
        'tag' => 'Kauf',
        'params' => [
            ['name' => 'id', 'in' => 'path', 'description' => 'Bestell-ID.'],
        ],
        'responses' => [
            ['status' => 200, 'description' => '`{ok: true, invoiceNumber, lexwareId}`'],
            ['status' => 409, 'description' => 'Nicht bezahlt, oder zu viele Fehlversuche.'],
            ['status' => 503, 'description' => 'Lexware ist auf diesem Host nicht eingerichtet.'],
        ],
    ],

    /* --- Angebotsabgleich -------------------------------------------------- */
    [
        'method' => 'GET',
        'pattern' => '/shop/sync/status',
        'summary' => 'Zustand des Amazon-Abgleichs',
        'description' => '`revoked: true` heisst, Amazon hat den API-Zugang entzogen — das '
            . 'passiert, wenn qualifizierte Verkaeufe ausbleiben, und ist ein Zustand, den ein '
            . 'Mensch aufloesen muss, kein Fehler zum Wiederholen. Der Katalog laeuft weiter: '
            . 'die Affiliate-Links sind nicht die API, nur die Preise verschwinden binnen eines '
            . 'Tages von selbst.',
        'auth' => 'permission',
        'permission' => 'shop:sync',
        'tag' => 'Abgleich',
        'responses' => [['status' => 200, 'description' => '`{queue, revoked, lastRun, configured}`']],
    ],
    [
        'method' => 'POST',
        'pattern' => '/shop/sync/enqueue',
        'summary' => 'Abgleich jetzt anstossen',
        'description' => 'Setzt einen gestoppten Abgleich fort, stellt veraltete Angebote in die '
            . 'Warteschlange und laeuft sofort einen Takt. Bewusst EIN Knopf: wer gerade sein '
            . 'Amazon-Konto in Ordnung gebracht hat, will beides — zwei Knoepfe laden dazu ein, '
            . 'nur den ersten zu druecken und den Abgleich fuer kaputt zu halten.',
        'auth' => 'permission',
        'permission' => 'shop:sync',
        'tag' => 'Abgleich',
        'responses' => [['status' => 200, 'description' => '`{resumed, queued, ok, failed, calls, stopped}`']],
    ],
    [
        'method' => 'POST',
        'pattern' => '/shop/sync/tick',
        'summary' => 'Externer Anstoss (tokengeschuetzt)',
        'description' => 'Fuer einen beliebigen externen Zeitgeber — Uptime-Monitor, '
            . 'GitHub-Actions-`schedule`, Plesk-Aufgabe falls vorhanden. Ausdruecklich KEINE '
            . 'Betriebsvoraussetzung: der anfragegetriebene Takt haelt den Katalog aktuell, dies '
            . 'beschleunigt ihn nur. Ohne `SHOP_SYNC_TOKEN` antwortet die Route 503, statt '
            . 'unauthentifiziert zu laufen.',
        'auth' => 'token',
        'tag' => 'Abgleich',
        'params' => [
            ['name' => 'X-TDS-Sync-Token', 'in' => 'header', 'description' => 'Muss `SHOP_SYNC_TOKEN` entsprechen.'],
        ],
        'responses' => [
            ['status' => 200, 'description' => '`{ok, failed, calls, stopped}`'],
            ['status' => 401, 'description' => 'Token fehlt oder stimmt nicht.'],
            ['status' => 503, 'description' => 'Auf dem Host ist kein Token gesetzt.'],
        ],
    ],
    [
        'method' => 'POST',
        'pattern' => '/shop/affiliate/lookup',
        'summary' => 'Amazon-Produkt nachschlagen (ASIN oder Suchbegriff)',
        'description' => 'Fuer den Import im Panel. Fehler werden hier NICHT geschluckt — der '
            . 'Betreiber steht davor, und ein entzogener Zugang liest sich voellig anders als '
            . 'ein Tippfehler in einer ASIN.',
        'auth' => 'permission',
        'permission' => 'shop:write',
        'tag' => 'Abgleich',
        'params' => [
            ['name' => 'asin', 'in' => 'body', 'description' => 'Eine ASIN. Schlaegt `keywords`.'],
            ['name' => 'keywords', 'in' => 'body', 'description' => 'Alternativ: Suchbegriff.'],
        ],
        'responses' => [
            ['status' => 200, 'description' => '`{items: [...]}`'],
            ['status' => 422, 'description' => 'Weder ASIN noch Suchbegriff.'],
            ['status' => 502, 'description' => 'Dauerhafter Fehler — Zugangsdaten oder entzogener Zugang.'],
            ['status' => 503, 'description' => 'Amazon nicht konfiguriert oder voruebergehend nicht erreichbar.'],
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
