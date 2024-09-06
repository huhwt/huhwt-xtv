/**
 * HuH Extensions for webtrees - Treeview-Extended
 * Interactive Treeview with add-ons
 * Copyright (C) 2020-2024 EW.Heinrich
 * 
 * ---------------------------------------------
 * HuHwt EW.H - MOD ...
 * TreeViewHandler 
 *      added Statistics
 *      added Namelist
 *      dropped AutoExpand on dragging
 *      added AutoExpand on button-action
 *          -   +1 Level
 *          -   All Links
 *      added SelectiveExpand
 *          -   not allready expanded links can be individually clicked
 *      adopted webtrees-treeview_full_screen by UksusoFF as expanded view
 *      export to PNG
 *          (done by html2canvas  https://html2canvas.hertzen.com/)
 *      added CCEadapter - transfer XREFs to ClippingsCartEnhanced-Module
 *      collapse/expand partial trees on any level
 * makeNList
 *      list of all names in viewport, ordered by surnames
 * showgens...
 *      adding Generations-Spinner functionality to Chart-Menu
 * isInViewport
 *      test if element is currently in viewport
 * dragElement
 *      adding Drag-and-Drop to Namelist
 */

function TreeViewHandlerXT (tv_kenn, doFullScreen, MinTitle, MaxTitle) {
    var tv = this; // Store "this" for usage within jQuery functions where "this" is not this ;-)
    this.tv_kenn = tv_kenn;
    this.doFullScreen = doFullScreen;
  
    this.treeview       = $('#' + tv_kenn + '_in'); //[0];
    this.treeHome       = document.getElementById(tv_kenn + '_tools');
    this.loadingImage   = $('#' + tv_kenn + '_loading');
    this.toolbox        = $('#' + tv_kenn + '_tools');
    this.next           = $('#' + tv_kenn + '_shownext');
    this.stats          = $('#' + tv_kenn + '_showstats');
    this.buttons        = $('.tv_button:first', this.toolbox);
    this.names          = $('#' + tv_kenn + '_namelist');
    this.namesul        = $('#' + tv_kenn + '_namelistul');
    this.zoom           = 100;                              // in percent
    this.boxWidth       = 180;                              // default family box width
    this.boxExpandedWidth = 250;                            // default expanded family box width
    this.cookieDays     = 3;                                // lifetime of preferences memory, in days
    let elemTVout       = document.getElementById(tv_kenn + '_out');
    this.ajaxDetails    = elemTVout.dataset.urlDetails + '&instance=' + encodeURIComponent(tv_kenn);
    this.ajaxPersons    = elemTVout.dataset.urlIndividuals + '&instance=' + encodeURIComponent(tv_kenn);
    this.ajaxCCE        = elemTVout.dataset.urlCceadapter + '&instance=' + encodeURIComponent(tv_kenn);
  
    this.pIDsel         = null;
  
    this.container      = this.treeview.parent();           // Store the container element ("#" + tv_kenn + "_out")
    this.boxIDsel       = null;                             // Store the active Box-ID
    this.uliIDsel       = null;                             // Store the selected li-element
    this.auto_box_width = false;                            // check if compact-view is active
    this.namelist_do    = false;                            // check if namelist is active
    this.updating       = false;                            // check if there are actions pending
    this.showstats_do   = false;                            // check if showstats-form is shown
    this.shownext_do    = true;                             // check if show-next-panel is shown
    this.shownext_all   = false;                            // check if auto-expand is active
  
    this.stateMin       = 0;                                // store the minimum of state-values (aka child-generations)
    this.stateMax       = 0;                                // store the maximum of state-values (aka ancestor-generations)

    this.glevel_width  = 229;                              // width of a basic level-box - the tv_box with its tv_hlines on each side

    this.map_rB         = new Map();
    this.map_RC         = new Map();
    
    this.doubleRoot     = false;
    let _tree_RPs = document.getElementsByClassName('tv_tree_RC');
    if( _tree_RPs.length > 1) { 
        this.doubleRoot = true;
        let i = 0;
        let tree_RPs = tv.treeview.find('.tv_tree_RC').each(function (index, RC) {
            RC = $(RC, tv.treeview);
            let min_gl, max_gl, min_gl_a, max_gl_a = 0;
            let RC_boxes    = RC.find('.hasBox').each(function (ind_hB, hB) {
                let hB_gl = hB.getAttribute('glevel');
                if (hB_gl < 0) {
                    if (hB_gl < min_gl) { min_gl = hB_gl; }
                } else {
                    if (hB_gl > max_gl) { max_gl = hB_gl; }
                }
            });
            let m_key       = 'RClfd-'+i;
            tv.map_RC.set(m_key, [RC, min_gl, max_gl, min_gl_a, max_gl_a]);     // *_gl_a: Placeholders for later on actual values
            let RC_stL      = RC[0].style.left;
            let RC_cw       = RC[0].offsetWidth;
            let RC_oL       = RC[0].offsetLeft;
            console.log('setup-RC', m_key, 'RC_stL:', RC_stL, 'RC_cw:', RC_cw, 'dR -> min_gl:', min_gl, 'max_gl:', max_gl, 'RC_oL:', RC_oL);
            i++;
        });
        i = 0;
        let rBoxes = tv.treeview.find('.rootPerson').each(function (index, rB) {
            rB.setAttribute('rBlfd', i);
            rB = $(rB, tv.treeview);
            let rB_p        = rB.parent();
            let rB_p_oL     = rB_p[0].offsetLeft;
            let rB_c        = rB.parent().parent().parent().parent();
            let rB_c_oL     = rB_c[0].offsetLeft;
            let rB_leftS    = rB_p_oL + rB_c_oL;
            let rB_c_oW     = rB_c[0].offsetWidth;
            let m_key       = 'rBlfd-'+i;
            tv.map_rB.set(m_key, [rB, rB_p, rB_p_oL, rB_c, rB_c_oL, rB_leftS, rB_c_oW]);
            let rB_stL      = rB_c[0].style.left;
            let rB_cw       = rB_c[0].offsetWidth;
            // console.log('setup', m_key, 'rBstL:', rB_stL, 'rBcw:', rB_cw, 'dR -> rB_p_oL:', rB_p_oL, 'rB_c_oL:', rB_c_oL, 'rb_leftS', rB_leftS, 'rB_p-0:', rB_p[0].offsetLeft, 'rB_c-0', rB_c[0].offsetLeft);
            i++;
        });
    }
  
    // Drag handlers for the treeview canvas
    (   function () {
        let dragging = false;
        let isDown = false;
        let drag_start_x;
        let drag_start_y;
    
        tv.treeview.on('mousedown touchstart', function (event) {
            // event.preventDefault();
    
            let pageX = (event.type === 'touchstart') ? event.touches[0].pageX : event.pageX;
            let pageY = (event.type === 'touchstart') ? event.touches[0].pageY : event.pageY;
    
            drag_start_x = tv.treeview.offset().left - pageX;
            drag_start_y = tv.treeview.offset().top - pageY;
            isDown = true;
        });
    
        $(document).on('mousemove touchmove', function (event) {
            if (isDown) {
                event.preventDefault();
                dragging = true;
        
                let pageX = (event.type === 'touchmove') ? event.touches[0].pageX : event.pageX;
                let pageY = (event.type === 'touchmove') ? event.touches[0].pageY : event.pageY;
        
                tv.treeview.offset({
                    left: pageX + drag_start_x,
                    top: pageY + drag_start_y,
                });
            }
        });
    
        $(document).on('mouseup touchend', function (event) {
            isDown = false;
            if (dragging) {
            event.preventDefault();
            dragging = false;
    //        tv.updateTree(false);         // EW.H - MOD ... dropped Autoexpand
            }
        });
    })();
    //
    // Add click handlers to buttons
    //
    // Toggle arranging boxes in columns or condensed
    tv.toolbox.find('#' + tv_kenn + 'bCompact').each(function (index, tvCompact) {
        tvCompact.onclick = function () {
            tv.compact();
        };
    });
    // Show/Hide Show-Next-Panel
    tv.toolbox.find('#' + tv_kenn + 'bShowNext').each(function (index, tvShowNext) {
        tvShowNext.onclick = function () {
            tv.shownext(tv_kenn);
        };
    });
    // Show/Hide Stats-Panel
    tv.toolbox.find('#' + tv_kenn + 'bShowStats').each(function (index, tvShowStats) {
        tvShowStats.onclick = function () {
            tv.showstats(tv);
        };
    });
    // Show/Hide Namelist-Form
    tv.toolbox.find('#' + tv_kenn + 'bNamelist').each(function (index, tvNamelist) {
        tvNamelist.onclick = function () {
            tv.namelist(tv_kenn);
        };
    });
    // Execute 'Transform viewport-content to PNG'
    tv.toolbox.find('#' + tv_kenn + 'bexportPNG').each(function (index, tvExportPNG) {
        tvExportPNG.onclick = function () {
            tv.setLoading();
            tv.exportPNG(tv_kenn);
        };
    });
    // Toggle Viewport to Fullscreen
    tv.toolbox.find('#' + tv_kenn + 'bfs').each(function (index, tvFullScreen) {
        let _tvFullScreen = tvFullScreen;
        tvFullScreen.onclick = function() {
            tv.container.parent().toggleClass('tvfs-full-screen');
            tv.container.closest('.wt-ajax-load').toggleClass('tvfs-full-screen');
            tv.doFullScreen = !tv.doFullScreen;
            if (tv.doFullScreen)
                _tvFullScreen.title = MinTitle;
            else
                _tvFullScreen.title = MaxTitle;
        };
    });
    // Center on rootperson
    // tv.toolbox.find('#' + tv_kenn + 'bCenter').each(function (index, tvCenter) {
    //     tvCenter.onclick = function () {
    //         tv.centerOnRoot();
    //     };
    // });
    
    // Execute Show-Next-1
    // - set the _all-state to false
    tv.next.find('#' + tv_kenn + 'bShowNext1').each(function (index, tvShowNext1) {
        tvShowNext1.onclick = function () {
            tv.shownext_all = false;
            tv.shownextDo(tv_kenn, '1');
        };
    });
    // Execute Show-Next-All
    // - set the _all-state to true
    tv.next.find('#' + tv_kenn + 'bShowNextAll').each(function (index, tvShowNextAll) {
        tvShowNextAll.onclick = function () {
            tv.shownext_all = true;
            tv.shownextDo(tv_kenn, 'All');
        };
    });
    // Add click-event to showstats-form to close it
    this.container.find('#' + tv_kenn + '_showstats').each(function(index, tvfShowStats) {
        tvfShowStats.onclick = function(event) {
            tv.showstats(tv, true);
        };
    });
    // Add click-event to namelist to add Drag-and-Drop functionality
    this.container.find('#' + tv_kenn + '_namelist').each(function(index, tvNamelistDrag) {
        s_elnl = tv_kenn + '_namelist';
        tvNamelistDrag.onclick = dragElement(document.getElementById(s_elnl), tv);
    });
    // Add click-event to namelist-ul to Highlight selected line and corresponding box in viewport
    // Click is done on child element, therefore event will only be done when bubbling happens ...
    // Click-event will also occur when resizing element itsself, but then eventPhase == 2 - event.AT_TARGET
    this.container.find('#' + tv_kenn + '_namelistul').each(function(index, tvNamelistUl) {
        tvNamelistUl.onclick = function(event) {
            if (event.eventPhase == 3) {          // Click on li -> eventPhase == 3 - event.BUBBLING_PHASE
                tv.namelistul(event);
                event.preventDefault();
            } else
                event.stopPropagation();
        };
    });
    // Add click-event to namelist to save the list
    this.container.find('#' + tv_kenn + '_namelistSave').each(function(index, tvNamelistSave) {
        tvNamelistSave.onclick = function(event) {
            nString = dumpNlist_txt(tv);
        };
    });
    // Add click-event to namelist to close the form
    this.container.find('#' + tv_kenn + '_namelistClose').each(function(index, tvNamelistClose) {
        tvNamelistClose.onclick = function(event) {
            tv.namelist(tv_kenn);
        };
    });
    // Add click-event to ClippingCart 
    tv.toolbox.find('#' + tv_kenn + 'bClipping').each(function (index, tvClipping) {
        if (!tvClipping.classList.contains('noCCEadapter')) {
            tvClipping.onclick = function () {
                tv.CCEadapter(null, tv.treeHome);
            };
        }
    });

    // fire ajax update if needed, which call setComplete() when all is loaded
    tv.centerOnRoot();
    
    //
    if (doFullScreen) {
        tv.container.parent().toggleClass('tvfs-full-screen');
    }
}
/**
 * Class TreeView setLoading method
 */
