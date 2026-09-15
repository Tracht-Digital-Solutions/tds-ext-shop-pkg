<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Six fixed-price service packages, created as DRAFTS.
 *
 * The catalogue was empty. These are real, sellable packages cut from the four
 * services of the landing page, each priced as that service's hourly rate times
 * a fixed scope — so no number in here is invented, and the scope is described
 * by what the customer receives, not by hours (the care package is the one
 * exception, because its hours ARE the product).
 *
 * ### Drafts, on purpose
 *
 * `status = 'draft'` and `published_at = NULL`: nothing appears in the shop, in
 * the journal's placements or on the portal dashboard until the operator
 * publishes a product in the panel, which stamps `published_at` on the first
 * publish. `editorial_status = 'published'` is already set because the text
 * below IS the product's own assessment, so a published package is indexed.
 *
 * ### What each package writes
 *
 * product → two translations (de, en) → one `own` offer → its sale terms in
 * `shop_own_product` → one remote cover. The sale terms are the single source of
 * the price; `shop_offer.price_cents` and `sort_price_cents` carry the derived
 * gross only so the sort helper behaves as it does for a product saved through
 * `ProductRepository::setOffers()`.
 *
 * - `body_format = 'markdown'`: the shop renders nothing for a `blocks` body.
 * - `machine_translated = 0`: both languages are written by hand.
 * - The cover is `source = 'remote'` on the landing page's own service photos.
 *   An `upload` cover resolves to a media route that does not exist.
 *
 * ### Idempotent, and careful on the way down
 *
 * A package is skipped when its German OR English slug is already taken, which
 * also keeps the unique (lang, slug) index from aborting the run. Writes go
 * through the connection's prepared statements only — never an adapter internal
 * such as `quoteValue()`, which stopped every module's migrations once.
 *
 * `down()` removes a product only while its German title and body are still
 * exactly as seeded (CASCADE takes translations, offer, terms and cover), and
 * the category only while its names are unchanged and no product uses it.
 */
final class ShopSeedServicePackages extends AbstractMigration
{
    public const CATEGORY = [
        'slug' => 'leistungspakete',
        'name_de' => 'Leistungspakete',
        'name_en' => 'Service packages',
    ];

    public const MERCHANT = 'Tracht Digital Solutions';

