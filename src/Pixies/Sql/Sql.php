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

namespace Datto\Cinnabari\Pixies\Sql;

use Datto\Cinnabari\AbstractArtifact\Tables\Table;
use Datto\Cinnabari\AbstractArtifact\Tables\AbstractTable;
use Datto\Cinnabari\AbstractArtifact\Tables\JoinTable;
use Datto\Cinnabari\AbstractArtifact\Tables\SelectTable;
use Datto\Cinnabari\AbstractArtifact\SIL;
use Datto\Cinnabari\AbstractArtifact\Column;
use Datto\Cinnabari\AbstractArtifact\Statements\DeleteStatement;
use Datto\Cinnabari\AbstractArtifact\Statements\SelectStatement;
use Datto\Cinnabari\AbstractArtifact\Statements\Clauses\GroupBy;
use Datto\Cinnabari\AbstractArtifact\Statements\Clauses\Limit;
use Datto\Cinnabari\AbstractArtifact\Statements\Clauses\OrderBy;
use Datto\Cinnabari\Exception;
use Datto\Cinnabari\Pixies\AliasMapper;

/**
 * Class Sql
 *
 * Create SQL output based on the instances of SIL and AliasMapper given
 * to the constructor.
 *
 * This class can be derived from (with appropriate protected functions overridden)
 * to create support for a SQL dialects such as MySQL. (See, for example,
 * class Mysql.)
 */
class Sql
{
    /** @var SIL */
    private $sil;

    /** @var AliasMapper */
    private $aliasMapper;

    public function __construct(SIL $sil, AliasMapper $aliasMapper)
    {
        $this->sil = $sil;
        $this->aliasMapper = $aliasMapper;
    }

    /**
     * Return an SQL string corresponding to the SIL and AliasMapper passed
     * into the constructor.
     *
     * @return string
     * @throws Exception
     */
    public function format()
    {
        $mysqls = array();

        foreach ($this->sil->getStatements() as $statement) {
            if ($statement instanceof SelectStatement) {
                $mysqls[] = $this->formatSelect(
                    $statement->getColumns(),
                    $statement->getTable(),
                    $statement->getJoins(),
                    $statement->getWhere(),
                    $statement->getGroupBys(),
                    $statement->getHaving(),
                    $statement->getOrderBys(),
                    $statement->getLimit(),
                    false
                );
            } elseif ($statement instanceof DeleteStatement) {
                $mysqls[] = $this->formatDeleteStatement($statement);
            } else {
                throw Exception::internalError('format: bad statement type');
            }
        }

        return implode(";\n", $mysqls);
    }

    /**
     * Format a DELETE statement.
     *
     * @param DeleteStatement $delete
     *
     * @return string
     */
    protected function formatDeleteStatement(DeleteStatement $delete)
    {
        $parts = array();

        // Tables
        $tables = array();
        foreach ($delete->getTables() as $table) {
            $tables[] = $table->getName();
        }
        $parts[] = "\n\tFROM " . implode(', ', $tables);

        $this->formatWhere($delete->getWhere(), $parts);
        $this->formatOrderBy($delete->getOrderBys(), $parts);
        $this->formatLimit($delete->getLimit(), $parts);
        return 'DELETE ' . implode("\n\t", $parts);
    }

