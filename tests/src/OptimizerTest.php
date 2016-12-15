<?php

namespace Datto\Cinnabari\Tests;

use Datto\Cinnabari\Optimizer;
use Datto\Cinnabari\Parser;
use PHPUnit_Framework_TestCase;

class OptimizerTest extends PHPUnit_Framework_TestCase
{
    /** @var Optimizer */
    private $optimizer;

    public function __construct()
    {
        parent::__construct();

        $this->optimizer = new Optimizer();
    }

    public function testAverageSort()
    {
        $input = array(Parser::TYPE_FUNCTION, 'average',
            array(Parser::TYPE_FUNCTION, 'sort',
                array(Parser::TYPE_PROPERTY, 'people'),
                array(Parser::TYPE_PROPERTY, 'id')
            ),
            array(Parser::TYPE_PROPERTY, 'age')
        );

        $output = array(Parser::TYPE_FUNCTION, 'average',
            array(Parser::TYPE_PROPERTY, 'people'),
            array(Parser::TYPE_PROPERTY, 'age')
        );

        $this->verify($input, $output);
    }

    public function testCountSort()
    {
        $input = array(Parser::TYPE_FUNCTION, 'count',
            array(Parser::TYPE_FUNCTION, 'sort',
                array(Parser::TYPE_PROPERTY, 'people'),
                array(Parser::TYPE_PROPERTY, 'id')
            )
        );

        $output = array(Parser::TYPE_FUNCTION, 'count',
            array(Parser::TYPE_PROPERTY, 'people')
        );

        $this->verify($input, $output);
    }

    public function testDeleteSort()
    {
        $input = array(Parser::TYPE_FUNCTION, 'delete',
            array(Parser::TYPE_FUNCTION, 'sort',
                array(Parser::TYPE_PROPERTY, 'people'),
                array(Parser::TYPE_PROPERTY, 'id')
            )
        );

        $output = array(Parser::TYPE_FUNCTION, 'delete',
            array(Parser::TYPE_PROPERTY, 'people')
        );

        $this->verify($input, $output);
    }

    public function testMaxSort()
    {
        $input = array(Parser::TYPE_FUNCTION, 'max',
            array(Parser::TYPE_FUNCTION, 'sort',
                array(Parser::TYPE_PROPERTY, 'people'),
                array(Parser::TYPE_PROPERTY, 'id')
            ),
            array(Parser::TYPE_PROPERTY, 'age')
        );

        $output = array(Parser::TYPE_FUNCTION, 'max',
            array(Parser::TYPE_PROPERTY, 'people'),
            array(Parser::TYPE_PROPERTY, 'age')
        );

        $this->verify($input, $output);
    }

    public function testMinSort()
    {
        $input = array(Parser::TYPE_FUNCTION, 'min',
            array(Parser::TYPE_FUNCTION, 'sort',
                array(Parser::TYPE_PROPERTY, 'people'),
                array(Parser::TYPE_PROPERTY, 'id')
            ),
            array(Parser::TYPE_PROPERTY, 'age')
        );

        $output = array(Parser::TYPE_FUNCTION, 'min',
            array(Parser::TYPE_PROPERTY, 'people'),
            array(Parser::TYPE_PROPERTY, 'age')
        );

        $this->verify($input, $output);
    }

    public function testSetSort()
    {
        $input = array(Parser::TYPE_FUNCTION, 'set',
            array(Parser::TYPE_FUNCTION, 'sort',
                array(Parser::TYPE_PROPERTY, 'people'),
                array(Parser::TYPE_PROPERTY, 'id')
            ),
            array(Parser::TYPE_OBJECT, array(
                'id' => array(Parser::TYPE_PARAMETER, 'id')
            ))
        );

        $output = array(Parser::TYPE_FUNCTION, 'set',
            array(Parser::TYPE_PROPERTY, 'people'),
            array(Parser::TYPE_OBJECT, array(
                'id' => array(Parser::TYPE_PARAMETER, 'id')
            ))
        );

        $this->verify($input, $output);
    }

    public function testSortSort()
    {
        $input = array(Parser::TYPE_FUNCTION, 'sort',
            array(Parser::TYPE_FUNCTION, 'sort',
                array(Parser::TYPE_PROPERTY, 'people'),
                array(Parser::TYPE_PROPERTY, 'id')
            ),
            array(Parser::TYPE_PROPERTY, 'age')
        );

        $output = array(Parser::TYPE_FUNCTION, 'sort',
            array(Parser::TYPE_PROPERTY, 'people'),
            array(Parser::TYPE_PROPERTY, 'age')
        );

        $this->verify($input, $output);
    }

