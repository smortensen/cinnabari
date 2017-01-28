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

class Exception extends \Exception
{
    const QUERY_TYPE = 1;
    const QUERY_SYNTAX = 2;
    const PROPERTY_UNKNOWN = 3;
    const FUNCTION_UNKNOWN = 4;
    const PARAMETER_TYPE = 5;
    const PROPERTY_TYPE = 6;
    const FUNCTION_TYPE = 7;

    /** @var mixed */
    private $data;

    /**
     * @param int $code
     * @param mixed $data
     * @param string|null $message
     */
    public function __construct($code, $data = null, $message = null)
    {
        parent::__construct($message, $code);

        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    public static function typeInvalid($input)
    {
        $code = self::QUERY_TYPE;

        $data = array(
            'statement' => $input
        );

        $description = self::getValueDescription($input);

        $message = "Expected a string query,"
            . " but received {$description} instead.";

        return new self($code, $data, $message);
    }

    public static function syntaxInvalid($input, $position)
    {
        $code = self::QUERY_SYNTAX;

        $data = array(
            'statement' => $input,
            'position' => $position
        );

        $tail = self::getTail($input, $position);
        $tailJson = json_encode($tail);

        list($line, $character) = self::getLineCharacter($input, $position);

        $message = "Syntax error near {$tailJson}"
            . " on line {$line} character {$character}.";

        return new self($code, $data, $message);
    }

    public static function unknownProperty($class, $property)
    {
        $code = self::PROPERTY_UNKNOWN;

        $data = array(
            'class' => $class,
            'property' => $property
        );

        $className = json_encode($class);
        $propertyName = json_encode($property);

        $message = "The {$className} class has no {$propertyName} property.";

        return new self($code, $data, $message);
    }

    public static function unknownFunction($function)
    {
        $code = self::FUNCTION_UNKNOWN;

        $data = array(
            'function' => $function
        );

        $functionName = json_encode($function);

        $message = "There is no {$functionName} function.";

        return new self($code, $data, $message);
    }

    public static function typeParameter($parameter)
    {
        $code = self::PARAMETER_TYPE;

        $data = array(
            'parameter' => $parameter
        );

        $parameterName = json_encode($parameter);

        // TODO: provide more help than this:
        $message = "The parameter {$parameterName} is unconstrained.";

        return new self($code, $data, $message);
    }

    public static function typeProperty($property, $type)
    {
        $code = self::PROPERTY_TYPE;

        $data = array(
            'property' => $property,
            'type' => $type
        );

        $propertyName = json_encode(implode('.', $property));

        // TODO: provide better help:
        $message = "The property {$propertyName} can take on values that are forbidden in this query.";

        return new self($code, $data, $message);
    }

    public static function typeFunction($function, $arguments)
    {
        $code = self::FUNCTION_TYPE;

        $data = array(
            'function' => $function,
            'arguments' => $arguments
        );

        $functionName = json_encode($function);

        $message = "The function {$functionName} is unsatisfiable.";

        return new self($code, $data, $message);
    }

    private static function getValueDescription($value)
    {
        $type = gettype($value);

        switch ($type) {
            case 'NULL':
                return 'a null value';

            case 'boolean':
                $valueJson = json_encode($value);
                return "a boolean ({$valueJson})";

            case 'integer':
                $valueJson = json_encode($value);
                return "an integer ({$valueJson})";

            case 'double':
                $valueJson = json_encode($value);
                return "a float ({$valueJson})";

            case 'string':
                $valueJson = json_encode($value);
                return "a string ({$valueJson})";

            case 'array':
                $valueJson = json_encode($value);
                return "an array ({$valueJson})";

            case 'object':
                return 'an object';

            case 'resource':
                return 'a resource';

            default:
                return 'an unknown value';
        }
    }

    private static function getTail($input, $position)
    {
        $tail = ltrim(substr($input, $position));
        $newlinePosition = strpos($tail, "\n");

        if ($newlinePosition !== false) {
            $tail = substr($tail, 0, $newlinePosition);
        }

        $tail = rtrim($tail);

        $maximumLength = 72;

        if ($maximumLength < strlen($tail)) {
            $tail = substr($tail, 0, $maximumLength) . '...';
        }

        return $tail;
    }

    private static function getLineCharacter($input, $errorPosition)
    {
        $iLine = 0;
        $iCharacter = 0;

        $lines = preg_split('~\r?\n~', $input, null, PREG_SPLIT_OFFSET_CAPTURE);

        foreach ($lines as $line) {
            list($lineText, $linePosition) = $line;

            $iCharacter = $errorPosition - $linePosition;

            if ($iCharacter <= strlen($lineText)) {
                break;
            }

            ++$iLine;
        }

        return array($iLine + 1, $iCharacter + 1);
    }
}
