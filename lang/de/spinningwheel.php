<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * German language strings for mod_spinningwheel.
 *
 * @package    mod_spinningwheel
 * @copyright  2026 Andrea Juettner, andrea.juettner@eledia.de; AI-assisted by Claude (Anthropic).
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['modulename'] = 'Spinning Wheel';
$string['modulename_help'] = 'Mit dem Spinning Wheel bringen Sie Schwung und Spannung in Ihren Unterricht! Das animierte Rad wählt zufällig aus frei konfigurierbaren Einträgen — ob Fragen, Themen, Aufgaben oder direkt aus den Kursteilnehmer/innen. Mit Soundeffekten, Konfetti-Animation und anpassbaren Farben wird jede Drehung zum Erlebnis.

<strong>So setzen Sie das Spinning Wheel ein:</strong>
<ul>
<li><strong>Faire Beteiligung fördern:</strong> Tragen Sie die Namen Ihrer Teilnehmer/innen ein (oder nutzen Sie die automatische Teilnehmerliste) und lassen Sie das Rad entscheiden, wer als Nächstes dran ist. Das sorgt für eine gleichmäßige Beteiligung und nimmt den Druck, sich selbst melden zu müssen — niemand wird bloßgestellt, denn „das Rad hat entschieden".</li>
<li><strong>Themen und Aufgaben spannend verteilen:</strong> Befüllen Sie das Rad mit Projektthemen, Referatsthemen oder Aufgaben und lassen Sie Ihre Gruppen drehen. So wird die Verteilung zum fairen Spielmoment statt zur endlosen Diskussion. Tipp: Mit der Option „Nach dem Drehen entfernen" wird jedes Thema nur einmal vergeben.</li>
<li><strong>Eisbrecher und Kennenlernrunden auflockern:</strong> Starten Sie Ihren Kurs mit kreativen Fragen auf dem Rad — von „Was ist dein Lieblingsgericht?" bis „Welche Superkraft hättest du gerne?". Das Zufallselement macht Vorstellungsrunden lebendiger und nimmt schüchternen Teilnehmer/innen die Hemmung, denn alle beantworten die gleiche zufällige Frage.</li>
</ul>';
$string['modulenameplural'] = 'Glücksräder';
$string['pluginname'] = 'Spinning Wheel';
$string['pluginadministration'] = 'Spinning Wheel Administration';

// Capabilities.
$string['spinningwheel:addinstance'] = 'Neues Spinning Wheel hinzufügen';
$string['spinningwheel:view'] = 'Spinning Wheel ansehen';
$string['spinningwheel:spin'] = 'Rad drehen';
$string['spinningwheel:viewhistory'] = 'Drehverlauf ansehen';
$string['spinningwheel:clearhistory'] = 'Drehverlauf löschen';

// Form.
$string['entrysource'] = 'Eintragsquelle';
$string['entrysource_help'] = 'Wählen Sie, ob das Rad mit eingeschriebenen Kursteilnehmern, manuellen Einträgen oder Kursaktivitäten befüllt werden soll. Die Option "Kursaktivitäten" erfordert das Plugin availability_spinningwheel zur Steuerung des Aktivitätszugangs.';
$string['entrysource_participants'] = 'Kursteilnehmer/innen';
$string['entrysource_manual'] = 'Manuelle Einträge';
$string['entrysource_activities'] = 'Kursaktivitäten';
$string['entrysource_activities_notinstalled'] = '<small class="text-muted">ⓘ Die Eintragsquelle "Kursaktivitäten" erfordert das Plugin <strong>availability_spinningwheel</strong>. <a href="https://github.com/andreajuettner/moodle-availability_spinningwheel" target="_blank">Mehr erfahren</a></small>';
$string['rolefilter'] = 'Rollenfilter';
$string['rolefilter_help'] = 'Nur Nutzer/innen mit den ausgewählten Rollen auf dem Rad anzeigen. Leer lassen, um alle eingeschriebenen Nutzer/innen einzubeziehen.';
$string['manualentries'] = 'Manuelle Einträge';
$string['manualentries_help'] = 'Geben Sie einen Namen oder Eintrag pro Zeile ein. Diese erscheinen als Segmente auf dem Rad.';
$string['removeafter'] = 'Nach Auswahl entfernen';
$string['removeafter_help'] = 'Wenn aktiviert, werden ausgewählte Einträge bei nachfolgenden Drehungen vom Rad entfernt.';
$string['spintime'] = 'Drehdauer';
$string['maxspins'] = 'Maximale Drehungen';
$string['maxspins_help'] = 'Maximale Anzahl erlaubter Drehungen pro Sitzung. 0 = unbegrenzt.';
$string['allowstudentspin'] = 'Teilnehmer/innen dürfen drehen';
$string['allowstudentspin_help'] = 'Wenn aktiviert, können Teilnehmer/innen mit Ansichtsberechtigung auch das Rad drehen.';
$string['color'] = 'Farbe {$a}';
$string['colors'] = 'Segmentfarben';
$string['colors_help'] = 'Bis zu 6 Hex-Farben (z.B. #FF6384) für die Radsegmente festlegen. Leer lassen für Standardfarben. Die Farben werden zyklisch auf die Segmente verteilt.';
$string['behaviour'] = 'Verhalten';
$string['permissions'] = 'Berechtigungen';