    public function testSumSort()
    {
        $input = array(Parser::TYPE_FUNCTION, 'sum',
            array(Parser::TYPE_FUNCTION, 'sort',
                array(Parser::TYPE_PROPERTY, 'people'),
                array(Parser::TYPE_PROPERTY, 'id')
            ),
            array(Parser::TYPE_PROPERTY, 'age')
        );

        $output = array(Parser::TYPE_FUNCTION, 'sum',
            array(Parser::TYPE_PROPERTY, 'people'),
            array(Parser::TYPE_PROPERTY, 'age')
        );

        $this->verify($input, $output);
    }

    public function testCountSliceSort()
    {
        $input = array(Parser::TYPE_FUNCTION, 'count',
            array(Parser::TYPE_FUNCTION, 'slice',
                array(Parser::TYPE_FUNCTION, 'sort',
                    array(Parser::TYPE_PROPERTY, 'people'),
                    array(Parser::TYPE_PROPERTY, 'id')
                ),
                array(Parser::TYPE_PARAMETER, 'begin'),
                array(Parser::TYPE_PARAMETER, 'end')
            )
        );

        $output = $input;

        $this->verify($input, $output);
    }

    public function testCountFilterSort()
    {
        $input = array(Parser::TYPE_FUNCTION, 'count',
            array(Parser::TYPE_FUNCTION, 'filter',
                array(Parser::TYPE_FUNCTION, 'sort',
                    array(Parser::TYPE_PROPERTY, 'people'),
                    array(Parser::TYPE_PROPERTY, 'id')
                ),
                array(Parser::TYPE_FUNCTION, 'equal',
                    array(Parser::TYPE_PROPERTY, 'id'),
                    array(Parser::TYPE_PARAMETER, 'id')
                )
            )
        );

        $output = array(Parser::TYPE_FUNCTION, 'count',
            array(Parser::TYPE_FUNCTION, 'filter',
                array(Parser::TYPE_PROPERTY, 'people'),
                array(Parser::TYPE_FUNCTION, 'equal',
                    array(Parser::TYPE_PROPERTY, 'id'),
                    array(Parser::TYPE_PARAMETER, 'id')
                )
            )
        );

        $this->verify($input, $output);
    }

    public function testCountSortFilter()
    {
        $input = array(Parser::TYPE_FUNCTION, 'count',
            array(Parser::TYPE_FUNCTION, 'sort',
                array(Parser::TYPE_FUNCTION, 'filter',
                    array(Parser::TYPE_PROPERTY, 'people'),
                    array(Parser::TYPE_FUNCTION, 'equal',
                        array(Parser::TYPE_PROPERTY, 'id'),
                        array(Parser::TYPE_PARAMETER, 'id')
                    )
                ),
                array(Parser::TYPE_PROPERTY, 'id')
            )
        );

        $output = array(Parser::TYPE_FUNCTION, 'count',
            array(Parser::TYPE_FUNCTION, 'filter',
                array(Parser::TYPE_PROPERTY, 'people'),
                array(Parser::TYPE_FUNCTION, 'equal',
                    array(Parser::TYPE_PROPERTY, 'id'),
                    array(Parser::TYPE_PARAMETER, 'id')
                )
            )
        );

        $this->verify($input, $output);
    }

    public function testInsert()
    {
        $input = array(Parser::TYPE_FUNCTION, 'insert',
            array(Parser::TYPE_PROPERTY, 'people'),
            array(Parser::TYPE_OBJECT, array(
                'id' => array(Parser::TYPE_PARAMETER, 'id')
            ))
        );

        $output = $input;

        $this->verify($input, $output);
    }

    public function testInsertSlice()
    {
        $input = array(Parser::TYPE_FUNCTION, 'insert',
            array(Parser::TYPE_FUNCTION, 'slice',
                array(Parser::TYPE_PROPERTY, 'people'),
                array(Parser::TYPE_PARAMETER, 'begin'),
                array(Parser::TYPE_PARAMETER, 'end')
            ),
            array(Parser::TYPE_OBJECT, array(
                'id' => array(Parser::TYPE_PARAMETER, 'id')
            ))
        );

        $output = array(Parser::TYPE_FUNCTION, 'insert',
            array(Parser::TYPE_PROPERTY, 'people'),
            array(Parser::TYPE_OBJECT, array(
                'id' => array(Parser::TYPE_PARAMETER, 'id')
            ))
        );

        $this->verify($input, $output);
    }

