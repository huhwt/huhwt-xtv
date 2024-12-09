<?php

/**
 * HuH Extensions for webtrees - Treeview-Extended
 * Interactive Treeview with add-ons
 * Copyright (C) 2020-2024 EW.Heinrich
 */

declare(strict_types=1);

namespace HuHwt\WebtreesMods\InteractiveTreeXT\Module;

use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Gedcom;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Session;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;

use HuHwt\WebtreesMods\InteractiveTreeXT;

/**
 * Class TreeViewXTmod
 *
 * EW.H - MOD ... derived from webtrees/Module/InteractiveTree/Treeview.php
 */
class TreeViewXTmod
{
    /** @var string HTML element name */
    private $name;

    /** @var string Root module name */
    private $module;

    /** @var int option to follow matrilinear relationship */
    private $showmatri;

    /** @var int glevel-Stradonitz number */
    private $glevel;

    /** @var bool option to show Implex */
    private $showImplex;

    /** @var bool option to suppress Implex */
    private $suppImplex;

    private $CCEok;

    private Tree $tree;

    private string $root;

    private string $mode;

    private int $box_lfd;

    private string $rcLfd;

    /**
     * Treeview Constructor
     *
     * @param string $name the name of the TreeView object’s instance
     */
    public function __construct(string $name, string $module, Tree $tree, string $XREFroot, string $mode,
                                int $showmatri = 0, bool $showImplex = false, bool $suppImplex = false)
    {
        $this->tree = $tree;
        $this->root = $XREFroot;

        $this->name = $name;
        $this->showmatri = $showmatri;
        $this->showImplex = $showImplex;
        $this->suppImplex = $suppImplex;
        if( $this->suppImplex ) { $this->showImplex = true; }
        $this->module = $module;

        $this->CCEok = class_exists("HuHwt\WebtreesMods\ClippingsCartEnhanced\ClippingsCartEnhancedModule", true);

        $this->rcLfd         = '';

        if ($mode == 'separated') {
            $this->mode = 'separated';
        } else {
            $this->mode = 'default';
        }

        $this->box_lfd = 0;

    }

    public function init() : void
    {

        $_tname = $this->tree->name();
        $xrefsI = Session::get('XTVxrefsI', []);
        $xrefsI[$_tname] = [];
        $xrefsI[$_tname][$this->root] = [];
        $xrefsI[$_tname][$this->root]['_showImplex_'] = $this->showImplex;
        $xrefsI[$_tname][$this->root]['_suppImplex_'] = $this->suppImplex;
        Session::put('XTVxrefsI', $xrefsI);

        $xrefsF = Session::get('XTVxrefsF', []);
        $xrefsF[$_tname] = [];
        $xrefsF[$_tname][$this->root] = [];
        $xrefsF[$_tname][$this->root]['_box_lfd_'] = $this->box_lfd;
        Session::put('XTVxrefsF', $xrefsF);
    }

    private function put_xrefsI(string $xref): int
    {
        $xrefsI = Session::get('XTVxrefsI');
        $xrefsI = is_array($xrefsI) ? $xrefsI : [];

        $_tree = $this->tree->name();
        $_root = $this->root;

        if (($xrefsI[$_tree][$_root][$xref] ?? '_NIX_') === '_NIX_') {
            $xrefsI[$_tree][$_root][$xref] = 0;
        } else {
            $xrefsI[$_tree][$_root][$xref] += 1;
        }
        Session::put('XTVxrefsI', $xrefsI);
        return intval($xrefsI[$_tree][$_root][$xref]);
    }

    private function test_xrefsI(string $xref): int
    {
        $xrefsI = Session::get('XTVxrefsI');

        $_tree = $this->tree->name();
        $_root = $this->root;

        if (($xrefsI[$_tree][$_root][$xref] ?? '_NIX_') === '_NIX_') {
            return 0;
        } else {
            return intval($xrefsI[$_tree][$_root][$xref]);
        }
    }

