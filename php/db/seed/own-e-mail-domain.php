<?php
declare(strict_types=1);

/**
 * Own products, category `e-mail-domain`. Rate: Individuelle Lösungen, 70 € net per hour.
 */
$image = 'https://tracht-digital.de/images/services/03-loesungen-800.webp';
$rate = 7000;

return [
    [
        'category' => 'e-mail-domain', 'tags' => 'domain,e-mail', 'hours' => 3, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Eigene Domain mit E-Mail-Adressen',
        'prompt' => 'a domain name tag connected to a few neat email envelopes with an @ symbol',
        'de' => [
            'slug' => 'domain-und-e-mail-einrichten',
            'title' => 'Domain und E-Mail einrichten',
            'teaser' => 'Ihre eigene Domain mit professionellen E-Mail-Adressen, eingerichtet auf allen Geräten.',
            'meta' => 'Domain und E-Mail einrichten: Domain registrieren, bis zu fünf Postfächer anlegen und auf Ihren Geräten einrichten. Professionell erreichbar unter Ihrem Namen.',
            'summary' => 'Ich registriere Ihre Wunschdomain auf Ihren Namen und richte bis zu fünf E-Mail-Postfächer ein. Zum Abschluss funktionieren sie auf Computer und Handy.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Domain, bis zu 5 Postfächer'],
                ['label' => 'Dauer', 'value' => '2–3 Werktage'],
                ['label' => 'Aufwand', 'value' => '3 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Funktionierende Postfächer'],
            ],
            'faq' => [
                ['q' => 'Bei welchem Anbieter?', 'a' => 'Bei einem Anbieter Ihrer Wahl. Ich empfehle gern einen mit Serverstandort in Deutschland.'],
                ['q' => 'Was kostet die Domain?', 'a' => 'Meist wenige Euro im Monat beim Anbieter. Diese Kosten zahlen Sie direkt dort.'],
                ['q' => 'Kann ich meine alte Gmail-Adresse weiter nutzen?', 'a' => 'Ja, parallel. Ich richte auf Wunsch eine Weiterleitung ein.'],
            ],
            'body' => <<<'MD'
                info@ihrbetrieb.de wirkt anders als eine Gratis-Adresse. Ich richte es ein.

                ## Das ist enthalten

                - Registrierung der Domain auf Ihren Namen
                - Bis zu fünf Postfächer und Weiterleitungen
                - Einrichtung auf Ihren Geräten
                - Kurze Dokumentation der Zugangsdaten

                ## So läuft es ab

                1. Wir wählen Domain und Anbieter.
                2. Sie schließen den Vertrag ab.
                3. Ich richte Postfächer und Geräte ein.

                ## Nicht enthalten

                - Anbieterkosten für Domain und Postfächer
                - Umzug alter E-Mails (eigenes Paket)

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'domain-and-email-setup',
            'title' => 'Domain and email setup',
            'teaser' => 'Your own domain with professional email addresses, set up on all your devices.',
            'meta' => 'Domain and email setup: register your domain, create up to five mailboxes and set them up on your devices. Professional email in your own name.',
            'summary' => 'I register your domain in your name and set up up to five email mailboxes. At the end they work on your computer and phone.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 domain, up to 5 mailboxes'],
                ['label' => 'Duration', 'value' => '2–3 working days'],
                ['label' => 'Effort', 'value' => '3 hours'],
                ['label' => 'Result', 'value' => 'Working mailboxes'],
            ],
            'faq' => [
                ['q' => 'With which provider?', 'a' => 'A provider of your choice. I am happy to recommend one with servers in Germany.'],
                ['q' => 'What does the domain cost?', 'a' => 'Usually a few euros a month at the provider. You pay that directly to them.'],
                ['q' => 'Can I keep my old Gmail address?', 'a' => 'Yes, alongside. On request I set up forwarding.'],
            ],
            'body' => <<<'MD'
                info@yourbusiness.com looks different from a free address. I set it up.

                ## What is included

                - Registering the domain in your name
                - Up to five mailboxes and forwards
                - Setup on your devices
                - Short documentation of the logins

                ## How it works

                1. We choose domain and provider.
                2. You sign the contract.
                3. I set up mailboxes and devices.

                ## Not included

                - Provider costs for domain and mailboxes
                - Moving old emails (separate package)

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'e-mail-domain', 'tags' => 'microsoft-365,office', 'hours' => 6, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Microsoft 365 für ein kleines Team',
        'prompt' => 'a small team of five avatars connected to a cloud with mail, calendar and document icons',
        'de' => [
            'slug' => 'microsoft-365-einrichten',
            'title' => 'Microsoft 365 einrichten',
            'teaser' => 'E-Mail, Kalender, Teams und Dateien für Ihr Team, sicher eingerichtet.',
            'meta' => 'Microsoft 365 einrichten für bis zu 5 Personen: Domain verbinden, Postfächer, Teams, OneDrive und Zwei-Faktor-Anmeldung. Sicher und dokumentiert.',
            'summary' => 'Ich richte Microsoft 365 für bis zu fünf Personen ein, verbunden mit Ihrer Domain. Postfächer, Teams und OneDrive laufen, und die Anmeldung ist mit einem zweiten Faktor gesichert.',
            'facts' => [
                ['label' => 'Umfang', 'value' => 'bis zu 5 Benutzer'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche'],
                ['label' => 'Aufwand', 'value' => '6 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Eingerichtetes Microsoft 365'],
            ],
            'faq' => [
                ['q' => 'Welche Lizenz brauche ich?', 'a' => 'Für die meisten kleinen Betriebe reicht Business Basic oder Standard. Ich berate Sie vorher.'],
                ['q' => 'Werden meine alten E-Mails übernommen?', 'a' => 'Nicht in diesem Paket. Dafür gibt es den „E-Mail-Umzug“.'],
                ['q' => 'Was ist Zwei-Faktor-Anmeldung?', 'a' => 'Neben dem Passwort bestätigen Sie die Anmeldung am Handy. Das stoppt die meisten Kontoübernahmen.'],
            ],
            'body' => <<<'MD'
                Für Teams, die gemeinsam mailen, planen und Dateien teilen.

                ## Das ist enthalten

                - Einrichtung des Kontos und Verbindung mit Ihrer Domain
                - Bis zu fünf Benutzer mit Postfach
                - Teams und OneDrive eingerichtet
                - Zwei-Faktor-Anmeldung für alle

                ## So läuft es ab

                1. Wir wählen die passende Lizenz.
                2. Sie schließen den Vertrag bei Microsoft ab.
                3. Ich richte alles ein und dokumentiere es.

                ## Nicht enthalten

                - Lizenzkosten
                - Umzug alter E-Mails und Dateien

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'microsoft-365-setup',
            'title' => 'Microsoft 365 setup',
            'teaser' => 'Email, calendar, Teams and files for your team, set up securely.',
            'meta' => 'Microsoft 365 setup for up to 5 people: connect your domain, mailboxes, Teams, OneDrive and two-factor sign-in. Secure and documented.',
            'summary' => 'I set up Microsoft 365 for up to five people, connected to your domain. Mailboxes, Teams and OneDrive work, and sign-in is protected by a second factor.',
            'facts' => [
                ['label' => 'Scope', 'value' => 'up to 5 users'],
                ['label' => 'Duration', 'value' => 'about 1 week'],
                ['label' => 'Effort', 'value' => '6 hours'],
                ['label' => 'Result', 'value' => 'Microsoft 365 up and running'],
            ],
            'faq' => [
                ['q' => 'Which licence do I need?', 'a' => 'For most small businesses Business Basic or Standard is enough. I advise you beforehand.'],
                ['q' => 'Are my old emails moved?', 'a' => 'Not in this package. That is what the "Email migration" is for.'],
                ['q' => 'What is two-factor sign-in?', 'a' => 'Besides the password you confirm sign-in on your phone. It stops most account takeovers.'],
            ],
            'body' => <<<'MD'
                For teams that email, plan and share files together.

                ## What is included

                - Account setup and connection to your domain
                - Up to five users with mailboxes
                - Teams and OneDrive set up
                - Two-factor sign-in for everyone

                ## How it works

                1. We choose the right licence.
                2. You sign the contract with Microsoft.
                3. I set everything up and document it.

                ## Not included

                - Licence costs
                - Moving old emails and files

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'e-mail-domain', 'tags' => 'google-workspace,gmail', 'hours' => 6, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Google Workspace für ein kleines Team',
        'prompt' => 'a small team connected to a bright cloud with colourful mail, calendar, drive and meeting icons',
        'de' => [
            'slug' => 'google-workspace-einrichten',
            'title' => 'Google Workspace einrichten',
            'teaser' => 'Gmail, Kalender und Drive unter Ihrer Domain, für Sie und Ihr Team.',
            'meta' => 'Google Workspace einrichten für bis zu 5 Personen: Domain verbinden, Gmail, Kalender, Drive und Zwei-Faktor-Anmeldung. Sicher eingerichtet und dokumentiert.',
            'summary' => 'Ich richte Google Workspace für bis zu fünf Personen unter Ihrer Domain ein. Gmail, Kalender und Drive sind startklar, die Anmeldung ist mit zwei Faktoren gesichert.',
            'facts' => [
                ['label' => 'Umfang', 'value' => 'bis zu 5 Benutzer'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche'],
                ['label' => 'Aufwand', 'value' => '6 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Eingerichtetes Google Workspace'],
            ],
            'faq' => [
                ['q' => 'Google Workspace oder Microsoft 365?', 'a' => 'Wer viel mit Word und Excel arbeitet, ist bei Microsoft richtig. Wer im Browser arbeitet, oft bei Google.'],
                ['q' => 'Werden alte E-Mails übernommen?', 'a' => 'Nicht in diesem Paket. Dafür gibt es den „E-Mail-Umzug“.'],
                ['q' => 'Wem gehört das Konto?', 'a' => 'Ihnen. Sie sind Hauptadministrator, ich bekomme nur für die Einrichtung Zugang.'],
            ],
            'body' => <<<'MD'
                Für Teams, die im Browser arbeiten und Dokumente gemeinsam bearbeiten.

                ## Das ist enthalten

                - Einrichtung und Verbindung mit Ihrer Domain
                - Bis zu fünf Benutzer mit Gmail
                - Kalender und Drive mit Freigaben
                - Zwei-Faktor-Anmeldung für alle

                ## So läuft es ab

                1. Wir wählen den passenden Tarif.
                2. Sie schließen den Vertrag bei Google ab.
                3. Ich richte alles ein und dokumentiere es.

                ## Nicht enthalten

                - Lizenzkosten
                - Umzug alter E-Mails und Dateien

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'google-workspace-setup',
            'title' => 'Google Workspace setup',
            'teaser' => 'Gmail, Calendar and Drive on your own domain, for you and your team.',
            'meta' => 'Google Workspace setup for up to 5 people: connect your domain, Gmail, Calendar, Drive and two-factor sign-in. Set up securely and documented.',
            'summary' => 'I set up Google Workspace for up to five people on your domain. Gmail, Calendar and Drive are ready to go, and sign-in is protected by two factors.',
            'facts' => [
                ['label' => 'Scope', 'value' => 'up to 5 users'],
                ['label' => 'Duration', 'value' => 'about 1 week'],
                ['label' => 'Effort', 'value' => '6 hours'],
                ['label' => 'Result', 'value' => 'Google Workspace up and running'],
            ],
            'faq' => [
                ['q' => 'Google Workspace or Microsoft 365?', 'a' => 'If you work a lot in Word and Excel, choose Microsoft. If you work in the browser, Google often fits.'],
                ['q' => 'Are old emails moved?', 'a' => 'Not in this package. That is what the "Email migration" is for.'],
                ['q' => 'Who owns the account?', 'a' => 'You do. You are the main administrator; I only get access for the setup.'],
            ],
            'body' => <<<'MD'
                For teams that work in the browser and edit documents together.

                ## What is included

                - Setup and connection to your domain
                - Up to five users with Gmail
                - Calendar and Drive with sharing
                - Two-factor sign-in for everyone

                ## How it works

                1. We choose the right plan.
                2. You sign the contract with Google.
                3. I set everything up and document it.

                ## Not included

                - Licence costs
                - Moving old emails and files

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'e-mail-domain', 'tags' => 'spf,dkim,dmarc,zustellung', 'hours' => 3, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'E-Mail mit Echtheitsnachweis',
        'prompt' => 'an email envelope with a wax seal and three small verification stamps arriving in an inbox, not in spam',
        'de' => [
            'slug' => 'spf-dkim-dmarc-einrichten',
            'title' => 'SPF, DKIM und DMARC einrichten',
            'teaser' => 'Damit Ihre E-Mails ankommen und niemand in Ihrem Namen Spam verschickt.',
            'meta' => 'SPF, DKIM und DMARC einrichten: bessere Zustellbarkeit Ihrer E-Mails und Schutz vor Fälschung Ihrer Absenderadresse. Geprüft und dokumentiert.',
            'summary' => 'Ich richte SPF, DKIM und DMARC für Ihre Domain ein. Ihre E-Mails landen seltener im Spam, und Fälscher können Ihre Adresse kaum noch missbrauchen.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Domain'],
                ['label' => 'Dauer', 'value' => '2–3 Werktage, DMARC-Beobachtung 2 Wochen'],
                ['label' => 'Aufwand', 'value' => '3 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Geprüfte DNS-Einträge'],
            ],
            'faq' => [
                ['q' => 'Warum landen meine Mails im Spam?', 'a' => 'Oft fehlen genau diese Einträge. Seit 2024 verlangen Google und Yahoo sie für viele Absender.'],
                ['q' => 'Was ist DMARC?', 'a' => 'Eine Regel, die Empfängern sagt, was mit E-Mails passieren soll, die angeblich von Ihnen kommen, es aber nicht tun.'],
                ['q' => 'Kann dabei etwas kaputtgehen?', 'a' => 'Ich beginne mit einer beobachtenden Einstellung und verschärfe erst, wenn alle echten Absender erfasst sind.'],
            ],
            'body' => <<<'MD'
                Drei DNS-Einträge entscheiden mit, ob Ihre E-Mails ankommen.

                ## Das ist enthalten

                - Erfassung aller Dienste, die in Ihrem Namen senden
                - SPF-, DKIM- und DMARC-Einträge
                - Zwei Wochen Beobachtung, dann Verschärfung
                - Prüfung mit gängigen Testwerkzeugen

                ## So läuft es ab

                1. Sie geben mir Zugang zur DNS-Verwaltung.
                2. Ich richte die Einträge ein.
                3. Nach der Beobachtung stelle ich DMARC scharf.

                ## Nicht enthalten

                - Umstellung von Newsletter- oder Shop-Systemen

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'spf-dkim-dmarc-setup',
            'title' => 'SPF, DKIM and DMARC setup',
            'teaser' => 'So your emails arrive and nobody sends spam in your name.',
            'meta' => 'SPF, DKIM and DMARC setup: better deliverability for your emails and protection against spoofing of your sender address. Tested and documented.',
            'summary' => 'I set up SPF, DKIM and DMARC for your domain. Your emails land in spam less often, and forgers can hardly misuse your address.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 domain'],
                ['label' => 'Duration', 'value' => '2–3 working days, DMARC monitoring 2 weeks'],
                ['label' => 'Effort', 'value' => '3 hours'],
                ['label' => 'Result', 'value' => 'Verified DNS records'],
            ],
            'faq' => [
                ['q' => 'Why do my emails land in spam?', 'a' => 'Often exactly these records are missing. Since 2024 Google and Yahoo require them for many senders.'],
                ['q' => 'What is DMARC?', 'a' => 'A rule telling receivers what to do with emails that claim to come from you but do not.'],
                ['q' => 'Can something break?', 'a' => 'I start with a monitoring setting and only tighten it once every real sender is covered.'],
            ],
            'body' => <<<'MD'
                Three DNS records help decide whether your emails arrive.

                ## What is included

                - Listing every service that sends in your name
                - SPF, DKIM and DMARC records
                - Two weeks of monitoring, then tightening
                - Checks with common test tools

                ## How it works

                1. You give me access to your DNS settings.
                2. I set up the records.
                3. After monitoring I enforce DMARC.

                ## Not included

                - Reconfiguring newsletter or shop systems

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'e-mail-domain', 'tags' => 'e-mail,umzug,migration', 'hours' => 5, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Umzug von Postfächern zu einem neuen Anbieter',
        'prompt' => 'stacks of email envelopes flowing along a conveyor from an old mailbox to a new one',
        'de' => [
            'slug' => 'e-mail-umzug',
            'title' => 'E-Mail-Umzug zu neuem Anbieter',
            'teaser' => 'Alle Postfächer mit alten E-Mails, Ordnern und Kontakten zum neuen Anbieter, ohne Lücke.',
            'meta' => 'E-Mail-Umzug zum Festpreis: bis zu 5 Postfächer mit allen E-Mails, Ordnern und Kontakten zu einem neuen Anbieter übertragen. Ohne Ausfall, ohne Verlust.',
            'summary' => 'Ich übertrage bis zu fünf Postfächer mit allen E-Mails und Ordnern zu Ihrem neuen Anbieter. Umgestellt wird erst, wenn alles angekommen ist, damit keine Nachricht verloren geht.',
            'facts' => [
                ['label' => 'Umfang', 'value' => 'bis zu 5 Postfächer'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche'],
                ['label' => 'Aufwand', 'value' => '5 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Postfächer beim neuen Anbieter'],
            ],
            'faq' => [
                ['q' => 'Wie viele E-Mails können umziehen?', 'a' => 'Bis zu je 10 GB pro Postfach sind im Preis enthalten. Größere Postfächer kosten nach Aufwand.'],
                ['q' => 'Bin ich während des Umzugs erreichbar?', 'a' => 'Ja. Die Umstellung dauert nur wenige Stunden, und E-Mails aus dieser Zeit werden nachgeholt.'],
                ['q' => 'Ziehen Kalender und Kontakte mit um?', 'a' => 'Ja, sofern beide Anbieter das unterstützen. Ich prüfe das vorher.'],
            ],
            'body' => <<<'MD'
                Anbieterwechsel ohne verlorene E-Mails.

                ## Das ist enthalten

                - Übertragung von bis zu fünf Postfächern
                - E-Mails, Ordner, Kontakte und Kalender, soweit möglich
                - Umstellung der Domain auf den neuen Anbieter
                - Nachholen der E-Mails aus dem Umstellungszeitraum

                ## So läuft es ab

                1. Sie richten den neuen Anbieter ein oder lassen es mich tun.
                2. Ich kopiere die Postfächer.
                3. Nach Ihrer Freigabe stelle ich um.

                ## Nicht enthalten

                - Kosten des neuen Anbieters
                - Postfächer über 10 GB

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'email-migration',
            'title' => 'Email migration to a new provider',
            'teaser' => 'Every mailbox with old emails, folders and contacts moves to the new provider, with no gap.',
            'meta' => 'Email migration at a fixed price: up to 5 mailboxes with all emails, folders and contacts moved to a new provider. No downtime, no loss.',
            'summary' => 'I move up to five mailboxes with all emails and folders to your new provider. The switch only happens once everything has arrived, so no message is lost.',
            'facts' => [
                ['label' => 'Scope', 'value' => 'up to 5 mailboxes'],
                ['label' => 'Duration', 'value' => 'about 1 week'],
                ['label' => 'Effort', 'value' => '5 hours'],
                ['label' => 'Result', 'value' => 'Mailboxes at the new provider'],
            ],
            'faq' => [
                ['q' => 'How much email can move?', 'a' => 'Up to 10 GB per mailbox is included. Larger mailboxes are billed by effort.'],
                ['q' => 'Can people reach me during the move?', 'a' => 'Yes. The switch only takes a few hours, and emails from that window are picked up afterwards.'],
                ['q' => 'Do calendars and contacts move too?', 'a' => 'Yes, if both providers support it. I check beforehand.'],
            ],
            'body' => <<<'MD'
                Switch providers without losing a single email.

                ## What is included

                - Moving up to five mailboxes
                - Emails, folders, contacts and calendars where possible
                - Switching the domain to the new provider
                - Picking up emails from the switch window

                ## How it works

                1. You set up the new provider, or I do.
                2. I copy the mailboxes.
                3. After your approval I switch over.

                ## Not included

                - Costs of the new provider
                - Mailboxes over 10 GB

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'e-mail-domain', 'tags' => 'signatur,e-mail', 'hours' => 2, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Einheitliche E-Mail-Signatur',
        'prompt' => 'several identical elegant email signature cards with logo and contact lines, stacked neatly',
        'de' => [
            'slug' => 'einheitliche-e-mail-signaturen',
            'title' => 'Einheitliche E-Mail-Signaturen',
            'teaser' => 'Eine gestaltete Signatur mit allen Pflichtangaben, gleich für das ganze Team.',
            'meta' => 'Einheitliche E-Mail-Signaturen: Gestaltung mit Logo und Pflichtangaben, eingerichtet für bis zu 5 Personen in Outlook, Gmail oder Apple Mail.',
            'summary' => 'Ich gestalte eine einheitliche E-Mail-Signatur mit Logo und den Pflichtangaben für Geschäftsbriefe. Für bis zu fünf Personen richte ich sie in den genutzten Programmen ein.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Vorlage, bis zu 5 Personen'],
                ['label' => 'Dauer', 'value' => '2–3 Werktage'],
                ['label' => 'Aufwand', 'value' => '2 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Eingerichtete Signaturen'],
            ],
            'faq' => [
                ['q' => 'Welche Angaben gehören in die Signatur?', 'a' => 'Bei Firmen oft Name, Rechtsform, Sitz, Registergericht und Geschäftsführer. Welche für Sie gelten, klären Sie mit Ihrem Steuerberater.'],
                ['q' => 'Sieht die Signatur überall gleich aus?', 'a' => 'Ich teste in den gängigen Programmen auf Computer und Handy.'],
                ['q' => 'Kann ich sie später selbst ändern?', 'a' => 'Ja. Sie bekommen die Vorlage und eine kurze Anleitung.'],
            ],
            'body' => <<<'MD'
                Jede E-Mail ist eine kleine Visitenkarte. Sie sollte im ganzen Team gleich aussehen.

                ## Das ist enthalten

                - Gestaltung einer Signatur mit Logo
                - Platz für Pflichtangaben
                - Einrichtung für bis zu fünf Personen
                - Test auf Computer und Handy

                ## So läuft es ab

                1. Sie schicken Logo und Angaben.
                2. Ich gestalte einen Entwurf zur Freigabe.
                3. Ich richte die Signaturen ein.

                ## Nicht enthalten

                - Zentrale Signatur-Software mit Lizenz

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'consistent-email-signatures',
            'title' => 'Consistent email signatures',
            'teaser' => 'A designed signature with all required details, the same for the whole team.',
            'meta' => 'Consistent email signatures: a design with logo and required details, set up for up to 5 people in Outlook, Gmail or Apple Mail.',
            'summary' => 'I design a consistent email signature with your logo and the details required for business correspondence. I set it up in the programs used by up to five people.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 template, up to 5 people'],
                ['label' => 'Duration', 'value' => '2–3 working days'],
                ['label' => 'Effort', 'value' => '2 hours'],
                ['label' => 'Result', 'value' => 'Signatures in place'],
            ],
            'faq' => [
                ['q' => 'What belongs in the signature?', 'a' => 'For companies often name, legal form, registered office, register court and managing director. Check with your tax adviser which apply to you.'],
                ['q' => 'Does it look the same everywhere?', 'a' => 'I test in common programs on computer and phone.'],
                ['q' => 'Can I change it later myself?', 'a' => 'Yes. You receive the template and short instructions.'],
            ],
            'body' => <<<'MD'
                Every email is a small business card. It should look the same across the team.

                ## What is included

                - Designing a signature with your logo
                - Space for required details
                - Setup for up to five people
                - Testing on computer and phone

                ## How it works

                1. You send logo and details.
                2. I design a draft for approval.
                3. I set up the signatures.

                ## Not included

                - Central signature software with a licence

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'e-mail-domain', 'tags' => 'domain,umzug', 'hours' => 2, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Domain-Umzug zu einem neuen Registrar',
        'prompt' => 'a domain name tag carried across a bridge from one building to another, DNS records following',
        'de' => [
            'slug' => 'domain-umzug',
            'title' => 'Domain-Umzug zu neuem Anbieter',
            'teaser' => 'Ihre Domain wechselt den Anbieter, Website und E-Mail laufen ohne Unterbrechung weiter.',
            'meta' => 'Domain-Umzug zum Festpreis: Domain mit allen DNS-Einträgen zu einem neuen Anbieter übertragen. Website und E-Mail laufen ohne Unterbrechung weiter.',
            'summary' => 'Ich übertrage Ihre Domain zu einem neuen Anbieter und nehme alle DNS-Einträge mit. Website und E-Mail laufen während des Wechsels weiter.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Domain'],
                ['label' => 'Dauer', 'value' => '3–7 Tage, je nach Endung'],
                ['label' => 'Aufwand', 'value' => '2 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Domain beim neuen Anbieter'],
            ],
            'faq' => [
                ['q' => 'Was ist ein Auth-Code?', 'a' => 'Ein Schlüssel, den Ihr alter Anbieter herausgibt. Ohne ihn lässt sich eine Domain nicht übertragen.'],
                ['q' => 'Fällt meine Website aus?', 'a' => 'Nein. Ich lege alle Einträge vorher beim neuen Anbieter an.'],
                ['q' => 'Wann kündige ich beim alten Anbieter?', 'a' => 'Erst nach dem Umzug, und nur die Domain. Ich sage Ihnen, wann.'],
            ],
            'body' => <<<'MD'
                Ein Domain-Umzug ist schnell gemacht, aber ein falscher DNS-Eintrag legt E-Mail und Website lahm.

                ## Das ist enthalten

                - Sicherung aller bestehenden DNS-Einträge
                - Anlage beim neuen Anbieter vor dem Wechsel
                - Übertragung mit Auth-Code
                - Prüfung von Website und E-Mail danach

                ## So läuft es ab

                1. Sie besorgen den Auth-Code beim alten Anbieter.
                2. Ich bereite den neuen Anbieter vor und starte den Umzug.
                3. Nach dem Wechsel prüfe ich alles.

                ## Nicht enthalten

                - Domain-Gebühren beim neuen Anbieter
                - Umzug von Website oder Postfächern

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'domain-transfer',
            'title' => 'Domain transfer to a new provider',
            'teaser' => 'Your domain changes provider, while website and email keep running without interruption.',
            'meta' => 'Domain transfer at a fixed price: move your domain with every DNS record to a new provider. Website and email keep running throughout.',
            'summary' => 'I transfer your domain to a new provider and take every DNS record along. Website and email keep running during the change.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 domain'],
                ['label' => 'Duration', 'value' => '3–7 days, depending on the extension'],
                ['label' => 'Effort', 'value' => '2 hours'],
                ['label' => 'Result', 'value' => 'Domain at the new provider'],
            ],
            'faq' => [
                ['q' => 'What is an auth code?', 'a' => 'A key issued by your old provider. A domain cannot be transferred without it.'],
                ['q' => 'Will my website go down?', 'a' => 'No. I create every record at the new provider beforehand.'],
                ['q' => 'When do I cancel with the old provider?', 'a' => 'Only after the transfer, and only the domain. I tell you when.'],
            ],
            'body' => <<<'MD'
                A domain transfer is quick, but one wrong DNS record can stop email and website.

                ## What is included

                - Backup of every existing DNS record
                - Recreating them at the new provider before the switch
                - Transfer with the auth code
                - Checking website and email afterwards

                ## How it works

                1. You get the auth code from the old provider.
                2. I prepare the new provider and start the transfer.
                3. After the switch I check everything.

                ## Not included

                - Domain fees at the new provider
                - Moving the website or mailboxes

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
];
