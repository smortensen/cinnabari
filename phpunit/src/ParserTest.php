<?php

namespace Datto\Cinnabari\Tests\Request;

use Datto\Cinnabari\Exception;
use Datto\Cinnabari\Language\Operators;
use Datto\Cinnabari\Parser;
use Datto\Cinnabari\Parser\Tokens\FunctionToken;
use Datto\Cinnabari\Parser\Tokens\ObjectToken;
use Datto\Cinnabari\Parser\Tokens\ParameterToken;
use Datto\Cinnabari\Parser\Tokens\PropertyToken;
use PHPUnit_Framework_TestCase;

class ParserTest extends PHPUnit_Framework_TestCase
{
	public function testA()
	{
		$input = null;

		$output = Exception::invalidType($input);

		$this->verify($input, $output);
	}

	public function testB()
	{
		$input = false;

		$output = Exception::invalidType($input);

		$this->verify($input, $output);
	}

	public function testC()
	{
		$input = '';

		$output = Exception::invalidSyntax($input, 0);

		$this->verify($input, $output);
	}

	public function testD()
	{
		$input = ':x';

		$output = new ParameterToken('x');

		$this->verify($input, $output);
	}

	public function testE()
	{
		$input = ':_';

		$output = new ParameterToken('_');

		$this->verify($input, $output);
	}

	public function testF()
	{
		$input = ':0';

		$output = new ParameterToken('0');

		$this->verify($input, $output);
	}

	public function testG()
	{
		$input = ':Php_7';

		$output = new ParameterToken('Php_7');

		$this->verify($input, $output);
	}

	public function testH()
	{
		$input = ':*';

		$output = Exception::invalidSyntax($input, 0);

		$this->verify($input, $output);
	}

	public function testI()
	{
		$input = ':';

		$output = Exception::invalidSyntax($input, 0);

		$this->verify($input, $output);
	}

	public function testJ()
	{
		$input = ':x ';

		$output = Exception::invalidSyntax($input, 2);

		$this->verify($input, $output);
	}

	public function testK()
	{
		$input = 'notes';

		$output = new PropertyToken(array('notes'));

		$this->verify($input, $output);
	}

	public function testL()
	{
		$input = 'x';

		$output = new PropertyToken(array('x'));

		$this->verify($input, $output);
	}

	public function testM()
	{
		$input = 'x . y';

		$output = new PropertyToken(array('x', 'y'));

		$this->verify($input, $output);
	}

	public function testN()
	{
		$input = 'x . y . z';

		$output = new PropertyToken(array('x', 'y', 'z'));

		$this->verify($input, $output);
	}

	public function testO()
	{
		$input = '.';

		$output = Exception::invalidSyntax($input, 0);

		$this->verify($input, $output);
	}

	public function testP()
	{
		$input = 'x .';

		$output = Exception::invalidSyntax($input, 3);

		$this->verify($input, $output);
	}

	public function testQ()
	{
		$input = 'f()';

		$output = new FunctionToken('f', array());

		$this->verify($input, $output);
	}

	public function testR()
	{
		$input = 'f(x)';

		$output = new FunctionToken('f', array(
			new PropertyToken(array('x'))
		));

		$this->verify($input, $output);
	}

	public function testS()
	{
		$input = 'f(*)';

		$output = Exception::invalidSyntax($input, 2);

		$this->verify($input, $output);
	}

	public function testT()
	{
		$input = 'f(:x, y)';

		$output = new FunctionToken('f', array(
			new ParameterToken('x'),
			new PropertyToken(array('y'))
		));

		$this->verify($input, $output);
	}

	public function testU()
	{
		$input = 'f(:x, *)';

		$output = Exception::invalidSyntax($input, 6);

		$this->verify($input, $output);
	}

	public function testV()
	{
		$input = 'f(';

		$output = Exception::invalidSyntax($input, 2);

		$this->verify($input, $output);
	}

	public function testW()
	{
		$input = 'x.f()';

		$output = Exception::invalidSyntax($input, 3);

		$this->verify($input, $output);
	}

	public function testX()
	{
		$input = '(';

		$output = Exception::invalidSyntax($input, 1);

		$this->verify($input, $output);
	}

	public function testY()
	{
		$input = '()';

		$output = Exception::invalidSyntax($input, 1);

		$this->verify($input, $output);
	}

	public function testZ()
	{
		$input = '(:x)';

		$output = new ParameterToken('x');

		$this->verify($input, $output);
	}

	public function testAA()
	{
		$input = '{}';

		$output = Exception::invalidSyntax($input, 1);

		$this->verify($input, $output);
	}

	public function testAB()
	{
		$input = "{\n\t\"x\": :x\n}";

		$output = new ObjectToken(array(
			'x' => new ParameterToken('x')
		));

		$this->verify($input, $output);
	}

