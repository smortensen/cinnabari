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

namespace Datto\Cinnabari\Phases\Translator\Parser;

use Datto\Cinnabari\Entities\Language\Types;
use Datto\Cinnabari\Entities\Mysql\Select;
use Datto\Cinnabari\Entities\Parameters;
use Datto\Cinnabari\Entities\Request\Request;
use Datto\Cinnabari\Entities\Request\FunctionRequest;
use Datto\Cinnabari\Entities\Request\ParameterRequest;
use Datto\Cinnabari\Entities\Request\PropertyRequest;
use Datto\Cinnabari\Phases\Compiler\PhpOutputCompiler;
use Datto\Cinnabari\Phases\Translator\Map;
use Datto\Cinnabari\Phases\Translator\Parser\Rules\FunctionRule;
use Datto\Cinnabari\Phases\Translator\Parser\Rules\ParameterRule;
use Datto\Cinnabari\Phases\Translator\Parser\Rules\PropertyRule;
use SpencerMortensen\Parser\Core\Parser as CoreParser;
use SpencerMortensen\Parser\ParserException;
use SpencerMortensen\Parser\Rule;

class Parser extends CoreParser
{
    /** @var Rule */
    private $rule;

    /** @var Map */
    private $map;

    /** @var string */
    protected $class;

    /** @var integer|null */
    protected $table;

    /** @var Request */
    protected $node;

    /** @var Select */
    protected $select;

    /** @var Parameters */
    protected $parameters;

    /** @var PhpOutputCompiler */
    protected $phpOutputCompiler;

    public function __construct(Rule $rule, Map $map)
    {
        $this->rule = $rule;
        $this->map = $map;
        $this->phpOutputCompiler = new PhpOutputCompiler();
    }

    protected function parse(Request $node)
    {
        $this->class = 'Database';
        $this->table = null;
        $this->node = $node;
        $this->select = new Select();
        $this->parameters = new Parameters();

        if (!$this->runRule($this->rule, $phpOutput)) {
            throw $this->parserException();
        }

        $phpOutput = $this->phpOutputCompiler->getRowsPhp($phpOutput);

        return array($this->select, $this->parameters, $phpOutput);
    }

    protected function runRule(Rule $rule, &$output = null)
    {
        if ($rule instanceof FunctionRule) {
            /** @var FunctionRule $rule */
            return $this->runFunctionRule($rule, $output);
        } elseif ($rule instanceof ParameterRule) {
            /** ParameterRule $rule */
            return $this->runParameterRule($rule, $output);
        } elseif ($rule instanceof PropertyRule) {
            /** PropertyRule $rule */
            return $this->runPropertyRule($rule, $output);
        } else {
            return parent::runRule($rule, $output);
        }
    }

    private function runFunctionRule(FunctionRule $rule, &$output)
    {
        $type = $this->node->getNodeType();

        if ($type !== Request::TYPE_FUNCTION) {
            return false;
        }

        /** @var FunctionRequest $functionNode */
        $functionNode = $this->node;

        if ($functionNode->getFunction() !== $rule->getFunction()) {
            return false;
        }

        $arguments = $functionNode->getArguments();
        $rules = $rule->getArguments();

        if (!$this->processArguments($arguments, $rules, $input)) {
            return false;
        }

        $this->node = $functionNode;
        $output = $this->formatOutput($rule, $input);
        return true;
    }

    private function processArguments(array $arguments, array $rules, array &$input = null)
    {
        $countArguments = count($arguments);
        $countRules = count($rules);

        if ($countArguments !== $countRules) {
            return false;
        }

        if ($countArguments === 0) {
            return true;
        }

        $state = $this->getState();
        $class = $this->class;
        $table = $this->table;

        $input = array();

        /** @var Request $firstArgument */
        $firstArgument = array_shift($arguments);
        $firstRule = array_shift($rules);

        $this->node = $firstArgument;

        if (!$this->runRule($firstRule, $input[])) {
            $this->setState($state);
            return false;
        }

        $isArrayFunction = self::isObjectArray($firstArgument);

        if ($isArrayFunction) {
            $class = $this->class;
            $table = $this->table;
        } else {
            $this->class = $class;
            $this->table = $table;
        }

        foreach ($arguments as $key => $argument) {
            $rule = $rules[$key];
            $this->node = $argument;

            if (!$this->runRule($rule, $input[])) {
                $this->setState($state);
                return false;
            }

            $this->class = $class;
            $this->table = $table;
        }

        return true;
    }

    private static function isObjectArray(Request $node)
    {
        $dataType = $node->getDataType();

        if (!is_array($dataType) || ($dataType[0] !== Types::TYPE_ARRAY)) {
            return false;
        }

        $subDataType = $dataType[1];

        return is_array($subDataType) && ($subDataType[0] === Types::TYPE_OBJECT);
    }

    private function runParameterRule(Rule $rule, &$output)
    {
        $type = $this->node->getNodeType();

        if ($type !== Request::TYPE_PARAMETER) {
            return false;
        }

        /** @var ParameterRequest $parameter */
        $parameter = $this->node;
        $name = $parameter->getName();
        $dataType = $parameter->getDataType();

        $this->parameters->addApiParameter($name, $dataType);

        $output = $this->node;
        return true;
    }

    private function runPropertyRule(Rule $rule, &$output)
    {
        $type = $this->node->getNodeType();

        if ($type !== Request::TYPE_PROPERTY) {
            return false;
        }

        /** @var PropertyRequest $apiNode */
        $apiNode = $this->node;
        $propertyNames = $apiNode->getPath();

        $databaseNodes = array();

        foreach ($propertyNames as $propertyName) {
            $databaseNodes = array_merge($databaseNodes, $this->map->map($this->class, $propertyName));
        }

        $input = array($databaseNodes);
        $output = $this->formatOutput($rule, $input);
        return true;
    }

    private function formatOutput(Rule $rule, array $input)
    {
        $callable = $rule->getCallable();

        if ($callable === null) {
            return $input;
        }

        return call_user_func_array($callable, $input);
    }

    private function parserException()
    {
        $ruleName = null;
        $state = null;

        return new ParserException($ruleName, $state);
    }

    protected function getState()
    {
        return array($this->class, $this->table, $this->node, clone $this->select, clone $this->parameters);
    }

    protected function setState($state)
    {
        list($this->class, $this->table, $this->node, $this->select, $this->parameters) = $state;
    }

    protected function setExpectation(Rule $rule = null)
    {
        // TODO
    }
}