TreeViewHandlerXT.prototype.setLoading = function () {
    this.treeview.css('cursor', 'wait');
    this.loadingImage.css('display', 'block');
};
/**
 * Class TreeView setComplete  method
 */
TreeViewHandlerXT.prototype.setComplete = function () {
    this.treeview.css('cursor', 'move');
    this.loadingImage.css('display', 'none');
};
  
/**
 * Class TreeView getSize  method
 * Store the viewport current size
 */
TreeViewHandlerXT.prototype.getSize = function () {
    var tv = this;
    // retrieve the current container bounding box
    var container = tv.container.parent();
    var offset = container.offset();
    tv.leftMin = offset.left;
    tv.leftMax = tv.leftMin + container.innerWidth();
    tv.topMin = offset.top;
    tv.topMax = tv.topMin + container.innerHeight();
};
  
/**
   * Class TreeView updateTree  method
   * Perform ajax requests to complete the tree after drag
   * @param center    boolean     center on root person when done
   * @param button    (object)    correspondig button
   * @param doall     boolean     if true -> load links also off-viewport
   * @param doloop    boolean     if true -> load links until there are no more links left
   */
TreeViewHandlerXT.prototype.updateTree = function (center, button, doall, doloop) {
    var tv = this; // Store "this" for usage within jQuery functions where "this" is not this ;-)
    var to_load = [];
    var elts = [];
    this.getSize();
    if (!tv.showstats_do) {
        tv.showstats(tv);
    }
    // check which td with datafld attribute are within the container bounding box
    // and therefore need to be dynamically loaded
    var RC_boxleft = false;
    tv.treeview.find('td[abbr]').each(function (index, el) {
        el = $(el, tv.treeview);
        let _boxleft = (el.attr('align') == 'left');     // equivalent to class "exp_toRight" at subordinate level 
        RC_boxleft = (RC_boxleft || _boxleft);     // equivalent to class "exp_toRight" at subordinate level 
        if (doall) {                            // load all nodes in chart
            let _load = el.attr('abbr') + '|' + el.attr('state');
            let _rclfd = el.attr('rclfd');
            if (_rclfd) { _load = _load + '|' + _rclfd; }
            to_load.push(_load);
            elts.push(el);
        } else {
            var pos = el.offset();              // load only when node is in viewport
            if (pos.left >= tv.leftMin && pos.left <= tv.leftMax && pos.top >= tv.topMin && pos.top <= tv.topMax) {
                let _load = el.attr('abbr') + '|' + el.attr('state');
                let _rclfd = el.attr('rclfd');
                if (_rclfd) { _load = _load + '|' + _rclfd; }
                to_load.push(_load);
                elts.push(el);
            }
        }
        let bc = el.parent();                   // bc is Box Container
        var t = null;
        if (_boxleft) {
            let bcp = bc.parent().parent().parent();
            t = $(bcp[0].previousSibling.firstChild);
        } else {
            t = $(bc[0].firstChild.nextSibling.firstChild);
        }
        var t0 = t[0];
        if (t0.classList.contains('TreeToExpand')) {     // first call, we have to expand the link(s)
            t0.classList.remove('TreeToExpand');
        } else {
            t0.classList.remove('TreeExpand');
        }
        t0.classList.add('TreeCollaps');
    });
    tv.updateTreeDo(tv, center, button, doall, doloop, elts, to_load, RC_boxleft);
  };
  
