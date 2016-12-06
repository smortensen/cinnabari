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
 * @author Anthony Liu <aliu@datto.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2016 Datto, Inc.
 */

namespace Datto\Cinnabari\Compiler;

use Datto\Cinnabari\Exception\CompilerException;
use Datto\Cinnabari\Mysql\AbstractMysql;
use Datto\Cinnabari\Mysql\Column;
use Datto\Cinnabari\Mysql\Dot;
use Datto\Cinnabari\Mysql\Functions\Average;
use Datto\Cinnabari\Mysql\Functions\Count;
use Datto\Cinnabari\Mysql\Functions\Max;
use Datto\Cinnabari\Mysql\Functions\Min;
use Datto\Cinnabari\Mysql\Functions\Sum;
use Datto\Cinnabari\Mysql\Identifier;
use Datto\Cinnabari\Mysql\Literals\True;
use Datto\Cinnabari\Mysql\Parameter;
use Datto\Cinnabari\Mysql\Table;
use Datto\Cinnabari\Mysql\Statements\Select;
use Datto\Cinnabari\Php\Output;
use Datto\Cinnabari\Translator;

/**
 * Class GetCompiler
 * @package Datto\Cinnabari
 */
class GetCompiler extends AbstractCompiler
{
    /** @var string */
    private $phpOutput;

    /** @var Select */
    protected $mysql;

    /** @var Select */
    protected $subquery;

    /** @var array */
    private $table;

    public function __construct($schema, $signatures)
    {
        parent::__construct($schema, $signatures);
    }

    public function compile($request)
    {
        $topLevelFunction = self::getTopLevelFunction($request);

        $translator = new Translator($this->schema);
        $translatedRequest = $translator->translateIgnoringObjects($request);

        $optimizedRequest = self::optimize($topLevelFunction, $translatedRequest);
        $types = self::getTypes($this->signatures, $optimizedRequest);

        $this->initialize($optimizedRequest);
        $this->enterTable($id, $hasZero);
        $this->getFunctionSequence($topLevelFunction, $id, $hasZero);

        $mysql = $this->mysql->getMysql();
        $phpInput = $this->input->getPhp($types);
        $phpOutput = $this->phpOutput;

        return array($mysql, $phpInput, $phpOutput);
    }

    private function initialize($request)
    {
        $mysql = new Select();
        $this->parentReset($request, $mysql);
        $this->phpOutput = null;
    }

    private function enterTable(&$id, &$hasZero)
    {
        $firstElement = array_shift($this->request);
        list(, $token) = each($firstElement);

        $this->table = $token;

        $table = new Table($token['table']);
        $this->context = $this->mysql->setTable($table);
        $id = $token['id'];
        $hasZero = $token['hasZero'];

        return true;
    }

    private function getFunctionSequence($topLevelFunction, $id, $hasZero)
    {
        $idAlias = null;

        if ($topLevelFunction === 'get') {
            $idAlias = $this->mysql->addValue($this->context, $id);
        }
            
        $this->getOptionalFilterFunction();
        $this->getOptionalSortFunction();
        $this->getOptionalSliceFunction();

        if (!isset($this->request) || count($this->request) === 0) {
            throw CompilerException::badGetArgument($this->request);
        }

        if ($this->readFork()) {
            return $this->getFunctionSequence($topLevelFunction, null, null);
        }

        $this->request = reset($this->request);

        if ($this->readGet()) {
            $this->phpOutput = Output::getList($idAlias, $hasZero, true, $this->phpOutput);
            return true;
        }

        if ($this->readCount()) {
            return true;
        }

        if ($this->readParameterizedAggregator($topLevelFunction)) {
            return true;
        }

        throw CompilerException::invalidMethodSequence($this->request);
    }

    private function readFork()
    {
        $token = reset($this->request);

        if (!self::scanFunction($token, $name, $arguments)) {
            return false;
        }

        if ($name !== 'fork') {
            return false;
        }

        array_shift($this->request);

        $this->subquery = $this->mysql;
        $this->mysql = new Select();
        $this->subqueryContext = $this->context;
        $this->context = $this->mysql->setTable($this->subquery);

        return true;
    }

