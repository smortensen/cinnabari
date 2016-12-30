<?php

namespace Datto\Cinnabari\Tests;

use Datto\Cinnabari\Language\Types;
use Datto\Cinnabari\Parser;
use Datto\Cinnabari\Translator;
use PHPUnit_Framework_TestCase;

class TranslatorTest extends PHPUnit_Framework_TestCase
{
    const KEY_TOKEN = 0;
    const KEY_MYSQL = 1;

    private static function getPeopleSchema()
    {
        return array(
            'classes' => array(
                'Database' => array(
                    'people' => array('Person', 'People')
                ),
                'Person' => array(
                    'id' => array(Types::TYPE_INTEGER, 'Id'),
                    'isMarried' => array(Types::TYPE_BOOLEAN, 'Married'),
                    'age' => array(Types::TYPE_INTEGER, 'Age'),
                    'height' => array(Types::TYPE_FLOAT, 'Height'),
                    'name' => array(Types::TYPE_STRING, 'Name'),
                    'email' => array(Types::TYPE_STRING, 'Email')
                )
            ),
            'values' => array(
                '`People`' => array(
                    'Id' => array('`Id`', false),
                    'Married' => array('`Married`', true),
                    'Age' => array('`Age`', true),
                    'Height' => array('`Height`', true),
                    'Name' => array('`Name`', true),
                    'Email' => array('IF(`Email` <=> \'\', NULL, LOWER(`Email`))', true)
                )
            ),
            'lists' => array(
                'People' => array('`People`', 'Id')
            )
        );
    }

    public function testGetFilterSort()
    {
        $schema = self::getPeopleSchema();

        $input = array(Parser::TYPE_FUNCTION, 'get', array(
            array(Parser::TYPE_PROPERTY, 'people'),
            array(Parser::TYPE_OBJECT, array(
                'a' => array(Parser::TYPE_PROPERTY, 'id'),
                'b' => array(Parser::TYPE_PROPERTY, 'isMarried'),
                'c' => array(Parser::TYPE_PROPERTY, 'age'),
                'd' => array(Parser::TYPE_PROPERTY, 'height'),
                'e' => array(Parser::TYPE_PROPERTY, 'name'),
                'f' => array(Parser::TYPE_PROPERTY, 'email')
            ))
        ));

        $output = array(Parser::TYPE_FUNCTION, 'get', array(
            array(
                self::KEY_TOKEN => Parser::TYPE_PROPERTY,
                self::KEY_MYSQL => array(array(
                    'token' => Translator::MYSQL_TABLE,
                    'table' => '`People`',
                    'id' => array(
                        'token' => Translator::MYSQL_VALUE,
                        'value' => '`Id`',
                        'type' => Types::TYPE_STRING,
                        'isNullable' => false
                    )
                ))
            ),
            array(Parser::TYPE_OBJECT, array(
                'a' =>    array(
                    self::KEY_TOKEN => Parser::TYPE_PROPERTY,
                    self::KEY_MYSQL => array(array(
                        'token' => Translator::MYSQL_VALUE,
                        'value' => '`Id`',
                        'type' => Types::TYPE_INTEGER,
                        'isNullable' => false
                    )),
                ),
                'b' => array(
                    self::KEY_TOKEN => Parser::TYPE_PROPERTY,
                    self::KEY_MYSQL => array(array(
                        'token' => Translator::MYSQL_VALUE,
                        'value' => '`Married`',
                        'type' => Types::TYPE_BOOLEAN,
                        'isNullable' => true
                    ))
                ),
                'c' => array(
                    self::KEY_TOKEN => Parser::TYPE_PROPERTY,
                    self::KEY_MYSQL => array(array(
                        'token' => Translator::MYSQL_VALUE,
                        'value' => '`Age`',
                        'type' => Types::TYPE_INTEGER,
                        'isNullable' => true
                    ))
                ),
                'd' => array(
                    self::KEY_TOKEN => Parser::TYPE_PROPERTY,
                    self::KEY_MYSQL => array(array(
                        'token' => Translator::MYSQL_VALUE,
                        'value' => '`Height`',
                        'type' => Types::TYPE_FLOAT,
                        'isNullable' => true
                    ))
                ),
                'e' => array(
                    self::KEY_TOKEN => Parser::TYPE_PROPERTY,
                    self::KEY_MYSQL => array(array(
                        'token' => Translator::MYSQL_VALUE,
                        'value' => '`Name`',
                        'type' => Types::TYPE_STRING,
                        'isNullable' => true
                    ))
                ),
                'f' => array(
                    self::KEY_TOKEN => Parser::TYPE_PROPERTY,
                    self::KEY_MYSQL => array(array(
                        'token' => Translator::MYSQL_VALUE,
                        'value' => 'IF(`Email` <=> \'\', NULL, LOWER(`Email`))',
                        'type' => Types::TYPE_STRING,
                        'isNullable' => true
                    ))
                )
            ))
        ));

        $this->verify($schema, $input, $output);
    }

    private function verify($schema, $input, $expectedOutput)
    {
        $translator = new Translator($schema);
        $actualOutput = $translator->translate($input);

        $expectedOutputJson = json_encode($expectedOutput);
        $actualOutputJson = json_encode($actualOutput);

        $this->assertSame($expectedOutputJson, $actualOutputJson);
    }
}
