<?php

namespace Datto\Cinnabari;

use Datto\Cinnabari\Entities\Language\Functions;
use Datto\Cinnabari\Entities\Language\Operators;
use Datto\Cinnabari\Entities\Language\Properties;
use Datto\Cinnabari\Entities\Language\Types;
use Datto\Cinnabari\Entities\Mysql\Join;
use Datto\Cinnabari\Entities\Mysql\Table;
use Datto\Cinnabari\Entities\Mysql\Value;
use Datto\Cinnabari\Tests\Standardizer;
use Datto\Cinnabari\Phases\Translator\Map;

$standardizer = new Standardizer();


// Test
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

list($mysql, $parameters, $phpInput, $phpOutput) = $cinnabari->translate($query);
$standardizer->standardizeMysql($mysql);


// Input
$query = 'count(people)';

// Output
$mysql = 'SELECT COUNT(`0`.`Id`) AS `0` FROM `People` AS `0`';
$parameters = array();
$phpInput = array();
$phpOutput = 'foreach ($rows as $row) {
	$output = (integer)$row[0];
}';


// Input
$query = 'count(sort(people, id))';

// Output
$mysql = 'SELECT COUNT(`0`.`Id`) AS `0` FROM `People` AS `0` ORDER BY `0`.`Id` ASC';
$parameters = array();
$phpInput = array();
$phpOutput = 'foreach ($rows as $row) {
	$output = (integer)$row[0];
}';


// Input
$query = 'count(sort(people, name.last))';

// Output
$mysql = 'SELECT COUNT(`0`.`Id`) AS `0` FROM `People` AS `0` LEFT JOIN `Names` AS `1` ON `0`.`Name` = `1`.`Id` ORDER BY `1`.`Last` ASC';
$parameters = array();
$phpInput = array();
$phpOutput = 'foreach ($rows as $row) {
	$output = (integer)$row[0];
}';


// Input
$query = 'map(people, id)';

// Output
$mysql = 'SELECT `0`.`Id` AS `0` FROM `People` AS `0`';
$parameters = array();
$phpInput = array();
$phpOutput = 'foreach ($rows as $row) {
	$output[$row[0]] = (integer)$row[0];
}';


// Input
$query = 'map(people, name.last)';

// Output
$mysql = 'SELECT `0`.`Id` AS `0`, `1`.`Last` AS `1` FROM `People` AS `0` LEFT JOIN `Names` AS `1` ON `0`.`Name` = `1`.`Id`';
$parameters = array();
$phpInput = array();
$phpOutput = 'foreach ($rows as $row) {
	$output[$row[0]] = $row[1];
}';


// Input
$query = 'map(sort(people, name.last), name.first)';

// Output
$mysql = 'SELECT `0`.`Id` AS `0`, `1`.`First` AS `1` FROM `People` AS `0` LEFT JOIN `Names` AS `1` ON `0`.`Name` = `1`.`Id` ORDER BY `1`.`Last` ASC';
$parameters = array();
$phpInput = array();
$phpOutput = 'foreach ($rows as $row) {
	$output[$row[0]] = $row[1];
}';


// Input
$query = 'count(slice(people, :begin, :end))';

// Output
$mysql = 'SELECT COUNT(`0`.`Id`) AS `0` FROM `People` AS `0` LIMIT :0, :1';
$parameters = array(
	'begin' => Types::TYPE_INTEGER,
	'end' => Types::TYPE_INTEGER
);
$phpInput = array(
	':0' => "max(\$input['begin'], 0)",
	':1' => "(max(\$input['begin'], 0) < \$input['end']) ? (\$input['end'] - max(\$input['begin'], 0)) : 0"
);
$phpOutput = 'foreach ($rows as $row) {
	$output = (integer)$row[0];
}';


// Input
$query = 'count(slice(sort(people, name.last), :begin, :end))';

// Output
$mysql = "SELECT COUNT(`0`.`Id`) AS `0` FROM `People` AS `0` LEFT JOIN `Names` AS `1` ON `0`.`Name` = `1`.`Id` ORDER BY `1`.`Last` ASC LIMIT :0, :1";
$parameters = array(
	'begin' => Types::TYPE_INTEGER,
	'end' => Types::TYPE_INTEGER
);
$phpInput = array(
	':0' => "max(\$input['begin'], 0)",
	':1' => "(max(\$input['begin'], 0) < \$input['end']) ? (\$input['end'] - max(\$input['begin'], 0)) : 0"
);
$phpOutput = 'foreach ($rows as $row) {
	$output = (integer)$row[0];
}';


// Input
$query = 'map(slice(sort(people, name.last), :begin, :end), name.first)';

// Output
$mysql = "SELECT `0`.`Id` AS `0`, `1`.`First` AS `1` FROM `People` AS `0` LEFT JOIN `Names` AS `1` ON `0`.`Name` = `1`.`Id` ORDER BY `1`.`Last` ASC LIMIT :0, :1";
$parameters = array(
	'begin' => Types::TYPE_INTEGER,
	'end' => Types::TYPE_INTEGER
);
$phpInput = array(
	':0' => "max(\$input['begin'], 0)",
	':1' => "(max(\$input['begin'], 0) < \$input['end']) ? (\$input['end'] - max(\$input['begin'], 0)) : 0"
);
$phpOutput = 'foreach ($rows as $row) {
	$output[$row[0]] = $row[1];
}';