/**
 * Class TreeView updateTree  method
 * Perform ajax requests to expand the tree on demand
 * - either click on 'TreeToExpand'-widget in the viewport 
 *   or by executing the 'Expand-1'/'Expand-All'-methods in Show-Next-panel
 * @param tv            (object)    the parent-object
 * @param center        boolean     center on root person when done
 * @param button        (object)    correspondig button in Show-Next-panel
 * @param doall         boolean     if true -> load links even when they are not in viewport
 * @param doloop        boolean     if true -> load links until there are no more links left
 * @param elts          (array)     elements containing link information
 * @param to_load       (array)     strings carrying link information
 * @param exp_toRight   boolean     true: expanding to the left
 */
TreeViewHandlerXT.prototype.updateTreeDo = function (tv, center, button, doall, doloop, elts, to_load, exp_toRight) {
    // if some boxes need update, we perform an ajax request
    if (to_load.length > 0) {
        var root_element = $('.rootPerson', tv.treeview);
        tv.updating = true;
        tv.setLoading();
        var re_o_left   = root_element.offset().left;
        var _rclfd      = null;
        jQuery.ajax({
            url: tv.ajaxPersons,
            dataType: 'json',
            data: 'q=' + to_load.join(';'),
            success: function (ret) {
                var nb = elts.length;
                for (var i = 0; i < nb; i++) {
                    let _el = elts[i];
                    _el.removeAttr('abbr');
                    _el.html(ret[i]);
                }
                // we now ajust the draggable treeview size to its content size
                tv.getSize();
                // we have a splitted view - 2 or more .rootPerson elements - so we have to adjust the offsetLeft of each
                if (tv.doubleRoot) {
                    doubleRoot_RC('uTD', tv, exp_toRight );
                }
            },
            complete: function () {
                tv.showstatsExec( tv );
                if (doloop) {                                   // recursive call only when explicitely wanted
                    if (tv.treeview.find('td[abbr]').length) {
                        tv.updateTree(center, button, doall, doloop); // recursive call
                    }
                }
                // the added boxes need that in mode compact boxes
                if (tv.auto_box_width) {
                    tv.treeview.find('.tv_box').css('width', 'auto');
                }
                tv.updating = true; // avoid an unuseful recursive call when all requested persons are loaded
                if (center) {
                    tv.centerOnRoot();
                }
                if (button) {
                    button.removeClass('tvPressed');
                }
                tv.setComplete();
                tv.updating = false;
            },
            timeout: function () {
                if (button) {
                    button.removeClass('tvPressed');
                }
                tv.updating = false;
                tv.setComplete();
            }
        });
    } else {
        if (button) {
            button.removeClass('tvPressed');
        }
        tv.setComplete();
    }
    return false;
};
  
/**
 * Class TreeView CCEadapter method
 * Perform ajax requests to send XREFs to cart
 */
TreeViewHandlerXT.prototype.CCEadapter = function (button, treeHome) {
    var tv = this; // Store "this" for usage within jQuery functions where "this" is not this ;-)
    var XREF_ar = [];
    this.getSize();

    // check which div with tv_box attribute are within the container bounding box
    // - they are carrying pid -> xref
    var tds = tv.treeview.find('td.hasBox');
      for (let tdx of tds) {
        if (tdx.firstChild && !tdx.hidden) {
          var boxes = $(tdx).find('.tv_box:visible');
          for(let bx of boxes) {
            let elb = $(bx, tv.treeview);
            let elbP = elb[0].parentElement;
            if (!elbP.classList.contains('not-visible')) {
                if (elb[0].checkVisibility({visibilityProperty: true})) {
                    let elf_pid = elb.find('[pid]');
                    for(let _elx of elf_pid) {
                        let el = $(_elx, tv.treeview);
                        XREF_ar.push(el.attr('pid'));
                    }
                    if (elb[0].hasAttribute('fid')) {
                        let _fid = elb[0].getAttribute('fid');
                        if (_fid.indexOf('|') < 0) {
                            XREF_ar.push(_fid);
                        } else {
                            let _fids = _fid.split('|');
                            for(let _fid of _fids) {
                                XREF_ar.push(_fid);
                            }
                        }
                    }
                }
            }
          }
        //   break;
        }
      }
    // if some boxes need update, we perform an ajax request
    if (XREF_ar.length > 0) {
        tv.updating = true;
        tv.setLoading();
        jQuery.ajax({
            url: tv.ajaxCCE,
            dataType: 'json',
            data: 'xrefs=' + XREF_ar.join(';'),
            success: function (ret) {
                var _ret = ret;
                updateCCEcount(_ret, treeHome);
                return true;
                },
            complete: function () {
                tv.updating = true; // avoid an unuseful recursive call when all requested persons are loaded
                if (button) {
                    button.removeClass('tvPressed');
                }
                tv.setComplete();
                tv.updating = false;
            },
            timeout: function () {
                if (button) {
                    button.removeClass('tvPressed');
                }
                tv.updating = false;
                tv.setComplete();
            }
        });
    } else {
        if (button) {
            button.removeClass('tvPressed');
        }
        tv.setComplete();
    }
    return false;
};

/**
 * Class TreeView compact method
 */
TreeViewHandlerXT.prototype.compact = function (tv_kenn) {
    var tv = this;
    var b = $('#' + tv.tv_kenn + 'bCompact', tv.toolbox);
    tv.setLoading();
    if (tv.auto_box_width) {
        var w = tv.boxWidth * (tv.zoom / 100) + 'px';
        var ew = tv.boxExpandedWidth * (tv.zoom / 100) + 'px';
        tv.treeview.find('.tv_box:not(boxExpanded)', tv.treeview).css('width', w);
        tv.treeview.find('.boxExpanded', tv.treeview).css('width', ew);
        tv.auto_box_width = false;
        b.removeClass('tvPressed');
    } else {
        tv.treeview.find('.tv_box').css('width', 'auto');
        tv.auto_box_width = true;
        if (!tv.updating) {
        //   tv.updateTree(false);
        }
        b.addClass('tvPressed');
    }
    tv.setComplete();
    return false;
};
  
/**
 * Class TreeView show/hide shownext panel
 */
TreeViewHandlerXT.prototype.shownext  = function (tv_kenn, auto_close) {
    var tv = this;
    var b = $('#' + tv.tv_kenn + 'bShowNext', tv.toolbox);
    if (auto_close) {
        tv.shownext_do = true;
    }
    tv.setLoading();
    if (tv.shownext_do) {
        tv.shownext_do = false;
        tv.next.toggle(false);
        b.removeClass('tvPressed');
    } else {
        tv.shownext_do = true;
        tv.next.toggle(true);
        b.addClass('tvPressed');
    }
    tv.setComplete();
    return false;
};
  
/**
 * Class TreeView shownext method
 * @param tv_kenn   string      Prefix for IDs to identify individual elements
 * @param tv_kenn   nextXXX     Identifying 'Expand-1'/'Expand-All'
 */
TreeViewHandlerXT.prototype.shownextDo = function (tv_kenn, nextXXX) {
    var tv = this;
    var b = $('#' + tv_kenn + 'bShowNext' + nextXXX, tv.next);
    tv.setLoading();
    b.addClass('tvPressed');
    tv.treeview.find('.tv_box').css('width', 'auto');
    tv.auto_box_width = true;
    if (!tv.updating) {
        tv.updateTree(false, b, true, tv.shownext_all);
        if (tv.shownext_all) {
            tv.shownext(tv_kenn, true);
        }
    }
    tv.setComplete();
    return false;
};
  
/**
 * Class TreeView showstats method
 */
TreeViewHandlerXT.prototype.showstats  = function (tv, auto_close) {
    var b = $('#' + tv.tv_kenn + 'bShowStats', tv.toolbox);
    tv.setLoading();
    if (auto_close) {
        tv.showstats_do = true;
    }
    if (tv.showstats_do) {
        tv.showstats_do = false;
        tv.stats.toggle(false);
        b.removeClass('tvPressed');
    } else {
        tv.showstats_do = true;
        tv.stats.toggle(true);
        b.addClass('tvPressed');
        this.showstatsExec( tv );
    }
    tv.setComplete();
    return false;
};
/**
 * Class TreeView showstatsExec method
 */
