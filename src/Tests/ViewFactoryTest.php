<?php

namespace Speedwork\View\Tests;

use Speedwork\View\Engine\StringEngine;
use Speedwork\View\ViewFactory;

class ViewFactoryTest extends \PHPUnit_Framework_TestCase
{
    private $engine;
    private $factory;

    protected function setUp()
    {
        $this->engine  = new StringEngine();
        $this->factory = new ViewFactory($this->engine);
    }

    public function testGetSharedBag()
    {
        $this->assertInstanceOf('Speedwork\\View\\Bag\\DataBag', $this->factory->getSharedBag());
    }

    public function testShare()
    {
        $this->assertNull($this->factory->getSharedBag()->get('foo'));

        $this->factory->share(['foo' => 'bar']);

        $this->assertEquals('bar', $this->factory->getSharedBag()->get('foo'));
    }

    public function testGetExceptionBag()
    {
        $this->assertInstanceOf('Speedwork\\View\\Bag\\ExceptionBag', $this->factory->getExceptionBag());
    }

    public function testCreate()
    {
        $view = $this->factory->create('foobar.html', ['foo' => 'bar']);

        $this->assertInstanceOf('Speedwork\\View\\ViewInterface', $view);
        $this->assertEquals('bar', $view['foo']);

        $view = $this->factory->create($view, ['bar' => 'foo']);

        $this->assertEquals('foo', $view['bar']);
    }
}
