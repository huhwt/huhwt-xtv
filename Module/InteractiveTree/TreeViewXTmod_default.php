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

use HuHwt\WebtreesMods\InteractiveTreeXT\Module\TreeViewXTmod_draws;

/**
 * Trait    ...draw_default routines
 *
 * EW.H - MOD ... derived from webtrees/Module/InteractiveTree/Treeview.php
 */

trait TreeViewXTmod_default {

    // use TreeViewXTmod_draws;

    /**
     * Draw a person in the tree
     *
     * @param Individual  $person   The Person object to draw the box for
     * @param string      $earmark  Mark  First - Second - (and so on) Treeview on page          # EW.H - MOD 
     * @param int         $gen      The number of generations up or down to print
     * @param int         $state    Whether we are going up or down the tree, -1 for descendents +1 for ancestors
     * @param Family|null $pfamily
     * @param string      $line     b, c, h, t. Required for drawing lines between boxes
     * @param bool        $isRoot
     * @param string      $tmark    table hierarchie
     *
     * @return string
     */
    private function drawPerson_default(Individual $person, string $earmark, int $gen, int $state, 
                                        Family|null $pfamily = null, string $line = '', bool $isRoot = false,
                                        string $tmark = ''): string
    {
        if ($gen < 0) {
            return '';
        }

        // if ($isRoot) {
        //     $this->glevel = 0;
        //     $html = '<table id="tv' . $earmark . 'TreeBorder" class="tv_tree"><tbody><tr><td id="tv' . $earmark . '_tree_topleft"></td><td id="tv' . $earmark . '_tree_top"></td><td id="tv' . $earmark . '_tree_topright"></td></tr><tr><td id="tv' . $earmark . '_tree_left"></td><td>';
        // } else {
        //     $html = '';
        // }
        if ($isRoot) {
            $this->glevel = 0;
            $html   = $this->table_root_header($earmark);
        } else {
            $html = '';
        }

        $Pxref = $person->xref();
        // if ($Pxref == 'I120')
        //     $debug_do = true;
        $isImplexI = 0;
        if ($this->showImplex)
            $isImplexI = $this->put_xrefsI($Pxref);

        $fID = ' fID="_NIX_"';
        $_fID = '';
        $pf_ID = null;
        $partners = $person->spouseFamilies();
        $isImplexF = 0;
        if ($pfamily instanceof Family) {
            $pf_ID = $pfamily->xref();
            $partner = $pfamily->spouse($person);
            $fID = ' fID="' . $pf_ID . '"';
            $_fID = $pf_ID;
            if ($this->showImplex)
                $isImplexF = $this->put_xrefsF('PF_' . $pf_ID);
        } else {
            $partner = $person->getCurrentSpouse();         // we use this as an indicator that there is at least 1 spouce
            $sf_ar = [];
            $sf_arj = '';
            foreach ($person->spouseFamilies() as $family) {
                $sf_ar[] = $family->xref();
            }
            if (!empty($sf_ar)) {
                $sf_arj = implode('|', $sf_ar);
            }
            $fID = ' fID="' . $sf_arj . '"';
            $_fID = $sf_arj;
        }

        /* height 1% : this hack enable the div auto-dimensioning in td for FF & Chrome */
        $html .= '<table class="tv_tree"' . ($isRoot ? ' id="tv' . $earmark . '_tree"' : '') . $tmark . ' style="height: 1%"><tbody><tr>';

        if ($state <= 0) {
            // draw children
            $this->glevel -= 1;
            $html .= $this->drawChildren_default($person->spouseFamilies(), $earmark, $gen, $state - 1);        # EW.H - MOD
            $this->glevel += 1;
        } else {
            // draw the parent’s lines
            $expand_div = 'exp_toRight huhwt_button16 switchPartVisON';
            $html .= $this->drawVerticalLine($line) . $this->drawHorizontalLineV($expand_div) . $this->drawHorizontalLine();
        }

        /* draw the person. Do NOT add person or family id as an id, since a same person could appear more than once in the tree !!! */
        // we store the person's html for later use -> there might be more than 1 family -> we want each family separated       # EW.H - MOD
        $A_glevel = ' glevel="' . $this->glevel . '"';
        $html .= '<td class="hasBox"' . $A_glevel . ' >';    // .hasBox CSS -> style width=1px   always because there might be more than 1 FAM-ID in GLEVEL which then would break the layout # EW.H - MOD
        $html .= '<div class="tv_box' . ($isRoot ? ' rootPerson' : ' def') . '" dir="' . I18N::direction() . '" style="text-align: ' . (I18N::direction() === 'rtl' ? 'right' : 'left') . '; direction: ' . I18N::direction() . '" abbr="' . $Pxref . '" state="' . $state . '" '. $fID . ' onclick="' . $this->tvHandle . 'Handler.expandBox(this, event);">';
        $html .= $this->drawPersonName($person, '', $isImplexI);

        $fop = []; // $fop is fathers of partners

//        if ($partner !== null) {
            $dashed = '';
            foreach ($person->spouseFamilies() as $family) {
                $do_sF  = true;
                if ($pf_ID)
                    if($family->xref() != $pf_ID)
                        $do_sF = false;
                if ($do_sF) {
                    $spouse = $family->spouse($person);
                    if ($spouse instanceof Individual) {
                        $spouse_parents = $spouse->childFamilies()->first();
                        if ($spouse_parents instanceof Family) {
                            $spouse_parent = $this->test_showmatri($spouse_parents);
                            if ($spouse_parent instanceof Individual) {
                                $fop[] = [$spouse_parent, $spouse_parents];
                            }
                        }

                        $isImplexP = 0;
                        if ($this->showImplex)
                            $isImplexP  = $this->put_xrefsI($spouse->xref());
                        $divorced   = false;
                        foreach ($family->facts(Gedcom::DIVORCE_EVENTS, true) as $fact) {
                            $divorced   = true;
                        }
                        $_isdivorced    = $divorced ? $this->divorced_icon : '';
                        $html .= $this->drawPersonName($spouse, $dashed, $isImplexP, $_isdivorced);
                        $dashed = 'dashed';
                    }
                }
            }
//        }

        $_glevel = '(' . I18N::translate('Generation') . ' ' . strval($this->glevel) . ')';
        $this->box_lfd += 1;
        $_glevel = '[' . strval($this->box_lfd) . '] ' . '-' . $_fID . '- ' . $_glevel;
        $html .= '</div><div class="tv_box_glevel">' . $_glevel;

        $html .= '</div></td>';

        if (!$this->suppImplex || $isImplexF < 1) {
            if ( $state >= 0) {
                $primaryChildFamily = $person->childFamilies()->first();
                if ($primaryChildFamily instanceof Family) {
                    $parent = $this->test_showmatri($primaryChildFamily);
                } else {
                    $parent = null;
                }
                $expand_div = ' TreeCollaps exp_toRight huhwt_button16';
                $html_HlineC = '';
                $html_HlineE = '';
                if ($parent instanceof Individual || !empty($fop)) {
                    $html_HlineC .= $this->drawHorizontalLine() . $this->drawHorizontalLineT(expand_div: $expand_div);
                    $html_HlineE .= $html_HlineC;
                    $html_HlineE = str_replace ( 'TreeCollaps', 'TreeToExpand', $html_HlineE);
                    /* draw the parents */
                    if ($parent instanceof Individual || !empty($fop)) {
                        $unique = $parent === null || empty($fop);
                        $snext = $this->glevel + 1;
                        $html_tb = '<td align="left"><table class="tv_tree"><tbody>';

                        if ($parent instanceof Individual) {
                            $u = $unique ? 'c' : 't';
                            if ($gen > 0) {
                                $html .= $html_HlineC;
                                $html .= $html_tb;
                                $html .= '<tr><td>';
                                $this->glevel += 1;
                                        /**
                                        * @param Individual  $person   The Person object to draw the box for
                                        * @param string      $earmark  Mark  First - Second - (and so on) Treeview on page
                                        * @param int         $gen      The number of generations up or down to print
                                        * @param int         $state    Whether we are going up or down the tree, -1 for descendents +1 for ancestors
                                        * @param Family|null $pfamily
                                        * @param string      $line     b, c, h, t. Required for drawing lines between boxes
                                        * @param bool        $isRoot
                                        */
                                $tmark = ' dps="P_' . $parent->xref() . '" ';
                                $html .= $this->drawPerson_default($parent, $earmark, $gen - 1, 1, $primaryChildFamily, $u, false, $tmark);
                                $this->glevel -= 1;
                                $html .= '</td></tr>';
                                $html_tb = '';
                                $html_HlineE = '';
                            } else {
                                $html .= $html_HlineE;
                                $html .= $html_tb;
                                $finis = ' abbr="p' . $primaryChildFamily->xref() . '@' . $u . '" state="' . $snext . '" align="left"';
                                $html .= '<tr><td' . $finis . '>';
                                $html .= '</td></tr>';
                                $html_tb = '';
                                $html_HlineE = '';
                            }
                        }

                        if (count($fop)) {
                            $n  = 0;
                            $nb = count($fop);
                            if ($gen > 0)
                                $html_HlineE = str_replace ( 'TreeToExpand', 'TreeCollaps', $html_HlineE);
                            $html .= $html_HlineE;
                            $html .= $html_tb;
                            foreach ($fop as $p) {
                                $n++;
                                $u = $unique ? 'c' : ($n == $nb || empty($p[1]) ? 'b' : 'h');
                                if ($gen > 0) {
                                    $this->glevel += 1;
                                                        /**
                                                        * @param Individual  $person   The Person object to draw the box for
                                                        * @param string      $earmark  Mark  First - Second - (and so on) Treeview on page
                                                        * @param int         $gen      The number of generations up or down to print
                                                        * @param int         $state    Whether we are going up or down the tree, -1 for descendents +1 for ancestors
                                                        * @param Family|null $pfamily
                                                        * @param string      $line     b, c, h, t. Required for drawing lines between boxes
                                                        * @param bool        $isRoot
                                                        */
                                    $tmark = ' dps="P_' . $p[0]->xref() . '" ';
                                    $html .= '<tr><td>' . $this->drawPerson_default($p[0], $earmark, $gen - 1, 1, $p[1], $u, false, $tmark) . '</td></tr>';      # EW.H - MOD
                                    $this->glevel -= 1;
                                } else {
                                    $snext = $this->glevel + 1;
                                    $finis = ' abbr="p' . $p[1]->xref() . '@' . $u . '" state="' . $snext . '" align="left"';
                                    $html .= '<tr><td ' . $finis . '></td></tr>';                           # EW.H - MOD
                                }
                            }
                        }
                        $html .= '</tbody></table></td>';
                    }

                }
            } else {
                $expand_div = 'exp_toLeft huhwt_button16 switchPartVisON';
                $html .= $this->drawHorizontalLine();
                $html .= $this->drawHorizontalLineV($expand_div). $this->drawVerticalLine($line);
            }
        }

        $html .= '</tr></tbody></table>';

        if ($isRoot) {
            $html .= $this->table_root_footer($earmark);
        }

        return $html;
    }

