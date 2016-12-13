<?php

/**
 * Copyright (C) 2016 Datto, Inc.
 *
 * This file is part of Cinnabari.
 *
 * Cinnabari is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License, version 3,
 * as published by the Free Software Foundation.
 *
 * Cinnabari is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Cinnabari. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Spencer Mortensen <smortensen@datto.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2016 Datto, Inc.
 */

namespace Datto\Cinnabari;

use Datto\Cinnabari\Optimizer\RemoveUnnecessarySorting;

class Optimizer
{
/*
Optimize: get(slice(sort(filter(...), ...), ...), ...)

 * Inside the "insert" function:
     * insert sort => insert [x]
     * insert slice => insert [x]
     * insert filter => insert [x]
 * Inside each top-level "get" function, require a "sort" (use the schema to get the id property):
     * get(people, ...) => get(sort(people, id), ...)
     * get(slice(people, ...), ...) => get(slice(sort(people, id), ...), ...)
 * Use the canonical order for filtering and sorting:
     * filter(sort(people, ...) => sort(filter(people, ...), ...)

TYPE_PARAMETER, TYPE_PROPERTY
TYPE_FUNCTION
TYPE_OBJECT
*/

    public function optimize($request)
    {
        return $this->removeUnnecessarySorting($request);
    }

    private function removeUnnecessarySorting($request)
    {
        $removeUnnecessarySorting = new RemoveUnnecessarySorting();
        return $removeUnnecessarySorting->optimize($request);
    }
}
