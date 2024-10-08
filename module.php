<?php

/**
 * HuH Extensions for webtrees - Extended Treeview
 * Extension for webtrees - a Treeview with single step expand and fold on/fold off a branch 
 * Copyright (C) 2020-2024 EW.Heinrich
 * See LICENSE.md file for further details.
 */

declare(strict_types=1);

namespace HuHwt\WebtreesMods\InteractiveTreeXT;

use Fisharebest\Webtrees\Webtrees;
use Fisharebest\Webtrees\Registry;

//webtrees major version switch
if (defined("WT_VERSION"))
    {
    //this is a webtrees 2.x module. it cannot be used with webtrees 1.x. See README.md.
    return;
    } else {
    $version = Webtrees::VERSION;
}

// Register our namespace
require_once __DIR__ . '/autoload.php';

// Create and return instance of the module
return Registry::container()->get(InteractiveTreeXT::class);
