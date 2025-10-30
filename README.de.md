# ℍ&ℍwt - HuH Extensions for webtrees - Treeview-Extended
==========================================================

[![Latest Release](https://img.shields.io/github/v/release/huhwt/huhwt-xtv)][1]
[![webtrees major version](https://img.shields.io/badge/webtrees-v2.2-green)][2]
[![Downloads](https://img.shields.io/github/downloads/huhwt/huhwt-xtv/total)]()

# Erweiterungen für Webtrees zur Prüfung und Anzeige von Inhalten in der Datenbank.

Dies ist ein webtrees 2.2 Modul - kann nicht mit webtrees 2.1 benutzt werden.

Für Systeme mit webtrees 2.1 bitte das letzte Release aus huhwt-xtv Branch 2.1 verwenden.

## Einführung
-------------

Das Interaktive Sanduhrdiagramm in Webtrees ist ein großartiges Tool, um Beziehungen rund um eine Person auf einen Blick darzustellen.

Es kann sowohl aus den Reitern der Personen-Ansicht als auch über die allgemeine Genealogie-Funktion 'Diagramme' aufgerufen werden.

Bei der Nutzung gibt es allerdings schnell Irritationen. Die Startperson ist bei größeren Übersichten nur noch schwer zu finden. Man weiß nicht so genau, ob und wann der Baum jetzt komplett ist. Es wäre auch angenehm, wenn man einen Überblick hätte, welche und wieviele Personen nun eigentlich in der Darstellung sind. Der Bildschirm zeigt auch immer nur einen Teil des Gesamtbaums und man weiß nicht so genau, wo im Baum der aktuelle Ausschnitt eigentlich zu verorten sein mag. Hat eine Person mehrere Beziehungen, wird zwischen diesen nicht differenziert. Treten Personen mehrfach an verschiedenen Stellen im Baum auf - Implex -, kann man diese Situation nicht direkt erkennen. Es kann es auch interessieren, welche Zweige des Baumes aktuell noch nicht verschieden sind. Will man Beziehungen analysieren, können Verzweigungen in andere Bereiche stören, weil sie einfach viel Platz einnehmen. Schließlich wäre es auch schön, wenn man den dargestellten Zustand auch anderweitig analysieren könnte.

### Merkmale der Erweiterung

Hier setzt nun die Erweiterung an:

Der Ausgangzustand wird über Formular-Steuerfelder definiert. Ist die Ansicht geöffnet, kann sie weitgehend analysiert und ihr Zustand reversibel verändert werden. Man erhält zu jedem Zeitpunkt Informationen über die Anzahl der gezeigten Personen und kann in der Ansicht zu einer gewünschten Person navigieren. Die ergänzenden Funktionen können über Steuerfelder in der Ansicht ausgelöst werden.

Die Startperson wird eindeutig markiert.

Man kann ganze Teilbereiche der Ansicht oder auch nur einzelne Familienzweige gezielt nachladen und auch wieder ausblenden.

Man kann sich Implex-Zustände hervorheben lassen.

Man kann im Implex-Fall das Nachladen unterdrücken.

Man kann Mehrfach-Beziehungen getrennt auflösen und darstellen.

Der gesamte Baum wird abstrahiert als Pagemap angezeigt, man kann über die Pagemap direkt zu jedem Bereich des Baums navigieren und die Position des aktuellen Bildschirm-Ausschnitts ist in der Pagemap unmittelbar erkannbar. Die Position der Startperson ist in der Pagemap eindeutig hervorgehoben.

Man kann eine Liste der im aktuellen Zustand angezeigten Personen nutzen, um über die Listeneinträge direkt an die Positionen in der Ansicht zu springen. Dabei werden mehrfach auftretende Personen (-Implex-) speziell hervorgehoben.

Als Option kann man verstorbene Personen eindeutig markieren und die Ansicht auf die (nach Datenlage) noch lebenden Personen beschränken.

Man kann die Kennungen der angezeigten Personen (und Familien) in den Sammelbehälter überführen.

## Beschreibung der Funktionen
------------------------------

Das Modul kann eingebettet als Reiter in der Personenseite oder unabhängig als eigenes Genealogie-Diagramm verwendet werden. Die nachstehend beschriebenen Steuerfelder sind nur bei der Verwendung als Diagramm zugänglich.

### Formular-Steuerfelder:

* Man kann die Anzahl der vom Start an gezeigten Generationen vorgeben
    - '-' N '+' - Option im Kopf-Formular   - Min: 1, Max: 25 - Default: 4
- (Je mehr Generationen vorgegeben, desto länger dauert die primäre Auflösung.)

* Man kann steuern, ob bei der Eltern-Auflösung Patri- oder Matri-linear vorgegangen wird
    - Patri-linear -> Vater-Seite hat Vorrang / Matri-linear -> Mutter-Seite hat Vorrang
    - (Gibt es keinen Treffer auf der vorrangigen Linie, wird automatisch die andere Linie aufgerufen)
- Checkbox 'Mütterliche Seite hat Vorrang' im Kopf-Formular   - Default: Patri-linear (webtrees-Standard)

* Option 'Implex anzeigen'
    - Wenn aktiv, werden Personen, für welche Implex erkannt wurde, mit einem Rahmen hervorgehoben (Standard: aus).

* Option 'Implex unterdrücken'
    - Wenn aktiv, werden Teilbäume ausgehend von Personen/Familien, für welche Implex erkant wurde, nicht weiter aufgelöst bzw. keine Erweiterungsboxen eingebaut (Standard: aus).

* Option 'Verstorbene markieren'
    - Wenn aktiv, werden als 'Verstorben' erkannte Personen entsprechend markiert. Darüber hinaus können diese Personen in der Ansicht ausgeblendet werden, so dass man nur noch die lebenden Personen angezeigt bekommt.
    - Anmerkung: Die Ermittlung, ob eine Person als verstorben zu betrachten ist, kann abhängig von der Datenlage aufwendig sein - u.U. werden weitreichende Analysen des Gedcom ausgeführt. Das kann die Antwortzeit merkbar verlängern (Standard: aus).

* Option 'Getrennt anzeigen'
    - Wenn aktiv, werden Mehrfach-Beziehungen - 1 Person mit mehreren Partnern - als jeweils eigene Familienboxen angezeigt, welche optisch mit Strichlinien ober- und unterhalb hervorgehoben sind (Standard: aus).
    - Ist die Start-Person selbst betroffen, wird die ganze Ansicht in 2 oder mehr Bereiche aufgeteilt. Dabei wird versucht, die allgemeine Ausrichtung der Bereiche synchron zu halten. Start-Person wie auch Generationen-Spalten in den Bereichen sollten auf gleichen Achsen liegen (Standard: aus).

### Steuerfelder in der Ansicht-Toolbox:

* Kompaktes Layout, Klick wechselt Layout-Modus
    - Im Standard haben die Familienboxen eine feste Breite. Je mehr Generationen in der Ansicht enthalten  sind, desto breiter wird diese und man kann sie nicht mehr in Gänze darstellen.
    - Im kompakten Layout wird die Breite der Familenboxen dynamisch angepasst, so dass alle Generationen ohne horizontales Scrollen sichtbar sind.

* Untermenü 'Nächste Verknüpfungen erweitern' - automatisch geöffnet, Klick schliesst/öffnet
    - Man kann die Ansicht global um jeweils 1 Ebene erweitern, wirkt sowohl auf Vorfahren- wie auch Nachkommen-Seite.
        - Schaltfläche 'Nächste Verknüpfungen erweitern' - Option '1 Ebene'
    - Man kann die Ansicht komplett erweitern lassen, wirkt sowohl auf Vorfahren- wie auch Nachkommen-Seite.
        - Schaltfläche 'Nächste Verknüpfungen erweitern' - Option 'Alle'
    - Die Erweiterungsaktionen öffnen automatisch die Statistik-Ansicht.

* Man kann die Anzahl der dargestellten Personen und der aktuell noch offenen Verknüpfungen anzeigen lassen
    - Schaltfläche 'Aktueller Zustand'      - Klick öffnet, nächster Klick schliesst
        - Anzahl Namen in der Ansicht
        - Anzahl noch offene Verknüpfungen in der Ansicht
        - Spannweite der Generationen in der Ansicht
        - Dimensionen der Ansicht - Breite/Höhe in Pixeln
        - Übersicht Anzahl dargestelllte Personen pro Generation
          - (Abhängig von Aufruf und Zustand der Ansicht: als Chart -immer- | als Tab -nur wenn expandiert-)
          - Klick auf einen Generationen-Eintrag hebt die Personen-Boxen hervor, auch öffnet sich eine Namensliste der dargestellten Personen der Generation (nach Reihenfolge in Ansicht)
            - Klick auf einen Eintrag in der Namensliste markiert die zugehörige Personen-Box und scrollt sie fallweise in den sichtbaren Bereich
            - Die Namensliste ist frei im Viewport verschiebbar

* Man kann sich eine Namensliste der dargestellten Personen anzeigen lassen (sortiert Nachname, Vorname).
    - Schaltfläche 'Zeige Namensliste'      - Klick öffnet, nächster Klick schliesst
        - Mehrfach auftretende Namen werden hervorgehoben
        - Klick auf einen Eintrag in der Namensliste markiert die zugehörige Personen-Box und scrollt sie fallweise in den sichtbaren Bereich
        - Die Namensliste ist frei im Viewport verschiebbar
        - Der Inhalt der Namensliste kann als txt-File heruntergeladen werden

* Die Pagemap kann gezielt ein- und ausgeblendet werden.

* Ansicht expandieren / Ansicht im Fullscreen-Modus (siehe unten).

* Man kann den Zustand der Ansicht als PNG exportieren lassen.
    - Es wird die aktuelle Ansicht in ein PNG übersetzt. Vorsicht: Je nach Browser kann das Abbild
unvollständig sein bzw. es fehlen gewisse Elemente (Das ist abhängig von Größe und Breite des 
Viewports).

* Optionales Feature: Verstorbene aus-/einblenden.
    - Diese Steuerfläche erscheint nur, wenn die Option 'Verstorbene anzeigen' im Formularkopf aktiviert ist. Anzeige-Elemente zu verstorbenen Personen werden komplett aus der Ansicht herausgenommen, es verbleiben nur noch die Verbindungslinien, so dass die Struktur an sich noch einigermaßen erkennbar bleibt.

* Optionales Feature: CCE-Export.
    - Wenn das Erweiterungs-Modul HuHwt-CCE ab der Version 2.20 installiert ist, kann man die aktuell in XTV angezeigten Personen und Familien in den Sammelbehälter übernehmen.

### Steuerfelder in der Ansicht selbst:

Bei den Personen-Boxen wird der Generationen-Rang in Bezug auf die Start-Person angezeigt
  - (Nachkommen < 0, Vorfahren > 0)

* Bei den Familienboxen werden Ein-/Aus-Falten-Schaltflächen für die jeweils nächste Ebene eingeblendet, das entspricht den jeweils anhängenden kompletten Teilbäumen. Über diese Schaltflächen kann die Anzeige gezielt erweitert werden, die Aktion lädt nur die jeweils relevanten Vorfahren bzw. Nachkommen. Noch nicht geöffnete Anschlusspunkte sind rot hinterlegt. Offene Anschlusspunkte sind transparent. Faltet man einen bereits geöffneten Teilbaum wieder ein, ist der Anschlusspunkt gelb hinterlegt.

* Innerhalb einer Ebene/ eines Teilbaums gibt es darüberhinaus die Option, jeweils einen Zweig inaktiv zu setzen. Im Ausgangzustand sind alle Zweige sichtbar, die Schaltflächen sind transparent. Ein Klick darauf macht den Zweig unsichtbar, die Geometrie der Darstellung bleibt erhalten, die Schaltfläche wird gelb hinterlegt. Ein Strg-Klick (Ctrl-Click) blendet den Zweig komplett aus, die Geometrie der Darstellung ordnet sich entsprechend um und die Schaltfläche wird rot hinterlegt.

Die Ansicht ist scrollfähig.

Die Pagemap:

Die Pagemap zeigt jederzeit den gesamten Baum. Der sichtbare Ausschnitt ist klar hervorgehoben. Beim Klick auf eine nicht im aktuellen Ausschnitt liegenden Bereich des Baums wechselt die Ansicht unmittelbar zu diesem Bereich. Man kann die Markierung "sichtbarer Ausschnitt" in der Pagemap verschieben, der Baumausschnitt in der Ansicht wandert entsprechend mit. Die Position(en) der primären Person sowie der fallweise über die Namensliste ausgewählte Person(en) werden deutlich farbig markiert und hervorgehoben.

### Zustände der Ansicht

Die Ansicht kennt 3 Zustände:

1. Normalansicht
    - Der Webtrees-Kopf mit allen Menüs und Auswahloptionen sowie fallweise die Formular-Steuerfelder des Moduls sind sichtbar, die Ansicht selbst ist nur wenige 100px hoch.
2. Expandierter Zustand
    - Die Ansicht überdeckt den ganzen Webtrees-Bildschirm. Der Browserkopf mit Tabs, Adressleiste und Lesezeichenleiste ist noch sichtbar.
3. Fullscreen-Modus
    - Die Ansicht überdeckt den ganzen Bildschirm inklusive Browserkopf.

* Die Ansicht wird beim Aufruf als Personenseite-Tab als Normalansicht und beim Aufruf aus den Genealogie-Diagrammen im expandierten Zustand geöffnet.
    - Beim Aufruf als Personenseite-Tab sind die Formular-Steuerfelder nicht zugänglich, alle Optionen auf Default; es werden 4 Generationen vorab aufgelöst.
    - Die Startansicht - Normal oder expandiert - kann über die Admin-Funktion 'Einstellungen verwalten' beeinflusst werden. XTV ist sowohl bei den Genealogie-Diagrammen als auch bei den Personenseite-Reitern gelistet, die Einstellungen beider Verwendungen werden gemeinsam verwaltet.

Der expandierte Zustand wurde aus einer anderen Webtrees-Erweiterung übernommen. Der Eigner dieser Erweiterung hat dieses Modul wegen der Integration des Fullscreen-Modus ausgesetzt. Allerdings ist der expandierte Zustand aussagefähiger als die Normalansicht, es hat sich auch gezeigt, dass die Kombination von expandierter Ansicht und Fullscreen-Option das Problem abgeschnittener PNG-Inhalte vermeiden hilft. Zwischen den Zuständen kann über Schaltflächen in der Ansicht gewechselt werden.

Die Ansicht ist technisch als Konstrukt von ineinandergeschachtelten Tabellen-Elementen realisiert. Das Verfahren ist robust und schnell, hat aber auch Nachteile: Es gibt keine Zoom-Option und die Technik erlaubt nur die vorliegende Darstellungs-Technik - Start-Person in der Mitte, Expansion horizontal links Nachfahren, rechts Vorfahren - Alternativen wie vertikale Expansion sind nicht darstellbar.

## Abhängigkeiten

* keine

## Caveat

* Es wurde unter Firefox entwickelt und unter Chrome getestet. Für andere Browser kann die erwartete Funktion 
u.U. nicht vorhanden sein.
* Entwicklung und Test erfolgten mit minimal- und webtrees-Thema. Bei anderen Themen kann es zu Einschränkungen
kommen.
* Es wird ein möglichst großer Bildschirm vorausgesetzt - FullHD oder besser noch 1900x1200 ...
* Handy und Tablet werden bisher nicht berücksichtigt, es handelt sich um ein Desktop-Modul mit Mouse-Bedienung. Andere Umgebungen unterstützen bestimmte Feature nur eingeschränkt, u.U. auch gar nicht. Die sich daraus ergebende Komplexität liegt noch nicht im Fokus bzw. außerhalb meiner Reichweite, Unterstützung hier wäre hoch willkommen!

## Danksagung

* Für die Umsetzung des Viewports in PNG wird die html2canvas-Bibliothek genutzt.
 ( https://html2canvas.hertzen.com/ | MIT License )
 Die Bibliothek wird bei der HuHwt-Installation automatisch mit kopiert.
* Die Pagemap ist abgeleitet von
   ( https://github.com/lrsjng/pagemap | MIT License)
   als fork ( https://github.com/PiSaucer/pagemap | MIT License )
* Für Test, Anregung und Kritik besonderen Dank an Hermann Harthentaler. -> https://github.com/hartenthaler

* Übersetzung ins Niederländische - Dank an TheDutchJewel
* Übersetzung ins Spanische + Catalanische - Dank an BernatBanyuls
* Übersetzung ins Englische - Deepl.com

## Installation and upgrading
--------------------------

... auf die übliche Art und Weise: Laden Sie die Zip-Datei herunter, entpacken Sie sie in das modules_v4-Verzeichnis, und das war's. Man sollte die vorhandene Version vorher komplett entfernen.

Hinweis: Man sollte das Zip file aus dem jeweils als "latest" markierte Release herunterladen. Der aus der Github-Ansicht herunterladbare Code entspricht der Entwicklungs-Version und kann instabil sein.

Development
-------------------------

[TODO]

-- für den PNG-Export Schriftgröße hochsetzen.

Bugs and feature requests
-------------------------
If you experience any bugs or have a feature request for this theme you can [create a new issue][3].

[1]: https://github.com/huhwt/huhwt-xtv/releases/latest
[2]: https://webtrees.net/download
[3]: https://github.com/huhwt/huhwt-xtv/issues?state=open