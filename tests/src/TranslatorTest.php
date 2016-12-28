<?php

namespace Datto\Cinnabari\Tests;

use Datto\Cinnabari\Parser;
use Datto\Cinnabari\Translator;
use Datto\Cinnabari\Types;
use PHPUnit_Framework_TestCase;

class TranslatorTest extends PHPUnit_Framework_TestCase
{
    const KEY_TOKEN = 0;
    const KEY_NAME = 1;
    const KEY_TYPE = 2;
    const KEY_MYSQL = 3;

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
                self::KEY_NAME => array('people'),
                self::KEY_TYPE => null, // array(Types::TYPE_ARRAY, Types::TYPE_OBJECT),
                self::KEY_MYSQL => array(array(
                    'token' => Translator::MYSQL_TABLE,
                    'table' => '`People`',
                    'id' => array(
                        'token' => Translator::MYSQL_VALUE,
                        'value' => '`Id`',
                        'isNullable' => false
                    )
                ))
            ),
            array(Parser::TYPE_OBJECT, array(
                'a' =>    array(
                    self::KEY_TOKEN => Parser::TYPE_PROPERTY,
                    self::KEY_NAME => array('id'),
                    self::KEY_TYPE => null, // Types::TYPE_INTEGER,
                    self::KEY_MYSQL => array(array(
                        'token' => Translator::MYSQL_VALUE,
                        'value' => '`Id`',
                        'isNullable' => false
                    )),
                ),
                'b' => array(
                    self::KEY_TOKEN => Parser::TYPE_PROPERTY,
                    self::KEY_NAME => array('isMarried'),
                    self::KEY_TYPE => null, // array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN),
                    self::KEY_MYSQL => array(array(
                        'token' => Translator::MYSQL_VALUE,
                        'value' => '`Married`',
                        'isNullable' => true
                    ))
                ),
                'c' => array(
                    self::KEY_TOKEN => Parser::TYPE_PROPERTY,
                    self::KEY_NAME => array('age'),
                    self::KEY_TYPE => null, // array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER),
                    self::KEY_MYSQL => array(array(
                        'token' => Translator::MYSQL_VALUE,
                        'value' => '`Age`',
                        'isNullable' => true
                    ))
                ),
                'd' => array(
                    self::KEY_TOKEN => Parser::TYPE_PROPERTY,
                    self::KEY_NAME => array('height'),
                    self::KEY_TYPE => null, // array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_FLOAT),
                    self::KEY_MYSQL => array(array(
                        'token' => Translator::MYSQL_VALUE,
                        'value' => '`Height`',
                        'isNullable' => true
                    ))
                ),
                'e' => array(
                    self::KEY_TOKEN => Parser::TYPE_PROPERTY,
                    self::KEY_NAME => array('name'),
                    self::KEY_TYPE => null, // array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_STRING),
                    self::KEY_MYSQL => array(array(
                        'token' => Translator::MYSQL_VALUE,
                        'value' => '`Name`',
                        'isNullable' => true
                    ))
                ),
                'f' => array(
                    self::KEY_TOKEN => Parser::TYPE_PROPERTY,
                    self::KEY_NAME => array('email'),
                    self::KEY_TYPE => null, // array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_STRING),
                    self::KEY_MYSQL => array(array(
                        'token' => Translator::MYSQL_VALUE,
                        'value' => 'IF(`Email` <=> \'\', NULL, LOWER(`Email`))',
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
