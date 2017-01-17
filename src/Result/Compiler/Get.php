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

namespace Datto\Cinnabari\Result\Compiler;

use Datto\Cinnabari\Translator;

class Get
{
    /** @var int[] */
    private $tables;

    /** @var array */
    private $columns;

    public function __construct()
    {
        $this->tables = array();
        $this->columns = array();
    }

    public function setTable($token)
    {
        if ($token['token'] !== Translator::MYSQL_TABLE) {
            // TODO: throw exception
            return false;
        }

        $key = self::getKey($token);

        $this->tables[$key] = count($this->tables);

        return true;
    }

    public function count()
    {
        $token = self::getValue(key($this->tables));

        $id = $token['id']['value'];
        $column = "COUNT(`0`.{$id})";

        $this->addColumn($column);
    }

    private function addColumn($expression)
    {
        $key = self::getKey($expression);

        $this->columns[$key] = $expression;
    }

    private static function getKey($token)
    {
        return json_encode($token);
    }

    private static function getValue($key)
    {
        return json_decode($key, true);
    }
}
