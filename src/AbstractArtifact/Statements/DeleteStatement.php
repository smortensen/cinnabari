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
 * @author Spencer Mortensen <smortensen@datto.com>
 * @author Mark Greeley mgreeley@datto.com>
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2016, 2017 Datto, Inc.
 */

namespace Datto\Cinnabari\AbstractArtifact\Statements;

use Datto\Cinnabari\Exception;
use Datto\Cinnabari\AbstractArtifact\Tables\Table;
use Datto\Cinnabari\AbstractArtifact\Statements\Clauses\Limit;
use Datto\Cinnabari\AbstractArtifact\Statements\Clauses\OrderBy;

/**
 * Class DeleteStatement
 *
 * The AbstractArtifact equivalent of a (My)SQL DELETE statement.
 */
class DeleteStatement extends AbstractStatement
{
    /** @var Table[] */
    private $tables;

    /** @var null|string */
    private $where;

    /** @var OrderBy[] */
    private $orderBys;

    /** @var null|Limit */
    private $limit;

    public function __construct()
    {
        $this->where = null;
        $this->orderBys = array();
        $this->limit = null;
        parent::__construct();
    }

    public function addTable(Table $table)
    {
        $this->tables[] = $table;
    }

    public function getTables()
    {
        return $this->tables;
    }

    public function setWhere($where)
    {
        if ($this->where) {
            throw Exception::internalError('Delete: multiple wheres');
        }
        $this->where = $where;
    }

    public function getWhere()
    {
        return $this->where;
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
            throw Exception::internalError('Delete: multiple limits');
        }
        $this->limit = $limit;
    }

    public function getLimit()
    {
        return $this->limit;
    }
}