// View.
$string['spin'] = 'Drehen!';
$string['history'] = 'Drehverlauf';
$string['noentries'] = 'Keine Einträge für das Rad verfügbar.';
$string['result'] = 'Ergebnis: {$a}';
$string['spunby'] = 'Gedreht von';
$string['selectedentry'] = 'Ausgewählt';
$string['spinnedat'] = 'Zeitpunkt';
$string['nospinsyet'] = 'Noch keine Drehungen aufgezeichnet.';
$string['clearhistory'] = 'Verlauf löschen';
$string['clearhistoryconfirm'] = 'Möchten Sie wirklich alle Drehaufzeichnungen löschen? Dies kann nicht rückgängig gemacht werden.';
$string['historycleared'] = 'Der Drehverlauf wurde gelöscht.';
$string['spincount'] = '{$a} Drehung(en)';
$string['maxspinsreached'] = 'Maximale Anzahl an Drehungen erreicht.';
$string['removespins'] = 'Alle Drehaufzeichnungen entfernen';
$string['startnow'] = 'Jetzt bearbeiten';
$string['later'] = 'Später';
$string['completed'] = '(abgeschlossen)';
$string['lastactivity_notice'] = 'Letzte verbleibende Aktivität: <strong>{$a}</strong>';
$string['allcompleted_notice'] = 'Herzlichen Glückwunsch! Sie haben alle Aktivitäten abgeschlossen.';
$string['pendingactivity'] = 'Sie müssen zuerst \'{$a}\' abschließen, bevor Sie erneut drehen können.';
$string['pendingactivity_notice'] = 'Bitte schließen Sie zuerst <strong>{$a}</strong> ab, bevor Sie erneut drehen.';
$string['page-mod-spinningwheel-x'] = 'Jede Spinning Wheel-Modulseite';

// Completion.
$string['completionspin'] = 'Teilnehmer/in muss das Rad drehen';
$string['completiondetail:spin'] = 'Rad drehen';

// Events.
$string['eventwheel_spun'] = 'Rad gedreht';

// Privacy.
$string['privacy:metadata:spinningwheel_entries'] = 'Rad-Einträge, die auf eingeschriebene Nutzer/innen verweisen.';
$string['privacy:metadata:spinningwheel_entries:userid'] = 'Die ID der als Radeintrag gelisteten Person.';
$string['privacy:metadata:spinningwheel_entries:text'] = 'Der Anzeigetext des Eintrags.';
$string['privacy:metadata:spinningwheel_entries:timecreated'] = 'Der Zeitpunkt, an dem der Eintrag erstellt wurde.';
$string['privacy:metadata:spinningwheel_spins'] = 'Aufzeichnungen der von Nutzer/innen durchgeführten Rad-Drehungen.';
$string['privacy:metadata:spinningwheel_spins:userid'] = 'Die ID der Person, die das Rad gedreht hat.';
$string['privacy:metadata:spinningwheel_spins:selectedtext'] = 'Der Text des ausgewählten Eintrags.';
$string['privacy:metadata:spinningwheel_spins:selecteduserid'] = 'Die ID der Person, die durch die Drehung ausgewählt wurde.';
$string['privacy:metadata:spinningwheel_spins:timecreated'] = 'Der Zeitpunkt der Drehung.';
$string['privacy:metadata:spinningwheel_spins:selectedentryid'] = 'Die ID des ausgewählten Eintrags.';
$string['privacy:metadata:spinningwheel_spins:groupid'] = 'Die Gruppen-ID, die beim Drehen des Rades verwendet wurde.';
$string['deleteduser'] = 'Gelöschte/r Nutzer/in';

