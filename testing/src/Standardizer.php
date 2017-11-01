<?php

namespace Datto\Cinnabari\Tests;

class Standardizer
{
	public function standardizeMysql(&$mysql)
	{
		$mysql = preg_replace('~\s+~', ' ', $mysql);
	}
}
