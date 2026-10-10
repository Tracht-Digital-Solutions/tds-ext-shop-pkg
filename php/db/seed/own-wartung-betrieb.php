<?php
declare(strict_types=1);

/**
 * Own products, category `wartung-betrieb`. Rate: Webauftritt, 65 € net per hour.
 */
$image = 'https://tracht-digital.de/images/services/04-webauftritt-800.webp';
$rate = 6500;

return [
    [
        'category' => 'wartung-betrieb', 'tags' => 'wartung,updates', 'hours' => 3, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Website-Wartung für drei Monate',
        'prompt' => 'a website on a workbench with small tools, a calendar showing three months ticked off',
        'de' => [
            'slug' => 'website-wartung-3-monate',
            'title' => 'Website-Wartung für 3 Monate',
            'teaser' => 'Drei Monate Updates, Sicherungen und Kontrolle, ohne Abo und ohne Kündigungsfrist.',
            'meta' => 'Website-Wartung für 3 Monate zum Festpreis: monatliche Updates, Sicherungen und Funktionsprüfung. Ohne Abo, ohne Kündigung, für WordPress und Baukästen.',
            'summary' => 'Drei Monate lang halte ich Ihre Website aktuell: monatliche Updates mit vorheriger Sicherung und eine kurze Funktionsprüfung. Danach endet das Paket von selbst.',
            'facts' => [
                ['label' => 'Laufzeit', 'value' => '3 Monate, endet automatisch'],
                ['label' => 'Rhythmus', 'value' => '1 Wartung pro Monat'],
                ['label' => 'Aufwand', 'value' => '3 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Monatlicher Kurzbericht'],
            ],
            'faq' => [
                ['q' => 'Muss ich kündigen?', 'a' => 'Nein. Das Paket endet nach drei Monaten von selbst.'],
                ['q' => 'Was passiert bei Problemen nach einem Update?', 'a' => 'Ich stelle den gesicherten Stand wieder her und melde mich bei Ihnen.'],
                ['q' => 'Sind Inhaltsänderungen enthalten?', 'a' => 'Nein. Dafür eignet sich das Pflegekontingent.'],
            ],
            'body' => <<<'MD'
                Updates schließen Sicherheitslücken. Dieses Paket erledigt sie für drei Monate.

                ## Das ist enthalten

                - Monatliches Update von System, Plugins und Theme
                - Sicherung vor jedem Update
                - Kurzer Funktionstest: Seiten, Formular, Darstellung
                - Monatlicher Kurzbericht per E-Mail

                ## So läuft es ab

                1. Sie geben mir Zugang zur Website-Verwaltung.
                2. Ich warte einmal pro Monat.
                3. Nach drei Monaten endet das Paket.

                ## Nicht enthalten

                - Änderungen an Inhalten
                - Kosten für Premium-Plugins

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'website-maintenance-3-months',
            'title' => 'Website maintenance for 3 months',
            'teaser' => 'Three months of updates, backups and checks, with no subscription and no notice period.',
            'meta' => 'Website maintenance for 3 months at a fixed price: monthly updates, backups and function checks. No subscription, no cancellation, for WordPress.',
            'summary' => 'For three months I keep your website up to date: monthly updates with a prior backup and a short function check. Afterwards the package simply ends.',
            'facts' => [
                ['label' => 'Term', 'value' => '3 months, ends automatically'],
                ['label' => 'Frequency', 'value' => '1 maintenance per month'],
                ['label' => 'Effort', 'value' => '3 hours'],
                ['label' => 'Result', 'value' => 'Monthly short report'],
            ],
            'faq' => [
                ['q' => 'Do I need to cancel?', 'a' => 'No. The package ends by itself after three months.'],
                ['q' => 'What if an update causes problems?', 'a' => 'I restore the backup and get in touch with you.'],
                ['q' => 'Are content changes included?', 'a' => 'No. The website care package is made for that.'],
            ],
            'body' => <<<'MD'
                Updates close security holes. This package takes care of them for three months.

                ## What is included

                - Monthly update of system, plugins and theme
                - Backup before every update
                - Short function test: pages, form, layout
                - Monthly short report by email

                ## How it works

                1. You give me access to your website admin.
                2. I do the maintenance once a month.
                3. After three months the package ends.

                ## Not included

                - Content changes
                - Costs of premium plugins

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'wartung-betrieb', 'tags' => 'wartung,updates,jahrespaket', 'hours' => 10, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Website-Wartung für ein Jahr',
        'prompt' => 'a website protected under a glass dome with a full year calendar ring around it',
        'de' => [
            'slug' => 'website-wartung-12-monate',
            'title' => 'Website-Wartung für 12 Monate',
            'teaser' => 'Ein Jahr Ruhe: Updates, Sicherungen und Überwachung der Erreichbarkeit.',
            'meta' => 'Website-Wartung für 12 Monate zum Festpreis: Updates, Sicherungen, Erreichbarkeits-Überwachung und Jahresbericht. Ohne Abo, endet nach einem Jahr.',
            'summary' => 'Ein Jahr lang halte ich Ihre Website aktuell und überwache, ob sie erreichbar ist. Fällt sie aus, werde ich benachrichtigt. Das Paket endet nach zwölf Monaten.',
            'facts' => [
                ['label' => 'Laufzeit', 'value' => '12 Monate, endet automatisch'],
                ['label' => 'Rhythmus', 'value' => 'Updates monatlich, Überwachung laufend'],
                ['label' => 'Aufwand', 'value' => '10 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Jahresbericht'],
            ],
            'faq' => [
                ['q' => 'Was bedeutet Überwachung?', 'a' => 'Ein Dienst prüft alle paar Minuten, ob Ihre Seite antwortet. Fällt sie aus, bekomme ich eine Nachricht.'],
                ['q' => 'Wie schnell reagieren Sie bei einem Ausfall?', 'a' => 'Werktags in der Regel am selben Tag. Eine Reaktionszeit wird nicht garantiert.'],
                ['q' => 'Ist das günstiger als vier Quartalspakete?', 'a' => 'Ja, etwas. Dazu kommen Überwachung und Jahresbericht.'],
            ],
            'body' => <<<'MD'
                Für alle, die sich ein Jahr lang nicht um Updates kümmern wollen.

                ## Das ist enthalten

                - Monatliche Updates mit vorheriger Sicherung
                - Überwachung der Erreichbarkeit
                - Prüfung der Sicherungen zweimal im Jahr
                - Jahresbericht mit Empfehlungen

                ## So läuft es ab

                1. Sie geben mir Zugang zur Website-Verwaltung.
                2. Ich richte die Überwachung ein und warte monatlich.
                3. Nach zwölf Monaten bekommen Sie den Jahresbericht.

                ## Nicht enthalten

                - Änderungen an Inhalten
                - Garantierte Reaktionszeiten

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'website-maintenance-12-months',
            'title' => 'Website maintenance for 12 months',
            'teaser' => 'A year of peace of mind: updates, backups and uptime monitoring.',
            'meta' => 'Website maintenance for 12 months at a fixed price: updates, backups, uptime monitoring and a yearly report. No subscription, ends after a year.',
            'summary' => 'For a year I keep your website up to date and monitor whether it is online. If it goes down, I am notified. The package ends after twelve months.',
            'facts' => [
                ['label' => 'Term', 'value' => '12 months, ends automatically'],
                ['label' => 'Frequency', 'value' => 'Monthly updates, continuous monitoring'],
                ['label' => 'Effort', 'value' => '10 hours'],
                ['label' => 'Result', 'value' => 'Yearly report'],
            ],
            'faq' => [
                ['q' => 'What does monitoring mean?', 'a' => 'A service checks every few minutes whether your site answers. If it goes down, I get a message.'],
                ['q' => 'How fast do you react to an outage?', 'a' => 'On working days usually the same day. A response time is not guaranteed.'],
                ['q' => 'Is it cheaper than four quarterly packages?', 'a' => 'Yes, slightly. Plus monitoring and the yearly report.'],
            ],
            'body' => <<<'MD'
                For everyone who does not want to think about updates for a year.

                ## What is included

                - Monthly updates with a prior backup
                - Uptime monitoring
                - Backup restore check twice a year
                - Yearly report with recommendations

                ## How it works

                1. You give me access to your website admin.
                2. I set up monitoring and maintain monthly.
                3. After twelve months you receive the yearly report.

                ## Not included

                - Content changes
                - Guaranteed response times

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'wartung-betrieb', 'tags' => 'wordpress,sicherheit,update', 'hours' => 2, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Einmaliges Sicherheits-Update für WordPress',
        'prompt' => 'a WordPress-like dashboard with an update button pressed and a padlock clicking shut',
        'de' => [
            'slug' => 'sicherheits-update-wordpress',
            'title' => 'Sicherheits-Update für WordPress',
            'teaser' => 'Ihre WordPress-Seite einmal auf den neuesten Stand bringen, mit Sicherung und Prüfung.',
            'meta' => 'Einmaliges Sicherheits-Update für WordPress: Sicherung, Update von Kern, Plugins und Theme, Funktionsprüfung. Für lange nicht gepflegte Websites.',
            'summary' => 'Ich bringe Ihre WordPress-Seite einmalig auf den aktuellen Stand. Vorher wird gesichert, danach geprüft, und veraltete Plugins werden benannt.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 WordPress-Website'],
                ['label' => 'Dauer', 'value' => '1–3 Werktage'],
                ['label' => 'Aufwand', 'value' => '2 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Aktuelle Website und Kurzbericht'],
            ],
            'faq' => [
                ['q' => 'Meine Seite wurde jahrelang nicht aktualisiert. Geht das noch?', 'a' => 'Meist ja. Bei sehr alten Ständen kann mehr Aufwand nötig sein. Dann melde ich mich vorher.'],
                ['q' => 'Was passiert mit nicht mehr gepflegten Plugins?', 'a' => 'Ich nenne sie im Bericht und schlage Ersatz vor. Der Austausch ist nicht enthalten.'],
                ['q' => 'Fällt die Seite während des Updates aus?', 'a' => 'Höchstens wenige Minuten. Auf Wunsch arbeite ich außerhalb Ihrer Geschäftszeiten.'],
            ],
            'body' => <<<'MD'
                Veraltete Plugins sind das häufigste Einfallstor für Angriffe. Ein Update schließt es.

                ## Das ist enthalten

                - Vollständige Sicherung vorab
                - Update von WordPress, Plugins und Theme
                - Funktionsprüfung der wichtigsten Seiten
                - Kurzbericht mit veralteten Plugins

                ## So läuft es ab

                1. Sie geben mir Zugang zur WordPress-Verwaltung und zum Hosting.
                2. Ich sichere und aktualisiere.
                3. Sie bekommen den Kurzbericht.

                ## Nicht enthalten

                - Austausch nicht mehr gepflegter Plugins
                - Bereinigung nach einem Hackerangriff (eigenes Paket)

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'wordpress-security-update',
            'title' => 'WordPress security update',
            'teaser' => 'Bring your WordPress site up to date once, with a backup and a check.',
            'meta' => 'One-off WordPress security update: backup, update of core, plugins and theme, function check. For websites that have not been maintained in a while.',
            'summary' => 'I bring your WordPress site up to date once. It is backed up first and checked afterwards, and outdated plugins are listed.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 WordPress website'],
                ['label' => 'Duration', 'value' => '1–3 working days'],
                ['label' => 'Effort', 'value' => '2 hours'],
                ['label' => 'Result', 'value' => 'Updated website and short report'],
            ],
            'faq' => [
                ['q' => 'My site has not been updated for years. Is it still possible?', 'a' => 'Usually yes. Very old versions may need more effort. Then I contact you first.'],
                ['q' => 'What about abandoned plugins?', 'a' => 'I list them in the report and suggest replacements. Replacing them is not included.'],
                ['q' => 'Will the site go down during the update?', 'a' => 'A few minutes at most. On request I work outside your business hours.'],
            ],
            'body' => <<<'MD'
                Outdated plugins are the most common way in for attackers. An update closes it.

                ## What is included

                - Full backup beforehand
                - Update of WordPress, plugins and theme
                - Function check of the key pages
                - Short report listing outdated plugins

                ## How it works

                1. You give me access to the WordPress admin and hosting.
                2. I back up and update.
                3. You receive the short report.

                ## Not included

                - Replacing abandoned plugins
                - Clean-up after a hack (separate package)

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'wartung-betrieb', 'tags' => 'backup,sicherung', 'hours' => 3, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Automatische Website-Sicherung',
        'prompt' => 'a website duplicated into a safe vault with circular arrows, a restore button beside it',
        'de' => [
            'slug' => 'backup-einrichten-und-testen',
            'title' => 'Website-Backup einrichten und testen',
            'teaser' => 'Automatische Sicherungen an einem zweiten Ort, mit einer echten Wiederherstellung als Probe.',
            'meta' => 'Website-Backup einrichten: automatische Sicherungen an einem externen Ort, getestete Wiederherstellung und Anleitung. Damit im Ernstfall nichts verloren ist.',
            'summary' => 'Ich richte automatische Sicherungen Ihrer Website an einem zweiten Ort ein. Eine Probe-Wiederherstellung beweist, dass sie im Ernstfall funktionieren.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Website mit Datenbank'],
                ['label' => 'Dauer', 'value' => '2–3 Werktage'],
                ['label' => 'Aufwand', 'value' => '3 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Getestete Sicherung und Anleitung'],
            ],
            'faq' => [
                ['q' => 'Macht mein Hoster nicht schon Backups?', 'a' => 'Oft ja, aber auf demselben Server. Fällt der Hoster aus, sind auch die Sicherungen weg.'],
                ['q' => 'Wo werden die Sicherungen gespeichert?', 'a' => 'Bei einem Speicherdienst Ihrer Wahl, etwa in einer Cloud mit Serverstandort in der EU.'],
                ['q' => 'Warum eine Probe-Wiederherstellung?', 'a' => 'Eine Sicherung, die nie zurückgespielt wurde, ist nur eine Hoffnung.'],
            ],
            'body' => <<<'MD'
                Eine Sicherung zählt erst, wenn sie sich zurückspielen lässt.

                ## Das ist enthalten

                - Automatische tägliche Sicherung von Dateien und Datenbank
                - Speicherung an einem externen Ort
                - Probe-Wiederherstellung in einer Testumgebung
                - Kurzanleitung für den Ernstfall

                ## So läuft es ab

                1. Wir wählen den Speicherort.
                2. Ich richte die Sicherung ein.
                3. Ich spiele eine Sicherung testweise zurück und dokumentiere das.

                ## Nicht enthalten

                - Kosten des Speicherdienstes
                - Laufende Kontrolle (siehe Wartungspakete)

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'website-backup-setup-and-test',
            'title' => 'Website backup setup and test',
            'teaser' => 'Automatic backups in a second location, with a real restore as proof.',
            'meta' => 'Website backup setup: automatic backups to an external location, a tested restore and instructions, so nothing is lost when it matters.',
            'summary' => 'I set up automatic backups of your website in a second location. A test restore proves they work when it matters.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 website with database'],
                ['label' => 'Duration', 'value' => '2–3 working days'],
                ['label' => 'Effort', 'value' => '3 hours'],
                ['label' => 'Result', 'value' => 'Tested backup and instructions'],
            ],
            'faq' => [
                ['q' => 'Doesn\'t my host make backups already?', 'a' => 'Often yes, but on the same server. If the host fails, the backups go with it.'],
                ['q' => 'Where are the backups stored?', 'a' => 'With a storage service of your choice, for example cloud storage with servers in the EU.'],
                ['q' => 'Why a test restore?', 'a' => 'A backup that has never been restored is only a hope.'],
            ],
            'body' => <<<'MD'
                A backup only counts once it can be restored.

                ## What is included

                - Automatic daily backup of files and database
                - Storage in an external location
                - Test restore in a staging environment
                - Short emergency guide

                ## How it works

                1. We choose the storage location.
                2. I set up the backup.
                3. I restore a backup as a test and document it.

                ## Not included

                - Costs of the storage service
                - Ongoing checks (see maintenance packages)

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'wartung-betrieb', 'tags' => 'hosting,umzug', 'hours' => 6, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Umzug einer Website zu einem neuen Hoster',
        'prompt' => 'a website packed in a moving box travelling on an arrow from one server rack to another',
        'de' => [
            'slug' => 'website-umzug-zu-neuem-hoster',
            'title' => 'Website-Umzug zu neuem Hoster',
            'teaser' => 'Ihre Website zieht zu einem neuen Anbieter um, ohne Ausfall und ohne Datenverlust.',
            'meta' => 'Website-Umzug zum Festpreis: Dateien, Datenbank und E-Mail-Einstellungen zu einem neuen Hoster übertragen, getestet umstellen. Ohne Ausfall.',
            'summary' => 'Ich ziehe Ihre Website zu einem neuen Hoster um und teste sie dort, bevor die Domain umgestellt wird. Besucher merken vom Wechsel nichts.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Website mit Datenbank'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche'],
                ['label' => 'Aufwand', 'value' => '6 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Website läuft beim neuen Hoster'],
            ],
            'faq' => [
                ['q' => 'Ziehen meine E-Mails mit um?', 'a' => 'Nicht automatisch. Für Postfächer gibt es das Paket „E-Mail-Umzug“. Ich achte darauf, dass E-Mails weiter ankommen.'],
                ['q' => 'Helfen Sie bei der Wahl des Hosters?', 'a' => 'Ja. Ich empfehle zwei bis drei passende Anbieter.'],
                ['q' => 'Wann kündige ich den alten Vertrag?', 'a' => 'Erst nach dem erfolgreichen Umzug. Ich sage Ihnen, wann es so weit ist.'],
            ],
            'body' => <<<'MD'
                Zu teuer, zu langsam oder schlechter Support: Ein Hosterwechsel lohnt sich oft.

                ## Das ist enthalten

                - Übertragung aller Dateien und der Datenbank
                - Test beim neuen Hoster vor der Umstellung
                - Umstellung der Domain mit minimaler Ausfallzeit
                - SSL-Zertifikat beim neuen Hoster

                ## So läuft es ab

                1. Sie schließen den Vertrag beim neuen Hoster ab.
                2. Ich ziehe um und teste.
                3. Nach Ihrer Freigabe stelle ich die Domain um.

                ## Nicht enthalten

                - Umzug der E-Mail-Postfächer (eigenes Paket)
                - Hosting-Kosten

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'website-move-to-new-host',
            'title' => 'Website move to a new host',
            'teaser' => 'Your website moves to a new provider, with no downtime and no data loss.',
            'meta' => 'Website move at a fixed price: transfer files, database and email settings to a new host, test, then switch. No downtime for your visitors.',
            'summary' => 'I move your website to a new host and test it there before the domain is switched. Visitors notice nothing of the change.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 website with database'],
                ['label' => 'Duration', 'value' => 'about 1 week'],
                ['label' => 'Effort', 'value' => '6 hours'],
                ['label' => 'Result', 'value' => 'Website running at the new host'],
            ],
            'faq' => [
                ['q' => 'Do my emails move too?', 'a' => 'Not automatically. For mailboxes there is the "Email migration" package. I make sure email keeps arriving.'],
                ['q' => 'Do you help choose a host?', 'a' => 'Yes. I recommend two or three suitable providers.'],
                ['q' => 'When do I cancel the old contract?', 'a' => 'Only after a successful move. I tell you when.'],
            ],
            'body' => <<<'MD'
                Too expensive, too slow or poor support: changing host often pays off.

                ## What is included

                - Transfer of all files and the database
                - Testing at the new host before switching
                - Domain switch with minimal downtime
                - SSL certificate at the new host

                ## How it works

                1. You sign up with the new host.
                2. I move and test.
                3. After your approval I switch the domain.

                ## Not included

                - Moving mailboxes (separate package)
                - Hosting costs

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'wartung-betrieb', 'tags' => 'hack,malware,notfall', 'hours' => 6, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Bereinigung einer gehackten Website',
        'prompt' => 'a website being cleaned with a sponge, small bug icons washing away, a shield appearing',
        'de' => [
            'slug' => 'gehackte-website-bereinigen',
            'title' => 'Gehackte Website bereinigen',
            'teaser' => 'Schadcode entfernen, Zugänge sichern und die Seite wieder sauber online bringen.',
            'meta' => 'Gehackte Website bereinigen: Schadcode entfernen, Passwörter erneuern, Lücke schließen und Google-Warnung aufheben lassen. Schnelle Hilfe zum Festpreis.',
            'summary' => 'Ich entferne Schadcode von Ihrer Website, schließe die Lücke und erneuere alle Zugänge. Zeigt Google eine Warnung, beantrage ich die Überprüfung.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Website'],
                ['label' => 'Start', 'value' => 'werktags meist am selben Tag'],
                ['label' => 'Aufwand', 'value' => '6 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Bereinigte Website und Bericht'],
            ],
            'faq' => [
                ['q' => 'Woran erkenne ich einen Hack?', 'a' => 'An fremden Weiterleitungen, Spam-Seiten bei Google, Warnungen im Browser oder Beschwerden über Spam-Mails.'],
                ['q' => 'Was, wenn 6 Stunden nicht reichen?', 'a' => 'Dann melde ich mich, bevor weitere Kosten entstehen. Sie entscheiden.'],
                ['q' => 'Muss ich Behörden informieren?', 'a' => 'Wenn personenbezogene Daten betroffen sein könnten, kann eine Meldepflicht bestehen. Das klären Sie bitte mit Ihrem Datenschutzberater.'],
            ],
            'body' => <<<'MD'
                Ein Hack ist ärgerlich, aber lösbar. Wichtig ist, schnell und gründlich zu handeln.

                ## Das ist enthalten

                - Suche und Entfernung von Schadcode
                - Neue Passwörter für Verwaltung, Hosting und Datenbank
                - Schließen der Einfallslücke, soweit erkennbar
                - Antrag auf Überprüfung bei Google, falls gewarnt wird
                - Bericht: was passiert ist und wie es sich vermeiden lässt

                ## So läuft es ab

                1. Sie bestellen und geben mir Zugang zum Hosting.
                2. Ich sichere den Ist-Zustand und bereinige.
                3. Sie bekommen den Bericht.

                ## Nicht enthalten

                - Datenschutzrechtliche Beratung
                - Neubau, falls eine Bereinigung nicht möglich ist

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'hacked-website-clean-up',
            'title' => 'Hacked website clean-up',
            'teaser' => 'Remove malicious code, secure all logins and bring the site back online cleanly.',
            'meta' => 'Hacked website clean-up: remove malware, renew passwords, close the hole and request a Google review. Fast help at a fixed price.',
            'summary' => 'I remove malicious code from your website, close the hole and renew all logins. If Google shows a warning, I request a review.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 website'],
                ['label' => 'Start', 'value' => 'usually the same working day'],
                ['label' => 'Effort', 'value' => '6 hours'],
                ['label' => 'Result', 'value' => 'Clean website and report'],
            ],
            'faq' => [
                ['q' => 'How do I recognise a hack?', 'a' => 'Strange redirects, spam pages on Google, browser warnings or complaints about spam emails.'],
                ['q' => 'What if 6 hours are not enough?', 'a' => 'Then I contact you before any further cost arises. You decide.'],
                ['q' => 'Do I need to inform authorities?', 'a' => 'If personal data may be affected, a reporting duty can apply. Please clarify this with your data protection adviser.'],
            ],
            'body' => <<<'MD'
                A hack is annoying but solvable. What matters is acting fast and thoroughly.

                ## What is included

                - Finding and removing malicious code
                - New passwords for admin, hosting and database
                - Closing the entry point, as far as identifiable
                - Requesting a Google review if a warning is shown
                - Report: what happened and how to prevent it

                ## How it works

                1. You order and give me access to the hosting.
                2. I secure the current state and clean up.
                3. You receive the report.

                ## Not included

                - Data protection legal advice
                - A rebuild if clean-up is not possible

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'wartung-betrieb', 'tags' => 'ssl,https', 'hours' => 2, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Website mit HTTPS-Schloss im Browser',
        'prompt' => 'a browser address bar with a green padlock, mixed-content warnings turning into checkmarks',
        'de' => [
            'slug' => 'https-umstellung',
            'title' => 'HTTPS-Umstellung mit SSL-Zertifikat',
            'teaser' => 'Das Schloss im Browser: verschlüsselte Verbindung, keine Warnungen, saubere Weiterleitungen.',
            'meta' => 'HTTPS-Umstellung zum Festpreis: SSL-Zertifikat einrichten, alle Adressen umleiten und gemischte Inhalte beheben. Keine Browser-Warnungen mehr.',
            'summary' => 'Ich stelle Ihre Website vollständig auf HTTPS um. Das Zertifikat erneuert sich automatisch, und alle alten Adressen leiten sauber weiter.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Domain mit www'],
                ['label' => 'Dauer', 'value' => '1–2 Werktage'],
                ['label' => 'Aufwand', 'value' => '2 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Website nur noch über HTTPS'],
            ],
            'faq' => [
                ['q' => 'Kostet das Zertifikat extra?', 'a' => 'Meist nicht. Ich nutze kostenlose Zertifikate, wenn Ihr Hoster sie anbietet.'],
                ['q' => 'Warum zeigt der Browser trotz Zertifikat eine Warnung?', 'a' => 'Oft wegen gemischter Inhalte: einzelne Bilder oder Skripte laden noch über HTTP. Das behebe ich.'],
                ['q' => 'Verliere ich Google-Platzierungen?', 'a' => 'Nein, wenn die Weiterleitungen stimmen. Genau darauf achte ich.'],
            ],
            'body' => <<<'MD'
                Ohne HTTPS warnen Browser Ihre Besucher. Das kostet Vertrauen und Anfragen.

                ## Das ist enthalten

                - SSL-Zertifikat mit automatischer Erneuerung
                - Weiterleitung aller HTTP-Adressen auf HTTPS
                - Behebung gemischter Inhalte
                - Aktualisierung in der Google Search Console

                ## So läuft es ab

                1. Sie geben mir Zugang zum Hosting.
                2. Ich richte Zertifikat und Weiterleitungen ein.
                3. Ich prüfe alle Seiten auf Warnungen.

                ## Nicht enthalten

                - Kostenpflichtige Zertifikate, falls nötig

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'https-switch-with-ssl',
            'title' => 'HTTPS switch with SSL certificate',
            'teaser' => 'The padlock in the browser: encrypted connections, no warnings, clean redirects.',
            'meta' => 'HTTPS switch at a fixed price: set up an SSL certificate, redirect every address and fix mixed content. No more browser warnings.',
            'summary' => 'I switch your website fully to HTTPS. The certificate renews automatically, and every old address redirects cleanly.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 domain incl. www'],
                ['label' => 'Duration', 'value' => '1–2 working days'],
                ['label' => 'Effort', 'value' => '2 hours'],
                ['label' => 'Result', 'value' => 'Website served over HTTPS only'],
            ],
            'faq' => [
                ['q' => 'Does the certificate cost extra?', 'a' => 'Usually not. I use free certificates if your host offers them.'],
                ['q' => 'Why does the browser still warn despite a certificate?', 'a' => 'Often due to mixed content: some images or scripts still load over HTTP. I fix that.'],
                ['q' => 'Will I lose Google rankings?', 'a' => 'No, if the redirects are right. That is exactly what I take care of.'],
            ],
            'body' => <<<'MD'
                Without HTTPS, browsers warn your visitors. That costs trust and enquiries.

                ## What is included

                - SSL certificate with automatic renewal
                - Redirect of every HTTP address to HTTPS
                - Fixing mixed content
                - Update in Google Search Console

                ## How it works

                1. You give me access to the hosting.
                2. I set up the certificate and redirects.
                3. I check every page for warnings.

                ## Not included

                - Paid certificates, if required

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
];
