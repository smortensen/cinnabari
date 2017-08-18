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

namespace Datto\Cinnabari;

require TESTPHP . '/autoload.php';

use Datto\Cinnabari\AbstractArtifact\SIL;
use Datto\Cinnabari\AbstractArtifact\Tables\Table;
use Datto\Cinnabari\AbstractArtifact\Tables\JoinTable;
use Datto\Cinnabari\AbstractArtifact\Tables\SelectTable;
use Datto\Cinnabari\AbstractArtifact\Parameter;
use Datto\Cinnabari\AbstractArtifact\Column;
use Datto\Cinnabari\AbstractArtifact\Statements\SelectStatement;
use Datto\Cinnabari\AbstractArtifact\Statements\DeleteStatement;
use Datto\Cinnabari\AbstractArtifact\Statements\Clauses\Limit;
use Datto\Cinnabari\AbstractArtifact\Statements\Clauses\GroupBy;
use Datto\Cinnabari\AbstractArtifact\Statements\Clauses\OrderBy;
use Datto\Cinnabari\Pixies\AliasMapper;
use Datto\Cinnabari\Pixies\Sql\Mysql\Mysql;

/**
 * Return $input with all successions of whitespace changed to a single space,
 * to facilitate diffing.
 *
 * @param string $input
 *
 * @return string
 */
function removeExcessWhitespace($input)
{
    return trim(preg_replace('/[\s\t\n\r\s]+/', ' ', $input));
}


/**
 * Create Mysql output based on $sil and $aliasMapper. Strip all extraneous
 * whitespace from the returned string.
 *
 * @param SIL         $sil
 * @param AliasMapper $aliasMapper
 *
 * @return string
 */
function silToOutput(SIL $sil, AliasMapper $aliasMapper)
{
    $formatter = new Mysql($sil, $aliasMapper);
    return removeExcessWhitespace($formatter->format());
}

//---------------------------------------------------------------
// Test
$sil = new SIL();
$aliasMapper = new AliasMapper(function ($in) {
    return "`{$in}`";
});
$select = new SelectStatement();
$from = new Table('client', $aliasMapper);
$select->setTable($from);
$clientId = new Column('id', $from->getTag() . '.`clientId`', $aliasMapper);
$select->addColumn($clientId);
$sil->addStatement($select);
$output = silToOutput($sil, $aliasMapper);

// Output
$output = removeExcessWhitespace( <<<'EOS'
    SELECT
	{{t0}}.`clientId` AS {{c0}}
	FROM `client` AS {{t0}}
EOS
);


//---------------------------------------------------------------
// Test
$sil = new SIL();
$aliasMapper = new AliasMapper(function ($in) {
    return "`{$in}`";
});
$parameterId = new Parameter('id', $aliasMapper);
$sil->addParameter($parameterId);
$parameterId2 = new Parameter('id2', $aliasMapper);
$sil->addParameter($parameterId2);
$select = new SelectStatement();
$from = new Table('client', $aliasMapper);
$select->setTable($from);
$clientId = new Column('id', $from->getTag() . '.`clientId`', $aliasMapper);
$select->addColumn($clientId);
$clientName = new Column('name', $from->getTag() . '.`clientName`', $aliasMapper);
$select->addColumn($clientName);
$clientIdTag = $clientId->getTag();
$clientNameTag = $clientName->getTag();
$select->addOrderBy(new OrderBy($clientIdTag));
$select->addOrderBy(new OrderBy($clientNameTag, true));
$select->setLimit(new Limit('3', '32'));
$select->addGroupBy(new GroupBy($clientNameTag));
$select->setWhere("{$clientIdTag} >= {$parameterId->getTag()} AND {$clientIdTag} <= {$parameterId2->getTag()}");
$select->setHaving("{$clientIdTag} <=> {$parameterId->getTag()}");
$joinTable = new JoinTable('device', $aliasMapper, true);
$select->addJoin($joinTable);
$joinTable->setCriterion("{$clientIdTag} >= {$parameterId->getTag()} AND {$clientIdTag} <= {$parameterId2->getTag()}");
$sil->addStatement($select);
$output = silToOutput($sil, $aliasMapper);

