<?php

namespace Datto\Cinnabari\Tests;

use Datto\Cinnabari\Language\Functions;
use Datto\Cinnabari\Language\Types;
use Datto\Cinnabari\Parser;
use Datto\Cinnabari\Resolver;
use Datto\Cinnabari\Translator;
use PHPUnit_Framework_TestCase;

class ResolverTest extends PHPUnit_Framework_TestCase
{
    public function testGetPeopleId()
    {
        $input = array(Parser::TYPE_FUNCTION, 'get', array(
            array(Parser::TYPE_FUNCTION, 'filter', array(
                array(Parser::TYPE_PROPERTY, array(
                    array(
                        'token' => Translator::MYSQL_TABLE,
                        'table' => '`People`',
                        'id' => array(
                            'token' => Translator::MYSQL_VALUE,
                            'value' => '`Id`',
                            'type' => Types::TYPE_STRING,
                            'isNullable' => false
                        )
                    )
                )),
                array(Parser::TYPE_FUNCTION, 'equal', array(
                    array(Parser::TYPE_PROPERTY, array(
                        array(
                            'token' => Translator::MYSQL_VALUE,
                            'value' => '`Age`',
                            'type' => Types::TYPE_INTEGER,
                            'isNullable' => true
                        )
                    )),
                    array(Parser::TYPE_PARAMETER, 'age')
                ))
            )),
            array(Parser::TYPE_PROPERTY, array(
                array(
                    'token' => Translator::MYSQL_VALUE,
                    'value' => '`Id`',
                    'type' => Types::TYPE_INTEGER,
                    'isNullable' => false
                )
            ))
        ));

        $output = array(
            Parser::TYPE_FUNCTION,
            'get',
            array(
                array(
                    Parser::TYPE_FUNCTION,
                    'filter',
                    array(
                        array(
                            Parser::TYPE_PROPERTY,
                            array(
                                array(
                                    'token' => Translator::MYSQL_TABLE,
                                    'table' => '`People`',
                                    'id' => array(
                                        'token' => Translator::MYSQL_VALUE,
                                        'value' => '`Id`',
                                        'type' => Types::TYPE_STRING,
                                        'isNullable' => false
                                    )
                                )
                            ),
                            array(Types::TYPE_ARRAY, Types::TYPE_OBJECT)
                        ),
                        array(
                            Parser::TYPE_FUNCTION,
                            'equal',
                            array(
                                array(
                                    Parser::TYPE_PROPERTY,
                                    array(
                                        array(
                                            'token' => Translator::MYSQL_VALUE,
                                            'value' => '`Age`',
                                            'type' => Types::TYPE_INTEGER,
                                            'isNullable' => true
                                        )
                                    ),
                                    array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER)
                                ),
                                array(
                                    Parser::TYPE_PARAMETER,
                                    'age',
                                    null
                                )
                            ),
                            null
                        )
                    ),
                    null
                ),
                array(
                    Parser::TYPE_PROPERTY,
                    array(
                        array(
                            'token' => Translator::MYSQL_VALUE,
                            'value' => '`Id`',
                            'type' => Types::TYPE_INTEGER,
                            'isNullable' => false
                        )
                    ),
                    Types::TYPE_INTEGER
                )
            ),
            null
        );

        $this->verify($input, $output);
    }

    private function verify($input, $expectedOutput)
    {
        $functions = new Functions();
        $resolver = new Resolver($functions);

        $actualOutput = $resolver->resolve($input);

        $expectedOutputJson = json_encode($expectedOutput);
        $actualOutputJson = json_encode($actualOutput);

        $this->assertSame($expectedOutputJson, $actualOutputJson);
    }
}
