<?php
declare(strict_types=1);

/**
 * Own products, category `webauftritt`. Rate: Webauftritt, 65 € net per hour.
 * Price = rate × hours (`CatalogueSeed::netCents()`), shown gross incl. 19 % VAT.
 * `prompt` is the DALL-E subject for the cover (docs/product-image-prompts.md).
 */
$image = 'https://tracht-digital.de/images/services/04-webauftritt-800.webp';
$rate = 6500;

return [
    [
        'category' => 'webauftritt', 'tags' => 'website,onepager', 'hours' => 16, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Onepager-Website auf Laptop und Smartphone',
        'prompt' => 'a single long web page scrolling on a laptop and a phone side by side, sections stacked like cards',
        'de' => [
            'slug' => 'onepager-website',
            'title' => 'Onepager-Website',
            'teaser' => 'Eine schlanke Website auf einer Seite: wer Sie sind, was Sie anbieten, wie man Sie erreicht.',
            'meta' => 'Onepager-Website zum Festpreis: eine Seite mit Leistungen, Über-mich, Kontakt und Rechtstexten. Für Selbstständige, mobil optimiert, in Ihrem Namen.',
            'summary' => 'Ein Onepager ist eine Website auf einer einzigen Seite. Sie bekommt bis zu fünf Abschnitte, ein Kontaktformular und die Rechtstexte. Nach etwa zwei Wochen ist sie online.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Seite, bis zu 5 Abschnitte'],
                ['label' => 'Dauer', 'value' => 'ca. 2 Wochen ab Inhalten'],
                ['label' => 'Aufwand', 'value' => '16 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Fertige Website mit Zugängen'],
            ],
            'faq' => [
                ['q' => 'Für wen eignet sich ein Onepager?', 'a' => 'Für Selbstständige und kleine Betriebe mit einem klaren Angebot. Wer viele Leistungen erklären muss, ist mit der Firmenwebsite besser bedient.'],
                ['q' => 'Kann ich später Seiten ergänzen?', 'a' => 'Ja. Der Onepager wächst mit, weitere Seiten lassen sich jederzeit anbauen.'],
                ['q' => 'Welches System wird verwendet?', 'a' => 'Meist WordPress, damit Sie Texte selbst ändern können. Andere Wünsche stimmen wir vorab ab.'],
            ],
            'body' => <<<'MD'
                Ein Onepager zeigt alles Wichtige auf einer Seite. Besucher scrollen, statt zu suchen.

                ## Das ist enthalten

                - Gestaltung auf Basis Ihres Logos und Ihrer Farben
                - Bis zu fünf Abschnitte, z. B. Start, Leistungen, Über mich, Referenzen, Kontakt
                - Kontaktformular mit Spamschutz
                - Impressum und Datenschutzerklärung eingebunden (Texte liefern Sie)
                - Grundlegende Suchmaschinen-Einstellungen

                ## So läuft es ab

                1. Wir klären in einem kurzen Termin Ziel, Inhalte und Stil.
                2. Ich baue die Seite und schicke Ihnen einen Vorschau-Link.
                3. Nach einer Korrekturrunde geht die Seite online.

                ## Nicht enthalten

                - Texte, Fotos und Logo. Sie liefern die Inhalte.
                - Hosting und Domain. Sie laufen auf Ihren Namen.

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer. Eine Korrekturrunde ist enthalten.
                MD,
        ],
        'en' => [
            'slug' => 'one-page-website',
            'title' => 'One-page website',
            'teaser' => 'A lean website on a single page: who you are, what you offer, how to reach you.',
            'meta' => 'One-page website at a fixed price: services, about, contact and legal pages on one page. For freelancers, mobile-ready, in your name.',
            'summary' => 'A one-pager is a website on a single page. It gets up to five sections, a contact form and the legal pages. It is online after about two weeks.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 page, up to 5 sections'],
                ['label' => 'Duration', 'value' => 'about 2 weeks from content'],
                ['label' => 'Effort', 'value' => '16 hours'],
                ['label' => 'Result', 'value' => 'Finished website with logins'],
            ],
            'faq' => [
                ['q' => 'Who is a one-pager for?', 'a' => 'Freelancers and small businesses with a clear offer. If you need to explain many services, the business website fits better.'],
                ['q' => 'Can I add pages later?', 'a' => 'Yes. The one-pager can grow, and more pages can be added at any time.'],
                ['q' => 'Which system is used?', 'a' => 'Usually WordPress, so you can edit the copy yourself. Other wishes are agreed beforehand.'],
            ],
            'body' => <<<'MD'
                A one-pager shows everything important on one page. Visitors scroll instead of searching.

                ## What is included

                - Design based on your logo and colours
                - Up to five sections, e.g. home, services, about, references, contact
                - Contact form with spam protection
                - Legal notice and privacy policy added (you supply the texts)
                - Basic search engine settings

                ## How it works

                1. In a short call we agree on goal, content and style.
                2. I build the page and send you a preview link.
                3. After one round of corrections the page goes live.

                ## Not included

                - Copy, photos and logo. You supply the content.
                - Hosting and domain. They run in your name.

                ## Good to know

                Fixed price including 19 % VAT. One round of corrections is included.
                MD,
        ],
    ],
    [
        'category' => 'webauftritt', 'tags' => 'website,firmenwebsite', 'hours' => 32, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Firmenwebsite mit mehreren Unterseiten',
        'prompt' => 'a small company website with five page tabs fanned out like paper sheets above a laptop',
        'de' => [
            'slug' => 'firmenwebsite-bis-5-seiten',
            'title' => 'Firmenwebsite bis 5 Seiten',
            'teaser' => 'Eine vollständige Website für Ihren Betrieb mit bis zu fünf Unterseiten, die Sie selbst pflegen können.',
            'meta' => 'Firmenwebsite zum Festpreis: bis zu 5 Unterseiten, Kontaktformular, Rechtstexte und SEO-Grundlagen. Selbst pflegbar, mobil optimiert, in Ihrem Namen.',
            'summary' => 'Die Firmenwebsite hat bis zu fünf Unterseiten, etwa Start, Leistungen, Über uns, Referenzen und Kontakt. Sie ist selbst pflegbar und nach etwa vier Wochen online.',
            'facts' => [
                ['label' => 'Umfang', 'value' => 'bis zu 5 Unterseiten'],
                ['label' => 'Dauer', 'value' => 'ca. 4 Wochen ab Inhalten'],
                ['label' => 'Aufwand', 'value' => '32 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Website mit Einweisung'],
            ],
            'faq' => [
                ['q' => 'Kann ich die Texte selbst ändern?', 'a' => 'Ja. Zum Abschluss zeige ich Ihnen in einer kurzen Einweisung, wie das geht.'],
                ['q' => 'Was, wenn ich mehr als fünf Seiten brauche?', 'a' => 'Weitere Seiten kosten nach Aufwand. Den Betrag nenne ich Ihnen vorher.'],
                ['q' => 'Ist die Website für Google vorbereitet?', 'a' => 'Ja. Seitentitel, Beschreibungen, Sitemap und die Anmeldung in der Google Search Console sind dabei.'],
            ],
            'body' => <<<'MD'
                Für Betriebe, die mehrere Leistungen zeigen wollen und eine Website brauchen, die sie selbst pflegen können.

                ## Das ist enthalten

                - Gestaltung passend zu Logo und Farben
                - Bis zu fünf Unterseiten mit Navigation
                - Kontaktformular mit Spamschutz
                - Rechtstexte eingebunden (Texte liefern Sie)
                - Seitentitel, Beschreibungen, Sitemap und Search Console
                - Einweisung zur Pflege (30 Minuten per Video)

                ## So läuft es ab

                1. Wir legen Seitenstruktur und Inhalte fest.
                2. Ich baue die Website und schicke Ihnen einen Vorschau-Link.
                3. Nach zwei Korrekturrunden geht die Website online.

                ## Nicht enthalten

                - Texte, Fotos und Logo
                - Hosting, Domain und kostenpflichtige Erweiterungen

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer. Zwei Korrekturrunden sind enthalten.
                MD,
        ],
        'en' => [
            'slug' => 'business-website-up-to-5-pages',
            'title' => 'Business website up to 5 pages',
            'teaser' => 'A complete website for your business with up to five pages that you can maintain yourself.',
            'meta' => 'Business website at a fixed price: up to 5 pages, contact form, legal pages and SEO basics. Easy to maintain, mobile-ready, in your name.',
            'summary' => 'The business website has up to five pages, such as home, services, about, references and contact. You can maintain it yourself, and it is online after about four weeks.',
            'facts' => [
                ['label' => 'Scope', 'value' => 'up to 5 pages'],
                ['label' => 'Duration', 'value' => 'about 4 weeks from content'],
                ['label' => 'Effort', 'value' => '32 hours'],
                ['label' => 'Result', 'value' => 'Website with a handover session'],
            ],
            'faq' => [
                ['q' => 'Can I change the copy myself?', 'a' => 'Yes. At the end I show you how in a short handover session.'],
                ['q' => 'What if I need more than five pages?', 'a' => 'Extra pages are billed by effort. I tell you the amount beforehand.'],
                ['q' => 'Is the website ready for Google?', 'a' => 'Yes. Page titles, descriptions, a sitemap and Google Search Console setup are included.'],
            ],
            'body' => <<<'MD'
                For businesses that want to present several services and need a website they can maintain themselves.

                ## What is included

                - Design matching your logo and colours
                - Up to five pages with navigation
                - Contact form with spam protection
                - Legal pages added (you supply the texts)
                - Page titles, descriptions, sitemap and Search Console
                - Handover session on maintenance (30 minutes by video)

                ## How it works

                1. We define the page structure and content.
                2. I build the website and send you a preview link.
                3. After two rounds of corrections the website goes live.

                ## Not included

                - Copy, photos and logo
                - Hosting, domain and paid add-ons

                ## Good to know

                Fixed price including 19 % VAT. Two rounds of corrections are included.
                MD,
        ],
    ],
    [
        'category' => 'webauftritt', 'tags' => 'landingpage,kampagne', 'hours' => 12, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Landingpage mit einem klaren Aufruf',
        'prompt' => 'a single focused landing page with one big call-to-action button glowing softly',
        'de' => [
            'slug' => 'landingpage-fuer-ein-angebot',
            'title' => 'Landingpage für ein Angebot',
            'teaser' => 'Eine Seite für genau ein Angebot oder eine Aktion, gebaut für Anfragen.',
            'meta' => 'Landingpage zum Festpreis: eine Seite für ein Angebot, eine Aktion oder eine Anzeige. Klarer Aufbau, Anfrageformular und Messung der Besuche.',
            'summary' => 'Eine Landingpage stellt ein einzelnes Angebot vor und führt zu einer Anfrage. Sie passt zu Anzeigen, Aktionen oder einer neuen Leistung und ist nach etwa einer Woche fertig.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Seite für 1 Angebot'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche ab Inhalten'],
                ['label' => 'Aufwand', 'value' => '12 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Seite mit Anfrageformular'],
            ],
            'faq' => [
                ['q' => 'Brauche ich dafür eine bestehende Website?', 'a' => 'Nein. Die Landingpage kann für sich stehen oder in Ihre Website eingebunden werden.'],
                ['q' => 'Messen Sie, wie viele Anfragen kommen?', 'a' => 'Ja, datenschutzfreundlich und nur mit Einwilligung, wo sie nötig ist.'],
                ['q' => 'Schreiben Sie auch die Texte?', 'a' => 'Ich helfe beim Aufbau und schärfe Ihre Texte. Fertige Werbetexte sind nicht enthalten.'],
            ],
            'body' => <<<'MD'
                Eine Seite, ein Angebot, ein Ziel: die Anfrage. Ideal für Anzeigen und Aktionen.

                ## Das ist enthalten

                - Aufbau nach dem Muster Problem, Angebot, Nutzen, Aufruf
                - Anfrageformular mit Spamschutz
                - Messung der Anfragen, datenschutzfreundlich
                - Einbindung in Ihre Website oder eigene Adresse

                ## So läuft es ab

                1. Wir klären Angebot, Zielgruppe und gewünschte Aktion.
                2. Ich baue die Seite und schicke einen Vorschau-Link.
                3. Nach einer Korrekturrunde geht sie online.

                ## Nicht enthalten

                - Anzeigenschaltung und Werbebudget
                - Fotos und fertige Werbetexte

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer. Eine Korrekturrunde ist enthalten.
                MD,
        ],
        'en' => [
            'slug' => 'landing-page-for-one-offer',
            'title' => 'Landing page for one offer',
            'teaser' => 'One page for exactly one offer or campaign, built to generate enquiries.',
            'meta' => 'Landing page at a fixed price: one page for an offer, a campaign or an ad. Clear structure, enquiry form and visit tracking included.',
            'summary' => 'A landing page presents a single offer and leads to an enquiry. It suits ads, campaigns or a new service and is ready in about one week.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 page for 1 offer'],
                ['label' => 'Duration', 'value' => 'about 1 week from content'],
                ['label' => 'Effort', 'value' => '12 hours'],
                ['label' => 'Result', 'value' => 'Page with enquiry form'],
            ],
            'faq' => [
                ['q' => 'Do I need an existing website?', 'a' => 'No. The landing page can stand alone or sit inside your website.'],
                ['q' => 'Do you track how many enquiries come in?', 'a' => 'Yes, privacy-friendly and only with consent where required.'],
                ['q' => 'Do you write the copy?', 'a' => 'I help with the structure and sharpen your copy. Finished ad copy is not included.'],
            ],
            'body' => <<<'MD'
                One page, one offer, one goal: the enquiry. Ideal for ads and campaigns.

                ## What is included

                - Structure following problem, offer, benefit, call to action
                - Enquiry form with spam protection
                - Privacy-friendly enquiry tracking
                - Integrated into your website or on its own address

                ## How it works

                1. We clarify the offer, audience and desired action.
                2. I build the page and send a preview link.
                3. After one round of corrections it goes live.

                ## Not included

                - Running ads and ad budget
                - Photos and finished ad copy

                ## Good to know

                Fixed price including 19 % VAT. One round of corrections is included.
                MD,
        ],
    ],
    [
        'category' => 'webauftritt', 'tags' => 'relaunch,konzept', 'hours' => 8, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Konzept für den Relaunch einer Website',
        'prompt' => 'an old website wireframe transforming into a clean new wireframe, with a sitemap diagram between them',
        'de' => [
            'slug' => 'relaunch-konzept',
            'title' => 'Relaunch-Konzept für Ihre Website',
            'teaser' => 'Bevor neu gebaut wird: ein Plan, was bleibt, was geht und was neu kommt.',
            'meta' => 'Relaunch-Konzept zum Festpreis: Analyse Ihrer Website, neue Seitenstruktur, Weiterleitungsplan und Kostenrahmen, damit beim Neubau nichts verloren geht.',
            'summary' => 'Das Relaunch-Konzept prüft Ihre bestehende Website und plant die neue. Sie bekommen Seitenstruktur, Weiterleitungsplan und Kostenrahmen, damit Besucher und Google-Platzierungen erhalten bleiben.',
            'facts' => [
                ['label' => 'Umfang', 'value' => 'Analyse und Plan, kein Neubau'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche'],
                ['label' => 'Aufwand', 'value' => '8 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Schriftliches Konzept (PDF)'],
            ],
            'faq' => [
                ['q' => 'Warum ein Konzept vor dem Relaunch?', 'a' => 'Ohne Plan gehen beim Neubau oft Google-Platzierungen verloren. Das Konzept verhindert das.'],
                ['q' => 'Muss ich den Neubau bei Ihnen beauftragen?', 'a' => 'Nein. Das Konzept können Sie auch mit jemand anderem umsetzen.'],
                ['q' => 'Was ist ein Weiterleitungsplan?', 'a' => 'Eine Liste, welche alte Adresse auf welche neue zeigt. So landen Besucher und Google nicht auf Fehlerseiten.'],
            ],
            'body' => <<<'MD'
                Ein Relaunch ohne Plan kostet oft Besucher. Dieses Konzept sorgt dafür, dass der Neubau besser wird als das Alte.

                ## Das ist enthalten

                - Bestandsaufnahme: Seiten, Inhalte, Besucherzahlen, Google-Platzierungen
                - Neue Seitenstruktur mit Begründung
                - Weiterleitungsplan von alten auf neue Adressen
                - Kostenrahmen für den Neubau

                ## So läuft es ab

                1. Sie geben mir Zugang zu Website und, falls vorhanden, Statistik.
                2. Ich analysiere und erstelle das Konzept.
                3. Wir besprechen es in einem Abschlusstermin.

                ## Nicht enthalten

                - Der Neubau selbst
                - Texte für die neuen Seiten

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'website-relaunch-plan',
            'title' => 'Relaunch plan for your website',
            'teaser' => 'Before rebuilding: a plan for what stays, what goes and what is new.',
            'meta' => 'Website relaunch plan at a fixed price: analysis, new page structure, redirect plan and cost frame, so nothing gets lost in the rebuild.',
            'summary' => 'The relaunch plan reviews your current website and plans the new one. You get a page structure, a redirect plan and a cost frame, so visitors and Google rankings are kept.',
            'facts' => [
                ['label' => 'Scope', 'value' => 'Analysis and plan, no rebuild'],
                ['label' => 'Duration', 'value' => 'about 1 week'],
                ['label' => 'Effort', 'value' => '8 hours'],
                ['label' => 'Result', 'value' => 'Written plan (PDF)'],
            ],
            'faq' => [
                ['q' => 'Why plan before a relaunch?', 'a' => 'Without a plan a rebuild often loses Google rankings. The plan prevents that.'],
                ['q' => 'Do I have to commission the rebuild from you?', 'a' => 'No. You can carry out the plan with someone else.'],
                ['q' => 'What is a redirect plan?', 'a' => 'A list of which old address points to which new one. Visitors and Google never land on error pages.'],
            ],
            'body' => <<<'MD'
                A relaunch without a plan often costs visitors. This plan makes sure the rebuild beats the old site.

                ## What is included

                - Review: pages, content, visitor numbers, Google rankings
                - New page structure with reasoning
                - Redirect plan from old to new addresses
                - Cost frame for the rebuild

                ## How it works

                1. You give me access to the website and, if available, analytics.
                2. I analyse and write the plan.
                3. We discuss it in a closing call.

                ## Not included

                - The rebuild itself
                - Copy for the new pages

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'webauftritt', 'tags' => 'mehrsprachig,englisch', 'hours' => 10, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Website mit Sprachumschalter Deutsch und Englisch',
        'prompt' => 'a website with a language switch toggling between two speech bubbles, one German flag colours and one British',
        'de' => [
            'slug' => 'zweite-sprache-fuer-ihre-website',
            'title' => 'Zweite Sprache für Ihre Website',
            'teaser' => 'Ihre Website zusätzlich auf Englisch oder einer anderen Sprache, sauber für Google eingerichtet.',
            'meta' => 'Zweite Sprache für Ihre Website: Sprachumschalter, übersetzte Seiten und korrekte hreflang-Angaben, damit Google jede Sprache richtig ausspielt.',
            'summary' => 'Ich richte eine zweite Sprache auf Ihrer bestehenden Website ein, mit Umschalter und eigenen Adressen je Sprache. Google erkennt so, welche Seite für wen gedacht ist.',
            'facts' => [
                ['label' => 'Umfang', 'value' => 'bis zu 10 Seiten'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche ab Übersetzungen'],
                ['label' => 'Aufwand', 'value' => '10 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Zweisprachige Website'],
            ],
            'faq' => [
                ['q' => 'Übersetzen Sie die Texte?', 'a' => 'Nein, die Übersetzung liefern Sie oder ein Übersetzungsbüro. Auf Wunsch erstelle ich eine maschinelle Rohfassung zum Prüfen.'],
                ['q' => 'Was ist hreflang?', 'a' => 'Eine Angabe für Suchmaschinen, welche Seite die Übersetzung welcher anderen ist. Ohne sie zeigt Google oft die falsche Sprache.'],
                ['q' => 'Funktioniert das mit meinem System?', 'a' => 'Mit WordPress und den meisten Baukästen ja. Bei anderen Systemen fragen Sie bitte vorher an.'],
            ],
            'body' => <<<'MD'
                Für Betriebe mit internationalen Kunden oder Gästen: Ihre Website spricht zwei Sprachen.

                ## Das ist enthalten

                - Sprachumschalter auf jeder Seite
                - Eigene Adressen je Sprache, z. B. /en/
                - hreflang-Angaben für Google
                - Einpflegen der Übersetzungen für bis zu zehn Seiten

                ## So läuft es ab

                1. Wir legen fest, welche Seiten übersetzt werden.
                2. Sie liefern die Übersetzungen.
                3. Ich richte alles ein und prüfe die Verknüpfungen.

                ## Nicht enthalten

                - Die Übersetzung selbst
                - Lizenzkosten für Übersetzungs-Plugins

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'second-language-for-your-website',
            'title' => 'Second language for your website',
            'teaser' => 'Your website also in English or another language, set up properly for Google.',
            'meta' => 'Second language for your website: language switch, translated pages and correct hreflang tags so Google serves each language to the right people.',
            'summary' => 'I add a second language to your existing website, with a switch and separate addresses per language. Google can then tell which page is meant for whom.',
            'facts' => [
                ['label' => 'Scope', 'value' => 'up to 10 pages'],
                ['label' => 'Duration', 'value' => 'about 1 week from translations'],
                ['label' => 'Effort', 'value' => '10 hours'],
                ['label' => 'Result', 'value' => 'Bilingual website'],
            ],
            'faq' => [
                ['q' => 'Do you translate the copy?', 'a' => 'No, you or a translation agency supply it. On request I prepare a machine draft for you to check.'],
                ['q' => 'What is hreflang?', 'a' => 'A hint for search engines saying which page translates which. Without it Google often shows the wrong language.'],
                ['q' => 'Does it work with my system?', 'a' => 'With WordPress and most website builders, yes. For other systems, please ask beforehand.'],
            ],
            'body' => <<<'MD'
                For businesses with international customers or guests: your website speaks two languages.

                ## What is included

                - Language switch on every page
                - Separate addresses per language, e.g. /en/
                - hreflang tags for Google
                - Adding the translations for up to ten pages

                ## How it works

                1. We decide which pages are translated.
                2. You supply the translations.
                3. I set everything up and check the links between languages.

                ## Not included

                - The translation itself
                - Licence costs for translation plugins

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'webauftritt', 'tags' => 'kontaktformular,spamschutz', 'hours' => 3, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Kontaktformular mit Schutz vor Spam',
        'prompt' => 'a clean contact form with a small shield icon blocking a swarm of tiny spam envelopes',
        'de' => [
            'slug' => 'kontaktformular-mit-spamschutz',
            'title' => 'Kontaktformular mit Spamschutz',
            'teaser' => 'Ein Kontaktformular, das ankommt, keinen Spam durchlässt und datenschutzfreundlich ist.',
            'meta' => 'Kontaktformular für Ihre Website: zuverlässiger Versand, Spamschutz ohne Google-Captcha, Bestätigungsmail und Datenschutzhinweis. Festpreis.',
            'summary' => 'Ich baue ein Kontaktformular in Ihre Website ein, das zuverlässig zustellt und Spam abfängt. Ohne Google-Captcha, mit Bestätigungsmail an den Absender.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Formular, bis zu 8 Felder'],
                ['label' => 'Dauer', 'value' => '2–3 Werktage'],
                ['label' => 'Aufwand', 'value' => '3 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Getestetes Formular'],
            ],
            'faq' => [
                ['q' => 'Warum kommen meine Formular-Mails nicht an?', 'a' => 'Oft fehlt ein richtig eingerichteter Mailversand. Ich stelle auf einen sauberen Versand über Ihr Postfach um.'],
                ['q' => 'Brauche ich ein Captcha?', 'a' => 'Nein. Ich setze unsichtbare Prüfungen ein, die Besucher nicht stören.'],
                ['q' => 'Ist das datenschutzkonform?', 'a' => 'Das Formular erhebt nur nötige Daten und verweist auf Ihre Datenschutzerklärung. Deren Inhalt verantworten Sie.'],
            ],
            'body' => <<<'MD'
                Ein Formular, das keine Anfrage verliert und keinen Spam durchlässt.

                ## Das ist enthalten

                - Formular mit bis zu acht Feldern
                - Spamschutz ohne Google-Captcha
                - Versand über Ihr Postfach, mit Bestätigung an den Absender
                - Test mit mehreren Mailanbietern

                ## So läuft es ab

                1. Sie nennen die Felder und die Empfängeradresse.
                2. Ich baue und teste das Formular.
                3. Sie bekommen eine Testanfrage zur Kontrolle.

                ## Nicht enthalten

                - Anbindung an CRM oder Ticketsystem (siehe „Anfragen automatisch als Ticket“)

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'contact-form-with-spam-protection',
            'title' => 'Contact form with spam protection',
            'teaser' => 'A contact form that arrives, keeps spam out and respects privacy.',
            'meta' => 'Contact form for your website: reliable delivery, spam protection without Google captcha, confirmation email and privacy notice. Fixed price.',
            'summary' => 'I add a contact form to your website that delivers reliably and catches spam. No Google captcha, with a confirmation email to the sender.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 form, up to 8 fields'],
                ['label' => 'Duration', 'value' => '2–3 working days'],
                ['label' => 'Effort', 'value' => '3 hours'],
                ['label' => 'Result', 'value' => 'Tested form'],
            ],
            'faq' => [
                ['q' => 'Why do my form emails not arrive?', 'a' => 'Often proper mail sending is missing. I switch to clean sending through your mailbox.'],
                ['q' => 'Do I need a captcha?', 'a' => 'No. I use invisible checks that do not bother visitors.'],
                ['q' => 'Is it privacy-compliant?', 'a' => 'The form only collects what is needed and links to your privacy policy. Its content is your responsibility.'],
            ],
            'body' => <<<'MD'
                A form that loses no enquiry and lets no spam through.

                ## What is included

                - Form with up to eight fields
                - Spam protection without Google captcha
                - Sending through your mailbox, with a confirmation to the sender
                - Tested with several mail providers

                ## How it works

                1. You name the fields and the recipient address.
                2. I build and test the form.
                3. You receive a test enquiry to check.

                ## Not included

                - Connection to a CRM or ticket system (see "Enquiries as tickets")

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'webauftritt', 'tags' => 'terminbuchung,kalender', 'hours' => 4, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Online-Terminbuchung auf einer Website',
        'prompt' => 'a website calendar widget with open time slots, a hand tapping one slot on a phone',
        'de' => [
            'slug' => 'online-terminbuchung-einbinden',
            'title' => 'Online-Terminbuchung einbinden',
            'teaser' => 'Kunden buchen Termine direkt auf Ihrer Website, Ihr Kalender bleibt aktuell.',
            'meta' => 'Online-Terminbuchung für Ihre Website: Buchungstool auswählen, einrichten und mit Ihrem Kalender verbinden. Weniger Telefonate, weniger Absprachen.',
            'summary' => 'Ich richte ein Buchungstool ein und binde es in Ihre Website ein. Kunden sehen freie Zeiten und buchen selbst, der Termin landet in Ihrem Kalender.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Buchungstool, bis zu 5 Terminarten'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche'],
                ['label' => 'Aufwand', 'value' => '4 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Buchung auf der Website'],
            ],
            'faq' => [
                ['q' => 'Welches Buchungstool nehmen Sie?', 'a' => 'Eines, das zu Ihrem Kalender und Budget passt, etwa Calendly, Cal.com oder ein WordPress-Plugin.'],
                ['q' => 'Fallen laufende Kosten an?', 'a' => 'Je nach Tool ja. Ich nenne sie vorher.'],
                ['q' => 'Bekommen Kunden eine Erinnerung?', 'a' => 'Ja, wenn das Tool es kann. Ich richte Bestätigung und Erinnerung per E-Mail ein.'],
            ],
            'body' => <<<'MD'
                Schluss mit Telefon-Pingpong: Kunden buchen selbst, wann es Ihnen passt.

                ## Das ist enthalten

                - Auswahl eines passenden Buchungstools
                - Bis zu fünf Terminarten mit Dauer und Pufferzeiten
                - Verbindung mit Ihrem Kalender
                - Einbindung auf Ihrer Website
                - Bestätigung und Erinnerung per E-Mail

                ## So läuft es ab

                1. Wir klären Terminarten und Verfügbarkeiten.
                2. Ich richte das Tool ein und binde es ein.
                3. Sie buchen einen Testtermin zur Kontrolle.

                ## Nicht enthalten

                - Lizenzkosten des Buchungstools
                - Online-Zahlung bei der Buchung

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'online-booking-on-your-website',
            'title' => 'Online booking on your website',
            'teaser' => 'Customers book appointments right on your website, and your calendar stays up to date.',
            'meta' => 'Online booking for your website: choose a booking tool, set it up and connect it to your calendar. Fewer calls, less back and forth.',
            'summary' => 'I set up a booking tool and add it to your website. Customers see free slots and book themselves, and the appointment lands in your calendar.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 booking tool, up to 5 appointment types'],
                ['label' => 'Duration', 'value' => 'about 1 week'],
                ['label' => 'Effort', 'value' => '4 hours'],
                ['label' => 'Result', 'value' => 'Booking on your website'],
            ],
            'faq' => [
                ['q' => 'Which booking tool do you use?', 'a' => 'One that suits your calendar and budget, such as Calendly, Cal.com or a WordPress plugin.'],
                ['q' => 'Are there running costs?', 'a' => 'Depending on the tool, yes. I name them beforehand.'],
                ['q' => 'Do customers get a reminder?', 'a' => 'Yes, if the tool supports it. I set up confirmation and reminder emails.'],
            ],
            'body' => <<<'MD'
                No more phone tag: customers book themselves, at times that suit you.

                ## What is included

                - Choosing a suitable booking tool
                - Up to five appointment types with duration and buffers
                - Connection to your calendar
                - Embedding on your website
                - Confirmation and reminder emails

                ## How it works

                1. We clarify appointment types and availability.
                2. I set up the tool and embed it.
                3. You book a test appointment to check.

                ## Not included

                - Licence costs of the booking tool
                - Online payment at booking

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'webauftritt', 'tags' => 'referenzen,galerie', 'hours' => 4, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Referenzseite mit Projektbildern',
        'prompt' => 'a portfolio grid of project photos arranged neatly on a web page, one tile enlarged',
        'de' => [
            'slug' => 'referenzseite-anlegen',
            'title' => 'Referenzseite anlegen',
            'teaser' => 'Eine Seite, die Ihre Arbeiten zeigt: Projekte, Bilder und Kundenstimmen, übersichtlich aufbereitet.',
            'meta' => 'Referenzseite für Ihre Website: Projekte mit Bildern, kurzen Beschreibungen und Kundenstimmen. Schnell ladend, mobil optimiert, zum Festpreis.',
            'summary' => 'Ich lege eine Referenzseite mit bis zu zwölf Projekten an. Bilder werden für schnelles Laden optimiert, und Sie können weitere Projekte selbst ergänzen.',
            'facts' => [
                ['label' => 'Umfang', 'value' => 'bis zu 12 Projekte'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche ab Material'],
                ['label' => 'Aufwand', 'value' => '4 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Referenzseite, selbst erweiterbar'],
            ],
            'faq' => [
                ['q' => 'Darf ich Kundennamen zeigen?', 'a' => 'Nur mit Einverständnis der Kunden. Ohne Freigabe zeigen wir die Projekte anonym.'],
                ['q' => 'Werden die Bilder verkleinert?', 'a' => 'Ja. Ich optimiere sie, damit die Seite auch mobil schnell lädt.'],
                ['q' => 'Kann ich später selbst Projekte ergänzen?', 'a' => 'Ja. Ich zeige Ihnen, wie ein neues Projekt angelegt wird.'],
            ],
            'body' => <<<'MD'
                Zeigen statt erzählen: Eine Referenzseite überzeugt neue Kunden mit echten Arbeiten.

                ## Das ist enthalten

                - Seite mit bis zu zwölf Projekten
                - Bildoptimierung für schnelles Laden
                - Kurze Beschreibung und Kundenstimme je Projekt
                - Anleitung zum Ergänzen weiterer Projekte

                ## So läuft es ab

                1. Sie schicken Bilder und Stichpunkte zu den Projekten.
                2. Ich lege die Seite an.
                3. Nach einer Korrekturrunde geht sie online.

                ## Nicht enthalten

                - Fotografie
                - Einholen von Kundenfreigaben

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'portfolio-page-setup',
            'title' => 'Portfolio page setup',
            'teaser' => 'A page that shows your work: projects, images and testimonials, clearly presented.',
            'meta' => 'Portfolio page for your website: projects with images, short descriptions and testimonials. Fast loading, mobile-ready, at a fixed price.',
            'summary' => 'I create a portfolio page with up to twelve projects. Images are optimised for fast loading, and you can add more projects yourself.',
            'facts' => [
                ['label' => 'Scope', 'value' => 'up to 12 projects'],
                ['label' => 'Duration', 'value' => 'about 1 week from material'],
                ['label' => 'Effort', 'value' => '4 hours'],
                ['label' => 'Result', 'value' => 'Portfolio page you can extend'],
            ],
            'faq' => [
                ['q' => 'May I show client names?', 'a' => 'Only with the client\'s consent. Without it we show projects anonymously.'],
                ['q' => 'Are the images resized?', 'a' => 'Yes. I optimise them so the page loads quickly on phones too.'],
                ['q' => 'Can I add projects later?', 'a' => 'Yes. I show you how to add a new project.'],
            ],
            'body' => <<<'MD'
                Show, don't tell: a portfolio page convinces new customers with real work.

                ## What is included

                - Page with up to twelve projects
                - Image optimisation for fast loading
                - Short description and testimonial per project
                - Instructions for adding more projects

                ## How it works

                1. You send images and notes on the projects.
                2. I build the page.
                3. After one round of corrections it goes live.

                ## Not included

                - Photography
                - Obtaining client permissions

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
];
