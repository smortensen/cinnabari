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

require TESTPHP . '/autoload.php';

use Datto\Cinnabari\AbstractArtifact\SIL;
use Datto\Cinnabari\AbstractArtifact\Tables\Table;
use Datto\Cinnabari\AbstractArtifact\Statements\Clauses\Limit;
use Datto\Cinnabari\AbstractArtifact\Statements\Clauses\OrderBy;
use Datto\Cinnabari\Exception;
use Datto\Cinnabari\Pixies\AliasMapper;

/**
 * Return a SIL, AliasManager, and Delete for use in the tests below
 */
function init()
{
    $sil = new SIL();
    $aliasMapper = new AliasMapper($sil, function ($in) {
        return "`{$in}`";
    });
    $delete = new DeleteStatement();
    return array($sil, $aliasMapper, $delete);
}


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $delete) = init();
$delete->setWhere('abc');
$output = $delete->getWhere();

// Output
$output = 'abc';


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $delete) = init();
$delete->setWhere('hi');
$delete->setWhere('again');

// Output
throw Exception::internalError('Delete: multiple wheres');


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $delete) = init();
$delete->addOrderBy(new OrderBy('xyz'));
$orderBys = $delete->getOrderBys();
$output = $orderBys[0]->getExpression();

// Output
$output = 'xyz';


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $delete) = init();
$delete->setLimit(new Limit(33,44));
$output = $delete->getLimit()->getRowCount() * 100 + $delete->getLimit()->getOffset();

// Output
$output = 4433;


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $delete) = init();
$delete->setLimit(new Limit(33,44));
$delete->setLimit(new Limit(55,66));

// Output
throw Exception::internalError('Delete: multiple limits');


//---------------------------------------------------------------
// Test
list($sil, $aliasMapper, $delete) = init();
$delete->addTable(new Table('aTable', $aliasMapper));
$tables = $delete->getTables();
$output = $tables[0]->getName();

// Output
$output = 'aTable';