	public function testAC()
	{
		$input = "{\n\t\"x\": x\n}";

		$output = new ObjectToken(array(
			'x' => new PropertyToken(array('x'))
		));

		$this->verify($input, $output);
	}

	public function testAD()
	{
		$input = '{6: x}';

		$output = Exception::invalidSyntax($input, 1);

		$this->verify($input, $output);
	}

	public function testAE()
	{
		$input = '{"x" x}';

		$output = Exception::invalidSyntax($input, 4);


		$this->verify($input, $output);
	}

	public function testAF()
	{
		$input = '{"x": *}';

		$output = Exception::invalidSyntax($input, 6);

		$this->verify($input, $output);
	}

	public function testAG()
	{
		$input = '{"x": x';

		$output = Exception::invalidSyntax($input, 7);

		$this->verify($input, $output);
	}

	public function testAH()
	{
		$input = "{\n\t\"x\": :x,\n\t\"y\": y\n}";

		$output = new ObjectToken(array(
			'x' => new ParameterToken('x'),
			'y' => new PropertyToken(array('y'))
		));

		$this->verify($input, $output);
	}

	public function testAI()
	{
		$input = '{"x": :x, "x": x}';

		$output = new ObjectToken(array(
			'x' => new PropertyToken(array('x'))
		));

		$this->verify($input, $output);
	}

	public function testAJ()
	{
		$input = '{"x": :x "x": x }';

		$output = Exception::invalidSyntax($input, 8);

		$this->verify($input, $output);
	}

	public function testAK()
	{
		$input = '{"x": :x, }';

		$output = Exception::invalidSyntax($input, 10);

		$this->verify($input, $output);
	}

	public function testAL()
	{
		$input = 'not :x';

		$output = new FunctionToken('not', array(
			new ParameterToken('x')
		));

		$this->verify($input, $output);
	}

	public function testAM()
	{
		$input = 'f() + (:c)';

		$output = new FunctionToken('plus', array(
			new FunctionToken('f', array()),
			new ParameterToken('c')
		));

		$this->verify($input, $output);
	}

	public function testAN()
	{
		$input = 'a * b';

		$output = new FunctionToken('times', array(
			new PropertyToken(array('a')),
			new PropertyToken(array('b'))
		));

		$this->verify($input, $output);
	}

	public function testAO()
	{
		$input = 'a / b';

		$output = new FunctionToken('divides', array(
			new PropertyToken(array('a')),
			new PropertyToken(array('b'))
		));

		$this->verify($input, $output);
	}

	public function testAP()
	{
		$input = 'a + b';

		$output = new FunctionToken('plus', array(
			new PropertyToken(array('a')),
			new PropertyToken(array('b'))
		));

		$this->verify($input, $output);
	}

	public function testAQ()
	{
		$input = 'a - b';

		$output = new FunctionToken('minus', array(
			new PropertyToken(array('a')),
			new PropertyToken(array('b'))
		));

		$this->verify($input, $output);
	}

	public function testAR()
	{
		$input = 'a < b';

		$output = new FunctionToken('less', array(
			new PropertyToken(array('a')),
			new PropertyToken(array('b'))
		));

		$this->verify($input, $output);
	}

	public function testAS()
	{
		$input = 'a <= b';

		$output = new FunctionToken('lessEqual', array(
			new PropertyToken(array('a')),
			new PropertyToken(array('b'))
		));

		$this->verify($input, $output);
	}

	public function testAT()
	{
		$input = 'a = b';

		$output = new FunctionToken('equal', array(
			new PropertyToken(array('a')),
			new PropertyToken(array('b'))
		));

		$this->verify($input, $output);
	}

	public function testAU()
	{
		$input = 'a != b';

		$output = new FunctionToken('notEqual', array(
			new PropertyToken(array('a')),
			new PropertyToken(array('b'))
		));

		$this->verify($input, $output);
	}

	public function testAV()
	{
		$input = 'a >= b';

		$output = new FunctionToken('greaterEqual', array(
			new PropertyToken(array('a')),
			new PropertyToken(array('b'))
		));

		$this->verify($input, $output);
	}

	public function testAW()
	{
		$input = 'a > b';

		$output = new FunctionToken('greater', array(
			new PropertyToken(array('a')),
			new PropertyToken(array('b'))
		));

		$this->verify($input, $output);
	}

	public function testAX()
	{
		$input = 'not a';

		$output = new FunctionToken('not', array(
			new PropertyToken(array('a'))
		));

		$this->verify($input, $output);
	}

	public function testAY()
	{
		$input = 'a and b';

		$output = new FunctionToken('and', array(
			new PropertyToken(array('a')),
			new PropertyToken(array('b'))
		));

		$this->verify($input, $output);
	}

	public function testAZ()
	{
		$input = 'a or b';

		$output = new FunctionToken('or', array(
			new PropertyToken(array('a')),
			new PropertyToken(array('b'))
		));

		$this->verify($input, $output);
	}

