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

namespace Datto\Cinnabari;

use Datto\Cinnabari\Exception\TranslatorException;

class Translator
{
    const TYPE_PARAMETER = 1;
    const TYPE_FUNCTION = 3;
    const TYPE_OBJECT = 4;
    const TYPE_LIST = 5;
    const TYPE_TABLE = 6;
    const TYPE_JOIN = 7;
    const TYPE_VALUE = 8;

    /** @var array */
    private $schema;

    public function __construct($schema)
    {
        $this->schema = $schema;
    }

    public function translateIgnoringObjects($request)
    {
        return $this->translate($request, false);
    }

    public function translateIncludingObjects($request)
    {
        return $this->translate($request, true);
    }

    private function translate($request, $shouldTranslateKeys)
    {
        $this->getExpression('Database', null, $request, $shouldTranslateKeys, $expression);

        return $expression;
    }

    private function getExpression($class, $table, $tokens, $shouldTranslateKeys, &$output)
    {
        foreach ($tokens as $token) {
            $type = $token[0];

            switch ($type) {
                case Parser::TYPE_PARAMETER:
                    $parameter = $token[1];
                    self::getParameter($parameter, $output);
                    break;

                case Parser::TYPE_PROPERTY:
                    $property = $token[1];
                    $this->getProperty($class, $table, $property, false, $output);
                    break;

                case Parser::TYPE_FUNCTION:
                    self::scanFunction($token, $function, $arguments);
                    $this->getFunction($class, $table, $function, $arguments, $shouldTranslateKeys, $output);
                    break;

                default: // Parser::TYPE_OBJECT:
                    $object = $token[1];
                    $this->getObject($shouldTranslateKeys, $class, $table, $object, $output);
                    break;
            }
        }
    }

    private static function getParameter($parameter, &$output)
    {
        $output[] = array(
            self::TYPE_PARAMETER => $parameter
        );
    }

    private function getProperty(&$class, &$table, $property, $isContextual, &$output)
    {
        list($type, $path) = $this->getPropertyDefinition($class, $property);
        $isPrimitiveProperty = is_int($type);

        if ($table === null) {
            $list = array_shift($path);
            $this->getList($table, $list, $output);
        }

        $value = $isPrimitiveProperty ? array_pop($path) : null;

        foreach ($path as $connection) {
            $this->getConnection($table, $connection, $isContextual, $output);
        }

        if ($isPrimitiveProperty) {
            $this->getValue($table, $value, $type, $output);
        } else {
            $class = $type;
        }
    }

    private function getList(&$table, $list, &$output)
    {
        list($table, $id, $hasZero) = $this->getListDefinition($list);

        $output[] = array(
            self::TYPE_TABLE => array(
                'table' => $table,
                'id' => $id,
                'hasZero' => $hasZero
            )
        );
    }

    private function getConnection(&$table, $connection, $isContextual, &$output)
    {
        $definition = $this->getConnectionDefinition($table, $connection);

        $output[] = array(
            self::TYPE_JOIN => array(
                'tableA' => $table,
                'tableB' => $definition[0],
                'expression' => $definition[1],
                'id' => $definition[2],
                'hasZero' => $definition[3],
                'hasMany' => $definition[4],
                'isContextual' => $isContextual
            )
        );

        $table = $definition[0];
    }

    private function getValue($table, $value, $type, &$output)
    {
        list($expression, $hasZero) = $this->getValueDefinition($table, $value);

        $output[] = array(
            self::TYPE_VALUE => array(
                'table' => $table,
                'expression' => $expression,
                'type' => $type,
                'hasZero' => $hasZero
            )
        );
    }

    private function getFunction(&$class, &$table, $name, $arguments, $shouldTranslateKeys, &$output)
    {
        switch ($name) {
            case 'average':
            case 'count':
            case 'delete':
            case 'filter':
            case 'get':
            case 'insert':
            case 'max':
            case 'min':
            case 'set':
            case 'slice':
            case 'sort':
            case 'sum':
                $this->getArrayFunction($class, $table, $name, $arguments, $shouldTranslateKeys, $output);
                break;

            default:
                $this->getOtherFunction($class, $table, $name, $arguments, $shouldTranslateKeys, $output);
        }
    }