    private function getSubtractiveParameters($nameA, $nameB, &$outputA, &$outputB)
    {
        $idA = $this->input->useSliceBeginArgument($nameA, self::IS_REQUIRED);
        $idB = $this->input->useSliceEndArgument($nameA, $nameB, self::IS_REQUIRED);

        if (($idA === null) || ($idB === null)) {
            return false;
        }

        $outputA = new Parameter($idA);
        $outputB = new Parameter($idB);
        return true;
    }

    private function readExpression()
    {
        if (!isset($this->request) || (count($this->request) < 1)) {
            return false;
        }

        $firstElement = reset($this->request);
        list($tokenType, $token) = each($firstElement);

        if (!isset($token)) {
            return false;
        }

        switch ($tokenType) {
            case Translator::TYPE_JOIN:
                $this->setRollbackPoint();
                $this->handleJoin($token);
                array_shift($this->request);

                return $this->conditionallyRollback(
                    $this->readExpression()
                );

            case Translator::TYPE_VALUE:
                return $this->readProperty();

            case Translator::TYPE_OBJECT:
                return $this->readObject();

            case Translator::TYPE_FUNCTION:
                return $this->readFunction();

            default:
                return false;
        }
    }

    private function readProperty()
    {
        $firstElement = reset($this->request);
        list(, $token) = each($firstElement);

        $actualType = $token['type'];
        $column = $token['expression'];
        $hasZero = $token['hasZero'];

        $tableId = $this->context;

        $columnId = $this->mysql->addValue($tableId, $column);
        $this->phpOutput = Output::getValue($columnId, $hasZero, $actualType);

        return true;
    }

    private function readObject()
    {
        if (!self::scanObject($this->request, $object)) {
            return false;
        }

        $properties = array();

        $initialContext = $this->context;
        foreach ($object as $label => $this->request) {
            $this->context = $initialContext;
            if (!$this->readExpression()) {
                return false;
            }

            $properties[$label] = $this->phpOutput;
        }

        $this->phpOutput = Output::getObject($properties);
        return true;
    }

    private function getGet($request)
    {
        $this->request = $request;

        if (!isset($this->contextJoin)) {
            throw CompilerException::badGetArgument($this->request);
        }

        return $this->getFunctionSequence(
            'get',
            $this->contextJoin['id'],
            $this->contextJoin['hasZero']
        );
    }

    private function readGet()
    {
        if (!self::scanFunction($this->request, $name, $arguments)) {
            return false;
        }

        if ($name !== 'get') {
            return false;
        }

        if (!isset($arguments) || (count($arguments) !== 1)) {
            throw CompilerException::badGetArgument($this->request);
        }

        // at this point they definitely intend to use a get function
        $this->request = reset($arguments);

        if (!$this->readExpression()) {
            throw CompilerException::badGetArgument($this->request);
        }

        return true;
    }

    private function readCount()
    {
        if (!self::scanFunction($this->request, $name, $arguments)) {
            return false;
        }

        if ($name !== 'count') {
            return false;
        }

        if (!isset($arguments) || (count($arguments) > 0)) {
            throw CompilerException::badGetArgument($this->request);
        }

        $this->request = null;

        $countExpression = $this->getCountExpression();
        $columnId = $this->mysql->addExpression($countExpression);

        $this->phpOutput = Output::getValue($columnId, false, Output::TYPE_INTEGER);

        return true;
    }

    private function getCountExpression()
    {
        if (isset($this->subquery)) {
            $true = new True();

            $expressionId = $this->subquery->addExpression($true);

            $contextIdentifier = new Identifier($this->context);
            $expressionIdentifier = new Identifier($expressionId);

            $columnToSelect = Select::getAbsoluteExpression(
                $contextIdentifier->getMysql(),
                $expressionIdentifier->getMysql()
            );

            $expressionInner = new Column($columnToSelect);
        } else {
            $tableAlias = $this->context;
            $tableIdName = substr($this->table['id'], 1, -1);

            $expressionInner = new Identifier($tableAlias, $tableIdName);
        }

        return new Count($expressionInner);
    }

