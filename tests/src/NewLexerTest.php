<?php

namespace Datto\Cinnabari\Tests;

use Datto\Cinnabari\NewLexer;
use Datto\Cinnabari\Exception\LexerException;
use PHPUnit_Framework_TestCase;

class NewLexerTest extends PHPUnit_Framework_TestCase
{
    /** @var NewLexer */
    private $lexer;

    public function __construct()
    {
        parent::__construct();

        $this->lexer = new NewLexer();
    }

    public function testInvalidNull()
    {
        $input = null;

        $output = self::exceptionInvalidType($input);

        $this->verifyException($input, $output);
    }

    public function testInvalidFalse()
    {
        $input = false;

        $output = self::exceptionInvalidType($input);

        $this->verifyException($input, $output);
    }

    public function testInvalidEmptyString()
    {
        $input = '';

        $output = self::exceptionInvalidSyntax($input, 0);

        $this->verifyException($input, $output);
    }

    public function testValidParameterBasic()
    {
        $input = ':x';

        $output = array(
            array(NewLexer::TYPE_PARAMETER => 'x')
        );

        $this->verifyOutput($input, $output);
    }

    public function testValidParameterUnderscore()
    {
        $input = ':_';

        $output = array(
            array(NewLexer::TYPE_PARAMETER => '_')
        );

        $this->verifyOutput($input, $output);
    }

    public function testValidParameterDigit()
    {
        $input = ':0';

        $output = array(
            array(NewLexer::TYPE_PARAMETER => '0')
        );

        $this->verifyOutput($input, $output);
    }

    public function testValidParameterComplex()
    {
        $input = ':Php_7';

        $output = array(
            array(NewLexer::TYPE_PARAMETER => 'Php_7')
        );

        $this->verifyOutput($input, $output);
    }

    public function testInvalidParameterIllegalCharacter()
    {
        $input = ':*';

        $output = self::exceptionInvalidSyntax($input, 0);

        $this->verifyException($input, $output);
    }

    public function testInvalidParameterEmptyString()
    {
        $input = ':';

        $output = self::exceptionInvalidSyntax($input, 0);

        $this->verifyException($input, $output);
    }

    public function testInvalidWhitespace()
    {
        $input = ":x ";

        $output = self::exceptionInvalidSyntax($input, 2);

        $this->verifyException($input, $output);
    }

    public function testValidProperty()
    {
        $input = 'x';

        $output = array(
            array(NewLexer::TYPE_PROPERTY => array('x'))
        );

        $this->verifyOutput($input, $output);
    }

    public function testValidPropertyPath()
    {
        $input = 'x . y';

        $output = array(
            array(NewLexer::TYPE_PROPERTY => array('x', 'y'))
        );

        $this->verifyOutput($input, $output);
    }

    public function testInvalidPropertyPathEmptyPropertyNames()
    {
        $input = '.';

        $output = self::exceptionInvalidSyntax($input, 0);

        $this->verifyException($input, $output);
    }

    public function testInvalidPropertyPathValidPropertyEmptyPropertyName()
    {
        $input = 'x .';

        $output = self::exceptionInvalidSyntax($input, 3);

        $this->verifyException($input, $output);
    }

    public function testValidFunctionZeroArguments()
    {
        $input = 'f()';

        $output = array(
            array(NewLexer::TYPE_FUNCTION => array('f'))
        );

        $this->verifyOutput($input, $output);
    }

    public function testValidFunctionOneArgument()
    {
        $input = 'f(x)';

        $output = array(
            array(NewLexer::TYPE_FUNCTION => array(
                'f',
                array(
                    array(NewLexer::TYPE_PROPERTY => array('x'))
                )
            ))
        );

        $this->verifyOutput($input, $output);
    }

    public function testInvalidFunctionOneIllegalArgument()
    {
        $input = 'f(*)';

        $output = self::exceptionInvalidSyntax($input, 2);

        $this->verifyException($input, $output);
    }

