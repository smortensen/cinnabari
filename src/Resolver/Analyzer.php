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
use Datto\Cinnabari\Language\Types;
use Datto\Cinnabari\Resolver\Tokens\Token;
use Datto\Cinnabari\Resolver\Tokens\FunctionToken;
use Datto\Cinnabari\Resolver\Tokens\ObjectToken;
use Datto\Cinnabari\Resolver\Tokens\PropertyToken;

class Analyzer
{
    /** @var Functions */
    private $functions;

    /** @var Properties */
    private $properties;

    /** @var array */
    private $anonymousObjects;

    /** @var Request */
    private $request;

    /** @var array */
    private $constraints;

    /**
     * Resolver constructor.
     *
     * @param Functions $functions
     * @param Properties $properties
     */
    public function __construct(Functions $functions, Properties $properties)
    {
        $this->functions = $functions;
        $this->properties = $properties;
        $this->anonymousObjects = array();
    }

    public function analyze(Request $request)
    {
        $this->request = $request;
        $this->constraints = array();

        $context = array(Types::TYPE_OBJECT, 'Database');
        $this->getConstraints($context, 0);

        return $this->constraints;
    }

    private function getConstraints(&$context, $id)
    {
        $token = $this->request->getToken($id);
        $type = $token->getTokenType();

        if ($type === Token::TYPE_PROPERTY) {
            $this->getPropertyConstraints($context, $id);
        } elseif ($type === Token::TYPE_FUNCTION) {
            $this->getFunctionConstraints($context, $id);
        } elseif ($type === Token::TYPE_OBJECT) {
            $this->getObjectConstraints($context, $id);
        }
    }

    private function getPropertyConstraints(&$context, $id)
    {
        /** @var PropertyToken $token */
        $token = $this->request->getToken($id);
        $path = $token->getPath();

        $context = $this->getPropertyType($context, $path);

        $this->constraints[] = self::getConstraintFromType($context, $id);
    }

    private function getPropertyType($dataType, array $path)
    {
        foreach ($path as $name) {
            if ($dataType[0] !== Types::TYPE_OBJECT) {
                throw Exception::invalidPropertyAccess($dataType, $name);
            }

            $dataType = $this->properties->getType($dataType[1], $name);
        }

        return $dataType;
    }

    private static function getConstraintFromType($type, $id)
    {
        if ($type[0] === Types::TYPE_OR) {
            $types = array_slice($type, 1);
        } else {
            $types = array($type);
        }

        $options = array();

        foreach ($types as $type) {
            $options[] = array(
                $id => $type
            );
        }

        return $options;
    }

    private function getFunctionConstraints(&$context, $id)
    {
        /** @var FunctionToken $token */
        $token = $this->request->getToken($id);
        $name = $token->getName();
        $arguments = $token->getArguments();

        $keys = $arguments;
        $keys[] = $id;

        if (0 < count($arguments)) {
            $argument = array_shift($arguments);

            $firstChildContext = $context;
            $this->getConstraints($firstChildContext, $argument);

            $isMapFunction = self::isObjectArray($firstChildContext);

            if ($isMapFunction) {
                $context = $firstChildContext[1];
            }

            foreach ($arguments as $argument) {
                $childContext = $context;
                $this->getConstraints($childContext, $argument);
            }

            if ($isMapFunction) {
                $context = $firstChildContext;
            }
        }

        $this->constraints[] = $this->getFunctionOptions($name, $keys);
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

    private function getObjectConstraints(&$context, $id)
    {
        /** @var ObjectToken $token */
        $token = $this->request->getToken($id);
        $properties = $token->getProperties();

        foreach ($properties as $childId) {
            $childContext = $context;
            $this->getConstraints($childContext, $childId);
        }

        $this->constraints[] = $this->getObjectOptions($id, $properties);
    }

    private function getObjectOptions($id, array $properties)
    {
        $signature = array();

        $option = array();

        $n = 0;

        foreach ($properties as $key => $childId) {
            $variableName = '$' . $n++;
            $signature[$key] = $variableName;
            $option[$childId] = $variableName;
        }

        $option[$id] = array(Types::TYPE_OBJECT, $signature);

        return array($option);
    }
}
