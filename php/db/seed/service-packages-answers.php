<?php
declare(strict_types=1);

/**
 * "Kurz gesagt", facts and FAQ for the six packages seeded by
 * `20260915000001_shop_seed_service_packages`, keyed by language and slug.
 * Written only where the fields are still empty.
 */
return [
    'de' => [
        'digital-check-fuer-ihren-betrieb' => [
            'summary' => 'Der Digital-Check ist eine Bestandsaufnahme Ihrer Abläufe und Programme mit schriftlicher Einschätzung. Sie erfahren, was sich zuerst lohnt, was warten kann und welche Kosten zu erwarten sind.',
            'facts' => [
                ['label' => 'Format', 'value' => '1 Termin per Video oder vor Ort'],
                ['label' => 'Dauer', 'value' => 'Ergebnis innerhalb von 10 Werktagen'],
                ['label' => 'Aufwand', 'value' => '4 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Schriftliche Einschätzung mit Reihenfolge'],
            ],
            'faq' => [
                ['q' => 'Für wen ist der Digital-Check?', 'a' => 'Für Selbstständige und kleine Betriebe, die viele Ideen haben und nicht wissen, womit sie anfangen sollen.'],
                ['q' => 'Muss ich danach etwas beauftragen?', 'a' => 'Nein. Der Check ist für sich abgeschlossen.'],
                ['q' => 'Ersetzt der Check eine Steuer- oder Rechtsberatung?', 'a' => 'Nein. Er bewertet Abläufe und Technik, keine Rechts- oder Steuerfragen.'],
            ],
        ],
        'website-check-mit-massnahmenliste' => [
            'summary' => 'Ich prüfe Ihre Website auf Handy-Tauglichkeit, Ladezeit, Auffindbarkeit und Verständlichkeit. Sie bekommen eine sortierte Liste der Maßnahmen, die sich wirklich lohnen.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 bestehende Website'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche'],
                ['label' => 'Aufwand', 'value' => '4 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Sortierte Maßnahmenliste'],
            ],
            'faq' => [
                ['q' => 'Spielt das System meiner Website eine Rolle?', 'a' => 'Nein. Baukasten, WordPress oder etwas anderes: Der Check funktioniert mit jeder Website.'],
                ['q' => 'Setzen Sie die Maßnahmen auch um?', 'a' => 'Auf Wunsch ja, als eigene Leistung. Die Liste können Sie aber auch selbst oder mit jemand anderem abarbeiten.'],
                ['q' => 'Was ist der Unterschied zur technischen SEO?', 'a' => 'Der Check bewertet die ganze Seite und priorisiert. Die technische SEO setzt einen Teil davon um.'],
            ],
        ],
        'google-unternehmensprofil-einrichten' => [
            'summary' => 'Ich richte Ihr Google-Unternehmensprofil ein oder bringe ein bestehendes in Ordnung. Ihr Betrieb erscheint dann in der Google-Suche und bei Maps mit richtigen Angaben, Fotos und Leistungen.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Standort'],
                ['label' => 'Dauer', 'value' => 'abhängig von Googles Bestätigung'],
                ['label' => 'Aufwand', 'value' => '3 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Vollständiges Profil'],
            ],
            'faq' => [
                ['q' => 'Wie lange dauert die Bestätigung durch Google?', 'a' => 'Das entscheidet Google, meist wenige Tage, manchmal länger.'],
                ['q' => 'Garantieren Sie eine Platzierung bei Maps?', 'a' => 'Nein, das kann niemand. Ein vollständiges Profil verbessert aber die Chancen deutlich.'],
                ['q' => 'Kann ich das Profil danach selbst pflegen?', 'a' => 'Ja. Dafür gibt es auch die Einweisung „Google-Unternehmensprofil selbst pflegen“.'],
            ],
        ],
        'ablauf-analyse-ein-arbeitsablauf' => [
            'summary' => 'Wir gehen einen Arbeitsablauf, der Sie regelmäßig Zeit kostet, Schritt für Schritt durch. Ich zeige Ihnen, was sich streichen, vereinfachen oder automatisieren lässt.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '1 Arbeitsablauf'],
                ['label' => 'Dauer', 'value' => 'ca. 2 Wochen'],
                ['label' => 'Aufwand', 'value' => '6 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Ablaufbild und Empfehlungen'],
            ],
            'faq' => [
                ['q' => 'Was zählt als ein Ablauf?', 'a' => 'Ein zusammenhängender Vorgang mit Anfang und Ergebnis, zum Beispiel „von der Anfrage bis zum Angebot“.'],
                ['q' => 'Was, wenn es eigentlich zwei Abläufe sind?', 'a' => 'Dann sprechen wir darüber, bevor mehr Aufwand entsteht.'],
                ['q' => 'Setzen Sie die Empfehlungen um?', 'a' => 'Auf Wunsch, etwa mit dem Paket „Einen Arbeitsablauf automatisieren“.'],
            ],
        ],
        'machbarkeitspruefung-schnittstelle' => [
            'summary' => 'Ich prüfe, ob und wie zwei Programme über ihre vorhandenen Schnittstellen Daten austauschen können. Sie erfahren, was es kostet und ob es einen einfacheren Weg gibt.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '2 Programme, 1 Datenfluss'],
                ['label' => 'Dauer', 'value' => 'ca. 1 Woche'],
                ['label' => 'Aufwand', 'value' => '4 Stunden'],
                ['label' => 'Ergebnis', 'value' => 'Schriftliche Einschätzung mit Kostenrahmen'],
            ],
            'faq' => [
                ['q' => 'Was, wenn es nicht geht?', 'a' => 'Auch „geht nicht“ ist ein Ergebnis. Sie wissen es, bevor Geld in eine Entwicklung fließt.'],
                ['q' => 'Brauchen Sie Zugänge zu den Programmen?', 'a' => 'Meist reichen Dokumentation und ein Testzugang. Was genau, kläre ich vorher.'],
                ['q' => 'Bauen Sie die Schnittstelle danach?', 'a' => 'Auf Wunsch, als eigenes Angebot auf Basis der Prüfung.'],
            ],
        ],
        'pflegekontingent-webseite-5-stunden' => [
            'summary' => 'Fünf Stunden für Änderungen an Ihrer bestehenden Website: neue Texte und Bilder, Updates und kleine Korrekturen. Das Kontingent gilt zwölf Monate, ohne jedes Mal einen neuen Auftrag.',
            'facts' => [
                ['label' => 'Umfang', 'value' => '5 Stunden'],
                ['label' => 'Gültigkeit', 'value' => '12 Monate ab Kauf'],
                ['label' => 'Abrechnung', 'value' => 'nach tatsächlichem Aufwand'],
                ['label' => 'Ergebnis', 'value' => 'Stundenübersicht bei jedem Auftrag'],
            ],
            'faq' => [
                ['q' => 'Wie beauftrage ich eine Änderung?', 'a' => 'Per E-Mail. Ich schätze den Aufwand und lege los, wenn Sie zustimmen.'],
                ['q' => 'Was passiert mit ungenutzten Stunden?', 'a' => 'Sie verfallen nach zwölf Monaten.'],
                ['q' => 'Was, wenn eine Aufgabe mehr Zeit braucht?', 'a' => 'Dann sage ich es Ihnen vorher, und Sie entscheiden.'],
            ],
        ],
    ],
    'en' => [
        'digital-check-for-your-business' => [
            'summary' => 'The digital check reviews your workflows and programs and ends in a written assessment. You learn what pays off first, what can wait and which costs to expect.',
            'facts' => [
                ['label' => 'Format', 'value' => '1 session by video or on site'],
                ['label' => 'Duration', 'value' => 'Result within 10 working days'],
                ['label' => 'Effort', 'value' => '4 hours'],
                ['label' => 'Result', 'value' => 'Written assessment with priorities'],
            ],
            'faq' => [
                ['q' => 'Who is the digital check for?', 'a' => 'Freelancers and small businesses with many ideas who do not know where to start.'],
                ['q' => 'Do I have to commission anything afterwards?', 'a' => 'No. The check stands on its own.'],
                ['q' => 'Does it replace tax or legal advice?', 'a' => 'No. It assesses workflows and technology, not legal or tax questions.'],
            ],
        ],
        'website-check-with-action-list' => [
            'summary' => 'I check your website for mobile use, loading speed, findability and clarity. You receive a sorted list of the measures that are genuinely worth doing.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 existing website'],
                ['label' => 'Duration', 'value' => 'about 1 week'],
                ['label' => 'Effort', 'value' => '4 hours'],
                ['label' => 'Result', 'value' => 'Sorted action list'],
            ],
            'faq' => [
                ['q' => 'Does the system behind my site matter?', 'a' => 'No. Website builder, WordPress or something else: the check works with any website.'],
                ['q' => 'Do you carry out the measures too?', 'a' => 'On request, as a separate service. You can also work through the list yourself or with someone else.'],
                ['q' => 'How is it different from technical SEO?', 'a' => 'The check assesses the whole site and sets priorities. Technical SEO carries out part of it.'],
            ],
        ],
        'google-business-profile-setup' => [
            'summary' => 'I set up your Google Business Profile or put an existing one in order. Your business then appears in Google Search and on Maps with correct details, photos and services.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 location'],
                ['label' => 'Duration', 'value' => 'depends on Google\'s verification'],
                ['label' => 'Effort', 'value' => '3 hours'],
                ['label' => 'Result', 'value' => 'Complete profile'],
            ],
            'faq' => [
                ['q' => 'How long does Google take to verify?', 'a' => 'Google decides, usually a few days, sometimes longer.'],
                ['q' => 'Do you guarantee a position on Maps?', 'a' => 'No, nobody can. But a complete profile clearly improves your chances.'],
                ['q' => 'Can I maintain the profile myself afterwards?', 'a' => 'Yes. There is also the training "Maintain your Google Business Profile".'],
            ],
        ],
        'workflow-analysis-one-process' => [
            'summary' => 'We walk through one workflow that regularly costs you time, step by step. I show you what can be dropped, simplified or automated.',
            'facts' => [
                ['label' => 'Scope', 'value' => '1 workflow'],
                ['label' => 'Duration', 'value' => 'about 2 weeks'],
                ['label' => 'Effort', 'value' => '6 hours'],
                ['label' => 'Result', 'value' => 'Process map and recommendations'],
            ],
            'faq' => [
                ['q' => 'What counts as one workflow?', 'a' => 'One connected sequence with a start and a result, for example "from enquiry to quote".'],
                ['q' => 'What if it is really two workflows?', 'a' => 'Then we talk about it before any extra effort arises.'],
                ['q' => 'Do you implement the recommendations?', 'a' => 'On request, for example with the package "Automate one workflow".'],
            ],
        ],
        'integration-feasibility-check' => [
            'summary' => 'I check whether and how two programs can exchange data through their existing interfaces. You learn what it costs and whether there is a simpler way.',
            'facts' => [
                ['label' => 'Scope', 'value' => '2 programs, 1 data flow'],
                ['label' => 'Duration', 'value' => 'about 1 week'],
                ['label' => 'Effort', 'value' => '4 hours'],
                ['label' => 'Result', 'value' => 'Written assessment with cost frame'],
            ],
            'faq' => [
                ['q' => 'What if it cannot be done?', 'a' => '"It cannot be done" is a result too. You know before any money goes into development.'],
                ['q' => 'Do you need access to the programs?', 'a' => 'Usually documentation and a test account are enough. I clarify the details first.'],
                ['q' => 'Do you build the integration afterwards?', 'a' => 'On request, as a separate offer based on the check.'],
            ],
        ],
        'website-care-5-hours' => [
            'summary' => 'Five hours for changes to your existing website: new copy and images, updates and small fixes. The package is valid for twelve months, without a new order each time.',
            'facts' => [
                ['label' => 'Scope', 'value' => '5 hours'],
                ['label' => 'Validity', 'value' => '12 months from purchase'],
                ['label' => 'Billing', 'value' => 'by actual time spent'],
                ['label' => 'Result', 'value' => 'Hours overview with each task'],
            ],
            'faq' => [
                ['q' => 'How do I request a change?', 'a' => 'By email. I estimate the effort and start once you agree.'],
                ['q' => 'What happens to unused hours?', 'a' => 'They expire after twelve months.'],
                ['q' => 'What if a task needs more time?', 'a' => 'Then I tell you beforehand, and you decide.'],
            ],
        ],
    ],
];
