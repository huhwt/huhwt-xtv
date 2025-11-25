<?php

/**
 * See LICENSE.md file for further details.
 */

declare(strict_types=1);

namespace HuHwt\WebtreesMods\InteractiveTreeXT\Traits;

use Fisharebest\Webtrees\Age;
use Fisharebest\Webtrees\Date;
use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Individual;
use Fisharebest\Webtrees\Menu;

/**
 * Trait ModuleChartTrait.
 *
 * @author  EW.H <GIT@HuH-netz.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/huhwt/huhwt-xtv/
 */
trait ModuleChartTrait
{
    use \Fisharebest\Webtrees\Module\ModuleChartTrait;

    /**
     * CSS class for the URL.
     *
     * @return string
     */
    public function chartMenuClass(): string
    {
        return 'menu-chart-tree';
    }

    /**
     * Return a menu item for this chart - for use in individual boxes.
     *
     * @param Individual $individual
     *
     * @return Menu|null
     */
    public function chartBoxMenu(Individual $individual): ?Menu
    {
        return $this->chartMenu($individual);
    }

    /**
     * The title for this chart.
     *
     * @param Individual $individual
     *
     * @return string
     */
    public function chartTitle(Individual $individual): string
    {
        $ct = $this->huh_short . ' ' . I18N::translate('Interactive tree XT'); 
        return $ct;
    }

    /**
     * The title on the page.
     *
     * @param Individual $individual
     *
     * @return string
     */
    public function pageTitle(): string
    {
        $ct = '-' . $this->huh . '- ' . I18N::translate('Interactive tree XT'); 
        return $ct;
    }

    /**
     * The title for a specific instance of this chart.
     *
     * @param Individual $individual
     *
     * @return string
     */
    public function chartSubTitle(Individual $individual): string
    {
        // What is (was) the age of the individual
        $bdate = $individual->getBirthDate();
        $ddate = $individual->getDeathDate();

        if ($individual->isDead()) {
            // If dead, show age at death
            $age = (string) new Age($individual->getBirthDate(), $individual->getDeathDate());
        } else {
            // If living, show age today
            $today = new Date(strtoupper(date('d M Y')));
            $age   = (string) new Age($individual->getBirthDate(), $today);
        }

        $htmlTOP = view('modules/treeviewXT/pageh2', [
            'individual' => $individual,
            'age'        => $age,
        ]);
        return $htmlTOP;
    }

    public function chartUrl(Individual $individual, array $parameters = []): string
    {
        $_name = $this->name();
        return route('module', [
                'module' => $_name,
                'action' => 'Chart',
                'xref'   => $individual->xref(),
                'tree'    => $individual->tree()->name(),
            ] + $parameters);
    }
}