TreeViewHandlerXT.prototype.showstatsExec  = function (tv) {
    let tv_kenn = tv.tv_kenn;
    var n_listCount = 0;
    var to_loadCount = 0;
    tv.stateMin = 0; tv.stateMax = 0;
    // check which div with tv_box attribute are within the container bounding box
    // - they are carrying names
    var tds = tv.treeview.find('td.hasBox');
    for (let tdx of tds) {
        if (tdx.firstChild && !tdx.hidden) {
            var boxes = $(tdx).find('.tv_box:visible');
            for(let bx of boxes) {
                let elb = $(bx, tv.treeview);
                let elbP = elb[0].parentElement;
                if (!elbP.classList.contains('not-visible')) {
                    if (elb[0].checkVisibility({visibilityProperty: true})) {
                        let elNf = elb.find('.NAME');
                        for(let _elx of elNf) {
                            let bs = parseInt(_elx.parentNode.getAttribute('glevel'));
                            if (bs < tv.stateMin) { tv.stateMin = bs; }
                            if (bs > tv.stateMax) { tv.stateMax = bs; }
                            n_listCount += 1;
                        }
                    }
                }
            }
        }
    }
    // check which td with datafld attribute are within the container
    // and therefore would need to be dynamically loaded
    var tda = tv.treeview.find('div.TreeToExpand');
    for(let ela of tda) {
        if (ela.checkVisibility({visibilityProperty: true})) {
            to_loadCount += 1;
        }
    }
    // get the viewport's canvas
    var theChart = $('#' + tv_kenn + '_in');
    theChart = theChart[0];
    theChart = theChart.children[0];
    let tCw = theChart.clientWidth;
    let tCh = theChart.clientHeight;
    // update stats-form-elements
    let elN = $( '#' + tv_kenn + 'sNames');
    let elNt = elN.children();
    elNt[1].textContent = n_listCount;
    let elL = $( '#' + tv_kenn + 'sLinks');
    let elLt = elL.children();
    let slLt = (to_loadCount > 0) ? String(to_loadCount) : '-0-';
    elLt[1].textContent = slLt;
    let elS = $( '#' + tv_kenn + 'sStates');
    let elSt = elS.children();
    elSt[1].textContent = tv.stateMin;
    elSt[2].textContent = tv.stateMax;
    let elD = $( '#' + tv_kenn + 'sDims');
    let elDt = elD.children();
    elDt[2].textContent = tCw;
    elDt[4].textContent = tCh;
};
  
/**
 * Class TreeView export top PNG
 */
TreeViewHandlerXT.prototype.exportPNG = function (tv_kenn) {
    var tv = this;
    this.getSize();
    var b = $('#' + tv_kenn + 'bexportPNG', tv.toolbox);
    b.addClass('tvPressed');
    tv.setLoading();
    // get the rootPerson-Name as filename
    var ftv = tv.treeview[0];
    var fBox = ftv.getElementsByClassName('rootPerson');
    var fName = fBox[0].firstChild.innerText;
    // get the viewport inner elements
    var _tvtree = tv.treeview;
    _tvtree = _tvtree[0];
    var el = _tvtree.children[0];
    // export viewport's content as png
    // html2canvas    -> Base64
    // dataURItoBlob  -> PNG-file format
    if ( el ) {
        html2canvas(el, { allowTaint: true })
            .then(function (canvas) {
                var a = document.createElement('a');
                var file = dataURItoBlob(canvas.toDataURL(), 'image/png');
                var filename = fName + '.png';
                a.href= URL.createObjectURL(file);
                a.download = filename;
                a.click();
                
                URL.revokeObjectURL(a.href);
            });
    }
    tv.setComplete();
    b.removeClass('tvPressed');
    return false;
};
  
/**
 * Class TreeView namelist method
 */
TreeViewHandlerXT.prototype.namelist = function (tv_kenn) {
    var tv = this;
    this.getSize();
    var b = $('#' + tv_kenn + 'bNamelist', tv.toolbox);
    if (tv.namelist_do) {
        b.removeClass('tvPressed');
        if (tv.uliIDsel) {                                // there is a list-element selected ...
            let uIDoff = tv.uliIDsel;
            uIDoff.classList.remove('selectedID');
            tv.uliIDsel = null;
        }
        if (tv.boxIDsel) {                                // remove adjacent outlinings
            for (let obox of tv.boxIDsel) {
                let bIDoff = obox.parentElement;
                bIDoff.classList.remove('selectedID');
            }
            tv.boxIDsel = null;
        }
        tv.namelist_do = false;
        tv.names.toggle(false);
    } else {
        tv.namelist_do = true;
        b.addClass('tvPressed');
        tv.setLoading();
        makeNList(tv);
        tv.names.toggle(true);
    }
    tv.setComplete();
    return false;
};
    
/**
 * Class TreeView namelist method
*/
TreeViewHandlerXT.prototype.namelistul = function (event) {
    var tv = this;
    let bIDoff = null;
    let tvBox = null;
    this.getSize();
    let elA = event.target;
    if (elA.classList.contains('selectedID')) {       // List-element already selected
        if (tv.boxIDsel) {                                  // remove adjacent outlinings
            for (let obox of tv.boxIDsel) {
            bIDoff = obox.parentElement;
            bIDoff.classList.remove('selectedID');
            }
        }
        elA.classList.remove('selectedID');                 // remove selection
        return false;
    }
    if (tv.uliIDsel) {                                // there is another list-element already selected ...
        if (tv.boxIDsel) {                                  // remove adjacent outlinings
            for (let obox of tv.boxIDsel) {
            bIDoff = obox.parentElement;
            bIDoff.classList.remove('selectedID');
            }
            tv.boxIDsel = null;
        }
        let uIDoff = tv.uliIDsel;
        uIDoff.classList.remove('selectedID');
    }
    elA.classList.add('selectedID');                  // mark list-element as selected
    tv.uliIDsel = elA;                                // save list-element
    let slA = elA.textContent;
    let xrefID = slA.substring(slA.indexOf('(')+1);
    xrefID = xrefID.substring(0, xrefID.indexOf(')'));
    xrefID = ''.concat(tv.tv_kenn, 'xref', xrefID);
    var boxes = document.getElementsByName(xrefID);
    if (boxes) {
        if (tv.boxIDsel) {
            for (let obox of tv.boxIDsel) {
                bIDoff = obox.parentElement;
                bIDoff.classList.remove('selectedID');
            }
            }
            for (let abox of boxes) {
                tvBox = abox.parentElement;
                tvBox.classList.add('selectedID');
            }
            tv.boxIDsel = boxes;
            let obox = boxes[0];
            bIDoff = obox.parentElement;
            if ( !isInViewport(bIDoff)) {
                bIDoff.scrollIntoView({
                    behavior: "smooth",
                    block: "center",
                    inline: "center",
                });
        }
    }
    return false;
};
  
/**
 * Class TreeView centerOnRoot method
 */
TreeViewHandlerXT.prototype.centerOnRoot = function () {
    this.loadingImage.css('display', 'block');
    var tv = this;
    var tvc = this.container;
    var root_person = $('.rootPerson', this.treeview);

    var tvc_width = tvc.innerWidth() / 2;
    if (Number.isNaN(tvc_width)) {
        return false;
    }
    var tvc_height = tvc.innerHeight() / 2;

    // let tvc_left = tvc.offset().left;
    // let tv_left = this.treeview.offset().left;
    // let tv_width = this.treeview.outerWidth()/2;
    // let rp_left = root_person.offset().left;
    // let rp_owidth = root_person.outerWidth()/2;

    // let tvc_top = tvc.offset().top;
    // let tv_top = this.treeview.offset().top;
    // let tv_height = this.treeview.outerHeight()/2;
    // let rp_top = root_person.offset().top;
    // let rp_oheight = root_person.outerHeight()/2;

    // var dLeft = 0; // tvc_width - tv_width; // tvc_left + tv_left + tvc_width - rp_left - rp_owidth;
    // var dTop = 0; // tvc_height - tv_height; // tvc_top + tv_top + tvc_height + rp_top - rp_oheight;
    // this.treeview.offset({left: dLeft, top: dTop});
    if (!this.updating) {
        // tv.updateTree(true);
        tv.setComplete();
    }
    return false;
};
  
/**
 * Class TreeView expandTree method
 * Called ONLY for elements which have NOT the class tv_link to avoid un-useful requests to the server
 * @param {string} box   - the person box element
 * @param {string} event - the call event
 */
