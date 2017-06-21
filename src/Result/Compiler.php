<?php

/**
 * Copyright (C) 2016, 2017 Datto, Inc.
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
 * @copyright 2016, 2017 Datto, Inc.
 */

namespace Datto\Cinnabari\Result;

use Datto\Cinnabari\Parser;

/**
 * Class Compiler
 * @package Datto\Cinnabari
 *
 * top-level = read-expression # delete | insert | set
 * read-expression = average | count | get | max | min | sum
 *
 * average = <array, numeric>
 * count = <array>
 * delete = <array>
 * filter = <array, boolean>
 * get = <array, GET-EXPRESSION>
 * insert = <array, object>
 * max = <array, numeric>
 * min = <array, numeric>
 * set = <array, object>
 * slice = <array, numeric, numeric>
 * sort = <array, boolean | numeric | string>
 * sum = <array, numeric>
 * length = <array, string>
 * match = <string, string>
 * substring = <string, numeric, numeric>
 * lowercase = <string>
 * uppercase = <string>
 * times = <numeric, numeric>
 * divides = <numeric, numeric>
 * plus = <numeric, numeric> | <string, string>
 * minus = <numeric, numeric>
 *
 * numeric = times | divides | plus | minus | length | property | :parameter
 * boolean = less | lessEqual | equal | notEqual | greaterEqual | greater | match | not | and | or | property | :parameter
 * string = lowercase | uppercase | substring | plus | property | :parameter
 * array = slice | sort | filter | property
 */
class Compiler

{
    /** @var Map */
    private $map;

    public function __construct(Map $map)
    {
        $this->map = $map;
    }

    public function compile($request)
    {
        if (!$this->getReadExpression($request)) {
            // TODO: throw exception
        }

        $mysql = null;
        $phpInput = null;
        $phpOutput = null;

        return array($mysql, $phpInput, $phpOutput);
    }

    private function getReadExpression($request)
    {
        return $this->getCountFunction($request);
    }

    private function getCountFunction($token)
    {
        if (!self::getFunction('count', $arguments, $token)) {
            return false;
        }

        if (!$this->getArrayExpression($arguments[0])) {
            return false;
        }

        echo " * count\n";
        return true;
    }

    private function getArrayExpression($token)
    {
        return $this->getSliceFunction($token)
            || $this->getSortFunction($token)
            || $this->getFilterFunction($token)
            || $this->getArrayProperty($token);
    }

    private function getSliceFunction($token)
    {
        if (!self::getFunction('slice', $arguments, $token)) {
            return false;
        }

        if (!$this->getArrayExpression($arguments[0])) {
            return false;
        }

        echo " * slice\n";
        return true;
    }

    private function getSortFunction($token)
    {
        if (!self::getFunction('sort', $arguments, $token)) {
            return false;
        }

        if (!$this->getArrayExpression($arguments[0])) {
            return false;
        }

        echo " * sort\n";
        return true;
    }

    private function getFilterFunction($token)
    {
        if (!self::getFunction('filter', $arguments, $token)) {
            return false;
        }

        if (!$this->getArrayExpression($arguments[0])) {
            return false;
        }

        echo " * filter\n";
        return true;
    }

    private function getArrayProperty($token)
    {
        if (!$this->getProperty($token, $mysql)) {
            return false;
        }

        echo " * array: ", json_encode($mysql), "\n";
        return true;
    }

    private static function getProperty($token, &$mysql)
    {
        if ($token[0] !== Parser::TYPE_PROPERTY) {
            return false;
        }

        $mysql = $token[1];
        return true;
    }

    private static function getFunction($function, &$arguments, $token)
    {
        if ($token[0] !== Parser::TYPE_FUNCTION) {
            return false;
        }

        if ($token[1] !== $function) {
            return false;
        }

        $arguments = $token[2];
        return true;
    }
}
