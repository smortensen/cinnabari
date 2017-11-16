<?php

namespace Datto\Cinnabari;

use Datto\Cinnabari\Entities\Language\Functions;
use Datto\Cinnabari\Entities\Language\Operators;
use Datto\Cinnabari\Entities\Language\Properties;
use Datto\Cinnabari\Entities\Language\Types;
use Datto\Cinnabari\Phases\Translator\Map;
use Datto\Cinnabari\Entities\Mysql\Join;
use Datto\Cinnabari\Entities\Mysql\Table;
use Datto\Cinnabari\Entities\Mysql\Value;
use SpencerMortensen\Parser\ParserException;

require __DIR__ . '/autoload.php';

$functions = new Functions();
$operators = new Operators();

/*
DROP DATABASE IF EXISTS `database`;
CREATE DATABASE `database`;
USE `database`;

CREATE TABLE `Names` (
	`Id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`First` VARCHAR(256) NOT NULL,
	`Last` VARCHAR(256) NOT NULL,
	CONSTRAINT `uc_Names_First_Last` UNIQUE (`First`, `Last`)
);

CREATE TABLE `People` (
	`Id` INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
	`Name` INT UNSIGNED NOT NULL,
	`Age` TINYINT UNSIGNED NOT NULL,
	CONSTRAINT `fk_People_Name__Names_Id` FOREIGN KEY (`Name`) REFERENCES `Names` (`Id`)
);

INSERT INTO `Names`
	(`Id`, `First`, `Last`) VALUES
	(11, 'Ann', 'Adams');

INSERT INTO `People`
	(`Id`, `Name`, `Age`) VALUES
	(1, 11, 36),
	(2, 11, 18);
*/

$properties = new Properties(
	array(
		'Database' => array(
			'people' => array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'))
		),
		'Name' => array(
			'first' => Types::TYPE_STRING,
			'last' => Types::TYPE_STRING
		),
		'Person' => array(
			'id' => Types::TYPE_INTEGER,
			'name' => array(Types::TYPE_OBJECT, 'Name'),
			'age' => Types::TYPE_INTEGER
		)
	)
);

$map = new Map(
	array(
		'Database' => array(
			'people' => array(new Table('`People`', '`Id`', false), 'Person')
		),
		'Name' => array(
			'first' => array(new Value('`First`')),
			'last' => array(new Value('`Last`'))
		),
		'Person' => array(
			'id' => array(new Value('`Id`')),
			'name' => array(new Join('`Names`', '`Id`', '`0`.`Name` = `1`.`Id`', false, false), 'Name'),
			'age' => array(new Value('`Age`'))
		)
	)
);

$cinnabari = new Cinnabari($functions, $operators, $properties, $map);

$query = 'count(filter(people, id = :id))';

try {
	$result = $cinnabari->translate($query);
	echo "result: ", json_encode($result), "\n";
} catch (ParserException $exception) {
	$rule = $exception->getRule();
	echo "rule: ", json_encode($rule), "\n";
}

/*
	$mysql = <<<'EOS'
SELECT
	COUNT(`0`.`Id`) AS `0`
FROM `People` AS `0`
EOS;

	$phpInput = <<<'EOS'
$output = array();
EOS;

	$phpOutput = <<<'EOS'
foreach ($input as $row) {
	$output = (integer)$row[0];
}
EOS;
*/