TreeViewHandlerXT.prototype.expandTree = function (_box, event) {
    var t = $(event.target);
    if (t.hasClass('tv_link')) {
        return false;
    }
  
    var tv = this;            // Store "this" for usage within jQuery functions where "this" is not this ;-)
    var to_load = [];
    var elts = [];
  //   this.getSize();
    if (!tv.showstats_do) {
        tv.showstats(tv);
    }
    let box = $(_box, this.treeview);
    let _exp_toRight = box.hasClass('exp_toRight');    // 
    let bc = box.parent();                // bc is Box Container
    var bcp = null;
    if (_exp_toRight) {
        bcp = $(bc[0].nextSibling);
        bcp = $(bcp[0].firstChild);
        // if (!bcp[0].hasAttribute("abbr"))
        //     bcp = $(bcp[0].firstChild);
        // if (!bcp[0].hasAttribute("abbr"))
        //     bcp = $(bcp[0].firstChild);
        // if (!bcp[0].hasAttribute("abbr"))
        //     bcp = $(bcp[0].firstChild);
    } else {
        bcp = $(bc[0].previousSibling);
        // bcp = $(bcp[0].previousSibling);
    }
    let _test_shift = false;
    if (t.hasClass('TreeToExpand')) {     // first call, we have to expand the link(s)
        if (_exp_toRight) {                                 // 1 to n links stored in child-elements
            $(bcp).find('td[abbr]').each(function (index, el) {
                el = $(el, bcp);
                let e_state = el.attr('state');
                if (e_state < 0) {_test_shift = true;}
                let e_load = el.attr('abbr') + '|' + e_state;
                let e_rclfd = el.attr('rclfd');
                if (e_rclfd) { e_load = e_load + '|' + e_rclfd; }
                to_load.push(e_load);
                elts.push(el);
            });
        } else {                                        // only 1 link directly stored in element
            if (!bcp[0].hasAttribute('abbr'))
                bcp = $(bcp[0].previousSibling);
            if (!bcp[0].hasAttribute('abbr'))
                bcp = $(bcp[0].previousSibling);
            let b_state = bcp.attr('state');
            if (b_state < 0) {_test_shift = true;}
            let b_load = bcp.attr('abbr') + '|' + b_state;
            let b_rclfd = bcp.attr('rclfd');
            if (b_rclfd) { b_load = b_load + '|' + b_rclfd; }
            to_load.push(b_load);
            elts.push(bcp);
        }
        /**
         * @param tv        (object)    the parent-object
         * @param center    boolean     center on root person when done
         * @param button    (object)    correspondig button in Show-Next-panel
         * @param doall     boolean     if true -> load links even when they are not in viewport
         * @param doloop    boolean     if true -> load links until there are no more links left
         * @param elts      (array)     elements containing link information
         * @param to_load   (array)     strings carrying link information
         */
        tv.updateTreeDo(tv, false, null, false, false, elts, to_load, _exp_toRight);
        t[0].classList.remove('TreeToExpand');
        t[0].classList.add('TreeCollaps');
    } else {                              // allready expanded, we have to hide the content
        if (t.hasClass('TreeCollaps')) {
            bcp[0].classList.toggle('hidden');
            t[0].classList.add('TreeExpand');
            t[0].classList.remove('TreeCollaps');
        } else {                            // ... or otherwise to show it again
            if (t.hasClass('TreeExpand')) {
                bcp[0].classList.toggle('hidden');
                t[0].classList.add('TreeCollaps');
                t[0].classList.remove('TreeExpand');
            }
        }
        doubleRoot_RC('epT', tv, _exp_toRight);
    }
    tv.showstatsExec( tv );
    if (_test_shift) {
        tv.testShift();
    }
    return true;
};

/**
 * Class TreeView switchVis method - partial expand/collapse
 * Called ONLY for elements which have NOT the class tv_link to avoid un-useful requests to the server
 * @param {string} box   - the person box element
 * @param {string} event - the call event
 */
TreeViewHandlerXT.prototype.switchVis = function (_box, event) {
    function toggleAttached(elem, _exp_toRight, _cname, _action) {
        function cl_action(elem, _cname, _action) {
            if (_action == 'add') {
                elem.classList.add(_cname);
            } else {
                if (elem.className.includes(_cname[0])) { elem.classList.remove(_cname[0]); }
                if (elem.className.includes(_cname[1])) { elem.classList.remove(_cname[1]); }
            }
        }
        cl_action(elem, _cname, _action);
        if (_exp_toRight) {
            let eprev = elem.previousSibling;
            cl_action(eprev, _cname, _action);
            let enext1 = elem.nextSibling;
            if (enext1) {
                let enext2 = enext1.nextSibling;
                cl_action(enext1, _cname, _action);
                if (enext2) {
                    let enext3 = enext2.nextSibling;
                    cl_action(enext2, _cname, _action);
                    if (enext3) {
                        cl_action(enext3, _cname, _action);
                    }
                }
            }
        } else {
            let eprev1 = elem.previousSibling;
            if (eprev1) {
                let eprev2 = eprev1.previousSibling;
                cl_action(eprev1, _cname, _action);
                if (eprev2) {
                    let eprev3 = eprev2.previousSibling;
                    cl_action(eprev2, _cname, _action);
                    if (eprev3) {
                        let eprev4 = eprev3.previousSibling;
                        cl_action(eprev3, _cname, _action);
                        if (eprev4) {
                            cl_action(eprev4, _cname, _action);
                        }
                    }
                }
            }
        }
    }
    var t = $(event.target);
    if (t.hasClass('tv_link')) {
        return false;
    }
  
    var tv = this;            // Store "this" for usage within jQuery functions where "this" is not this ;-)
    var to_load = [];
    var elts = [];
  //   this.getSize();
    if (!tv.showstats_do) {
        tv.showstats(tv);
    }
    let box = $(_box, this.treeview);
    let _exp_toRight = box.hasClass('exp_toRight');
    let bc = box.parent();                // bc is Box Container
    var bcp = null;
    if (_exp_toRight) {
        bcp = $(bc[0].nextSibling);
        bcp = $(bcp[0].nextSibling);
    } else {
        bcp = $(bc[0].previousSibling);
    }
    let _test_shift = false;
    // '...VisOFF'  -> attached structure(s)
    //      Click       not-visible -> visibilty: hidden
    //      Ctrl-Click  is-hidden   -> display: none
    let $_cname = (event.ctrlKey) ? 'is-hidden' : 'not-visible';
    let $_off_cL = ['switchPartVisOFF'];
    if ($_cname == 'is-hidden') { $_off_cL.push('andHidden'); }
    if (t.hasClass('switchPartVisON')) {    // temporarly conceal the attached structure(s) ...
        t[0].classList.add(...$_off_cL);
        t[0].classList.remove('switchPartVisON');
        toggleAttached(bcp[0], _exp_toRight, $_cname, 'add');
    } else {                            // ... or otherwise show it again
        if (t.hasClass('switchPartVisOFF')) {
            t[0].classList.add('switchPartVisON');
            t[0].classList.remove(...$_off_cL);
            let $_cname_ar = ['is-hidden', 'not-visible'];     // one or the other may be set - we want to remove them either
            toggleAttached(bcp[0], _exp_toRight, $_cname_ar, 'remove');
        }
    }
    tv.showstatsExec( tv );
    if (_test_shift) {
        tv.testShift();
    }
    return true;
};

/**
 * Class TreeView testShift method
 */
TreeViewHandlerXT.prototype.testShift = function () {
    this.loadingImage.css('display', 'block');
    var tv = this;
    var tvc = this.container;
    var tvc_width = tvc.innerWidth() / 2;
    if (Number.isNaN(tvc_width)) {
        tv.setComplete();
        return false;
    }
    var tvc_height  = tvc.innerHeight() / 2;
    var root_person = $('.rootPerson', this.treeview);

    let tvc_left    = tvc.offset().left;
    let tv_left     = this.treeview.offset().left;
    let tvc_top     = tvc.offset().top;
    let tv_top      = this.treeview.offset().top;
    console.log('testShift -> tvc_left:', tvc_left, 'tv_left:', tv_left, 'tvc_width', tvc_width);

    var dLeft = 0; // tvc_left + tv_left + tvc_width - root_person.offset().left - root_person.outerWidth/2;
    var dTop = tv_top; // tvc.offset().top + this.treeview.offset().top + tvc_height + root_person.offset().top - root_person.outerHeight/2;
    this.treeview.offset({left: dLeft, top: dTop});
    if (!this.updating) {
        // tv.updateTree(true);
        tv.setComplete();
    }
    return false;
};

