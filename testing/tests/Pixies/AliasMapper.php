<?php

namespace Datto\Cinnabari\Pixies;

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
$aliasMapper = new AliasMapper(function ($in) {
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
$aliasMapper = new AliasMapper(function ($in) {
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
