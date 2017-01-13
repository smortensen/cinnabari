<?php

namespace Datto\Cinnabari\Tests;

use Datto\Cinnabari\Language\Operators;
use Datto\Cinnabari\Lexer;
use Datto\Cinnabari\Parser;
use PHPUnit_Framework_TestCase;

class ParserTest extends PHPUnit_Framework_TestCase
{
    public function testParameterToken()
    {
        $input = array(
            array(Lexer::TYPE_PARAMETER => 'x')
        );

        $output = array(Parser::TYPE_PARAMETER, 'x');

        $this->verify($input, $output);
    }

    public function testPropertyToken()
    {
        $input = array(
            array(Lexer::TYPE_PROPERTY => array('x'))
        );

        $output = array(Parser::TYPE_PROPERTY, array('x'));

        $this->verify($input, $output);
    }

    public function testFunctionTokenNoArguments()
    {
        $input = array(
            array(Lexer::TYPE_FUNCTION => array('f'))
        );

        $output = array(Parser::TYPE_FUNCTION, 'f', array());

        $this->verify($input, $output);
    }

    public function testFunctionTokenOneArgument()
    {
        $input = array(
            array(Lexer::TYPE_FUNCTION => array(
                'f',
                array(
                    array(Lexer::TYPE_PARAMETER => 'x')
                )
            ))
        );

        $output = array(Parser::TYPE_FUNCTION, 'f', array(
            array(Parser::TYPE_PARAMETER, 'x')
        ));

        $this->verify($input, $output);
    }

    public function testFunctionTokenTwoArguments()
    {
        $input = array(
            array(Lexer::TYPE_FUNCTION => array(
                'f',
                array(
                    array(Lexer::TYPE_PARAMETER => 'x')
                ),
                array(
                    array(Lexer::TYPE_PROPERTY => array('y'))
                )
            ))
        );

        $output = array(Parser::TYPE_FUNCTION, 'f', array(
            array(Parser::TYPE_PARAMETER, 'x'),
            array(Parser::TYPE_PROPERTY, array('y'))
        ));

        $this->verify($input, $output);
    }

    public function testObjectTokenOneKey()
    {
        $input = array(
            array(Lexer::TYPE_OBJECT => array(
                'a' => array(
                    array(Lexer::TYPE_PARAMETER => 'x')
                )
            ))
        );

        $output = array(Parser::TYPE_OBJECT, array(
            'a' => array(Parser::TYPE_PARAMETER, 'x'))
        );

        $this->verify($input, $output);
    }

    public function testObjectTokenTwoKeys()
    {
        $input = array(
            array(Lexer::TYPE_OBJECT => array(
                'a' => array(
                    array(Lexer::TYPE_PARAMETER => 'x')
                ),
                'b' => array(
                    array(Lexer::TYPE_PROPERTY => array('y'))
                )
            ))
        );

        $output = array(Parser::TYPE_OBJECT, array(
            'a' => array(Parser::TYPE_PARAMETER, 'x'),
            'b' => array(Parser::TYPE_PROPERTY, array('y'))
        ));

        $this->verify($input, $output);
    }

    public function testGroupToken()
    {
        $input = array(
            array(Lexer::TYPE_GROUP => array(
                array(Lexer::TYPE_PARAMETER => 'x')
            ))
        );

        $output = array(Parser::TYPE_PARAMETER, 'x');

        $this->verify($input, $output);
    }

    public function testPathTokenPropertyDotProperty()
    {
        $input = array(
            array(Lexer::TYPE_PROPERTY => array('x', 'y'))
        );

        $output = array(Parser::TYPE_PROPERTY, array('x', 'y'));

        $this->verify($input, $output);
    }

    public function testPathTokenPropertyDotPropertyDotFunction()
    {
        $input = array(
            array(Lexer::TYPE_PROPERTY => array('x', 'y', 'z'))
        );

        $output = array(Parser::TYPE_PROPERTY, array('x', 'y', 'z'));

        $this->verify($input, $output);
    }

    public function testOperatorPrecedence()
    {
        $input = array(
            array(Lexer::TYPE_OPERATOR => 'not'),
            array(Lexer::TYPE_PROPERTY => array('a', 'b')),
            array(Lexer::TYPE_OPERATOR => '<'),
            array(Lexer::TYPE_PROPERTY => array('c')),
            array(Lexer::TYPE_OPERATOR => '+'),
            array(Lexer::TYPE_PROPERTY => array('d')),
            array(Lexer::TYPE_OPERATOR => '*'),
            array(Lexer::TYPE_PROPERTY => array('e')),
            array(Lexer::TYPE_OPERATOR => 'or'),
            array(Lexer::TYPE_PROPERTY => array('f'))
        );

        $output = array(Parser::TYPE_FUNCTION, 'or', array(
            array(Parser::TYPE_FUNCTION, 'not', array(
                array(Parser::TYPE_FUNCTION, 'less', array(
                    array(Parser::TYPE_PROPERTY, array('a', 'b')),
                    array(Parser::TYPE_FUNCTION, 'plus', array(
                        array(Parser::TYPE_PROPERTY, array('c')),
                        array(Parser::TYPE_FUNCTION, 'times', array(
                            array(Parser::TYPE_PROPERTY, array('d')),
                            array(Parser::TYPE_PROPERTY, array('e'))
                        ))
                    ))
                ))
            )),
            array(Parser::TYPE_PROPERTY, array('f'))
        ));

        $this->verify($input, $output);
    }

    public function testOperatorLeftAssociativity()
    {
        $input = array(
            array(Lexer::TYPE_PROPERTY => array('a')),
            array(Lexer::TYPE_OPERATOR => '+'),
            array(Lexer::TYPE_PROPERTY => array('b')),
            array(Lexer::TYPE_OPERATOR => '-'),
            array(Lexer::TYPE_PROPERTY => array('c'))
        );

        $output = array(Parser::TYPE_FUNCTION, 'plus', array(
            array(Parser::TYPE_PROPERTY, array('a')),
            array(Parser::TYPE_FUNCTION, 'minus', array(
                array(Parser::TYPE_PROPERTY, array('b')),
                array(Parser::TYPE_PROPERTY, array('c'))
            ))
        ));

        $this->verify($input, $output);
    }

    private function verify($input, $expectedOutput)
    {
        $operators = new Operators();
        $parser = new Parser($operators);
        $actualOutput = $parser->parse($input);

        $this->assertSame($expectedOutput, $actualOutput);
    }
}