    private function readParameterizedAggregator($functionName)
    {
        if (!self::scanFunction($this->request, $name, $arguments)) {
            return false;
        }

        if (!isset($arguments) || (count($arguments) !== 1)) {
            throw CompilerException::badGetArgument($this->request);
        }

        // at this point they definitely intend to use a parameterized aggregator
        $this->request = reset($arguments);
        if (!isset($this->request) || (count($this->request) === 0)) {
            throw CompilerException::badGetArgument($this->request);
        }

        $this->request = $this->followJoins($this->request);
        if (!isset($this->request) || (count($this->request) === 0)) {
            throw CompilerException::badGetArgument($this->request);
        }

        $this->request = reset($this->request);
        if (!$this->scanProperty($this->request, $table, $name, $type, $hasZero)) {
            throw CompilerException::badGetArgument($this->request);
        }

        // get the aggregator's argument's corresponding column
        $tableId = $this->context;
        $tableAliasIdentifier = new Identifier($tableId);
        $columnExpression = Select::getAbsoluteExpression($tableAliasIdentifier->getMysql(), $name);
        $column = new Column($columnExpression);
        $expressionToAggregate = $column;

        if (isset($this->subquery)) {
            $expressionId = $this->subquery->addExpression($column);

            $contextIdentifier = new Identifier($this->context);
            $expressionIdentifier = new Identifier($expressionId);

            $columnToSelect = Select::getAbsoluteExpression(
                $contextIdentifier->getMysql(),
                $expressionIdentifier->getMysql()
            );

            $expressionToAggregate = new Column($columnToSelect);
        }

        switch ($functionName) {
            case 'average':
                $aggregator = new Average($expressionToAggregate);
                $type = Output::TYPE_FLOAT;
                break;

            case 'sum':
                $aggregator = new Sum($expressionToAggregate);
                break;

            case 'min':
                $aggregator = new Min($expressionToAggregate);
                break;

            case 'max':
                $aggregator = new Max($expressionToAggregate);
                break;

            default:
                throw CompilerException::unknownRequestType($functionName);
        }

        $columnId = $this->mysql->addExpression($aggregator);
        $this->phpOutput = Output::getValue($columnId, true, $type);

        return true;
    }

    private function readFunction()
    {
        if (!self::scanFunction(reset($this->request), $name, $arguments)) {
            return false;
        }

        switch ($name) {
            case 'get':
                return $this->getGet($this->request);

            case 'uppercase':
            case 'lowercase':
            case 'substring':
            case 'length':
            case 'plus':
            case 'minus':
            case 'times':
            case 'divides':
                if (!$this->getExpression(
                    $this->request,
                    self::IS_REQUIRED,
                    $expression,
                    $type
                )) {
                    return false;
                }

                /** @var AbstractMysql $expression */
                $columnId = $this->mysql->addExpression($expression);

                $isNullable = true; // TODO: assumption
                $this->phpOutput = Output::getValue(
                    $columnId,
                    $isNullable,
                    $type
                );

                return true;

            default:
                return false;
        }
    }

    protected function getOptionalFilterFunction()
    {
        if (!self::scanFunction(reset($this->request), $name, $arguments)) {
            return false;
        }

        if ($name !== 'filter') {
            return false;
        }

        if (!isset($arguments) || (count($arguments) === 0)) {
            throw CompilerException::noFilterArguments($this->request);
        }

        if (!$this->getExpression($arguments[0], self::IS_REQUIRED, $where, $type)) {
            throw CompilerException::badFilterExpression(
                $this->context,
                $arguments[0]
            );
        }

        $this->mysql->setWhere($where);

        array_shift($this->request);

        return true;
    }