// Anzeige & Darstellung.
$string['displaymode'] = 'Anzeigemodus';
$string['displaymode_help'] = 'Wählen Sie, wie Teilnehmereinträge auf dem Rad angezeigt werden. Nur Name zeigt Text, Name + Bild zeigt beides, Nur Bild zeigt Profilbilder ohne Namen.';
$string['displaymode_name'] = 'Nur Name';
$string['displaymode_namepic'] = 'Name + Profilbild';
$string['displaymode_pic'] = 'Nur Profilbild';
$string['maxvisible'] = 'Maximale sichtbare Einträge';
$string['maxvisible_help'] = 'Begrenzt, wie viele Einträge gleichzeitig auf dem Rad angezeigt werden, um die Lesbarkeit zu erhalten. 0 = keine Begrenzung. Bei Begrenzung wird eine zufällige Auswahl angezeigt, aber alle Einträge bleiben wählbar.';
$string['nameformat'] = 'Namensformat';
$string['nameformat_help'] = 'Wählen Sie, wie Teilnehmernamen auf dem Rad angezeigt werden. Diese Einstellung gilt nur, wenn die Eintragsquelle auf Kursteilnehmer/innen gesetzt ist.';
$string['nameformat_full'] = 'Vollständiger Name';
$string['nameformat_first'] = 'Nur Vorname';
$string['nameformat_last'] = 'Nur Nachname';
$string['nameformat_firstinitial'] = 'Vorname + Initiale';
$string['showtitle'] = 'Aktivitätstitel anzeigen';
$string['showtitle_help'] = 'Zeigt den Aktivitätsnamen über dem Rad an. Dies ist vor allem nützlich, wenn das Rad auf der Kursseite eingebettet ist, wo der Moodle-Aktivitätskopf nicht angezeigt wird.';
$string['showshadow'] = 'Radschatten anzeigen';
$string['embedoncourse'] = 'Anzeigen';
$string['embedoncourse_help'] = 'Wählen Sie, wie das Spinning Wheel auf der Kursseite angezeigt wird:<br>
<strong>Automatisch:</strong> Standard-Aktivitätslink (Klick öffnet die Aktivität).<br>
<strong>Einbetten:</strong> Das Rad wird direkt auf der Kursseite angezeigt.<br>
<strong>Öffnen:</strong> Klick öffnet das Rad bildschirmfüllend im selben Fenster (mit Zurück-Button).<br>
<strong>Als Popup-Fenster:</strong> Klick öffnet das Rad in einem kleinen Popup-Fenster.';
$string['embedoncourse_auto'] = 'Automatisch';
$string['embedoncourse_embed'] = 'Einbetten';
$string['embedoncourse_open'] = 'Öffnen';
$string['embedoncourse_popup'] = 'Als Popup-Fenster';
$string['backtocourse'] = 'Zurück zum Kurs';

// Während der Drehung.
$string['duringspin'] = 'Während der Drehung';
$string['tickingsound'] = 'Tick-Geräusch während der Drehung';
$string['tickingsound_help'] = 'Wenn aktiviert, ertönt ein Tick-Geräusch, wenn das Rad während der Drehung jedes Segment passiert.';

// Nach der Drehung.
$string['afterspin'] = 'Nach der Drehung';
$string['showconfetti'] = 'Konfetti-Feier anzeigen';
$string['showconfetti_help'] = 'Wenn aktiviert, wird nach jedem Drehergebnis eine Konfetti-Animation abgespielt.';
$string['winnermessage'] = 'Benutzerdefinierte Gewinnernachricht';
$string['winnermessage_help'] = 'Benutzerdefinierte Nachricht, die im Ergebnis-Popup nach dem Drehen angezeigt wird. Leer lassen für die Standardnachricht.';
$string['celebratesound'] = 'Jubel-Sound';
$string['celebratesound_help'] = 'Wählen Sie einen Jubel-Soundeffekt, der nach dem Drehergebnis abgespielt wird. Soundeffekte von Pixabay (Pixabay Content License).';
$string['celebratesound_off'] = 'Aus';
$string['celebratesound_1'] = 'Applaus & Jubel';
$string['celebratesound_2'] = 'Applaus';
$string['celebratesound_3'] = 'Publikumsapplaus';

