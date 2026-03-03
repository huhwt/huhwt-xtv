<?php

/**
 * HuH Extensions for webtrees - Treeview-Extended
 * Interactive Treeview with add-ons
 * Copyright (C) 2020-2025 EW.Heinrich
 */

declare(strict_types=1);

namespace HuHwt\WebtreesMods\InteractiveTreeXT\Module;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\FlashMessages;
use Fisharebest\Webtrees\Gedcom;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Session;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;

use HuHwt\WebtreesMods\InteractiveTreeXT\Module\TreeViewXTmod_drawX;
use HuHwt\WebtreesMods\InteractiveTreeXT\Module\TreeViewXTmod_default;
use HuHwt\WebtreesMods\InteractiveTreeXT\Module\TreeViewXTmod_separated;
use HuHwt\WebtreesMods\InteractiveTreeXT\Module\TreeViewXTmod_xtR;
use HuHwt\WebtreesMods\InteractiveTreeXT\Module\TreeViewXTmod_dump;

/**
 * Class TreeViewXTmod
 *
 * EW.H - MOD ... derived from webtrees/Module/InteractiveTree/Treeview.php
 */
class TreeViewXTmod
{

    use TreeViewXTmod_drawX;
    use TreeViewXTmod_default;
    use TreeViewXTmod_separated;
    use TreeViewXTmod_xtR;
    use TreeViewXTmod_dump;

    /** @var string HTML element name */
    private $name;

    /** @var bool Module is called from Diagramms Menu */
    private $isChart;

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

    /** @var bool option to mark Deceased */
    private $markDeceased;

    /**
     * CCE installed?
     * @var bool
     */
    private $CCEok;

    private Tree $tree;

    private string $root;

    private string $mode;

    private int $box_lfd;

    private string $rcLfd;

    private string $isDead_icon;

    private string $divorced_icon;

    /**
     * _vesta_extended_relationships_ installed?
     * @var bool
     */
    private bool $vERok = false;

    private $xtRdata;
    private $xtR_parts;

    private bool $do_xtR = false;

    private string $xtR_Ifrom;
    private string $xtR_Ito;

    private array $xtRinfo;

    private array $xtR_INDIs;

    private bool $xtR_header_done = false;

    private string $tvMark = 'tvXTr';

    private string $tvHandle = 'tvXT';

    /**
     * Treeview Constructor
     *
     * @param string $name the name of the TreeView object’s instance
     */
    public function __construct(string $name, string $module, Tree $tree, string $XREFroot, string $mode,
                                int $showmatri = 0, bool $markdeceased = false, bool $showImplex = false, bool $suppImplex = false
                                )
    {
        $this->tree = $tree;
        $this->root = $XREFroot;

        $this->name         = $name;
        $this->showmatri    = $showmatri;
        $this->showImplex   = $showImplex;
        $this->suppImplex   = $suppImplex;
        $this->markDeceased = $markdeceased;
        if( $this->suppImplex ) { $this->showImplex = true; }
        $this->module       = $module;

        $this->CCEok        = class_exists("HuHwt\WebtreesMods\ClippingsCartEnhanced\ClippingsCartEnhancedModule", true);
        $this->vERok        = false;

        $this->rcLfd        = '';

        if ($mode == 'separated') {
            $this->mode = 'separated';
        } else {
            $this->mode = 'default';
        }

        $this->isChart = str_ends_with($name,'C');

        $this->box_lfd = 0;

        $this->isDead_icon = ' <span class="tvbisdead" title="' . I18N::translate("deceased") . '">&#x1F547;</span>';

        $this->divorced_icon = ' <span class="tvbisdivorced" title="' . I18N::translate("divorced") . '">&#x26AE;</span>';
    }