    public const PACKAGES = [
        [
            // Beratung & Konzeption, 75 € × 4 h.
            'tags' => 'beratung,digitalisierung',
            'net_cents' => 30000,
            'vat_rate_bp' => 1900,
            'fulfilment' => 'project',
            'image' => 'https://tracht-digital.de/images/services/01-beratung-800.webp',
            'alt' => 'Beratung & Konzeption',
            'de' => [
                'slug' => 'digital-check-fuer-ihren-betrieb',
                'title' => 'Digital-Check für Ihren Betrieb',
                'teaser' => 'Ich nehme Ihre Abläufe, Programme und Ideen auf und sage Ihnen schriftlich, was sich zuerst lohnt, was warten kann und mit welchen Kosten Sie rechnen sollten.',
                'meta' => 'Digital-Check für kleine Betriebe: Bestandsaufnahme Ihrer Abläufe und Programme, schriftliche Einschätzung und klare Reihenfolge der nächsten Schritte.',
                'body' => <<<'MD'
                    Viele Ideen, wenig Zeit, und niemand weiß so recht, womit man anfangen soll: Der Digital-Check ist für Selbstständige und kleine Betriebe, die vor einer Entscheidung erst einen klaren Blick von außen wollen.

                    ## Das ist enthalten

                    - Ein gemeinsamer Termin per Video oder nach Absprache vor Ort, in dem ich Ihre Abläufe, Programme und Vorhaben aufnehme
                    - Eine schriftliche Einschätzung, wo heute Zeit und Geld verloren gehen
                    - Eine Liste möglicher Maßnahmen mit Nutzen, Aufwand und grober Kostenschätzung
                    - Eine empfohlene Reihenfolge: was zuerst dran ist und was warten kann
                    - Ein Abschlussgespräch, in dem wir die Ergebnisse gemeinsam durchgehen

                    ## So läuft es ab

                    1. Nach Ihrer Bestellung melde ich mich innerhalb von zwei Werktagen, um einen Termin abzustimmen.
                    2. Vorab schicke ich Ihnen ein paar Fragen, damit der Termin gut vorbereitet ist.
                    3. Im Termin gehen wir Ihren Betrieb gemeinsam durch.
                    4. Innerhalb von zehn Werktagen nach dem Termin erhalten Sie die schriftliche Einschätzung, danach folgt das Abschlussgespräch.

                    ## Nicht enthalten

                    - Die Umsetzung der Maßnahmen. Sie ist eine eigene Leistung und wird getrennt vereinbart.
                    - Rechts-, Steuer- oder Datenschutzberatung. Der Check ersetzt keinen Anwalt, Steuerberater oder Prüfer.
                    - Reisekosten für einen Termin vor Ort, falls wir einen vereinbaren. Sie werden vorher mit Ihnen abgestimmt.

                    ## Gut zu wissen

                    Der Preis ist ein Festpreis zuzüglich 19 % Umsatzsteuer. Danach müssen Sie nichts weiter beauftragen. Brauchen Sie mehr als den Check, gilt der Stundensatz für Beratung & Konzeption, und zwar erst nach Ihrer Zustimmung.
                    MD,
            ],
            'en' => [
                'slug' => 'digital-check-for-your-business',
                'title' => 'Digital check for your business',
                'teaser' => 'I take stock of your workflows, programs and ideas and tell you in writing what pays off first, what can wait and which costs to expect.',
                'meta' => 'Digital check for small businesses: a review of your workflows and programs, a written assessment and a clear order for the next steps.',
                'body' => <<<'MD'
                    Plenty of ideas, little time, and nobody quite sure where to start: the digital check is for self-employed people and small businesses who want a clear outside view before they decide.

                    ## What is included

                    - One session by video, or on site by arrangement, in which I take stock of your workflows, programs and plans
                    - A written assessment of where time and money are lost today
                    - A list of possible measures with benefit, effort and a rough cost estimate
                    - A recommended order: what comes first and what can wait
                    - A closing conversation in which we go through the results together

                    ## How it works

                    1. After your order I get in touch within two working days to arrange a date.
                    2. Beforehand I send you a few questions so the session is well prepared.
                    3. In the session we go through your business together.
                    4. Within ten working days of the session you receive the written assessment, followed by the closing conversation.

                    ## Not included

                    - Carrying out the measures. That is a separate service and is agreed separately.
                    - Legal, tax or data protection advice. The check does not replace a lawyer, an accountant or an auditor.
                    - Travel costs for an on-site session, if we agree on one. They are settled with you beforehand.

                    ## Good to know

                    The price is a fixed price plus 19 % VAT. Nothing else has to be commissioned afterwards. If you need more than the check, the hourly rate for Consulting & Planning applies, and only once you have agreed to it.
                    MD,
            ],
        ],
        [
            // Webauftritt, 65 € × 4 h.
            'tags' => 'webauftritt',
            'net_cents' => 26000,
            'vat_rate_bp' => 1900,
            'fulfilment' => 'project',
            'image' => 'https://tracht-digital.de/images/services/04-webauftritt-800.webp',
            'alt' => 'Webauftritt',
            'de' => [
                'slug' => 'website-check-mit-massnahmenliste',
                'title' => 'Website-Check mit Maßnahmenliste',
                'teaser' => 'Ich prüfe Ihre bestehende Webseite auf Handy-Tauglichkeit, Ladezeit, Auffindbarkeit und Verständlichkeit und gebe Ihnen eine sortierte Liste, was sich wirklich lohnt.',
                'meta' => 'Website-Check für kleine Unternehmen: Handy, Ladezeit, Auffindbarkeit und Verständlichkeit geprüft, mit einer sortierten Liste konkreter Verbesserungen.',
                'body' => <<<'MD'
                    Die Webseite ist online, aber es kommen kaum Anfragen? Oder sie ist in die Jahre gekommen, und Sie wissen nicht, ob eine Überarbeitung reicht oder ein Neubau nötig ist? Der Website-Check gibt Ihnen eine ehrliche Antwort, bevor Sie Geld ausgeben.

                    ## Das ist enthalten

                    - Prüfung Ihrer Webseite mit den wichtigsten Unterseiten: Startseite, Angebot oder Leistungen, Kontakt
                    - Darstellung und Bedienung auf Handy, Tablet und Bildschirm
                    - Ladezeit und technische Grundlagen wie Verschlüsselung, Seitentitel und Beschreibungen
                    - Auffindbarkeit bei Google, einschließlich eines Blicks auf Ihr Unternehmensprofil
                    - Verständlichkeit: Erkennt ein Besucher sofort, was Sie anbieten und wie er Sie erreicht?
                    - Eine schriftliche Maßnahmenliste, sortiert nach Wirkung und Aufwand
                    - Ein Gespräch per Video, in dem wir die Liste gemeinsam durchgehen

                    ## So läuft es ab

                    1. Nach Ihrer Bestellung melde ich mich innerhalb von zwei Werktagen und frage nach der Adresse Ihrer Webseite und nach Ihren Zielen.
                    2. Ich prüfe die Seite, ohne dass Sie etwas freischalten oder installieren müssen.
                    3. Innerhalb von zehn Werktagen erhalten Sie die Maßnahmenliste.
                    4. Im Gespräch klären wir Ihre Fragen und was Sie selbst umsetzen können.

                    ## Nicht enthalten

                    - Die Umsetzung der Maßnahmen. Auf Wunsch mache ich Ihnen dafür ein eigenes Angebot.
                    - Eine Rechtsprüfung. Hinweise auf fehlende Pflichtangaben ersetzen keine Rechtsberatung.
                    - Die Prüfung von Bestellablauf, Zahlungsarten und Versandregeln eines Online-Shops. Dafür ist ein eigener Umfang nötig.

                    ## Gut zu wissen

                    Der Preis ist ein Festpreis zuzüglich 19 % Umsatzsteuer. Ob Ihre Seite mit einem Baukasten, mit WordPress oder mit etwas anderem gebaut ist, spielt für den Check keine Rolle.
                    MD,
            ],
            'en' => [
                'slug' => 'website-check-with-action-list',
                'title' => 'Website check with action list',
                'teaser' => 'I check your existing website for mobile use, loading speed, findability and clarity, and give you a sorted list of what is genuinely worth doing.',
                'meta' => 'Website check for small businesses: mobile use, speed, findability and clarity reviewed, with a sorted list of concrete improvements.',
                'body' => <<<'MD'
                    Your website is online, but hardly any enquiries come in? Or it is showing its age, and you do not know whether a refresh is enough or a rebuild is due? The website check gives you an honest answer before you spend money.

                    ## What is included

                    - A review of your website and its most important pages: home, offer or services, contact
                    - Layout and usability on phone, tablet and desktop
                    - Loading speed and technical basics such as encryption, page titles and descriptions
                    - Findability on Google, including a look at your business profile
                    - Clarity: does a visitor see straight away what you offer and how to reach you?
                    - A written action list, sorted by impact and effort
                    - A video call in which we go through the list together

                    ## How it works

                    1. After your order I get in touch within two working days and ask for your website address and your goals.
                    2. I review the site without you having to grant access or install anything.
                    3. Within ten working days you receive the action list.
                    4. In the call we answer your questions and sort out what you can do yourself.

                    ## Not included

                    - Carrying out the measures. On request I make you a separate offer for that.
                    - A legal review. Pointing out missing mandatory information does not replace legal advice.
                    - Reviewing the checkout, payment methods and shipping rules of an online shop. That needs a scope of its own.

                    ## Good to know

                    The price is a fixed price plus 19 % VAT. Whether your site was built with a website builder, with WordPress or with something else makes no difference to the check.
                    MD,
            ],
        ],
        [
            // Webauftritt, 65 € × 3 h.
            'tags' => 'webauftritt,google',
            'net_cents' => 19500,
            'vat_rate_bp' => 1900,
            'fulfilment' => 'project',
            'image' => 'https://tracht-digital.de/images/services/04-webauftritt-800.webp',
            'alt' => 'Webauftritt',
            'de' => [
                'slug' => 'google-unternehmensprofil-einrichten',
                'title' => 'Google-Unternehmensprofil einrichten',
                'teaser' => 'Ich richte Ihr Google-Unternehmensprofil ein oder bringe ein bestehendes in Ordnung, damit Ihr Betrieb in der Google-Suche und bei Maps mit den richtigen Angaben erscheint.',
                'meta' => 'Google-Unternehmensprofil einrichten lassen: Angaben, Kategorien, Öffnungszeiten, Leistungen und Fotos, damit Ihr Betrieb in Suche und Maps gefunden wird.',
                'body' => <<<'MD'
                    Wer einen Betrieb in der Nähe sucht, sieht zuerst die Einträge bei Google Maps. Fehlen dort die Öffnungszeiten, stimmt die Telefonnummer nicht oder gibt es gar keinen Eintrag, geht die Anfrage an den Nächsten. Dieses Paket bringt Ihr Profil in Ordnung.

                    ## Das ist enthalten

                    - Ein neues Profil anlegen oder ein vorhandenes übernehmen und prüfen
                    - Name, Adresse oder Einzugsgebiet, Telefonnummer, Webseite und Öffnungszeiten vollständig und einheitlich eintragen
                    - Passende Haupt- und Nebenkategorien auswählen
                    - Leistungen oder Produkte mit kurzer Beschreibung anlegen
                    - Ihre Fotos auswählen, zuschneiden und hochladen
                    - Eine kurze Anleitung, wie Sie Bewertungen beantworten und Beiträge veröffentlichen

                    ## So läuft es ab

                    1. Nach Ihrer Bestellung melde ich mich innerhalb von zwei Werktagen und schicke Ihnen eine Liste der Angaben und Fotos, die ich brauche.
                    2. Sie laden mich als Verwalter in Ihr Profil ein, oder wir legen es gemeinsam an. Das Profil gehört immer Ihnen.
                    3. Ich richte alles ein und gebe Ihnen Bescheid, sobald das Profil vollständig ist.
                    4. Verlangt Google eine Bestätigung Ihres Betriebs, begleite ich Sie durch diesen Schritt.

                    ## Nicht enthalten

                    - Google Ads. Das Mediabudget dafür zahlen Sie direkt an Google, es ist kein Teil dieses Pakets.
                    - Gekaufte oder selbst geschriebene Bewertungen. Sie verstoßen gegen die Richtlinien von Google.
                    - Fotografie vor Ort. Verwendet werden die Fotos, die Sie mir zur Verfügung stellen.

                    ## Gut zu wissen

                    Der Preis ist ein Festpreis zuzüglich 19 % Umsatzsteuer. Wie lange Google für eine Bestätigung braucht, liegt nicht in meiner Hand, und eine bestimmte Platzierung in der Suche kann niemand zusagen.
                    MD,
            ],
            'en' => [
                'slug' => 'google-business-profile-setup',
                'title' => 'Google Business Profile setup',
                'teaser' => 'I set up your Google Business Profile or put an existing one in order, so your business appears in Google Search and on Maps with the right details.',
                'meta' => 'Google Business Profile set up for you: details, categories, opening hours, services and photos, so your business is found in Search and on Maps.',
                'body' => <<<'MD'
                    Anyone looking for a business nearby sees the Google Maps listings first. If the opening hours are missing, the phone number is wrong or there is no listing at all, the enquiry goes to the next business. This package puts your profile in order.

                    ## What is included

                    - Create a new profile, or take over and review an existing one
                    - Enter name, address or service area, phone number, website and opening hours completely and consistently
                    - Choose fitting primary and additional categories
                    - Add services or products with a short description
                    - Select, crop and upload your photos
                    - A short guide on answering reviews and publishing posts

                    ## How it works

                    1. After your order I get in touch within two working days and send you a list of the details and photos I need.
                    2. You invite me as a manager of your profile, or we create it together. The profile always belongs to you.
                    3. I set everything up and let you know as soon as the profile is complete.
                    4. If Google asks you to verify your business, I guide you through that step.

                    ## Not included

                    - Google Ads. You pay the media budget for them directly to Google; it is not part of this package.
                    - Bought or self-written reviews. They break Google's policies.
                    - On-site photography. The photos you provide are the ones used.

                    ## Good to know

                    The price is a fixed price plus 19 % VAT. How long Google takes to verify a business is outside my control, and nobody can promise a particular position in search results.
                    MD,
            ],
        ],
        [
            // Prozessoptimierung, 70 € × 6 h.
            'tags' => 'prozessoptimierung,automatisierung',
            'net_cents' => 42000,
            'vat_rate_bp' => 1900,
            'fulfilment' => 'project',
            'image' => 'https://tracht-digital.de/images/services/02-prozesse-800.webp',
            'alt' => 'Prozessoptimierung',
            'de' => [
                'slug' => 'ablauf-analyse-ein-arbeitsablauf',
                'title' => 'Ablauf-Analyse für einen Arbeitsablauf',
                'teaser' => 'Ich gehe einen Ablauf, der Sie regelmäßig Zeit kostet, Schritt für Schritt mit Ihnen durch und zeige Ihnen, was sich streichen, vereinfachen oder automatisieren lässt.',
                'meta' => 'Ablauf-Analyse für kleine Betriebe: ein Arbeitsablauf aufgenommen, Zeitfresser sichtbar gemacht, mit Vorschlag zu Vereinfachung und Automatisierung.',
                'body' => <<<'MD'
                    Angebote schreiben, Aufträge erfassen, Rechnungen stellen, Termine abstimmen: In vielen Betrieben gibt es einen Ablauf, der über Jahre gewachsen ist und jede Woche mehr Zeit kostet als nötig. Dieses Paket nimmt sich genau einen davon vor.

                    ## Das ist enthalten

                    - Ein gemeinsamer Termin per Video oder nach Absprache vor Ort, in dem wir den Ablauf mit den Beteiligten durchgehen
                    - Eine übersichtliche Darstellung des heutigen Ablaufs: wer macht was, womit und wann
                    - Zeitfresser, doppelte Eingaben und Fehlerquellen, benannt und eingeordnet
                    - Ein Vorschlag für den vereinfachten Ablauf, wo es geht mit den Programmen, die Sie schon haben
                    - Eine Einschätzung, welche Schritte sich automatisieren lassen, mit Aufwand und grober Kostenschätzung
                    - Ein Abschlussgespräch mit einer Empfehlung für den ersten Schritt

                    ## So läuft es ab

                    1. Nach Ihrer Bestellung melde ich mich innerhalb von zwei Werktagen, und wir legen fest, um welchen Ablauf es geht.
                    2. Im Termin verfolgen wir den Ablauf an einem echten Beispiel.
                    3. Innerhalb von zehn Werktagen erhalten Sie Darstellung und Vorschlag schriftlich.
                    4. Im Abschlussgespräch entscheiden Sie, ob und wie es weitergeht.

                    ## Nicht enthalten

                    - Die Einrichtung der Automatisierung. Sie wird nach der Analyse getrennt vereinbart, auf Wunsch zum Festpreis.
                    - Weitere Abläufe. Für jeden weiteren gibt es ein eigenes Paket oder ein gemeinsames Angebot.
                    - Reisekosten für einen Termin vor Ort, falls wir einen vereinbaren. Sie werden vorher mit Ihnen abgestimmt.

                    ## Gut zu wissen

                    Der Preis ist ein Festpreis zuzüglich 19 % Umsatzsteuer. Ein Ablauf ist ein zusammenhängender Vorgang mit einem Anfang und einem Ergebnis, zum Beispiel „von der Anfrage bis zum Angebot“. Zeigt sich im Termin, dass es eigentlich zwei sind, sprechen wir darüber, bevor mehr Aufwand entsteht.
                    MD,
            ],
            'en' => [
                'slug' => 'workflow-analysis-one-process',
                'title' => 'Workflow analysis for one process',
                'teaser' => 'We walk through one workflow that regularly costs you time, step by step, and I show you what can be dropped, simplified or automated.',
                'meta' => 'Workflow analysis for small businesses: one process mapped, time sinks made visible, with a proposal for simplifying and automating it.',
                'body' => <<<'MD'
                    Writing quotes, recording orders, sending invoices, arranging appointments: many businesses have one workflow that grew over the years and costs more time every week than it should. This package takes on exactly one of them.

                    ## What is included

                    - One session by video, or on site by arrangement, in which we walk through the workflow with the people involved
                    - A clear picture of today's workflow: who does what, with which tool and when
                    - Time sinks, duplicate data entry and sources of error, named and weighed
                    - A proposal for the simplified workflow, using the programs you already have wherever possible
                    - An assessment of which steps can be automated, with effort and a rough cost estimate
                    - A closing conversation with a recommendation for the first step

                    ## How it works

                    1. After your order I get in touch within two working days, and we settle which workflow it is about.
                    2. In the session we follow the workflow through a real example.
                    3. Within ten working days you receive the picture and the proposal in writing.
                    4. In the closing conversation you decide whether and how to continue.

                    ## Not included

                    - Setting up the automation. It is agreed separately after the analysis, at a fixed price if you prefer.
                    - Further workflows. Each additional one gets its own package or a combined offer.
                    - Travel costs for an on-site session, if we agree on one. They are settled with you beforehand.

                    ## Good to know

                    The price is a fixed price plus 19 % VAT. A workflow is one connected sequence with a start and a result, for example "from enquiry to quote". If the session shows that it is really two, we talk about it before any extra effort arises.
                    MD,
            ],
        ],
        [
            // Individuelle Lösungen, 70 € × 4 h.
            'tags' => 'individuelle-loesungen,schnittstellen',
            'net_cents' => 28000,
            'vat_rate_bp' => 1900,
            'fulfilment' => 'project',
            'image' => 'https://tracht-digital.de/images/services/03-loesungen-800.webp',
            'alt' => 'Individuelle Lösungen',
            'de' => [
                'slug' => 'machbarkeitspruefung-schnittstelle',
                'title' => 'Machbarkeitsprüfung für eine Schnittstelle',
                'teaser' => 'Zwei Programme sollen Daten austauschen? Ich prüfe, ob und wie das mit den vorhandenen Schnittstellen geht, was es kostet und ob es einen einfacheren Weg gibt.',
                'meta' => 'Machbarkeitsprüfung: Können zwei Programme Daten austauschen? Schnittstellen geprüft, Lösungsweg, Aufwand und Alternativen schriftlich für Ihre Entscheidung.',
                'body' => <<<'MD'
                    Bestellungen aus dem Online-Shop werden von Hand in die Warenwirtschaft übertragen, Kundendaten aus einem Programm ins andere abgetippt: Eine Verbindung zwischen zwei Programmen spart oft viel Arbeit. Ob sie sich bauen lässt, hängt aber davon ab, was die Anbieter zulassen. Diese Prüfung klärt das, bevor Sie in eine Entwicklung investieren.

                    ## Das ist enthalten

                    - Aufnahme der Aufgabe: welche Daten wann von wo nach wo fließen sollen
                    - Prüfung der Schnittstellen, Exportmöglichkeiten und Nutzungsbedingungen beider Programme
                    - Suche nach fertigen Verbindungen und Standardwerkzeugen, die die Aufgabe bereits lösen
                    - Ein empfohlener Lösungsweg mit Risiken, laufenden Kosten Dritter und grober Aufwandsschätzung
                    - Eine schriftliche Zusammenfassung und ein Gespräch per Video

                    ## So läuft es ab

                    1. Nach Ihrer Bestellung melde ich mich innerhalb von zwei Werktagen, und wir klären die Aufgabe und die beteiligten Programme.
                    2. Falls nötig, geben Sie mir Zugang zur Dokumentation Ihrer Anbieter oder zu Testdaten.
                    3. Innerhalb von zehn Werktagen erhalten Sie die schriftliche Zusammenfassung.
                    4. Im Gespräch entscheiden Sie, ob und wie es weitergeht.

                    ## Nicht enthalten

                    - Die Entwicklung der Schnittstelle. Sie wird nach der Prüfung getrennt vereinbart.
                    - Mehr als zwei Programme. Für einen größeren Verbund ist ein Konzept aus der Beratung der bessere Anfang.
                    - Verträge oder Freischaltungen bei den Anbietern. Dafür bleiben Sie der Vertragspartner.

                    ## Gut zu wissen

                    Der Preis ist ein Festpreis zuzüglich 19 % Umsatzsteuer. Auch „geht nicht“ oder „lohnt sich nicht“ ist ein Ergebnis: Sie wissen es dann, bevor Geld in eine Entwicklung geflossen ist.
                    MD,
            ],
            'en' => [
                'slug' => 'integration-feasibility-check',
                'title' => 'Feasibility check for an integration',
                'teaser' => 'Two programs should exchange data? I check whether and how that works with the available interfaces, what it costs and whether there is a simpler way.',
                'meta' => 'Feasibility check: can two programs exchange data? Interfaces reviewed, with the solution, effort and alternatives in writing for your decision.',
                'body' => <<<'MD'
                    Orders from the online shop are copied into the inventory system by hand, customer data is retyped from one program into another: a connection between two programs often saves a lot of work. Whether it can be built, though, depends on what the providers allow. This check settles that before you invest in development.

                    ## What is included

                    - Capturing the task: which data should move when, from where and to where
                    - A review of the interfaces, export options and terms of use of both programs
                    - A search for ready-made connections and standard tools that already solve the task
                    - A recommended solution with its risks, third-party running costs and a rough effort estimate
                    - A written summary and a video call

                    ## How it works

                    1. After your order I get in touch within two working days, and we clarify the task and the programs involved.
                    2. If needed, you give me access to your providers' documentation or to test data.
                    3. Within ten working days you receive the written summary.
                    4. In the call you decide whether and how to continue.

                    ## Not included

                    - Building the integration. It is agreed separately after the check.
                    - More than two programs. For a larger set of systems, a concept from the consulting service is the better start.
                    - Contracts or activations with the providers. You remain their contracting party.

                    ## Good to know

                    The price is a fixed price plus 19 % VAT. "It cannot be done" or "it does not pay off" is a result too: you know it before any money has gone into development.
                    MD,
            ],
        ],
        [
            // Webauftritt, 65 € × 5 h. The hours are the product here.
            'tags' => 'webauftritt,website-pflege',
            'net_cents' => 32500,
            'vat_rate_bp' => 1900,
            'fulfilment' => 'ticket',
            'image' => 'https://tracht-digital.de/images/services/04-webauftritt-800.webp',
            'alt' => 'Webauftritt',
            'de' => [
                'slug' => 'pflegekontingent-webseite-5-stunden',
                'title' => 'Pflegekontingent Webseite: 5 Stunden',
                'teaser' => 'Fünf Stunden für Änderungen und Pflege an Ihrer bestehenden Webseite: neue Texte und Bilder, Updates und kleine Korrekturen, ohne jedes Mal einen eigenen Auftrag.',
                'meta' => 'Pflegekontingent für Ihre Webseite: fünf Stunden für Texte, Bilder, Updates und kleine Korrekturen, abgerechnet nach tatsächlichem Aufwand.',
                'body' => <<<'MD'
                    Ein neues Foto, geänderte Öffnungszeiten, ein zusätzlicher Menüpunkt, das fällige Update: Kleine Änderungen an der Webseite bleiben oft liegen, weil sich ein eigener Auftrag dafür nicht lohnt. Mit dem Pflegekontingent schreiben Sie mir einfach, was zu tun ist.

                    ## Das ist enthalten

                    - Fünf Stunden Arbeitszeit für Ihre bestehende Webseite
                    - Texte, Bilder, Öffnungszeiten, Preise und Termine ändern oder ergänzen
                    - Updates von System und Erweiterungen einspielen und die Seite danach prüfen
                    - Kleine Korrekturen an Darstellung und Funktion
                    - Eine Übersicht über verbrauchte und verbleibende Zeit, jederzeit auf Nachfrage

                    ## So läuft es ab

                    1. Nach Ihrer Bestellung melde ich mich innerhalb von zwei Werktagen und lasse mir die nötigen Zugänge geben.
                    2. Änderungswünsche schicken Sie mir per E-Mail.
                    3. Kleine Aufgaben erledige ich in der Regel innerhalb von drei Werktagen. Größere stimme ich vorher mit Ihnen ab, damit Sie wissen, wie viel Zeit sie brauchen.
                    4. Abgerechnet wird in Viertelstunden gegen das Kontingent.

                    ## Nicht enthalten

                    - Neue Funktionen, ein neues Design oder ein Neubau. Dafür mache ich Ihnen ein eigenes Angebot.
                    - Hosting, Domain und Lizenzen für Erweiterungen. Diese Kosten Dritter bleiben bei Ihnen.
                    - Notfalleinsätze mit fest zugesagter Reaktionszeit.

                    ## Gut zu wissen

                    Der Preis ist ein Festpreis zuzüglich 19 % Umsatzsteuer und entspricht fünf Stunden zum Stundensatz für den Webauftritt. Das Kontingent gilt zwölf Monate ab dem Kauf. Reicht es für eine Aufgabe nicht, sage ich Ihnen vorher Bescheid.
                    MD,
            ],
            'en' => [
                'slug' => 'website-care-5-hours',
                'title' => 'Website care: 5 hours',
                'teaser' => 'Five hours for changes and upkeep on your existing website: new copy and images, updates and small fixes, without a separate order each time.',
                'meta' => 'Website care package: five hours for copy, images, updates and small fixes on your existing website, billed by the actual time spent.',
                'body' => <<<'MD'
                    A new photo, changed opening hours, an extra menu item, the overdue update: small changes to a website often wait, because a separate order is not worth it. With the care package you simply write to me with what needs doing.

                    ## What is included

                    - Five hours of work on your existing website
                    - Change or add copy, images, opening hours, prices and dates
                    - Install updates to the system and its extensions, then check the site
                    - Small fixes to layout and function
                    - An overview of the time used and remaining, whenever you ask

                    ## How it works

                    1. After your order I get in touch within two working days and ask for the access I need.
                    2. You send change requests to me by email.
                    3. Small tasks are usually done within three working days. Larger ones I agree with you first, so you know how much time they take.
                    4. Time is billed against the package in quarter hours.

                    ## Not included

                    - New features, a new design or a rebuild. I make you a separate offer for those.
                    - Hosting, domain and licences for extensions. These third-party costs stay with you.
                    - Emergency work with a guaranteed response time.

                    ## Good to know

                    The price is a fixed price plus 19 % VAT and equals five hours at the Web Presence hourly rate. The package is valid for twelve months from purchase. If it will not cover a task, I tell you beforehand.
                    MD,
            ],
        ],
    ];