/**
 * Class TreeView expandBox method
 * Called ONLY for elements which have NOT the class tv_link to avoid un-useful requests to the server
 * @param {string} box   - the person box element
 * @param {string} event - the call event
 */
TreeViewHandlerXT.prototype.expandBox = function (_box, event) {
    var t = $(event.target);
    if (t.hasClass('tv_link')) {
        return false;
    }

    var box = $(_box, this.treeview);
    var bc = box.parent();                // bc is Box Container
    var pid = box.attr('abbr');
    var tv = this;            // Store "this" for usage within jQuery functions where "this" is not this ;-)
    var expanded;
    var collapsed;

    if (bc.hasClass('detailsLoaded')) {
        collapsed = bc.find('.collapsedContent');
        expanded = bc.find('.tv_box:not(.collapsedContent)');
    } else {
        // Cache the box content as an hidden person's box in the box's parent element
        expanded = box;                             // make ref to actual element -> it will get the imported content
        collapsed = box.clone();                    // save actual content in new element
        var attrbts = expanded.prop("attributes");
        // loop through expanded attributes and apply them on collapsed -> deep cloning
        $.each(attrbts, function() {
            collapsed.attr(this.name, this.value);
        });
        expanded.after(collapsed.addClass('collapsedContent').css('display', 'none'));  // insert new element directly after old element
        // we add a waiting image at the right side of the box
        var loading_image = this.loadingImage.find('img').clone().addClass('tv_box_loading').css('display', 'block');
        box.prepend(loading_image);
        tv.updating = true;
        tv.setLoading();
        // perform the Ajax request and load the result in the box - quite a lot of additional stuff
        box.load(tv.ajaxDetails + '&pid=' + encodeURIComponent(pid), function () {
            // If Lightbox module is active, we reinitialize it for the new links
            if (typeof CB_Init === 'function') {
                CB_Init();
            }
            box.css('width', tv.boxExpandedWidth * (tv.zoom / 100) + 'px');
            loading_image.remove();
            bc.addClass('detailsLoaded');
            tv.setComplete();
            tv.updating = false;
        });
    }
    if (box.hasClass('boxExpanded')) {
        expanded.css('display', 'none');
        collapsed.css('display', 'block');
        box.removeClass('boxExpanded');
    } else {
        expanded.css('display', 'block');
        collapsed.css('display', 'none');
        expanded.addClass('boxExpanded');
    }
    // we must ajust the draggable treeview size to its content size
    this.getSize();
    return false;
};

function updateCCEcount(XREFcnt, elem_main) {
    let pto = typeof XREFcnt;
    switch (pto) {
        case 'object':
            showCountPop(XREFcnt, elem_main);
            break;
        case 'number':
        default:
            break;
    }
}
function showCountPop(XREFcnt, elem_main) {
    //                                          Information zum Zustand im Cart ...
    let vCntS = XREFcnt[0];                     // Num - Anzahl Elemente im Cart gesamt
    let vCntN = XREFcnt[1];                     // Num - davon neue Elemente
    let vCntStxt = XREFcnt[2];                  // String - Infotext (I18N) Anzahl Elemente im Cart gesamt
    let vCntNtxt = XREFcnt[3];                  // String - Infotext (I18n) davon neue Elemente
    var elem_pop = document.getElementById('CCEpopUp');
    if (!elem_pop) {
        let elem_dpop = document.createElement('div');
        elem_dpop.id = 'CCEpopUp';
        elem_dpop.classList = 'CCEpopup CCE-xtv-popup hidden';

        let elem_dlineS = document.createElement('div');
        elem_dlineS.className = 'pop-line lineS';
        elem_dpop.appendChild(elem_dlineS);
        let elem_dlineN = document.createElement('div');
        elem_dlineN.className = 'pop-line lineN';
        elem_dpop.appendChild(elem_dlineN);

        elem_main.appendChild(elem_dpop);

        elem_pop = document.getElementById('CCEpopUp');
    }
    let elem_par_par = elem_pop.parentNode.parentNode;
    var elem_stats = elem_par_par.getElementsByClassName('tv_showstats')[0];
    if (elem_stats) {
        elem_stats.classList.toggle('hidden');
        setTimeout(show_stats,3000);
    }

    let elem_dlineS = elem_pop.firstElementChild;
    elem_dlineS.textContent = vCntStxt;
    let elem_dlineN = elem_pop.lastElementChild;
    elem_dlineN.textContent = vCntNtxt;
    if (elem_pop.classList.contains('hidden'))
        elem_pop.classList.remove('hidden');
    elem_pop.style.opacity = 1;
    setTimeout(fadeOutpu,2400);
    function fadeOutpu() {
        fadeOut(elem_pop);
    }
    function show_stats() {
        elem_stats.classList.toggle('hidden');
    }
}
function fadeOut(elem_fade) {
    let elem_par = elem_fade.parentNode;
    var op = 1;  // initial opacity
    var timer = setInterval(function () {
        if (op <= 0.1){
            clearInterval(timer);
            elem_fade.classList.add('hidden');
            elem_par.removeChild(elem_fade);
        }
        elem_fade.style.opacity = op;
        elem_fade.style.filter = 'alpha(opacity=' + op * 100 + ")";
        op -= op * 0.2;
    }, 100);
}


/**
 * @param {string} name
 * @param {string} value
 * @param {number} days
 */
// function createCookie (name, value, days) {
// if (days) {
//     var date = new Date();
//     date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
//     document.cookie = name + '=' + value + '; expires=' + date.toGMTString() + '; path=/';
// } else {
//     document.cookie = name + '=' + value + '; path=/';
// }
// }

/**
 * Creates a sorted overview from the names displayed in the treeview.
 * 
 * @param {object} tv
 */
function makeNList(tv) {
    var n_list = [];
    let n_list0 = [];
    var to_load = [];
    var elts = [];
    // check which div with tv_box attribute are within the container bounding box
    // - they are carrying names
    var boxes = tv.treeview.find('.tv_box:visible');
    boxes.each(function (index, el) {
        let elb = $(el, tv.treeview);
        let elbP = elb[0].parentElement;
        if (!elbP.classList.contains('not-visible')) {
            let elNs = elb.find('.NAME');
            let sID = '';
            let sNs = 'N.N.';
            elNs.each(function (index, elNf) {
                let sNv = elNf.innerText;                 // Vorname
                sNs = ' N.N.';
                if (elNf.children.length > 0) {
                    let eNs = elNf.children[0];               // Zuname in eigenem SPAN-Element
                    sNs = eNs.innerText;
                    let pNs = sNv.indexOf(sNs);               // unter Umständen Zunamen bereits mitgegeben ...
                    if (pNs > 0) {                                // dann rausschneiden
                        sNv = sNv.replace(sNs, '');
                        sNv = sNv.trim();
                    }
                }
                let elNsp = elNf.parentElement;               // EW.H - MOD ... surrounding DIV carries the personal ID
                sID = elNsp.getAttribute('pID');
                sID = ''.concat(' (', sID, ')');
                let lN = sNs.concat(', ', sNv, sID);      // Listeneintrag zusammenstellen
                n_list0.push(lN);
            });
        }
    });
    n_list0.sort(function (l,u) {
        return l.toLowerCase().localeCompare(u.toLowerCase());
    });

    let a_name = ''; let p_name = ''; let l_name = '';
    let n_ln = n_list0.length;
    let n=1;
    for(let i = 0; i < n_ln; i++) {
        a_name = n_list0[i];
        if (a_name == p_name) {
            n++;
        } else {
            if (p_name == '') {
                p_name = a_name;
            } else {
                l_name = p_name;
                if (n>1) {
                    l_name = p_name + ' . . . . [' + n + ']';
                    n = 1;
                }
                n_list.push(l_name);
                p_name = a_name;
                l_name = a_name;
            }
        }
    }
    if (n>1) {
        l_name = p_name + ' . . . . [' + n + ']';
        n = 1;
    }
    if (l_name > '') {
        n_list.push(l_name);
    }

    let elN = $( '#' + tv.tv_kenn + 'lNames');    // Anzahl Listeneinträge 
    let elNt = elN.children();
    let n_lL = n_list.length;
    elNt[1].textContent = n_lL;

    // check which td with datafld attribute are within the container
    // and therefore would need to be dynamically loaded
    tv.treeview.find('td[abbr]').each(function (index, el) {
        el = $(el, tv.treeview);
        to_load.push(el.attr('abbr'));
    });
    let elL = $( '#' + tv.tv_kenn + 'lLinks');    // Anzahl noch offene Links
    let elLt = elL.children();
    let slLt = (to_load.length > 0) ? String(to_load.length) : '-keine-';
    elLt[1].textContent = slLt;

    nl = $('#' + tv.tv_kenn + '_namelistul');
    nl.empty();
    for(const lN of n_list) {
        let liTag = '<li>';
        if (lN.includes('. . . . [')) { liTag = '<li class="multName">'; }
        nl.append(liTag + lN + '</li>');
    }
    let nl_fCh = 12.8;
    var etvp = tv.container;
    let htv = etvp.outerHeight();
    let hnl = htv - 85;
    let hnlc = nl_fCh * n_lL + 16;
    if (hnlc < hnl)
        hnl = hnlc;
    tv.namesul.outerHeight(hnl);
}

