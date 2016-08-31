<?php

namespace Speedwork\View\Tests;

use Speedwork\View\Engine\StringEngine;
use Speedwork\View\LoggableViewFactory;

class LoggableViewFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $engine;
    private $logger;
    private $factory;

    protected function setUp()
    {
        $this->engine  = new StringEngine();
        $this->logger  = $this->getMockBuilder('Speedwork\\View\\Logger\\ViewLogger')->disableOriginalConstructor()->getMock();
        $this->factory = new LoggableViewFactory($this->engine, $this->logger);
    }

    public function testGetSharedBag()
    {
        $this->assertInstanceOf('Speedwork\\View\\Bag\\DataBag', $this->factory->getSharedBag());
    }

    public function testGetExceptionBag()
    {
        $this->assertInstanceOf('Speedwork\\View\\Bag\\ExceptionBag', $this->factory->getExceptionBag());
    }

    public function testCreate()
    {
        $view = $this->factory->create('foobar.html', ['foo' => 'bar']);

        $this->assertInstanceOf('Speedwork\\View\\LoggableView', $view);
        $this->assertEquals('bar', $view['foo']);

        $view = $this->factory->create($view, ['bar' => 'foo']);

        $this->assertEquals('foo', $view['bar']);
    }
}