    public function testInsertSort()
    {
        $input = array(Parser::TYPE_FUNCTION, 'insert',
            array(Parser::TYPE_FUNCTION, 'sort',
                array(Parser::TYPE_PROPERTY, 'people'),
                array(Parser::TYPE_PROPERTY, 'id')
            ),
            array(Parser::TYPE_OBJECT, array(
                'id' => array(Parser::TYPE_PARAMETER, 'id')
            ))
        );

        $output = array(Parser::TYPE_FUNCTION, 'insert',
            array(Parser::TYPE_PROPERTY, 'people'),
            array(Parser::TYPE_OBJECT, array(
                'id' => array(Parser::TYPE_PARAMETER, 'id')
            ))
        );

        $this->verify($input, $output);
    }

    public function testInsertFilter()
    {
        $input = array(Parser::TYPE_FUNCTION, 'insert',
            array(Parser::TYPE_FUNCTION, 'filter',
                array(Parser::TYPE_PROPERTY, 'people'),
                array(Parser::TYPE_FUNCTION, 'equal',
                    array(Parser::TYPE_PROPERTY, 'id'),
                    array(Parser::TYPE_PARAMETER, 'id')
                )
            ),
            array(Parser::TYPE_OBJECT, array(
                'id' => array(Parser::TYPE_PARAMETER, 'id')
            ))
        );

        $output = array(Parser::TYPE_FUNCTION, 'insert',
            array(Parser::TYPE_PROPERTY, 'people'),
            array(Parser::TYPE_OBJECT, array(
                'id' => array(Parser::TYPE_PARAMETER, 'id')
            ))
        );

        $this->verify($input, $output);
    }

    public function testInsertSliceSortFilter()
    {
        $input = array(Parser::TYPE_FUNCTION, 'insert',
            array(Parser::TYPE_FUNCTION, 'slice',
                array(Parser::TYPE_FUNCTION, 'sort',
                    array(Parser::TYPE_FUNCTION, 'filter',
                        array(Parser::TYPE_PROPERTY, 'people'),
                        array(Parser::TYPE_FUNCTION, 'equal',
                            array(Parser::TYPE_PROPERTY, 'id'),
                            array(Parser::TYPE_PARAMETER, 'id')
                        )
                    ),
                    array(Parser::TYPE_PROPERTY, 'id')
                ),
                array(Parser::TYPE_PARAMETER, 'begin'),
                array(Parser::TYPE_PARAMETER, 'end')
            ),
            array(Parser::TYPE_OBJECT, array(
                'id' => array(Parser::TYPE_PARAMETER, 'id')
            ))
        );

        $output = array(Parser::TYPE_FUNCTION, 'insert',
            array(Parser::TYPE_PROPERTY, 'people'),
            array(Parser::TYPE_OBJECT, array(
                'id' => array(Parser::TYPE_PARAMETER, 'id')
            ))
        );

        $this->verify($input, $output);
    }

    public function testGetFilterSort()
    {
        $input = array(3, 'get',
            array(3, 'filter',
                array(3, 'sort',
                    array(2, 'people'),
                    array(2, 'id')
                ),
                array(3, 'equal',
                    array(2, 'age'),
                    array(1, 'age')
                )
            ),
            array(2, 'id')
        );

        $output = array(3, 'get',
            array(3, 'sort',
                array(3, 'filter',
                    array(2, 'people'),
                    array(3, 'equal',
                        array(2, 'age'),
                        array(1, 'age')
                    )
                ),
                array(2, 'id')
            ),
            array(2, 'id')
        );

        $this->verify($input, $output);
    }

    public function testGetSliceFilterSort()
    {
        $input = array(3, 'get',
            array(3, 'slice',
                array(3, 'filter',
                    array(3, 'sort',
                        array(2, 'people'),
                        array(2, 'id')
                    ),
                    array(3, 'equal',
                        array(2, 'age'),
                        array(1, 'age')
                    )
                ),
                array(1, 'begin'),
                array(1, 'end')
            ),
            array(2, 'id')
        );

        $output = array(3, 'get',
            array(3, 'slice',
                array(3, 'sort',
                    array(3, 'filter',
                        array(2, 'people'),
                        array(3, 'equal',
                            array(2, 'age'),
                            array(1, 'age')
                        )
                    ),
                    array(2, 'id')
                ),
                array(1, 'begin'),
                array(1, 'end')
            ),
            array(2, 'id')
        );

        $this->verify($input, $output);
    }

    private function verify($input, $expectedOutput)
    {
        $actualOutput = $this->optimizer->optimize($input);

        $this->assertSame($expectedOutput, $actualOutput);
    }
}
