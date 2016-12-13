<?php

namespace Datto\Cinnabari\Tests;

use Datto\Cinnabari\Lexer;
use Datto\Cinnabari\Parser;
use PHPUnit_Framework_TestCase;

class ParserTest extends PHPUnit_Framework_TestCase
{
    /** @var Parser */
    private $parser;

    public function __construct()
    {
        parent::__construct();

        $this->parser = new Parser();
    }

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

        $output = array(Parser::TYPE_PROPERTY, 'x');

        $this->verify($input, $output);
    }

    public function testFunctionTokenNoArguments()
    {
        $input = array(
            array(Lexer::TYPE_FUNCTION => array('f'))
        );

        $output = array(Parser::TYPE_FUNCTION, 'f');

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

        $output = array(Parser::TYPE_FUNCTION, 'f',
            array(Parser::TYPE_PARAMETER, 'x')
        );

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

        $output = array(Parser::TYPE_FUNCTION, 'f',
            array(Parser::TYPE_PARAMETER, 'x'),
            array(Parser::TYPE_PROPERTY, 'y')
        );

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
            'b' => array(Parser::TYPE_PROPERTY, 'y')
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

        $output = array(Parser::TYPE_PROPERTY, 'x', 'y');

        $this->verify($input, $output);
    }

    public function testPathTokenPropertyDotPropertyDotFunction()
    {
        $input = array(
            array(Lexer::TYPE_PROPERTY => array('x', 'y', 'z'))
        );

        $output = array(Parser::TYPE_PROPERTY, 'x', 'y', 'z');

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

        $output = array(Parser::TYPE_FUNCTION, 'or',
            array(Parser::TYPE_FUNCTION, 'not',
                array(Parser::TYPE_FUNCTION, 'less',
                    array(Parser::TYPE_PROPERTY, 'a', 'b'),
                    array(Parser::TYPE_FUNCTION, 'plus',
                        array(Parser::TYPE_PROPERTY, 'c'),
                        array(Parser::TYPE_FUNCTION, 'times',
                            array(Parser::TYPE_PROPERTY, 'd'),
                            array(Parser::TYPE_PROPERTY, 'e')
                        )
                    )
                )
            ),
            array(Parser::TYPE_PROPERTY, 'f')
        );

        $this->verify($input, $output);
    }

    private function verify($input, $expectedOutput)
    {
        $actualOutput = $this->parser->parse($input);

        $this->assertSame($expectedOutput, $actualOutput);
    }
}
