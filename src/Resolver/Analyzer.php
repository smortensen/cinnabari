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

namespace Datto\Cinnabari\Resolver;

use Datto\Cinnabari\Exception;
use Datto\Cinnabari\Language\Properties;
use Datto\Cinnabari\Language\Functions;
use Datto\Cinnabari\Language\Request\Token;
use Datto\Cinnabari\Language\Request\FunctionToken;
use Datto\Cinnabari\Language\Request\ObjectToken;
use Datto\Cinnabari\Language\Request\PropertyToken;
use Datto\Cinnabari\Language\Types;

class Analyzer
{
    /** @var Functions */
    private $functions;

    /** @var Properties */
    private $properties;

    /** @var array */
    private $anonymousObjects;

    /** @var array */
    private $constraints;

    /**
     * @param Functions $functions
     * @param Properties $properties
     */
    public function __construct(Functions $functions, Properties $properties)
    {
        $this->functions = $functions;
        $this->properties = $properties;
        $this->anonymousObjects = array();
    }

    public function analyze(Token $token)
    {
        $this->constraints = array();

        $context = array(Types::TYPE_OBJECT, 'Database');
        $id = 0;

        $this->read($token, $context, $id);

        return $this->constraints;
    }

    private function read(Token $token, &$context, &$id)
    {
        $type = $token->getTokenType();

        if ($type === Token::TYPE_PROPERTY) {
            /** @var PropertyToken $token */
            $this->readProperty($token, $context, $id);
        } elseif ($type === Token::TYPE_FUNCTION) {
            /** @var FunctionToken $token */
            $this->readFunction($token, $context, $id);
        } elseif ($type === Token::TYPE_OBJECT) {
            /** @var ObjectToken $token */
            $this->readObject($token, $context, $id);
        }
    }

    private function readProperty(PropertyToken $property, &$context, &$id)
    {
        $path = $property->getPath();
        $this->updatePropertyContext($context, $path);

        $this->constraints[] = self::getConstraintFromDataType($context, $id++);
    }

    private function updatePropertyContext(&$dataType, array $names)
    {
        foreach ($names as $name) {
            if ($dataType[0] === Types::TYPE_OBJECT) {
                $dataType = $this->properties->getDataType($dataType[1], $name);
            } else {
                throw Exception::invalidPropertyAccess($dataType, $name);
            }
        }
    }

    private static function getConstraintFromDataType($dataType, $id)
    {
        if ($dataType[0] === Types::TYPE_OR) {
            $dataTypes = array_slice($dataType, 1);
        } else {
            $dataTypes = array($dataType);
        }

        $constraint = array();

        foreach ($dataTypes as $dataType) {
            $constraint[] = array(
                $id => $dataType
            );
        }

        return $constraint;
    }

    private function readFunction(FunctionToken $function, &$context, &$id)
    {
        $name = $function->getName();
        $keys = $this->getFunctionKeys($function, $context, $id);

        $this->constraints[] = $this->getFunctionOptions($name, $keys);
    }

    private function getFunctionKeys(FunctionToken $function, &$context, &$id)
    {
        $idFunction = $id++;

        $keys = array();

        $arguments = $function->getArguments();

        if (0 < count($arguments)) {
			$argument = array_shift($arguments);

			$keys[] = $id;
			$firstArgumentContext = $context;
			$this->read($argument, $firstArgumentContext, $id);

			$isArrayFunction = self::isObjectArray($firstArgumentContext);

			if ($isArrayFunction) {
				$context = $firstArgumentContext[1];
			}

			foreach ($arguments as $argument) {
				$keys[] = $id;
				$childContext = $context;
				$this->read($argument, $childContext, $id);
			}

			if ($isArrayFunction) {
				$context = $firstArgumentContext;
			}
		}

        $keys[] = $idFunction;

        return $keys;
    }

    private static function isObjectArray($type)
    {
        return ($type[0] == Types::TYPE_ARRAY) && ($type[1][0] === Types::TYPE_OBJECT);
    }

    private function getFunctionOptions($function, $keys)
    {
        $options = array();

        $keyCount = count($keys);

        foreach ($this->functions->getSignatures($function) as $signature) {
            if (count($signature) === $keyCount) {
                $options[] = array_combine($keys, $signature);
            }
        }

        return $options;
    }

    private function readObject(ObjectToken $object, &$context, &$id)
    {
        $option = array();
        $signature = array();

        $objectId = $id++;

        $n = 0;
        $properties = $object->getProperties();

        foreach ($properties as $key => $child) {
            $variableName = '$' . $n++;

            $signature[$key] = $variableName;
            $option[$id] = $variableName;

            $childContext = $context;
            $this->read($child, $childContext, $id);
        }

        $option[$objectId] = array(Types::TYPE_OBJECT, $signature);

        $this->constraints[] = array($option);
    }
}