    private function put_xrefsF(string $xref): int
    {
        $xrefsF = Session::get('XTVxrefsF');
        $xrefsF = is_array($xrefsF) ? $xrefsF : [];

        $_tree = $this->tree->name();
        $_root = $this->root;

        if (($xrefsF[$_tree][$_root][$xref] ?? '_NIX_') === '_NIX_') {
            $xrefsF[$_tree][$_root][$xref] = 0;
        } else {
            $xrefsF[$_tree][$_root][$xref] += 1;
        }
        $xrefsF[$_tree][$_root]['_box_lfd_'] = $this->box_lfd;
        Session::put('XTVxrefsF', $xrefsF);
        return intval($xrefsF[$_tree][$_root][$xref]);
    }

    private function put_xrefsF_bl(): void
    {
        $xrefsF = Session::get('XTVxrefsF');
        $xrefsF = is_array($xrefsF) ? $xrefsF : [];

        $_tree = $this->tree->name();
        $_root = $this->root;

        $xrefsF[$_tree][$_root]['_box_lfd_'] = $this->box_lfd;
        Session::put('XTVxrefsF', $xrefsF);
    }

    private function test_xrefsF(string $xref): int
    {
        $xrefsF = Session::get('XTVxrefsF');

        $_tree = $this->tree->name();
        $_root = $this->root;

        if (($xrefsF[$_tree][$_root][$xref] ?? '_NIX_') === '_NIX_') {
            return -1;
        } else {
            return intval($xrefsF[$_tree][$_root][$xref]);
        }
    }

    public function reload(): void
    {
        $xrefsI = Session::get('XTVxrefsI');
        $xrefsI = is_array($xrefsI) ? $xrefsI : [];

        $xrefsF = Session::get('XTVxrefsF');
        $xrefsF = is_array($xrefsF) ? $xrefsF : [];

        $_tree = $this->tree->name();
        $_root = $this->root;

        $showImplex = ($xrefsI[$_tree][$_root]['_showImplex_'] ?? false);
        $this->showImplex = $showImplex;

        $suppImplex = ($xrefsI[$_tree][$_root]['_suppImplex_'] ?? false);
        $this->suppImplex = $suppImplex;

        $box_lfd = ($xrefsF[$_tree][$_root]['_box_lfd_'] ?? 0);
        $this->box_lfd = $box_lfd;
    }

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
    /**
     * EW.H - MOD ... Modified view and Handler 
     * 
     * Draw the viewport which creates the draggable/zoomable framework
     * Size is set by the container, as the viewport can scale itself automatically
     *
     * @param Individual $individual  Draw the chart for this individual
     * @param int        $generations number of generations to draw
     *
     * @return string[]  HTML and Javascript
     */
    public function drawViewport(Individual $individual, string $earmark, int $generations, bool $doExpand = false): array
    {
        $_name = trim($this->name);
        $_root = $individual->xref();
        $_tree = $individual->tree();

        if ($this->mode == 'separated') {
            // @param Individual  $person   The Person object to draw the box for
            // @param string      $earmark  Mark  First - Second - (and so on) Treeview on page          # EW.H - MOD 
            // @param int         $gen      The number of generations up or down to print
            // @param int         $state    Whether we are going up or down the tree, -1 for descendents +1 for ancestors
            // @param Family|null $pfamily
            // @param string      $line     b, c, h, t. Required for drawing lines between boxes
            // @param bool        $isRoot
            $innerHTML = $this->drawPerson_separated($individual, $earmark, $generations, 0, null, '', true);
        } else {
            $innerHTML = $this->drawPerson_default($individual, $earmark, $generations, 0, null, '', true);
        }

        if (!$this->suppImplex)
            $this->put_xrefsF_bl();

        $dump_dir = Session::get('XTV_dumpDir');
        if(is_dir($dump_dir)){
            $this->dump_file($individual, $innerHTML);
        }

        $showseparated = ( $this->mode == 'separated' ? '1' : '0' );

        $html = view('modules/treeviewXT/chart', [
            'module'     => $this->module,                  // EW.H - MOD ... put own Module here!
            'name'       => $_name,
            'earmark'    => $earmark,
            'XREFroot'   => $_root,
            'innerHTML'  => $innerHTML,
            'tree'       => $_tree,
            'withCCE'    => $this->CCEok,
            'showseparated' => $showseparated,
        ]);
        $_minTitle = I18N::translate('Minimize View');
        $_maxTitle = I18N::translate('Maximize View');
        $_doExpand = ( $doExpand ? 'true' : 'false' );
        return [
            $html,
            'var ' . $this->name . 'Handler = new TreeViewHandlerXT("' . $this->name  .'",'. $_doExpand . ', "' . $_minTitle . '", "' . $_maxTitle . '");',
        ];
    }