    private function getOtherFunction(&$class, &$table, $function, $arguments, $shouldTranslateKeys, &$output)
    {
        $output[] = array(
            self::TYPE_FUNCTION => array(
                'function' => $function,
                'arguments' => $this->translateArray($class, $table, $arguments, $shouldTranslateKeys)
            )
        );
    }

    private function getArrayFunction(&$class, &$table, $function, $arguments, $shouldTranslateKeys, &$output)
    {
        $firstArgument = array_shift($arguments);

        if (!isset($firstArgument) || (count($firstArgument) < 1)) {
            throw TranslatorException::unknownContext($function, $arguments);
        }

        $firstArgument = reset($firstArgument);

        if (!isset($firstArgument) || (count($firstArgument) < 2)) {
            throw TranslatorException::unknownContext($function, $arguments);
        }

        $firstArgumentType = $firstArgument[0];

        if ($firstArgumentType === Parser::TYPE_PROPERTY) {
            $property = $firstArgument[1];
            $this->getProperty($class, $table, $property, true, $output);
            $this->getOtherFunction($class, $table, $function, $arguments, $shouldTranslateKeys, $output);
            return $property;
        } else {
            self::scanFunction($firstArgument, $childFunction, $childArguments);

            $property = $this->getArrayFunction(
                $class,
                $table,
                $childFunction,
                $childArguments,
                $shouldTranslateKeys,
                $output
            );

            $output[] = array(
                self::TYPE_FUNCTION => array(
                    'function' => $function,
                    'arguments' => $this->translateArray(
                        $class,
                        $table,
                        $arguments,
                        $shouldTranslateKeys
                    )
                )
            );

            return $property;
        }
    }

    private function getObject($shouldTranslateKeys, &$class, &$table, $object, &$output)
    {
        if ($shouldTranslateKeys) {
            $output[] = array(
                self::TYPE_LIST => $this->translateKeysAndArray(
                    $class,
                    $table,
                    $object,
                    $shouldTranslateKeys
                )
            );
        } else {
            $output[] = array(
                self::TYPE_OBJECT => $this->translateArray(
                    $class,
                    $table,
                    $object,
                    $shouldTranslateKeys
                )
            );
        }
    }

    private function translateArray(&$class, &$table, $input, $shouldTranslateKeys)
    {
        $output = array();

        foreach ($input as $key => $value) {
            $this->getExpression($class, $table, $value, $shouldTranslateKeys, $output[$key]);
        }

        return $output;
    }

    private function translateKeysAndArray(&$class, &$table, $input, $shouldTranslateKeys)
    {
        $output = array();

        foreach ($input as $key => $value) {
            $translatedKey = array();
            $translatedValue = array();

            $propertyList = self::stringToPropertyList($key);

            $this->getExpression(
                $class,
                $table,
                $propertyList,
                $shouldTranslateKeys,
                $translatedKey
            );

            $this->getExpression(
                $class,
                $table,
                $value,
                $shouldTranslateKeys,
                $translatedValue
            );

            $output[] = array(
                'property' => $translatedKey,
                'value' => $translatedValue
            );
        }

        return $output;
    }

    private function getPropertyDefinition($class, $property)
    {
        $definition = &$this->schema['classes'][$class][$property];

        if ($definition === null) {
            throw TranslatorException::unknownProperty($class, $property);
        }

        $type = reset($definition);
        $path = array_slice($definition, 1);

        return array($type, $path);
    }

    private function getListDefinition($list)
    {
        $definition = &$this->schema['lists'][$list];

        if ($definition === null) {
            throw TranslatorException::unknownList($list);
        }

        return $definition;
    }

    private function getConnectionDefinition($table, $connection)
    {
        $definition = &$this->schema['connections'][$table][$connection];

        if ($definition === null) {
            throw TranslatorException::unknownConnection($table, $connection);
        }

        return $definition;
    }

    private function getValueDefinition($table, $value)
    {
        $definition = &$this->schema['values'][$table][$value];

        if ($definition === null) {
            throw TranslatorException::unknownValue($table, $value);
        }

        return $definition;
    }

    private static function stringToPropertyList($string)
    {
        // TODO: makes an assumption about the format of the Parser's output
        return array_map(
            function ($property) {
                return array(Parser::TYPE_PROPERTY, $property);
            },
            explode('.', $string)
        );
    }

    private static function scanFunction($token, &$function, &$arguments)
    {
        $function = $token[1];
        $arguments = array_slice($token, 2);
    }
}
