<?php

namespace Datto\Cinnabari\AbstractArtifact\Statements;

use Datto\Cinnabari\AbstractArtifact\AbstractArtifact;
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
 * Return an abstractArtifact, AliasManager, and Select for use in the tests below
 */
function init()
{
    $abstractArtifact = new AbstractArtifact();
    $aliasMapper = new AliasMapper($abstractArtifact, function ($in) {
        return "`{$in}`";
    });
    $select = new SelectStatement();
    return array($abstractArtifact, $aliasMapper, $select);
}


//---------------------------------------------------------------
// Test
list($abstractArtifact, $aliasMapper, $select) = init();
$select->setWhere('abc');
$output = $select->getWhere();

// Output
$output = 'abc';


//---------------------------------------------------------------
// Test
list($abstractArtifact, $aliasMapper, $select) = init();
$select->setWhere('hi');
$select->setWhere('again');

// Output
throw Exception::internalError('Select: multiple wheres');


//---------------------------------------------------------------
// Test
list($abstractArtifact, $aliasMapper, $select) = init();
$select->addGroupBy(new GroupBy('abc'));
$groupBys = $select->getGroupBys();
$output = $groupBys[0]->getExpression();

// Output
$output = 'abc';


//---------------------------------------------------------------
// Test
list($abstractArtifact, $aliasMapper, $select) = init();
$select->setHaving('hi');
$output = $select->getHaving();

// Output
$output = 'hi';


//---------------------------------------------------------------
// Test
list($abstractArtifact, $aliasMapper, $select) = init();
$select->setHaving('hi');
$select->setHaving('again');

// Output
throw Exception::internalError('Select: multiple havings');


//---------------------------------------------------------------
// Test
list($abstractArtifact, $aliasMapper, $select) = init();
$select->addOrderBy(new OrderBy('xyz'));
$orderBys = $select->getOrderBys();
$output = $orderBys[0]->getExpression();

// Output
$output = 'xyz';


//---------------------------------------------------------------
// Test
list($abstractArtifact, $aliasMapper, $select) = init();
$select->setLimit(new Limit(33,44));
$output = $select->getLimit()->getRowCount() * 100 + $select->getLimit()->getOffset();

// Output
$output = 4433;


//---------------------------------------------------------------
// Test
list($abstractArtifact, $aliasMapper, $select) = init();
$select->setLimit(new Limit(33,44));
$select->setLimit(new Limit(55,66));

// Output
throw Exception::internalError('Select: multiple limits');


//---------------------------------------------------------------
// Test
list($abstractArtifact, $aliasMapper, $select) = init();
$select->addColumn(new Column('col', 'val', $aliasMapper));
$columns = $select->getColumns();
$output = $columns[0]->getName() . ' ' . $columns[0]->getValue();

// Output
$output = 'col val';


//---------------------------------------------------------------
// Test
list($abstractArtifact, $aliasMapper, $select) = init();
$select->addJoin(new JoinTable('jt', $aliasMapper, false));
$joins = $select->getJoins();
$output = $joins[0]->getName();

// Output
$output = 'jt';


//---------------------------------------------------------------
// Test
list($abstractArtifact, $aliasMapper, $select) = init();
$select->setTable(new Table('aTable', $aliasMapper));
$output = $select->getTable()->getName();

// Output
$output = 'aTable';


//---------------------------------------------------------------
// Test
list($abstractArtifact, $aliasMapper, $select) = init();
$select->setTable(new JoinTable('xTable', $aliasMapper, true));
$output = $select->getTable()->getName();

// Output
$output = 'xTable';


//---------------------------------------------------------------
// Test
list($abstractArtifact, $aliasMapper, $select) = init();
$subquery = new SelectTable($aliasMapper);
$subquery->setTable(new Table('aTable', $aliasMapper));
$subquery->addColumn(new Column('col', 'valu', $aliasMapper));
$select->setTable($subquery);
$columns = $select->getTable()->getColumns();
$output = $columns[0]->getName();

// Output
$output = 'col';
