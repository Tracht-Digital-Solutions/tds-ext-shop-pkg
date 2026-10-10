<?php
declare(strict_types=1);

/**
 * Own products, category `recht-datenschutz`. Rate: Webauftritt, 65 € net per hour.
 * Technical implementation only — every text says so; legal content comes from
 * the customer, a generator or their lawyer.
 */
$image = 'https://tracht-digital.de/images/services/01-beratung-800.webp';
$rate = 6500;

return [
    [
        'category' => 'recht-datenschutz', 'tags' => 'impressum,datenschutz', 'hours' => 2, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Impressum und Datenschutzerklärung auf einer Website',
        'prompt' => 'two neat document pages labelled with a paragraph sign and a shield, linked from a website footer',
        'de' => [
            'slug' => 'impressum-und-datenschutz-einbinden',
            'title' => 'Impressum und Datenschutzerklärung einbinden',
            'meta_title' => 'Impressum & Datenschutzerklärung einbinden',
            'teaser' => 'Ihre Rechtstexte sauber auf der Website: erreichbar von jeder Seite, gut lesbar, aktuell.',
            'meta' => 'Impressum und Datenschutzerklärung technisch einbinden: eigene Seiten, Links im Fußbereich, gut lesbar formatiert. Die Texte liefern Sie, ich setze sie um.',
            'summary' => 'Ich binde Ihr Impressum und Ihre Datenschutzerklärung als eigene Seiten ein, verlinkt von jeder Seite. Die Texte liefern Sie, etwa aus einem Generator oder vom Anwalt.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '2 Rechtsseiten'],
                ['label' => 'Dauer', 'value' => '1–2 Werktage'],
                ['label' => 'Aufwand', 'value' => '2 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Eingebundene Rechtstexte'],
            ],
            'faq' => [
                ['q' => 'Schreiben Sie die Rechtstexte?', 'a' => 'Nein. Ich bin kein Anwalt. Gute Quellen sind Generatoren von Fachanwälten oder Ihr eigener Anwalt.'],
                ['q' => 'Passen Sie die Texte an meine Website an?', 'a' => 'Ich weise Sie darauf hin, welche Dienste Ihre Seite nutzt. Den Inhalt der Texte verantworten Sie.'],
                ['q' => 'Können die Texte automatisch aktualisiert werden?', 'a' => 'Bei manchen Generatoren ja, per Schnittstelle. Das richte ich auf Wunsch ein.'],
            ],
            'body' => <<<'MD'
                Impressum und Datenschutzerklärung müssen leicht zu finden sein. Ich sorge für die technische Seite.

                ## Das ist enthalten

                - Zwei eigene Seiten für Impressum und Datenschutzerklärung
                - Links im Fußbereich jeder Seite
                - Lesbare Formatierung mit Überschriften
                - Liste der Dienste, die Ihre Website nutzt

                ## So läuft es ab

                1. Ich schicke Ihnen die Liste der genutzten Dienste.
                2. Sie erstellen die Texte, etwa mit einem Generator.
                3. Ich binde sie ein.

                ## Nicht enthalten

                - Rechtsberatung und Erstellung der Texte
                - Kosten für Generatoren

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer. Dies ist eine technische Leistung, keine Rechtsberatung.
                MD,
        ],
        'en' => [
            'slug' => 'legal-notice-and-privacy-policy-setup',
            'title' => 'Legal notice and privacy policy setup',
            'teaser' => 'Your legal pages properly on your website: reachable from every page, readable, current.',
            'meta' => 'Technical setup of legal notice and privacy policy: dedicated pages, footer links, readable formatting. You supply the texts, I implement them.',
            'summary' => 'I add your legal notice and privacy policy as dedicated pages, linked from every page. You supply the texts, for example from a generator or your lawyer.',
            'facts' => [
                ['label' => 'Scope', 'value' => '2 legal pages'],
                ['label' => 'Duration', 'value' => '1–2 working days'],
                ['label' => 'Effort', 'value' => '2 hours'],
                ['label' => 'Result', 'value' => 'Legal texts in place'],
            ],
            'faq' => [
                ['q' => 'Do you write the legal texts?', 'a' => 'No. I am not a lawyer. Good sources are generators by specialist lawyers or your own lawyer.'],
                ['q' => 'Do you adapt the texts to my website?', 'a' => 'I tell you which services your site uses. The content of the texts is your responsibility.'],
                ['q' => 'Can the texts update automatically?', 'a' => 'With some generators, yes, via an interface. I set it up on request.'],
            ],
            'body' => <<<'MD'
                Legal notice and privacy policy must be easy to find. I take care of the technical side.

                ## What is included

                - Two dedicated pages for legal notice and privacy policy
                - Links in the footer of every page
                - Readable formatting with headings
                - List of the services your website uses

                ## How it works

                1. I send you the list of services in use.
                2. You create the texts, for example with a generator.
                3. I add them.

                ## Not included

                - Legal advice and writing the texts
                - Generator fees

                ## Good to know

                Fixed price including 19 % VAT. This is a technical service, not legal advice.
                MD,
        ],
    ],
    [
        'category' => 'recht-datenschutz', 'tags' => 'cookie-banner,consent', 'hours' => 3, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Cookie-Banner mit gleichwertigen Knöpfen',
        'prompt' => 'a friendly cookie consent panel with two equal buttons, scripts waiting behind a small gate',
        'de' => [
            'slug' => 'cookie-banner-einrichten',
            'title' => 'Cookie-Banner einrichten',
            'teaser' => 'Ein Einwilligungsbanner, das Dienste erst nach Zustimmung lädt und Besucher nicht nervt.',
            'meta' => 'Cookie-Banner einrichten: Einwilligung einholen, Dienste erst nach Zustimmung laden, gleichwertige Knöpfe. Technisch sauber umgesetzt, zum Festpreis.',
            'summary' => 'Ich richte ein Cookie-Banner ein, das externe Dienste erst nach Zustimmung lädt. Ablehnen ist so einfach wie Zustimmen, und die Einwilligungen werden protokolliert.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Website, bis zu 8 Dienste'],
                ['label' => 'Dauer', 'value' => '2–3 Werktage'],
                ['label' => 'Aufwand', 'value' => '3 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Getestetes Banner'],
            ],
            'faq' => [
                ['q' => 'Brauche ich überhaupt ein Cookie-Banner?', 'a' => 'Nur wenn Ihre Seite Dienste nutzt, die eine Einwilligung brauchen, etwa Statistik oder eingebettete Videos. Das prüfe ich zuerst.'],
                ['q' => 'Welches Tool wird verwendet?', 'a' => 'Eines, das zu Ihrer Seite passt, kostenlos oder kostenpflichtig. Laufende Kosten nenne ich vorher.'],
                ['q' => 'Ist das Banner damit rechtssicher?', 'a' => 'Technisch sauber, ja. Ob Ihre Einstellungen rechtlich ausreichen, kann nur ein Anwalt verbindlich sagen.'],
            ],
            'body' => <<<'MD'
                Viele Banner laden Dienste schon vor der Zustimmung. Dieses nicht.

                ## Das ist enthalten

                - Prüfung, welche Dienste eine Einwilligung brauchen
                - Einrichtung des Banners mit gleichwertigen Knöpfen
                - Blockieren der Dienste bis zur Zustimmung
                - Test, ob vor der Zustimmung wirklich nichts lädt

                ## So läuft es ab

                1. Ich prüfe Ihre Seite auf externe Dienste.
                2. Ich richte das Banner ein.
                3. Sie bekommen das Testergebnis.

                ## Nicht enthalten

                - Rechtsberatung
                - Lizenzkosten des Banner-Tools

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer. Dies ist eine technische Leistung, keine Rechtsberatung.
                MD,
        ],
        'en' => [
            'slug' => 'cookie-consent-banner-setup',
            'title' => 'Cookie consent banner setup',
            'teaser' => 'A consent banner that only loads services after consent and does not annoy visitors.',
            'meta' => 'Cookie consent banner setup: ask for consent, load services only after approval, equal buttons. Clean technical implementation at a fixed price.',
            'summary' => 'I set up a cookie banner that only loads external services after consent. Declining is as easy as accepting, and consents are logged.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 website, up to 8 services'],
                ['label' => 'Duration', 'value' => '2–3 working days'],
                ['label' => 'Effort', 'value' => '3 hours'],
                ['label' => 'Result', 'value' => 'Tested banner'],
            ],
            'faq' => [
                ['q' => 'Do I need a cookie banner at all?', 'a' => 'Only if your site uses services that need consent, such as analytics or embedded videos. I check that first.'],
                ['q' => 'Which tool is used?', 'a' => 'One that suits your site, free or paid. I name running costs beforehand.'],
                ['q' => 'Is the banner then legally safe?', 'a' => 'Technically clean, yes. Whether your settings are legally sufficient only a lawyer can confirm.'],
            ],
            'body' => <<<'MD'
                Many banners load services before consent. This one does not.

                ## What is included

                - Checking which services need consent
                - Setting up the banner with equal buttons
                - Blocking services until consent is given
                - Testing that nothing loads before consent

                ## How it works

                1. I scan your site for external services.
                2. I set up the banner.
                3. You receive the test result.

                ## Not included

                - Legal advice
                - Licence costs of the banner tool

                ## Good to know

                Fixed price including 19 % VAT. This is a technical service, not legal advice.
                MD,
        ],
    ],
    [
        'category' => 'recht-datenschutz', 'tags' => 'barrierefreiheit,bfsg,check', 'hours' => 6, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Barrierefreiheits-Prüfung einer Website',
        'prompt' => 'a website checked with a magnifier and accessibility symbols: keyboard, contrast, screen reader',
        'de' => [
            'slug' => 'barrierefreiheits-check-bfsg',
            'title' => 'Barrierefreiheits-Check (BFSG)',
            'teaser' => 'Wie barrierefrei ist Ihre Website? Ein Prüfbericht mit klaren Prioritäten.',
            'meta' => 'Barrierefreiheits-Check nach WCAG 2.1 AA: Prüfung von Tastatur, Kontrast, Screenreader und Formularen. Bericht mit Prioritäten, passend zum BFSG.',
            'summary' => 'Ich prüfe bis zu fünf Seiten Ihrer Website nach WCAG 2.1 AA, automatisiert und von Hand. Sie bekommen einen Bericht mit den Mängeln, sortiert nach Dringlichkeit.',
            'facts' => [
                ['label' => 'Umfang', 'value' => 'bis zu 5 Seiten'],
                ['label' => 'Maßstab', 'value' => 'WCAG 2.1 Stufe AA'],
                ['label' => 'Aufwand', 'value' => '6 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Prüfbericht (PDF)'],
            ],
            'faq' => [
                ['q' => 'Was ist das BFSG?', 'a' => 'Das Barrierefreiheitsstärkungsgesetz. Es verlangt seit Juni 2025 barrierefreie Online-Angebote für Verbraucher, mit Ausnahmen für Kleinstunternehmen bei Dienstleistungen.'],
                ['q' => 'Reicht ein automatischer Test?', 'a' => 'Nein. Automatische Tests finden nur einen Teil der Mängel. Deshalb prüfe ich zusätzlich von Hand.'],
                ['q' => 'Beheben Sie die Mängel auch?', 'a' => 'Ja, als eigenes Paket „Barrierefreiheit verbessern“.'],
            ],
            'body' => <<<'MD'
                Barrierefreiheit hilft allen Besuchern. Für viele Betriebe ist sie inzwischen Pflicht.

                ## Das ist enthalten

                - Automatische Prüfung von bis zu fünf Seiten
                - Prüfung von Hand: Tastatur, Screenreader, Kontrast, Formulare
                - Bericht mit Mängeln, Fundstellen und Dringlichkeit

                ## So läuft es ab

                1. Wir wählen die wichtigsten Seiten aus.
                2. Ich prüfe automatisiert und von Hand.
                3. Sie bekommen den Bericht und eine kurze Besprechung.

                ## Nicht enthalten

                - Behebung der Mängel (eigenes Paket)
                - Rechtliche Einschätzung, ob das BFSG für Sie gilt

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer. Dies ist eine technische Prüfung, keine Rechtsberatung.
                MD,
        ],
        'en' => [
            'slug' => 'accessibility-check',
            'title' => 'Accessibility check',
            'teaser' => 'How accessible is your website? An audit report with clear priorities.',
            'meta' => 'Accessibility check to WCAG 2.1 AA: keyboard, contrast, screen reader and forms tested. A report with priorities, in line with the German BFSG.',
            'summary' => 'I check up to five pages of your website against WCAG 2.1 AA, automatically and by hand. You receive a report of the issues, sorted by urgency.',
            'facts' => [
                ['label' => 'Scope', 'value' => 'up to 5 pages'],
                ['label' => 'Standard', 'value' => 'WCAG 2.1 level AA'],
                ['label' => 'Effort', 'value' => '6 hours'],
                ['label' => 'Result', 'value' => 'Audit report (PDF)'],
            ],
            'faq' => [
                ['q' => 'What is the BFSG?', 'a' => 'The German Accessibility Act. Since June 2025 it requires accessible online offerings for consumers, with exemptions for micro-enterprises providing services.'],
                ['q' => 'Is an automatic test enough?', 'a' => 'No. Automatic tests find only part of the issues. That is why I also test by hand.'],
                ['q' => 'Do you fix the issues too?', 'a' => 'Yes, as the separate package "Improve accessibility".'],
            ],
            'body' => <<<'MD'
                Accessibility helps every visitor. For many businesses it is now mandatory.

                ## What is included

                - Automatic testing of up to five pages
                - Manual testing: keyboard, screen reader, contrast, forms
                - Report with issues, locations and urgency

                ## How it works

                1. We pick the most important pages.
                2. I test automatically and by hand.
                3. You receive the report and a short discussion.

                ## Not included

                - Fixing the issues (separate package)
                - Legal assessment of whether the BFSG applies to you

                ## Good to know

                Fixed price including 19 % VAT. This is a technical audit, not legal advice.
                MD,
        ],
    ],
    [
        'category' => 'recht-datenschutz', 'tags' => 'barrierefreiheit,umsetzung', 'hours' => 12, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Barrierefreie Website mit gutem Kontrast',
        'prompt' => 'a web page with bold readable text, strong contrast and a visible keyboard focus ring, an accessibility icon',
        'de' => [
            'slug' => 'barrierefreiheit-verbessern',
            'title' => 'Barrierefreiheit verbessern',
            'teaser' => 'Die wichtigsten Mängel aus dem Check beheben: Kontrast, Tastatur, Bilder, Formulare.',
            'meta' => 'Barrierefreiheit verbessern: Kontraste, Tastaturbedienung, Alternativtexte, Formulare und Überschriften nach WCAG 2.1 AA beheben. Mit Nachprüfung.',
            'summary' => 'Ich behebe die wichtigsten Barrieren Ihrer Website, etwa schwache Kontraste, fehlende Alternativtexte und nicht bedienbare Formulare. Eine Nachprüfung zeigt den Fortschritt.',
            'facts' => [
                ['label' => 'Umfang', 'value' => 'bis zu 5 Seiten'],
                ['label' => 'Voraussetzung', 'value' => 'Barrierefreiheits-Check'],
                ['label' => 'Aufwand', 'value' => '12 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Behobene Mängel und Nachprüfung'],
            ],
            'faq' => [
                ['q' => 'Brauche ich vorher den Check?', 'a' => 'Ja, oder einen vergleichbaren Prüfbericht. Er legt fest, was behoben wird.'],
                ['q' => 'Ist meine Seite danach vollständig barrierefrei?', 'a' => 'Die wichtigsten Mängel sind behoben. Ob alles erreicht ist, zeigt die Nachprüfung.'],
                ['q' => 'Ändert sich das Design?', 'a' => 'Nur wo nötig, etwa bei Farben mit zu wenig Kontrast. Größere Änderungen stimme ich vorher ab.'],
            ],
            'body' => <<<'MD'
                Aus dem Prüfbericht wird eine bessere Website.

                ## Das ist enthalten

                - Kontraste und Schriftgrößen anpassen
                - Tastaturbedienung und sichtbaren Fokus herstellen
                - Alternativtexte und Überschriftenstruktur ergänzen
                - Formulare mit Beschriftungen und Fehlermeldungen
                - Nachprüfung der bearbeiteten Seiten

                ## So läuft es ab

                1. Wir legen anhand des Berichts die Reihenfolge fest.
                2. Ich behebe die Mängel.
                3. Ich prüfe nach und dokumentiere das Ergebnis.

                ## Nicht enthalten

                - Erklärung zur Barrierefreiheit (eigenes Paket)
                - Neubau von Seiten

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'improve-accessibility',
            'title' => 'Improve accessibility',
            'teaser' => 'Fix the main issues from the check: contrast, keyboard, images, forms.',
            'meta' => 'Improve accessibility: fix contrast, keyboard use, alt texts, forms and headings to WCAG 2.1 AA. Including a follow-up check of your pages.',
            'summary' => 'I fix the main barriers on your website, such as weak contrast, missing alt texts and forms that cannot be used. A follow-up check shows the progress.',
            'facts' => [
                ['label' => 'Scope', 'value' => 'up to 5 pages'],
                ['label' => 'Prerequisite', 'value' => 'Accessibility check'],
                ['label' => 'Effort', 'value' => '12 hours'],
                ['label' => 'Result', 'value' => 'Issues fixed, follow-up check'],
            ],
            'faq' => [
                ['q' => 'Do I need the check first?', 'a' => 'Yes, or a comparable audit report. It defines what gets fixed.'],
                ['q' => 'Is my site fully accessible afterwards?', 'a' => 'The main issues are fixed. The follow-up check shows whether everything is met.'],
                ['q' => 'Will the design change?', 'a' => 'Only where needed, for example colours with too little contrast. Bigger changes are agreed first.'],
            ],
            'body' => <<<'MD'
                The audit report becomes a better website.

                ## What is included

                - Adjusting contrast and font sizes
                - Keyboard operation and a visible focus
                - Adding alt texts and a heading structure
                - Forms with labels and error messages
                - Follow-up check of the pages worked on

                ## How it works

                1. We set the order based on the report.
                2. I fix the issues.
                3. I re-check and document the result.

                ## Not included

                - Accessibility statement (separate package)
                - Rebuilding pages

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'recht-datenschutz', 'tags' => 'google-fonts,datenschutz', 'hours' => 2, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Schriften lokal auf dem eigenen Server',
        'prompt' => 'letters of a font moving from a distant cloud into a small local server box',
        'de' => [
            'slug' => 'google-fonts-lokal-einbinden',
            'title' => 'Google Fonts lokal einbinden',
            'teaser' => 'Schriften von Ihrem eigenen Server laden statt von Google, ohne dass sich das Design ändert.',
            'meta' => 'Google Fonts lokal einbinden: Schriften auf Ihren Server holen, externe Abrufe entfernen und prüfen. Gleiches Design, keine Datenübertragung an Google.',
            'summary' => 'Ich lade die Schriften Ihrer Website auf Ihren eigenen Server, damit beim Seitenaufruf keine Daten an Google gehen. Das Design bleibt unverändert.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Website'],
                ['label' => 'Dauer', 'value' => '1–2 Werktage'],
                ['label' => 'Aufwand', 'value' => '2 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Keine externen Schriftabrufe'],
            ],
            'faq' => [
                ['q' => 'Warum lokal einbinden?', 'a' => 'Beim Laden von Google übermittelt der Browser die IP-Adresse an Google. Lokal geladen entfällt das.'],
                ['q' => 'Wird die Seite dadurch langsamer?', 'a' => 'Nein, meist sogar etwas schneller.'],
                ['q' => 'Wie prüfen Sie das Ergebnis?', 'a' => 'Ich prüfe mit den Entwicklerwerkzeugen des Browsers, dass keine Anfrage mehr an Google geht.'],
            ],
            'body' => <<<'MD'
                Gleiche Schrift, keine Verbindung zu Google.

                ## Das ist enthalten

                - Erfassen aller genutzten Schriften
                - Ablage auf Ihrem Server und Einbindung
                - Entfernen der externen Abrufe, auch aus Themes und Plugins
                - Prüfung im Browser

                ## So läuft es ab

                1. Sie geben mir Zugang zur Website.
                2. Ich stelle die Schriften um.
                3. Sie bekommen das Prüfergebnis.

                ## Nicht enthalten

                - Andere externe Dienste (siehe „Externe Dienste prüfen“)

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'host-google-fonts-locally',
            'title' => 'Host Google Fonts locally',
            'teaser' => 'Load fonts from your own server instead of Google, without changing the design.',
            'meta' => 'Host Google Fonts locally: move fonts to your server, remove external requests and verify. Same design, no data sent to Google.',
            'summary' => 'I move your website\'s fonts to your own server so no data goes to Google when a page loads. The design stays exactly the same.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 website'],
                ['label' => 'Duration', 'value' => '1–2 working days'],
                ['label' => 'Effort', 'value' => '2 hours'],
                ['label' => 'Result', 'value' => 'No external font requests'],
            ],
            'faq' => [
                ['q' => 'Why host locally?', 'a' => 'Loading from Google sends the visitor\'s IP address to Google. Hosting locally avoids that.'],
                ['q' => 'Will the site get slower?', 'a' => 'No, usually slightly faster.'],
                ['q' => 'How do you check the result?', 'a' => 'With the browser\'s developer tools I verify that no request goes to Google any more.'],
            ],
            'body' => <<<'MD'
                Same font, no connection to Google.

                ## What is included

                - Listing every font in use
                - Storing them on your server and wiring them in
                - Removing external requests, including from themes and plugins
                - Verification in the browser

                ## How it works

                1. You give me access to the website.
                2. I switch the fonts.
                3. You receive the verification result.

                ## Not included

                - Other external services (see "External services check")

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'recht-datenschutz', 'tags' => 'datenschutz,dienste,scan', 'hours' => 3, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Prüfung externer Dienste auf einer Website',
        'prompt' => 'a website with thin lines leading out to small third-party service icons, each line inspected by a magnifier',
        'de' => [
            'slug' => 'externe-dienste-pruefen',
            'title' => 'Externe Dienste prüfen',
            'teaser' => 'Welche Daten Ihre Website an wen sendet: eine vollständige Liste mit Empfehlungen.',
            'meta' => 'Externe Dienste Ihrer Website prüfen: alle Verbindungen zu Drittanbietern, Cookies und Einbettungen, mit Empfehlungen für Datenschutzerklärung und Banner.',
            'summary' => 'Ich liste alle Dienste auf, mit denen Ihre Website Verbindung aufnimmt, etwa Schriften, Karten, Videos oder Statistik. Zu jedem Dienst gibt es eine technische Empfehlung.',
            'facts' => [
                ['label' => 'Umfang', 'value' => 'bis zu 10 Seiten'],
                ['label' => 'Dauer', 'value' => '2–3 Werktage'],
                ['label' => 'Aufwand', 'value' => '3 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Liste mit Empfehlungen'],
            ],
            'faq' => [
                ['q' => 'Wofür brauche ich die Liste?', 'a' => 'Ihre Datenschutzerklärung und Ihr Cookie-Banner müssen die genutzten Dienste nennen. Die Liste ist die Grundlage.'],
                ['q' => 'Entfernen Sie die Dienste auch?', 'a' => 'Auf Wunsch, nach Aufwand. Viele lassen sich datenschutzfreundlich ersetzen.'],
                ['q' => 'Ist das eine Datenschutzprüfung?', 'a' => 'Es ist eine technische Bestandsaufnahme. Die rechtliche Bewertung trifft Ihr Datenschutzberater.'],
            ],
            'body' => <<<'MD'
                Viele Websites senden Daten an Dienste, von denen ihre Betreiber nichts wissen.

                ## Das ist enthalten

                - Analyse von bis zu zehn Seiten im Browser
                - Liste aller Drittanbieter, Cookies und Einbettungen
                - Technische Empfehlung je Dienst: behalten, ersetzen, entfernen

                ## So läuft es ab

                1. Sie nennen die wichtigsten Seiten.
                2. Ich analysiere die Verbindungen.
                3. Sie bekommen die Liste mit Empfehlungen.

                ## Nicht enthalten

                - Rechtliche Bewertung
                - Umbau der Website

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer. Dies ist eine technische Leistung, keine Rechtsberatung.
                MD,
        ],
        'en' => [
            'slug' => 'external-services-check',
            'title' => 'External services check',
            'teaser' => 'Which data your website sends to whom: a complete list with recommendations.',
            'meta' => 'External services check for your website: a list of all third-party connections, cookies and embeds, with recommendations for privacy policy and banner.',
            'summary' => 'I list every service your website connects to, such as fonts, maps, videos or analytics. Each service comes with a technical recommendation.',
            'facts' => [
                ['label' => 'Scope', 'value' => 'up to 10 pages'],
                ['label' => 'Duration', 'value' => '2–3 working days'],
                ['label' => 'Effort', 'value' => '3 hours'],
                ['label' => 'Result', 'value' => 'List with recommendations'],
            ],
            'faq' => [
                ['q' => 'What do I need the list for?', 'a' => 'Your privacy policy and cookie banner must name the services in use. The list is the basis.'],
                ['q' => 'Do you remove the services too?', 'a' => 'On request, billed by effort. Many can be replaced with privacy-friendly options.'],
                ['q' => 'Is this a data protection audit?', 'a' => 'It is a technical inventory. The legal assessment is up to your data protection adviser.'],
            ],
            'body' => <<<'MD'
                Many websites send data to services their owners know nothing about.

                ## What is included

                - Browser analysis of up to ten pages
                - List of all third parties, cookies and embeds
                - Technical recommendation per service: keep, replace, remove

                ## How it works

                1. You name the key pages.
                2. I analyse the connections.
                3. You receive the list with recommendations.

                ## Not included

                - Legal assessment
                - Rebuilding the website

                ## Good to know

                Fixed price including 19 % VAT. This is a technical service, not legal advice.
                MD,
        ],
    ],
    [
        'category' => 'recht-datenschutz', 'tags' => 'barrierefreiheit,erklaerung', 'hours' => 2, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Erklärung zur Barrierefreiheit auf einer Website',
        'prompt' => 'a document page with an accessibility symbol and a feedback envelope, linked from a website footer',
        'de' => [
            'slug' => 'erklaerung-zur-barrierefreiheit',
            'title' => 'Erklärung zur Barrierefreiheit einbinden',
            'teaser' => 'Die Seite, auf der Sie den Stand der Barrierefreiheit erklären und Rückmeldungen ermöglichen.',
            'meta' => 'Erklärung zur Barrierefreiheit einbinden: eigene Seite mit Stand, bekannten Einschränkungen und Kontaktweg für Rückmeldungen. Auf Basis Ihres Prüfberichts.',
            'summary' => 'Ich erstelle aus Ihrem Prüfbericht einen Entwurf der Erklärung zur Barrierefreiheit und binde ihn als eigene Seite ein. Ein Kontaktweg für Rückmeldungen gehört dazu.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Seite'],
                ['label' => 'Voraussetzung', 'value' => 'Prüfbericht'],
                ['label' => 'Aufwand', 'value' => '2 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Eingebundene Erklärung'],
            ],
            'faq' => [
                ['q' => 'Was steht in der Erklärung?', 'a' => 'Wie barrierefrei die Seite ist, welche Einschränkungen bekannt sind und wie man Probleme meldet.'],
                ['q' => 'Ist der Text rechtlich geprüft?', 'a' => 'Nein. Ich erstelle einen Entwurf aus dem Prüfbericht. Prüfen Sie ihn bei Bedarf mit Ihrem Anwalt.'],
                ['q' => 'Muss die Erklärung aktualisiert werden?', 'a' => 'Ja, wenn sich die Seite deutlich ändert. Das Datum der letzten Prüfung steht in der Erklärung.'],
            ],
            'body' => <<<'MD'
                Die Erklärung zeigt offen, wie barrierefrei Ihre Website ist, und wie man Probleme meldet.

                ## Das ist enthalten

                - Entwurf auf Basis Ihres Prüfberichts
                - Eigene Seite, verlinkt im Fußbereich
                - Kontaktweg für Rückmeldungen

                ## So läuft es ab

                1. Sie schicken den Prüfbericht.
                2. Ich erstelle den Entwurf zur Freigabe.
                3. Nach Ihrer Freigabe binde ich die Seite ein.

                ## Nicht enthalten

                - Barrierefreiheits-Prüfung (eigenes Paket)
                - Rechtliche Prüfung

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer. Dies ist eine technische Leistung, keine Rechtsberatung.
                MD,
        ],
        'en' => [
            'slug' => 'accessibility-statement-setup',
            'title' => 'Accessibility statement setup',
            'teaser' => 'The page where you explain your accessibility status and invite feedback.',
            'meta' => 'Accessibility statement setup: a dedicated page with status, known limitations and a feedback contact. Based on your audit report.',
            'summary' => 'From your audit report I draft an accessibility statement and add it as a dedicated page. A contact route for feedback is included.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 page'],
                ['label' => 'Prerequisite', 'value' => 'Audit report'],
                ['label' => 'Effort', 'value' => '2 hours'],
                ['label' => 'Result', 'value' => 'Statement in place'],
            ],
            'faq' => [
                ['q' => 'What does the statement contain?', 'a' => 'How accessible the site is, which limitations are known and how to report problems.'],
                ['q' => 'Is the text legally reviewed?', 'a' => 'No. I draft it from the audit report. Have it reviewed by your lawyer if needed.'],
                ['q' => 'Does it need updating?', 'a' => 'Yes, when the site changes significantly. The statement shows the date of the last review.'],
            ],
            'body' => <<<'MD'
                The statement openly shows how accessible your website is and how to report problems.

                ## What is included

                - Draft based on your audit report
                - Dedicated page, linked in the footer
                - Contact route for feedback

                ## How it works

                1. You send the audit report.
                2. I draft the statement for your approval.
                3. After approval I add the page.

                ## Not included

                - Accessibility audit (separate package)
                - Legal review

                ## Good to know

                Fixed price including 19 % VAT. This is a technical service, not legal advice.
                MD,
        ],
    ],
];
