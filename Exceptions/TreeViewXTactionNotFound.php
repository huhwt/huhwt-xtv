<?php

/**
 * webtrees: online genealogy
 * Copyright (C) 2021 webtrees development team
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 * 
 * HuH Extensions for webtrees - Extended Treeview
 * Extension for webtrees - a Treeview with single step expand and fold on/fold off a branch 
 * Copyright (C) 2020-2022 EW.Heinrich
 */

declare(strict_types=1);

namespace HuHwt\WebtreesMods\InteractiveTreeXT\Exceptions;

use Exception;
use Fisharebest\Webtrees\I18N;

/**
 * Application level exceptions.
 */
class TreeViewXTactionNotFoundException extends Exception
{
    /**
     * @param string|null $message
     */
    public function __construct(string $action = null)
    {
        $message = I18N::translate('Action unknown:') . '<pre>' . e($action) . '</pre>';

        parent::__construct($message);
    }
}
