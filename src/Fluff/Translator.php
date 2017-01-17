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

namespace Datto\Cinnabari\Fluff;

use Datto\Cinnabari\Request\Language\Schema;
use Datto\Cinnabari\Request\Language\Types;

class Translator
{
    const MYSQL_TABLE = 1;
    const MYSQL_JOIN = 2;
    const MYSQL_VALUE = 3;

    private static $databaseClass = 'Database';

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

    /** @var Schema */
    private $schema;

    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    public function translate($request)
    {
        return $this->translateExpression(self::$databaseClass, null, $request);
    }

    private function translateExpression($class, $table, $token)
    {
        return $this->getExpression($class, $table, $token);
    }

    private function getExpression(&$class, &$table, $token)
    {
        $type = $token[0];

        switch ($type) {
            case Parser::TYPE_PARAMETER:
                return $token;

            case Parser::TYPE_PROPERTY:
                $properties = $token[1];
                return $this->getProperties($class, $table, $properties);

            case Parser::TYPE_FUNCTION:
                $function = $token[1];
                $arguments = $token[2];
                return $this->getFunction($class, $table, $function, $arguments);

            default: // Parser::TYPE_OBJECT:
                $object = $token[1];
                return $this->getObject($class, $table, $object);
        }
    }

    private function getProperties(&$class, &$table, $properties)
    {
        $mysql = array();

        foreach ($properties as $property) {
            $this->getProperty($class, $table, $property, $mysql);
        }

        return array(Parser::TYPE_PROPERTY, $mysql);
    }

    private function getProperty(&$class, &$table, $property, &$mysql)
    {
        list($type, $path) = $this->schema->getPropertyDefinition($class, $property);
        $isPrimitiveProperty = is_int($type);

        if ($table === null) {
            $list = array_shift($path);
            $mysql[] = $this->getTableToken($table, $list);
        }

        $value = $isPrimitiveProperty ? array_pop($path) : null;

        foreach ($path as $connection) {
            $mysql[] = $this->getJoinToken($table, $connection);
        }

        if ($isPrimitiveProperty) {
            $mysql[] = $this->getValueToken($table, $type, $value);
        } else {
            $class = $type;
        }

        return $mysql;
    }

    private function getFunction(&$class, &$table, $function, $argumentsInput)
    {
        $argumentsOutput = array();

        if (self::isArrayFunction($function)) {
            $argument = array_shift($argumentsInput);
            $argumentsOutput[] = $this->getExpression($class, $table, $argument);
        }

        foreach ($argumentsInput as $argument) {
            $argumentsOutput[] = $this->translateExpression($class, $table, $argument);
        }

        return array(Parser::TYPE_FUNCTION, $function, $argumentsOutput);
    }

    private static function isArrayFunction($function)
    {
        return isset(self::$arrayFunctions[$function]);
    }

    private function getObject($class, $table, $object)
    {
        foreach ($object as &$value) {
            $value = $this->translateExpression($class, $table, $value);
        }

        return array(Parser::TYPE_OBJECT, $object);
    }

    private function getTableToken(&$table, $list)
    {
        list($table, $id) = $this->schema->getListDefinition($list);

        return array(
            'token' => self::MYSQL_TABLE,
            'table' => $table,
            'id' => $this->getValueToken($table, Types::TYPE_STRING, $id)
        );
    }

    private function getJoinToken(&$table, $connection)
    {
        $definition = $this->schema->getConnectionDefinition($table, $connection);

        return array(
            'token' => self::MYSQL_JOIN,
            'table' => $definition[0], // destination table
            'id' => $definition[2],
            'expression' => $definition[1],
            'isNullable' => $definition[3],
            'isMany' => $definition[4]
        );
    }

    private function getValueToken($table, $type, $value)
    {
        list($expression, $isNullable) = $this->schema->getValueDefinition($table, $value);

        return array(
            'token' => self::MYSQL_VALUE,
            'value' => $expression,
            'type' => $type,
            'isNullable' => $isNullable
        );
    }
}
