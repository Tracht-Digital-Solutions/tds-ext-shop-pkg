<?php
declare(strict_types=1);

/**
 * Own products, category `digitalisierung`. Rate: Prozessoptimierung, 70 € net per hour.
 */
$image = 'https://tracht-digital.de/images/services/02-prozesse-800.webp';
$rate = 7000;

return [
    [
        'category' => 'digitalisierung', 'tags' => 'formulare,papierlos', 'hours' => 5, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Online-Formular ersetzt Papierformular',
        'prompt' => 'a paper form folding itself into a glowing tablet form, data flowing into a neat table',
        'de' => [
            'slug' => 'online-formular-statt-papier',
            'title' => 'Online-Formular statt Papier',
            'teaser' => 'Ein Papierformular wird digital: Kunden füllen online aus, die Daten landen geordnet bei Ihnen.',
            'meta' => 'Online-Formular statt Papier: ein bestehendes Formular digitalisieren, mit Pflichtfeldern, Datei-Upload und geordneter Ablage der Antworten. Zum Festpreis.',
            'summary' => 'Ich mache aus einem Ihrer Papierformulare ein Online-Formular. Kunden füllen es am Handy oder Computer aus, und die Antworten landen geordnet in Ihrem Postfach oder einer Tabelle.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Formular, bis zu 25 Felder'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche'],
                ['label' => 'Aufwand', 'value' => '5 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Getestetes Online-Formular'],
            ],
            'faq' => [
                ['q' => 'Welche Formulare eignen sich?', 'a' => 'Anmeldungen, Aufträge, Rückmeldungen, Schadensmeldungen: alles, was heute ausgedruckt, ausgefüllt und abgetippt wird.'],
                ['q' => 'Können Kunden Dateien anhängen?', 'a' => 'Ja, etwa Fotos oder PDFs, mit Größenbegrenzung.'],
                ['q' => 'Wo werden die Daten gespeichert?', 'a' => 'In Ihrem Postfach, einer Tabelle oder Ihrem System, bevorzugt bei Anbietern in der EU.'],
            ],
            'body' => <<<'MD'
                Kein Ausdrucken, kein Abtippen, keine unleserlichen Handschriften mehr.

                ## Das ist enthalten

                - Umsetzung eines Formulars mit bis zu 25 Feldern
                - Pflichtfelder, Prüfungen und Datei-Upload
                - Ablage der Antworten in Postfach oder Tabelle
                - Einbindung auf Ihrer Website oder als Link

                ## So läuft es ab

                1. Sie schicken das Papierformular.
                2. Ich baue die Online-Version und schicke einen Test-Link.
                3. Nach einer Korrekturrunde geht es live.

                ## Nicht enthalten

                - Anbindung an Fachsoftware
                - Lizenzkosten für Formular-Dienste

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'online-form-instead-of-paper',
            'title' => 'Online form instead of paper',
            'teaser' => 'A paper form goes digital: customers fill it in online, and the data reaches you in order.',
            'meta' => 'Online form instead of paper: digitise an existing form with required fields, file upload and orderly storage of the answers. At a fixed price.',
            'summary' => 'I turn one of your paper forms into an online form. Customers fill it in on phone or computer, and the answers arrive in order in your mailbox or a spreadsheet.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 form, up to 25 fields'],
                ['label' => 'Duration', 'value' => 'about 1 week'],
                ['label' => 'Effort', 'value' => '5 hours'],
                ['label' => 'Result', 'value' => 'Tested online form'],
            ],
            'faq' => [
                ['q' => 'Which forms are suitable?', 'a' => 'Registrations, orders, feedback, damage reports: anything that is printed, filled in and retyped today.'],
                ['q' => 'Can customers attach files?', 'a' => 'Yes, such as photos or PDFs, with a size limit.'],
                ['q' => 'Where is the data stored?', 'a' => 'In your mailbox, a spreadsheet or your system, preferably with providers in the EU.'],
            ],
            'body' => <<<'MD'
                No printing, no retyping, no illegible handwriting.

                ## What is included

                - One form with up to 25 fields
                - Required fields, checks and file upload
                - Answers stored in your mailbox or a spreadsheet
                - Embedded on your website or as a link

                ## How it works

                1. You send the paper form.
                2. I build the online version and send a test link.
                3. After one round of corrections it goes live.

                ## Not included

                - Connection to specialist software
                - Licence costs of form services

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'digitalisierung', 'tags' => 'rechnungen,buchhaltung,lexware', 'hours' => 5, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Digitale Rechnungen mit einer Buchhaltungssoftware',
        'prompt' => 'invoices flying from a laptop into neat digital folders, a calculator and a check mark',
        'de' => [
            'slug' => 'rechnungen-digital-schreiben',
            'title' => 'Rechnungen digital schreiben',
            'teaser' => 'Rechnungsprogramm einrichten, Vorlagen anlegen, E-Rechnung vorbereiten.',
            'meta' => 'Rechnungen digital: Rechnungsprogramm wie Lexware Office einrichten, Vorlagen und Kunden anlegen, E-Rechnung vorbereiten. Mit Einweisung, zum Festpreis.',
            'summary' => 'Ich richte ein Rechnungsprogramm für Ihren Betrieb ein, mit Vorlagen, Artikeln und Ihren Stammkunden. Auf den Empfang und Versand von E-Rechnungen sind Sie danach vorbereitet.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Programm, bis zu 50 Kunden'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche'],
                ['label' => 'Aufwand', 'value' => '5 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Eingerichtetes Rechnungsprogramm'],
            ],
            'faq' => [
                ['q' => 'Muss ich E-Rechnungen empfangen können?', 'a' => 'Ja. Seit 2025 müssen Unternehmen in Deutschland E-Rechnungen empfangen können. Für den Versand gelten Übergangsfristen.'],
                ['q' => 'Welches Programm empfehlen Sie?', 'a' => 'Für kleine Betriebe oft Lexware Office oder sevDesk. Wir wählen nach Ihren Bedürfnissen.'],
                ['q' => 'Ersetzt das meinen Steuerberater?', 'a' => 'Nein. Viele Programme lassen sich aber direkt mit dem Steuerberater verbinden.'],
            ],
            'body' => <<<'MD'
                Rechnungen in Word schreiben kostet Zeit und erfüllt bald nicht mehr die Pflichten.

                ## Das ist enthalten

                - Auswahl und Einrichtung eines Rechnungsprogramms
                - Rechnungsvorlage mit Logo und Pflichtangaben
                - Anlage von bis zu 50 Kunden und Ihren Leistungen
                - Vorbereitung für E-Rechnungen
                - Einweisung (30 Minuten per Video)

                ## So läuft es ab

                1. Wir wählen das passende Programm.
                2. Sie schließen den Vertrag ab und schicken Kundenliste und Leistungen.
                3. Ich richte alles ein und weise Sie ein.

                ## Nicht enthalten

                - Lizenzkosten
                - Steuerberatung und Buchführung

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'digital-invoicing-setup',
            'title' => 'Digital invoicing setup',
            'teaser' => 'Set up invoicing software, create templates, prepare for e-invoicing.',
            'meta' => 'Digital invoicing: set up software such as Lexware Office, create templates and customers, prepare for e-invoices. With a handover session, fixed price.',
            'summary' => 'I set up invoicing software for your business, with templates, items and your regular customers. Afterwards you are ready to receive and send e-invoices.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 program, up to 50 customers'],
                ['label' => 'Duration', 'value' => 'about 1 week'],
                ['label' => 'Effort', 'value' => '5 hours'],
                ['label' => 'Result', 'value' => 'Invoicing software ready to use'],
            ],
            'faq' => [
                ['q' => 'Do I have to receive e-invoices?', 'a' => 'Yes. Since 2025 businesses in Germany must be able to receive e-invoices. Transition periods apply to sending.'],
                ['q' => 'Which program do you recommend?', 'a' => 'For small businesses often Lexware Office or sevDesk. We choose by your needs.'],
                ['q' => 'Does this replace my accountant?', 'a' => 'No. But many programs connect directly to your accountant.'],
            ],
            'body' => <<<'MD'
                Writing invoices in Word takes time and will soon no longer meet the requirements.

                ## What is included

                - Choosing and setting up invoicing software
                - Invoice template with logo and required details
                - Up to 50 customers and your services entered
                - Preparation for e-invoices
                - Handover session (30 minutes by video)

                ## How it works

                1. We choose the right program.
                2. You sign up and send the customer list and services.
                3. I set everything up and walk you through it.

                ## Not included

                - Licence costs
                - Tax advice and bookkeeping

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'digitalisierung', 'tags' => 'ablage,dokumente,cloud', 'hours' => 6, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Digitale Ablagestruktur mit Ordnern',
        'prompt' => 'a messy pile of papers transforming into a clean tree of labelled digital folders',
        'de' => [
            'slug' => 'digitale-ablage-einrichten',
            'title' => 'Digitale Ablage einrichten',
            'teaser' => 'Eine Ordnerstruktur, in der jeder alles findet, mit Regeln für Namen und Freigaben.',
            'meta' => 'Digitale Ablage einrichten: Ordnerstruktur, Benennungsregeln und Freigaben in Ihrer Cloud. Damit Dokumente nicht mehr gesucht werden müssen. Festpreis.',
            'summary' => 'Ich entwerfe mit Ihnen eine Ordnerstruktur und Regeln für Dateinamen und richte beides in Ihrer Cloud ein. Freigaben legen fest, wer was sehen darf.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Ablage, bis zu 5 Personen'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche'],
                ['label' => 'Aufwand', 'value' => '6 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Struktur und einseitige Anleitung'],
            ],
            'faq' => [
                ['q' => 'Welche Cloud wird verwendet?', 'a' => 'Die, die Sie haben: OneDrive, Google Drive, Nextcloud oder ein Netzlaufwerk. Neue Dienste schlage ich nur mit Grund vor.'],
                ['q' => 'Sortieren Sie meine alten Dateien ein?', 'a' => 'Nein, das kostet nach Aufwand. Ich zeige Ihnen aber, wie es schnell geht.'],
                ['q' => 'Was ist mit Aufbewahrungsfristen?', 'a' => 'Die Struktur berücksichtigt sie. Die Fristen selbst klären Sie mit Ihrem Steuerberater.'],
            ],
            'body' => <<<'MD'
                Wer Dokumente sucht, verliert jeden Tag Zeit. Eine klare Ablage spart sie.

                ## Das ist enthalten

                - Entwurf einer Ordnerstruktur für Ihren Betrieb
                - Regeln für Dateinamen
                - Einrichtung mit Freigaben für bis zu fünf Personen
                - Einseitige Anleitung für das Team

                ## So läuft es ab

                1. Wir besprechen, welche Dokumente es gibt.
                2. Ich entwerfe die Struktur zur Freigabe.
                3. Ich richte sie ein und erkläre sie dem Team.

                ## Nicht enthalten

                - Einsortieren bestehender Dateien
                - Lizenz- und Speicherkosten

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'digital-filing-system-setup',
            'title' => 'Digital filing system setup',
            'teaser' => 'A folder structure where everyone finds everything, with rules for names and sharing.',
            'meta' => 'Digital filing system setup: folder structure, naming rules and sharing in your cloud, so documents no longer have to be searched for. Fixed price.',
            'summary' => 'Together we design a folder structure and file naming rules, and I set both up in your cloud. Sharing settings define who sees what.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 filing system, up to 5 people'],
                ['label' => 'Duration', 'value' => 'about 1 week'],
                ['label' => 'Effort', 'value' => '6 hours'],
                ['label' => 'Result', 'value' => 'Structure and one-page guide'],
            ],
            'faq' => [
                ['q' => 'Which cloud is used?', 'a' => 'The one you have: OneDrive, Google Drive, Nextcloud or a network drive. I only suggest new services with a reason.'],
                ['q' => 'Do you sort my old files?', 'a' => 'No, that is billed by effort. But I show you how to do it quickly.'],
                ['q' => 'What about retention periods?', 'a' => 'The structure takes them into account. Clarify the periods themselves with your accountant.'],
            ],
            'body' => <<<'MD'
                Searching for documents loses time every day. A clear filing system saves it.

                ## What is included

                - Designing a folder structure for your business
                - File naming rules
                - Setup with sharing for up to five people
                - One-page guide for the team

                ## How it works

                1. We discuss which documents exist.
                2. I design the structure for your approval.
                3. I set it up and explain it to the team.

                ## Not included

                - Sorting existing files
                - Licence and storage costs

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'digitalisierung', 'tags' => 'tickets,anfragen,automatisierung', 'hours' => 4, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Kontaktanfragen werden automatisch zu Tickets',
        'prompt' => 'website contact messages dropping into a tidy kanban board as numbered ticket cards',
        'de' => [
            'slug' => 'anfragen-automatisch-als-ticket',
            'title' => 'Anfragen automatisch als Ticket',
            'teaser' => 'Jede Anfrage über Website oder E-Mail wird ein Ticket. Nichts geht mehr unter.',
            'meta' => 'Anfragen automatisch als Ticket: Kontaktformular und Postfach mit einem Ticketsystem verbinden. Jede Anfrage nummeriert, zugewiesen und nachverfolgbar.',
            'summary' => 'Ich verbinde Ihr Kontaktformular und Ihr Postfach mit einem Ticketsystem. Jede Anfrage bekommt eine Nummer und einen Status, und Sie sehen, was noch offen ist.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Formular und 1 Postfach'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche'],
                ['label' => 'Aufwand', 'value' => '4 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Anfragen landen als Tickets'],
            ],
            'faq' => [
                ['q' => 'Welches Ticketsystem nutzen Sie?', 'a' => 'Eines, das zu Ihrer Größe passt, oft mit kostenloser Einstiegsstufe. Laufende Kosten nenne ich vorher.'],
                ['q' => 'Bekommt der Kunde eine Bestätigung?', 'a' => 'Ja, mit Ticketnummer. Antworten laufen weiter per E-Mail.'],
                ['q' => 'Lohnt sich das für einen kleinen Betrieb?', 'a' => 'Ab etwa zehn Anfragen pro Woche oder wenn mehrere Personen antworten, fast immer.'],
            ],
            'body' => <<<'MD'
                Anfragen im Postfach gehen unter. Tickets nicht.

                ## Das ist enthalten

                - Einrichtung eines Ticketsystems
                - Anbindung von Kontaktformular und einem Postfach
                - Automatische Bestätigung mit Ticketnummer
                - Status und Zuweisung für bis zu fünf Personen

                ## So läuft es ab

                1. Wir wählen das Ticketsystem.
                2. Ich richte es ein und verbinde Formular und Postfach.
                3. Wir testen mit einer echten Anfrage.

                ## Nicht enthalten

                - Lizenzkosten
                - Übernahme alter E-Mails als Tickets

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'enquiries-as-tickets',
            'title' => 'Enquiries as tickets',
            'teaser' => 'Every enquiry via website or email becomes a ticket. Nothing gets lost any more.',
            'meta' => 'Enquiries as tickets: connect your contact form and mailbox to a ticket system. Every enquiry numbered, assigned and trackable.',
            'summary' => 'I connect your contact form and mailbox to a ticket system. Every enquiry gets a number and a status, and you see what is still open.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 form and 1 mailbox'],
                ['label' => 'Duration', 'value' => 'about 1 week'],
                ['label' => 'Effort', 'value' => '4 hours'],
                ['label' => 'Result', 'value' => 'Enquiries arrive as tickets'],
            ],
            'faq' => [
                ['q' => 'Which ticket system do you use?', 'a' => 'One that fits your size, often with a free starter tier. I name running costs beforehand.'],
                ['q' => 'Does the customer get a confirmation?', 'a' => 'Yes, with a ticket number. Replies continue by email.'],
                ['q' => 'Is it worth it for a small business?', 'a' => 'From about ten enquiries a week, or when several people reply, almost always.'],
            ],
            'body' => <<<'MD'
                Enquiries in an inbox get lost. Tickets don't.

                ## What is included

                - Setting up a ticket system
                - Connecting the contact form and one mailbox
                - Automatic confirmation with a ticket number
                - Status and assignment for up to five people

                ## How it works

                1. We choose the ticket system.
                2. I set it up and connect form and mailbox.
                3. We test with a real enquiry.

                ## Not included

                - Licence costs
                - Importing old emails as tickets

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'digitalisierung', 'tags' => 'crm,kundendaten', 'hours' => 8, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Kundendaten an einem Ort zusammengeführt',
        'prompt' => 'scattered contact cards, spreadsheets and sticky notes merging into one clean customer database',
        'de' => [
            'slug' => 'kundendaten-zusammenfuehren',
            'title' => 'Kundendaten zusammenführen (CRM-Start)',
            'meta_title' => 'Kundendaten zusammenführen: CRM-Start',
            'teaser' => 'Kontakte aus Excel, Handy und Postfach an einem Ort, bereinigt und ohne Dubletten.',
            'meta' => 'Kundendaten zusammenführen: Kontakte aus Tabellen, Handy und Postfach in ein CRM übernehmen, bereinigen und Dubletten entfernen. Mit Einweisung, Festpreis.',
            'summary' => 'Ich führe Ihre Kundendaten aus Tabellen, Handy und Postfach in einem CRM zusammen und entferne Dubletten. Sie sehen danach jeden Kunden mit seiner Geschichte an einem Ort.',
            'facts' => [
                ['label' => 'Umfang', 'value' => 'bis zu 3 Quellen, 1.000 Kontakte'],
                ['label' => 'Dauer', 'value' => 'ca. 2 Wochen'],
                ['label' => 'Aufwand', 'value' => '8 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Bereinigtes CRM mit Einweisung'],
            ],
            'faq' => [
                ['q' => 'Was ist ein CRM?', 'a' => 'Ein Programm für Kundenbeziehungen: Kontakte, Gespräche, Angebote und Aufgaben an einem Ort.'],
                ['q' => 'Welches CRM empfehlen Sie?', 'a' => 'Für kleine Betriebe ein einfaches, oft mit kostenloser Stufe. Die Auswahl treffen wir gemeinsam.'],
                ['q' => 'Ist das datenschutzrechtlich in Ordnung?', 'a' => 'Ich achte auf Anbieter mit Vertrag zur Auftragsverarbeitung. Die rechtliche Prüfung liegt bei Ihnen.'],
            ],
            'body' => <<<'MD'
                Kundendaten an fünf Orten heißt: niemand weiß, was aktuell ist.

                ## Das ist enthalten

                - Auswahl und Einrichtung eines CRM
                - Übernahme aus bis zu drei Quellen, bis 1.000 Kontakte
                - Bereinigung und Entfernen von Dubletten
                - Einweisung (45 Minuten per Video)

                ## So läuft es ab

                1. Wir sichten die Quellen und wählen das CRM.
                2. Ich übernehme und bereinige die Daten.
                3. Sie prüfen Stichproben, dann folgt die Einweisung.

                ## Nicht enthalten

                - Lizenzkosten
                - Anbindung an Shop oder Buchhaltung

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'customer-data-consolidation',
            'title' => 'Customer data consolidation (CRM start)',
            'meta_title' => 'Customer data consolidation: CRM start',
            'teaser' => 'Contacts from Excel, phone and inbox in one place, cleaned up and without duplicates.',
            'meta' => 'Customer data consolidation: move contacts from spreadsheets, phone and inbox into a CRM, clean them and remove duplicates. With handover, fixed price.',
            'summary' => 'I bring your customer data from spreadsheets, phone and inbox together in a CRM and remove duplicates. Afterwards you see every customer and their history in one place.',
            'facts' => [
                ['label' => 'Scope', 'value' => 'up to 3 sources, 1,000 contacts'],
                ['label' => 'Duration', 'value' => 'about 2 weeks'],
                ['label' => 'Effort', 'value' => '8 hours'],
                ['label' => 'Result', 'value' => 'Clean CRM with a handover'],
            ],
            'faq' => [
                ['q' => 'What is a CRM?', 'a' => 'Software for customer relationships: contacts, conversations, quotes and tasks in one place.'],
                ['q' => 'Which CRM do you recommend?', 'a' => 'For small businesses a simple one, often with a free tier. We choose together.'],
                ['q' => 'Is this compliant with data protection?', 'a' => 'I look for providers with a data processing agreement. The legal review is up to you.'],
            ],
            'body' => <<<'MD'
                Customer data in five places means nobody knows what is current.

                ## What is included

                - Choosing and setting up a CRM
                - Import from up to three sources, up to 1,000 contacts
                - Clean-up and removal of duplicates
                - Handover session (45 minutes by video)

                ## How it works

                1. We review the sources and choose the CRM.
                2. I import and clean the data.
                3. You check samples, then the handover follows.

                ## Not included

                - Licence costs
                - Connection to shop or accounting

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'digitalisierung', 'tags' => 'automatisierung,zapier,make', 'hours' => 5, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Automatisierter Arbeitsablauf zwischen Programmen',
        'prompt' => 'three app icons connected by animated pipes passing small data packets, a play button starting the flow',
        'de' => [
            'slug' => 'einen-ablauf-automatisieren',
            'title' => 'Einen Arbeitsablauf automatisieren',
            'teaser' => 'Eine wiederkehrende Aufgabe zwischen zwei Programmen läuft künftig von selbst.',
            'meta' => 'Einen Arbeitsablauf automatisieren: Daten zwischen zwei Programmen automatisch übertragen, etwa mit Make oder Zapier. Getestet, dokumentiert, zum Festpreis.',
            'summary' => 'Ich automatisiere einen wiederkehrenden Ablauf zwischen zwei Programmen, zum Beispiel neue Bestellungen in eine Tabelle oder Anfragen in den Kalender. Danach läuft er ohne Abtippen.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Ablauf, 2 Programme'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche'],
                ['label' => 'Aufwand', 'value' => '5 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Laufende Automatisierung mit Doku'],
            ],
            'faq' => [
                ['q' => 'Was lässt sich automatisieren?', 'a' => 'Alles, was nach festen Regeln von einem Programm ins andere übertragen wird. Wenn es Ausnahmen gibt, besprechen wir sie vorher.'],
                ['q' => 'Was passiert bei einem Fehler?', 'a' => 'Sie bekommen eine Benachrichtigung, und der Ablauf wartet, statt Daten zu verlieren.'],
                ['q' => 'Fallen laufende Kosten an?', 'a' => 'Bei Diensten wie Make oder Zapier ab einer gewissen Menge ja. Ich rechne das vorher durch.'],
            ],
            'body' => <<<'MD'
                Jede Aufgabe, die Sie jede Woche gleich erledigen, kann eine Maschine übernehmen.

                ## Das ist enthalten

                - Analyse eines Ablaufs zwischen zwei Programmen
                - Umsetzung mit einem Automatisierungsdienst
                - Fehlerbenachrichtigung
                - Kurze Dokumentation

                ## So läuft es ab

                1. Sie zeigen mir den Ablauf.
                2. Ich baue und teste die Automatisierung.
                3. Sie läuft eine Woche zur Probe, dann übergebe ich.

                ## Nicht enthalten

                - Lizenzkosten der Dienste
                - Programmierung eigener Schnittstellen

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'automate-one-workflow',
            'title' => 'Automate one workflow',
            'teaser' => 'A recurring task between two programs runs by itself from now on.',
            'meta' => 'Automate one workflow: transfer data between two programs automatically, for example with Make or Zapier. Tested, documented, fixed price.',
            'summary' => 'I automate one recurring workflow between two programs, such as new orders into a spreadsheet or enquiries into the calendar. Afterwards it runs without retyping.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 workflow, 2 programs'],
                ['label' => 'Duration', 'value' => 'about 1 week'],
                ['label' => 'Effort', 'value' => '5 hours'],
                ['label' => 'Result', 'value' => 'Running automation with docs'],
            ],
            'faq' => [
                ['q' => 'What can be automated?', 'a' => 'Anything moved from one program to another by fixed rules. If there are exceptions, we discuss them first.'],
                ['q' => 'What happens on an error?', 'a' => 'You get a notification, and the workflow waits instead of losing data.'],
                ['q' => 'Are there running costs?', 'a' => 'With services like Make or Zapier, above a certain volume, yes. I calculate it beforehand.'],
            ],
            'body' => <<<'MD'
                Any task you do the same way every week can be handed to a machine.

                ## What is included

                - Analysis of one workflow between two programs
                - Implementation with an automation service
                - Error notification
                - Short documentation

                ## How it works

                1. You show me the workflow.
                2. I build and test the automation.
                3. It runs for a trial week, then I hand over.

                ## Not included

                - Licence costs of the services
                - Programming custom interfaces

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'digitalisierung', 'tags' => 'passwortmanager,sicherheit,team', 'hours' => 4, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Passwortmanager für ein Team',
        'prompt' => 'a secure digital vault with key icons shared between three team avatars, sticky notes with passwords disappearing',
        'de' => [
            'slug' => 'passwortmanager-fuer-das-team',
            'title' => 'Passwortmanager für das Team',
            'teaser' => 'Schluss mit Passwörtern auf Zetteln: ein gemeinsamer Tresor mit klaren Freigaben.',
            'meta' => 'Passwortmanager für das Team einführen: Auswahl, Einrichtung, Übernahme der wichtigsten Zugänge und Einweisung. Sicherer Zugriff für bis zu 5 Personen.',
            'summary' => 'Ich führe einen Passwortmanager für bis zu fünf Personen ein und übernehme die wichtigsten Zugänge. Jeder sieht nur, was er braucht, und schwache Passwörter werden ersetzt.',
            'facts' => [
                ['label' => 'Umfang', 'value' => 'bis zu 5 Personen, 50 Zugänge'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche'],
                ['label' => 'Aufwand', 'value' => '4 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Eingerichteter Tresor mit Einweisung'],
            ],
            'faq' => [
                ['q' => 'Welchen Passwortmanager empfehlen Sie?', 'a' => 'Einen mit Teamfunktion und Verschlüsselung auf dem Gerät, etwa Bitwarden oder 1Password.'],
                ['q' => 'Sehen Sie meine Passwörter?', 'a' => 'Nur während der Übernahme, wenn Sie das wünschen. Danach ändern Sie die Hauptpasswörter.'],
                ['q' => 'Was, wenn jemand das Team verlässt?', 'a' => 'Sie entziehen den Zugang mit einem Klick und sehen, welche Passwörter geändert werden sollten.'],
            ],
            'body' => <<<'MD'
                Geteilte Passwörter per Zettel oder Chat sind ein Sicherheitsrisiko. Ein Tresor löst das.

                ## Das ist enthalten

                - Auswahl und Einrichtung eines Passwortmanagers
                - Ordner und Freigaben für bis zu fünf Personen
                - Übernahme von bis zu 50 wichtigen Zugängen
                - Einweisung für das Team (45 Minuten)

                ## So läuft es ab

                1. Wir wählen das Programm.
                2. Ich richte Tresor und Freigaben ein.
                3. Gemeinsam übernehmen wir die Zugänge, danach folgt die Einweisung.

                ## Nicht enthalten

                - Lizenzkosten
                - Ändern aller Passwörter bei den jeweiligen Diensten

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'password-manager-for-your-team',
            'title' => 'Password manager for your team',
            'teaser' => 'No more passwords on sticky notes: a shared vault with clear permissions.',
            'meta' => 'Introduce a password manager for your team: selection, setup, import of key logins and a handover. Secure access for up to 5 people.',
            'summary' => 'I introduce a password manager for up to five people and import the most important logins. Everyone sees only what they need, and weak passwords get replaced.',
            'facts' => [
                ['label' => 'Scope', 'value' => 'up to 5 people, 50 logins'],
                ['label' => 'Duration', 'value' => 'about 1 week'],
                ['label' => 'Effort', 'value' => '4 hours'],
                ['label' => 'Result', 'value' => 'Vault set up, with a handover'],
            ],
            'faq' => [
                ['q' => 'Which password manager do you recommend?', 'a' => 'One with team features and on-device encryption, such as Bitwarden or 1Password.'],
                ['q' => 'Will you see my passwords?', 'a' => 'Only during the import, if you want my help. Afterwards you change the master passwords.'],
                ['q' => 'What if someone leaves the team?', 'a' => 'You revoke access with one click and see which passwords should be changed.'],
            ],
            'body' => <<<'MD'
                Sharing passwords on notes or in chat is a security risk. A vault solves it.

                ## What is included

                - Choosing and setting up a password manager
                - Folders and permissions for up to five people
                - Importing up to 50 key logins
                - Team handover session (45 minutes)

                ## How it works

                1. We choose the program.
                2. I set up the vault and permissions.
                3. Together we import the logins, then the handover follows.

                ## Not included

                - Licence costs
                - Changing every password at each service

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
];
