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

class InputValidation
{
    /** @var array */
    private $statements;

    public function getPhp($request)
    {
        $this->statements = array();

        $this->getStatements($request);

        return implode("\n\n", $this->statements);
    }

    public function getStatements($token)
    {
        switch ($token[0]) {
            case Parser::TYPE_PARAMETER:
                $this->getParameterStatement($token);
                break;

            case Parser::TYPE_FUNCTION:
                $this->getFunctionStatements($token);
                break;

            case Parser::TYPE_OBJECT:
                $this->getObjectStatements($token);
                break;
        }
    }

    private function getParameterStatement($token)
    {
        $parameter = $token[1];
        $types = self::getTypeList($token[2]);

        $key = var_export($parameter, true);

        $clause = self::getConditionPhp($key, $types);
        $this->statements[] = self::getStatementPhp($key, $clause);
    }

    private static function getTypeList($token)
    {
        // $token = array(Types::TYPE_OR, $type, $type, ...);
        if (is_array($token)) {
            array_shift($token);
            return $token;
        }

        // $token = Types::TYPE_INTEGER;
        return array($token);
    }

    private static function getConditionPhp($key, $types)
    {
        $clauses = array(
            self::getExistenceConditionPhp($key),
            self::getTypeConditionPhp($key, $types)
        );

        return self::join($clauses, '&&');
    }

    private static function getStatementPhp($key, $clause)
    {
        return "if (!{$clause}) {" . "\n" .
            "\t" . "throw new Exception({$key}, 1);" . "\n" .
        "}";
    }

    private static function getExistenceConditionPhp($key)
    {
        return "array_key_exists({$key}, \$input)";
    }

    private static function getTypeConditionPhp($key, $types)
    {
        $conditions = array();

        foreach ($types as $type) {
            $conditions[] = self::getPrimitiveTypeConditionPhp($key, $type);
        }

        return self::join($conditions, '||');
    }

    private static function getPrimitiveTypeConditionPhp($key, $type)
    {
        $variable = "\$input[{$key}]";

        switch ($type) {
            case Types::TYPE_STRING:
                return "is_string({$variable})";

            case Types::TYPE_FLOAT:
                return "is_float({$variable})";

            case Types::TYPE_INTEGER:
                return "is_integer({$variable})";

            case Types::TYPE_BOOLEAN:
                return "is_bool({$variable})";

            default: // Types::TYPE_NULL:
                return "({$variable} === null)";
        }
    }

    private static function join($conditions, $operator)
    {
        $php = implode(" {$operator} ", $conditions);

        if (1 < count($conditions)) {
            $php = "({$php})";
        }

        return $php;
    }

    private function getFunctionStatements($token)
    {
        $arguments = $token[2];

        $this->getArrayStatements($arguments);
    }

    private function getObjectStatements($token)
    {
        $object = $token[1];

        $this->getArrayStatements($object);
    }

    private function getArrayStatements($tokens)
    {
        foreach ($tokens as $token) {
            $this->getStatements($token);
        }
    }
}