// Mobile.
$string['mobile:spinresult'] = 'Letztes Drehergebnis';
$string['mobile:taptoopen'] = 'Tippen, um im Browser zu öffnen';

// Didaktische Beispiele.
$string['spinningwheel:viewexamples'] = 'Didaktische Beispiele ansehen';
$string['examples'] = 'Didaktische Beispiele';
$string['examples_intro'] = 'Die folgenden Szenarien zeigen, wie das Spinning Wheel im Unterricht eingesetzt werden kann. Jedes Beispiel enthält eine Ausgangssituation, eine Schritt-für-Schritt-Umsetzung, den didaktischen Mehrwert und empfohlene Plugin-Einstellungen.';
$string['example_situation'] = 'Ausgangssituation';
$string['example_implementation'] = 'Umsetzung';
$string['example_benefit'] = 'Didaktischer Mehrwert';
$string['example_settings'] = 'Empfohlene Einstellungen';

// Beispiel 1.
$string['example1_title'] = 'Faire Beteiligung im Unterrichtsgespräch';
$string['example1_situation'] = 'In vielen Klassen melden sich immer dieselben Lernenden, während andere sich zurückhalten. Die Lehrkraft möchte eine gleichmäßige Beteiligung fördern, ohne einzelne Personen bloßzustellen.';
$string['example1_implementation'] = 'Das Spinning Wheel wird mit allen Kursteilnehmer/innen bestückt. Vor einer Frage oder Aufgabe dreht die Lehrkraft das Rad am Smartboard oder per geteiltem Bildschirm. Die ausgewählte Person beantwortet die Frage, löst eine Aufgabe oder fasst einen Textabschnitt zusammen. Durch die Aktivierung von „Nach Auswahl entfernen" wird sichergestellt, dass im Laufe der Stunde jede/r einmal drankommt — kein Verstecken, aber auch kein Bloßstellen, da der Zufall entscheidet.';
$string['example1_benefit'] = 'Die Zufallsauswahl wird von Lernenden als fair empfunden und erzeugt eine positive Grundspannung („Bin ich als Nächstes dran?"), die die Aufmerksamkeit erhöht. Gleichzeitig nimmt sie den sozialen Druck, sich aktiv melden zu müssen.';
$string['example1_settings'] = 'Eintragsquelle: Kursteilnehmer/innen · Anzeige: Name + Profilbild · Nach Auswahl entfernen: aktiviert · Rollenfilter: nur Teilnehmer/innen (Lehrkräfte ausschließen)';

// Beispiel 2.
$string['example2_title'] = 'Themen- und Aufgabenverteilung bei Gruppenarbeit';
$string['example2_situation'] = 'Bei Projekten oder Referaten müssen Themen auf Gruppen oder Einzelpersonen verteilt werden. Häufig entstehen Diskussionen („Ich will nicht dieses Thema!"), die Zeit kosten und zu Frustration führen.';
$string['example2_implementation'] = 'Die Lehrkraft legt alle verfügbaren Themen, Aufgabenstellungen oder Projektbereiche als manuelle Einträge an — z. B. „Klimawandel und Ozeane", „Erneuerbare Energien", „Biodiversität im Regenwald". Die Lehrkraft dreht das Rad am Smartboard für jede Gruppe nacheinander. Das zugeteilte Thema wird durch „Nach Auswahl entfernen" vom Rad genommen, sodass keine Doppelvergabe möglich ist. Der Drehverlauf dokumentiert die Zuordnung automatisch.';
$string['example2_benefit'] = 'Die Zufallszuteilung wird als neutral und unparteiisch wahrgenommen. Es gibt keinen Raum für den Vorwurf, die Lehrkraft habe bevorzugt oder benachteiligt. Gleichzeitig lernen Schüler/innen, sich auf unerwartete Themen einzulassen — eine wichtige Kompetenz für flexibles Arbeiten.';
$string['example2_settings'] = 'Eintragsquelle: Manuell · Nach Auswahl entfernen: aktiviert · Konfetti + Jubel-Sound: aktiviert (macht den Moment feierlich statt frustrierend)';

