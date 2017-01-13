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

use Datto\Cinnabari\Language\Functions;
use Datto\Cinnabari\Language\Properties;
use Datto\Cinnabari\Language\Types;

class PropertyResolver
{
    /** @var Functions */
    private $functions;

    /** @var Properties */
    private $properties;

    public function __construct(Functions $functions, Properties $properties)
    {
        $this->functions = $functions;
        $this->properties = $properties;
    }

    public function resolve($request)
    {
        $class = null;

        return $this->getToken($request, $class);
    }

    private function getToken($token, &$class)
    {
        switch ($token[0]) {
            case Parser::TYPE_PARAMETER:
                return $token;

            case Parser::TYPE_PROPERTY:
                return $this->getPropertyToken($token, $class);

            case Parser::TYPE_FUNCTION:
                return $this->getFunctionToken($token, $class);

            default: // Parser::TYPE_OBJECT:
                return $this->getObjectToken($token, $class);
        }
    }

    private function getPropertyToken($token, &$class)
    {
        $type = null;

        foreach ($token[1] as $property) {
            $type = $this->properties->getType($class, $property);
            self::updateClass($type, $class);
        }

        return array(Parser::TYPE_PROPERTY, $token[1], $type);
    }

    private static function updateClass($token, &$class)
    {
        if (is_string($token)) {
            $class = $token;
            return;
        }

        if (!is_array($token)) {
            return;
        }

        $type = $token[0];

        if (($type === Types::TYPE_OBJECT) || ($type === Types::TYPE_ARRAY)) {
            self::updateClass($token[1], $class);
        }
    }

    private function getFunctionToken($token, &$class)
    {
        $function = $token[1];
        $arguments = $token[2];

        $newArguments = array();

        if ($this->functions->isMapFunction($function)) {
            $argument = array_shift($arguments);
            $newArguments[] = $this->getToken($argument, $class);
        }

        foreach ($arguments as $argument) {
            $argumentClass = $class;
            $newArguments[] = $this->getToken($argument, $argumentClass);
        }

        return array(Parser::TYPE_FUNCTION, $function, $newArguments);
    }

    private function getObjectToken($token, $class)
    {
        $object = $token[1];

        $newObject = array();

        foreach ($object as $key => $expression) {
            $expressionClass = $class;
            $newObject[$key] = $this->getToken($expression, $expressionClass);
        }

        return array(Parser::TYPE_OBJECT, $newObject);
    }
}
