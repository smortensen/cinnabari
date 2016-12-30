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

use Datto\Cinnabari\Language\Types;

class Resolver
{
    const VALUE_NULL = 0;
    const VALUE_BOOLEAN = 1;
    const VALUE_INTEGER = 2;
    const VALUE_FLOAT = 3;
    const VALUE_STRING = 4;
    // array
    // object
    // function

    // TODO: infer this from the function signatures
    /** @var array */
    private static $arrayFunctions = array(
        'average' => true,
        'count' => true,
        'delete' => true,
        'filter' => true,
        'get' => true,
        'insert' => true,
        'max' => true,
        'min' => true,
        'set' => true,
        'slice' => true,
        'sort' => true,
        'sum' => true
    );

    /** @var array */
    private $schema;

    public function __construct($schema)
    {
        $this->schema = $schema;
    }

    public function resolve($token)
    {
        $type = $token[0];

        switch ($type) {
            case Parser::TYPE_PARAMETER:
                $parameter = $token[1];
                return $this->getParameterToken($parameter);

            case Parser::TYPE_PROPERTY:
                $mysql = $token[1];
                return $this->getPropertyToken($mysql);

            case Parser::TYPE_FUNCTION:
                $function = $token[1];
                $arguments = $token[2];
                return $this->getFunctionToken($function, $arguments);

            default: // Parser::TYPE_OBJECT:
                $object = $token[1];
                return $this->getObjectToken($object);
        }
    }

    private function getParameterToken($parameter)
    {
        $type = null;

        return array(Parser::TYPE_PARAMETER, $parameter, $type);
    }

    private function getPropertyToken($mysql)
    {
        $type = self::getPropertyType($mysql);

        return array(Parser::TYPE_PROPERTY, $mysql, $type);
    }

    private function getFunctionToken($function, $argumentsOld)
    {
        $argumentsNew = array();

        if (self::isArrayFunction($function)) {
            $argument = array_shift($argumentsOld);
            $argumentsNew[] = $this->resolve($argument);
        }

        foreach ($argumentsOld as $argument) {
            $argumentsNew[] = $this->resolve($argument);
        }

        $type = null;

        return array(Parser::TYPE_FUNCTION, $function, $argumentsNew, $type);
    }

    private function getObjectToken($objectOld)
    {
        $objectNew = array();

        foreach ($objectOld as $key => $value) {
            $objectNew[$key] = $this->resolve($value);
        }

        $type = null;

        return array(Parser::TYPE_OBJECT, $objectOld, $type);
    }

    private static function isArrayFunction($function)
    {
        return isset(self::$arrayFunctions[$function]);
    }

    private static function getPropertyType($tokens)
    {
        $type = null;

        foreach ($tokens as $token) {
            switch ($token['token']) {
                case Translator::MYSQL_TABLE:
                    $type = self::getMysqlTableType(Types::TYPE_OBJECT);
                    break;

                case Translator::MYSQL_JOIN:
                    $type = null;
                    break;

                case Translator::MYSQL_VALUE:
                    $type = self::getMysqlValueType($token);
                    break;

                default:
                    return null;
            }
        }

        return $type;
    }

    private static function getMysqlTableType($type)
    {
        return array(Types::TYPE_ARRAY, $type);
    }

    private static function getMysqlValueType($token)
    {
        $type = $token['type'];
        $isNullable = $token['isNullable'];

        if ($isNullable) {
            $type = array(Types::TYPE_OR, Types::TYPE_NULL, $type);
        }

        return $type;
    }
}