// Beispiel 3.
$string['example3_title'] = 'Spielerische Quiz- und Wiederholungsrunden';
$string['example3_situation'] = 'Wiederholungsphasen vor Prüfungen empfinden viele Lernende als monoton. Die Motivation sinkt, wenn die Lehrkraft einfach Fragen der Reihe nach abarbeitet.';
$string['example3_implementation'] = 'Auf dem Rad werden Themenkategorien platziert — z. B. im Englischunterricht: „Vocabulary", „Grammar", „Reading Comprehension", „Listening", „Culture". Die Lehrkraft (oder ein/e Schüler/in) dreht das Rad, und die Klasse bearbeitet gemeinsam eine Frage aus der erdrehten Kategorie. Da die Kategorien nicht entfernt werden, kann dasselbe Thema mehrfach drankommen — wie beim echten Lernen, wo Wiederholung wichtig ist.';
$string['example3_benefit'] = 'Der Spielcharakter steigert die intrinsische Motivation. Die Unvorhersagbarkeit, welche Kategorie als Nächstes kommt, hält die Aufmerksamkeit hoch. Die Lehrkraft kann zusätzlich Wettbewerbselemente einbauen (Punkte pro richtige Antwort, Teamwettbewerb).';
$string['example3_settings'] = 'Eintragsquelle: Manuell · Nach Auswahl entfernen: deaktiviert (Kategorien bleiben) · Eigene Farben: pro Kategorie eine Farbe für Wiedererkennung · Tick-Geräusch: aktiviert (baut Spannung auf)';

// Beispiel 4.
$string['example4_title'] = 'Eisbrecher und Kennenlernaktivitäten';
$string['example4_situation'] = 'Zu Beginn eines Kurses, Schuljahres oder Workshops kennen sich die Teilnehmer/innen oft noch nicht. Klassische Vorstellungsrunden („Sag deinen Namen und ein Hobby") sind wenig kreativ und erzeugen Langeweile.';
$string['example4_implementation'] = 'Das Rad wird mit originellen Kennenlernfragen bestückt: „Was ist dein ungewöhnlichstes Talent?", „Welches Buch hat dich am meisten beeinflusst?", „Wenn du eine Zeitreise machen könntest — wohin?", „Was war der beste Rat, den du je bekommen hast?". Zunächst wird eine Person per Namensrad ausgewählt, dann dreht diese Person das Fragenrad. So entstehen zwei Zufallsmomente, die für Überraschung und Gesprächsstoff sorgen.';
$string['example4_benefit'] = 'Die ungewöhnlichen Fragen führen zu persönlicheren Antworten als Standardvorstellungsrunden. Der spielerische Rahmen senkt die Hemmschwelle. Durch die Zufallsauswahl fühlt sich niemand herausgepickt.';
$string['example4_settings'] = 'Zwei separate Glücksräder im Kurs: eines mit Namen (Teilnehmer), eines mit Fragen (manuell) · Konfetti + Jubel-Sound: aktiviert (lockere Atmosphäre) · Gewinnernachricht: z. B. „Du bist dran!"';

// Beispiel 5.
$string['example5_title'] = 'Kreative Schreibanlässe und Erzählimpulse';
$string['example5_situation'] = 'Lernende sitzen vor einem leeren Blatt und wissen nicht, worüber sie schreiben sollen. Die freie Themenwahl überfordert manche, während vorgegebene Themen als einengend empfunden werden.';
$string['example5_implementation'] = 'Auf dem Rad stehen kreative Impulse unterschiedlicher Art — Anfangssätze („Es war der letzte Tag vor den Ferien, als …"), Schauplätze („Ein verlassener Bahnhof"), Figuren („Ein sprechender Hund"), Stimmungen („Geheimnisvoll") oder Gegenstände („Ein alter Schlüssel"). Die Lernenden drehen ein- oder mehrmals und kombinieren die erdrehten Elemente zu einer Geschichte. Je mehr Elemente kombiniert werden, desto kreativer und überraschender werden die Ergebnisse. Variante A (gemeinsam): Die Lehrkraft dreht am Bildschirm — alle schreiben zum selben Thema. Variante B (individuell): „Teilnehmer dürfen drehen" ist aktiviert — jede/r öffnet die Aktivität auf dem eigenen Gerät (Tablet, Laptop, Moodle App) und dreht selbst für einen individuellen Impuls.';
$string['example5_benefit'] = 'Der Zufallsimpuls überwindet die „leere Seite"-Blockade und fördert kreatives Denken unter Einschränkungen (sog. „Constraints-based Creativity"). In Gruppenarbeit entstehen aus denselben Impulsen sehr unterschiedliche Texte — ein guter Ausgangspunkt für Textvergleiche und Peer-Feedback.';
$string['example5_settings'] = 'Eintragsquelle: Manuell · Nach Auswahl entfernen: deaktiviert (Impulse können mehrfach verwendet werden) · Teilnehmer dürfen drehen: aktiviert (Variante B)';

