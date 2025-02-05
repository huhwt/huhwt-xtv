# ℍ&ℍwt - HuH Extensions for webtrees - Treeview-Extended
==========================================================

[![Latest Release](https://img.shields.io/github/v/release/huhwt/huhwt-xtv)][1]
[![webtrees major version](https://img.shields.io/badge/webtrees-v2.2-green)][2]
[![Downloads](https://img.shields.io/github/downloads/huhwt/huhwt-xtv/total)]()

# Extensions for web trees to check and display the contents in the database.

This is a webtrees 2.2 module - It cannot be used with webtrees 2.1.

For webtrees 2.1 use the the latest release of the huhwt-xtv Branch 2.1.

## Introduction
-------------

The 'Interactive Treev' diagram in Webtrees is a great tool for displaying relationships around a person at a glance.

It can be accessed from the 'Tabs' of the 'Individual page' as well as via the general 'Genealogy' sub-hierarchy 'Charts'.

When using it, however, it is sometimes irritating that you don't know exactly whether the tree is now complete and it would also be nice to have an overview of which and how many people are actually in the diagram. The screen also only ever shows part of the overall tree and you don't know exactly where in the tree the current section is actually located. If a person has several relationships, no differentiation is made between them. If persons appear several times in different places in the tree - implex - this situation cannot be recognized directly.

This is where the expansion comes in:

The initial state is defined via form control fields. Once the view is open, it can be analyzed to a large extent and its status can be reversibly changed. Information about the number of people shown is available at any time and you can navigate to a desired person in the view. The additional functions can be triggered via control fields in the view.

You can reload entire sections of the view or just individual family branches and hide them again.

Implex states can be highlighted.

You can suppress reloading in the implex case.

Multiple relationships can be resolved and displayed separately.

The entire tree is also displayed as an abstracted page map. You can use the page map to navigate directly to any area of the tree and the position of the current screen section is immediately visible in the page map.

## Description of the functions
------------------------------

The module can be used embedded as a tab in the 'Individual page' or independently as an own 'Genealogy' chart. The form control panels described below are only accessible when used as a chart.

Form controls:

* You can specify the number of generations shown from the start
    - '-' N '+' - option in the header form - Min: 1, Max: 25 - Default: 4
- (The more generations specified, the longer the primary resolution takes).

* You can control whether the parent resolution is patri-linear or matri-linear
    - Patri-linear -> Father side has priority / Matri-linear -> Mother side has priority
    - (If there is no match on the priority line, the other line is automatically called up)
- Checkbox 'Maternal side has priority' in the header form - Default: Patri-linear (webtrees standard)

* Option 'Show Implex'
    - If active, persons for whom implex was recognized are highlighted with a frame (default: off).

* Option 'Suppress Implex'
    - If active, subtrees based on persons/families for which Implex has been recognized are not expanded further or no extension boxes are added (default: off).

* Option 'Show separately'
    - If active, multiple relationships - 1 person with several partners - are displayed as separate family boxes, which are visually highlighted with dashed lines above and below (default: off).
    - If the start person is affected, the entire view is divided into 2 or more areas. An attempt is made to keep the general alignment of the areas synchronized. The start person and generation columns in the areas should be on the same axes (default: off).

View controls:

* Compact layout, click toggles layout mode
     - By default, the family boxes have a fixed width. The more generations are contained in the view, the broader it becomes and it can no longer be displayed in its entirety.
     - In the compact layout, the width of the family boxes is dynamically adjusted so that all generations are visible without horizontal scrolling.

* Submenu 'Expand Next Links' - automatically opened, click closes/opens
     - You can specifically expand by 1 level.
         - button 'Expand next links' - option '1 level'
         - You can specifically expand the view completely.
         - button 'Expand next links' - option 'All'
    - The extension actions automatically open the statistics view.

* You can display the number of people displayed and the links that are currently still open
     - 'Current state' button - click opens, next click closes
         - Number of names in the view
           Number of links still open in the view
           Span of generations in the view
           View dimensions - width/height in pixels

* You can display a list of the names of the people shown.
     - 'Show list of names' button - click opens, next click closes
         - Clicking on an entry in the list of names marks the associated person box and scrolls it into the visible area as the case may be
         - The list of names can be moved freely in the viewport
         - The content of the list of names can be downloaded as a txt file

* The page map can be shown or hidden as desired.

* Expand view / View in fullscreen mode (see below).

* You can have the state of the view exported as PNG.
     - It will translate the current view into a PNG. Caution: Depending on the browser, the image
be incomplete or certain elements are missing (this depends on the size and width of the
Viewports, from a height of 16,384 pixels it becomes critical.)

* Optional feature: CCE export.
    - If the extension module HuHwt-CCE from version 2.20 is installed, the persons and families currently displayed in XTV can be transferred to the collection container.

Control fields in the view itself:

In the person boxes, the generation rank is displayed in relation to the start person
  - (descendants < 0, ancestors > 0)

* In the family boxes, fold-in/fold-out buttons are displayed for the next level, which corresponds to the complete sub-trees attached in each case. These buttons can be used to selectively expand the display; the action only loads the relevant ancestors or descendants. Connection points that are not yet open are highlighted in red. Open connection points are transparent. If an already opened subtree is folded again, the connection point is highlighted in yellow.

* Within a level/sub-tree, there is also the option to set one branch inactive at a time. In the initial state, all branches are visible, the buttons are transparent. Clicking on it makes the branch invisible, the geometry of the display is retained and the button is highlighted in yellow. A Ctrl-click hides the branch completely, the geometry of the display is rearranged accordingly and the button is highlighted in red.

The view is scrollable.

The page map:

The page map shows the entire tree at all times. The visible section is clearly highlighted. If you click on an area of the tree that is not in the current section, the view immediately switches to this area. You can move the “visible section” marker in the page map; the tree section in the view moves accordingly. The position(s) of the primary person and the person(s) selected via the name list are clearly marked in color and highlighted.

The view knows 3 states:

1. Normal view
    - The Webtrees header with all menus and selection options as well as the form control fields of the module are visible, the view itself is only a few 100px high.
2. Expanded state
     - The view covers the whole Webtrees screen. The browser header with tabs, address bar and bookmarks bar is still visible.
3. Fullscreen mode
     - The view covers the entire screen including the browser header.

* The view opens as a normal view when called up as a 'Individual page' tab and in an expanded state when called up from the 'Genealogy' diagrams.
    - When called up as a person page tab, the form control fields are not accessible; 4 generations are resolved in advance.
    - The start view - normal or expanded - can be influenced using the admin function 'Manage settings'. XTV is listed in both the 'Genealogy' diagrams and the 'Individual page' tabs; the settings for both uses are managed together.

The expanded state was taken from another Webtrees extension. The owner of this extension has suspended this module due to the integration of fullscreen mode. However, the expanded state is more meaningful than the normal view, it has also been shown that the combination of the expanded view and the full-screen option helps to avoid the problem of truncated PNG content. You can switch between the states using buttons in the view.

The view is technically implemented as a construct of nested table elements. This method is robust and fast, but has one disadvantage: there is no zoom option.

## Dependencies 

* None

## Caveat

* It was developed on Firefox and tested on Chrome. For other browsers the expected functions
may not be available.
* Development and testing took place with the 'minimal' and 'webtrees' theme. There may be restrictions on 
other theme.
* The largest possible screen is required - FullHD or even better 1900x1200  ...
* Mobile phones and tablets have not been taken into account so far, it is a desktop module with mouse operation. Other environments support certain features only to a limited extent, possibly not at all. The resulting complexity is not yet in focus or out of my reach, support here would be very welcome!

## Thanks

* The html2canvas library is used to convert the viewport to PNG.
 ( https://html2canvas.hertzen.com/ | MIT License )
 The library is automatically copied during the HuHwt installation.
* The pagemap is derived from ( https://github.com/lrsjng/pagemap | MIT License )
as fork ( https://github.com/PiSaucer/pagemap | MIT License )
* Special thanks to Hermann Harthentaler for test, suggestion and criticism. -> https://github.com/hartenthaler

* Translation into Dutch - thanks to TheDutchJewel. 
* Translation into Catalan and Spanish - many thanks to BernatBanyuls

## Installation and upgrading
--------------------------

... in the usual way: download the zip file, extract it to the modules_v4 directory, and that's it. You should completely remove the existing version beforehand.

Development
-------------------------

[TODO]

.. Increase font size for PNG export.

Bugs and feature requests
-------------------------
If you experience any bugs or have a feature request for this theme you can [create a new issue][3].

[1]: https://github.com/huhwt/huhwt-xtv/releases/latest
[2]: https://webtrees.net/download
[3]: https://github.com/huhwt/huhwt-xtv/issues?state=open