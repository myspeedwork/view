<?php

namespace Speedwork\View\Tests\Engine;

use Speedwork\View\Engine\EngineResolver;
use Speedwork\View\Engine\PhpEngine;
use Speedwork\View\Engine\StringEngine;

class EngineResolverTest extends \PHPUnit_Framework_TestCase
{
    private $resolver;

    protected function setUp()
    {
        $this->resolver = new EngineResolver([
            new PhpEngine(),
        ]);
    }

    public function testResolve()
    {
        $this->assertInstanceOf('Speedwork\\View\\Engine\\PhpEngine', $this->resolver->resolve('foobar.php', 'php'));
        $this->assertInstanceOf('Speedwork\\View\\Engine\\PhpEngine', $this->resolver->resolve('foobar.php'));
        $this->assertInstanceOf('Speedwork\\View\\Engine\\PhpEngine', $this->resolver->resolve('foobar.html', 'php'));
        $this->assertInstanceOf('Speedwork\\View\\Engine\\PhpEngine', $this->resolver->resolve('foobar', 'php'));
        $this->assertFalse($this->resolver->resolve('foobar.html', 'html'));
        $this->assertFalse($this->resolver->resolve('foobar.html'));
        $this->assertFalse($this->resolver->resolve('foobar.php', 'html'));

        $this->resolver->addEngine(new StringEngine());

        $this->assertInstanceOf('Speedwork\\View\\Engine\\StringEngine', $this->resolver->resolve('foobar.html', 'html'));
        $this->assertInstanceOf('Speedwork\\View\\Engine\\StringEngine', $this->resolver->resolve('foobar.html'));
        $this->assertInstanceOf('Speedwork\\View\\Engine\\StringEngine', $this->resolver->resolve('foobar.php', 'html'));
        $this->assertInstanceOf('Speedwork\\View\\Engine\\StringEngine', $this->resolver->resolve('foobar'));
    }
}
