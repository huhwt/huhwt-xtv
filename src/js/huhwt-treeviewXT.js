/**
 * webtrees: online genealogy
 * Copyright (C) 2019 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
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
 *      adopted webtrees-treeview_full_screen by UksusoFF
 *          (full functionality depends on this extension)
 *      export to PNG
 *          (done by html2canvas  https://html2canvas.hertzen.com/)
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

function TreeViewHandlerXT (tv_kenn, doFullScreen = false) {
    var tv = this; // Store "this" for usage within jQuery functions where "this" is not this ;-)
    this.tv_kenn = tv_kenn;
  
    this.treeview = $('#' + tv_kenn + '_in'); //[0];
    this.loadingImage = $('#' + tv_kenn + '_loading');
    this.toolbox = $('#' + tv_kenn + '_tools');
    this.next = $('#' + tv_kenn + '_shownext');
    this.stats = $('#' + tv_kenn + '_showstats');
    this.buttons = $('.tv_button:first', this.toolbox);
    this.names = $('#' + tv_kenn + '_namelist');
    this.namesul = $('#' + tv_kenn + '_namelistul');
    this.zoom = 100; // in percent
    this.boxWidth = 180; // default family box width
    this.boxExpandedWidth = 250; // default expanded family box width
    this.cookieDays = 3; // lifetime of preferences memory, in days
    this.ajaxDetails = document.getElementById(tv_kenn + '_out').dataset.urlDetails + '&instance=' + encodeURIComponent(tv_kenn);
    this.ajaxPersons = document.getElementById(tv_kenn + '_out').dataset.urlIndividuals + '&instance=' + encodeURIComponent(tv_kenn);
  
    this.pIDsel = null;
  
    this.container = this.treeview.parent();      // Store the container element ("#" + tv_kenn + "_out")
    this.boxIDsel = null;                         // Store the active Box-ID
    this.uliIDsel = null;                         // Store the selected li-element
    this.auto_box_width = false;                  // check if compact-view is active
    this.namelist_do = false;                     // check if namelist is active
    this.updating = false;                        // check if there are actions pending
    this.showstats_do = false;                    // check if showstats-form is shown
    this.shownext_do = true;                      // check if show-next-panel is shown
    this.shownext_all = false;                    // check if auto-expand is active
  
    this.stateMin = 0;                            // store the minimum of state-values (aka child-generations)
    this.stateMax = 0;                            // store the maximum of state-values (aka ancestor-generations)
  
    // Drag handlers for the treeview canvas
    (function () {
      let dragging = false;
      let drag_start_x;
      let drag_start_y;
  
      tv.treeview.on('mousedown touchstart', function (event) {
        event.preventDefault();
  
        let pageX = (event.type === 'touchstart') ? event.touches[0].pageX : event.pageX;
        let pageY = (event.type === 'touchstart') ? event.touches[0].pageY : event.pageY;
  
        drag_start_x = tv.treeview.offset().left - pageX;
        drag_start_y = tv.treeview.offset().top - pageY;
        dragging = true;
      });
  
      $(document).on('mousemove touchmove', function (event) {
        if (dragging) {
          event.preventDefault();
  
          let pageX = (event.type === 'touchmove') ? event.touches[0].pageX : event.pageX;
          let pageY = (event.type === 'touchmove') ? event.touches[0].pageY : event.pageY;
  
          tv.treeview.offset({
            left: pageX + drag_start_x,
            top: pageY + drag_start_y,
          });
        }
      });
  
      $(document).on('mouseup touchend', function (event) {
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
      tvFullScreen.onclick = function() {
        tv.container.parent().toggleClass('tvfs-full-screen');
      };
    });
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
    this.container.find('#' + tv_kenn + '_namelistul').each(function(index, tvNamelistUl) {
      tvNamelistUl.onclick = function(event) {
        tv.namelistul(event);
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
    tv.treeview.find('td[abbr]').each(function (index, el) {
      el = $(el, tv.treeview);
      if (doall) {                            // load all nodes in chart
        let _load = el.attr('abbr') + '|' + el.attr('state');
        to_load.push(_load);
        elts.push(el);
      } else {
        var pos = el.offset();                // load only when node is in viewport
        if (pos.left >= tv.leftMin && pos.left <= tv.leftMax && pos.top >= tv.topMin && pos.top <= tv.topMax) {
          let _load = el.attr('abbr') + '|' + el.attr('state');
          to_load.push(_load);
          elts.push(el);
        }
      }
      var _boxleft = (el.attr('align') == 'left');
      let bc = el.parent();                // bc is Box Container
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
    tv.updateTreeDo(tv, center, button, doall, doloop, elts, to_load);
  };
  
  /**
   * Class TreeView updateTree  method
   * Perform ajax requests to expand the tree on demand - either click on 'TreeToExpand'-widget in 
   * the viewport or by executing the 'Expand-1'/'Expand-All'-methods in Show-Next-panel
   * @param tv        (object)    the parent-object
   * @param center    boolean     center on root person when done
   * @param button    (object)    correspondig button in Show-Next-panel
   * @param doall     boolean     if true -> load links even when they are not in viewport
   * @param doloop    boolean     if true -> load links until there are no more links left
   * @param elts      (array)     elements containing link information
   * @param to_load   (array)     strings carrying link information
   */
  TreeViewHandlerXT.prototype.updateTreeDo = function (tv, center, button, doall, doloop, elts, to_load) {
    // if some boxes need update, we perform an ajax request
    if (to_load.length > 0) {
      var root_element = $('.rootPerson', this.treeview);
      var l = root_element.offset().left;
      tv.updating = true;
      tv.setLoading();
      jQuery.ajax({
        url: tv.ajaxPersons,
        dataType: 'json',
        data: 'q=' + to_load.join(';'),
        success: function (ret) {
          var nb = elts.length;
          for (var i = 0; i < nb; i++) {
            let _elts = elts[i];
            _elts.removeAttr('abbr');
          //   let _eltsn = _elts.next().children[0];
          //   _eltsn.removeClass('tv_expand');
          //   _eltsn.addClass('tv_collapsed');
            _elts.html(ret[i]);
          }
          // repositionning
          root_element = $('.rootPerson', this.treeview);
          tv.treeview.offset({left: tv.treeview.offset().left - root_element.offset().left +l});
          // we now ajust the draggable treeview size to its content size
          tv.getSize();
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
   * Class TreeView compact method
   */
  TreeViewHandlerXT.prototype.compact = function (tv_kenn) {
    var tv = this;
    var b = $('#' + tv_kenn + 'bCompact', tv.toolbox);
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
    var b = $('#' + tv_kenn + 'bShowNext', tv.toolbox);
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
      tv.updateTree(true, b, true, tv.shownext_all);
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
      tv_kenn = tv.tv_kenn;
      var n_listCount = 0;
      var to_loadCount = 0;
      // check which div with tv_box attribute are within the container bounding box
      // - they are carrying names
      var tds = tv.treeview.find('td');
      for (let tdx of tds) {
        let doBreak = false;
        if (tdx.firstChild && !tdx.hidden) {
          var boxes = $(tdx).find('.tv_box:visible');
          for(let bx of boxes) {
            let elb = $(bx, tv.treeview);
            let elNf = elb.find('.NAME');
            for(let _elx of elNf) {
              n_listCount += 1;
            }
            let bs = parseInt(bx.getAttribute('state'));
            if (bs < tv.stateMin) { tv.stateMin = bs; }
            if (bs > tv.stateMax) { tv.stateMax = bs; }
          }
          break;
        }
      }
      // check which td with datafld attribute are within the container
      // and therefore would need to be dynamically loaded
      tv.treeview.find('td[abbr]').each(function (index, el) {
        to_loadCount += 1;
      });
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
      let slLt = (to_loadCount > 0) ? String(to_loadCount) : '-keine-';
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
      html2canvas(el, { allowTaint: true }).then(function (canvas) {
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
    var tvc_width = tvc.innerWidth() / 2;
    if (Number.isNaN(tvc_width)) {
      return false;
    }
    var tvc_height = tvc.innerHeight() / 2;
    var root_person = $('.rootPerson', this.treeview);
  
    var dLeft = tvc.offset().left + this.treeview.offset().left + tvc_width - root_person.offset().left - root_person.outerWidth/2;
    var dTop = tvc.offset().top + this.treeview.offset().top + tvc_height + root_person.offset().top - root_person.outerHeight/2;
    this.treeview.offset({left: dLeft, top: dTop});
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
    let _boxleft = box.hasClass('left');
    let bc = box.parent();                // bc is Box Container
    var bcp = null;
    if (_boxleft) {
      bcp = $(bc[0].nextSibling);
    } else {
      bcp = $(bc[0].previousSibling);
    }
    if (t.hasClass('TreeToExpand')) {     // first call, we have to expand the link(s)
      if (_boxleft) {                                 // 1 to n links stored in child-elements
        $(bcp).find('td[abbr]').each(function (index, el) {
          el = $(el, bcp);
          let _load = el.attr('abbr') + '|' + el.attr('state');
          to_load.push(_load);
          elts.push(el);
        });
      } else {                                        // only 1 link directly stored in element
        let _load = bcp.attr('abbr') + '|' + bcp.attr('state');
        to_load.push(_load);
        elts.push(bcp);
      }
      tv.updateTreeDo(tv, false, null, false, false, elts, to_load);
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
    }
    tv.showstatsExec( tv );
    return true;
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
  
/**
 * @param {string} name
 * @param {string} value
 * @param {number} days
 */
function createCookie (name, value, days) {
if (days) {
    var date = new Date();
    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
    document.cookie = name + '=' + value + '; expires=' + date.toGMTString() + '; path=/';
} else {
    document.cookie = name + '=' + value + '; path=/';
}
}

/**
 * Creates a sorted overview from the names displayed in the treeview.
 * 
 * @param {object} tv
 */
function makeNList(tv) {
var n_list = [];
var to_load = [];
var elts = [];
// check which div with tv_box attribute are within the container bounding box
// - they are carrying names
var boxes = tv.treeview.find('.tv_box:visible');
boxes.each(function (index, el) {
    var elb = $(el, tv.treeview);
    var elNs = elb.find('.NAME');
    var sID = '';
    elNs.each(function (index, elNf) {
    var sNv = elNf.innerText;                 // Vorname
    var eNs = elNf.children[0];               // Zuname in eigenem SPAN-Element
    var sNs = eNs.innerText;
    let pNs = sNv.indexOf(sNs);               // unter Umst??nden Zunamen bereits mitgegeben ...
    if (pNs > 0) {                                // dann rausschneiden
        sNv = sNv.replace(sNs, '');
        sNv = sNv.trim();
    }
    elNsp = elNf.parentElement;               // EW.H - MOD ... surrounding DIV carries the personal ID
    sID = elNsp.getAttribute('pID');
    sID = ''.concat(' (', sID, ')');
    var lN = sNs.concat(', ', sNv, sID);      // Listeneintrag zusammenstellen
    n_list.push(lN);
    });
});
// check which td with datafld attribute are within the container
// and therefore would need to be dynamically loaded
tv.treeview.find('td[abbr]').each(function (index, el) {
    el = $(el, tv.treeview);
    to_load.push(el.attr('abbr'));
});
let elN = $( '#' + tv.tv_kenn + 'lNames');    // Anzahl Listeneintr??ge 
let elNt = elN.children();
elNt[1].textContent = n_list.length;
let elL = $( '#' + tv.tv_kenn + 'lLinks');    // Anzahl noch offene Links
let elLt = elL.children();
let slLt = (to_load.length > 0) ? String(to_load.length) : '-keine-';
elLt[1].textContent = slLt;
n_list.sort(function (l,u) {
    return l.toLowerCase().localeCompare(u.toLowerCase());
});
nl = $('#' + tv.tv_kenn + '_namelistul');
nl.empty();
for(const lN of n_list) {
    nl.append('<li>' + lN + '</li>');
}
var etvp = tv.container;
let htv = etvp.outerHeight();
let hnl = htv - 60;
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
    e = e || window.event;
    e.preventDefault();
    // get the mouse cursor position at startup:
    pos3 = e.clientX;
    pos4 = e.clientY;
    document.onmouseup = closeDragElement;
    // call a function whenever the cursor moves:
    document.onmousemove = elementDrag;
    }

    function elementDrag(e) {
    e = e || window.event;
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