function dumpNlist_txt(tv)
{
    nStringAr = [];
    nl = $('#' + tv.tv_kenn + '_namelistul')[0];
    nlc = nl.children;
    for(const lNe of nlc) {
        nStringAr.push(lNe.textContent);
    }
    nString = nStringAr.join('\r\n');
    downloadToFile(nString, 'webtrees-treeviewXT-NameList.txt', 'text/plain');
    return true;
}
function downloadToFile(content, filename, contentType) 
{
    const a = document.createElement('a');
    const file = new Blob([content], {type: contentType});
    
    a.href= URL.createObjectURL(file);
    a.download = filename;
    a.click();

    URL.revokeObjectURL(a.href);
}

function dataURItoBlob(dataURI, type) {
    // convert base64 to raw binary data held in a string
    var byteString = atob(dataURI.split(',')[1]);

    // separate out the mime component
    var mimeString = dataURI.split(',')[0].split(':')[1].split(';')[0];

    // write the bytes of the string to an ArrayBuffer
    var ab = new ArrayBuffer(byteString.length);
    var ia = new Uint8Array(ab);
    for (var i = 0; i < byteString.length; i++) {
        ia[i] = byteString.charCodeAt(i);
    }

    // write the ArrayBuffer to a blob, and you're done
    var bb = new Blob([ab], { type: type });
    return bb;
}

/**
 * Add event-handler to Plus-/Minus-Buttons in page.html-showgens
 * 
 * @param {object} tv
 */
function showgensPrep() {
    $('#showgensSub').click(function () {
        showgensMinus();
    });
    $('#showgensAdd').click(function () {
    showgensPlus();
    });
    const f_options = document.getElementById('XToptions');
    const c_boxes = f_options.querySelectorAll('input');
    c_boxes.forEach((c_box) => {
        c_box.addEventListener('change', (event) => {
            let et = event.target;
            et.checked  ? et.value = '1' : et.value = '0';
          });
    });
}
function showgensMinus() {
    var esgV = $("#generations");
    let vsgV = parseInt(esgV[0].value);
    let esgVmin = parseInt(esgV[0].getAttribute("min"));
    let esgVmax = parseInt(esgV[0].getAttribute("max"));
    vsgV -= 1;
    if (esgVmin > 0 && vsgV < esgVmin ) { vsgV = esgVmin; }
    if (esgVmax > 0 && vsgV > esgVmax ) { vsgV = esgVmax; }
    esgV[0].value = vsgV.toString();
    return false;
}
function showgensPlus() {
    var esgV = $("#generations");
    let vsgV = parseInt(esgV[0].value);
    let esgVmin = parseInt(esgV[0].getAttribute("min"));
    let esgVmax = parseInt(esgV[0].getAttribute("max"));
    vsgV += 1;
    if (esgVmin > 0 && vsgV < esgVmin ) { vsgV = esgVmin; }
    if (esgVmax > 0 && vsgV > esgVmax ) { vsgV = esgVmax; }
    esgV[0].value = vsgV.toString();
    return false;
}
  
function isInViewport(el) {
    const rect = el.getBoundingClientRect();
    return (
        rect.top >= 0 &&
        rect.left >= 0 &&
        rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
        rect.right <= (window.innerWidth || document.documentElement.clientWidth)
    );
}
  
function dragElement(elmnt, tv) {
    var pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
    var tv_out = tv.container[0];
    if (document.getElementById(elmnt.id + "header")) {
        // the header is where you move the DIV from:
        document.getElementById(elmnt.id + "header").onmousedown = dragMouseDown;
    } else {
        // otherwise, move the DIV from anywhere inside the DIV:
        elmnt.onmousedown = dragMouseDown;
    }

    function dragMouseDown(e) {
        // e = e || window.event;
        e.preventDefault();
        // get the mouse cursor position at startup:
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        // call a function whenever the cursor moves:
        document.onmousemove = elementDrag;
    }

    function elementDrag(e) {
        // e = e || window.event;
        e.preventDefault();
        // calculate the new cursor position:
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        // set the element's new position:
        let _top = elmnt.offsetTop - pos2;
        if (_top < 1) { _top = 1; }
        if (_top > tv_out.clientHeight) { _top = (tv_out.clientHeight - 24);}
        let _left = elmnt.offsetLeft - pos1;
        if (_left < 1) { _left = 1; }
        if (_left > (tv_out.clientWidth - elmnt.offsetWidth)) { _left = (tv_out.clientWidth - elmnt.offsetWidth);}
        elmnt.style.top = _top + "px";
        elmnt.style.left = _left + "px";
    }

    function closeDragElement() {
        // stop moving when mouse button is released:
        document.onmouseup = null;
        document.onmousemove = null;
    }
}

