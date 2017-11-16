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

namespace Datto\Cinnabari\Phases\Translator;

use Datto\Cinnabari\Entities\Request\Request;
use Datto\Cinnabari\Entities\Request\ParameterRequest;
use Datto\Cinnabari\Entities\Mysql\AbstractNode as DatabaseNode;
use Datto\Cinnabari\Entities\Mysql\AbstractTable as DatabaseTable;
use Datto\Cinnabari\Entities\Mysql\Clauses\Limit;
use Datto\Cinnabari\Entities\Mysql\Clauses\OrderBy;
use Datto\Cinnabari\Entities\Mysql\Select;
use Datto\Cinnabari\Entities\Mysql\Value;
use Datto\Cinnabari\Phases\Translator\Parser\Parser;
use Datto\Cinnabari\Phases\Translator\Parser\Rules;

/**
 * expression = read
 * read = average | count | map | max | min | sum
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
class Translator extends Parser
{
    public function __construct(Map $map)
    {
        $grammar = <<<'EOS'
read: OR map count
map: FUNCTION map array column
array: OR table filter sort slice
table: PROPERTY
filter: FUNCTION filter array column
sort: FUNCTION sort array column
slice: FUNCTION slice array parameter parameter
parameter: PARAMETER
column: PROPERTY
count: FUNCTION count array
EOS;

        $rules = new Rules($this, $grammar);
        $rule = $rules->getRule('read');

        parent::__construct($rule, $map);
    }

    public function translate(Request $node)
    {
        return $this->parse($node);
    }

    public function getMap(DatabaseTable $table, Value $value)
    {
        $indexName = $table->getIndex();
        $index = new Value($indexName);
        $index->setContext($this->table);
        $this->select->add($this->table, $index);
        $indexAliasId = $this->select->getValueAlias($index);
        $indexPhp = $this->phpOutputCompiler->getValuePhp($indexAliasId);

        $this->select->add($this->table, $value);
        $valueAliasId = $this->select->getValueAlias($value);
        $mapDataType = $this->node->getDataType();
        $valueDataType = $mapDataType[1];

        $php = $this->phpOutputCompiler->getOutputPhp($valueAliasId, $valueDataType);
        $php = $this->phpOutputCompiler->getArrayPhp($php, $indexPhp);

        return $php;
    }

    public function getCount()
    {
        $table = $this->select->getTable($this->table);
        $index = $table->getIndex();
        $value = new Value("COUNT({$index})");
        $value->setContext($this->table);

        $this->select->add($this->table, $value);

        $inputAliasId = $this->select->getValueAlias($value);
        $outputDataType = $this->node->getDataType();

        return $this->phpOutputCompiler->getOutputPhp($inputAliasId, $outputDataType);
    }

    public function getTable(array $databaseNodes)
    {
        $databaseNode = null;

        foreach ($databaseNodes as $databaseNode) {
            $this->select->add($this->table, $databaseNode);
        }

        return $databaseNode;
    }

    public function getFilter(DatabaseTable $table, Value $value)
    {
        return $table;
    }

    public function getSort(DatabaseTable $table, Value $value)
    {
        $context = $value->getContext();
        $expression = $value->getExpression();

        $orderBy = new OrderBy($context, $expression, true);
        $this->select->addClause($orderBy);

        return $table;
    }

    public function getSlice(DatabaseTable $table, ParameterRequest $begin, ParameterRequest $end)
    {
        $beginPhp = self::getInputPhp($begin);
        $beginPhp = "max({$beginPhp}, 0)";
        $beginAlias = $this->parameters->addDatabaseParameter($beginPhp);

        $endPhp = self::getInputPhp($end);
        $endPhp = "({$beginPhp} < {$endPhp}) ? ({$endPhp} - {$beginPhp}) : 0";
        $endAlias = $this->parameters->addDatabaseParameter($endPhp);

        $limit = new Limit($beginAlias, $endAlias);
        $this->select->addClause($limit);

        return $table;
    }

    private static function getInputPhp(ParameterRequest $parameter)
    {
        $name = $parameter->getName();
        $namePhp = var_export($name, true);
        return "\$input[{$namePhp}]";
    }

    public function getColumn(array $databaseNodes)
    {
        foreach ($databaseNodes as $databaseNode) { /** @var DatabaseNode $databaseNode */
            $type = $databaseNode->getNodeType();

            if ($type === DatabaseNode::TYPE_VALUE) { /** @var Value $databaseNode */
                $databaseNode->setContext($this->table);
                return $databaseNode;
            }

            $this->select->add($this->table, $databaseNode);
        }

        return null;
    }
}
