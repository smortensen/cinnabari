<?php

namespace Datto\Cinnabari\Tests;

use Datto\Cinnabari\NewLexer;
use Datto\Cinnabari\NewParser;
use PHPUnit_Framework_TestCase;

class NewParserTest extends PHPUnit_Framework_TestCase
{
    /** @var NewParser */
    private $parser;

    public function __construct()
    {
        parent::__construct();

        $this->parser = new NewParser();
    }

    public function testParameterToken()
    {
        $input = array(
            array(NewLexer::TYPE_PARAMETER => 'x')
        );

        $output = array(NewParser::TYPE_PARAMETER, 'x');

        $this->verify($input, $output);
    }

    public function testPropertyToken()
    {
        $input = array(
            array(NewLexer::TYPE_PROPERTY => array('x'))
        );

        $output = array(NewParser::TYPE_PROPERTY, 'x');

        $this->verify($input, $output);
    }

    public function testFunctionTokenNoArguments()
    {
        $input = array(
            array(NewLexer::TYPE_FUNCTION => array('f'))
        );

        $output = array(NewParser::TYPE_FUNCTION, 'f');

        $this->verify($input, $output);
    }

    public function testFunctionTokenOneArgument()
    {
        $input = array(
            array(NewLexer::TYPE_FUNCTION => array(
                'f',
                array(
                    array(NewLexer::TYPE_PARAMETER => 'x')
                )
            ))
        );

        $output = array(NewParser::TYPE_FUNCTION, 'f',
            array(NewParser::TYPE_PARAMETER, 'x')
        );

        $this->verify($input, $output);
    }

    public function testFunctionTokenTwoArguments()
    {
        $input = array(
            array(NewLexer::TYPE_FUNCTION => array(
                'f',
                array(
                    array(NewLexer::TYPE_PARAMETER => 'x')
                ),
                array(
                    array(NewLexer::TYPE_PROPERTY => array('y'))
                )
            ))
        );

        $output = array(NewParser::TYPE_FUNCTION, 'f',
            array(NewParser::TYPE_PARAMETER, 'x'),
            array(NewParser::TYPE_PROPERTY, 'y')
        );

        $this->verify($input, $output);
    }

    public function testObjectTokenOneKey()
    {
        $input = array(
            array(NewLexer::TYPE_OBJECT => array(
                'a' => array(
                    array(NewLexer::TYPE_PARAMETER => 'x')
                )
            ))
        );

        $output = array(NewParser::TYPE_OBJECT, array(
            'a' => array(NewParser::TYPE_PARAMETER, 'x'))
        );

        $this->verify($input, $output);
    }

    public function testObjectTokenTwoKeys()
    {
        $input = array(
            array(NewLexer::TYPE_OBJECT => array(
                'a' => array(
                    array(NewLexer::TYPE_PARAMETER => 'x')
                ),
                'b' => array(
                    array(NewLexer::TYPE_PROPERTY => array('y'))
                )
            ))
        );

        $output = array(NewParser::TYPE_OBJECT, array(
            'a' => array(NewParser::TYPE_PARAMETER, 'x'),
            'b' => array(NewParser::TYPE_PROPERTY, 'y')
        ));

        $this->verify($input, $output);
    }

    public function testGroupToken()
    {
        $input = array(
            array(NewLexer::TYPE_GROUP => array(
                array(NewLexer::TYPE_PARAMETER => 'x')
            ))
        );

        $output = array(NewParser::TYPE_PARAMETER, 'x');

        $this->verify($input, $output);
    }

    public function testPathTokenPropertyDotProperty()
    {
        $input = array(
            array(NewLexer::TYPE_PROPERTY => array('x', 'y'))
        );

        $output = array(NewParser::TYPE_PROPERTY, 'x', 'y');

        $this->verify($input, $output);
    }

    public function testPathTokenPropertyDotPropertyDotFunction()
    {
        $input = array(
            array(NewLexer::TYPE_PROPERTY => array('x', 'y', 'z'))
        );

        $output = array(NewParser::TYPE_PROPERTY, 'x', 'y', 'z');

        $this->verify($input, $output);
    }

    public function testOperatorPrecedence()
    {
        $input = array(
            array(NewLexer::TYPE_OPERATOR => 'not'),
            array(NewLexer::TYPE_PROPERTY => array('a', 'b')),
            array(NewLexer::TYPE_OPERATOR => '<'),
            array(NewLexer::TYPE_PROPERTY => array('c')),
            array(NewLexer::TYPE_OPERATOR => '+'),
            array(NewLexer::TYPE_PROPERTY => array('d')),
            array(NewLexer::TYPE_OPERATOR => '*'),
            array(NewLexer::TYPE_PROPERTY => array('e')),
            array(NewLexer::TYPE_OPERATOR => 'or'),
            array(NewLexer::TYPE_PROPERTY => array('f'))
        );

        $output = array(NewParser::TYPE_FUNCTION, 'or',
            array(NewParser::TYPE_FUNCTION, 'not',
                array(NewParser::TYPE_FUNCTION, 'less',
                    array(NewParser::TYPE_PROPERTY, 'a', 'b'),
                    array(NewParser::TYPE_FUNCTION, 'plus',
                        array(NewParser::TYPE_PROPERTY, 'c'),
                        array(NewParser::TYPE_FUNCTION, 'times',
                            array(NewParser::TYPE_PROPERTY, 'd'),
                            array(NewParser::TYPE_PROPERTY, 'e')
                        )
                    )
                )
            ),
            array(NewParser::TYPE_PROPERTY, 'f')
        );

        $this->verify($input, $output);
    }

    private function verify($input, $expectedOutput)
    {
        $actualOutput = $this->parser->parse($input);

        $this->assertSame($expectedOutput, $actualOutput);
    }
}