    /**
     * Format a SELECT statement or SELECT subquery.
     *
     * @param Column[]       $columns
     * @param AbstractTable  $from
     * @param JoinTable[]    $joins
     * @param string         $where
     * @param GroupBy[]      $groups
     * @param string         $having
     * @param OrderBy[]      $orders
     * @param Limit|null     $limit
     * @param bool           $subquery
     *
     * @return string
     * @throws Exception
     */
    protected function formatSelect($columns, $from, $joins, $where, $groups, $having, $orders, $limit, $subquery = false)
    {
        $parts = array();
        $glue = $subquery ? "\n\t\t" : "\n\t";

        // The columns
        if (count($columns) > 0) {
            $columnString = array();
            foreach ($columns as $column) {
                $columnString[] = $column->getValue() . ' AS '
                    . $this->aliasOrTag($column->getTag());
            }
            $parts[] = implode(',' . $glue, $columnString);
        }

        if ($from instanceof SelectTable) {
            $parts[] = 'FROM ' .
                $this->formatSelect(
                    $from->getColumns(),
                    $from->getTable(),
                    $from->getJoins(),
                    $from->getWhere(),
                    $from->getGroupBys(),
                    $from->getHaving(),
                    $from->getOrderBys(),
                    $from->getLimit(),
                    true
                )
                . " AS {$this->aliasOrTag($from->getTag())}";
        } elseif ($from instanceof Table) {
            $parts[] = 'FROM ' . self::formatName($from->getName())
                . ' AS ' . $this->aliasOrTag($from->getTag());
        } else {
            throw Exception::internalError('formatSelect: bad table type');
        }

        $this->formatJoins($joins, $parts);
        $this->formatWhere($where, $parts);
        $this->formatGroupBy($groups, $parts);
        $this->formatHaving($having, $parts);
        $this->formatOrderBy($orders, $parts);
        $this->formatLimit($limit, $parts);
        return ($subquery ? '(' : '')
            . 'SELECT' . $glue . implode($glue, $parts)
            . ($subquery ? ')' : '');
    }

    /**
     * Format a WHERE clause.
     *
     * @param string|null $where
     * @param string[]    $parts
     */
    protected function formatWhere($where, &$parts)
    {
        if ($where !== null) {
            $parts[] = 'WHERE (' . $where . ')';
        }
    }

    /**
     * Format a LIMIT clause.
     *
     * @param null|Limit  $limit
     * @param string[]    &$parts
     */
    protected function formatLimit($limit, &$parts)
    {
        if ($limit !== null) {
            $offset = $limit->getOffset();
            $rowCount = $limit->getRowCount();
            $parts[] = 'LIMIT ' . ($offset ? ($offset . ', ') : '') . $rowCount;
        }
    }

    /**
     * Format an ORDER BY clause.
     *
     * @param OrderBy[] $orderBys
     * @param string[]  $parts
     */
    protected function formatOrderBy($orderBys, &$parts)
    {
        if (count($orderBys) > 0) {
            $orders = array();
            foreach ($orderBys as $order) {
                $direction = $order->getIsDescending() ? 'DESC' : 'ASC';
                $orders[] = "{$order->getExpression()} {$direction}";
            }
            $parts[] = 'ORDER BY ' . implode(', ', $orders);
        }
    }

    /**
     * Format a GROUP BY clause.
     *
     * @param GroupBy[] $groupBys
     * @param string[]  $parts
     */
    protected function formatGroupBy($groupBys, &$parts)
    {
        if (count($groupBys) > 0) {
            $groups = array();
            foreach ($groupBys as $group) {
                $direction = $group->getIsDescending() ? 'DESC' : 'ASC';
                $groups[] = "{$group->getExpression()} {$direction}";
            }
            $parts[] = 'GROUP BY ' . implode(", ", $groups);
        }
    }

    /**
     * Format a JOIN clause.
     *
     * @param JoinTable[] $joins
     * @param string[]      $parts
     */
    protected function formatJoins($joins, &$parts)
    {
        if (count($joins) > 0) {
            $joinString = array();

            /** @var JoinTable $join */
            foreach ($joins as $join) {
                $command = $join->getIsInner() ? "INNER JOIN" : "LEFT JOIN";
                $joinString[] = "{$command} {$join->getName()} "
                    . "AS {$this->aliasOrTag($join->getTag())} ON ({$join->getCriterion()})";
            }

            $parts[] = implode(",\t", $joinString);
        }
    }

    /**
     * Format a HAVING clause.
     *
     * @param string|null $having
     * @param string[]    $parts
     */
    protected function formatHaving($having, &$parts)
    {
        if ($having !== null) {
            $parts[] = 'HAVING (' . $having . ')';
        }
    }

    /**
     * Format a name. (Here, surround it with back ticks.)
     *
     * @param string  $name
     *
     * @return string
     */
    protected static function formatName($name)
    {
        return "`{$name}`";
    }

    /**
     * If an alias is available for $tag, return it, else return $tag.
     *
     * @param string $tag
     *
     * @return null|string
     */
    protected function aliasOrTag($tag)
    {
        $alias = $this->aliasMapper->getAlias($tag);
        return $alias == null ? $tag : $alias;
    }
}
