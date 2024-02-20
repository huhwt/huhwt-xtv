# ℍ&ℍwt - HuH Extensions for webtrees - Treeview-Extended
==========================================================

[![Latest Release](https://img.shields.io/github/v/release/huhwt/huhwt-xtv)][1]
[![webtrees major version](https://img.shields.io/badge/webtrees-v2.1-green)][2]
[![Downloads](https://img.shields.io/github/downloads/huhwt/huhwt-xtv/total)]()

# Erweiterungen für Webtrees zur Prüfung und Anzeige von Inhalten in der Datenbank.

Dies ist ein webtrees 2.1 Modul - kann nicht mit webtrees 1.x benutzt werden.

## Einführung
-------------

Das Interaktive Sanduhrdiagramm in Webtrees ist ein großartiges Tool, um die vorhandenen 
Beziehungen kompakt auf einen Blick darzustellen.

Bei der Nutzung ist es allerdings manchmal irritierend, dass man nicht so genau weiß, ob
der Baum jetzt komplett ist und es wäre auch angenehm, wenn man einen Überblick hätte, welche
und wieviele Personen nun eigentlich in der Darstellung sind.

Hier setzt nun die Erweiterung an:

Der Ausgangzustand wird über Formular-Steuerfelder definiert. Ist die Ansicht geöffnet, kann sie weitgehend analysiert und ihr Zustand reversibel verändert werden. Man erhält zu jedem Zeitpunkt Informationen über die Anzahl der gezeigten Personen und kann in der Ansicht zu einer gewünschten Person navigieren. Die ergänzenden Funktionen können über Steuerfelder in der Ansicht ausgelöst werden.

## Beschreibung der Funktionen
------------------------------

Formular-Steuerfelder:

* Man kann die Anzahl der vom Start an gezeigten Generationen vorgeben
    - '-' N '+' - Option im Kopf-Formular   - Min: 2, Max: 25 - Default: 4
- (Je mehr Generationen vorgegeben, desto länger dauert die primäre Auflösung.)

* Man kann steuern, ob bei der Eltern-Auflösung Patri- oder Matri-linear vorgegangen wird
    - Patri-linear -> Vater-Seite hat Vorrang / Matri-linear -> Mutter-Seite hat Vorrang
    - (Gibt es keinen Treffer auf der vorrangigen Linie, wird automatisch die andere Linie aufgerufen)
- Checkbox 'Väterliche Seite hat Vorrang' im Kopf-Formular   - Default: Patri-linear (webtrees-Standard)

* Bei den Personen-Boxen wird der Generationen-Rang in Bezug auf die Start-Person angezeigt
    - (Nachkommen < 0, Vorfahren > 0)

Ansicht-Steuerfelder:

* Kompaktes Layout, Klick wechselt Layout-Modus
    - Im Standard haben die Familienboxen eine feste Breite. Je mehr Generationen in der Ansicht enthalten  sind, desto breiter wird diese und man kann sie nicht mehr in Gänze darstellen.
    - Im kompakten Layout wird die Breite der Familenboxen dynamisch angepasst, so dass alle Generationen ohne horizontales Scrollen sichtbar sind.

* Untermenü 'Nächste Verknüpfungen erweitern' - automatisch geöffnet, Klick schliesst/öffnet
    -   Man kann gezielt um jeweils 1 Ebene erweitern.
        - Schaltfläche 'Nächste Verknüpfungen erweitern' - Option '1 Ebene'
        - Man kann gezielt die Ansicht komplett erweitern lassen.
        - Schaltfläche 'Nächste Verknüpfungen erweitern' - Option 'Alle'
    - Die Erweiterungsaktionen öffnen automatisch die Statistik-Ansicht.

* Man kann die Anzahl der dargestellten Personen und der aktuell noch offenen Verknüpfungen anzeigen lassen
    - Schaltfläche 'Aktueller Zustand'      - Klick öffnet, nächster Klick schliesst
        - Anzahl Namen in der Ansicht
          Anzahl noch offene Verknüpfungen in der Ansicht
          Spannweite der Generationen in der Ansicht
          Dimensionen der Ansicht - Breite/Höhe in Pixeln

* Man kann sich eine Namensliste der dargestellten Personen anzeigen lassen.
    - Schaltfläche 'Zeige Namensliste'      - Klick öffnet, nächster Klick schliesst
        - Klick auf einen Eintrag in der Namensliste markiert die zugehörige Personen-Box und scrollt sie fallweise in den sichtbaren Bereich
        - Die Namensliste ist frei im Viewport verschiebbar
        - Der Inhalt der Namensliste kann als txt-File heruntergeladen werden

* Man kann den Zustand der Ansicht als PNG exportieren lassen.
    - Es wird die aktuelle Ansicht in ein PNG übersetzt. Vorsicht: Je nach Browser kann das Abbild
unvollständig sein bzw. es fehlen gewisse Elemente (Das ist abhängig von Größe und Breite des 
Viewports, ab 16.384 Pixeln Höhe wird es kritisch.)

* Optionales Feature: CCE-Export.
    - Wenn das Erweiterungs-Modul HuHwt-CCE ab der Version 

Bei den Familienboxen werden Ein-/Aus-Falten-Schaltflächen eingeblendet.

* Man kann den jeweils anhängigen Teil-Baum komplett aus- bzw. wieder einblenden
    - Die jeweils letzten, noch nicht abgefragten Verbindungen werden jeweils 1 Ebene weiter per
Ajax-Call abgefragt. Bereits vorhandene Teil-Bäume werden je nach Zustand komplett 
sichtbar/unsichtbar gesetzt.
    - (Noch nicht bekannte Teilbaum-Erweiterungen sind rötlich unterlegt, bekannte und
eingeklappte werden beim Hovern grünlich eingefärbt)

Die Ansicht ist scrollfähig.

Die Ansicht kennt 3 Zustände:

1. Normalansicht
    - Der Webtrees-Kopf mit allen Menüs und Auswahloptionen sowie die Formular-Steuerfelder des Moduls sind sichtbar, die Ansicht selbst ist nur wenige 100px hoch.
2. Expandierter Zustand
    - Die Ansicht überdeckt den ganzen Webtrees-Bildschirm. Der Browserkopf mit Tabs, Adressleiste und Lesezeichenleiste ist noch sichtbar.
3. Fullscreen-Modus
    - Die Ansicht überdeckt den ganzen Bildschirm inklusive Browserkopf.

* Die Ansicht wird im expandierten Zustand geöffnet.

Der expandierte Zustand wurde aus einer anderen Webtrees-Erweiterung übernommen. Der Eigner dieser Erweiterung hat dieses Modul wegen der Integration des Fullscreen-Modus ausgesetzt. Allerdings ist der expandierte Zustand aussagefähiger als die Normalansicht, es hat sich auch gezeigt, dass die Kombination von expandierter Ansicht und Fullscreen-Option das Problem abgeschnittener PNG-Inhalte vermeiden hilft. Zwischen den Zuständen kann über Schaltflächen in der Ansicht gewechselt werden.

Die Ansicht ist technisch als Konstrukt von ineinandergeschachtelten Tabellen-Elementen realisiert. Dieses Verfahren ist robust und schnell, hat aber einen Nachteil: Es gibt keine Zoom-Option. 

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
*   ( https://html2canvas.hertzen.com/ | Apache License, Version 2.0 )
* Die Bibliothek wird bei der HuHwt-Installation automatisch mit kopiert.

* Für Test, Anregung und Kritik besonderen Dank an Hermann Harthentaler. -> https://github.com/hartenthaler

* Übersetzung in Niederländische - Dank an TheDutchJewel

## Installation and upgrading
--------------------------

... auf die übliche Art und Weise: Laden Sie die Zip-Datei herunter, entpacken Sie sie in das modules_v4-Verzeichnis, und das war's. Man sollte die vorhandene Version vorher komplett entfernen.

Development
-------------------------

[TODO]

.. es wäre schön, wenn man in der Ansicht über eine Mini-Map navigieren könnte.

-- für den PNG-Export Schriftgröße hochsetzen.

Bugs and feature requests
-------------------------
If you experience any bugs or have a feature request for this theme you can [create a new issue][3].

[1]: https://github.com/huhwt/huhwt-xtv/releases/latest
[2]: https://webtrees.net/download
[3]: https://github.com/huhwt/huhwt-xtv/issues?state=open