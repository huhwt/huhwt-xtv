# ℍ&ℍwt - HuH Extensions for webtrees - Treeview-Extended
==========================================================

[![Latest Release](https://img.shields.io/github/v/release/huhwt/huhwt-xtv)][1]
[![webtrees major version](https://img.shields.io/badge/webtrees-v2.x-green)][2]
[![Downloads](https://img.shields.io/github/downloads/huhwt/huhwt-xtv/v1.0/total)]()

# Erweiterungen für Webtrees zur Prüfung und Anzeige von Inhalten in der Datenbank.

This is a webtrees 2.x module - It cannot be used with webtrees 1.x.

## Einführung
-------------

Das Interaktive Sanduhrdiagramm in Webtrees ist ein großartiges Tool, um die vorhandenen 
Beziehungen kompakt auf einen Blick darzustellen.

Bei der Nutzung ist es allerdings manchmal irritierend, dass man nicht so genau weiß, ob
der Baum jetzt komplett ist und es wäre auch angenehm, wenn man einen Überblick hätte, welche
und wieviele Personen nun eigentlich in der Darstellung sind.

Hier setzt nun die Erweiterung an:

* Man kann die Anzahl der vom Start an gezeigten Generationen vorgeben
    * '-' N '+' - Option im Kopf-Formular   - Min: 2, Max: 25 - Default: 4
* - (Je mehr Generationen vorgegeben, desto länger dauert die primäre Auflösung.)

* Man kann steuern, ob bei der Eltern-Auflösung Patri- oder Matri-linear vorgegangen wird
    * Patri-linear -> Vater-Seite hat Vorrang / Matri-linear -> Mutter-Seite hat Vorrang
    * (Gibt es keinen Treffer auf der vorrangigen Linie, wird automatisch die andere Linie aufgerufen)
* - Checkbox 'Väterliche Seite hat Vorrang' im Kopf-Formular   - Default: Patri-linear (webtrees-Standard)

* Bei den Personen-Boxen wird der Generationen-Rang in Bezug auf die Start-Person angezeigt
    * (Nachkommen < 0, Vorfahren > 0)

* Man kann die Anzahl der dargestellten Personen und der aktuell noch offenen Verknüpfungen anzeigen lassen
    * (Statistik-Ansicht)
    * Schaltfläche 'Aktueller Zustand'      - Klick öffnet, nächster Klick schliesst

* Man kann sich eine Namensliste der dargestellten Personen anzeigen lassen.
    * Schaltfläche 'Zeige Namensliste'      - Klick öffnet, nächster Klick schliesst
    * Klick auf einen Eintrag in der Namensliste markiert die zugehörige Personen-Box und scrollt sie 
    * fallweise in den sichtbaren Bereich
    * Die Namensliste ist frei im Viewport verschiebbar
    * Der Inhalt der Namensliste kann als txt-File heruntergeladen werden

* Untermenü 'Nächste Verknüpfungen erweitern' - automatisch geöffnet, Klick schliesst/öffnet
*   Man kann gezielt um jeweils 1 Ebene erweitern.
    * Schaltfläche 'Nächste Verknüpfungen erweitern' - Option '1 Ebene'
*   Man kann gezielt die Ansicht komplett erweitern lassen.
    * Schaltfläche 'Nächste Verknüpfungen erweitern' - Option 'Alle'
* - Die Erweiterungsaktionen öffnen automatisch die Statistik-Ansicht.

* Bei den Familienboxen werden Ein-/Aus-Falten-Schaltflächen eingeblendet
    * Man kann den jeweils anhängigen Teil-Baum komplett aus- bzw. wieder einblenden
    * Die jeweils letzten, noch nicht abgefragten Verbindungen werden jeweils 1 Ebene weiter per
    * Ajax-Call abgefragt. Bereits vorhandene Teil-Bäume werden je nach Zustand komplett 
    * sichtbar/unsichtbar gesetzt.
    * (Noch nicht bekannte Teilbaum-Erweiterungen sind rötlich unterlegt, bekannte und
    * eingeklappte werden beim Hovern grünlich eingefärbt)

* Man kann den Zustand der Ansicht als PNG exportieren lassen.
    * Es wird die aktuelle Ansicht in ein PNG übersetzt. Vorsicht: Je nach Browser kann das Abbild
    * unvollständig sein bzw. es fehlen gewisse Elemente (Das ist abhängig von Größe und Breite des 
    * Viewports, ab ca. 10.000 Pixeln Höhe wird es kritisch.)

* Der Viewport ist scrollfähig.

* Der Viewport wird automatisch in Full-Screen-Ansicht geöffnet.

## Abhängigkeiten

* Die Erweiterung 'Tree-View-Full-Screen' von UksusoFF wird vorausgesetzt.
* (https://github.com/UksusoFF/webtrees-tree_view_full_screen)

* Es wurde unter Firefox entwickelt und unter Chrome getestet. Für andere Browser kann die erwartete Funktion 
* u.U. nicht vorhanden sein.
* Entwicklung und Test erfolgten mit minimal- und webtrees-Thema. Bei anderen Themen kann es zu Einschränkungen
* kommen.
* Es wird ein möglichst großer Bildschirm vorausgesetzt - FullHD oder besser noch 1900x1200 ...

## Danksagung

* Für die Umsetzung des Viewports in PNG wird die html2canvas-Bibliothek genutzt.
*   ( https://html2canvas.hertzen.com/ | Apache License, Version 2.0 )
* Die Bibliothek wird bei der HuHwt-Installation automatisch mit kopiert.

## Installation and upgrading
--------------------------

... auf die übliche Art und Weise: Laden Sie die Zip-Datei herunter, entpacken Sie sie in das modules_v4-Verzeichnis, und das war's. Man sollte die vorhandene Version vorher komplett entfernen.

Development
-------------------------

[TODO]

.. es wäre schön, wenn man im Viewport über eine Mini-Map navigieren könnte.

Bugs and feature requests
-------------------------
If you experience any bugs or have a feature request for this theme you can [create a new issue][3].

[1]: https://github.com/huhwt/huhwt-xtv/releases/latest
[2]: https://webtrees.net/download
[3]: https://github.com/huhwt/huhwt-xtv/issues?state=open