	public function testAAA()
	{
		$input = 'a * b + c < d';

		$output = new FunctionToken('less', array(
			new FunctionToken('plus', array(
				new FunctionToken('times', array(
					new PropertyToken(array('a')),
					new PropertyToken(array('b'))
				)),
				new PropertyToken(array('c'))
			)),
			new PropertyToken(array('d'))
		));

		$this->verify($input, $output);
	}

	public function testAAB()
	{
		$input = 'a * b < c + d';

		$output = new FunctionToken('less', array(
			new FunctionToken('times', array(
				new PropertyToken(array('a')),
				new PropertyToken(array('b'))
			)),
			new FunctionToken('plus', array(
				new PropertyToken(array('c')),
				new PropertyToken(array('d'))
			))
		));

		$this->verify($input, $output);
	}

	public function testAAC()
	{
		$input = 'a + b * c < d';

		$output = new FunctionToken('less', array(
			new FunctionToken('plus', array(
				new PropertyToken(array('a')),
				new FunctionToken('times', array(
					new PropertyToken(array('b')),
					new PropertyToken(array('c'))
				))
			)),
			new PropertyToken(array('d'))
		));

		$this->verify($input, $output);
	}

	public function testAAD()
	{
		$input = 'a + b < c * d';

		$output = new FunctionToken('less', array(
			new FunctionToken('plus', array(
				new PropertyToken(array('a')),
				new PropertyToken(array('b'))
			)),
			new FunctionToken('times', array(
				new PropertyToken(array('c')),
				new PropertyToken(array('d'))
			))
		));

		$this->verify($input, $output);
	}

	public function testAAE()
	{
		$input = 'a < b * c + d';

		$output = new FunctionToken('less', array(
			new PropertyToken(array('a')),
			new FunctionToken('plus', array(
				new FunctionToken('times', array(
					new PropertyToken(array('b')),
					new PropertyToken(array('c'))
				)),
				new PropertyToken(array('d'))
			))
		));

		$this->verify($input, $output);
	}

	public function testAAF()
	{
		$input = 'a < b + c * d';

		$output = new FunctionToken('less', array(
			new PropertyToken(array('a')),
			new FunctionToken('plus', array(
				new PropertyToken(array('b')),
				new FunctionToken('times', array(
					new PropertyToken(array('c')),
					new PropertyToken(array('d'))
				))
			))
		));

		$this->verify($input, $output);
	}

	public function testAAG()
	{
		$input = '(a * b) + c < d';

		$output = new FunctionToken('less', array(
			new FunctionToken('plus', array(
				new FunctionToken('times', array(
					new PropertyToken(array('a')),
					new PropertyToken(array('b'))
				)),
				new PropertyToken(array('c'))
			)),
			new PropertyToken(array('d'))
		));

		$this->verify($input, $output);
	}

	public function testAAH()
	{
		$input = 'a * (b + c) < d';

		$output = new FunctionToken('less', array(
			new FunctionToken('times', array(
				new PropertyToken(array('a')),
				new FunctionToken('plus', array(
					new PropertyToken(array('b')),
					new PropertyToken(array('c'))
				))
			)),
			new PropertyToken(array('d'))
		));

		$this->verify($input, $output);
	}

	public function testAAI()
	{
		$input = 'a * b + (c < d)';

		$output = new FunctionToken('plus', array(
			new FunctionToken('times', array(
				new PropertyToken(array('a')),
				new PropertyToken(array('b'))
			)),
			new FunctionToken('less', array(
				new PropertyToken(array('c')),
				new PropertyToken(array('d'))
			))
		));

		$this->verify($input, $output);
	}

	public function testAAJ()
	{
		$input = 'a or not b';

		$output = new FunctionToken('or', array(
			new PropertyToken(array('a')),
			new FunctionToken('not', array(
				new PropertyToken(array('b'))
			))
		));

		$this->verify($input, $output);
	}

	public function testAAK()
	{
		$input = 'not not a';

		$output = new FunctionToken('not', array(
			new FunctionToken('not', array(
				new PropertyToken(array('a'))
			))
		));

		$this->verify($input, $output);
	}

	private function verify($input, $expectedOutput)
	{
		try {
			$operators = new Operators();
			$parser = new Parser($operators);
			$output = $parser->parse($input);
			$exception = null;
		} catch (Exception $exception) {
			$output = null;
		}

		if (is_a($expectedOutput, 'Datto\\Cinnabari\\Exception')) {
			$this->assertSame(
				self::summarizeException($expectedOutput),
				self::summarizeException($exception)
			);
		} else {
			$this->assertEquals($expectedOutput, $output);
		}
	}

	/**
	 * @param null|Exception $exception
	 * @return array
	 */
	private static function summarizeException($exception)
	{
		if ($exception === null) {
			return null;
		}

		$code = $exception->getCode();
		$message = $exception->getMessage();
		$data = $exception->getData();

		return array($code, $message, $data);
	}
}