    protected function getExpression($arrayToken, $hasZero, &$expression, &$type)
    {
        $firstElement = reset($arrayToken);
        list($tokenType, $token) = each($firstElement);

        $context = $this->context;
        $result = false;

        switch ($tokenType) {
            case Translator::TYPE_JOIN:
                $this->setRollbackPoint();
                $this->handleJoin($token);
                array_shift($arrayToken);
                $result = $this->conditionallyRollback(
                    $this->getExpression($arrayToken, $hasZero, $expression, $type)
                );
                break;

            case Translator::TYPE_PARAMETER:
                $result = $this->getParameter($token, $hasZero, $expression);
                break;

            case Translator::TYPE_VALUE:
                $result = $this->getProperty($token, $expression, $type);
                if (isset($this->subquery) && $result) {
                    $subqueryValueId = $this->subquery->addValue(
                        $this->subqueryContext,
                        $token['expression']
                    );

                    $contextIdentifier = new Identifier($this->context);
                    $subqueryValueIdentifier = new Identifier($subqueryValueId);

                    $columnExpression = Select::getAbsoluteExpression(
                        $contextIdentifier->getMysql(),
                        $subqueryValueIdentifier->getMysql()
                    );

                    $expression = new Column($columnExpression);
                }
                break;

            case Translator::TYPE_FUNCTION:
                $name = $token['function'];
                $arguments = $token['arguments'];
                $result = $this->getFunction($name, $arguments, $hasZero, $expression, $type);
                break;

            default:
                // TODO
        }

        $this->context = $context;
        return $result;
    }

    private function getOptionalSortFunction()
    {
        if (!self::scanFunction(reset($this->request), $name, $arguments)) {
            return false;
        }

        if ($name !== 'sort') {
            return false;
        }

        // at this point, we're sure they want to sort
        if (!isset($arguments) || count($arguments) !== 1) {
            // TODO: add an explanation of the missing argument, or link to the documentation
            throw CompilerException::noSortArguments($this->request);
        }

        $state = array($this->request, $this->context);

        // consume all of the joins
        $this->request = $arguments[0];
        $this->request = $this->followJoins($this->request);

        if (!$this->scanProperty(reset($this->request), $table, $name, $type, $hasZero)) {
            return false;
        }

        if (isset($this->subquery)) {
            $subqueryContextAlias = $this->subquery->addValue($this->subqueryContext, $name);
            $subqueryContextIdentifier = new Identifier($subqueryContextAlias);
            $name = $subqueryContextIdentifier->getMysql();
        }

        $this->mysql->setOrderBy($this->context, $name, true);

        list($this->request, $this->context) = $state;

        array_shift($this->request);

        return true;
    }

    private function getOptionalSliceFunction()
    {
        if (!self::scanFunction(reset($this->request), $name, $arguments)) {
            return false;
        }

        if ($name !== 'slice') {
            return false;
        }

        if (!isset($arguments) || count($arguments) !== 2) {
            throw CompilerException::badSliceArguments($this->request);
        }

        if (
            !$this->scanParameter($arguments[0], $nameA) ||
            !$this->scanParameter($arguments[1], $nameB)
        ) {
            return false;
        }

        if (!$this->getSubtractiveParameters($nameA, $nameB, $start, $end)) {
            return false;
        }

        $this->mysql->setLimit($start, $end);

        array_shift($this->request);

        return true;
    }

    protected function getProperty($propertyToken, &$output, &$type)
    {
        $column = $propertyToken['expression'];
        $type = $propertyToken['type'];

        $tableId = $this->context;
        $tableAliasIdentifier = "`{$tableId}`";
        $columnExpression = Select::getAbsoluteExpression($tableAliasIdentifier, $column);
        $output = new Column($columnExpression);

        return true;
    }

    private static function scanObject($input, &$object)
    {
        // scan the next token of the supplied arrayToken
        $input = reset($input);
        list($tokenType, $token) = each($input);
        if ($tokenType !== Translator::TYPE_OBJECT) {
            return false;
        }

        $object = $token;
        return true;
    }
}
