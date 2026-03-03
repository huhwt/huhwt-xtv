<?php

/**
 * HuH Extensions for webtrees - Treeview-Extended
 * Interactive Treeview with add-ons
 *
 * Copyright (C) 2025 huhwt. All rights reserved.
 * 
 *  Cissee\Webtrees\Module\ExtendedRelationships
 *  Copyright (C) 2025 Richard Cissée. All rights reserved.
 *
 * webtrees: online genealogy / web based family history software
 * Copyright (C) 2021-2025 webtrees development team.
 *
 */

declare(strict_types=1);

namespace HuHwt\WebtreesMods\InteractiveTreeXT\Traits;

use Fisharebest\Webtrees\I18N;
use Fisharebest\Webtrees\Menu;
use Fisharebest\Webtrees\Services\ModuleService;
use Fisharebest\Webtrees\Session;
use Fisharebest\Webtrees\Tree;

use Cissee\Webtrees\Module\ExtendedRelationships\ExtendedRelationshipModule;
use phpDocumentor\Reflection\Types\Integer;
use Psr\Http\Message\ResponseInterface;

use function redirect;
use function route;
/**
 * Trait XTVcustomModuleConnect - bundling all actions regarding connecting to other custom modules
 */
trait XTVcustomModuleConnect
{
    /**
     * @param -none-
     *
     * @return array<int,<string> 
     * 
     * values for vesta Extended Relationships functions covered by XTV
     * key and value=message adopted from Vesta
     */

    public function vERfunctions () : array
    {
        return [
            4 => I18N::translate('Find the closest overall connections (preferably via common ancestors)'),
            5 => I18N::translate('Find the closest overall connections'),
        ];
    }

    /**
     * @param -none-
     *
     * @return bool
     * 
     * test if _extended_relationships_ is installed
     */
    private function test_VER_ () : bool
    {
        $retval = false;
        $this->module_service = new ModuleService();
        $extended_relationships = $this->module_service->findByName('_vesta_extended_relationships_');
        if ($extended_relationships !== null ) {
            $retval = true;
        }
        return $retval;
    }
}