function doubleRoot_rB(_pref, tv_map_rB, exp_toRight) {
    function dR_val(_val) {
        let r_val = _val;
        if ( typeof(_val) == 'string' ) {
            if (_val.endsWith('px') ) {
                let l_val = _val.length;
                let s_val = _val.substring(0, l_val-2);
                r_val = Number.parseInt(s_val);
            }
        }
        return r_val;
    }
    let max_rB_left = 0;
    if (!exp_toRight) {
        tv_map_rB.forEach(function(rb_val,m_key) {
            let rB_p        = rb_val[1];            // rootPerson's parent
            let rB_p_oL     = rB_p[0].offsetLeft;
            let rB_c        = rb_val[3];            // the container of rootPerson
            let rB_c_oL     = rB_c[0].offsetLeft;
            let rB_leftT    = rB_p[0].offsetLeft + rB_c[0].offsetLeft;
            if (rB_leftT > max_rB_left) { max_rB_left = rB_leftT; }
            let rB_stL      = rB_c[0].style.left;
            if (rB_stL)                 { max_rB_left -= rB_stL; }
            let rB_cw  = rB_c[0].offsetWidth;
            console.log(_pref, m_key, 'exp_toLeft', 'rBstL:', rB_stL, 'rBcw:', rB_cw, 'dR -> rB_p_oL:', rB_p_oL, 'rB_c_oL:', rB_c_oL, 'rB_leftT', rB_leftT, 'rB_p-0:', rB_p[0].offsetLeft, 'rB_c-0', rB_c[0].offsetLeft);
        });
        return;
    }

    //                       0   1     2        3     4        5         6
    // tv.map_rB.set(m_key, [rB, rB_p, rB_p_oL, rB_c, rB_c_oL, rB_leftS, rB_c_oW]);
    //               m_key > 'rBlfd-'+i;                                                                    - i = rootbox counter (1, 2, ...)
    //                       rB                                                                             - rootbox itself
    //                           rB_p > rB.parent();                                                        - parent element
    //                                 rB_p_oL > rB_p[0].offsetLeft;                                        - parent's offsetLeft
    //                                          rB_c > rB.parent().parent().parent().parent();              - container element
    //                                                rB_c_oL > rB_c[0].offsetLeft;                         - container's offsetLeft
    //                                                         rB_leftS > rB_p_oL + rB_c_oL;                - checksum
    //                                                                   rB_c_oW > rB_c[0].offsetWidth;     - container's width
    tv_map_rB.forEach(function(rb_val,m_key) {
        // let rB          = rb_val[0];                // rootPerson's box
        let rB_p        = rb_val[1];                // rootPerson's parent
        let rB_p_oL_a   = rB_p[0].offsetLeft;
        let rB_c        = rb_val[3];                // the container of rootPerson
        let rB_c_oL     = rb_val[4];                // container's offsetLeft
        let rB_c_oL_a   = rB_c[0].offsetLeft;
        let rB_leftT    = rB_p_oL_a + rB_c_oL_a;
        if (rB_leftT > max_rB_left) { max_rB_left = rB_leftT; }
        let rB_stL      = rB_c[0].style.left;
        let rB_cw  = rB_c[0].offsetWidth;
        if (rB_stL) { 
            rB_stL = dR_val(rB_stL);
            if (rB_stL > 0)  { max_rB_left -= rB_stL; }
        }
        console.log(_pref + '_tst', m_key, 'exp_toRight', 'max_rB_left', max_rB_left, 'rB_leftT', rB_leftT, 'rBstL:', rB_stL, 'rBcw:', rB_cw, 'rB_p_oLa:', rB_p_oL_a, 'rB_c_oLa', rB_c_oL_a);
    });
    tv_map_rB.forEach(function(rb_val,m_key) {
        // initial values
        let rB_p        = rb_val[1];            // rootPerson's parent
        let rB_p_oL     = rb_val[2];            // parent's offsetLeft at setup
        let rB_p_oL_a   = rB_p[0].offsetLeft;   // dto. actual
        let rB_c        = rb_val[3];            // the container of rootPerson
        let rB_c_oL     = rb_val[4];            // container's offsetLeft at setup
        let rB_c_oL_a   = rB_c[0].offsetLeft;   // dto. actual
        let rB_leftT    = rB_p_oL_a + rB_c_oL_a;
        // let rB_c_oW     = rb_val[6];            // ... offsetWidth
        // adjusting ...
        if (rB_leftT == max_rB_left) {
            if (rB_p_oL == rB_p_oL_a) { rB_c.offset({left: 0}); }
        } else {
            if (rB_leftT > max_rB_left) {
                // rB_c.offset({left: rB_c_oL + 1});
            } else {
                rB_c.offset({left: max_rB_left-rB_leftT});
            }
        }
        let rB_stL = rB_c[0].style.left;
        let rB_cw  = rB_c[0].offsetWidth;
        console.log(_pref + '_do_', m_key, 'exp_toRight', 'max_rB_left', max_rB_left, 'rB_leftT', rB_leftT, 'rBstL:', rB_stL, 'rBcw:', rB_cw, 'dR -> rB_p_oL:', rB_p_oL, 'rB_c_oL:', rB_c_oL, 'rB_p_oLa:', rB_p_oL_a, 'rB_c_oLa', rB_c_oL_a);
    });

}

function doubleRoot_RC(_pref, tv, exp_toRight) {
    function dR_val(_val) {
        let r_val = _val;
        if ( typeof(_val) == 'string' ) {
            if (_val.endsWith('px') ) {
                let l_val = _val.length;
                let s_val = _val.substring(0, l_val-2);
                r_val = Number.parseInt(s_val);
            }
        }
        return r_val;
    }

    let tv_map_RC = tv.map_RC;
    //                       0   1       2       3         4
    // tv.map_RC.set(m_key, [RC, min_gl, max_gl, min_gl_a, max_gl_a]);     // *_gl_a: Placeholders for later on actual values
    // tv.map_RC.set(m_key, [min_gl, max_gl]);
    //               m_key > 'RBlfd-'+i;                                                                    - i = RootContainer counter (1, 2, ...)
    //                       RC                                                                             - RootContainer itself
    //                           min_gl                                                                     - minimum glevel of .hasBox elements - left side
    //                                   max_gl                                                             - maximum glevel of .hasBox elements - right side
    let RC_min_gl_t = 0, RC_max_gl_t = 0;
    let txt_exp_toXXX = (exp_toRight ? 'exp_toRight' : 'exp_toLeft');
    tv_map_RC.forEach(function(RC_val,m_key) {
        let RC          = RC_val[0];                // RootContainer
        let RC_min_gl   = RC_val[1];                // minimum glevel - initial value
        let RC_max_gl   = RC_val[2];                // maximum glevel - initial value
        let RC_min_gl_a = 0;                        // minimum glevel - actual value
        let RC_max_gl_a = 0;                        // maximum glevel - actual value
        let RC_boxes    = RC.find('.hasBox:visible').each(function (ind_hB, hB) {
            let hB_gl = parseInt(hB.getAttribute('glevel'));
            if (hB_gl < 0) {
                if (hB_gl < RC_min_gl_a) { RC_min_gl_a = hB_gl; }
            } else {
                if (hB_gl > RC_max_gl_a) { RC_max_gl_a = hB_gl; }
            }
        });
        tv.map_RC.set(m_key, [RC, RC_min_gl, RC_max_gl, RC_min_gl_a, RC_max_gl_a]);

        if (RC_min_gl_a < RC_min_gl_t) { RC_min_gl_t = RC_min_gl_a; }
        if (RC_max_gl_a > RC_max_gl_t) { RC_max_gl_t = RC_max_gl_a; }

        console.log(_pref + '_tst', m_key, txt_exp_toXXX, 'RC_min_gl_a', RC_min_gl_a, 'RC_max_gl_a', RC_max_gl_a, 'RC_min_gl_t', RC_min_gl_t, 'RC_max_gl_t', RC_max_gl_t);
    });

    let exp_toLeft = !exp_toRight;
    tv_map_RC.forEach(function(RC_val,m_key) {
        let RC          = RC_val[0];                // RootContainer
        let RC_min_gl   = RC_val[1];                // minimum glevel - initial value
        let RC_max_gl   = RC_val[2];                // maximum glevel - initial value
        let RC_min_gl_a = RC_val[3];                // minimum glevel - actual value
        let RC_max_gl_a = RC_val[4];                // maximum glevel - actual value
        let diff_gl_m   = 0;
        if (RC_min_gl_a == RC_min_gl_t) {
            RC.offset({left: 1});
        } else {
            if (exp_toLeft) {
                if (RC_min_gl_t < RC_min_gl_a) {
                    diff_gl_m = ((RC_min_gl_a - RC_min_gl_t) * tv.glevel_width) + 1;
                    RC.offset({left: diff_gl_m});
                }
                let RC_stL = dR_val(RC[0].style.left);
                if (diff_gl_m != 0 && RC_stL != diff_gl_m) {
                    diff_gl_m -= 1;
                    RC_stL = diff_gl_m;
                    RC[0].style.left = diff_gl_m+'px';
                }
                let RC_oW  = RC[0].offsetWidth;
                console.log(_pref + '_do_', m_key, txt_exp_toXXX, 'RC_stL:', RC_stL, 'RC_oW:', RC_oW, 'RC_min_gl_a', RC_min_gl_a, 'RC_max_gl_a', RC_max_gl_a, 'RC_min_gl_t', RC_min_gl_t, 'RC_max_gl_t', RC_max_gl_t);
            // } else {
            //     let RC_stL = dR_val(RC[0].style.left);
            //     let RC_oW  = RC[0].offsetWidth;
            //     console.log(_pref + '_do_', m_key, txt_exp_toXXX, 'RC_stL:', RC_stL, 'RC_oW:', RC_oW, 'RC_min_gl_a', RC_min_gl_a, 'RC_max_gl_a', RC_max_gl_a, 'RC_min_gl_t', RC_min_gl_t, 'RC_max_gl_t', RC_max_gl_t);
            }
        }
    });

}
