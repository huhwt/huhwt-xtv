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
 * Trait    ... dump innerHTML of generated chart for analysis
 *
 * EW.H - MOD ... derived from webtrees/Module/InteractiveTree/Treeview.php
 */

trait TreeViewXTmod_dump {
    private function dump_file($individual, $innerHTML) {
        $dump_dir = Session::get('XTV_dumpDir');
        $_name = trim($this->name);
        $_root = $individual->xref();
        $_tree = $individual->tree();

        $_fName = $dump_dir . DIRECTORY_SEPARATOR . $_tree->name() . '-' . $_root . '-' . $this->mode . '.txt';
        $i_html = str_replace('\u{2068}','<bdi>',$innerHTML);
        $i_html = str_replace('\u{2069}','</bdi>',$i_html);

        // $_tr_09 = ['<table','<tbody','<tr','<td'];
        // $_tr_10 = ['</table>','</tbody>','</tr>','</td>'];
        // $i = 0;
        // foreach ($_tr_09 as $_tr_) {
        //     $_tr_nn = str_repeat(chr(9), $i);
        //     $_tr_r = chr(10) . $_tr_nn . $_tr_;
        //     $o_html = str_replace($_tr_, $_tr_r, $o_html);
        //     $i++;
        // }
        // foreach ($_tr_10 as $_tr_) {
        //     $_tr_r = $_tr_ . chr(10);
        //     $o_html = str_replace($_tr_, $_tr_r, $o_html);
        // }

        $_tr_s = ['<table','<tbody','<tr','<td'];
//      ....+....1....+....2....+....3....+....4....+....5
//      <table id="tvXTTreeBorder" class="tv_tree"><tbody><tr> ...
        $o_html = ''; $_tag = ''; $o_part = '';
        $l_ih = strlen($i_html);
        $ctab = 0;
        $is_tag_e = 0; $has_tag_e = 0;
        $t_a = 0; $t_e = 0;
        do {
            $l_ih = strlen($i_html);
            $cpos = stripos($i_html, '<');      // 0
            if ($cpos !== false) {
                $t_a = $cpos;                   // 0
                //     ....+....1....+....2....+....3....+....4....+....5
                //      table id="tvXTTreeBorder" class="tv_tree"><t
                $o_part = substr($i_html, $t_a + 1);               // table id="t ...
                $bpos = stripos($o_part, ' ');                      // 5
                if ($bpos === false)
                    $bpos = stripos($o_part,'>');
                $cpos = stripos($o_part, '><');                     // 41
                if ($cpos > 0 && $cpos < $bpos)
                    $bpos = $cpos;
                $t_e = $cpos + 1;   
                $_tag = substr($o_part, 0, $bpos);
                $cpref = '';
                $is_tag_e = 0;
                if (str_starts_with($_tag,'/')) {
                    $cpref = chr(10);
                    $is_tag_e = 1;
                    $_tag = substr($_tag,1);
                }
                switch ($_tag) {
                    case 'table':
                    case 'tbody':
                    case 'tr':
                    case 'td':
                        $has_tag_e = 0;
                        $t_e = stripos($i_html, '>');
                        $_tag_e = '></' . $_tag . '>';
                        $t_te = stripos($i_html, $_tag_e);
                        if ($t_e == $t_te) {
                            $t_e = $t_e + strlen($_tag_e) - 1;
                            $has_tag_e = 1;
                        }
                        $txt = substr($i_html, 0, $t_e+1);
                        if ($is_tag_e > 0) {
                            $ctab--;
                        }
                        if ($ctab < 0)
                            $ctab = 0;
                        $_tr_nn = str_repeat(chr(9), $ctab) . $txt;
                        if ($has_tag_e == 0) {
                            $ctab++;
                        }
                        if ($is_tag_e > 0) {
                            $ctab--;
                            $is_tag_e = 0;
                        }
                        if ($cpref) { 
                            $_tr_nn .= $cpref; 
                        } else {
                            $_tr_nn = chr(10) . $_tr_nn;
                            // $ctab++;
                        }
                        $o_html .= $cpref . $_tr_nn;
                        $i_html = substr($i_html, $t_e+1);
                        break;
                    case 'div':
                        $t_e = stripos($i_html,'</div>') + 5;
                        $new_div = 0;
                        if ($t_e > $l_ih)
                            $t_e = $l_ih;
                        $t_d = stripos($i_html, '<div', 4);
                        if ($t_d !== false) {
                            if ($t_d < $t_e) {
                                $t_e = $t_d - 1;
                                $new_div = 1;
                            }
                        }
                        $cpref = chr(10);
                        $txt = substr($i_html, 0, $t_e+1);
                        if ($is_tag_e > 0) {
                            $ctab--;
                            $is_tag_e = 0;
                        }
                        if ($ctab < 0)
                            $ctab = 0;
                        $_tr_nn = str_repeat(chr(9), $ctab) . $txt;
                        if ($new_div > 0)
                            $ctab++;
                        $o_html .= $cpref . $_tr_nn;
                        if ($t_e < $l_ih) {
                            $i_html = substr($i_html, $t_e+1);
                        } else {
                            $i_html = '';
                        }
                        break;
                    default:
                }
                $l_ih = strlen($i_html);
            } else {
                $l_ih = 0;
            }
        } while ($l_ih > 1);

        $_tr_ = chr(10) . chr(10);
        $_tr_r = chr(10);
        $o_html = str_replace($_tr_, $_tr_r, $o_html);

        file_put_contents($_fName, $o_html, LOCK_EX);

    }
}