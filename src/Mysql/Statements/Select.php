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

namespace Datto\Cinnabari\Mysql\Statements;

use Datto\Cinnabari\Mysql\Expression;

use Datto\Cinnabari\Mysql\Statements\Clauses\From;
use Datto\Cinnabari\Mysql\Statements\Clauses\GroupBy;
use Datto\Cinnabari\Mysql\Statements\Clauses\Having;
use Datto\Cinnabari\Mysql\Statements\Clauses\Limit;
use Datto\Cinnabari\Mysql\Statements\Clauses\OrderBy;
use Datto\Cinnabari\Mysql\Statements\Clauses\Where;
use Exception;

class Select implements Expression
{
    /** @var null|Expression */
    private $from;

    /** @var null|Expression */
    private $where;

    /** @var null|Expression */
    private $groupBy;

    /** @var null|Expression */
    private $having;

    /** @var null|Expression */
    private $orderBy;

    /** @var null|Expression */
    private $limit;

    /** @var array */
    private $joins;

    /** @var array */
    private $values;

    public function __construct()
    {
        $this->from = null;
        $this->where = null;
        $this->groupBy = null;
        $this->having = null;
        $this->orderBy = null;
        $this->limit = null;
        $this->joins = array();
        $this->values = array();
    }

    public function setFrom(Expression $from)
    {
        $this->from = new From($from);
    }

    public function setWhere(Expression $where)
    {
        $this->where = new Where($where);
    }

    public function setGroupBy(Expression $groupBy)
    {
        $this->groupBy = new GroupBy($groupBy);
    }

    public function setHaving(Expression $having)
    {
        $this->having = new Having($having);
    }

    public function setOrderBy(Expression $orderBy)
    {
        $this->orderBy = new OrderBy($orderBy);
    }

    public function setLimit(Expression $begin, Expression $end)
    {
        $this->limit = new Limit($begin, $end);
    }

    public function addJoin(Expression $join)
    {
        $this->joins[] = $join;
    }

    public function addValue(Expression $value)
    {
        $this->values[] = $value;
    }

    public function getMysql()
    {
        if ($this->from === null) {
            // TODO
            throw new Exception;
        }

        if (count($this->values) === 0) {
            // TODO
            throw new Exception;
        }

        $parts = array();

        $values = array_map('self::getExpressionMysql', $this->values);
        $parts[] = implode(', ', $values);

        $parts[] = $this->from->getMysql();

        $joins = array_map('self::getExpressionMysql', $this->joins);
        $parts = array_merge($parts, $joins);

        if ($this->where !== null) {
            $parts[] = $this->where->getMysql();
        }

        if ($this->groupBy !== null) {
            $parts[] = $this->groupBy->getMysql();
        }

        if ($this->having !== null) {
            $parts[] = $this->having->getMysql();
        }

        if ($this->orderBy !== null) {
            $parts[] = $this->orderBy->getMysql();
        }

        if ($this->limit !== null) {
            $parts[] = $this->limit->getMysql();
        }

        return "(SELECT " . implode(' ', $parts) . ")";
    }

    protected static function getExpressionMysql(Expression $expression)
    {
        return $expression->getMysql();
    }
}
