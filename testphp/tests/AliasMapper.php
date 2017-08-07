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

use Datto\Cinnabari\Result\SIL\SIL;
use Datto\Cinnabari\Result\AliasMapper\AliasMapper;

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

// Test
$sil = new SIL();
$aliasMapper = new AliasMapper($sil, function ($in) {
    return "`{$in}`";
});

$input = "{$aliasMapper->createParameterTag()} {$aliasMapper->createTableTag()} {$aliasMapper->createColumnTag()}" .
    " {$aliasMapper->createParameterTag()} {$aliasMapper->createTableTag()} {$aliasMapper->createParameterTag()}";
$aliasMapper->calculate($input);
$output = $aliasMapper->replaceTagsWithAliases($input);

// Output
$output = '`:0` `0` `0` `:1` `1` `:2`';


//---------------------------------------------------------------
// Test
$sil = new SIL();
$aliasMapper = new AliasMapper($sil, function ($in) {
    return "`{$in}`";
});

$input = removeExcessWhitespace(<<<'EOS'
    SELECT
    {{p1}},
	{{t0}}.`CA` AS {{c1}},
	{{t0}}.`CB` AS {{c0}}
	FROM `client` AS {{t0}}
    WHERE ({{c0}} + {{p0}} > {{p1}})
EOS
);
$aliasMapper->calculate($input);
$output = $aliasMapper->replaceTagsWithAliases($input);

// Output
$output = 'SELECT `:0`, `0`.`CA` AS `0`, `0`.`CB` AS `1` FROM `client` AS `0` WHERE (`1` + `:1` > `:0`)';
