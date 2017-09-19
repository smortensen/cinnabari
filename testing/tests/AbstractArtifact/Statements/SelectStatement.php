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

namespace Datto\Cinnabari\AbstractArtifact\Statements;

require LENS . 'autoload.php';

use Datto\Cinnabari\AbstractArtifact\SIL;
use Datto\Cinnabari\AbstractArtifact\Tables\Table;
use Datto\Cinnabari\AbstractArtifact\Tables\JoinTable;
use Datto\Cinnabari\AbstractArtifact\Tables\SelectTable;
use Datto\Cinnabari\AbstractArtifact\Column;
use Datto\Cinnabari\AbstractArtifact\Statements\Clauses\Limit;
use Datto\Cinnabari\AbstractArtifact\Statements\Clauses\GroupBy;
use Datto\Cinnabari\AbstractArtifact\Statements\Clauses\OrderBy;
use Datto\Cinnabari\Exception;
use Datto\Cinnabari\Pixies\AliasMapper;

/**
 * Return a SIL, AliasManager, and Select for use in the tests below
 */
function init()
{
    $sil = new SIL();
    $aliasMapper = new AliasMapper($sil, function ($in) {
        return "`{$in}`";
    });
    $select = new SelectStatement();
    return array($sil, $aliasMapper, $select);
}


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $select) = init();
$select->setWhere('abc');
$output = $select->getWhere();

// Output
$output = 'abc';


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $select) = init();
$select->setWhere('hi');
$select->setWhere('again');

// Output
throw Exception::internalError('Select: multiple wheres');


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $select) = init();
$select->addGroupBy(new GroupBy('abc'));
$groupBys = $select->getGroupBys();
$output = $groupBys[0]->getExpression();

// Output
$output = 'abc';


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $select) = init();
$select->setHaving('hi');
$output = $select->getHaving();

// Output
$output = 'hi';


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $select) = init();
$select->setHaving('hi');
$select->setHaving('again');

// Output
throw Exception::internalError('Select: multiple havings');


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $select) = init();
$select->addOrderBy(new OrderBy('xyz'));
$orderBys = $select->getOrderBys();
$output = $orderBys[0]->getExpression();

// Output
$output = 'xyz';


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $select) = init();
$select->setLimit(new Limit(33,44));
$output = $select->getLimit()->getRowCount() * 100 + $select->getLimit()->getOffset();

// Output
$output = 4433;


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $select) = init();
$select->setLimit(new Limit(33,44));
$select->setLimit(new Limit(55,66));

// Output
throw Exception::internalError('Select: multiple limits');


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $select) = init();
$select->addColumn(new Column('col', 'val', $aliasMapper));
$columns = $select->getColumns();
$output = $columns[0]->getName() . ' ' . $columns[0]->getValue();

// Output
$output = 'col val';


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $select) = init();
$select->addJoin(new JoinTable('jt', $aliasMapper, false));
$joins = $select->getJoins();
$output = $joins[0]->getName();

// Output
$output = 'jt';


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $select) = init();
$select->setTable(new Table('aTable', $aliasMapper));
$output = $select->getTable()->getName();

// Output
$output = 'aTable';


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $select) = init();
$select->setTable(new JoinTable('xTable', $aliasMapper, true));
$output = $select->getTable()->getName();

// Output
$output = 'xTable';


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $select) = init();
$subquery = new SelectTable($aliasMapper);
$subquery->setTable(new Table('aTable', $aliasMapper));
$subquery->addColumn(new Column('col', 'valu', $aliasMapper));
$select->setTable($subquery);
$columns = $select->getTable()->getColumns();
$output = $columns[0]->getName();

// Output
$output = 'col';
