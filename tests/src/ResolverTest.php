<?php

namespace Datto\Cinnabari\Tests;

use Datto\Cinnabari\Exception\TypeException;
use Datto\Cinnabari\Language\Types;
use Datto\Cinnabari\Parser;
use Datto\Cinnabari\Resolver;
use Datto\Cinnabari\Tests\Language\Functions;
use Exception;
use PHPUnit_Framework_TestCase;

class ResolverTest extends PHPUnit_Framework_TestCase
{
    // Properties
    private $null;
    private $boolean;
    private $integer;
    private $string;
    private $nullBoolean;
    private $nullInteger;
    private $nullFloat;

    // Types
    private $typeNull;
    private $typeBoolean;
    private $typeNullBoolean;
    private $typeNullInteger;

    public function __construct()
    {
        parent::__construct();

        // Properties
        $this->null = self::getProperty(array('null'), Types::TYPE_NULL);
        $this->boolean = self::getProperty(array('boolean'), Types::TYPE_BOOLEAN);
        $this->integer = self::getProperty(array('integer'), Types::TYPE_INTEGER);
        $this->string = self::getProperty(array('string'), Types::TYPE_STRING);
        $this->nullBoolean = self::getProperty(array('null', 'boolean'), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN));
        $this->nullInteger = self::getProperty(array('null', 'integer'), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER));
        $this->nullFloat = self::getProperty(array('null', 'float'), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_FLOAT));

        // Types
        $this->typeNull = Types::TYPE_NULL;
        $this->typeBoolean = Types::TYPE_BOOLEAN;
        $this->typeNullBoolean = array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN);
        $this->typeNullInteger = array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_INTEGER);
    }

    public function testBooleanNull()
    {
        $input = array(Parser::TYPE_FUNCTION, 'boolean', array(
            array(Parser::TYPE_PROPERTY, array('null'), Types::TYPE_NULL)
        ));

        $output = self::getFunction('boolean', array(
            $this->null
        ));

        $exception = self::getPropertyException($this->null);

        $this->verify($input, $output, $exception);
    }

    public function testBooleanBoolean()
    {
        $input = self::getFunction('boolean', array(
            $this->boolean
        ));

        $output = self::getFunction('boolean', array(
            $this->boolean
        ), $this->typeBoolean);

        $exception = null;

        $this->verify($input, $output, $exception);
    }

    public function testBooleanNullBoolean()
    {
        $input = self::getFunction('boolean', array(
            $this->nullBoolean
        ));

        $output = null;

        $exception = self::getPropertyException($this->nullBoolean);

        $this->verify($input, $output, $exception);
    }

    public function testBooleanNameFirst()
    {
        $input = self::getFunction('boolean', array(
            $this->string
        ));

        $output = null;

        $exception = self::getPropertyException($this->string);

        $this->verify($input, $output, $exception);
    }

    public function testNullBooleanBoolean()
    {
        $input = self::getFunction('null_boolean', array(
            $this->boolean
        ));

        $output = self::getFunction('null_boolean', array(
            $this->boolean
        ), $this->typeBoolean);

        $exception = null;

        $this->verify($input, $output, $exception);
    }

    public function testNullBooleanNullBoolean()
    {
        $input = self::getFunction('null_boolean', array(
            $this->nullBoolean
        ));

        $output = self::getFunction('null_boolean', array(
            $this->nullBoolean
        ), $this->typeNullBoolean);

        $exception = null;

        $this->verify($input, $output, $exception);
    }

    public function testBooleanParameter()
    {
        $input = self::getFunction('boolean', array(
            self::getParameter('a')
        ));

        $output = self::getFunction('boolean', array(
            self::getParameter('a', $this->typeBoolean)
        ), $this->typeBoolean);

        $exception = null;

        $this->verify($input, $output, $exception);
    }

    public function testNullBooleanParameter()
    {
        $input = self::getFunction('null_boolean', array(
            self::getParameter('a')
        ));

        $output = self::getFunction('null_boolean', array(
            self::getParameter('a', $this->typeNullBoolean)
        ), $this->typeNullBoolean);

        $exception = null;

        $this->verify($input, $output, $exception);
    }

    public function testMergeNullIntegerNullInteger()
    {
        $input = self::getFunction('merge', array(
            $this->nullInteger,
            $this->nullInteger
        ));

        $output = self::getFunction('merge', array(
            $this->nullInteger,
            $this->nullInteger
        ), $this->typeNullInteger);

        $exception = null;

        $this->verify($input, $output, $exception);
    }

    public function testMergeNullIntegerNullFloat()
    {
        $input = self::getFunction('merge', array(
            $this->nullInteger,
            $this->nullFloat
        ));

        $output = null;

        $exception = self::getPropertyException($this->nullFloat);

        $this->verify($input, $output, $exception);
    }

    public function testMergeBooleanParameter()
    {
        $input = self::getFunction('merge', array(
            $this->boolean,
            self::getParameter('a')
        ));

        $output = self::getFunction('merge', array(
            $this->boolean,
            self::getParameter('a', $this->typeBoolean)
        ), $this->typeBoolean);

        $exception = null;

        $this->verify($input, $output, $exception);
    }

    public function testMergeNullBooleanParameter()
    {
        $input = self::getFunction('merge', array(
            $this->nullBoolean,
            self::getParameter('a')
        ));

        $output = self::getFunction('merge', array(
            $this->nullBoolean,
            self::getParameter('a', $this->typeNullBoolean)
        ), $this->typeNullBoolean);

        $exception = null;

        $this->verify($input, $output, $exception);
    }

    // f: NULL, NULL => NULL
    // f: NULL, BOOLEAN => NULL
    // f: BOOLEAN, NULL => NULL
    // f: BOOLEAN, BOOLEAN => BOOLEAN
    // f: NULL, STRING => NULL
    // f: STRING, NULL => NULL
    // f: STRING, STRING => BOOLEAN
    // f(nullBoolean, :x) <-- :x should be "nullBoolean" (NOT "nullBooleanString")

    /*
    public function testMergeParameterBoolean()
    {
        $input = self::getFunction('merge', array(
            self::getParameter('a'),
            $this->boolean
        ));

        $output = self::getFunction('merge', array(
            self::getParameter('a', $this->typeBoolean),
            $this->boolean
        ),    $this->typeBoolean);

        $exception = null;

        $this->verify($input, $output, $exception);
    }
    */

    public function testMergeParameterParameter()
    {
        $input = self::getFunction('merge', array(
            self::getParameter('a'),
            self::getParameter('b')
        ));

        $output = null;

        $exception = self::getParameterException(self::getParameter('a'));

        $this->verify($input, $output, $exception);
    }

    public function testBooleanMergeParameterParameter()
    {
        $input = self::getFunction('boolean', array(
            self::getFunction('merge', array(
                self::getParameter('a'),
                self::getParameter('b')
            ))
        ));

        $output = self::getFunction('boolean', array(
            self::getFunction('merge', array(
                self::getParameter('a', $this->typeBoolean),
                self::getParameter('b', $this->typeBoolean)
            ), $this->typeBoolean)
        ), $this->typeBoolean);

        $exception = null;

        $this->verify($input, $output, $exception);
    }

    public function testNullBooleanMergeParameterParameter()
    {
        $input = self::getFunction('null_boolean', array(
            self::getFunction('merge', array(
                self::getParameter('a'),
                self::getParameter('b')
            ))
        ));

        $output = self::getFunction('null_boolean', array(
            self::getFunction('merge', array(
                self::getParameter('a', $this->typeNullBoolean),
                self::getParameter('b', $this->typeNullBoolean)
            ), $this->typeNullBoolean)
        ), $this->typeNullBoolean);

        $exception = null;

        $this->verify($input, $output, $exception);
    }

    private static function getParameter($name, $type = null)
    {
        $token = array(Parser::TYPE_PARAMETER, $name);

        if ($type !== null) {
            $token[] = $type;
        }

        return $token;
    }

    private static function getProperty($name, $type)
    {
        return array(Parser::TYPE_PROPERTY, $name, $type);
    }

    private static function getFunction($name, $arguments, $type = null)
    {
        $token = array(Parser::TYPE_FUNCTION, $name, $arguments);

        if ($type !== null) {
            $token[] = $type;
        }

        return $token;
    }

    private static function getParameterException($token)
    {
        $name = $token[1];

        return array(
            'exception' => 'TypeException',
            'code' => TypeException::UNCONSTRAINED_PARAMETER,
            'data' => array(
                'parameter' => $name
            )
        );
    }

    private static function getPropertyException($token)
    {
        $name = $token[1];
        $type = $token[2];

        return array(
            'exception' => 'TypeException',
            'code' => TypeException::FORBIDDEN_PROPERTY_TYPE,
            'data' => array(
                'property' => $name,
                'type' => $type
            )
        );
    }

    private function verify($input, $expectedOutput, $expectedException)
    {
        try {
            $resolver = self::getResolver();
            $actualOutput = $resolver->resolve($input);

            $this->compare($expectedOutput, $actualOutput);
        } catch (Exception $exception) {
            $actualException = array(
                'exception' => self::getClass($exception),
                'code' => self::getCode($exception),
                'data' => self::getData($exception)
            );

            $this->compare($expectedException, $actualException);
        }
    }

    private static function getResolver()
    {
        $functions = array(
            'boolean' => array(
                array(Types::TYPE_BOOLEAN, Types::TYPE_BOOLEAN)
            ),
            'null_boolean' => array(
                array(Types::TYPE_NULL, Types::TYPE_NULL),
                array(Types::TYPE_BOOLEAN, Types::TYPE_BOOLEAN)
            ),
            'merge' => array(
                array('A', 'A', 'A')
            )
        );

        return new Resolver(
            new Functions($functions)
        );
    }

    private static function getClass(Exception $exception)
    {
        $classPath = get_class($exception);

        return self::getClassName($classPath);
    }

    private static function getCode(Exception $exception)
    {
        return $exception->getCode();
    }

    private static function getClassName($classPath)
    {
        $slash = strrpos($classPath, '\\');

        if (!is_integer($slash)) {
            return $classPath;
        }

        return substr($classPath, $slash + 1);
    }

    private static function getData(Exception $exception)
    {
        $callable = array($exception, 'getData');

        if (is_callable($callable)) {
            $data = call_user_func($callable);
        } else {
            $data = null;
        }

        return $data;
    }

    private function compare($expected, $actual)
    {
        $expectedJson = json_encode($expected);
        $actualJson = json_encode($actual);

        $this->assertSame($expectedJson, $actualJson);
    }
}
