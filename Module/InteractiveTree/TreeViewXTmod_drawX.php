<?php

/**
 * HuH Extensions for webtrees - Treeview-Extended
 * Interactive Treeview with add-ons
 * Copyright (C) 2020-2025 EW.Heinrich
 */

declare(strict_types=1);

namespace HuHwt\WebtreesMods\InteractiveTreeXT\Module;

use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\Gedcom;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Session;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;

use HuHwt\WebtreesMods\InteractiveTreeXT\Module\TreeViewXTmod;

/**
 * Trait    ... all secondary draw routines
 *
 * EW.H - MOD ... derived from webtrees/Module/InteractiveTree/Treeview.php
 */

trait TreeViewXTmod_drawX {

    private function table_root_header(string $earmark): string
    {
        $html   = '<table id="tv' . $earmark . 'TreeBorder" class="tv_tree">'
                    . '<tbody>'
                        . '<tr>'
                            . '<td id="tv' . $earmark . '_tree_topleft"></td>'
                            . '<td id="tv' . $earmark . '_tree_top"></td>'
                            . '<td id="tv' . $earmark . '_tree_topright"></td>'
                        . '</tr>'
                        . '<tr>'
                            . '<td id="tv' . $earmark . '_tree_left"></td>'
                            . '<td>';
        return $html;
    }

    private function table_root_xtR_header(string $earmark, string $p_header): string
    {
        $html   = '<table id="' . $earmark . 'TreeBorder" class="tv_tree">'
                    . '<tbody>'
                        . '<tr>'
                            . '<td id="' . $earmark . '_tree_headleft"></td>'
                            . '<td id="' . $earmark . '_tree_head">'. $p_header .'</td>'
                            . '<td id="' . $earmark . '_tree_headright"></td>'
                        . '</tr>';
        return $html;
    }
    private function table_root_xtR_nextpart(string $earmark, string $p_header): string
    {
        $html   = '<tr>'
                    . '<td id="' . $earmark . '_tree_topleft"></td>'
                    . '<td id="' . $earmark . '_tree_top">'. $p_header .'</td>'
                    . '<td id="' . $earmark . '_tree_topright"></td>'
                . '</tr>'
                . '<tr>'
                    . '<td id="' . $earmark . '_tree_left"></td>'
                    . '<td>';
        return $html;
    }
    private function table_root_xtR_nextpart_E(string $earmark): string
    {
        $html =       '</td>'
                    . '<td id="' . $earmark . '_tree_right"></td>'
                . '</tr>'
                . '<tr>'
                    . '<td id="' . $earmark . '_tree_bottomleft"></td>'
                    . '<td id="' . $earmark . '_tree_bottom"></td>'
                    .' <td id="' . $earmark . '_tree_bottomright"></td>'
                . '</tr>';
        return $html;
    }
    private function table_root_xtR_footer(string $earmark): string
    {
        $html   =   '</tbody>'
                . '</table>';
        return $html;
    }

    private function table_root_footer(string $earmark): string
    {
        $html = '</td><td id="tv' . $earmark . '_tree_right"></td></tr><tr><td id="tv' . $earmark . '_tree_bottomleft"></td><td id="tv' . $earmark . '_tree_bottom"></td><td id="tv' . $earmark . '_tree_bottomright"></td></tr></tbody></table>';         # EW.H - MOD
        return $html;
    }
        
    /**
     * Draw a vertical line
     *
     * @param string $line A parameter that set how to draw this line with auto-redimensionning capabilities
     *
     * @return string
     * WARNING : some tricky hacks are required in CSS to ensure cross-browser compliance
     * some browsers shows an image, which imply a size limit in height,
     * and some other browsers (ex: firefox) shows a <div> tag, which have no size limit in height
     * Therefore, Firefox is a good choice to print very big trees.
     */
    private function drawVerticalLine(string $line): string
    {
        return '<td class="tv_vline tv_vline_' . $line . '"><div class="tv_vline tv_vline_' . $line . '"></div></td>';
    }

    /**
     * Draw a horizontal line
     */
    private function drawHorizontalLine(string $expand_td = ''): string
    {
        return '<td class="tv_hline' . $expand_td . '"><div class="tv_hline"></div></td>';
    }

    /**
     * Draw a horizontal line - eventually including Expander
     */
    private function drawHorizontalLineT(string $expand_td = '', string $expand_div = ''): string
    {
        $html_td = '<td class="tv_hline' . $expand_td . '">';
        $html_td .= '<div class=" ' . $expand_div . '" title="' . I18N::translate('Click here to expand/collapse subtree') . '" onclick="' . $this->tvHandle . 'Handler.expandTree(this, event);"></div>';
        $html_td .= '</td>';
        return $html_td;
    }

    /**
     * Draw a horizontal line - including Visibility-Switch
     */
    private function drawHorizontalLineV(string $expand_div, string $expand_td = ''): string
    {
        $html_td = '<td class="tv_hline' . $expand_td . '">';
        $html_td .= '<div class=" ' . $expand_div . '" title="' . I18N::translate('Click here to hide/show partial subtree - Ctrl-Click -> collapse/expand') . '" onclick="' . $this->tvHandle . 'Handler.switchVis(this, event);"></div>';
        $html_td .= '</td>';
        return $html_td;
    }
}