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
 * Trait    ...draw_separated routines
 *
 * EW.H - MOD ... derived from webtrees/Module/InteractiveTree/Treeview.php
 */

trait TreeViewXTmod_separated {

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
     *
     * @return string
     */
    private function drawPerson_separated(Individual $person, string $earmark, int $gen, int $state,
                                          Family|null $pfamily = null, string $line, $isRoot = false, string $rcLfd): string
    {

        if ($gen < 0) {
            return '';
        }

        if ($isRoot) {
            $this->glevel = 0;
            $html   = $this->table_root_header($earmark);
        } else {
            $html = '';
        }

        $Pxref = $person->xref();
        if ($Pxref == 'I143')
            $do_dump = true;

        $isImplexI = 0;
        if ($this->showImplex) { $isImplexI = $this->put_xrefsI($Pxref); }

        $fID = ' fID="_NIX_"';
        $pf_ID = null;
        $_fID = '';
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
            $partner = $person->getCurrentSpouse();         // we use this as an indicator that there is at least 1 spouse
        }

        $_line  = $line;
        $i_rcLfd   = 0;
        $i_cpv = count($partners);
        if ($i_cpv > 0) {
            $is_cpx = ($i_cpv > 1 && $state < 0) ? true : false;    // more than 1 partner and we are handling the childrens
            $i_cp = 0;
            foreach ($partners as $Sfamily) {
                if ($is_cpx) { 
                    $i_cp++;
                }
                $this->rcLfd = $rcLfd;
                $_treeID = '';
                $_cList = 'tv_tree';
                if ($isRoot) { 
                    $i_rcLfd++;
                    $this->rcLfd  = strval($i_rcLfd);
                    $_treeID = ' id="tv' . $earmark . '_tree-' . $this->rcLfd . '"';
                    $_cList .= ' tv_tree_RC';
                }
                $_rcLfd         = ' rclfd="' . $this->rcLfd . '"';
                $SFxref = $Sfamily->xref();
                if (!$pf_ID || ($SFxref == $pf_ID)) {           // single Person or 1 explicit family

                    /* height 1% : this hack enable the div auto-dimensioning in td for FF & Chrome */
                    $html   .= '<table class="' . $_cList . '"' . $_treeID . ' style="height: 1%">'
                                . '<tbody>'
                                    . '<tr>';

                    if ($state <= 0) {
                        // draw children
                        $this->glevel -= 1;
                        $htmlC = $this->drawChildren_separated($Sfamily, $earmark, $gen, $state - 1, false, '', $this->rcLfd);
                        $html .= $htmlC;
                        $this->glevel += 1;
                    } else {
                        // draw the parent’s lines
                        $expand_div = 'exp_toRight huhwt_button16 switchPartVisON';
                        $html .= $this->drawVerticalLine($line) . $this->drawHorizontalLineV($expand_div) . $this->drawHorizontalLine();
                    }

                    // $A_glevel = ' glevel="' . $this->glevel . '"';
                    // /* draw the person. Do NOT add person or family id as an id, since a same person could appear more than once in the tree !!! */
                    // // we store the person's html for later use -> there might be more than 1 family -> we want each family separated       # EW.H - MOD
                    // // .hasBox CSS -> style width=1px   always because there might be more than 1 FAM-ID in GLEVEL which then would break the layout # EW.H - MOD
                    // $html .= '<td class="hasBox"' . $A_glevel . '>';
                    // if ($is_cpx) { $html .= '<div class="dPs">'; }
                    $html_box = $this->drawBoxTD_separated($isRoot, $SFxref, $Pxref, $state, $_rcLfd, $is_cpx);
                    // $_rootParms = $isRoot ? ' rootPerson" id="rootPerson-' . $this->rcLfd : '';
                    // $_fID = ' fID="' . $SFxref . '"';
                    // $html .= '<div class="tv_box' . ($isRoot ? $_rootParms : ' def') . '"  dir="' . I18N::direction() . '" style="text-align: ' . (I18N::direction() === 'rtl' ? 'right' : 'left') . '; direction: ' . I18N::direction() . '" abbr="' . $Pxref . '" state="' . $state . '" '. $_fID . ' onclick="' . $this->tvHandle . 'Handler.expandBox(this, event);"' . $_rcLfd . '>';
                    $html .= $html_box . $this->drawPersonName($person, '', $isImplexI);

                    $fop = []; // $fop is fathers of partners
//                    if ($partner !== null) {
                        $dashed = '';
                            // If one parent was engaged in more than one partnership, we only want to display the direct parent relationship 
                            $do_sF  = true;                         // show this family
                            if ($pf_ID)
                                if($SFxref != $pf_ID)           // ... it's not the direct parent relationship ...
                                    $do_sF = false;                         // ... don't show
                            if ($do_sF) {
                                $spouse = $Sfamily->spouse($person);
                                if ($spouse instanceof Individual) {
                                    $spouse_parents = $spouse->childFamilies()->first();
                                    if ($spouse_parents instanceof Family) {
                                        $spouse_parent = $this->test_showmatri($spouse_parents);
                                        if ($spouse_parent instanceof Individual) {
                                            $fop[] = [$spouse_parent, $spouse_parents];
                                        }
                                        // $isImplexSP = $this->put_xrefsF($spouse_parents->xref());
                                    }

                                    $isImplexP = 0;
                                    if ($this->showImplex) 
                                        $isImplexP = $this->put_xrefsI($spouse->xref());
                                    $divorced   = false;
                                    foreach ($Sfamily->facts(Gedcom::DIVORCE_EVENTS, true) as $fact) {
                                        $divorced   = true;
                                    }
                                    $_isdivorced    = $divorced ? $this->divorced_icon : '';
                                    $html .= $this->drawPersonName($spouse, $dashed, $isImplexP, $_isdivorced);
                                    $dashed = 'dashed';
                                }
                            }
//                    }
                    if ($is_cpx) { $html .= '</div>'; }

                    $_glevel = '(' . I18N::translate('Generation') . ' ' . strval($this->glevel) . ')';
                    $this->box_lfd += 1;
                    $_glevel = '[' . strval($this->box_lfd) . '] ' . '-' . $SFxref . '- ' . $_glevel;
                    $html .= '</div><div class="tv_box_glevel">' . $_glevel;

                    $html .= '</div></td>';

                    if (!$this->suppImplex || $isImplexF < 1) {
                        if ( $state >= 0) {
                            $primaryChildFamily = $person->childFamilies()->first();
                            $isImplexCF = 0;
                            if ($primaryChildFamily instanceof Family) {
                                $parent = $this->test_showmatri($primaryChildFamily);
                            } else {
                                $parent = null;
                            }
                            $expand_div = 'TreeCollaps exp_toRight huhwt_button16';
                            $html_HlineC = '';
                            $html_HlineE = '';
                            if ($parent instanceof Individual || !empty($fop)) {
                                $html_HlineC .= $this->drawHorizontalLine() . $this->drawHorizontalLineT(expand_div: $expand_div);
                                $html_HlineE .= $html_HlineC;
                                $html_HlineE = str_replace ( 'TreeCollaps', 'TreeToExpand', $html_HlineE);

                                /* draw the parents */
                                if ($state >= 0 && ($parent instanceof Individual || !empty($fop))) {
                                    $unique = $parent === null || empty($fop);
                                    $snext = $this->glevel + 1;
                                    $html_tb = '<td align="left"><table class="tv_tree"><tbody>';

                                    if ($parent instanceof Individual) {
                                        $u = $unique ? 'c' : 't';
                                        if ($gen > 0) {
                                            $html .= $html_HlineC;
                                            $html .= $html_tb;
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
                                            $htmlP = $this->drawPerson_separated($parent, $earmark, $gen - 1, 1,
                                                                                 $primaryChildFamily, $u, false, $this->rcLfd);
                                            $tL = ' tL="' . $this->glevel . '"';
                                            $this->glevel -= 1;
                                            $html .= '<tr><td class="pCF_gt"' . $tL . '>' . $htmlP . '</td></tr>';
                                            $html_tb = '';
                                            $html_HlineE = '';
                                        } else {
                                            $pCF_xref = $primaryChildFamily->xref();
                                            $isImplexCF = 0;
                                            if ($this->showImplex)
                                                $isImplexCF = $this->put_xrefsF($pCF_xref);
                                            if (!$this->suppImplex || $isImplexCF < 1) {
                                                $html .= $html_HlineE;
                                                $html .= $html_tb;
                                                $finis = ' abbr="p' . $pCF_xref . '@' . $u . '" state="' . $snext . '" align="left"' . $_rcLfd;
                                                $html .= '<tr><td class="pCF_0"' . $finis . '></td></tr>';
                                                $html_tb = '';
                                                $html_HlineE = '';
                                            }
                                        }
                                    }

                                    if (count($fop)) {
                                        $n  = 0;
                                        $nb = count($fop);
                                        foreach ($fop as $p) {
                                            $n++;
                                            $u = $unique ? 'c' : ($n == $nb || empty($p[1]) ? 'b' : 'h');
                                            $pfop_xref = $p[1]->xref();
                                            $isImplexCF = 0;
                                            if ($this->showImplex)
                                                $isImplexCF = $this->put_xrefsF($pfop_xref);
                                            if ($gen > 0) {
                                                if (!$this->suppImplex || $isImplexCF < 1) {
                                                    $html .= $html_HlineE;
                                                    $html .= $html_tb;
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
                                                    $html .= '<tr><td class="fop_gt">'
                                                        . $this->drawPerson_separated($p[0], $earmark, $gen - 1, 1,
                                                                                      $p[1], $u, false, $this->rcLfd)
                                                        . '</td></tr>';
                                                    $this->glevel -= 1;
                                                }
                                            } else {
                                                if (!$this->suppImplex || $isImplexCF < 1) {
                                                    $html .= $html_HlineE;
                                                    $html .= $html_tb;
                                                    $snext = $this->glevel + 1;
                                                    $finis = ' abbr="p' . $pfop_xref . '@' . $u . '" state="' . $snext . '" align="left"' . $_rcLfd;
                                                    $html .= '<tr><td class="fop_0"'
                                                        . $finis
                                                        . '></td></tr>';
                                                }
                                            }
                                        }
                                    }
                                    $html .= '</tbody></table></td>';
                                }
                            }
                        } else {
                            $expand_div = 'exp_toLeft huhwt_button16 switchPartVisON';
                            if ($is_cpx) { 
                                if ($line == 'b') { ($i_cp == $i_cpv) ? $_line = 'b'  : $_line = 'h'; }
                                if ($line == 'c') { ($i_cp == $i_cpv) ? $_line = 'b'  : (($i_cp == 1) ? $_line = 't' : $_line = 'h'); }
                            }
                            $html .= $this->drawHorizontalLine();
                            $html .= $this->drawHorizontalLineV($expand_div). $this->drawVerticalLine($_line);
                            $_line = 'h';
                        }
                    }

                    $html .= '</tr></tbody></table>';
                }
            }
        } else {
            // $html   .= '<table class="tv_tree"' .  ' style="height: 1%">'
            //             . '<tbody>'
            //                 . '<tr>';
            $html .= '<tr>';
            $html .= $this->drawPerson_self($person, $Pxref, $fID, $_fID, $state,
                                            $line, $isRoot, $this->rcLfd, $isImplexI);
            $html .= '</tr>';
            // $html .= '</tr></tbody></table>';
        }

