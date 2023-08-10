<?php

/**
 * See LICENSE.md file for further details.
 */

declare(strict_types=1);

namespace HuHwt\WebtreesMods\InteractiveTreeXT\Traits;

use Fisharebest\Webtrees\Auth;
use Fisharebest\Webtrees\Http\Exceptions\HttpAccessDeniedException;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Registry;
use Fisharebest\Webtrees\Tree;
use Fisharebest\Webtrees\Validator;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use function response;
use function view;

/**
 * Trait ModuleTabTrait.
 *
 * @author  EW.H <GIT@HuH-netz.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/huhwt/huhwt-xtv/
 */
trait ModuleTabTrait
{

    use \Fisharebest\Webtrees\Module\ModuleTabTrait;

    // The default position for this tab.  It can be changed in the control panel.
    protected int $tab_order;

    /**
     * How should this module be identified in the control panel, etc.?
     *
     * @return string
     */
    abstract public function title(): string;

    /**
     * The text that appears on the tab.
     *
     * @return string
     */
    public function tabTitle(): string
    {
        return $this->title();
    }

    /**
     * Get the current access level for a module
     *
     * @template T of ModuleInterface
     *
     * @param Tree            $tree
     * @param class-string<T> $interface
     *
     * @return int
     */
    abstract public function accessLevel(Tree $tree, string $interface): int;

    /**
     * Users change change the order of tabs using the control panel.
     *
     * @param int $tab_order
     *
     * @return void
     */
    public function setTabOrder(int $tab_order): void
    {
        $this->tab_order = $tab_order;
    }

    /**
     * Users change change the order of tabs using the control panel.
     *
     * @return int
     */
    public function getTabOrder(): int
    {
        return $this->tab_order ?? $this->defaultTabOrder();
    }

    /**
     * The default position for this tab.  It can be changed in the control panel.
     *
     * @return int
     */
    public function defaultTabOrder(): int
    {
        return 7;                       // EW.H - MOD ...
    }

    /**
     * This module handles the following facts - so don't show them on the "Facts and events" tab.
     *
     * @return Collection<int,string>
     */
    public function supportedFacts(): Collection
    {
        return new Collection();
    }

    /**
     * Generate the HTML content of this tab.
     *
     * @param Individual $individual
     *
     * @return string
     */
    public function getTabContent(Individual $individual): string
    {
        return '';
    }

    /**
     * @param ServerRequestInterface $request
     *
     * @return ResponseInterface
     */
    public function getTabAction(ServerRequestInterface $request): ResponseInterface
    {
        $tree = Validator::attributes($request)->tree();
        $user = Validator::attributes($request)->user();
        $xref = Validator::queryParams($request)->isXref()->string('xref');

        $record = Registry::individualFactory()->make($xref, $tree);
        $record = Auth::checkIndividualAccess($record);

        if ($this->accessLevel($tree, ModuleTabInterface::class) < Auth::accessLevel($tree, $user)) {
            throw new HttpAccessDeniedException();
        }

        $layout = view('layouts/ajax', [
            'content' => $this->getTabContent($record),
        ]);

        return response($layout);
    }
}
