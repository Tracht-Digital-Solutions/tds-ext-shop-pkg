<?php
declare(strict_types=1);

/**
 * Shop categories with names, intro and FAQ in German and English.
 * Written by `Support\CatalogueSeed::categories()`; empty fields only.
 */
return [
    [
        'slug' => 'webauftritt',
        'name_de' => 'Webauftritt',
        'name_en' => 'Websites',
        'intro_de' => 'Websites und Landingpages für Selbstständige und kleine Betriebe, zum Festpreis. Jedes Paket beschreibt genau, was Sie bekommen.',
        'intro_en' => 'Websites and landing pages for freelancers and small businesses, at a fixed price. Each package states exactly what you get.',
        'faq_de' => [
            ['q' => 'Wem gehört die Website danach?', 'a' => 'Ihnen. Sie bekommen alle Zugänge und können später jederzeit selbst oder mit jemand anderem weiterarbeiten.'],
            ['q' => 'Brauche ich eigene Texte und Bilder?', 'a' => 'Ja, Inhalte liefern Sie. Ich sage Ihnen vorher genau, was gebraucht wird, und helfe beim Ordnen.'],
            ['q' => 'Sind Hosting und Domain im Preis enthalten?', 'a' => 'Nein. Sie laufen auf Ihren Namen beim Anbieter Ihrer Wahl. Bei der Auswahl helfe ich gern.'],
        ],
        'faq_en' => [
            ['q' => 'Who owns the website afterwards?', 'a' => 'You do. You receive every login and can continue on your own or with someone else at any time.'],
            ['q' => 'Do I need my own copy and images?', 'a' => 'Yes, you supply the content. I tell you beforehand exactly what is needed and help you put it in order.'],
            ['q' => 'Are hosting and domain included?', 'a' => 'No. They run in your name with the provider of your choice. I am happy to help you choose.'],
        ],
    ],
    [
        'slug' => 'seo-sichtbarkeit',
        'name_de' => 'SEO & Sichtbarkeit',
        'name_en' => 'SEO & visibility',
        'intro_de' => 'Damit Kunden Sie finden: bei Google, auf Karten und in KI-Antworten. Klare Pakete statt monatlicher SEO-Verträge.',
        'intro_en' => 'So customers find you: on Google, on maps and in AI answers. Clear packages instead of monthly SEO contracts.',
        'faq_de' => [
            ['q' => 'Garantieren Sie Platz 1 bei Google?', 'a' => 'Nein. Niemand kann Rankings seriös garantieren. Ich beseitige, was Sie nachweislich bremst, und zeige, was es bewirkt.'],
            ['q' => 'Was ist GEO?', 'a' => 'Generative Engine Optimization: Ihre Seite so aufbereiten, dass KI-Assistenten wie ChatGPT oder Perplexity sie verstehen und als Quelle nennen.'],
            ['q' => 'Wie schnell sehe ich Ergebnisse?', 'a' => 'Technische Fehler wirken oft nach wenigen Wochen. Bessere Platzierungen brauchen meist zwei bis sechs Monate.'],
        ],
        'faq_en' => [
            ['q' => 'Do you guarantee the top spot on Google?', 'a' => 'No. Nobody can honestly guarantee rankings. I remove what demonstrably holds you back and show what it achieves.'],
            ['q' => 'What is GEO?', 'a' => 'Generative engine optimisation: preparing your site so AI assistants such as ChatGPT or Perplexity understand it and cite it as a source.'],
            ['q' => 'How soon will I see results?', 'a' => 'Technical fixes often show within a few weeks. Better rankings usually take two to six months.'],
        ],
    ],
    [
        'slug' => 'wartung-betrieb',
        'name_de' => 'Wartung & Betrieb',
        'name_en' => 'Maintenance & hosting',
        'intro_de' => 'Updates, Backups, Umzüge und Hilfe im Notfall. Damit Ihre Website sicher und erreichbar bleibt.',
        'intro_en' => 'Updates, backups, moves and help in an emergency. So your website stays secure and online.',
        'faq_de' => [
            ['q' => 'Für welche Systeme gelten die Pakete?', 'a' => 'Für WordPress und die gängigen Baukästen. Bei anderen Systemen fragen Sie bitte vorher kurz an.'],
            ['q' => 'Was passiert, wenn bei einem Update etwas kaputtgeht?', 'a' => 'Vor jedem Update gibt es eine Sicherung. Läuft etwas schief, stelle ich den alten Stand wieder her.'],
            ['q' => 'Brauche ich Zugang zu meinem Hoster?', 'a' => 'Ja. Für die meisten Arbeiten brauche ich einen Zugang zum Hosting oder zur Website-Verwaltung.'],
        ],
        'faq_en' => [
            ['q' => 'Which systems do the packages cover?', 'a' => 'WordPress and the common website builders. For other systems, please ask briefly beforehand.'],
            ['q' => 'What if an update breaks something?', 'a' => 'Every update starts with a backup. If something goes wrong, I restore the previous state.'],
            ['q' => 'Do I need access to my hosting?', 'a' => 'Yes. Most tasks need a login for your hosting or your website admin.'],
        ],
    ],
    [
        'slug' => 'recht-datenschutz',
        'name_de' => 'Recht & Barrierefreiheit',
        'name_en' => 'Compliance & accessibility',
        'intro_de' => 'Die technische Umsetzung von Impressum, Datenschutz, Cookie-Einwilligung und Barrierefreiheit. Keine Rechtsberatung, sondern saubere Technik.',
        'intro_en' => 'The technical side of legal notice, privacy, cookie consent and accessibility. Not legal advice, but clean implementation.',
        'faq_de' => [
            ['q' => 'Ist das eine Rechtsberatung?', 'a' => 'Nein. Ich setze technisch um. Die Rechtstexte selbst kommen von Ihnen, einem Generator oder Ihrem Anwalt.'],
            ['q' => 'Betrifft mich das Barrierefreiheitsstärkungsgesetz?', 'a' => 'Seit Juni 2025 gilt es für viele Online-Angebote an Verbraucher. Kleinstunternehmen mit Dienstleistungen sind teils ausgenommen. Im Zweifel klärt das ein Anwalt.'],
            ['q' => 'Welche Unterlagen brauchen Sie?', 'a' => 'Die Rechtstexte, die eingebunden werden sollen, und einen Zugang zu Ihrer Website.'],
        ],
        'faq_en' => [
            ['q' => 'Is this legal advice?', 'a' => 'No. I handle the technical implementation. The legal texts come from you, a generator or your lawyer.'],
            ['q' => 'Does the German Accessibility Act apply to me?', 'a' => 'Since June 2025 it applies to many online offerings for consumers. Micro-enterprises providing services are partly exempt. A lawyer can confirm your case.'],
            ['q' => 'What do you need from me?', 'a' => 'The legal texts to be added and access to your website.'],
        ],
    ],
    [
        'slug' => 'e-mail-domain',
        'name_de' => 'E-Mail & Domain',
        'name_en' => 'Email & domains',
        'intro_de' => 'Eigene Domain, professionelle E-Mail-Adressen und Postfächer, die zuverlässig ankommen. Eingerichtet und dokumentiert.',
        'intro_en' => 'Your own domain, professional email addresses and mailboxes that reliably arrive. Set up and documented.',
        'faq_de' => [
            ['q' => 'Sind Lizenzen und Domain-Gebühren enthalten?', 'a' => 'Nein. Sie schließen die Verträge selbst ab, damit alles auf Ihren Namen läuft. Ich richte ein.'],
            ['q' => 'Gehen beim Umzug E-Mails verloren?', 'a' => 'Nein. Ich übertrage die Postfächer und stelle erst um, wenn alles angekommen ist.'],
            ['q' => 'Funktioniert das auch auf dem Handy?', 'a' => 'Ja. Zum Abschluss richte ich die Postfächer auf Ihren Geräten ein oder zeige Ihnen, wie es geht.'],
        ],
        'faq_en' => [
            ['q' => 'Are licences and domain fees included?', 'a' => 'No. You sign the contracts yourself so everything runs in your name. I do the setup.'],
            ['q' => 'Will emails get lost in a move?', 'a' => 'No. I copy the mailboxes and only switch over once everything has arrived.'],
            ['q' => 'Does it work on my phone?', 'a' => 'Yes. At the end I set up the mailboxes on your devices or show you how.'],
        ],
    ],
    [
        'slug' => 'digitalisierung',
        'name_de' => 'Digitalisierung',
        'name_en' => 'Digitalisation',
        'intro_de' => 'Weniger Papier, weniger Abtippen, weniger Suchen. Einzelne Abläufe digital machen, Schritt für Schritt.',
        'intro_en' => 'Less paper, less retyping, less searching. Make single workflows digital, one step at a time.',
        'faq_de' => [
            ['q' => 'Welche Programme setzen Sie ein?', 'a' => 'Was zu Ihrem Betrieb passt, bevorzugt Werkzeuge, die Sie schon haben. Neue Programme schlage ich nur mit Begründung vor.'],
            ['q' => 'Fallen laufende Kosten an?', 'a' => 'Für manche Programme ja. Ich nenne sie vorher, damit Sie entscheiden können.'],
            ['q' => 'Was, wenn mein Ablauf nicht in ein Paket passt?', 'a' => 'Dann beginnen Sie mit der Ablauf-Analyse. Sie zeigt, was sich lohnt und was es kostet.'],
        ],
        'faq_en' => [
            ['q' => 'Which tools do you use?', 'a' => 'Whatever suits your business, preferably tools you already have. I only suggest new software with a reason.'],
            ['q' => 'Are there running costs?', 'a' => 'For some tools, yes. I name them beforehand so you can decide.'],
            ['q' => 'What if my workflow does not fit a package?', 'a' => 'Then start with the workflow analysis. It shows what pays off and what it costs.'],
        ],
    ],
    [
        'slug' => 'schulung',
        'name_de' => 'Schulungen',
        'name_en' => 'Training',
        'intro_de' => 'Praxisnahe Einweisungen für Sie und Ihr Team, per Video oder vor Ort. Mit Unterlagen zum Nachlesen.',
        'intro_en' => 'Practical training for you and your team, by video or on site. With notes to look things up later.',
        'faq_de' => [
            ['q' => 'Wie viele Personen können teilnehmen?', 'a' => 'Bis zu sechs Personen. Für größere Gruppen erstelle ich gern ein Angebot.'],
            ['q' => 'Online oder vor Ort?', 'a' => 'Beides. Vor Ort kommen Reisekosten hinzu, die wir vorher abstimmen.'],
            ['q' => 'Gibt es eine Aufzeichnung?', 'a' => 'Auf Wunsch ja, wenn alle Teilnehmenden zustimmen. Unterlagen bekommen Sie in jedem Fall.'],
        ],
        'faq_en' => [
            ['q' => 'How many people can take part?', 'a' => 'Up to six people. For larger groups I am happy to make an offer.'],
            ['q' => 'Online or on site?', 'a' => 'Both. On-site sessions add travel costs, agreed beforehand.'],
            ['q' => 'Is there a recording?', 'a' => 'On request, if all participants agree. You receive written notes in any case.'],
        ],
    ],
    [
        'slug' => 'leistungspakete',
        'name_de' => 'Leistungspakete',
        'name_en' => 'Service packages',
        'intro_de' => 'Checks und Analysen zum Festpreis: der klare Blick von außen, bevor Sie entscheiden.',
        'intro_en' => 'Checks and analyses at a fixed price: a clear outside view before you decide.',
        'faq_de' => [
            ['q' => 'Muss ich danach etwas beauftragen?', 'a' => 'Nein. Jedes Paket ist für sich abgeschlossen.'],
            ['q' => 'Wie schnell geht es los?', 'a' => 'Nach der Bestellung melde ich mich innerhalb von zwei Werktagen.'],
            ['q' => 'Wie wird bezahlt?', 'a' => 'Online beim Bestellen. Die Rechnung kommt per E-Mail.'],
        ],
        'faq_en' => [
            ['q' => 'Do I have to commission anything afterwards?', 'a' => 'No. Every package stands on its own.'],
            ['q' => 'How soon does it start?', 'a' => 'After your order I get in touch within two working days.'],
            ['q' => 'How do I pay?', 'a' => 'Online when ordering. The invoice arrives by email.'],
        ],
    ],
    // Affiliate categories. `netzwerk` and `peripherie` may already exist;
    // only empty fields are filled.
    [
        'slug' => 'netzwerk',
        'name_de' => 'Netzwerk & WLAN',
        'name_en' => 'Networking & Wi-Fi',
        'intro_de' => 'Router, Mesh-WLAN und Speicher fürs Büro, die ich selbst einsetze oder empfehle. Mit ehrlicher Einschätzung.',
        'intro_en' => 'Routers, mesh Wi-Fi and storage for the office that I use or recommend. With an honest assessment.',
        'faq_de' => [
            ['q' => 'Verdienen Sie an den Links?', 'a' => 'Ja, eine kleine Provision. Der Preis für Sie ändert sich dadurch nicht, und die Auswahl hängt nicht davon ab.'],
            ['q' => 'Warum steht kein Preis da?', 'a' => 'Partnerpreise zeige ich nur, wenn sie jünger als 24 Stunden sind. Den aktuellen Preis sehen Sie beim Händler.'],
            ['q' => 'Helfen Sie bei der Einrichtung?', 'a' => 'Ja, als eigene Leistung. Fragen Sie einfach an.'],
        ],
        'faq_en' => [
            ['q' => 'Do you earn from these links?', 'a' => 'Yes, a small commission. Your price does not change, and it does not decide the selection.'],
            ['q' => 'Why is there no price?', 'a' => 'I only show partner prices younger than 24 hours. The retailer shows the current price.'],
            ['q' => 'Can you help with the setup?', 'a' => 'Yes, as a separate service. Just ask.'],
        ],
    ],
    [
        'slug' => 'peripherie',
        'name_de' => 'Peripherie',
        'name_en' => 'Peripherals',
        'intro_de' => 'Monitore, Tastaturen, Docks und Kameras für einen Arbeitsplatz, an dem man gern sitzt.',
        'intro_en' => 'Monitors, keyboards, docks and cameras for a desk you enjoy working at.',
        'faq_de' => [
            ['q' => 'Verdienen Sie an den Links?', 'a' => 'Ja, eine kleine Provision. Der Preis für Sie ändert sich dadurch nicht.'],
            ['q' => 'Passt das zu meinem Laptop?', 'a' => 'Jede Seite nennt die Anschlüsse. Im Zweifel prüfen Sie die Angaben Ihres Laptops oder fragen kurz an.'],
            ['q' => 'Warum steht kein Preis da?', 'a' => 'Partnerpreise zeige ich nur, wenn sie jünger als 24 Stunden sind.'],
        ],
        'faq_en' => [
            ['q' => 'Do you earn from these links?', 'a' => 'Yes, a small commission. Your price does not change.'],
            ['q' => 'Will it work with my laptop?', 'a' => 'Each page lists the ports. If unsure, check your laptop specs or ask briefly.'],
            ['q' => 'Why is there no price?', 'a' => 'I only show partner prices younger than 24 hours.'],
        ],
    ],
    [
        'slug' => 'homeoffice',
        'name_de' => 'Homeoffice',
        'name_en' => 'Home office',
        'intro_de' => 'Headsets, Licht und Zubehör für klare Videocalls und konzentriertes Arbeiten zu Hause.',
        'intro_en' => 'Headsets, lighting and accessories for clear video calls and focused work at home.',
        'faq_de' => [
            ['q' => 'Verdienen Sie an den Links?', 'a' => 'Ja, eine kleine Provision. Der Preis für Sie ändert sich dadurch nicht.'],
            ['q' => 'Was bringt am meisten für Videocalls?', 'a' => 'Zuerst ein gutes Headset, dann Licht von vorn. Eine bessere Kamera kommt erst danach.'],
            ['q' => 'Warum steht kein Preis da?', 'a' => 'Partnerpreise zeige ich nur, wenn sie jünger als 24 Stunden sind.'],
        ],
        'faq_en' => [
            ['q' => 'Do you earn from these links?', 'a' => 'Yes, a small commission. Your price does not change.'],
            ['q' => 'What helps video calls most?', 'a' => 'A good headset first, then light from the front. A better camera comes last.'],
            ['q' => 'Why is there no price?', 'a' => 'I only show partner prices younger than 24 hours.'],
        ],
    ],
    [
        'slug' => 'unterwegs',
        'name_de' => 'Mobiles Arbeiten',
        'name_en' => 'Working on the go',
        'intro_de' => 'Laptop, Ladegerät und ein sicheres Netz: Ausstattung, mit der Sie im Zug, beim Kunden und im Hotel arbeiten wie im Büro.',
        'intro_en' => 'Laptop, charger and a safe network: kit that lets you work on the train, at a client and in a hotel as you would in the office.',
        'faq_de' => [
            ['q' => 'Verdienen Sie an den Links?', 'a' => 'Ja, eine kleine Provision. Der Preis für Sie ändert sich dadurch nicht.'],
            ['q' => 'Was brauche ich unterwegs zuerst?', 'a' => 'Ein Ladegerät für alle Geräte und eine sichere Verbindung statt offenem Hotel-WLAN.'],
            ['q' => 'Warum steht kein Preis da?', 'a' => 'Partnerpreise zeige ich nur, wenn sie jünger als 24 Stunden sind.'],
        ],
        'faq_en' => [
            ['q' => 'Do you earn from these links?', 'a' => 'Yes, a small commission. Your price does not change.'],
            ['q' => 'What do I need first on the go?', 'a' => 'One charger for every device and a secure connection instead of open hotel Wi-Fi.'],
            ['q' => 'Why is there no price?', 'a' => 'I only show partner prices younger than 24 hours.'],
        ],
    ],
    [
        'slug' => 'sicherheit',
        'name_de' => 'IT-Sicherheit',
        'name_en' => 'IT security',
        'intro_de' => 'Sicherheitsschlüssel, Backup-Speicher und Stromschutz: kleine Anschaffungen, die große Schäden verhindern.',
        'intro_en' => 'Security keys, backup storage and power protection: small purchases that prevent big damage.',
        'faq_de' => [
            ['q' => 'Verdienen Sie an den Links?', 'a' => 'Ja, eine kleine Provision. Der Preis für Sie ändert sich dadurch nicht.'],
            ['q' => 'Womit fange ich an?', 'a' => 'Mit einem Backup, das nicht dauerhaft am Rechner hängt. Danach schützt ein Sicherheitsschlüssel Ihre wichtigsten Konten.'],
            ['q' => 'Warum steht kein Preis da?', 'a' => 'Partnerpreise zeige ich nur, wenn sie jünger als 24 Stunden sind.'],
        ],
        'faq_en' => [
            ['q' => 'Do you earn from these links?', 'a' => 'Yes, a small commission. Your price does not change.'],
            ['q' => 'Where do I start?', 'a' => 'With a backup that is not permanently attached to your computer. Then a security key protects your most important accounts.'],
            ['q' => 'Why is there no price?', 'a' => 'I only show partner prices younger than 24 hours.'],
        ],
    ],
];
