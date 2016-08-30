<?php

namespace Speedwork\View\Tests\Engine;

use Speedwork\Container\Container;
use Speedwork\View\Engine\LazyEngineResolver;
use Speedwork\View\Engine\PhpEngine;
use Speedwork\View\Engine\StringEngine;

class LazyEngineResolverTest extends \PHPUnit_Framework_TestCase
{
    private $app;
    private $resolver;

    protected function setUp()
    {
        $this->app                       = new Container();
        $this->app['view.engine.string'] = new StringEngine();
        $this->app['view.engine.php']    = new PhpEngine();
        $this->resolver                  = new LazyEngineResolver($this->app, [
            'html' => 'view.engine.string',
        ], 'html');
    }

    public function testResolve()
    {
        $this->assertInstanceOf('Speedwork\\View\\Engine\\StringEngine', $this->resolver->resolve('foobar.html', 'html'));
        $this->assertInstanceOf('Speedwork\\View\\Engine\\StringEngine', $this->resolver->resolve('foobar.html'));
        $this->assertInstanceOf('Speedwork\\View\\Engine\\StringEngine', $this->resolver->resolve('foobar.php', 'html'));
        $this->assertInstanceOf('Speedwork\\View\\Engine\\StringEngine', $this->resolver->resolve('foobar'));
        $this->assertFalse($this->resolver->resolve('foobar.php', 'php'));
        $this->assertFalse($this->resolver->resolve('foobar.php'));
        $this->assertFalse($this->resolver->resolve('foobar.html', 'php'));

        $this->resolver->addMapping('php', 'view.engine.php');

        $this->assertInstanceOf('Speedwork\\View\\Engine\\PhpEngine', $this->resolver->resolve('foobar.php', 'php'));
        $this->assertInstanceOf('Speedwork\\View\\Engine\\PhpEngine', $this->resolver->resolve('foobar.php'));
        $this->assertInstanceOf('Speedwork\\View\\Engine\\PhpEngine', $this->resolver->resolve('foobar.html', 'php'));
        $this->assertInstanceOf('Speedwork\\View\\Engine\\PhpEngine', $this->resolver->resolve('foobar', 'php'));
    }
}