    /** Gross in cents, rounded the way `OrderRepository::price()` rounds. */
    public static function grossCents(int $netCents, int $vatRateBp): int
    {
        return $netCents + (int) round($netCents * $vatRateBp / 10000);
    }

    public function up(): void
    {
        $conn = $this->getAdapter()->getConnection();

        // Select, then insert: `VALUES()` in an upsert is deprecated on MySQL 8,
        // and a name the operator has since edited in the panel must stay.
        $category = $conn->prepare('SELECT slug FROM shop_category WHERE slug = :s LIMIT 1');
        $category->execute([':s' => self::CATEGORY['slug']]);
        if ($category->fetch() === false) {
            $conn->prepare('INSERT INTO shop_category (slug, name_de, name_en) VALUES (:s, :de, :en)')
                ->execute([
                    ':s' => self::CATEGORY['slug'],
                    ':de' => self::CATEGORY['name_de'],
                    ':en' => self::CATEGORY['name_en'],
                ]);
        }

        $slugTaken = $conn->prepare(
            'SELECT 1 FROM shop_product_translation WHERE lang = :l AND slug = :s LIMIT 1'
        );
        $product = $conn->prepare(
            'INSERT INTO shop_product
                (kind, status, editorial_status, category, tags, sort_price_cents, published_at)
             VALUES
                (\'digital\', \'draft\', \'published\', :c, :t, :sort, NULL)'
        );
        $translation = $conn->prepare(
            'INSERT INTO shop_product_translation
                (product_id, lang, slug, title, teaser, body, body_format, meta_description, machine_translated)
             VALUES
                (:p, :l, :s, :t, :teaser, :body, \'markdown\', :m, 0)'
        );
        $offer = $conn->prepare(
            'INSERT INTO shop_offer
                (product_id, kind, network, merchant, url, price_cents, currency, price_checked_at, availability, position)
             VALUES
                (:p, \'own\', \'direct\', :merchant, \'\', :price, \'EUR\', UTC_TIMESTAMP(), \'in_stock\', 0)'
        );
        $terms = $conn->prepare(
            'INSERT INTO shop_own_product (offer_id, net_cents, vat_rate_bp, fulfilment, requires_shipping)
             VALUES (:o, :net, :vat, :f, 0)'
        );
        $cover = $conn->prepare(
            'INSERT INTO shop_media (product_id, role, source, url, alt, sort)
             VALUES (:p, \'cover\', \'remote\', :u, :a, 0)'
        );

        foreach (self::PACKAGES as $package) {
            foreach (['de', 'en'] as $lang) {
                $slugTaken->execute([':l' => $lang, ':s' => $package[$lang]['slug']]);
                if ($slugTaken->fetch() !== false) {
                    continue 2;
                }
            }

            $gross = self::grossCents($package['net_cents'], $package['vat_rate_bp']);

            $product->execute([
                ':c' => self::CATEGORY['slug'],
                ':t' => $package['tags'],
                ':sort' => $gross,
            ]);
            $productId = (int) $conn->lastInsertId();

            foreach (['de', 'en'] as $lang) {
                $text = $package[$lang];
                $translation->execute([
                    ':p' => $productId,
                    ':l' => $lang,
                    ':s' => $text['slug'],
                    ':t' => $text['title'],
                    ':teaser' => $text['teaser'],
                    ':body' => $text['body'],
                    ':m' => $text['meta'],
                ]);
            }

            $offer->execute([
                ':p' => $productId,
                ':merchant' => self::MERCHANT,
                ':price' => $gross,
            ]);
            $offerId = (int) $conn->lastInsertId();

            $terms->execute([
                ':o' => $offerId,
                ':net' => $package['net_cents'],
                ':vat' => $package['vat_rate_bp'],
                ':f' => $package['fulfilment'],
            ]);

            $cover->execute([
                ':p' => $productId,
                ':u' => $package['image'],
                ':a' => $package['alt'],
            ]);
        }
    }

    public function down(): void
    {
        $conn = $this->getAdapter()->getConnection();

        // Only a package whose German title AND body are still verbatim. One the
        // operator has since edited stays, with everything attached to it.
        $find = $conn->prepare(
            'SELECT product_id FROM shop_product_translation
              WHERE lang = \'de\' AND slug = :s AND title = :t AND body = :b LIMIT 1'
        );
        $delete = $conn->prepare('DELETE FROM shop_product WHERE id = :id');
        foreach (self::PACKAGES as $package) {
            $find->execute([
                ':s' => $package['de']['slug'],
                ':t' => $package['de']['title'],
                ':b' => $package['de']['body'],
            ]);
            $productId = $find->fetchColumn();
            if ($productId !== false && $productId !== null) {
                $delete->execute([':id' => (int) $productId]);
            }
        }

        $inUse = $conn->prepare('SELECT 1 FROM shop_product WHERE category = :s LIMIT 1');
        $inUse->execute([':s' => self::CATEGORY['slug']]);
        if ($inUse->fetch() === false) {
            $conn->prepare(
                'DELETE FROM shop_category WHERE slug = :s AND name_de = :de AND name_en = :en'
            )->execute([
                ':s' => self::CATEGORY['slug'],
                ':de' => self::CATEGORY['name_de'],
                ':en' => self::CATEGORY['name_en'],
            ]);
        }
    }
}
