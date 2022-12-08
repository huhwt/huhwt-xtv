<?php

/**
 * webtrees: online genealogy
 * Copyright (C) 2020 webtrees development team
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
 * HuH Extensions for webtrees - Extended Treeview
 * Extension for webtrees - a Treeview with single step expand and fold on/fold off a branch 
 * Copyright (C) 2020-2022 EW.Heinrich
 */

declare(strict_types=1);

namespace HuHwt\WebtreesMods\InteractiveTreeXT\Module;

use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Family;
use Fisharebest\Webtrees\Gedcom;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Tree;
use Illuminate\Support\Collection;

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
    private $showpatri;

    /** @var int glevel-Stradonitz number */
    private $glevel;

    /**
     * Treeview Constructor
     *
     * @param string $name the name of the TreeView object’s instance
     */
    public function __construct(string $name = 'tvX', string $module, int $showpatri = 0)
    {
        $this->name = $name;
        $this->showpatri = $showpatri;
        $this->module = $module;

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
    public function drawViewport(Individual $individual, string $earmark, int $generations): array
    {
        $_name = trim($this->name);
        $html = view('modules/treeviewXT/chart', [
            'module'     => $this->module,                  // EW.H - MOD ... put own Module here!
            'name'       => $_name,
            'earmark'    => $earmark,
            'individual' => $this->drawPerson($individual, $earmark, $generations, 0, null, '', true),
            'tree'       => $individual->tree(),
        ]);

        return [
            $html,
            'var ' . $this->name . 'Handler = new TreeViewHandlerXT("' . $this->name  .'", true);',
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

            [$json_r0, $_state] = explode('|', $json_request);
            $state = intval($_state);
            $this->glevel = $state;
            switch ($firstLetter) {
                case 'c':
                    $families = Collection::make(explode(',', $json_r0))
                        ->map(static function (string $xref) use ($tree): ?Family {
                            return Registry::familyFactory()->make($xref, $tree);
                        })
                        ->filter();

                        $r[] = $this->drawChildren($families, $earmark, $state, 1, true);
                    break;

                case 'p':
                    [$xref, $order] = explode('@', $json_r0);

                    $family = Registry::familyFactory()->make($xref, $tree);
                    if ($family instanceof Family) {
                        // Prefer the paternal line
                        if ($this->showpatri == 0) {
                            $parent = $family->husband() ?? $family->wife(); 
                        } else {
                            $parent = $family->wife() ?? $family->husband();
                        }

                        // The family may have no parents (just children).
                        if ($parent instanceof Individual) {
                            $r[] = $this->drawPerson($parent, $earmark, 0, $state, $family, $order, false);
                        }
                    }
                    break;
            }
        }

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
     * EW.H - MOD ... added parm 'earmark'
     * 
     * Draw the children for some families
     *
     * @param Collection $familyList array of families to draw the children for
     * @param string     $earmark    Mark  First - Second - (up to five) Treeview on page          # EW.H - MOD 
     * @param int        $gen        number of generations to draw
     * @param bool       $ajax       true for an ajax call
     *
     * @return string
     */
    private function drawChildren(Collection $familyList, string $earmark, int $state, int $gen = 1, bool $ajax = false): string
    {
        $html          = '';
        $children2draw = [];
        $f2load        = [];

        foreach ($familyList as $f) {
            $children = $f->children();
            if ($children->isNotEmpty()) {
                $f2load[] = $f->xref();
                foreach ($children as $child) {
                    // Eliminate duplicates - e.g. when adopted by a step-parent
                    $children2draw[$child->xref()] = $child;
                }
            }
        }
        $tc = count($children2draw);
        if ($tc) {
            $f2load = implode(',', $f2load);
            $nbc    = 0;
            foreach ($children2draw as $child) {
                $nbc++;
                if ($tc == 1) {
                    $co = 'c'; // unique
                } elseif ($nbc == 1) {
                    $co = 't'; // first
                } elseif ($nbc == $tc) {
                    $co = 'b'; // last
                } else {
                    $co = 'h';
                }
                $html .= $this->drawPerson($child, $earmark, $gen - 1, $state, null, $co, false);           // EW.H - MOD ... added parm 'earmark'
            }
            if (!$ajax) {
                $expandit = '';
                if ( $gen > 0 ) {
                    $finis = '';
                    $expandit = 'TreeCollaps right huhwt_button16';
                } else {
                    $snext = $this->glevel;
                    $finis = ' abbr="c' . $f2load . '" state="' . $snext . '"';
                    $expandit = 'TreeToExpand right huhwt_button16';
                }
                $html = '<td align="right"' . $finis . '>' . $html . '</td>' . $this->drawHorizontalLine($expandit);
            }
        }

        return $html;
    }

    /**
     * EW.H - MOD ... added parm 'earmark' 
     * 
     * Draw a person in the tree
     *
     * @param Individual  $person The Person object to draw the box for
     * @param string      $earmark Mark  First - Second - (and so on) Treeview on page          # EW.H - MOD 
     * @param int         $gen    The number of generations up or down to print
     * @param int         $state  Whether we are going up or down the tree, -1 for descendents +1 for ancestors
     * @param Family|null $pfamily
     * @param string      $line   b, c, h, t. Required for drawing lines between boxes
     * @param bool        $isRoot
     *
     * @return string
     */
    private function drawPerson(Individual $person, string $earmark, int $gen, int $state, Family $pfamily = null, string $line = '', $isRoot = false): string
    {
        if ($gen < 0) {
            return '';
        }

        $fID = ' fID="_NIX_"';
        if ($pfamily instanceof Family) {
            $partner = $pfamily->spouse($person);
            $fID = ' fID="' . $pfamily->xref() . '"';
        } else {
            $partner = $person->getCurrentSpouse();
        }

        if ($isRoot) {
            $this->glevel = 0;
            $html = '<table id="tv' . $earmark . 'TreeBorder" class="tv_tree"><tbody><tr><td id="tv' . $earmark . '_tree_topleft"></td><td id="tv' . $earmark . '_tree_top"></td><td id="tv' . $earmark . '_tree_topright"></td></tr><tr><td id="tv' . $earmark . '_tree_left"></td><td>';
        } else {
            $html = '';
        }
        /* height 1% : this hack enable the div auto-dimensioning in td for FF & Chrome */
        $html .= '<table class="tv_tree"' . ($isRoot ? ' id="tv' . $earmark . '_tree"' : '') . ' style="height: 1%"><tbody><tr>';

        if ($state <= 0) {
            // draw children
            $this->glevel -= 1;
            $html .= $this->drawChildren($person->spouseFamilies(), $earmark, $state - 1, $gen);        # EW.H - MOD
            $this->glevel += 1;
        } else {
            // draw the parent’s lines
            $html .= $this->drawVerticalLine($line) . $this->drawHorizontalLine();
        }

        /* draw the person. Do NOT add person or family id as an id, since a same person could appear more than once in the tree !!! */
        // Fixing the width for td to the box initial width when the person is the root person fix a rare bug that happen when a person without child and without known parents is the root person : an unwanted white rectangle appear at the right of the person’s boxes, otherwise.
        $html .= '<td' . ($isRoot ? ' style="width:1px"' : '') . '>';
        $html .= '<div class="tv_box' . ($isRoot ? ' rootPerson' : '') . '" dir="' . I18N::direction() . '" style="text-align: ' . (I18N::direction() === 'rtl' ? 'right' : 'left') . '; direction: ' . I18N::direction() . '" abbr="' . $person->xref() . '" state="' . $state . '" '. $fID . ' onclick="' . $this->name . 'Handler.expandBox(this, event);">';
        $html .= $this->drawPersonName($person, '');

        $fop = []; // $fop is fathers of partners

        if ($partner !== null) {
            $dashed = '';
            foreach ($person->spouseFamilies() as $family) {
                $spouse = $family->spouse($person);
                if ($spouse instanceof Individual) {
                    $spouse_parents = $spouse->childFamilies()->first();
                    if ($spouse_parents instanceof Family) {
                        if ($this->showpatri == 0) {
                            $spouse_parent = $spouse_parents->husband() ?? $spouse_parents->wife(); 
                        } else {
                            $spouse_parent = $spouse_parents->wife() ?? $spouse_parents->husband();
                        }

                        if ($spouse_parent instanceof Individual) {
                            $fop[] = [$spouse_parent, $spouse_parents];
                        }
                    }

                    $html .= $this->drawPersonName($spouse, $dashed);
                    $dashed = 'dashed';
                }
            }
        }
        if ($state <> 0) {
            $_glevel = '(' . I18N::translate('Generation') . ' ' . strval($this->glevel) . ')';
            $html .= '</div><div class="tv_box_glevel">' . $_glevel;
        }
        $html .= '</div></td>';

        $primaryChildFamily = $person->childFamilies()->first();
        if ($primaryChildFamily instanceof Family) {
            if ($this->showpatri == 0) {
                $parent = $primaryChildFamily->husband() ?? $primaryChildFamily->wife(); 
            } else {
                $parent = $primaryChildFamily->wife() ?? $primaryChildFamily->husband();
            }
        } else {
            $parent = null;
        }

        $expandit = 'TreeCollaps left huhwt_button16';
        $html_HlineC = '';
        $html_HlineE = '';
        if ($parent instanceof Individual || !empty($fop) || $state < 0) {
            if ($state < 0) {$expandit = '';}
            $html_HlineC .= $this->drawHorizontalLine($expandit);
            $html_HlineE .= $html_HlineC;
            if ($expandit) {
                $html_HlineE = str_replace ( 'TreeCollaps', 'TreeToExpand', $html_HlineE);
            }
        }

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
                    $html .= $this->drawPerson($parent, $earmark, $gen - 1, 1, $primaryChildFamily, $u, false);             # EW.H - MOD
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
                $html .= $html_HlineE;
                $html .= $html_tb;
                foreach ($fop as $p) {
                    $n++;
                    $u = $unique ? 'c' : ($n == $nb || empty($p[1]) ? 'b' : 'h');
                    if ($gen > 0) {
                        $this->glevel += 1;
                        $html .= '<tr><td>' . $this->drawPerson($p[0], $earmark, $gen - 1, 1, $p[1], $u, false) . '</td></tr>';      # EW.H - MOD
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

        if ($state < 0) {
            $html .= $this->drawVerticalLine($line);
        }

        $html .= '</tr></tbody></table>';

        if ($isRoot) {
            $html .= '</td><td id="tv' . $earmark . '_tree_right"></td></tr><tr><td id="tv' . $earmark . '_tree_bottomleft"></td><td id="tv' . $earmark . '_tree_bottom"></td><td id="tv' . $earmark . '_tree_bottomright"></td></tr></tbody></table>';         # EW.H - MOD
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
    private function drawPersonName(Individual $individual, string $dashed): string
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
                $title = ' title="' . I18N::translate('Son of %s', $family_name) . '"';
                break;
            case 'F':
                /* I18N: e.g. “Daughter of [father name & mother name]” */
                $title = ' title="' . I18N::translate('Daughter of %s', $family_name) . '"';
                break;
            default:
                /* I18N: e.g. “Child of [father name & mother name]” */
                $title = ' title="' . I18N::translate('Child of %s', $family_name) . '"';
                break;
        }
        $sex = $individual->sex();

        $xref = $individual->xref();
        $pID = ' pID="' . $xref . '" ';    // EW.H - MOD ... we want the xref anyway
        $pIDdom = ' name="' . $this->name . 'xref' . $xref . '" ';
        $Glevel = ' Glevel="' . $this->glevel . '"';

        return '<div class="tv' . $sex . ' ' . $dashed . '"' . $title . $pID . $Glevel . $pIDdom .'><a href="' . e($individual->url()) . '"></a>' . $individual->fullName() . ' <span class="dates">' . $individual->lifespan() . '</span></div>';
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
     * Draw an horizontal line
     */
    private function drawHorizontalLine($expand = ''): string
    {
        if ( $expand == '' ) {
            return '<td class="tv_hline"><div class="tv_hline"></div></td>';
        } else {
            $html_td = '<td class="tv_hline">';
            $html_td .= '<div class="' . $expand . '" title="' . I18N::translate('Click here to expand/collapse subtree') . '" onclick="' . $this->name . 'Handler.expandTree(this, event);"></div>';
            $html_td .= '</td>';
            return $html_td;
        }
    }
}
