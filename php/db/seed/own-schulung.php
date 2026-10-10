<?php
declare(strict_types=1);

/**
 * Own products, category `schulung`. Rate: Beratung & Konzeption, 75 € net per hour.
 * Hours include preparation and written notes, not only the session itself.
 */
$image = 'https://tracht-digital.de/images/services/01-beratung-800.webp';
$rate = 7500;

return [
    [
        'category' => 'schulung', 'tags' => 'website,pflege,einweisung', 'hours' => 2, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Einweisung in die Pflege der eigenen Website',
        'prompt' => 'a person at a laptop editing a website text block with a friendly coach pointing at the screen',
        'de' => [
            'slug' => 'website-selbst-pflegen-einweisung',
            'title' => 'Website selbst pflegen: Einweisung',
            'teaser' => 'Texte, Bilder und Seiten selbst ändern, an Ihrer eigenen Website gezeigt.',
            'meta' => 'Website selbst pflegen: Einweisung an Ihrer eigenen Website. Texte ändern, Bilder tauschen, Seiten anlegen, sicher speichern. Mit Spickzettel, per Video.',
            'summary' => 'In einer Einweisung an Ihrer eigenen Website lernen Sie, Texte zu ändern, Bilder zu tauschen und neue Seiten anzulegen. Ein Spickzettel hält die Schritte fest.',
            'facts' => [
                ['label' => 'Format', 'value' => '90 Minuten per Video'],
                ['label' => 'Teilnehmende', 'value' => 'bis zu 3 Personen'],
                ['label' => 'Aufwand', 'value' => '2 Stunden inkl. Vorbereitung'],
                ['label' => 'Ergebnis', 'value' => 'Spickzettel als PDF'],
            ],
            'faq' => [
                ['q' => 'Für welche Systeme?', 'a' => 'WordPress und die gängigen Baukästen. Bei anderen Systemen bitte vorher fragen.'],
                ['q' => 'Brauche ich Vorkenntnisse?', 'a' => 'Nein. Wer E-Mails schreiben kann, kann auch eine Website pflegen.'],
                ['q' => 'Was, wenn ich etwas kaputt mache?', 'a' => 'Ich zeige Ihnen, wie Sie Änderungen rückgängig machen. Vorher gibt es eine Sicherung.'],
            ],
            'body' => <<<'MD'
                Kleine Änderungen sollten Sie nicht jedes Mal beauftragen müssen.

                ## Das ist enthalten

                - Einweisung an Ihrer eigenen Website
                - Texte ändern, Bilder tauschen, Seiten anlegen
                - Änderungen rückgängig machen
                - Spickzettel mit den wichtigsten Schritten

                ## So läuft es ab

                1. Wir vereinbaren einen Termin.
                2. Ich sichere die Website und bereite Übungen vor.
                3. In der Einweisung arbeiten Sie selbst, ich leite an.

                ## Nicht enthalten

                - Gestaltungsänderungen am Layout

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'maintain-your-website-yourself',
            'title' => 'Maintain your website yourself',
            'teaser' => 'Change copy, images and pages yourself, shown on your own website.',
            'meta' => 'Maintain your website yourself: training on your own site. Edit copy, swap images, add pages, save safely. With a cheat sheet, by video.',
            'summary' => 'In a session on your own website you learn to edit copy, swap images and add new pages. A cheat sheet records the steps.',
            'facts' => [
                ['label' => 'Format', 'value' => '90 minutes by video'],
                ['label' => 'Participants', 'value' => 'up to 3 people'],
                ['label' => 'Effort', 'value' => '2 hours incl. preparation'],
                ['label' => 'Result', 'value' => 'Cheat sheet as PDF'],
            ],
            'faq' => [
                ['q' => 'For which systems?', 'a' => 'WordPress and common website builders. For other systems, please ask first.'],
                ['q' => 'Do I need prior knowledge?', 'a' => 'No. If you can write emails, you can maintain a website.'],
                ['q' => 'What if I break something?', 'a' => 'I show you how to undo changes. There is a backup beforehand.'],
            ],
            'body' => <<<'MD'
                You should not have to commission every small change.

                ## What is included

                - Training on your own website
                - Editing copy, swapping images, adding pages
                - Undoing changes
                - Cheat sheet with the key steps

                ## How it works

                1. We agree on a date.
                2. I back up the website and prepare exercises.
                3. In the session you work yourself while I guide you.

                ## Not included

                - Layout or design changes

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'schulung', 'tags' => 'ki,workshop,chatgpt', 'hours' => 3, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Workshop zu KI im Büroalltag',
        'prompt' => 'a small office team around a screen with an AI chat assistant drafting a letter, light bulbs above',
        'de' => [
            'slug' => 'ki-im-buero-workshop',
            'title' => 'KI im Büro: Praxis-Workshop',
            'teaser' => 'ChatGPT und Co. sinnvoll und sicher im Arbeitsalltag nutzen, an Ihren eigenen Aufgaben.',
            'meta' => 'KI im Büro: Praxis-Workshop für kleine Teams. Texte, E-Mails und Recherche mit KI-Assistenten, gute Anweisungen schreiben und Datenschutz beachten.',
            'summary' => 'Im Workshop lernen Sie und Ihr Team, KI-Assistenten für E-Mails, Texte und Recherche zu nutzen. Wir üben an Ihren eigenen Aufgaben und klären, welche Daten nicht hineingehören.',
            'facts' => [
                ['label' => 'Format', 'value' => '2 Stunden, online oder vor Ort'],
                ['label' => 'Teilnehmende', 'value' => 'bis zu 6 Personen'],
                ['label' => 'Aufwand', 'value' => '3 Stunden inkl. Vorbereitung'],
                ['label' => 'Ergebnis', 'value' => 'Vorlagen für Anweisungen (Prompts)'],
            ],
            'faq' => [
                ['q' => 'Welche KI-Werkzeuge kommen vor?', 'a' => 'Gängige Assistenten wie ChatGPT, Claude, Microsoft Copilot oder Gemini, je nachdem, was Sie nutzen oder nutzen wollen.'],
                ['q' => 'Dürfen wir Kundendaten eingeben?', 'a' => 'In der Regel nicht in öffentliche Versionen. Im Workshop klären wir, was erlaubt ist und was nicht.'],
                ['q' => 'Brauchen wir kostenpflichtige Konten?', 'a' => 'Nein, kostenlose Versionen reichen zum Lernen.'],
            ],
            'body' => <<<'MD'
                KI spart Zeit, wenn man weiß, wofür man sie einsetzt und wofür nicht.

                ## Das ist enthalten

                - Vorgespräch zu Ihren typischen Aufgaben
                - Workshop mit Übungen an Ihren Beispielen
                - Regeln für den sicheren Umgang mit Daten
                - Vorlagen für gute Anweisungen

                ## So läuft es ab

                1. Wir sammeln im Vorgespräch Ihre Aufgaben.
                2. Ich bereite passende Übungen vor.
                3. Im Workshop arbeitet jeder selbst mit.

                ## Nicht enthalten

                - Lizenzen für KI-Werkzeuge
                - Rechtsberatung zum Datenschutz

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer. Vor Ort kommen Reisekosten hinzu, die wir vorher abstimmen.
                MD,
        ],
        'en' => [
            'slug' => 'ai-at-work-workshop',
            'title' => 'AI at work: hands-on workshop',
            'teaser' => 'Use ChatGPT and similar tools sensibly and safely at work, on your own tasks.',
            'meta' => 'AI at work: hands-on workshop for small teams. Copy, emails and research with AI assistants, writing good prompts and respecting data protection.',
            'summary' => 'In this workshop you and your team learn to use AI assistants for emails, copy and research. We practise on your own tasks and clarify which data must stay out.',
            'facts' => [
                ['label' => 'Format', 'value' => '2 hours, online or on site'],
                ['label' => 'Participants', 'value' => 'up to 6 people'],
                ['label' => 'Effort', 'value' => '3 hours incl. preparation'],
                ['label' => 'Result', 'value' => 'Prompt templates'],
            ],
            'faq' => [
                ['q' => 'Which AI tools are covered?', 'a' => 'Common assistants such as ChatGPT, Claude, Microsoft Copilot or Gemini, depending on what you use or want to use.'],
                ['q' => 'May we enter customer data?', 'a' => 'Usually not into public versions. In the workshop we clarify what is allowed and what is not.'],
                ['q' => 'Do we need paid accounts?', 'a' => 'No, free versions are enough for learning.'],
            ],
            'body' => <<<'MD'
                AI saves time when you know what to use it for and what not.

                ## What is included

                - Preliminary call about your typical tasks
                - Workshop with exercises on your examples
                - Rules for handling data safely
                - Templates for good prompts

                ## How it works

                1. In a preliminary call we collect your tasks.
                2. I prepare matching exercises.
                3. In the workshop everyone works hands-on.

                ## Not included

                - Licences for AI tools
                - Legal advice on data protection

                ## Good to know

                Fixed price including 19 % VAT. On-site sessions add travel costs, agreed beforehand.
                MD,
        ],
    ],
    [
        'category' => 'schulung', 'tags' => 'it-sicherheit,phishing,schulung', 'hours' => 2, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'IT-Sicherheitsschulung für Mitarbeitende',
        'prompt' => 'a fishing hook pulling at an email envelope, a team member spotting it with a shield badge',
        'de' => [
            'slug' => 'it-sicherheit-fuer-mitarbeitende',
            'title' => 'IT-Sicherheit für Mitarbeitende',
            'teaser' => 'Phishing erkennen, sichere Passwörter, richtig reagieren: die wichtigsten Regeln in 90 Minuten.',
            'meta' => 'IT-Sicherheitsschulung für kleine Teams: Phishing erkennen, sichere Passwörter, Zwei-Faktor-Anmeldung und richtiges Verhalten im Ernstfall. Praxisnah.',
            'summary' => 'In 90 Minuten lernt Ihr Team, Phishing-Mails zu erkennen, sichere Passwörter zu nutzen und im Ernstfall richtig zu reagieren. Echte Beispiele machen es greifbar.',
            'facts' => [
                ['label' => 'Format', 'value' => '90 Minuten, online oder vor Ort'],
                ['label' => 'Teilnehmende', 'value' => 'bis zu 6 Personen'],
                ['label' => 'Aufwand', 'value' => '2 Stunden inkl. Vorbereitung'],
                ['label' => 'Ergebnis', 'value' => 'Merkblatt für alle'],
            ],
            'faq' => [
                ['q' => 'Warum eine Schulung statt eines Virenscanners?', 'a' => 'Die meisten Angriffe beginnen mit einer E-Mail, die jemand öffnet. Technik hilft, aber aufmerksame Menschen helfen mehr.'],
                ['q' => 'Gibt es eine Teilnahmebestätigung?', 'a' => 'Ja, auf Wunsch für jede Person.'],
                ['q' => 'Wie oft sollte man das wiederholen?', 'a' => 'Einmal im Jahr ist ein guter Rhythmus.'],
            ],
            'body' => <<<'MD'
                Die meisten Angriffe zielen auf Menschen, nicht auf Technik.

                ## Das ist enthalten

                - Phishing erkennen an echten Beispielen
                - Passwörter, Passwortmanager und Zwei-Faktor-Anmeldung
                - Verhalten im Ernstfall: wen informieren, was tun
                - Merkblatt für alle Teilnehmenden

                ## So läuft es ab

                1. Wir vereinbaren den Termin.
                2. Ich passe die Beispiele an Ihre Branche an.
                3. Nach der Schulung bekommen alle das Merkblatt.

                ## Nicht enthalten

                - Simulierte Phishing-Kampagnen
                - Technische Sicherheitsprüfung

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer. Vor Ort kommen Reisekosten hinzu.
                MD,
        ],
        'en' => [
            'slug' => 'it-security-awareness-training',
            'title' => 'IT security awareness training',
            'teaser' => 'Spot phishing, use strong passwords, react correctly: the key rules in 90 minutes.',
            'meta' => 'IT security training for small teams: spot phishing, strong passwords, two-factor sign-in and the right response in an emergency. Practical.',
            'summary' => 'In 90 minutes your team learns to spot phishing emails, use strong passwords and react correctly in an emergency. Real examples make it tangible.',
            'facts' => [
                ['label' => 'Format', 'value' => '90 minutes, online or on site'],
                ['label' => 'Participants', 'value' => 'up to 6 people'],
                ['label' => 'Effort', 'value' => '2 hours incl. preparation'],
                ['label' => 'Result', 'value' => 'Fact sheet for everyone'],
            ],
            'faq' => [
                ['q' => 'Why training instead of antivirus?', 'a' => 'Most attacks start with an email someone opens. Technology helps, attentive people help more.'],
                ['q' => 'Is there a certificate of attendance?', 'a' => 'Yes, for each person on request.'],
                ['q' => 'How often should it be repeated?', 'a' => 'Once a year is a good rhythm.'],
            ],
            'body' => <<<'MD'
                Most attacks target people, not technology.

                ## What is included

                - Spotting phishing with real examples
                - Passwords, password managers and two-factor sign-in
                - What to do in an emergency: whom to tell, what to do
                - Fact sheet for all participants

                ## How it works

                1. We agree on a date.
                2. I adapt the examples to your industry.
                3. After the training everyone receives the fact sheet.

                ## Not included

                - Simulated phishing campaigns
                - Technical security audit

                ## Good to know

                Fixed price including 19 % VAT. On-site sessions add travel costs.
                MD,
        ],
    ],
    [
        'category' => 'schulung', 'tags' => 'google-unternehmensprofil,bewertungen', 'hours' => 2, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Schulung zur Pflege des Google-Unternehmensprofils',
        'prompt' => 'a phone showing a business listing with stars, photos and a posts feed, a hand adding a new photo',
        'de' => [
            'slug' => 'google-unternehmensprofil-selbst-pflegen',
            'title' => 'Google-Unternehmensprofil selbst pflegen',
            'teaser' => 'Beiträge, Fotos, Öffnungszeiten und Bewertungen selbst im Griff, in einer Stunde gelernt.',
            'meta' => 'Google-Unternehmensprofil selbst pflegen: Einweisung zu Beiträgen, Fotos, Öffnungszeiten und Antworten auf Bewertungen. Mehr Sichtbarkeit auf Google Maps.',
            'summary' => 'Ich zeige Ihnen, wie Sie Ihr Google-Unternehmensprofil selbst aktuell halten. Sie üben Beiträge, Fotos, Sonderöffnungszeiten und gute Antworten auf Bewertungen.',
            'facts' => [
                ['label' => 'Format', 'value' => '60 Minuten per Video'],
                ['label' => 'Teilnehmende', 'value' => 'bis zu 3 Personen'],
                ['label' => 'Aufwand', 'value' => '2 Stunden inkl. Vorbereitung'],
                ['label' => 'Ergebnis', 'value' => 'Monats-Checkliste'],
            ],
            'faq' => [
                ['q' => 'Brauche ich schon ein Profil?', 'a' => 'Ja. Ohne Profil buchen Sie zuerst „Google-Unternehmensprofil einrichten“.'],
                ['q' => 'Wie oft sollte ich etwas posten?', 'a' => 'Ein bis zwei Beiträge im Monat reichen, regelmäßig ist wichtiger als häufig.'],
                ['q' => 'Wie antworte ich auf eine schlechte Bewertung?', 'a' => 'Sachlich, freundlich und mit einem Angebot zur Klärung. Wir üben das an Beispielen.'],
            ],
            'body' => <<<'MD'
                Ein gepflegtes Profil bringt Anrufe. Die Pflege dauert wenige Minuten im Monat.

                ## Das ist enthalten

                - Beiträge und Fotos veröffentlichen
                - Öffnungszeiten und Sonderzeiten pflegen
                - Auf Bewertungen antworten
                - Monats-Checkliste

                ## So läuft es ab

                1. Wir vereinbaren einen Termin.
                2. Ich sehe mir Ihr Profil vorab an.
                3. In der Einweisung arbeiten Sie an Ihrem eigenen Profil.

                ## Nicht enthalten

                - Einrichtung eines neuen Profils (eigenes Paket)

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'maintain-your-google-business-profile',
            'title' => 'Maintain your Google Business Profile',
            'teaser' => 'Posts, photos, opening hours and reviews under your own control, learned in an hour.',
            'meta' => 'Maintain your Google Business Profile: training on posts, photos, opening hours and review replies. More visibility on Google Maps.',
            'summary' => 'I show you how to keep your Google Business Profile up to date yourself. You practise posts, photos, special opening hours and good replies to reviews.',
            'facts' => [
                ['label' => 'Format', 'value' => '60 minutes by video'],
                ['label' => 'Participants', 'value' => 'up to 3 people'],
                ['label' => 'Effort', 'value' => '2 hours incl. preparation'],
                ['label' => 'Result', 'value' => 'Monthly checklist'],
            ],
            'faq' => [
                ['q' => 'Do I already need a profile?', 'a' => 'Yes. Without one, book "Google Business Profile setup" first.'],
                ['q' => 'How often should I post?', 'a' => 'One or two posts a month are enough; regular beats frequent.'],
                ['q' => 'How do I reply to a bad review?', 'a' => 'Factual, friendly and with an offer to sort it out. We practise with examples.'],
            ],
            'body' => <<<'MD'
                A well-kept profile brings calls. Keeping it up takes a few minutes a month.

                ## What is included

                - Publishing posts and photos
                - Maintaining opening and special hours
                - Replying to reviews
                - Monthly checklist

                ## How it works

                1. We agree on a date.
                2. I review your profile beforehand.
                3. In the session you work on your own profile.

                ## Not included

                - Setting up a new profile (separate package)

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'schulung', 'tags' => 'social-media,instagram,marketing', 'hours' => 2, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Social-Media-Grundlagen für kleine Betriebe',
        'prompt' => 'a phone with a simple content calendar and a few post tiles, a shop owner planning with a coffee',
        'de' => [
            'slug' => 'social-media-grundlagen-fuer-betriebe',
            'title' => 'Social-Media-Grundlagen für Betriebe',
            'teaser' => 'Welche Plattform passt, was posten, wie oft: ein einfacher Plan für wenig Zeit.',
            'meta' => 'Social-Media-Grundlagen für kleine Betriebe: passende Plattform wählen, Themenplan erstellen, Beiträge vorplanen. Ein realistischer Plan für wenig Zeit.',
            'summary' => 'Ich helfe Ihnen, die passende Plattform zu wählen und einen einfachen Themenplan aufzustellen. Sie lernen, Beiträge vorzuplanen, damit Social Media nicht jeden Tag Zeit kostet.',
            'facts' => [
                ['label' => 'Format', 'value' => '90 Minuten per Video'],
                ['label' => 'Teilnehmende', 'value' => 'bis zu 3 Personen'],
                ['label' => 'Aufwand', 'value' => '2 Stunden inkl. Vorbereitung'],
                ['label' => 'Ergebnis', 'value' => 'Themenplan für 3 Monate'],
            ],
            'faq' => [
                ['q' => 'Muss ich auf allen Plattformen sein?', 'a' => 'Nein. Eine Plattform, die Sie regelmäßig bespielen, ist besser als drei, die verwaisen.'],
                ['q' => 'Übernehmen Sie das Posten für mich?', 'a' => 'Nein, dieses Paket macht Sie selbstständig. Für laufende Betreuung gibt es bessere Ansprechpartner.'],
                ['q' => 'Geht es auch um Werbung?', 'a' => 'Nur am Rande. Der Schwerpunkt liegt auf unbezahlten Beiträgen.'],
            ],
            'body' => <<<'MD'
                Social Media muss nicht täglich Zeit fressen. Mit Plan reichen wenige Stunden im Monat.

                ## Das ist enthalten

                - Auswahl der passenden Plattform
                - Themenplan für drei Monate
                - Beiträge vorplanen mit kostenlosen Werkzeugen
                - Tipps für Fotos mit dem Handy

                ## So läuft es ab

                1. Ich sehe mir Ihren Betrieb und Ihre Mitbewerber an.
                2. In der Einweisung erstellen wir gemeinsam den Plan.
                3. Sie bekommen den Plan als Tabelle.

                ## Nicht enthalten

                - Erstellen und Posten von Beiträgen
                - Bezahlte Werbung

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'social-media-basics-for-businesses',
            'title' => 'Social media basics for businesses',
            'teaser' => 'Which platform fits, what to post, how often: a simple plan for little time.',
            'meta' => 'Social media basics for small businesses: choose the right platform, build a topic plan, schedule posts. A realistic plan for little time.',
            'summary' => 'I help you choose the right platform and set up a simple topic plan. You learn to schedule posts so social media does not eat time every day.',
            'facts' => [
                ['label' => 'Format', 'value' => '90 minutes by video'],
                ['label' => 'Participants', 'value' => 'up to 3 people'],
                ['label' => 'Effort', 'value' => '2 hours incl. preparation'],
                ['label' => 'Result', 'value' => 'Topic plan for 3 months'],
            ],
            'faq' => [
                ['q' => 'Do I need to be on every platform?', 'a' => 'No. One platform you use regularly beats three that go quiet.'],
                ['q' => 'Will you post for me?', 'a' => 'No, this package makes you independent. Others are better suited to ongoing management.'],
                ['q' => 'Does it cover advertising?', 'a' => 'Only briefly. The focus is on unpaid posts.'],
            ],
            'body' => <<<'MD'
                Social media does not have to eat time daily. With a plan, a few hours a month are enough.

                ## What is included

                - Choosing the right platform
                - Topic plan for three months
                - Scheduling posts with free tools
                - Tips for photos with your phone

                ## How it works

                1. I look at your business and your competitors.
                2. In the session we build the plan together.
                3. You receive the plan as a spreadsheet.

                ## Not included

                - Creating and posting content
                - Paid advertising

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'schulung', 'tags' => 'outlook,e-mail,organisation', 'hours' => 2, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Aufgeräumtes E-Mail-Postfach',
        'prompt' => 'an overflowing inbox turning into a tidy one with folders, rules and an empty inbox badge',
        'de' => [
            'slug' => 'e-mail-postfach-im-griff',
            'title' => 'E-Mail-Postfach im Griff',
            'teaser' => 'Regeln, Ordner und Vorlagen, mit denen das Postfach nicht mehr überläuft.',
            'meta' => 'E-Mail-Postfach im Griff: Einweisung zu Regeln, Ordnern, Vorlagen und Kalender in Outlook, Gmail oder Apple Mail. Weniger Zeit im Posteingang.',
            'summary' => 'In einer Einweisung richten wir Regeln, Ordner und Textvorlagen in Ihrem Postfach ein. Sie lernen eine einfache Methode, mit der der Posteingang leer bleibt.',
            'facts' => [
                ['label' => 'Format', 'value' => '90 Minuten per Video'],
                ['label' => 'Teilnehmende', 'value' => 'bis zu 3 Personen'],
                ['label' => 'Aufwand', 'value' => '2 Stunden inkl. Vorbereitung'],
                ['label' => 'Ergebnis', 'value' => 'Eingerichtete Regeln und Vorlagen'],
            ],
            'faq' => [
                ['q' => 'Für welche Programme?', 'a' => 'Outlook, Gmail und Apple Mail. Andere Programme bitte vorher nennen.'],
                ['q' => 'Werden meine Ordner umgebaut?', 'a' => 'Nur mit Ihnen gemeinsam und nur so weit, wie Sie es möchten.'],
                ['q' => 'Hilft das auch dem Team?', 'a' => 'Ja. Bis zu drei Personen können teilnehmen, gemeinsame Postfächer besprechen wir mit.'],
            ],
            'body' => <<<'MD'
                Zu viele E-Mails sind kein Schicksal, sondern eine Frage der Einstellungen.

                ## Das ist enthalten

                - Regeln zum automatischen Sortieren
                - Ordnerstruktur und Textvorlagen
                - Eine einfache Methode für den leeren Posteingang
                - Kalender und Aufgaben aus E-Mails

                ## So läuft es ab

                1. Sie beschreiben, was Sie am meisten stört.
                2. Ich bereite passende Regeln vor.
                3. In der Einweisung richten wir alles gemeinsam ein.

                ## Nicht enthalten

                - Umzug des Postfachs (eigenes Paket)

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer.
                MD,
        ],
        'en' => [
            'slug' => 'get-your-inbox-under-control',
            'title' => 'Get your inbox under control',
            'teaser' => 'Rules, folders and templates that stop your inbox from overflowing.',
            'meta' => 'Get your inbox under control: training on rules, folders, templates and calendar in Outlook, Gmail or Apple Mail. Less time in the inbox.',
            'summary' => 'In one session we set up rules, folders and reply templates in your mailbox. You learn a simple method that keeps your inbox empty.',
            'facts' => [
                ['label' => 'Format', 'value' => '90 minutes by video'],
                ['label' => 'Participants', 'value' => 'up to 3 people'],
                ['label' => 'Effort', 'value' => '2 hours incl. preparation'],
                ['label' => 'Result', 'value' => 'Rules and templates in place'],
            ],
            'faq' => [
                ['q' => 'For which programs?', 'a' => 'Outlook, Gmail and Apple Mail. Please name other programs beforehand.'],
                ['q' => 'Will my folders be restructured?', 'a' => 'Only together with you and only as far as you want.'],
                ['q' => 'Does it help the team too?', 'a' => 'Yes. Up to three people can join, and we cover shared mailboxes as well.'],
            ],
            'body' => <<<'MD'
                Too many emails are not fate, they are a matter of settings.

                ## What is included

                - Rules for automatic sorting
                - Folder structure and reply templates
                - A simple method for an empty inbox
                - Calendar entries and tasks from emails

                ## How it works

                1. You describe what bothers you most.
                2. I prepare suitable rules.
                3. In the session we set everything up together.

                ## Not included

                - Moving the mailbox (separate package)

                ## Good to know

                Fixed price including 19 % VAT.
                MD,
        ],
    ],
    [
        'category' => 'schulung', 'tags' => 'microsoft-365,teams,einfuehrung', 'hours' => 3, 'rate_cents' => $rate,
        'fulfilment' => 'project', 'image' => $image, 'alt' => 'Einführung in Microsoft Teams für ein Team',
        'prompt' => 'a team video call grid with shared files and chat bubbles, a trainer highlighting a channel list',
        'de' => [
            'slug' => 'microsoft-teams-einfuehrung',
            'title' => 'Microsoft 365 und Teams: Einführung',
            'teaser' => 'Chat, Besprechungen und gemeinsame Dateien: Ihr Team arbeitet ab sofort an einem Ort.',
            'meta' => 'Microsoft 365 und Teams Einführung für kleine Teams: Kanäle, Besprechungen, gemeinsame Dateien und Regeln für die Zusammenarbeit. Online oder vor Ort.',
            'summary' => 'In einer Einführung lernt Ihr Team, Teams-Kanäle, Besprechungen und gemeinsame Dateien zu nutzen. Gemeinsam legen wir Regeln fest, damit E-Mail-Pingpong der Vergangenheit angehört.',
            'facts' => [
                ['label' => 'Format', 'value' => '2 Stunden, online oder vor Ort'],
                ['label' => 'Teilnehmende', 'value' => 'bis zu 6 Personen'],
                ['label' => 'Aufwand', 'value' => '3 Stunden inkl. Vorbereitung'],
                ['label' => 'Ergebnis', 'value' => 'Kanalstruktur und Team-Regeln'],
            ],
            'faq' => [
                ['q' => 'Brauchen wir Microsoft 365 schon?', 'a' => 'Ja. Ohne Lizenz buchen Sie zuerst „Microsoft 365 einrichten“.'],
                ['q' => 'Ersetzt Teams die E-Mail?', 'a' => 'Intern weitgehend, nach außen nicht. Genau das legen wir in den Regeln fest.'],
                ['q' => 'Gibt es Unterlagen?', 'a' => 'Ja, eine kurze Anleitung und die vereinbarten Regeln als PDF.'],
            ],
            'body' => <<<'MD'
                Teams hilft nur, wenn alle wissen, wie es genutzt wird.

                ## Das ist enthalten

                - Vorgespräch und Vorschlag für die Kanalstruktur
                - Einführung: Chat, Kanäle, Besprechungen, Dateien
                - Gemeinsame Regeln für die Zusammenarbeit
                - Kurze Anleitung als PDF

                ## So läuft es ab

                1. Im Vorgespräch klären wir, wie Ihr Team arbeitet.
                2. Ich lege die Kanäle an.
                3. In der Einführung arbeiten alle direkt mit.

                ## Nicht enthalten

                - Lizenzen und Einrichtung von Microsoft 365

                ## Gut zu wissen

                Festpreis inklusive 19 % Umsatzsteuer. Vor Ort kommen Reisekosten hinzu.
                MD,
        ],
        'en' => [
            'slug' => 'microsoft-teams-introduction',
            'title' => 'Microsoft 365 and Teams: introduction',
            'teaser' => 'Chat, meetings and shared files: your team works in one place from now on.',
            'meta' => 'Microsoft 365 and Teams introduction for small teams: channels, meetings, shared files and rules for working together. Online or on site.',
            'summary' => 'In an introduction your team learns to use Teams channels, meetings and shared files. Together we agree rules so email ping-pong becomes a thing of the past.',
            'facts' => [
                ['label' => 'Format', 'value' => '2 hours, online or on site'],
                ['label' => 'Participants', 'value' => 'up to 6 people'],
                ['label' => 'Effort', 'value' => '3 hours incl. preparation'],
                ['label' => 'Result', 'value' => 'Channel structure and team rules'],
            ],
            'faq' => [
                ['q' => 'Do we need Microsoft 365 already?', 'a' => 'Yes. Without a licence, book "Microsoft 365 setup" first.'],
                ['q' => 'Does Teams replace email?', 'a' => 'Internally, largely; externally, no. That is exactly what we set in the rules.'],
                ['q' => 'Are there notes?', 'a' => 'Yes, a short guide and the agreed rules as a PDF.'],
            ],
            'body' => <<<'MD'
                Teams only helps if everyone knows how it is used.

                ## What is included

                - Preliminary call and proposed channel structure
                - Introduction: chat, channels, meetings, files
                - Shared rules for working together
                - Short guide as a PDF

                ## How it works

                1. In a preliminary call we clarify how your team works.
                2. I create the channels.
                3. In the introduction everyone works along.

                ## Not included

                - Licences and setup of Microsoft 365

                ## Good to know

                Fixed price including 19 % VAT. On-site sessions add travel costs.
                MD,
        ],
    ],
];