    /**
     * Return a JSON structure to a JSON request
     *
     * @param Tree   $tree
     * @param string $request list of JSON requests
     *
     * @return string
     */
    public function getIndividuals(Tree $tree, string $earmark, string $request): string
    {
        $json_requests = explode(';', $request);
        $r    = [];
        foreach ($json_requests as $json_request) {
            $firstLetter = substr($json_request, 0, 1);
            $json_request = substr($json_request, 1);

            $jr_p = explode('|', $json_request);
            $json_r0 = $jr_p[0];
            $_state = $jr_p[1];
            if (count($jr_p) > 2) { $_rcLfd = $jr_p[2]; }
            $state = intval($_state);
            $this->glevel = $state;
            switch ($firstLetter) {             // Indicator
                case 'c':                           // we want the children
                    $families = Collection::make(explode(',', $json_r0))
                        ->map(static function (string $xref) use ($tree): ?Family {
                            return Registry::familyFactory()->make($xref, $tree);
                        })
                        ->filter();

                    if ($this->mode == 'separated') {
                        foreach ($families as $f) {
                            if (!$_rcLfd) { $_rcLfd = ''; }
                            $r[] = $this->drawChildren_separated($f, $earmark, 1, $state, true, ' getI', $_rcLfd);
                        }
                    } else
                        $r[] = $this->drawChildren_default($families, $earmark, 1, $state, true);
                    break;

                case 'p':                           // we want the parents
                    [$xref, $order] = explode('@', $json_r0);

                    $family = Registry::familyFactory()->make($xref, $tree);
                    if ($family instanceof Family) {
                        $parent = $this->test_showmatri($family);

                        // The family may have no parents (just children).
                        if ($parent instanceof Individual) {
                            if ($this->mode == 'separated') {
                                if (!$_rcLfd) { $_rcLfd = ''; }
                                $r[] = $this->drawPerson_separated($parent, $earmark, 0, $state, $family, $order, false, $_rcLfd);
                            } else
                                $r[] = $this->drawPerson_default($parent, $earmark, 0, $state, $family, $order, false);
                        }
                    }
                    break;
            }
        }

        $this->put_xrefsF_bl();

        return json_encode($r);
    }

    /**
     * Get the details for a person and their life partner(s)
     *
     * @param Individual $individual the individual to return the details for
     *
     * @return string
     */
    public function getDetails(Individual $individual): string
    {
        $html = $this->getPersonDetails($individual, null);
        foreach ($individual->spouseFamilies() as $family) {
            $spouse = $family->spouse($individual);
            if ($spouse) {
                $html .= $this->getPersonDetails($spouse, $family);
            }
        }

        return $html;
    }

