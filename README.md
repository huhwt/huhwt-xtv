# ℍ&ℍwt - HuH Extensions for webtrees - Treeview-Extended
==========================================================

[![Latest Release](https://img.shields.io/github/v/release/huhwt/huhwt-xtv)][1]
[![webtrees major version](https://img.shields.io/badge/webtrees-v2.1-green)][2]
[![Downloads](https://img.shields.io/github/downloads/huhwt/huhwt-xtv/total)]()

# Extensions for web trees to check and display the contents in the database.

This is a webtrees 2.1 module - It cannot be used with webtrees 1.x.

## Introduction
-------------

The 'Interactive Treeview' in webtrees is a great tool to visualize the existing relationships in a compact way at a glance. 

When using it, however, it is sometimes irritating that you don't really know whether
the tree is now complete and it would be nice to have an overview of which
and how many people are actually in the representation.

This is where the expansion comes in:

The initial state is defined via form control fields. If the view is open, it can be extensively analyzed and its status can be changed reversibly. You get information about the number of people shown at any time and you can navigate to a desired person in the view. The additional functions can be triggered via control fields in the view.

## Description of the functions
------------------------------

Form controls:

* You can specify the number of generations shown from the start
    - '-' N '+' - option in the form's header    - Min: 2, Max: 25 - Default: 4
- (The more generations given, the longer the primary dissolution takes.)

* You can control whether the patri- or matri-linear approach is used for the parent resolution 
    - Patri-linear -> father's side takes precedence  / Matri-linear -> mother's side takes precedence
    - (If there is no hit on the priority line, the other line is called automatically)
- Checkbox 'Paternal side takes precedence' in header   - Default: Paternal (webtrees-Standard)

* In the person boxes, the generation rank in relation to the starting person is displayed 
    - (Decedants < 0, Ancestors > 0)

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

* You can have the state of the view exported as PNG.
     - It will translate the current view into a PNG. Caution: Depending on the browser, the image
be incomplete or certain elements are missing (this depends on the size and width of the
Viewports, from a height of 16,384 pixels it becomes critical.)

On/off fold buttons are displayed for the family boxes.

* You can completely hide or show the respective sub-tree
     - The last connections that have not yet been queried are each 1 level further by
Ajax call queried. Existing partial trees become complete depending on their status
set visible/invisible.
     - (Not yet known subtree extensions are highlighted in red, known and
collapsed ones are colored greenish when hovering)

The view is scrollable.

The view knows 3 states:

1. Normal view
     - The webtrees header with all menus and selection options as well as the form control fields of the module are visible, the view itself is only a few 100px high.
2. Expanded state
     - The view covers the whole Webtrees screen. The browser header with tabs, address bar and bookmarks bar is still visible.
3. Fullscreen mode
     - The view covers the entire screen including the browser header.

* The view opens in the expanded state.

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

* The html2canvas library is used to implement exporting the viewport to PNG .
*   ( https://html2canvas.hertzen.com/ | Apache License, Version 2.0 )
* The library is automatically copied with the HuHwt installation.

* Special thanks to Hermann Harthentaler for test, suggestion and criticism. -> https://github.com/hartenthaler

* Translation into Dutch - thanks to TheDutchJewel. 

## Installation and upgrading
--------------------------

... in the usual way: download the zip file, extract it to the modules_v4 directory, and that's it. You should completely remove the existing version beforehand.

Development
-------------------------

[TODO]

.. it would be nice if you could navigate in the viewport using a mini-map.
.. Increase font size for PNG export.

Bugs and feature requests
-------------------------
If you experience any bugs or have a feature request for this theme you can [create a new issue][3].

[1]: https://github.com/huhwt/huhwt-xtv/releases/latest
[2]: https://webtrees.net/download
[3]: https://github.com/huhwt/huhwt-xtv/issues?state=open