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

use Datto\Cinnabari\Result\AliasMapper\AliasMapper;
use Datto\Cinnabari\Result\SIL\Column;

/**
 * Abstract class AbstractTable
 *
 * A FROM/INTO table, JOIN, or SELECT subquery.
 *
 * @package Datto\Cinnabari\Result\SIL
 */
abstract class AbstractTable
{
    /** @var Column[] */
    private $columns;

    /** @var int */
    private $limitOffset;

    /** @var int */
    private $limitRowcount;

    /** @var string */
    private $tag;

    /**
     * AbstractTable constructor.
     *
     * @param AliasMapper $mapper
     */
    public function __construct(AliasMapper $mapper)
    {
        $this->columns = array();
        $this->limitOffset = 0;
        $this->limitRowcount = null;
        $this->tag = $mapper->createTableTag();
    }

    /**
     * @param Column $column
     */
    public function addColumn($column)
    {
        $this->columns[] = $column;
    }

    /**
     * @return array|Column[]
     */
    public function getColumns()
    {
        return $this->columns;
    }

    public function getTag()
    {
        return $this->tag;
    }
}