// Output
$output = removeExcessWhitespace( <<<'EOS'
    SELECT
        {{t0}}.`clientId` AS {{c0}},
        {{t0}}.`clientName` AS {{c1}}
        FROM `client` AS {{t0}}
        INNER JOIN device AS {{t1}} ON ({{c0}} >= {{p0}} AND {{c0}} <= {{p1}})
        WHERE ({{c0}} >= {{p0}} AND {{c0}} <= {{p1}})
        GROUP BY {{c1}} ASC 
        HAVING ({{c0}} <=> {{p0}})
        ORDER BY {{c0}} ASC, {{c1}} DESC
        LIMIT 3, 32
EOS
);


//---------------------------------------------------------------
// Test
$sil = new SIL();
$aliasMapper = new AliasMapper(function ($in) {
    return "`{$in}`";
});
$subquery = new SelectTable($aliasMapper);
$from = new Table('client', $aliasMapper);
$subquery->setTable($from);
$clientId = new Column('id', '`clientId`', $aliasMapper);
$subquery->addColumn($clientId);
$subquery->setLimit(new Limit('0', '2'));
$select = new SelectStatement();
$select->setTable($subquery);
$avgClientId = new Column('id', "AVG({$subquery->getTag()}.{$clientId->getTag()})", $aliasMapper);
$select->addColumn($avgClientId);
$sil->addStatement($select);
$output = silToOutput($sil, $aliasMapper);

// Output
$output = removeExcessWhitespace( <<<'EOS'
    SELECT
        AVG({{t0}}.{{c0}}) AS {{c1}}
        FROM (SELECT
            `clientId` AS {{c0}}
            FROM `client` AS {{t1}}
            LIMIT 2) AS {{t0}}
EOS
);


//---------------------------------------------------------------
// Test
$sil = new SIL();
$aliasMapper = new AliasMapper(function ($in) {
    return "`{$in}`";
});

$delete = new DeleteStatement();
$from = new Table('client', $aliasMapper);
$delete->addTable($from);
$clientId = new Column('id', '`clientId`', $aliasMapper);
$parameterId = new Parameter('id', $aliasMapper);
$sil->addParameter($parameterId);
$delete->setWhere("{$parameterId->getTag()} <= {$clientId->getValue()}");
$delete->setLimit(new Limit('0', '2'));
$delete->addOrderBy(new OrderBy($clientId->getTag()));
$sil->addStatement($delete);
$output = silToOutput($sil, $aliasMapper);

// Output
$output = removeExcessWhitespace( <<<'EOS'
    DELETE
        FROM client
        WHERE ({{p0}} <= `clientId`)
        ORDER BY {{c0}} ASC
        LIMIT 2
EOS
);


//---------------------------------------------------------------
// Test
$sil = new SIL();
$aliasMapper = new AliasMapper(function ($in) {
    return "`{$in}`";
});

$select = new SelectStatement();
$from = new Table('client', $aliasMapper);
$select->setTable($from);
$clientId = new Column('id', $from->getTag() . '.`clientId`', $aliasMapper);
$select->addColumn($clientId);
$sil->addStatement($select);
$output = silToOutput($sil, $aliasMapper);

$delete = new DeleteStatement();
$from = new Table('client', $aliasMapper);
$delete->addTable($from);
$clientId = new Column('id', '`clientId`', $aliasMapper);
$parameterId = new Parameter('id', $aliasMapper);
$sil->addParameter($parameterId);
$delete->setWhere("{$parameterId->getTag()} <= {$clientId->getValue()}");
$delete->setLimit(new Limit('0', '2'));
$sil->addStatement($delete);
$output = silToOutput($sil, $aliasMapper);

// Output
$output = removeExcessWhitespace( <<<'EOS'
    SELECT {{t0}}.`clientId` AS {{c0}}
        FROM `client` AS {{t0}};
    DELETE
        FROM client
        WHERE ({{p0}} <= `clientId`)
        LIMIT 2
EOS
);
