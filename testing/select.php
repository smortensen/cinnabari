<?php

namespace Datto\Cinnabari;

use Datto\Cinnabari\Parser\Language\Functions;
use Datto\Cinnabari\Parser\Language\Operators;
use Datto\Cinnabari\Parser\Language\Properties;
use Datto\Cinnabari\Parser\Language\Types;
use Datto\Cinnabari\Translator\Map;
use Datto\Cinnabari\Translator\Nodes\Table;
use Datto\Cinnabari\Translator\Nodes\Value;
require __DIR__ . '/autoload.php';

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

$types = array(
	'Database' => array(
		'people' => array(Types::TYPE_ARRAY, array(Types::TYPE_OBJECT, 'Person'))
	),
	'Person' => array(
		'id' => Types::TYPE_INTEGER
	)
);

$map = array(
	'Database' => array(
		'people' => array(new Table('`People`', '`Id`', false), 'Person')
	),
	'Person' => array(
		'id' => array(new Value('`Id`'))
	)
);

$functions = new Functions();
$operators = new Operators();
$properties = new Properties($types);
$map = new Map($map);

$cinnabari = new Cinnabari($functions, $operators, $properties, $map);

$query = 'count(sort(people, id))';
$query = 'count(people)';

$result = $cinnabari->translate($query);

echo "result: ", json_encode($result), "\n";

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