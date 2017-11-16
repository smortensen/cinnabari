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
 * @license http://www.gnu.org/licenses/lgpl-3.0.html LGPL-3.0
 * @copyright 2016, 2017 Datto, Inc.
 */

namespace Datto\Cinnabari\Entities\Mysql;

use Datto\Cinnabari\Phases\Translator\Aliases;
use Datto\Cinnabari\Entities\Mysql\Clauses\Clause;

class Select extends AbstractNode
{
    /** @var array */
    private $tables;

    /** @var array */
    private $values;

    /** @var array */
    private $clauses;

    /** @var Aliases */
    private $tableAliases;

    /** @var Aliases */
    private $valueAliases;

    /** @var Aliases */
    private $parameterAliases;

    public function __construct()
    {
        parent::__construct(AbstractNode::TYPE_SELECT);

        $this->tables = array();
        $this->values = array();
        $this->clauses = array();

        $this->tableAliases = new Aliases();
        $this->valueAliases = new Aliases();
        $this->parameterAliases = new Aliases();
    }

    public function add(&$context, AbstractNode $node)
    {
        $type = $node->getNodeType();

        switch ($type) {
            default: // Node::TYPE_SELECT:
                return null;

            case AbstractNode::TYPE_TABLE:
                /** @var Table $node */
                return $this->addTable($context, $node);

            case AbstractNode::TYPE_JOIN:
                /** @var Join $node */
                return $this->addJoin($context, $node);

            case AbstractNode::TYPE_VALUE:
                /** @var Value $node */
                return $this->addValueNode($node);
        }
    }

    private function addTable(&$context, Table $table)
    {
        $context = $this->addTableNode($table);
    }

    private function addJoin(&$context, Join $join)
    {
        $join->setContext($context);

        $context = $this->addTableNode($join);
    }

    private function addTableNode(AbstractTable $node)
    {
        $alias = $this->getTableAlias($node);
        $this->tables[$alias] = $node;

        return $alias;
    }

    private function addValueNode(Value $node)
    {
        $alias = $this->getValueAlias($node);
        $this->values[$alias] = $node;

        return $alias;
    }

    public function addClause(Clause $clause)
    {
        $this->clauses[] = $clause;
    }

    public function getTables()
    {
        return $this->tables;
    }

    /**
     * @param integer $alias
     * @return AbstractTable
     */
    public function getTable($alias)
    {
        $table = &$this->tables[$alias];

        return $table;
    }

    public function getTableAlias(AbstractNode $node)
    {
        $state = $node->getState();
        return $this->tableAliases->getAlias($state);
    }

    public function getValues()
    {
        return $this->values;
    }

    public function getValueAlias(AbstractNode $node)
    {
        $state = $node->getState();
        return $this->valueAliases->getAlias($state);
    }

    public function getClauses()
    {
        return $this->clauses;
    }

    public function getState()
    {
        return null;
    }
}
