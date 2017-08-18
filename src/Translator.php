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

namespace Datto\Cinnabari;

use Datto\Cinnabari\AbstractRequest\Nodes\FunctionNode;
use Datto\Cinnabari\AbstractRequest\Nodes\PropertyNode;
use Datto\Cinnabari\AbstractRequest\Node;
use Datto\Cinnabari\Translator\Map\Map;

/**
 * expression = select
 * select = average | count | map | max | min | sum
 * average = <array>
 * count = <array>
 * map = <array, MAP-EXPRESSION>
 * max = <array>
 * min = <array>
 * sum = <array>
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
 * boolean = less | lessEqual | equal | notEqual | greaterEqual | greater | match | not | and | or | boolean-property | :parameter
 * string = lowercase | uppercase | substring | plus | property | :parameter
 * array = filter | sort | slice | property
 * filter = <array, boolean>
 * sort = <array, boolean | numeric | string>
 * slice = <array, numeric, numeric>
 */
/**
 * delete = <array>
 * insert = <array, object>
 * set = <array, object>
 */

/*
SELECT
    TABLES:
        0: <TABLE, "`People`">
        1: <JOIN, "`Names`", "`0`.`Name` = `1`.`Id`", true)

    COLUMNS:
        1: <COLUMN, 0, "`Id`">
        2: <COLUMN, 1, "CONCAT(`First`, '', `Last`)">

    CLAUSES:
        <FILTER, "`0`.`id` = :id">,
        <SORT, "`1`.`id`", true>,
        <SLICE, ":begin", ":end">,
        ...
*/

class Translator
{
    /** @var Map */
    private $map;

    public function __construct(Map $map)
    {
        $this->map = $map;
    }

    public function translate(Node $input)
    {
        $this->getExpression($input, $output);

        var_dump($output);
    }

    private function getExpression(Node $input, &$output)
    {
        return $this->getSelect($input, $output);
    }

    private function getSelect(Node $input, &$output)
    {
        $tokenType = $input->getNodeType();

        if ($tokenType !== Node::TYPE_FUNCTION) {
            return false;
        }

        /** @var FunctionNode $input */
        $name = $input->getName();

        switch ($name) {
            case 'average':
                return $this->getAverage($input, $output);

            case 'count':
                return $this->getCount($input, $output);

            case 'map':
                return $this->getMap($input, $output);

            case 'max':
                return $this->getMax($input, $output);

            case 'min':
                return $this->getMin($input, $output);

            case 'sum':
                return $this->getSum($input, $output);

            default:
                return false;
        }
    }

    private function getAverage(FunctionNode $input, &$output)
    {
        $arguments = $input->getArguments();
        $argument = array_shift($arguments);

        return $this->getArray($argument, $output);
    }

    private function getCount(FunctionNode $input, &$output)
    {
        $arguments = $input->getArguments();
        $argument = array_shift($arguments);

        return $this->getArray($argument, $output);
    }

    private function getMap(FunctionNode $input, &$output)
    {
        return false;
    }

    private function getMax(FunctionNode $input, &$output)
    {
        $arguments = $input->getArguments();
        $argument = array_shift($arguments);

        return $this->getArray($argument, $output);
    }

    private function getMin(FunctionNode $input, &$output)
    {
        $arguments = $input->getArguments();
        $argument = array_shift($arguments);

        return $this->getArray($argument, $output);
    }

    private function getSum(FunctionNode $input, &$output)
    {
        $arguments = $input->getArguments();
        $argument = array_shift($arguments);

        return $this->getArray($argument, $output);
    }

    private function getArray(Node $input, &$output)
    {
        $tokenType = $input->getNodeType();

        switch ($tokenType) {
            case Node::TYPE_FUNCTION:
                /** @var FunctionNode $input */
                return $this->getArrayFunction($input, $output);

            case Node::TYPE_PROPERTY:
                /** @var PropertyNode $input */
                return $this->getArrayProperty($input, $output);

            default:
                return false;
        }
    }

    private function getArrayFunction(FunctionNode $input, &$output)
    {
        $name = $input->getName();

        switch ($name) {
            case 'filter':
                return $this->getFilter($input, $output);

            case 'sort':
                return $this->getSort($input, $output);

            case 'slice':
                return $this->getSlice($input, $output);

            default:
                return false;
        }
    }

    private function getFilter(FunctionNode $input, &$output)
    {
        return false;
    }

    private function getSort(FunctionNode $input, &$output)
    {
        return false;
    }

    private function getSlice(FunctionNode $input, &$output)
    {
        return false;
    }

    private function getArrayProperty(PropertyNode $input, &$output)
    {
        echo "getProperty\n";
        return false;
    }
}
