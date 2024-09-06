<?php

/**
 * HuH Extensions for webtrees - Treeview-Extended
 * Interactive Treeview with add-ons
 * Copyright (C) 2020-2024 EW.Heinrich
 */

declare(strict_types=1);

namespace HuHwt\WebtreesMods\InteractiveTreeXT;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Session;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use HuHwt\WebtreesMods\InteractiveTreeXT\Module\TreeViewXTmod;
use HuHwt\WebtreesMods\InteractiveTreeXT\Exceptions\TreeViewXTactionNotFoundException;

use HuHwt\WebtreesMods\ClippingsCartEnhanced\ClippingsCartEnhancedModule;

/**
 * Class InteractiveTreeXT
 * 
 * @author  EW.H <GIT@HuHnetz.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/huhwt/huhwt-xtv/
 */

class InteractiveTreeXTmod extends AbstractModule implements RequestHandlerInterface
{
    // use IndividualTrait;

    // private const ROUTE_DEFAULT = 'huhwt-xtv';
    // private const ROUTE_URL = '/tree/{tree}/xtv&xref={xref}';

    private $huh;

    public function __construct() {
        $this->huh = json_decode('"\u210D"') . "&" . json_decode('"\u210D"') . "wt";
    }


    /**
     * EW.H MOD ... Switch over to 'Details' or 'Individuals'
     *
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $action = Validator::queryParams($request)->string('action');

        if ( $action == 'Details' ) {
            return $this->getDetailsAction($request);
        }

        if ( $action == 'Individuals' ) {
            return $this->getIndividualsAction($request);
        }

        if ( $action == 'CCEadapter' ) {
            return $this->getCCEadapterAction($request);
        }

        throw new TreeViewXTactionNotFoundException($action);
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function getDetailsAction(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();
        $XREFroot = Validator::queryParams($request)->string('XREFroot');

        $pid  = Validator::queryParams($request)->string('pid');

        $individual = Registry::individualFactory()->make($pid, $tree);
        $individual = Auth::checkIndividualAccess($individual);
        $instance = Validator::queryParams($request)->string('instance');
        $module   = Validator::queryParams($request)->string('module');

        $s_showseparated = Validator::queryParams($request)->string('showseparated', '0');
        $showseparated = $s_showseparated == '1' ? 'separated' : 'default';

        $treeview = new TreeViewXTmod($instance, $module, $tree, $XREFroot, $showseparated);
        $treeview->reload();

        return response($treeview->getDetails($individual));
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function getIndividualsAction(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();
        $XREFroot = Validator::queryParams($request)->string('XREFroot');

        $q        = Validator::queryParams($request)->string('q');
        $instance = Validator::queryParams($request)->string('instance');
        $earmark  = substr($instance, 2);
        $module   = Validator::queryParams($request)->string('module');

        $s_showseparated = Validator::queryParams($request)->string('showseparated', '0');
        $showseparated = $s_showseparated == '1' ? 'separated' : 'default';

        $treeview = new TreeViewXTmod($instance, $module, $tree, $XREFroot, $showseparated);
        $treeview->reload();

        return response($treeview->getIndividuals($tree, $earmark, $q));
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     * 
     * perform ClippingsCart
     */
    public function getCCEadapterAction(ServerRequestInterface $request): ResponseInterface
    {

        $tree       = Validator::attributes($request)->tree();
        $XREFindi   = Validator::queryParams($request)->string('XREFindi', '');
        $xrefs = Validator::queryParams($request)->string('xrefs', '');

        $CCEok = class_exists("HuHwt\WebtreesMods\ClippingsCartEnhanced\ClippingsCartEnhancedModule", true);
        if (!$CCEok) {
            $cart = Session::get('cart', []);
            $xrefs = $cart[$tree->name()] ?? [];
            $countXREFcold = count($xrefs);
            return response((string) $countXREFcold);
        }

        $CCEadapter = new ClippingsCartEnhancedModule();

        return response($CCEadapter->clip_xtv($request));
    }

}
