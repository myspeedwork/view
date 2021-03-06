<?php

namespace Speedwork\View\Tests\Bag;

use Speedwork\View\Bag\ExceptionBag;

class ExceptionBagTest extends \PHPUnit_Framework_TestCase
{
    private $exception;
    private $bag;

    protected function setUp()
    {
        $this->exception = new \Exception();
        $this->bag       = new ExceptionBag();
        $this->bag->add($this->exception);
    }

    public function testAll()
    {
        $this->assertEquals([$this->exception], $this->bag->all());
    }

    public function testAdd()
    {
        $exception = new \Exception();
        $this->bag->add($exception);

        $this->assertEquals([$this->exception, $exception], $this->bag->all());
    }

    public function testPop()
    {
        $this->assertEquals($this->exception, $this->bag->pop());

        $this->bag->clear();

        $this->assertNull($this->bag->pop());
    }

    public function testClear()
    {
        $this->bag->clear();

        $this->assertEquals([], $this->bag->all());
    }

    public function testGetIterator()
    {
        $this->assertInstanceOf('ArrayIterator', $this->bag->getIterator());

        $data = [];
        foreach ($this->bag as $value) {
            $data[] = $value;
        }

        $this->assertEquals([$this->exception], $data);
    }

    public function testCount()
    {
        $this->assertSame(1, $this->bag->count());
        $this->assertSame(1, count($this->bag));

        $exception = new \Exception();
        $this->bag->add($exception);

        $this->assertSame(2, $this->bag->count());
        $this->assertSame(2, count($this->bag));
    }
}
