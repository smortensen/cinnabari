<?php

namespace Datto\Cinnabari\Tests\Language;

use Datto\Cinnabari\Language;

class Functions extends Language\Functions
{
	/** @var array */
	private $functions;

	public function __construct($functions)
	{
		$this->functions = $functions;
	}

	public function getSignatures($function)
	{
		$definition = &$this->functions[$function];

		return $definition;
	}
}
