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

namespace HuHwt\WebtreesMods\InteractiveTreeXT\Http\RequestHandlers;

use Fisharebest\Webtrees\Age;
use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Date;
use Fisharebest\Webtrees\Exceptions\IndividualAccessDeniedException;
use Fisharebest\Webtrees\Exceptions\IndividualNotFoundException;
use Fisharebest\Webtrees\Http\ViewResponseTrait;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Session;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use HuHwt\WebtreesMods\InteractiveTreeXT\Module\TreeViewXTmod;

use function assert;
use function redirect;
use function app;

/**
 * TreeView eXTended - Interactive check
 * 
 * EW.H - MOD ... derived from webtrees/app/Http/Requesthandlers/MergeRecordsAction.php
 *                        and  webtrees/app/Module/InteractiveTreeModule.php
 */
class TreeViewXTrh implements RequestHandlerInterface
{
    use ViewResponseTrait;

    /** @var string A unique internal name for this module (based on the installation folder). */
    private $name = '';

    /**
     * A unique internal name for this module (based on the installation folder).
     *
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * The title for a specific instance of this chart.
     *
     * @param Individual $individual
     *
     * @return string
     */
    public function chartTitle(Individual $individual): string
    {
        // What is (was) the age of the individual
        $bdate = $individual->getBirthDate();
        $ddate = $individual->getDeathDate();

        if ($individual->isDead()) {
            // If dead, show age at death
            $age = (string) new Age($bdate, $ddate);
        } else {
            // If living, show age today
            $today = strtoupper(date('d M Y'));
            $age   = (string) new Age($bdate, new Date($today));
        }

        $htmlTOP = view('modules/treeviewXT/pageh2', [
            'individual' => $individual,
            'age'        => $age,
        ]);
        $ct = I18N::translate('Check tree of %s', $htmlTOP); 
        return $ct;
    }
    /**
     * EW.H - MOD ... die ID mit anzeigen
     * @param Individual $individual
     *
     * @return string
     */
    public function chartSubTitle(Individual $individual): string
    {
        /* I18N: %s is an individual’s name */
        $ct = I18N::translate('Tree of %s', $individual->fullName()); 
        if (!str_ends_with($ct, ")")) {                         // EW.H - MOD ... test if other extension occasionally had added ID
            $ct = $ct  . " (" . $individual->xref() . ")";
        }
        return $ct;
        /* return I18N::translate('Interactive tree of %s', $individual->fullName()); */
    }


    /**
    * EW.H - MOD ... we need root of extension for explicitly referencing styles and scripts in generated HTML
    *
    * Get root of Module
    *       huhwt-mtv/          <- we don't know what to preset here to identify the location in page-hierarchy
    *       - Http/
    *         - RequestHandlers/
    *           - (thisFile)
    *       - resources/        <- here we want to point to later
    */
    private function modRoot(): string
    {
        $file_path = e(asset('snip/'));
        $file_path = str_replace("/public/snip/", "", $file_path) . "/modules_v4/huhwt-xtv";
        return $file_path;
    }

    /**
     * Merge two genealogy records.
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // $this->layout = 'layouts/adminMTV';

        $tree = Validator::attributes($request)->tree();

        $xref_s = Validator::queryParams($request)->string('xrefs', '');
        $xrefs = explode(",", $xref_s);
        $xref1 = $xrefs[0];
        $xref2 = $xrefs[1];

        $titleRH = I18N::translate('Interactive check') . ' — ' . e($tree->title());
        $modRoot = $this->modRoot();

        $record1 = Registry::gedcomRecordFactory()->make($xref1, $tree);
        $record2 = Registry::gedcomRecordFactory()->make($xref2, $tree);

        if (
            $record1 === null ||
            $record2 === null ||
            $record1 === $record2 ||
            $record1->tag() !== $record2->tag() ||
            $record1->isPendingDeletion() ||
            $record2->isPendingDeletion()
        ) {
            return redirect(route(MergeRecordsPage::class, [
                'tree'  => $tree->name(),
                'xref1' => $xref1,
                'xref2' => $xref2,
            ]));
        }

        // $tv = new MultTreeViewMod('tv');

        $earmarks = [ "M", "U", "L", "T", "V" ];         // EW.H - MOD ... up to 5 Indi which are viewed as possible duplicates

        $module   = Validator::queryParams($request)->string('module');

        $htmlAr = [];
        $jsAr = [];
        for ($i = 0, $iE = count($xrefs); $i < $iE ; ++$i) {

            $xref = $xrefs[$i];
            $individual = Registry::individualFactory()->make($xref, $tree);
            $individual = Auth::checkIndividualAccess($individual, false, true);
            if ( $i == 0) { $individual0 = $individual; }

            $htmlTOP = $this->chartSubTitle($individual);
    
            $earmark = $earmarks[$i];
            $tv = new TreeViewXTmod('tv' . $earmark, $module);          // EW.H - MOD ... we need a private instance for each treeview

            [$html, $js] = $tv->drawViewport($individual, $earmark, 3);
    
            $html = $htmlTOP . $html;

            $htmlAr[] = $html;
            $jsAr[] = $js;
        }
        // echo $htmlAr[1];
        $actLan = Session::get('language', '');

        return $this->viewResponse('modules/treeviewXT/page', [
            'htmls'      => $htmlAr,
            // 'individual' => $individual,
            'jss'        => $jsAr,
            // 'module'     => $this->name(),
            'title'      => $this->chartTitle($individual0),
            'actLan'     => $actLan,
            'tree'       => $tree,
            'modRoot'    => $modRoot,       // EW.H - MOD ... root of this module
        ]);
    }
}
