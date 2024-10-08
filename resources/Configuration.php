<?php

/**
 * See LICENSE.md file for further details.
 */

declare(strict_types=1);

namespace HuHwt\WebtreesMods\InteractiveTreeXT;

use Fisharebest\Webtrees\I18N;
use HuHwt\WebtreesMods\InteractiveTreeXT;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Configuration class.
 *
 * @author  EW.H <GIT@HuHnetz.de>
 * @license https://opensource.org/licenses/GPL-3.0 GNU General Public License v3.0
 * @link    https://github.com/huhwt/huhwt-xtv/
 */
class Configuration
{

    /**
     *  Expansion level
     *  
     * @var int
     */
    public const PATRI_PRIO  = 0;               // Mothers first

    /**
     * The default number of generations to display.
     *
     * @var int
     */
    public const DEFAULT_GENERATIONS = 4;

    /**
     * Minimum number of displayable generations.
     *
     * @var int
     */
    public const MIN_GENERATIONS = 1;

    /**
     * Maximum number of displayable generations.
     *
     * @var int
     */
    public const MAX_GENERATIONS = 25;

    /**
     * The current request instance.
     *
     * @var ServerRequestInterface
     */
    private $suppImplex; 

    /**
     * Expansion level.
     *
     * @var int
     */
    private $request;

    /**
     * Rootname
     * 
     * @var string
     */
    private $moduleName;

    /**
     * Treeview prefix
     */
    // private const TV_PREFIX = [ 'M', 'U', 'L', 'T', 'V'];
    private const TV_PREFIX = [ 'XT' ];

    /**
     * Treeview index
     */
    private $tv_PREFind;

    /**
     * Display modes
     */
    private const MODES = [ 'default', 'separated'];


    /**
     * Configuration constructor.
     *
     * @param ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
        $this->tv_PREFind = 0;
    }


    /**
     * Returns the number of generations to display.
     *
     * @return int
     */
    public function getGenerations(): int
    {
        $generations = (int) ($this->request->getQueryParams()['generations'] ?? self::DEFAULT_GENERATIONS);
        $generations = min($generations, self::MAX_GENERATIONS);

        return max($generations, self::MIN_GENERATIONS);
    }

    /**
     * Returns the expansion level.
     *
     * @return int
     */
    public function getPatriPrio(): int
    {
        return $this->request->getQueryParams()['patriprio'] ?? self::PATRI_PRIO;
    }

    /**
     * In case of Implex - shall this be shown?
     *
     * @return bool
     */
    public function getShowImplex(): bool
    {
        $showImplex = (int) $this->request->getQueryParams()['showImplex'] ?? 0;
        return boolval($showImplex);
    }

    /**
     * In case of Implex - shall all instances be expanded?
     *
     * @return bool
     */
    public function getSuppImplex(): bool
    {
        $suppImplex = (int) $this->request->getQueryParams()['suppImplex'] ?? 0;
        return boolval($suppImplex);
    }

    /**
     * In case of Implex - shall all instances be expanded?
     *
     * @return string
     */
    public function getMode(): string
    {
        $mode = (int) $this->request->getQueryParams()['showseparated'] ?? 0;
        return self::MODES[$mode];
    }

    /**
     * Set the Module-Name
     */
    public function setModuleName($name)
    {
        $this->moduleName = $name;
    }
    /**
     * Get the Module-Name
     */
    public function getModuleName(): string
    {
        return $this->moduleName;
    }

    /**
     * Get the prefices for all possible treeviews
     */
    public function getTvPrefix() : array
    {
        return self::TV_PREFIX;
    }

    /**
     * Get the prefix for actual treeview
     */
    public function getTvPrefixI(int $tvi=0) : string
    {
        return self::TV_PREFIX[$tvi];
    }

    /**
     * Get tv_PREFind
     */
    public function getTv_PREFind() : int
    {
        return $this->tv_PREFind;
    }
    /**
     * Add tv_PREFind
     */
    public function addTv_PREFind() : int
    {
        if ( $this->tv_PREFind < count(self::TV_PREFIX)) 
            return (int) $this->tv_PREFind += 1;
        else
            return count(self::TV_PREFIX);
    }
    /**
     * Sub tv_PREFind
     */
    public function subTv_PREFind() : int
    {
        if ( $this->tv_PREFind > 0)
            return (int) $this->tv_PREFind -= 1;
        else
            return 0;
    }
}
