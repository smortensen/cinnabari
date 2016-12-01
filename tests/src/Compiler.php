<?php

namespace Datto\Cinnabari\Tests;

use Datto\Cinnabari\Cinnabari;
use PHPUnit_Framework_TestCase;

/*
When joining from an origin table to a destination table:
 * Assume there is exactly one matching row in the destination table
 * If there is NO foreign key:
      Add the possibility of no matching rows in the destination table
 * If there is either:
     (a) NO uniqueness constraint on the destination table, or
     (b) BOTH the origin and destination columns are nullable:
 * Then add the possibility of many matching rows
*/

class CompilerTest extends PHPUnit_Framework_TestCase
{





    private function verifyResult($scenarioJson, $method, $mysql, $phpInput, $phpOutput)
    {
        $scenario = json_decode($scenarioJson, true);
        $cinnabari = new Cinnabari($scenario);

        $expected = array($mysql, $phpInput, $phpOutput);        
        $actual = $cinnabari->translate($method);

        $this->assertSame(
            self::standardize($expected),
            self::standardize($actual)
        );
    }

    private static function standardize($artifact)
    {
        list($mysql, $phpInput, $phpOutput) = $artifact;

        return array(
            self::standardizeMysql($mysql),
            self::standardizePhp($phpInput),
            self::standardizePhp($phpOutput)
        );
    }

    private static function standardizePhp($php)
    {
        return preg_replace('~\t~', '    ', $php);
    }

    private static function standardizeMysql($mysql)
    {
        $mysql = preg_replace('~\s+~', ' ', $mysql);

        // Remove any unnecessary whitespace after an opening parenthesis
        // Example: "( `" => "(`"
        // Example: "( (" => "(("
        // Example: "( :" => "(:"
        $mysql = preg_replace('~\( (?=`|\(|:)~', '(', $mysql);

        // Remove any unnecessary whitespace before a closing parenthesis
        // Example: "` )" => "`)"
        // Example: ") )" => "))"
        $mysql = preg_replace('~(?<=`|\)) \)~', ')', $mysql);

        return $mysql;
    }
}