    public function testValidFunctionTwoArguments()
    {
        $input = 'f(:x, y)';

        $output = array(
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

        $this->verifyOutput($input, $output);
    }

    public function testInvalidFunctionOneLegalArgumentOneIllegalArgument()
    {
        $input = 'f(:x, *)';

        $output = self::exceptionInvalidSyntax($input, 6);

        $this->verifyException($input, $output);
    }

    public function testInvalidFunctionMissingClosingParenthesis()
    {
        $input = 'f(';

        $output = self::exceptionInvalidSyntax($input, 2);

        $this->verifyException($input, $output);
    }

    public function testInvalidGroupMissingClosingParenthesis()
    {
        $input = '(';

        $output = self::exceptionInvalidSyntax($input, 1);

        $this->verifyException($input, $output);
    }

    public function testInvalidGroupEmptyBody()
    {
        $input = '()';

        $output = self::exceptionInvalidSyntax($input, 1);

        $this->verifyException($input, $output);
    }

    public function testValidGroupParameterBody()
    {
        $input = '(:x)';

        $output = array(
            array(NewLexer::TYPE_GROUP => array(
                array(NewLexer::TYPE_PARAMETER => 'x')
            ))
        );

        $this->verifyOutput($input, $output);
    }

    public function testInvalidObjectEmptyBody()
    {
        $input = '{}';

        $output = self::exceptionInvalidSyntax($input, 1);

        $this->verifyException($input, $output);
    }

    public function testValidObjectParameterValue()
    {
        $input = '{
            "x": :x
        }';

        $output = array(
            array(NewLexer::TYPE_OBJECT => array(
                'x' => array(
                    array(NewLexer::TYPE_PARAMETER => 'x')
                )
            ))
        );

        $this->verifyOutput($input, $output);
    }

    public function testValidObjectPropertyValue()
    {
        $input = '{
            "x": x
        }';

        $output = array(
            array(NewLexer::TYPE_OBJECT => array(
                'x' => array(
                    array(NewLexer::TYPE_PROPERTY => array('x'))
                )
            ))
        );

