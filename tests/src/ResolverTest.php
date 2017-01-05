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
    private $string;
    private $nullBoolean;

    // Types
    private $typeBoolean;
    private $typeNullBoolean;

    public function __construct()
    {
        parent::__construct();

        // Properties
        $this->null = self::getProperty(array('null'), Types::TYPE_NULL);
        $this->boolean = self::getProperty(array('boolean'), Types::TYPE_BOOLEAN);
        $this->string = self::getProperty(array('string'), Types::TYPE_STRING);
        $this->nullBoolean = self::getProperty(array('null', 'boolean'), array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN));

        // Types
        $this->typeBoolean = Types::TYPE_BOOLEAN;
        $this->typeNullBoolean = array(Types::TYPE_OR, Types::TYPE_NULL, Types::TYPE_BOOLEAN);
    }

    public function testBooleanNull()
    {
        $input = self::getFunction('boolean',
            array($this->null)
        );

        $output = self::getFunction('boolean',
            array($this->null)
        );

        $exception = self::getPropertyException($this->null);

        $this->verify($input, $output, $exception);
    }

    public function testBooleanBoolean()
    {
        $input = self::getFunction('boolean',
            array($this->boolean)
        );

        $output = self::getFunction('boolean',
            array($this->boolean),
            $this->typeBoolean
        );

        $exception = null;

        $this->verify($input, $output, $exception);
    }

    public function testBooleanNullBoolean()
    {
        $input = self::getFunction('boolean',
            array($this->nullBoolean)
        );

        $output = null;

        $exception = self::getPropertyException($this->nullBoolean);

        $this->verify($input, $output, $exception);
    }

    public function testBooleanNameFirst()
    {
        $input = self::getFunction('boolean',
            array($this->string)
        );

        $output = null;

        $exception = self::getPropertyException($this->string);

        $this->verify($input, $output, $exception);
    }

    public function testNullBooleanBoolean()
    {
        $input = self::getFunction('null_boolean',
            array($this->boolean)
        );

        $output = self::getFunction('null_boolean',
            array($this->boolean),
            $this->typeBoolean
        );

        $exception = null;

        $this->verify($input, $output, $exception);
    }

    public function testNullBooleanNullBoolean()
    {
        $input = self::getFunction('null_boolean',
            array($this->nullBoolean)
        );

        $output = self::getFunction('null_boolean',
            array($this->nullBoolean),
            $this->typeNullBoolean
        );

        $exception = null;

        $this->verify($input, $output, $exception);
    }

    public function testBooleanParameter()
    {
        $input = self::getFunction('boolean',
            array(self::getParameter('x'))
        );

        $output = self::getFunction('boolean',
            array(self::getParameter('x', $this->typeBoolean)),
            $this->typeBoolean
        );

        $exception = null;

        $this->verify($input, $output, $exception);
    }

    public function testNullBooleanParameter()
    {
        $input = self::getFunction('null_boolean',
            array(self::getParameter('x'))
        );

        $output = self::getFunction('null_boolean',
            array(self::getParameter('x', $this->typeNullBoolean)),
            $this->typeNullBoolean
        );

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
            'type' => 'TypeException',
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
            'type' => 'TypeException',
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
            $functions = new Functions();
            $resolver = new Resolver($functions);
            $actualOutput = $resolver->resolve($input);

            $this->compare($expectedOutput, $actualOutput);
        } catch (Exception $exception) {
            $actualException = array(
                'type' => self::getClass($exception),
                'code' => self::getCode($exception),
                'data' => self::getData($exception)
            );

            $this->compare($expectedException, $actualException);
        }
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