    public function init() : void
    {

        $_tname = $this->tree->name();
        $xrefsI = Session::get('XTVxrefsI', []);
        $xrefsI[$_tname] = [];
        $xrefsI[$_tname][$this->root] = [];
        $xrefsI[$_tname][$this->root]['_showImplex_']   = $this->showImplex;
        $xrefsI[$_tname][$this->root]['_suppImplex_']   = $this->suppImplex;
        $xrefsI[$_tname][$this->root]['_markDeceased_'] = $this->markDeceased;
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

        $markDeceased = ($xrefsI[$_tree][$_root]['_markDeceased_'] ?? false);
        $this->markDeceased = $markDeceased;

        $box_lfd = ($xrefsF[$_tree][$_root]['_box_lfd_'] ?? 0);
        $this->box_lfd = $box_lfd;
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
    public function drawViewport(Individual $individual, string $earmark, int $generations, bool $doExpand): array
    {
        $_name          = 'tv' . $earmark . 'C';
        $this->name     = $_name;
        $this->tvMark   = $_name;

        $_root = $individual->xref();
        $_tree = $individual->tree();

        $partners = $individual->spouseFamilies();
        $i_cpv = count($partners);
        if ($this->mode == 'separated' && $i_cpv == 0) { 
            $this->mode = 'default';
            FlashMessages::addMessage("'". I18N::translate("Show separated") . "' " . I18N::translate("not executed - %s has no family/descendants by now.", $individual->xref()), 'info');
            $doExpand = false;
        }

        if ($this->mode == 'separated') {
            // @param Individual  $person   The Person object to draw the box for
            // @param string      $earmark  Mark  First - Second - (and so on) Treeview on page          # EW.H - MOD 
            // @param int         $gen      The number of generations up or down to print
            // @param int         $state    Whether we are going up or down the tree, -1 for descendents +1 for ancestors
            // @param Family|null $pfamily
            // @param string      $line     b, c, h, t. Required for drawing lines between boxes
            // @param bool        $isRoot
            $innerHTML = $this->drawPerson_separated($individual, $earmark, $generations, 0, null, '', true, '');
        } else {
            $innerHTML = $this->drawPerson_default($individual, $earmark, $generations, 0, null, '', true);
        }

        if (!$this->suppImplex)
            $this->put_xrefsF_bl();

        $dump_dir = Session::get('XTV_dumpDir','');
        if(is_dir($dump_dir)){
            $this->dump_file($individual, $innerHTML);
        }

        $markdeceased   = $this->markDeceased;
        $showseparated  = $this->mode == 'separated' ? '1' : '0';

        $_minTitle = I18N::translate('Minimize View');
        $_maxTitle = I18N::translate('Maximize View');
        $_bseTitle = $doExpand ? $_minTitle : $_maxTitle;
        $pmaphide  = $doExpand ? '1' : '0';
        $xtRmode   = $this->xtRdata ? '1' : '0';

        $html = view('modules/treeviewXT/chart', [
            'module'    => $this->module,
            'name'      => $_name,
            'isChart'   => $this->isChart,
            'earmark'   => $earmark,
            'XREFroot'  => $_root,
            'innerHTML' => $innerHTML,
            'tree'      => $_tree,
            'withCCE'   => $this->CCEok,
            'markdeceased'  => (bool) $markdeceased,
            'showseparated' => $showseparated,
            'bseTitle'  => $_bseTitle,
            'pmaphide'  => $pmaphide,
            'xtRmode'   => (bool) $xtRmode,
            'xtv_in'    => 'xtv_in',
        ]);
        $_doExpand = $doExpand ? 'true' : 'false';
        return [
            $html,
            'var ' . $this->tvHandle . 'Handler = new TreeViewHandlerXT("' . $this->name  .'",'. $_doExpand . ', "' . $_minTitle . '", "' . $_maxTitle . '", "' . $markdeceased  . '");',
        ];
    }

    public function drawViewport_xtR(Individual $individual, Individual $individual2, array $XT_struct, bool $doExpand): array {
        $_name          = 'tvXTr';
        $this->name     = $_name;
        $this->tvMark   = $_name;
        $_root          = $individual->xref();
        $_tree          = $individual->tree();
        $this->mode     = 'separated';
        $this->suppImplex = true;   $this->showImplex = true; 

        $d_Ifrom        = $individual->fullName() . ', ' . $individual->lifespan();
        $d_Ito          = $individual2->fullName() . ', ' . $individual2->lifespan();
        $d_Ifrom_to     = '<div class="xtv_header mx-auto">' . $d_Ifrom . ' -> ' . $d_Ito . '</div>';

        $html_parts     = $this->table_root_xtR_header($_name, $d_Ifrom_to);

        foreach ($XT_struct['parts'] as $n_p => $part) {
            $p_header = '';
            if (array_key_exists('label', $part)) {
                $label      = $part['label'];
                $h_part     = I18N::translate('Path') . ' ' . $label;
                $p_header   = '<div class="xtv_part"><div class="mx-auto">' . $h_part .'</div></div>';
            }
            $html_part   = $this->table_root_xtR_nextpart($n_p, $p_header);
            $html_part  .= $this->drawViewport_xtR_part($individual, $n_p, $doExpand);
            $html_part  .= $this->table_root_xtR_nextpart_E($n_p);
            $html_parts .= $html_part;
        }
        $html_parts .= $this->table_root_xtR_footer($_name);

        $dump_dir = Session::get('XTV_dumpDir');
        if( $dump_dir && is_dir($dump_dir)){
            $this->dump_file($individual, $html_parts);
        }

        $markdeceased   = $this->markDeceased;
        $showseparated  = $this->mode == 'separated' ? '1' : '0';

        $_minTitle      = I18N::translate('Minimize View');
        $_maxTitle      = I18N::translate('Maximize View');
        $_bseTitle      = $doExpand ? $_minTitle : $_maxTitle;
        $pmaphide       = $doExpand ? '1' : '0';
        $xtRmode        = $this->xtRdata ? '1' : '0';
        $_doExpand      = $doExpand ? 'true' : 'false';

        $html = view('modules/treeviewXT/chart_xtR', [
            'module'    => $this->module,
            'name'      => $_name,
            'isChart'   => $this->isChart,
            'earmark'   => $_name,
            'XREFroot'  => $_root,
            'innerHTML' => $html_parts,
            'tree'      => $_tree,
            'withCCE'   => $this->CCEok,
            'markdeceased'  => (bool) $markdeceased,
            'showseparated' => $showseparated,
            'bseTitle'  => $_bseTitle,
            'minTitle'  => $_minTitle,
            'maxTitle'  => $_maxTitle,
            'pmaphide'  => $pmaphide,
            'doExpand'  => $_doExpand,
            'xtRmode'   => (bool) $xtRmode,
            'xtv_in'    => 'xtv_in_' . $_name,
        ]);
        return [
            $html,
            'var ' . $_name . 'Handler = new TreeViewHandlerXT("' . $_name  .'",'. $_doExpand . ', "' . $_minTitle . '", "' . $_maxTitle . '", "' . $markdeceased  . '");',
        ];
    }
    private function drawViewport_xtR_part(Individual $individual, string $earmark, bool $doExpand): string
    {
        $this->name     = $earmark;
        $_name          = trim($this->name);

        $individual     = $this->xtR_root($individual, $earmark);
        $this->xtR_Ifrom = $this->xtR_parts[$earmark]['I_from']->xref();
        $this->xtR_Ito  = $this->xtR_parts[$earmark]['I_to']->xref();

        $_root          = $individual->xref();
        $_tree          = $individual->tree();

        $_genMIN        = (int) $this->xtR_genMIN($earmark); // - 1;
        $_genMAX        = (int) 0;

        // @param Individual  $person   The Person object to draw the box for
        // @param string      $earmark  Mark  First - Second - (and so on) Treeview on page
        // @param int         $gen      The number of generations up or down to print
        // @param int         $state    Whether we are going up or down the tree, -1 for descendents +1 for ancestors
        // @param Family|null $pfamily
        // @param string      $line     b, c, h, t. Required for drawing lines between boxes
        // @param bool        $isRoot
        // @param string      $rcLfd    

        $innerHTML = $this->drawPerson_xtR($individual, $earmark, $_genMIN, $_genMAX, 0, null, '', true, '');

        if (!$this->suppImplex)
            $this->put_xrefsF_bl();

        return $innerHTML;
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
        $json_requests  = explode(';', $request);
        $r    = [];
        foreach ($json_requests as $json_request) {
            $firstLetter = substr($json_request, 0, 1);
            $json_request = substr($json_request, 1);

            $jr_p       = explode('|', $json_request);
            $json_r0    = $jr_p[0];
            $_state     = $jr_p[1];
            if (count($jr_p) > 2) { $_rcLfd = $jr_p[2]; }
            $state      = intval($_state);
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
                                $r[] = $this->drawPerson_separated($parent, $earmark, 0, $state,
                                                                   $family, $order, false, $_rcLfd);
                            } else
                                $r[] = $this->drawPerson_default($parent, $earmark, 0, $state,
                                                                 $family, $order, false);
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
     * @param string $fid       the family(-ies) to return the details for
     * @param string $pid       the individual to return the details for
     *
     * @return string
     */
    public function getDetails(string $fid, string $pid, Tree $tree): string
    {
        $individual = Registry::individualFactory()->make($pid, $tree);
        $individual = Auth::checkIndividualAccess($individual);

        $html = $this->getPersonDetails($individual, null);

        if (str_contains($fid, '|')) {
            $fids   = explode('|', $fid);
        } else {
            $fids[] = $fid;
        }
        foreach ($fids as $_fid) {
            $family     = Registry::familyFactory()->make($_fid, $tree);
            if ($family) {
                $spouse = $family->spouse($individual);
                if ($spouse) {
                    $html .= $this->getPersonDetails($spouse, $family);
                }
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
    private function getPersonDetails(Individual $individual, Family|null $family = null): string
    {

        $chart_url  = route('module', [
            'module' => $this->module,
            'action' => 'Chart',
            'xref'   => $individual->xref(),
            'tree'   => $individual->tree()->name(),
        ]);

        $icon_indi  = ($individual->sex() == 'F' ? 'huhwt-iconF' : 'huhwt-iconM');
        if ($this->markDeceased) { 
            $_isdead = $individual->isDead() ? ' ' . $this->isDead_icon : '';
        } else { $_isdead = ''; }
        $_divorced  = PD_facts($family, Gedcom::DIVORCE_EVENTS);

        $html       = $this->getThumbnail($individual);
        $html       .= '<a class="tv_link" href="' . e($individual->url()) . '">' . $individual->fullName() . '</a>';
        if ($_divorced) { $html .= ' ' . $this->divorced_icon; }
        $html       .= $_isdead;
        $html       .= '<a href="' . e($chart_url) . '" title="' . I18N::translate('Interactive tree of %s', strip_tags($individual->fullName())) . '" class="' . $icon_indi . ' tv_link tv_treelink" ></a>';
        foreach ($individual->facts(Gedcom::BIRTH_EVENTS, true) as $fact) {
            $html       .= $fact->summary();
        }
        if ($family) {
            foreach ($family->facts(Gedcom::MARRIAGE_EVENTS, true) as $fact) {
                $html       .= $fact->summary();
            }
        }
        if ($_divorced) {
            $html   .= $_divorced;
        }
        foreach ($individual->facts(Gedcom::DEATH_EVENTS, true) as $fact) {
            $html       .= $fact->summary();
        }

        $xref       = $individual->xref();
        $_name      = trim($this->name);
        $pIDdom     = ''.join('', [' name="', $_name , 'xref', $xref , '"']);
        $_pID       = ''.join('', [' pid="', $xref , '"']);
        $html_div   = 'class="tv' . $individual->sex() . ' tv_person_expanded"' . $_pID . $pIDdom;
        return '<div ' . $html_div . '">' . $html . '</div>';
    }

    /**
     * Draw a person name preceded by sex icon, with parents as tooltip
     *
     * @param Individual $individual The individual to draw
     * @param string     $dashed     Either "dashed", to print dashed top border to separate multiple spouses, or ""
     *
     * @return string
     */
    private function drawPersonName(Individual $individual, string $dashed, int $isImplexI, string $_isdivorced = ''): string
    {
        $family = $individual->childFamilies()->first();
        if ($family) {
            $family_name = strip_tags($family->fullName());
        } else {
            $family_name = I18N::translateContext('unknown family', 'unknown');
        }
        $sex = $individual->sex();
        switch ($sex) {
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

        $xref = $individual->xref();
        $pID = ' pID="' . $xref . '" ';    // EW.H - MOD ... we want the xref anyway
        $pIDdom = ' name="' . $this->tvMark . 'xref' . $xref . '" ';

        $s_Implex = '';
        $dbolded = '';
        if ($isImplexI > 0) {
            $s_Implex = '<span> ->' . json_decode('"\u210D"') . '<- </span>';
            $title_0 .= ' - ' . I18N::translate('Implex detected');
            $dbolded = ' dbolded';
        }
        $title = ' title="' . $title_0 . '"';

        $_isdead        = '';
        if ($this->markDeceased) {
            $_isdead = $individual->isDead() ? $this->isDead_icon : '';
        }

        return '<div class="tv' . $sex . ' tv_Person ' . $dashed . $dbolded . '"' . $title . $pID . $pIDdom .'><a href="' . e($individual->url()) . '"></a>' . $individual->fullName() . $_isdivorced . $_isdead . ' <span class="dates">' . $individual->lifespan() . '</span>' . $s_Implex . '</div>';
    }

    /**
     * Test maternal/paternal precedence
     * 
     * @param Family $theFamily
     * 
     * @return Individual || null
     */
    private function test_showmatri(Family $theFamily): ?Individual
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

}

function PD_facts($ent, $ev_type) : string
{
    $html = '';
    if ($ent) {
        foreach ($ent->facts($ev_type, true) as $fact) {
            $html       .= $fact->summary();
        }
    }
    return $html;
}