        $this->verifyOutput($input, $output);
    }

    public function testInvalidObjectIllegalKey()
    {
        $input = '{6: x}';

        $output = self::exceptionInvalidSyntax($input, 1);

        $this->verifyException($input, $output);
    }

    public function testInvalidObjectMissingKeyValueSeparator()
    {
        $input = '{"x" x}';

        $output = self::exceptionInvalidSyntax($input, 4);

        $this->verifyException($input, $output);
    }

    public function testInvalidObjectIllegalProperty()
    {
        $input = '{"x": *}';

        $output = self::exceptionInvalidSyntax($input, 6);

        $this->verifyException($input, $output);
    }

    public function testInvalidObjectMissingClosingBrace()
    {
        $input = '{"x": x';

        $output = self::exceptionInvalidSyntax($input, 7);

        $this->verifyException($input, $output);
    }

    public function testValidObjectPropertyValueParameterValue()
    {
        $input = '{
            "x": :x,
            "y": x
        }';

        $output = array(
            array(NewLexer::TYPE_OBJECT => array(
                'x' => array(
                    array(NewLexer::TYPE_PARAMETER => 'x')
                ),
                'y' => array(
                    array(NewLexer::TYPE_PROPERTY => array('x'))
                )
            ))
        );

        $this->verifyOutput($input, $output);
    }

    public function testValidObjectDuplicateKey()
    {
        $input = '{
            "x": :x,
            "x": x
        }';

        $output = array(
            array(NewLexer::TYPE_OBJECT => array(
                'x' => array(
                    array(NewLexer::TYPE_PROPERTY => array('x'))
                )
            ))
        );

        $this->verifyOutput($input, $output);
    }

    public function testInvalidObjectMissingPairSeparator()
    {
        $input = '{"x": :x "x": x}';

        $output = self::exceptionInvalidSyntax($input, 8);

        $this->verifyException($input, $output);
    }

    public function testValidUnaryExpressionParameter()
    {
        $input = 'not :x';

        $output = array(
            array(NewLexer::TYPE_OPERATOR => 'not'),
            array(NewLexer::TYPE_PARAMETER => 'x')
        );

        $this->verifyOutput($input, $output);
    }

    public function testInvalidBinaryExpressionPropertyDotFunction()
    {
        $input = 'x.f()';

        $output = self::exceptionInvalidSyntax($input, 3);

        $this->verifyException($input, $output);
    }

    public function testValidBinaryExpressionExpressionPlusExpression()
    {
        $input = 'f() + (:c)';

        $output = array(
            array(NewLexer::TYPE_FUNCTION => array('f')),
            array(NewLexer::TYPE_OPERATOR => '+'),
            array(NewLexer::TYPE_GROUP => array(
                array(NewLexer::TYPE_PARAMETER => 'c')
            ))
        );

        $this->verifyOutput($input, $output);
    }

    public function testValidBinaryExpressionOperators()
    {
        $input = 'a + b - c * d / e <= f < g != h = i >= j > k and l or m';

        $output = array(
            array(NewLexer::TYPE_PROPERTY => array('a')),
            array(NewLexer::TYPE_OPERATOR => '+'),
            array(NewLexer::TYPE_PROPERTY => array('b')),
            array(NewLexer::TYPE_OPERATOR => '-'),
            array(NewLexer::TYPE_PROPERTY => array('c')),
            array(NewLexer::TYPE_OPERATOR => '*'),
            array(NewLexer::TYPE_PROPERTY => array('d')),
            array(NewLexer::TYPE_OPERATOR => '/'),
            array(NewLexer::TYPE_PROPERTY => array('e')),
            array(NewLexer::TYPE_OPERATOR => '<='),
            array(NewLexer::TYPE_PROPERTY => array('f')),
            array(NewLexer::TYPE_OPERATOR => '<'),
            array(NewLexer::TYPE_PROPERTY => array('g')),
            array(NewLexer::TYPE_OPERATOR => '!='),
            array(NewLexer::TYPE_PROPERTY => array('h')),
            array(NewLexer::TYPE_OPERATOR => '='),
            array(NewLexer::TYPE_PROPERTY => array('i')),
            array(NewLexer::TYPE_OPERATOR => '>='),
            array(NewLexer::TYPE_PROPERTY => array('j')),
            array(NewLexer::TYPE_OPERATOR => '>'),
            array(NewLexer::TYPE_PROPERTY => array('k')),
            array(NewLexer::TYPE_OPERATOR => 'and'),
            array(NewLexer::TYPE_PROPERTY => array('l')),
            array(NewLexer::TYPE_OPERATOR => 'or'),
            array(NewLexer::TYPE_PROPERTY => array('m')),
        );

        $this->verifyOutput($input, $output);
    }

    private static function exceptionInvalidType($statement)
    {
        return array(
            'code' => LexerException::TYPE_INVALID,
            'data' => array(
                'statement' => $statement
            )
        );
    }

    private static function exceptionInvalidSyntax($statement, $position)
    {
        return array(
            'code' => LexerException::SYNTAX_INVALID,
            'data' => array(
                'statement' => $statement,
                'position' => $position
            )
        );
    }

    private function verifyException($input, $expected)
    {
        try {
            $this->lexer->tokenize($input);

            $actual = null;
        } catch (LexerException $exception) {
            $actual = array(
                'code' => $exception->getCode(),
                'data' => $exception->getData()
            );
        }

        $this->assertSame($expected, $actual);
    }

    private function verifyOutput($input, $expectedOutput)
    {
        $actualOutput = $this->lexer->tokenize($input);

        $this->assertSame($expectedOutput, $actualOutput);
    }
}
