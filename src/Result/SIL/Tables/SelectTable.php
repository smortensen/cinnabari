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
 * @author Mark Greeley mgreeley@datto.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2016, 2017 Datto, Inc.
 */

namespace Datto\Cinnabari\Result\SIL\Tables;

use Datto\Cinnabari\Exception;
use Datto\Cinnabari\Result\SIL\Column;
use Datto\Cinnabari\Result\SIL\Statements\Clauses\Limit;
use Datto\Cinnabari\Result\SIL\Statements\Clauses\GroupBy;
use Datto\Cinnabari\Result\SIL\Statements\Clauses\OrderBy;

/**
 * Class SelectTable
 *
 * The SIL equivalent of a (My)SQL SELECT subquery.
 *
 * @package Datto\Cinnabari\Result\SIL\Tables
 */
class SelectTable extends AbstractTable
{
    /** @var AbstractTable */
    private $table;

    /** @var null|string */
    private $where;

    /** @var GroupBy[] */
    private $groupBys;

    /** @var null|string */
    private $having;

    /** @var OrderBy[] */
    private $orderBys;

    /** @var null|Limit */
    private $limit;

    /** @var Column[] */
    private $columns;

    /** @var JoinTable[] */
    private $joins;

    public function __construct()
    {
        $this->where = null;
        $this->groupBys = array();
        $this->having = null;
        $this->orderBys = array();
        $this->limit = null;
        $this->columns = array();
        $this->joins = array();
        parent::__construct();
    }

    public function setWhere($where)
    {
        if ($this->where) {
            throw Exception::internalError('Select: multiple wheres');
        }
        $this->where = $where;
    }

    public function getWhere()
    {
        return $this->where;
    }

    public function addGroupBy(GroupBy $groupBy)
    {
        $this->groupBys[] = $groupBy;
    }

    public function getGroupBys()
    {
        return $this->groupBys;
    }

    public function setHaving($having)
    {
        if ($this->having) {
            throw Exception::internalError('Select: multiple havings');
        }
        $this->having = $having;
    }

    public function getHaving()
    {
        return $this->having;
    }

    public function addOrderBy(OrderBy $orderBy)
    {
        $this->orderBys[] = $orderBy;
    }

    public function getOrderBys()
    {
        return $this->orderBys;
    }

    public function setLimit($limit)
    {
        if ($this->limit) {
            throw Exception::internalError('Select: multiple limits');
        }
        $this->limit = $limit;
    }

    public function getLimit()
    {
        return $this->limit;
    }

    public function setTable($table)
    {
        $this->table = $table;
    }

    public function getTable()
    {
        return $this->table;
    }

    /**
     * @param JoinTable $join
     */
    public function addJoin($join)
    {
        $this->joins[] = $join;
    }

    /**
     * @return array|AbstractTable[]
     */
    public function getJoins()
    {
        return $this->joins;
    }
}
