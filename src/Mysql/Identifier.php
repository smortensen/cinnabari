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

namespace Datto\Cinnabari\Mysql;

use Exception;
use Datto\Cinnabari\Mysql\Statements\AbstractStatement;

class Identifier extends Expression
{
    /** @var string[] */
    private $names;

    private $overrideMysql = false;

    public function __construct()
    {
        $this->names = func_get_args();
    }

    public function getMysql()
    {
        if ($this->overrideMysql !== false) {
            return $this->overrideMysql;
        }

        $countNames = count($this->names);

        if ($countNames === 0) {
            throw new Exception();
        }

        $escapedNames = array_map('self::escape', $this->names);
        $name = array_pop($escapedNames);
        $context = implode('.', $escapedNames);

        return trim(AbstractStatement::getAbsoluteExpression($context, $name), '.');
    }

    public function overrideMysql($sql)
    {
        $this->overrideMysql = $sql;
    }

    protected static function escape($name)
    {
        return (preg_match('/^[A-z0-9]+$/', $name)) ? "`{$name}`" : $name;
    }
}
