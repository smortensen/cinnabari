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
 * @author Christopher Hoult <choult@datto.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2016 Datto, Inc.
 */

namespace Datto\Cinnabari\Optimizer;

use Datto\Cinnabari\Parser;

/**
 * An optimizer to rearrage the ordering of sorts and filters in order to lead to a more performant response
 *
 * Rule: "filter(sort(a, b), c) => sort(filter(a, c), b)"
 *   Note: this ordering simplifies the MySQL output (it removes a subquery--speeding up the query--without changing the
 *      result set)
 *   Note: this sequence may appear more than once in a single request (because there can be more than one "get"
 *      expression in a single request)
 *   Note: if this rule doesn't apply, then the request should be left unchanged
 */
class FilterBeforeSort
{

    public function optimize($token)
    {
        return $this->convert($token);
    }

    private function convert($token)
    {
        if ($token[0] !== Parser::TYPE_FUNCTION) {
            return $token;
        }

        $token[2] = $this->convert($token[2]);

        if (
            $token[1] == 'filter'
            && $token[2][0] == Parser::TYPE_FUNCTION
            && $token[2][1] == 'sort'
        ) {
            // Swap in place:

            list(
                $token[1], // Should be 'filter'
                $token[2][1], // Should be 'sort'
                $token[3], // The filter's second argument
                $token[2][3] // The sort's second argument
            ) = array(
                'sort',
                'filter',
                $token[2][3],   // The sort's second argument
                $token[3]   // The filter's second argument
            );
        }

        return $token;
    }
}
