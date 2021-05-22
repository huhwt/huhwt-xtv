# ℍ&ℍwt - HuH Extensions for webtrees - Treeview-Extended
==========================================================

[![Latest Release](https://img.shields.io/github/v/release/huhwt/huhwt-xtv)][1]
[![webtrees major version](https://img.shields.io/badge/webtrees-v2.x-green)][2]
[![Downloads](https://img.shields.io/github/downloads/huhwt/huhwt-xtv/v1.0/total)]()

# Extensions for web trees to check and display the contents in the database.

This is a webtrees 2.x module - It cannot be used with webtrees 1.x.

## Introduction
-------------

The 'Interactive Treeview' in webtrees is a great tool to visualize the existing relationships in a compact way at a glance. 

When using it, however, it is sometimes irritating that you don't really know whether
the tree is now complete and it would be nice to have an overview of which
and how many people are actually in the representation.

This is where the expansion comes in:

* You can specify the number of generations shown from the start
    - '-' N '+' - option in the form's header    - Min: 2, Max: 25 - Default: 4
- (The more generations given, the longer the primary dissolution takes.)

* You can control whether the patri- or matri-linear approach is used for the parent resolution 
    - Patri-linear -> father's side takes precedence  / Matri-linear -> mother's side takes precedence
    - (If there is no hit on the priority line, the other line is called automatically)
- Checkbox 'Paternal side takes precedence' in header   - Default: Paternal (webtrees-Standard)

* In the person boxes, the generation rank in relation to the starting person is displayed 
    - (Decedants < 0, Ancestors > 0)

* You can display the number of people shown and the links that are currently still open
    - (Statistics view )
    - 'Current status' button               - click opens, next click closes 

* The list of names of the persons in current view can be displayed.
    - 'Show name list' button               - click opens, next click closes 
    - Clicking on an entry in the name list marks the associated person box and scrolls it
occasionally in the visible area     
    - The list of names can be moved freely in the viewport 
    - The contents of the name list can be downloaded as a txt file 

* Submenu 'Expand next links'               - automatically opened, click closes / opens 
-   You can specifically expand by 1 level at a time.
    - 'Expand Next Links' button    - '1 Level' option
*   You can specifically expand the view completely .
    - 'Expand Next Links' button    - 'All' option 
    - The extension actions automatically open the statistics view.

* Collapse / Expand folding buttons are displayed at the family boxes 
    - The pending subtree can be completely hidden or shown again 
    - The last connections that have not yet been queried are each forwarded 1 level via
Ajax call queried. Existing partial trees become complete depending on their condition
visible / invisible set. 
    - (Sub-tree extensions that are not yet known are highlighted in red, known and
folded ones are colored green when hovering)

* You can export the state of the view as a PNG.
    - The current view is translated into a PNG. Caution: Depending on the browser, the image
may be incomplete or certain elements are missing (depends on size and width of the
viewport, from a height of 16,384 pixels it becomes critical.) 

* Viewport is scrollable.

* Viewport is automatically opened in full-screen view .

## Dependencies 

* The extension 'Tree-View-Full-Screen' from UksusoFF is required.
* (https://github.com/UksusoFF/webtrees-tree_view_full_screen)

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

## Installation and upgrading
--------------------------

... in the usual way: download the zip file, extract it to the modules_v4 directory, and that's it. You should completely remove the existing version beforehand.

Development
-------------------------

[TODO]

.. it would be nice if you could navigate in the viewport using a mini-map.

Bugs and feature requests
-------------------------
If you experience any bugs or have a feature request for this theme you can [create a new issue][3].

[1]: https://github.com/huhwt/huhwt-xtv/releases/latest
[2]: https://webtrees.net/download
[3]: https://github.com/huhwt/huhwt-xtv/issues?state=open