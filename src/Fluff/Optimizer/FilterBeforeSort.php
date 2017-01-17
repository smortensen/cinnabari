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

namespace Datto\Cinnabari\Fluff\Optimizer;

use Datto\Cinnabari\Request\Parser;

/**
 * An optimizer to rearrange the ordering of sorts and filters in order to lead to a more performant response
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
        switch ($token[0]) {
            case Parser::TYPE_FUNCTION: {
                // It's important to optimize the children first, or our first 'filter' might head deeper
                $token[2] = $this->optimizeChildren($token[2]);

                $token = $this->convert($token);

                break;
            }

            case Parser::TYPE_OBJECT: {
                $token[1] = $this->optimizeChildren($token[1]);
                break;
            }
        }

        return $token;
    }

    private function optimizeChildren(array $tokens)
    {
        foreach ($tokens as $idx => $subToken) {
            $tokens[$idx] = $this->optimize($subToken);
        }

        return $tokens;
    }

    private function convert($token)
    {
        if (
            $this->checkFunction($token, 'filter', 2)
            && $this->checkFunction($token[2][0], 'sort', 2)
        ) {

            $sortToken = $token[2][0];

            $token = array(
                Parser::TYPE_FUNCTION,
                'sort',
                array(
                    array(
                        Parser::TYPE_FUNCTION,
                        'filter',
                        array(
                            $sortToken[2][0], // sort(a, ..)
                            $token[2][1] // filter(.., c)
                        )
                    ),
                    $sortToken[2][1] // sort(.., b)
                )
            );
        }

        return $token;
    }

    private function checkFunction(array $token, $functionName, $argCount)
    {
        return ($token[0] == Parser::TYPE_FUNCTION && $token[1] == $functionName && count($token[2]) == $argCount);
    }
}