// Beispiel 6.
$string['example6_title'] = 'Belohnungssystem und positive Verstärkung';
$string['example6_situation'] = 'Die Lehrkraft möchte positives Verhalten, gute Mitarbeit oder besondere Leistungen sichtbar belohnen — ohne auf materielle Belohnungen zurückzugreifen.';
$string['example6_implementation'] = 'Auf dem Rad stehen kleine Privilegien und Belohnungen: „5 Minuten früher in die Pause", „Sitzplatz frei wählen", „Musik während der Stillarbeit", „Hausaufgaben-Joker für morgen", „Du darfst das nächste Spiel aussuchen" oder „Ein Lob an die ganze Klasse". Lernende, die eine besondere Leistung erbracht oder sich positiv hervorgetan haben, dürfen als Anerkennung am Rad drehen. Der Überraschungseffekt verstärkt die positive Emotion.';
$string['example6_benefit'] = 'Das Spinning Wheel verbindet extrinsische Motivation (Belohnung) mit einem spielerischen Element. Die Ungewissheit, welche Belohnung kommt, macht das System spannender als eine vorhersehbare Belohnung. Wichtig: Die Einträge sollten so gewählt sein, dass alle Ergebnisse positiv sind — es gibt keine „Nieten".';
$string['example6_settings'] = 'Eintragsquelle: Manuell · Konfetti + Jubel-Sound: aktiviert (feierlicher Moment) · Gewinnernachricht: z. B. „Herzlichen Glückwunsch!" · Nach Auswahl entfernen: deaktiviert';

// Beispiel 7.
$string['example7_title'] = 'Zufällige Teambildung';
$string['example7_situation'] = 'Wenn Lernende ihre Gruppen selbst wählen dürfen, entstehen oft dieselben Konstellationen. Einzelne Personen werden ausgeschlossen oder es bilden sich leistungshomogene Gruppen. Die Lehrkraft möchte durchmischte Teams, ohne selbst die Einteilung vornehmen zu müssen.';
$string['example7_implementation'] = 'Alle Kursteilnehmer/innen stehen auf dem Rad. Die Lehrkraft legt fest: „Die ersten drei erdrehten Personen bilden Team A." Sie dreht dreimal hintereinander — durch „Nach Auswahl entfernen" kann niemand doppelt gezogen werden. Dann wird für Team B gedreht, Team C usw. Die Profilbilder auf dem Rad machen den Prozess visuell ansprechend und persönlich.';
$string['example7_benefit'] = 'Die zufällige Gruppenzusammenstellung fördert soziale Kompetenzen: Lernende müssen mit wechselnden Partner/innen zusammenarbeiten und sich auf unterschiedliche Arbeitsstile einstellen. Der transparente Zufallsprozess wird als fair akzeptiert und verhindert soziale Ausgrenzung.';
$string['example7_settings'] = 'Eintragsquelle: Kursteilnehmer/innen · Anzeige: Name + Profilbild · Nach Auswahl entfernen: aktiviert · Rollenfilter: nur Teilnehmer/innen';

// Beispiel 8.
$string['example8_title'] = 'Sprechübungen und Rollenspiele im Sprachunterricht';
$string['example8_situation'] = 'Mündliche Übungen im Sprachunterricht leiden häufig darunter, dass Lernende sich Situationen aussuchen, die sie bereits gut beherrschen, und schwierigere Szenarien vermeiden. Gleichzeitig fehlt ein motivierender Rahmen für Sprechübungen.';
$string['example8_implementation'] = 'Auf dem Rad stehen alltagsnahe Kommunikationssituationen: „Im Restaurant bestellen", „Nach dem Weg fragen", „Ein Hotelzimmer reklamieren", „Beim Arzt Symptome beschreiben", „Ein Vorstellungsgespräch führen", „Smalltalk auf einer Party". Zwei Lernende werden (ggf. über ein zweites Namensrad) ausgewählt und spielen die erdrehte Situation als spontanen Dialog nach. Das Tick-Geräusch während der Drehung baut Spannung auf und markiert den Übergang zur Übungsphase.';
$string['example8_benefit'] = 'Die Zufallsauswahl der Situation zwingt dazu, sich spontan auf neue Kontexte einzulassen — genau das, was in realen Kommunikationssituationen gefordert ist. Der spielerische Rahmen senkt die Sprechangst. Der Drehverlauf dokumentiert, welche Situationen bereits geübt wurden, sodass die Lehrkraft den Überblick behält.';
$string['example8_settings'] = 'Eintragsquelle: Manuell · Nach Auswahl entfernen: optional (deaktiviert = Situationen können mehrfach geübt werden) · Tick-Geräusch: aktiviert';