    /**
     * Return the details for a person
     *
     * @param Individual  $individual
     * @param Family|null $family
     *
     * @return string
     */
    private function getPersonDetails(Individual $individual, Family $family = null): string
    {
        $chart_url = route('module', [
            'module' => $this->module,
            'action' => 'Chart',
            'xref'   => $individual->xref(),
            'tree'   => $individual->tree()->name(),
        ]);

        $html = $this->getThumbnail($individual);
        $icon_indi = ($individual->sex() == 'F' ? 'huhwt-iconF' : 'huhwt-iconM');
        $html .= '<a class="tv_link" href="' . e($individual->url()) . '">' . $individual->fullName() . '</a>';
        $html .= '<a href="' . e($chart_url) . '" title="' . I18N::translate('Interactive tree of %s', strip_tags($individual->fullName())) . '" class="' . $icon_indi . ' tv_link tv_treelink" ></a>';
        foreach ($individual->facts(Gedcom::BIRTH_EVENTS, true) as $fact) {
            $html .= $fact->summary();
        }
        if ($family) {
            foreach ($family->facts(Gedcom::MARRIAGE_EVENTS, true) as $fact) {
                $html .= $fact->summary();
            }
        }
        foreach ($individual->facts(Gedcom::DEATH_EVENTS, true) as $fact) {
            $html .= $fact->summary();
        }

        $xref = $individual->xref();
        $_name = trim($this->name);
        $pIDdom = ''.join('', [' name="', $_name , 'xref', $xref , '"']);
        $html_div = 'class="tv' . $individual->sex() . ' tv_person_expanded"' . $pIDdom;
        return '<div ' . $html_div . '">' . $html . '</div>';
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
                $html .= $this->drawPerson_default($child, $earmark, $gen - 1, $state, null, $co, false);           // EW.H - MOD ... added parm 'earmark'
            }
            if (!$ajax) {
                $expandit = '';
                if ( $gen > 0 ) {
                    $finis = '';
                    $expandit = 'TreeCollaps exp_toLeft  huhwt_button16';
                } else {
                    $snext = $this->glevel;
                    $finis = ' abbr="c' . $f2load . '" state="' . $snext . '"';
                    $expandit = 'TreeToExpand exp_toLeft  huhwt_button16';
                }
                $html = '<td align="right"' . $finis . '>' . $html . '</td>' . $this->drawHorizontalLineT($expandit);
                $html .= $this->drawHorizontalLine();
            }
        }

        return $html;
    }

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
    private function drawPerson_default(Individual $person, string $earmark, int $gen, int $state, Family $pfamily = null, string $line = '', $isRoot = false): string
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
        } else {
            $html = '';
        }

        $Pxref = $person->xref();
        $C_Xref = '';
        if ($Pxref == 'I111') {
                $C_Xref = $Pxref . '';
        }
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
                $isImplexF = $this->put_xrefsF($pf_ID);
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
        $html .= '<table class="tv_tree"' . ($isRoot ? ' id="tv' . $earmark . '_tree"' : '') . ' style="height: 1%"><tbody><tr>';

        if ($state <= 0) {
            // draw children
            $this->glevel -= 1;
            $html .= $this->drawChildren_default($person->spouseFamilies(), $earmark, $gen, $state - 1);        # EW.H - MOD
            $this->glevel += 1;
        } else {
            // draw the parent’s lines
            $expandit = 'switchPartVisON exp_toRight huhwt_button16';
            $html .= $this->drawVerticalLine($line) . $this->drawHorizontalLineV($expandit) . $this->drawHorizontalLine();
        }

        /* draw the person. Do NOT add person or family id as an id, since a same person could appear more than once in the tree !!! */
        // we store the person's html for later use -> there might be more than 1 family -> we want each family separated       # EW.H - MOD
        $html .= '<td class="hasBox" >';    // .hasBox CSS -> style width=1px   always because there might be more than 1 FAM-ID in GLEVEL which then would break the layout # EW.H - MOD
        $html .= '<div class="tv_box' . ($isRoot ? ' rootPerson' : ' def') . '" dir="' . I18N::direction() . '" style="text-align: ' . (I18N::direction() === 'rtl' ? 'right' : 'left') . '; direction: ' . I18N::direction() . '" abbr="' . $Pxref . '" state="' . $state . '" '. $fID . ' onclick="' . $this->name . 'Handler.expandBox(this, event);">';
        $html .= $this->drawPersonName($person, '', $isImplexI);

        $fop = []; // $fop is fathers of partners

        if ($partner !== null) {
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
                            $isImplexP = $this->put_xrefsI($spouse->xref());
                        $html .= $this->drawPersonName($spouse, $dashed, $isImplexP);
                        $dashed = 'dashed';
                    }
                }
            }
        }

        $_glevel = '(' . I18N::translate('Generation') . ' ' . strval($this->glevel) . ')';
        $this->box_lfd += 1;
        $_glevel = '[' . strval($this->box_lfd) . '] ' . '-' . $_fID . '- ' . $_glevel;
        $html .= '</div><div class="tv_box_glevel">' . $_glevel;

        $html .= '</div></td>';

        $primaryChildFamily = $person->childFamilies()->first();
        if ($primaryChildFamily instanceof Family) {
            $parent = $this->test_showmatri($primaryChildFamily);
            if ($this->showImplex) 
                $isImplexF = $this->test_xrefsF($primaryChildFamily->xref());
        } else {
            $parent = null;
        }

        $expandit = 'TreeCollaps exp_toRight huhwt_button16';
        $html_HlineC = '';
        $html_HlineE = '';
        if ($parent instanceof Individual || !empty($fop)) {    // || $state < 0) {
            // if ($state < 0 || $isImplexF > 0) {$expandit = '';}
            if ($this->suppImplex && $isImplexF > 0) {$expandit = '';}
            if ($expandit)
                $html_HlineC .= $this->drawHorizontalLine() . $this->drawHorizontalLineT($expandit);
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
                            $html .= $this->drawPerson_default($parent, $earmark, $gen - 1, 1, $primaryChildFamily, $u, false);
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
                                $html .= '<tr><td>' . $this->drawPerson_default($p[0], $earmark, $gen - 1, 1, $p[1], $u, false) . '</td></tr>';      # EW.H - MOD
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

        if ($state < 0) {
            $html .= $this->drawHorizontalLine();
            $expandit = 'switchPartVisON exp_toLeft  huhwt_button16';
            $html .= $this->drawHorizontalLineV($expandit). $this->drawVerticalLine($line);
        }

        $html .= '</tr></tbody></table>';

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
    private function drawChildren_separated(Family $Cfamily, string $earmark, int $gen, int $state, bool $ajax = false, string $ajOps = '', string $rcLfd = ''): string
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
                $htmlS = $this->drawPerson_separated($child, $earmark, $gen - 1, $state, null, $co, false, $rcLfd);
                if ($htmlS > '') {
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
            $expandit = '';
            if ( $gen > 0 ) {
                $finis = '';
                $expandit = 'TreeCollaps exp_toLeft  huhwt_button16' . $ajOps;
            } else {
                $snext = $this->glevel;
                $finis = ' abbr="c' . $f2load . '" state="' . $snext . '"' . $_rcLfd;
                ;
                $expandit = 'TreeToExpand exp_toLeft  huhwt_button16' . $ajOps;
            }
            $html = '<td align="right"' . $finis . '>' . $htmlC . '</td>' . $this->drawHorizontalLineT($expandit);
            $html .= $this->drawHorizontalLine();
        }

        return $html;
    }


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
    private function drawPerson_separated(Individual $person, string $earmark, int $gen, int $state, Family $pfamily = null, string $line = '', $isRoot = false, string $rcLfd = ''): string
    {

        if ($gen < 0) {
            return '';
        }

        if ($isRoot) {
            $this->glevel = 0;
            $html   = '<table id="tv' . $earmark . 'TreeBorder" class="tv_tree">'
                        . '<tbody>'
                            . '<tr>'
                                . '<td id="tv' . $earmark . '_tree_topleft"></td>'
                                . '<td id="tv' . $earmark . '_tree_top"></td>'
                                . '<td id="tv' . $earmark . '_tree_topright"></td>'
                            . '</tr>'
                            . '<tr>'
                                . '<td id="tv' . $earmark . '_tree_left"></td>'
                                . '<td align="left">';
        } else {
            $html = '';
        }

        $Pxref = $person->xref();
        $isImplexI = 0;
        if ($this->showImplex)
            $isImplexI = $this->put_xrefsI($Pxref);
        if ($Pxref == 'I111')
            $T_Xref = $Pxref . '';

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
                $isImplexF = $this->put_xrefsF($pf_ID);
        } else {
            $partner = $person->getCurrentSpouse();         // we use this as an indicator that there is at least 1 spouse
        }

        $_line  = $line;
        $i_rcLfd   = 0;
        $i_cpv = count($partners);
        if ($i_cpv > 0) {
            $is_cpx = ($i_cpv > 1 && $state < 0) ? true : false;
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
                if ($SFxref == 'F500005')
                    $SFxref = $SFxref . '';
                if (!$pf_ID || ($SFxref == $pf_ID)) {

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
                        $expandit = 'switchPartVisON exp_toRight huhwt_button16';
                        $html .= $this->drawVerticalLine($line) . $this->drawHorizontalLineV($expandit) . $this->drawHorizontalLine();
                    }

                    $A_glevel = ' glevel="' . $this->glevel . '"';
                    /* draw the person. Do NOT add person or family id as an id, since a same person could appear more than once in the tree !!! */
                    // we store the person's html for later use -> there might be more than 1 family -> we want each family separated       # EW.H - MOD
                    // .hasBox CSS -> style width=1px   always because there might be more than 1 FAM-ID in GLEVEL which then would break the layout # EW.H - MOD
                    $html .= '<td class="hasBox"' . $A_glevel . '>';
                    if ($is_cpx) { $html .= '<div class="dPs">'; }
                    $_rootParms = $isRoot ? ' rootPerson" id="rootPerson-' . $this->rcLfd : '';
                    $_fID = ' fID="' . $SFxref . '"';
                    $html .= '<div class="tv_box' . ($isRoot ? $_rootParms : ' def') . '"  dir="' . I18N::direction() . '" style="text-align: ' . (I18N::direction() === 'rtl' ? 'right' : 'left') . '; direction: ' . I18N::direction() . '" abbr="' . $Pxref . '" state="' . $state . '" '. $_fID . ' onclick="' . $this->name . 'Handler.expandBox(this, event);"' . $_rcLfd . '>';
                    $html .= $this->drawPersonName($person, '', $isImplexI);

                    $fop = []; // $fop is fathers of partners
                    if ($partner !== null) {
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
                                    $html .= $this->drawPersonName($spouse, $dashed, $isImplexP);
                                    $dashed = 'dashed';
                                }
                            }
                    }
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
                            $expandit = 'TreeCollaps exp_toRight huhwt_button16';
                            $html_HlineC = '';
                            $html_HlineE = '';
                            if ($parent instanceof Individual || !empty($fop)) {
                                $html_HlineC .= $this->drawHorizontalLine() . $this->drawHorizontalLineT($expandit);
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
                                            $htmlP = $this->drawPerson_separated($parent, $earmark, $gen - 1, 1, $primaryChildFamily, $u, false, $this->rcLfd);
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
                                                        . $this->drawPerson_separated($p[0], $earmark, $gen - 1, 1, $p[1], $u, false, $this->rcLfd)
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
                            // $html .= $this->drawHorizontalLine();
                            // $html .= $this->drawVerticalLine($line);
                            $html .= $this->drawHorizontalLine();
                            $expandit = 'switchPartVisON exp_toLeft  huhwt_button16';
                            if ($is_cpx) { 
                                if ($line == 'b') { ($i_cp == $i_cpv) ? $_line = 'b'  : $_line = 'h'; }
                            }
                            $html .= $this->drawHorizontalLineV($expandit). $this->drawVerticalLine($_line);
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
            $html .= $this->drawPerson_self($person, $Pxref, $fID, $_fID, $state, $line, $isRoot, $this->rcLfd, $isImplexI);
            $html .= '</tr>';
            // $html .= '</tr></tbody></table>';
        }

        if ($isRoot) {
            $html .= '</td><td id="tv' . $earmark . '_tree_right"></td></tr><tr><td id="tv' . $earmark . '_tree_bottomleft"></td><td id="tv' . $earmark . '_tree_bottom"></td><td id="tv' . $earmark . '_tree_bottomright"></td></tr></tbody></table>';         # EW.H - MOD
        }

        return $html;
    }

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
    private function drawPerson_self(Individual $person, string $Pxref, string $fID, string $_fID, int $state, string $line, bool $isRoot, string $rcLfd, int $isImplexI)
    {

        $A_glevel = ' glevel="' . $this->glevel . '"';
        /* draw the person. Do NOT add person or family id as an id, since a same person could appear more than once in the tree !!! */
        // we store the person's html for later use -> there might be more than 1 family -> we want each family separated       # EW.H - MOD
        // .hasBox CSS -> style width=1px   always because there might be more than 1 FAM-ID in GLEVEL which then would break the layout # EW.H - MOD
        $html = '<td class="hasBox"' . $A_glevel . '>';
        $_rootParms = $isRoot ? ' rootPerson" id="rootPerson-' . $this->rcLfd : '';
        $_rcLfd     = ' rclfd="' . $this->rcLfd . '"';
        $html .= '<div class="tv_box' . ($isRoot ? $_rootParms : ' def') . '"  dir="' . I18N::direction() . '" style="text-align: ' . (I18N::direction() === 'rtl' ? 'right' : 'left') . '; direction: ' . I18N::direction() . '" abbr="' . $Pxref . '" state="' . $state . '" '. $_fID . ' onclick="' . $this->name . 'Handler.expandBox(this, event);"' . $_rcLfd . '">';
        $html .= $this->drawPersonName($person, '', $isImplexI);

        // if ($state <> 0) {
            $this->box_lfd += 1;
            $_glevel = '[' . strval($this->box_lfd) . '] ' . '-' . $_fID . '- ' . '(' . I18N::translate('Generation') . ' ' . strval($this->glevel) . ')';
            $html .= '</div><div class="tv_box_glevel">' . $_glevel;
        // }
        $html .= '</div></td>';

        if ($state < 0) {
            $html .= $this->drawHorizontalLine();
            $expandit = 'switchPartVisON exp_toLeft  huhwt_button16';
            $html .= $this->drawHorizontalLineV($expandit). $this->drawVerticalLine($line);
        }

        return $html;
    }

    /**
     * Draw a person name preceded by sex icon, with parents as tooltip
     *
     * @param Individual $individual The individual to draw
     * @param string     $dashed     Either "dashed", to print dashed top border to separate multiple spouses, or ""
     *
     * @return string
     */
    private function drawPersonName(Individual $individual, string $dashed, int $isImplexI): string
    {
        $family = $individual->childFamilies()->first();
        if ($family) {
            $family_name = strip_tags($family->fullName());
        } else {
            $family_name = I18N::translateContext('unknown family', 'unknown');
        }
        switch ($individual->sex()) {
            case 'M':
                /* I18N: e.g. “Son of [father name & mother name]” */
                $title_0 = I18N::translate('Son of %s', $family_name);
                break;
            case 'F':
                /* I18N: e.g. “Daughter of [father name & mother name]” */
                $title_0 = I18N::translate('Daughter of %s', $family_name);
                break;
            default:
                /* I18N: e.g. “Child of [father name & mother name]” */
                $title_0 = I18N::translate('Child of %s', $family_name);
                break;
        }
        $sex = $individual->sex();

        $xref = $individual->xref();
        $pID = ' pID="' . $xref . '" ';    // EW.H - MOD ... we want the xref anyway
        $pIDdom = ' name="' . $this->name . 'xref' . $xref . '" ';

        $s_Implex = '';
        $dbolded = '';
        if ($isImplexI > 0) {
            $s_Implex = '<span> ->' . json_decode('"\u210D"') . '<- </span>';
            $title_0 .= ' - ' . I18N::translate('Implex detected');
            $dbolded = ' dbolded';
        }
        $title = ' title="' . $title_0 . '"';

        return '<div class="tv' . $sex . ' tv_Person ' . $dashed . $dbolded . '"' . $title . $pID . $pIDdom .'><a href="' . e($individual->url()) . '"></a>' . $individual->fullName() . ' <span class="dates">' . $individual->lifespan() . '</span>' . $s_Implex . '</div>';
    }

    /**
     * Test maternal/paternal precedence
     * 
     * @param Family $theFamily
     * 
     * @return Individual
     */
    private function test_showmatri(Family $theFamily): Individual
    {
        if ($this->showmatri == 1) {
            $parent = $theFamily->wife() ?? $theFamily->husband();
        } else {
            $parent = $theFamily->husband() ?? $theFamily->wife();
        }
        return $parent;
    }

    /**
     * Get the thumbnail image for the given person
     *
     * @param Individual $individual
     *
     * @return string
     */
    private function getThumbnail(Individual $individual): string
    {
        if ($individual->tree()->getPreference('SHOW_HIGHLIGHT_IMAGES')) {
            return $individual->displayImage(40, 50, 'crop', []);
        }

        return '';
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
    private function drawHorizontalLine(): string
    {
        return '<td class="tv_hline"><div class="tv_hline"></div></td>';
    }

    /**
     * Draw a horizontal line - eventually including Expander
     */
    private function drawHorizontalLineT($expand = ''): string
    {
        $html_td = '<td class="tv_hline">';
        $html_td .= '<div class="' . $expand . '" title="' . I18N::translate('Click here to expand/collapse subtree') . '" onclick="' . $this->name . 'Handler.expandTree(this, event);"></div>';
        $html_td .= '</td>';
        return $html_td;
    }

    /**
     * Draw a horizontal line - including Visibility-Switch
     */
    private function drawHorizontalLineV($expand): string
    {
        $html_td = '<td class="tv_hline">';
        $html_td .= '<div class="' . $expand . '" title="' . I18N::translate('Click here to hide/show partial subtree - Ctrl-Click -> collapse/expand') . '" onclick="' . $this->name . 'Handler.switchVis(this, event);"></div>';
        $html_td .= '</td>';
        return $html_td;
    }
}
