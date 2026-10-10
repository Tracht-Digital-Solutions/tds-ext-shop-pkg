<?php
declare(strict_types=1);

/**
 * Own products, category `seo-sichtbarkeit`. Rate: Webauftritt, 65 € net per hour.
 */
$image = 'https://tracht-digital.de/images/services/04-webauftritt-800.webp';
$rate = 6500;

return [
    [
        'category' => 'seo-sichtbarkeit', 'tags' => 'seo,keywords', 'hours' => 5, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Keyword-Plan mit Suchbegriffen und Seiten',
        'prompt' => 'a magnifying glass over a mind map of search terms connected to web page icons',
        'de' => [
            'slug' => 'keyword-plan-fuer-ihre-leistungen',
            'title' => 'Keyword-Plan für Ihre Leistungen',
            'teaser' => 'Welche Begriffe Ihre Kunden wirklich suchen und welche Seite für welchen Begriff zuständig ist.',
            'meta' => 'Keyword-Plan zum Festpreis: echte Suchbegriffe Ihrer Kunden, Suchvolumen, Zuordnung zu Ihren Seiten und eine Liste fehlender Inhalte.',
            'summary' => 'Der Keyword-Plan zeigt, mit welchen Begriffen Kunden nach Ihren Leistungen suchen. Jeder Begriff bekommt eine zuständige Seite, und Sie sehen, welche Inhalte noch fehlen.',
            'facts' => [
                ['label' => 'Umfang', 'value' => 'bis zu 5 Leistungen'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche'],
                ['label' => 'Aufwand', 'value' => '5 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Tabelle und Kurzbericht'],
            ],
            'faq' => [
                ['q' => 'Was ist ein Keyword?', 'a' => 'Ein Suchbegriff, den Menschen bei Google eingeben, etwa „Elektriker Hannover Notdienst“.'],
                ['q' => 'Woher kommen die Suchzahlen?', 'a' => 'Aus gängigen SEO-Werkzeugen und dem Google Keyword Planner. Sie sind Schätzungen, aber gute Wegweiser.'],
                ['q' => 'Schreiben Sie auch die Texte?', 'a' => 'Nein. Der Plan sagt, was auf welche Seite gehört. Die Texte schreiben Sie oder ein Texter.'],
            ],
            'body' => <<<'MD'
                Gute Texte helfen nur, wenn sie die Begriffe treffen, nach denen Kunden suchen.

                ## Das ist enthalten

                - Recherche der Suchbegriffe zu bis zu fünf Leistungen
                - Suchvolumen und Wettbewerb je Begriff
                - Zuordnung: welcher Begriff gehört auf welche Seite
                - Liste fehlender Seiten und Inhalte

                ## So läuft es ab

                1. Sie nennen Ihre Leistungen und Ihr Einzugsgebiet.
                2. Ich recherchiere und ordne die Begriffe zu.
                3. Sie bekommen Tabelle und Kurzbericht, gern mit kurzem Termin.

                ## Nicht enthalten

                - Texte schreiben
                - Umsetzung auf der Website

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'keyword-plan-for-your-services',
            'title' => 'Keyword plan for your services',
            'teaser' => 'Which terms your customers really search for, and which page is responsible for which term.',
            'meta' => 'Keyword plan at a fixed price: the real search terms of your customers, search volume, mapping to your pages and a list of missing content.',
            'summary' => 'The keyword plan shows which terms customers use to search for your services. Each term gets a responsible page, and you see which content is still missing.',
            'facts' => [
                ['label' => 'Scope', 'value' => 'up to 5 services'],
                ['label' => 'Duration', 'value' => 'about 1 week'],
                ['label' => 'Effort', 'value' => '5 hours'],
                ['label' => 'Result', 'value' => 'Spreadsheet and short report'],
            ],
            'faq' => [
                ['q' => 'What is a keyword?', 'a' => 'A search term people type into Google, such as "electrician Hanover emergency".'],
                ['q' => 'Where do the search numbers come from?', 'a' => 'From common SEO tools and Google Keyword Planner. They are estimates, but good signposts.'],
                ['q' => 'Do you write the copy?', 'a' => 'No. The plan says what belongs on which page. You or a copywriter write the copy.'],
            ],
            'body' => <<<'MD'
                Good copy only helps if it matches the terms customers search for.

                ## What is included

                - Research of search terms for up to five services
                - Search volume and competition per term
                - Mapping: which term belongs on which page
                - List of missing pages and content

                ## How it works

                1. You name your services and your area.
                2. I research and map the terms.
                3. You receive the spreadsheet and report, with a short call if you like.

                ## Not included

                - Writing copy
                - Implementation on the website

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'seo-sichtbarkeit', 'tags' => 'local-seo,verzeichnisse', 'hours' => 4, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Einträge eines Betriebs in Branchenverzeichnissen',
        'prompt' => 'a shop front with map pins and several directory listing cards fanning out from it',
        'de' => [
            'slug' => 'eintraege-in-branchenverzeichnissen',
            'title' => 'Einträge in Branchenverzeichnissen',
            'teaser' => 'Ihr Betrieb mit einheitlichen Daten in den wichtigsten Verzeichnissen, damit Google Ihnen vertraut.',
            'meta' => 'Einträge in Branchenverzeichnissen: Ihr Betrieb mit einheitlichem Namen, Adresse und Telefon in bis zu zehn wichtigen Verzeichnissen. Für lokale Sichtbarkeit.',
            'summary' => 'Ich lege Ihren Betrieb in bis zu zehn wichtigen Verzeichnissen an oder korrigiere bestehende Einträge. Einheitliche Daten stärken Ihre Sichtbarkeit in der lokalen Suche.',
            'facts' => [
                ['label' => 'Umfang', 'value' => 'bis zu 10 Verzeichnisse'],
                ['label' => 'Dauer', 'value' => '1–2 Wochen'],
                ['label' => 'Aufwand', 'value' => '4 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Liste aller Einträge mit Zugängen'],
            ],
            'faq' => [
                ['q' => 'Welche Verzeichnisse sind dabei?', 'a' => 'Zum Beispiel Apple Maps, Bing Places, Gelbe Seiten, Das Örtliche und passende Branchenportale.'],
                ['q' => 'Warum sind einheitliche Daten wichtig?', 'a' => 'Google gleicht Name, Adresse und Telefon ab. Widersprüche kosten Vertrauen und Platzierungen.'],
                ['q' => 'Ist das Google-Profil dabei?', 'a' => 'Nein, dafür gibt es das eigene Paket „Google-Unternehmensprofil einrichten“.'],
            ],
            'body' => <<<'MD'
                Wer lokal gefunden werden will, braucht überall dieselben Daten.

                ## Das ist enthalten

                - Anlage oder Korrektur in bis zu zehn Verzeichnissen
                - Einheitlicher Name, Adresse, Telefon und Website
                - Liste aller Einträge mit Zugangsdaten für Sie

                ## So läuft es ab

                1. Sie bestätigen die genauen Firmendaten.
                2. Ich lege die Einträge an oder korrigiere sie.
                3. Sie bestätigen Prüfcodes, die per Post oder Telefon kommen.

                ## Nicht enthalten

                - Kostenpflichtige Premium-Einträge
                - Google-Unternehmensprofil (eigenes Paket)

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'business-directory-listings',
            'title' => 'Business directory listings',
            'teaser' => 'Your business with consistent details in the key directories, so Google trusts you.',
            'meta' => 'Business directory listings: your business with consistent name, address and phone in up to ten key directories. For local visibility.',
            'summary' => 'I list your business in up to ten key directories or correct existing entries. Consistent details strengthen your visibility in local search.',
            'facts' => [
                ['label' => 'Scope', 'value' => 'up to 10 directories'],
                ['label' => 'Duration', 'value' => '1–2 weeks'],
                ['label' => 'Effort', 'value' => '4 hours'],
                ['label' => 'Result', 'value' => 'List of all entries with logins'],
            ],
            'faq' => [
                ['q' => 'Which directories are included?', 'a' => 'For example Apple Maps, Bing Places, Gelbe Seiten, Das Örtliche and suitable trade portals.'],
                ['q' => 'Why do consistent details matter?', 'a' => 'Google compares name, address and phone. Contradictions cost trust and rankings.'],
                ['q' => 'Is the Google profile included?', 'a' => 'No, there is a separate package, "Google Business Profile setup".'],
            ],
            'body' => <<<'MD'
                To be found locally, you need the same details everywhere.

                ## What is included

                - Creating or correcting up to ten directory entries
                - Consistent name, address, phone and website
                - List of all entries with logins for you

                ## How it works

                1. You confirm your exact business details.
                2. I create or correct the entries.
                3. You confirm verification codes sent by post or phone.

                ## Not included

                - Paid premium listings
                - Google Business Profile (separate package)

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'seo-sichtbarkeit', 'tags' => 'geo,ki-suche,llms', 'hours' => 6, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Website als Quelle in einer KI-Antwort',
        'prompt' => 'a chat bubble from an AI assistant quoting a small website card as its source, soft glowing link line',
        'de' => [
            'slug' => 'ki-sichtbarkeit-geo-verbessern',
            'title' => 'KI-Sichtbarkeit (GEO) verbessern',
            'meta_title' => 'KI-Sichtbarkeit verbessern: GEO für Ihre Website',
            'teaser' => 'Damit ChatGPT, Perplexity und Google-KI Ihre Website verstehen und als Quelle nennen.',
            'meta' => 'GEO zum Festpreis: Ihre Website für KI-Antworten aufbereiten. Prüfung, strukturierte Daten, llms.txt und klare Antwortabsätze für ChatGPT und Co.',
            'summary' => 'Ich prüfe, ob KI-Assistenten Ihre Website lesen und verstehen können, und behebe die wichtigsten Hürden. Dazu gehören strukturierte Daten, eine llms.txt und klare Antworten auf typische Kundenfragen.',
            'facts' => [
                ['label' => 'Umfang', 'value' => 'bis zu 10 Seiten'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche'],
                ['label' => 'Aufwand', 'value' => '6 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Umgesetzte Maßnahmen und Bericht'],
            ],
            'faq' => [
                ['q' => 'Was ist der Unterschied zu SEO?', 'a' => 'SEO zielt auf Trefferlisten, GEO auf Antworten von KI-Assistenten. Vieles überschneidet sich, einiges kommt neu hinzu.'],
                ['q' => 'Was ist eine llms.txt?', 'a' => 'Eine kurze Übersicht Ihrer Website für Sprachmodelle, ähnlich einer Sitemap für Menschen.'],
                ['q' => 'Werde ich danach sicher in ChatGPT genannt?', 'a' => 'Das kann niemand garantieren. Die Maßnahmen machen es deutlich wahrscheinlicher.'],
            ],
            'body' => <<<'MD'
                Immer mehr Menschen fragen eine KI statt einer Suchmaschine. Ihre Website sollte darauf vorbereitet sein.

                ## Das ist enthalten

                - Prüfung: Dürfen und können KI-Crawler Ihre Seiten lesen?
                - Strukturierte Daten für Betrieb, Leistungen und Fragen
                - llms.txt mit einer Übersicht Ihrer Website
                - Kurze Antwortabsätze für bis zu zehn Seiten

                ## So läuft es ab

                1. Ich prüfe Ihre Website und frage typische Kundenfragen bei KI-Assistenten ab.
                2. Ich setze die Maßnahmen um.
                3. Sie bekommen einen Bericht mit Vorher und Nachher.

                ## Nicht enthalten

                - Neue Inhalte oder Seiten
                - Laufende Beobachtung

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'improve-ai-visibility-geo',
            'title' => 'Improve AI visibility (GEO)',
            'meta_title' => 'Improve AI visibility: GEO for your website',
            'teaser' => 'So ChatGPT, Perplexity and Google AI understand your website and cite it as a source.',
            'meta' => 'GEO at a fixed price: prepare your website for AI answers. Review, structured data, llms.txt and clear answer paragraphs for ChatGPT and others.',
            'summary' => 'I check whether AI assistants can read and understand your website and remove the main obstacles. This includes structured data, an llms.txt and clear answers to typical customer questions.',
            'facts' => [
                ['label' => 'Scope', 'value' => 'up to 10 pages'],
                ['label' => 'Duration', 'value' => 'about 1 week'],
                ['label' => 'Effort', 'value' => '6 hours'],
                ['label' => 'Result', 'value' => 'Measures in place and a report'],
            ],
            'faq' => [
                ['q' => 'How is it different from SEO?', 'a' => 'SEO targets result lists, GEO targets answers from AI assistants. Much overlaps, some is new.'],
                ['q' => 'What is an llms.txt?', 'a' => 'A short overview of your website for language models, like a sitemap written for people.'],
                ['q' => 'Will ChatGPT definitely mention me?', 'a' => 'Nobody can guarantee that. These measures make it much more likely.'],
            ],
            'body' => <<<'MD'
                More and more people ask an AI instead of a search engine. Your website should be ready for that.

                ## What is included

                - Review: may and can AI crawlers read your pages?
                - Structured data for business, services and questions
                - llms.txt with an overview of your website
                - Short answer paragraphs for up to ten pages

                ## How it works

                1. I review your website and ask AI assistants typical customer questions.
                2. I put the measures in place.
                3. You receive a report with before and after.

                ## Not included

                - New content or pages
                - Ongoing monitoring

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'seo-sichtbarkeit', 'tags' => 'technisches-seo,sitemap', 'hours' => 6, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Technische SEO-Prüfung einer Website',
        'prompt' => 'a website blueprint with gears, a sitemap tree and green check marks on technical items',
        'de' => [
            'slug' => 'technische-seo-grundlagen',
            'title' => 'Technische SEO-Grundlagen umsetzen',
            'teaser' => 'Die technischen Basics, ohne die gute Inhalte bei Google nicht ankommen.',
            'meta' => 'Technische SEO zum Festpreis: Seitentitel, Beschreibungen, Sitemap, robots.txt, Weiterleitungen und kaputte Links. Damit Google Ihre Seite richtig liest.',
            'summary' => 'Ich behebe die technischen Fehler, die Google am Lesen Ihrer Website hindern. Dazu gehören Titel, Beschreibungen, Sitemap, robots.txt, Weiterleitungen und kaputte Links.',
            'facts' => [
                ['label' => 'Umfang', 'value' => 'bis zu 20 Seiten'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche'],
                ['label' => 'Aufwand', 'value' => '6 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Behobene Fehler und Protokoll'],
            ],
            'faq' => [
                ['q' => 'Woran merke ich technische SEO-Probleme?', 'a' => 'Oft gar nicht. Seiten fehlen dann einfach bei Google. Die Search Console zeigt erste Hinweise.'],
                ['q' => 'Ändern Sie meine Texte?', 'a' => 'Nur Seitentitel und Beschreibungen. Sichtbare Texte bleiben unverändert.'],
                ['q' => 'Für welche Systeme?', 'a' => 'WordPress und gängige Baukästen. Bei anderen Systemen bitte vorher anfragen.'],
            ],
            'body' => <<<'MD'
                Die beste Seite nützt nichts, wenn Google sie nicht richtig lesen kann.

                ## Das ist enthalten

                - Seitentitel und Beschreibungen für bis zu 20 Seiten
                - XML-Sitemap und robots.txt prüfen und anlegen
                - Kaputte Links und fehlende Weiterleitungen beheben
                - Doppelte Inhalte über Canonical-Angaben bereinigen

                ## So läuft es ab

                1. Ich prüfe Ihre Website mit einem Crawler und der Search Console.
                2. Ich behebe die gefundenen Fehler.
                3. Sie bekommen ein Protokoll aller Änderungen.

                ## Nicht enthalten

                - Neue Texte oder Seiten
                - Ladezeit-Optimierung (eigenes Paket)

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'technical-seo-basics',
            'title' => 'Technical SEO basics',
            'teaser' => 'The technical basics without which good content never reaches Google.',
            'meta' => 'Technical SEO at a fixed price: page titles, descriptions, sitemap, robots.txt, redirects and broken links, so Google reads your site correctly.',
            'summary' => 'I fix the technical errors that keep Google from reading your website. This covers titles, descriptions, sitemap, robots.txt, redirects and broken links.',
            'facts' => [
                ['label' => 'Scope', 'value' => 'up to 20 pages'],
                ['label' => 'Duration', 'value' => 'about 1 week'],
                ['label' => 'Effort', 'value' => '6 hours'],
                ['label' => 'Result', 'value' => 'Fixed errors and a change log'],
            ],
            'faq' => [
                ['q' => 'How do I notice technical SEO problems?', 'a' => 'Often you don\'t. Pages are simply missing from Google. Search Console gives first hints.'],
                ['q' => 'Do you change my copy?', 'a' => 'Only page titles and descriptions. Visible copy stays unchanged.'],
                ['q' => 'Which systems?', 'a' => 'WordPress and common website builders. For other systems, please ask first.'],
            ],
            'body' => <<<'MD'
                The best page is useless if Google cannot read it properly.

                ## What is included

                - Page titles and descriptions for up to 20 pages
                - Checking and creating the XML sitemap and robots.txt
                - Fixing broken links and missing redirects
                - Cleaning up duplicate content with canonical tags

                ## How it works

                1. I scan your website with a crawler and Search Console.
                2. I fix the errors found.
                3. You receive a log of all changes.

                ## Not included

                - New copy or pages
                - Speed optimisation (separate package)

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'seo-sichtbarkeit', 'tags' => 'schema,strukturierte-daten', 'hours' => 4, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Strukturierte Daten im Code einer Website',
        'prompt' => 'code brackets wrapping neat labelled data blocks that turn into rich search result cards with stars and FAQs',
        'de' => [
            'slug' => 'strukturierte-daten-einbauen',
            'title' => 'Strukturierte Daten (Schema.org) einbauen',
            'meta_title' => 'Strukturierte Daten einbauen (Schema.org)',
            'teaser' => 'Maschinenlesbare Angaben zu Betrieb, Leistungen und Fragen, für bessere Suchergebnisse.',
            'meta' => 'Strukturierte Daten nach Schema.org: Betrieb, Leistungen, Öffnungszeiten und FAQ maschinenlesbar auszeichnen. Für Google und KI-Assistenten, geprüft.',
            'summary' => 'Ich zeichne Ihren Betrieb, Ihre Leistungen und häufige Fragen mit strukturierten Daten aus. Suchmaschinen und KI-Assistenten verstehen Ihre Seite dann besser und zeigen reichere Ergebnisse.',
            'facts' => [
                ['label' => 'Umfang', 'value' => 'bis zu 4 Datentypen'],
                ['label' => 'Dauer', 'value' => '3–5 Werktage'],
                ['label' => 'Aufwand', 'value' => '4 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Geprüfte Auszeichnung'],
            ],
            'faq' => [
                ['q' => 'Was sind strukturierte Daten?', 'a' => 'Unsichtbare Angaben im Code, die Maschinen sagen, was auf der Seite steht: Adresse, Öffnungszeiten, Preise, Fragen.'],
                ['q' => 'Bekomme ich dann Sterne bei Google?', 'a' => 'Das entscheidet Google. Strukturierte Daten sind die Voraussetzung, keine Garantie.'],
                ['q' => 'Wie wird das geprüft?', 'a' => 'Mit dem Test für Rich-Suchergebnisse von Google und dem Schema-Validator.'],
            ],
            'body' => <<<'MD'
                Strukturierte Daten übersetzen Ihre Website in eine Sprache, die Suchmaschinen sicher verstehen.

                ## Das ist enthalten

                - Auszeichnung für Betrieb, Leistungen, FAQ und Brotkrumen
                - Einbau auf den passenden Seiten
                - Prüfung mit den Werkzeugen von Google und Schema.org

                ## So läuft es ab

                1. Ich prüfe, welche Auszeichnungen zu Ihrer Seite passen.
                2. Ich baue sie ein.
                3. Sie bekommen die Prüfergebnisse.

                ## Nicht enthalten

                - Bewertungs-Sterne aus fremden Portalen
                - Neue Inhalte

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'add-structured-data',
            'title' => 'Add structured data (Schema.org)',
            'teaser' => 'Machine-readable details about your business, services and questions, for richer search results.',
            'meta' => 'Structured data with Schema.org: mark up business, services, opening hours and FAQ in machine-readable form. For Google and AI assistants, validated.',
            'summary' => 'I mark up your business, services and frequent questions with structured data. Search engines and AI assistants then understand your site better and show richer results.',
            'facts' => [
                ['label' => 'Scope', 'value' => 'up to 4 data types'],
                ['label' => 'Duration', 'value' => '3–5 working days'],
                ['label' => 'Effort', 'value' => '4 hours'],
                ['label' => 'Result', 'value' => 'Validated markup'],
            ],
            'faq' => [
                ['q' => 'What is structured data?', 'a' => 'Invisible details in the code that tell machines what the page says: address, opening hours, prices, questions.'],
                ['q' => 'Will I get stars on Google?', 'a' => 'Google decides. Structured data is the prerequisite, not a guarantee.'],
                ['q' => 'How is it checked?', 'a' => 'With Google\'s Rich Results Test and the Schema.org validator.'],
            ],
            'body' => <<<'MD'
                Structured data translates your website into a language search engines understand reliably.

                ## What is included

                - Markup for business, services, FAQ and breadcrumbs
                - Added to the right pages
                - Validation with Google and Schema.org tools

                ## How it works

                1. I check which markup suits your site.
                2. I add it.
                3. You receive the validation results.

                ## Not included

                - Review stars from third-party portals
                - New content

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'seo-sichtbarkeit', 'tags' => 'ladezeit,core-web-vitals', 'hours' => 6, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Website mit schneller Ladezeit',
        'prompt' => 'a speedometer needle in the green zone next to a web page loading instantly, motion lines',
        'de' => [
            'slug' => 'ladezeit-optimieren',
            'title' => 'Ladezeit optimieren',
            'teaser' => 'Eine schnellere Website: bessere Werte bei Google und weniger Absprünge auf dem Handy.',
            'meta' => 'Ladezeit-Optimierung zum Festpreis: Bilder, Caching und Skripte verbessern, Core Web Vitals messen. Schnellere Website, bessere Werte bei Google.',
            'summary' => 'Ich mache Ihre Website messbar schneller, vor allem auf dem Handy. Bilder, Caching und Skripte werden optimiert, und Sie sehen die Werte vorher und nachher.',
            'facts' => [
                ['label' => 'Umfang', 'value' => 'Startseite und 4 wichtige Seiten'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche'],
                ['label' => 'Aufwand', 'value' => '6 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Messwerte vorher und nachher'],
            ],
            'faq' => [
                ['q' => 'Was sind Core Web Vitals?', 'a' => 'Messwerte von Google für Ladezeit, Reaktion und stabiles Layout. Sie fließen ins Ranking ein.'],
                ['q' => 'Wie viel schneller wird es?', 'a' => 'Das hängt vom Ausgangszustand ab. Ich nenne nach der ersten Messung eine realistische Erwartung.'],
                ['q' => 'Brauche ich ein neues Hosting?', 'a' => 'Manchmal ist das Hosting der Engpass. Dann sage ich es Ihnen mit Begründung.'],
            ],
            'body' => <<<'MD'
                Jede Sekunde Ladezeit kostet Besucher. Dieses Paket holt die Zeit zurück.

                ## Das ist enthalten

                - Messung der Startseite und vier wichtiger Seiten
                - Bilder komprimieren und in moderne Formate umwandeln
                - Caching einrichten, unnötige Skripte entfernen
                - Messung nach der Optimierung

                ## So läuft es ab

                1. Ich messe den Ist-Zustand.
                2. Ich optimiere mit Sicherung vor jedem Schritt.
                3. Sie bekommen die Werte vorher und nachher.

                ## Nicht enthalten

                - Umzug zu einem neuen Hoster (eigenes Paket)
                - Neubau von Seiten

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'website-speed-optimisation',
            'title' => 'Website speed optimisation',
            'teaser' => 'A faster website: better scores on Google and fewer visitors leaving on mobile.',
            'meta' => 'Website speed optimisation at a fixed price: improve images, caching and scripts, measure Core Web Vitals. A faster site and better Google scores.',
            'summary' => 'I make your website measurably faster, especially on phones. Images, caching and scripts are optimised, and you see the scores before and after.',
            'facts' => [
                ['label' => 'Scope', 'value' => 'Home page and 4 key pages'],
                ['label' => 'Duration', 'value' => 'about 1 week'],
                ['label' => 'Effort', 'value' => '6 hours'],
                ['label' => 'Result', 'value' => 'Scores before and after'],
            ],
            'faq' => [
                ['q' => 'What are Core Web Vitals?', 'a' => 'Google\'s metrics for loading, responsiveness and layout stability. They count towards ranking.'],
                ['q' => 'How much faster will it be?', 'a' => 'That depends on the starting point. After the first measurement I give you a realistic expectation.'],
                ['q' => 'Do I need new hosting?', 'a' => 'Sometimes hosting is the bottleneck. Then I tell you, with reasons.'],
            ],
            'body' => <<<'MD'
                Every second of loading time costs visitors. This package wins the time back.

                ## What is included

                - Measuring the home page and four key pages
                - Compressing images and converting them to modern formats
                - Setting up caching, removing unneeded scripts
                - Measuring again after optimisation

                ## How it works

                1. I measure the current state.
                2. I optimise, with a backup before each step.
                3. You receive the scores before and after.

                ## Not included

                - Moving to a new host (separate package)
                - Rebuilding pages

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'seo-sichtbarkeit', 'tags' => 'search-console,google', 'hours' => 2, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Google Search Console mit Diagramm',
        'prompt' => 'a dashboard with a rising line chart and a search bar, a key handing over access',
        'de' => [
            'slug' => 'google-search-console-einrichten',
            'title' => 'Google Search Console einrichten',
            'teaser' => 'Sehen, wie Google Ihre Website findet: Suchbegriffe, Klicks und Fehler auf einen Blick.',
            'meta' => 'Google Search Console einrichten: Website bestätigen, Sitemap einreichen und Bericht erklären. So sehen Sie Suchbegriffe, Klicks und Fehler Ihrer Seite.',
            'summary' => 'Ich richte die Google Search Console für Ihre Website ein und reiche die Sitemap ein. In einer kurzen Einweisung zeige ich Ihnen die wichtigsten Berichte.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Website'],
                ['label' => 'Dauer', 'value' => '1–2 Werktage'],
                ['label' => 'Aufwand', 'value' => '2 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Eingerichtetes Konto mit Einweisung'],
            ],
            'faq' => [
                ['q' => 'Kostet die Search Console etwas?', 'a' => 'Nein, sie ist kostenlos. Sie brauchen nur ein Google-Konto.'],
                ['q' => 'Was sehe ich darin?', 'a' => 'Mit welchen Begriffen Sie gefunden werden, wie oft geklickt wird und welche Seiten Fehler haben.'],
                ['q' => 'Wem gehört der Zugang?', 'a' => 'Ihnen. Ich richte ihn auf Ihr Google-Konto ein.'],
            ],
            'body' => <<<'MD'
                Die Search Console ist der direkte Draht zu Google. Kostenlos und unverzichtbar.

                ## Das ist enthalten

                - Bestätigung Ihrer Website bei Google
                - Einreichen der Sitemap
                - Einweisung in die wichtigsten Berichte (20 Minuten per Video)

                ## So läuft es ab

                1. Sie geben mir Zugang zu Website oder Domain.
                2. Ich richte die Search Console ein.
                3. Wir gehen die Berichte gemeinsam durch.

                ## Nicht enthalten

                - Behebung gefundener Fehler (siehe „Technische SEO-Grundlagen“)

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'google-search-console-setup',
            'title' => 'Google Search Console setup',
            'teaser' => 'See how Google finds your website: search terms, clicks and errors at a glance.',
            'meta' => 'Google Search Console setup: verify your website, submit the sitemap and explain the reports. See the search terms, clicks and errors of your site.',
            'summary' => 'I set up Google Search Console for your website and submit the sitemap. In a short session I show you the most important reports.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 website'],
                ['label' => 'Duration', 'value' => '1–2 working days'],
                ['label' => 'Effort', 'value' => '2 hours'],
                ['label' => 'Result', 'value' => 'Account set up, with a walkthrough'],
            ],
            'faq' => [
                ['q' => 'Does Search Console cost anything?', 'a' => 'No, it is free. You only need a Google account.'],
                ['q' => 'What does it show?', 'a' => 'Which terms you are found for, how often people click and which pages have errors.'],
                ['q' => 'Who owns the access?', 'a' => 'You do. I set it up on your Google account.'],
            ],
            'body' => <<<'MD'
                Search Console is your direct line to Google. Free and essential.

                ## What is included

                - Verifying your website with Google
                - Submitting the sitemap
                - Walkthrough of the key reports (20 minutes by video)

                ## How it works

                1. You give me access to the website or domain.
                2. I set up Search Console.
                3. We go through the reports together.

                ## Not included

                - Fixing errors found (see "Technical SEO basics")

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
];