// Beispiel 9.
$string['example9_title'] = 'Entscheidungshelfer bei Gleichstand';
$string['example9_situation'] = 'Bei Abstimmungen im Klassenrat, bei der Auswahl eines Ausflugsziels oder bei der Entscheidung für ein gemeinsames Projekt kommt es zu einem Gleichstand oder einer endlosen Diskussion ohne Ergebnis. Die Gruppe dreht sich im Kreis.';
$string['example9_implementation'] = 'Die verbleibenden Optionen (z. B. „Zoo" und „Kletterpark" nach einer 50/50-Abstimmung) werden auf das Rad gesetzt. Das Spinning Wheel wird als neutraler Schiedsrichter eingesetzt und trifft die finale Entscheidung. Die Lehrkraft erklärt: „Wir haben ein Unentschieden — das Rad entscheidet." Der Konfetti-Effekt nach dem Ergebnis verwandelt einen potenziell frustrierenden Moment in einen feierlichen Anlass, den alle mittragen können.';
$string['example9_benefit'] = 'Das Spinning Wheel ist kein Ersatz für demokratische Prozesse, sondern eine Ergänzung bei Patt-Situationen. Es beendet unproduktive Diskussionen auf eine Weise, die als neutral akzeptiert wird. Gleichzeitig lernen Schüler/innen, Ergebnisse zu akzeptieren, die nicht ihrer ersten Wahl entsprechen — eine wichtige soziale Kompetenz.';
$string['example9_settings'] = 'Eintragsquelle: Manuell (nur die Optionen im Gleichstand) · Konfetti + Jubel-Sound: aktiviert · Nach Auswahl entfernen: deaktiviert (bei Bedarf erneut drehen) · Max. Drehungen: 1 · Drehdauer: 5000 ms (maximale Spannung)';

// Beispiel 10.
$string['example10_title'] = 'Mobile Einsatzmöglichkeiten im Präsenz- und Hybridunterricht';
$string['example10_situation'] = 'Nicht in jedem Unterrichtsraum steht ein Computer mit Beamer zur Verfügung. Bei Exkursionen, Outdoor-Aktivitäten oder im Hybridunterricht braucht die Lehrkraft eine mobile Lösung, die auch auf dem Smartphone oder Tablet funktioniert.';
$string['example10_implementation'] = 'Das Spinning Wheel Plugin funktioniert vollständig in der Moodle Mobile App — mit Animation, Profilbildern, Sounds und Konfetti. Die Lehrkraft öffnet die Aktivität in der Moodle App auf dem Tablet und zeigt das Rad der Klasse. Alternativ teilt sie den Bildschirm über ein Konferenztool. Lernende können auf ihren eigenen Geräten in der App drehen, wenn die Einstellung „Teilnehmer dürfen drehen" aktiviert ist. Im Hybridunterricht können sowohl Präsenz- als auch Remote-Teilnehmer/innen über die App oder den Browser gleichzeitig das Rad sehen und nutzen. Der Drehverlauf dokumentiert alle Ergebnisse für die Nachbereitung.';
$string['example10_benefit'] = 'Die volle App-Unterstützung macht das Spinning Wheel ortsunabhängig einsetzbar — im Klassenzimmer, auf dem Schulhof, bei Exkursionen oder im Distanzunterricht. Die einheitliche Erfahrung über alle Geräte hinweg (Desktop, Tablet, Smartphone) stellt sicher, dass alle Beteiligten die gleiche Funktionalität haben.';
$string['example10_settings'] = 'Alle Einstellungen je nach Einsatzzweck (siehe Szenarien 1–9) · Tipp: Nach Änderungen Cache leeren und in der App neu einloggen';