    /**
     * Draw the children for some families
     *
     * @param Collection $familyList array of families to draw the children for
     * @param string     $earmark    Mark  First - Second - (up to five) Treeview on page
     * @param int        $gen        number of generations to draw
     * @param bool       $ajax       true for an ajax call
     *
     * @return string
     */
    private function drawChildren_default(Collection $familyList, string $earmark, int $gen, int $state, bool $ajax = false): string
    {
        $html          = '';
        $children2draw = [];
        $f2load        = [];

        foreach ($familyList as $f) {
            $children = $f->children();
            if ($children->isNotEmpty()) {
                $f2load[] = $f->xref();
                foreach ($children as $child) {
                    $Cxref = 'C_' . $child->xref();
                    $isImplexC = 0;
                    if ($this->showImplex)
                        $isImplexC = $this->put_xrefsI($Cxref);
                    if (!$this->suppImplex || $isImplexC < 1) {
                        // Eliminate duplicates - e.g. when adopted by a step-parent
                        $children2draw[$child->xref()] = $child;
                    }
                }
            }
        }
        $c2d_cnt = count($children2draw);
        if ($c2d_cnt) {
            $f2load = implode(',', $f2load);
            $c2d_i  = 0;
            foreach ($children2draw as $child) {
                $c2d_i++;
                if ($c2d_cnt == 1) {
                    $co = 'c'; // unique
                } elseif ($c2d_i == 1) {
                    $co = 't'; // first
                } elseif ($c2d_i == $c2d_cnt) {
                    $co = 'b'; // last
                } else {
                    $co = 'h';
                }
                            // @param Individual  $person   The Person object to draw the box for
                            // @param string      $earmark  Mark  First - Second - (and so on) Treeview on page          # EW.H - MOD 
                            // @param int         $gen      The number of generations up or down to print
                            // @param int         $state    Whether we are going up or down the tree, -1 for descendents +1 for ancestors
                            // @param Family|null $pfamily
                            // @param string      $line     b, c, h, t. Required for drawing lines between boxes
                            // @param bool        $isRoot
                $tmark = ' dcs="C_' . $child->xref() . '" ';
                $html .= $this->drawPerson_default($child, $earmark, $gen - 1, $state, null, $co, false, $tmark);
            }
            if (!$ajax) {
                $expand_div = '';
                if ( $gen > 0 ) {
                    $finis = '';
                    $expand_div = ' TreeCollaps exp_toLeft huhwt_button16';
                } else {
                    $snext = $this->glevel;
                    $finis = ' abbr="c' . $f2load . '" state="' . $snext . '"';
                    $expand_div = ' TreeToExpand exp_toLeft huhwt_button16';
                }
                $html = '<td align="right"' . $finis . '>' . $html . '</td>' . $this->drawHorizontalLineT(expand_div: $expand_div);
                $html .= $this->drawHorizontalLine();
            }
        }

        return $html;
    }

}