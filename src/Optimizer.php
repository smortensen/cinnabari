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

use Datto\Cinnabari\Optimizer\RemoveUnusedSorts;

class Optimizer
{
    public function optimize($request)
    {
        $request = $this->removeUnusedSorts($request);
        $request = $this->insertDirectly($request);
        $request = $this->filterBeforeSort($request);

        return $request;
    }

    private function removeUnusedSorts($request)
    {
        $optimizer = new RemoveUnusedSorts();
        return $optimizer->optimize($request);
    }

    private function insertDirectly($request)
    {
        /*
        Rule: "insert(f(a, b), c) => insert(a, c)" when "f" is the "sort", "slice", or "filter" function
         * Note: this rule may apply more than once during the simplification of a request
         * Note: if this rule doesn't apply, then the request should be left unchanged
        */

        return $request;
    }

    private function filterBeforeSort($request)
    {
        /*
        Rule: "filter(sort(a, b), c) => sort(filter(a, c), b)"
         * Note: this ordering simplifies the MySQL output (it removes a subquery--speeding up the query--without changing the result set)
         * Note: this sequence may appear more than once in a single request (because there can be more than one "get" expression in a single request)
         * Note: if this rule doesn't apply, then the request should be left unchanged
         */
        return $request;
    }
}
