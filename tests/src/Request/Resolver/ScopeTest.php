<?php

namespace Datto\Cinnabari\Tests\Request\Resolver;

use Datto\Cinnabari\Request\Language\Types;
use Datto\Cinnabari\Request\Resolver\Scope;
use PHPUnit_Framework_TestCase;

class ScopeTest extends PHPUnit_Framework_TestCase
{
    public function testTypeSpecification()
    {
        $context = new Scope();
        $context->set(0, 'A');
        $context->set(0, Types::TYPE_STRING);

        $type = $context->get(0);

        $this->assertSame($type, Types::TYPE_STRING);
    }

    public function testTypeGeneralization()
    {
        $context = new Scope();
        $context->set(0, Types::TYPE_STRING);
        $context->set(0, 'A');

        $type = $context->get(0);

        $this->assertSame($type, Types::TYPE_STRING);
    }

    public function testTypePropagation()
    {
        $context = new Scope();
        $context->set('x', array(Types::TYPE_ARRAY, 'A'));
        $context->set('y', 'A');
        $context->set('y', Types::TYPE_BOOLEAN);

        $type = $context->get('x');

        $this->assertSame($type, array(Types::TYPE_ARRAY, Types::TYPE_BOOLEAN));
    }

    public function testTypeConfirmation()
    {
        $context = new Scope();
        $context->set(0, Types::TYPE_STRING);

        $result = $context->set(0, Types::TYPE_STRING);

        $this->assertSame($result, true);
    }

    public function testTypeContradiction()
    {
        $context = new Scope();
        $context->set(0, Types::TYPE_STRING);

        $result = $context->set(0, Types::TYPE_FLOAT);

        $this->assertSame($result, false);
    }

    public function testIsAbstractBoolean()
    {
        $context = new Scope();
        $context->set(0, Types::TYPE_BOOLEAN);

        $isAbstract = $context->isAbstract(0);

        $this->assertSame($isAbstract, false);
    }

    public function testIsAbstractAbstract()
    {
        $context = new Scope();
        $context->set(0, 'A');

        $isAbstract = $context->isAbstract(0);

        $this->assertSame($isAbstract, true);
    }

    public function testIsAbstractIntegerArray()
    {
        $context = new Scope();
        $context->set(0, array(Types::TYPE_ARRAY, Types::TYPE_INTEGER));

        $isAbstract = $context->isAbstract(0);

        $this->assertSame($isAbstract, false);
    }

    public function testIsAbstractAbstractArray()
    {
        $context = new Scope();
        $context->set(0, array(Types::TYPE_ARRAY, 'A'));

        $isAbstract = $context->isAbstract(0);

        $this->assertSame($isAbstract, true);
    }
}
