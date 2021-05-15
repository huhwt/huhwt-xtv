<?php

/**
 * See LICENSE.md file for further details.
 */

declare(strict_types=1);

namespace HuHwt\WebtreesMods\InteractiveTreeXT;

use Fisharebest\Webtrees\Exceptions\IndividualAccessDeniedException;
use Fisharebest\Webtrees\Exceptions\IndividualNotFoundException;
use Fisharebest\Webtrees\Module\AbstractModule;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

use HuHwt\WebtreesMods\InteractiveTreeXT\Module\TreeViewXTmod;
use HuHwt\WebtreesMods\InteractiveTreeXT\Exceptions\TreeViewXTactionNotFoundException;
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
        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $action = $request->getQueryParams()['action'];

        if ( $action == 'Details' ) {
            return $this->getDetailsAction($request);
        }

        if ( $action == 'Individuals' ) {
            return $this->getIndividualsAction($request);
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
        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $pid        = $request->getQueryParams()['pid'];
        $individual = Registry::individualFactory()->make($pid, $tree);

        if ($individual === null) {
            throw new IndividualNotFoundException();
        }

        if (!$individual->canShow()) {
            throw new IndividualAccessDeniedException();
        }

        $instance = $request->getQueryParams()['instance'];
        $treeview = new TreeViewXTmod($instance, $request->getQueryParams()['module']);

        return response($treeview->getDetails($individual));
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function getIndividualsAction(ServerRequestInterface $request): ResponseInterface
    {
        $tree = $request->getAttribute('tree');
        assert($tree instanceof Tree);

        $q        = $request->getQueryParams()['q'];
        $instance = $request->getQueryParams()['instance'];
        $earmark  = substr($instance, 2);
        $treeview = new TreeViewXTmod($instance, $request->getQueryParams()['module']);

        return response($treeview->getIndividuals($tree, $earmark, $q));
    }
}
