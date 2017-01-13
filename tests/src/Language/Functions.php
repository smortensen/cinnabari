<?php

namespace Datto\Cinnabari\Tests\Language;

use Datto\Cinnabari\Language;

class Functions extends Language\Functions
{
    /** @var boolean[] */
    private static $mapFunctions = array(
        'average' => true,
        'count' => true,
        'delete' => true,
        'filter' => true,
        'get' => true,
        'insert' => true,
        'max' => true,
        'min' => true,
        'set' => true,
        'slice' => true,
        'sort' => true,
        'sum' => true
    );

    /** @var array */
    private $functions;

    public function __construct($functions)
    {
        $this->functions = $functions;
    }

    public function isMapFunction($function)
    {
        return isset(self::$mapFunctions[$function]);
    }

    public function getSignatures($function)
    {
        $definition = &$this->functions[$function];

        return $definition;
    }
}