        if ($isRoot) {
            $html .= '</td><td id="tv' . $earmark . '_tree_right"></td></tr><tr><td id="tv' . $earmark . '_tree_bottomleft"></td><td id="tv' . $earmark . '_tree_bottom"></td><td id="tv' . $earmark . '_tree_bottomright"></td></tr></tbody></table>';         # EW.H - MOD
        }

        return $html;
    }

    /**
     * Draw the children for some families
     *
     * @param Collection $familyList array of families to draw the children for
     * @param string     $earmark    Mark  First - Second - (up to five) Treeview on page          # EW.H - MOD 
     * @param int        $gen        number of generations to draw
     * @param bool       $ajax       true for an ajax call
     *
     * @return string
     */
    private function drawChildren_separated(Family $Cfamily, string $earmark, int $gen, int $state,
                                            bool $ajax, string $ajOps, string $rcLfd): string
    {
        $html          = '';
        $children2draw = [];
        $f2load        = [];
        $children = $Cfamily->children();
        if ($children->isNotEmpty()) {
            $f2load[] = $Cfamily->xref();
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
        $f2load     = implode(',', $f2load);
        $c2d_cnt      = count($children2draw);
        $htmlC      = '';
        $_rcLfd     = ' rclfd="' . $rcLfd . '"';

        if ($c2d_cnt && $gen > 0) {
            $tL = ' tL="' . $this->glevel . '"';
            $htmlC .= '<td align="right"' . $tL . '>'; // </td>'
                        //    . '<td>';
            $c2d_i    = 0;
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
                            // @param string      $earmark  Mark  First - Second - (and so on) Treeview on page
                            // @param int         $gen      The number of generations up or down to print
                            // @param int         $state    Whether we are going up or down the tree, -1 for descendents +1 for ancestors
                            // @param Family|null $pfamily
                            // @param string      $line     b, c, h, t. Required for drawing lines between boxes
                            // @param bool        $isRoot
                $Cxref = $child->xref();
                $htmlS = $this->drawPerson_separated($child, $earmark, $gen - 1, $state,
                                                     null, $co, false, $rcLfd);
                if ($htmlS > '') {
                    $Cxref = 'C_' . $child->xref();
                    $htmlP  = '<table class="tv_tree" dCs="' . $Cxref . '" style="height: 1%"><tbody><tr><td align="right">';
                    $htmlP  .=  $htmlS;
                    $htmlP  .=  '</td></tr></tbody></table>';
                    $htmlC .= $htmlP;
                }
            }
            $htmlC .= '</td>';
        }

        if ($ajOps > '') {
            $html = '<td align="right">' . $htmlC . '</td>';
        } else if ($ajax || (!$ajax && $c2d_cnt)) {
            $expand_div = '';
            $expand_td = '';
            if ( $gen > 0 ) {
                $finis = '';
                $expand_div = 'TreeCollaps exp_toLeft huhwt_button16' . $ajOps;
            } else {
                $snext = $this->glevel;
                // $expand_td = ' is-hidden';
                $finis = ' abbr="c' . $f2load . '" state="' . $snext . '"' . $_rcLfd; // . ' class="is-hidden"';
                $expand_div = 'TreeToExpand exp_toLeft huhwt_button16' . $ajOps;
            }
            $html = '<td align="right"' . $finis . '>' . $htmlC . '</td>' . $this->drawHorizontalLineT($expand_td,$expand_div);
            $html .= $this->drawHorizontalLine($expand_td);
        }

        return $html;
    }

    /**
     * Draw a single person in the tree
     *
     * @param Individual  $person   The Person object to draw the box for
     * @param string      $Pxref    the XREF
     * @param string      $fID      family XREF
     * @param string      $_fID     family XREF
     * @param int         $state    Whether we are going up or down the tree, -1 for descendents +1 for ancestors
     * @param string      $line     b, c, h, t. Required for drawing lines between boxes
     * @param bool        $isRoot
     * @param string      $rcLfd    position in chain
     * @param int         $isImplexI indicator for implex
     * @param string      $callback_drawBox signature of callback function 
     * @param bool        $do_hidden    -> true: render the elements primary as if in hidden state
     *
     * @return string
     */
    private function drawPerson_self(Individual $person, string $Pxref, string $fID, string $_fID, int $state,
                                     string $line, bool $isRoot, string $rcLfd, int $isImplexI)
    {
        // $A_glevel = ' glevel="' . $this->glevel . '"';
        // /* draw the person. Do NOT add person or family id as an id, since a same person could appear more than once in the tree !!! */
        // // we store the person's html for later use -> there might be more than 1 family -> we want each family separated       # EW.H - MOD
        // // .hasBox CSS -> style width=1px   always because there might be more than 1 FAM-ID in GLEVEL which then would break the layout # EW.H - MOD
        // $html = '<td class="hasBox"' . $A_glevel . '>';
        $_rcLfd     = ' rclfd="' . $this->rcLfd . '"';
    /** 
     * @param bool $isRoot
     * @param string $SFxref
     * @param string $Pxref
     * @param int $state
     * @param string $_rcLfd
     * @param bool $is_cpx
     * @param bool $do_hidden
     * 
     * @return string
     */
        $html_box = $this->drawBoxTD_separated($isRoot, '', $Pxref, $state, $_rcLfd, false);
        // $_rootParms = $isRoot ? ' rootPerson" id="rootPerson-' . $this->rcLfd : '';
        // $html .= '<div class="tv_box' . ($isRoot ? $_rootParms : ' def') . '"  dir="' . I18N::direction() . '" style="text-align: ' . (I18N::direction() === 'rtl' ? 'right' : 'left') . '; direction: ' . I18N::direction() . '" abbr="' . $Pxref . '" state="' . $state . '" '. $_fID . ' onclick="' . $this->tvHandle . 'Handler.expandBox(this, event);"' . $_rcLfd . '">';
        $html = $html_box . $this->drawPersonName($person, '', $isImplexI);

        // if ($state <> 0) {
            $this->box_lfd += 1;
            $_glevel = '[' . strval($this->box_lfd) . '] ' . '-' . $_fID . '- ' . '(' . I18N::translate('Generation') . ' ' . strval($this->glevel) . ')';
            $html .= '</div><div class="tv_box_glevel">' . $_glevel;
        // }
        $html .= '</div></td>';

        if ($state < 0) {
            $expand_div = 'exp_toLeft huhwt_button16 switchPartVisON';
            $html .= $this->drawHorizontalLine();
            $html .= $this->drawHorizontalLineV($expand_div). $this->drawVerticalLine($line);
        }

        return $html;
    }
    private function drawBoxTD_separated(bool $isRoot, string $SFxref, string $Pxref, int $state, string $_rcLfd, bool $is_cpx): string
    {
        $A_glevel = ' glevel="' . $this->glevel . '"';
        $_class_td = ' def';

        $html_tv = $this->drawBoxTV_separated($isRoot, $SFxref, $Pxref, $state, $_rcLfd, ' def');

        $html = '<td class="hasBox' . $_class_td . '"' . $A_glevel . '>';
        if ($is_cpx) { $html .= '<div class="dPs">'; }
        $html .= $html_tv;

        return $html;
    }
    private function drawBoxTV_separated(bool $isRoot, string $SFxref, string $Pxref, int $state, string $_rcLfd, string $_def): string
    {
        $_rootParms = $isRoot ? ' rootPerson" id="rootPerson-' . $this->rcLfd : '';
        $_fID = ' fID="' . $SFxref . '"';
        $html = '<div class="tv_box' . ($isRoot ? $_rootParms : $_def) . '"  dir="' . I18N::direction() . '" style="text-align: ' . (I18N::direction() === 'rtl' ? 'right' : 'left') . '; direction: ' . I18N::direction() . '" abbr="' . $Pxref . '" state="' . $state . '" '. $_fID . ' onclick="' . $this->tvHandle . 'Handler.expandBox(this, event);"' . $_rcLfd . '>';

        return $html;

    }

}