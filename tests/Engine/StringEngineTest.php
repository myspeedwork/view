<?php

namespace Speedwork\View\Tests\Engine;

use Speedwork\View\Engine\StringEngine;

class StringEngineTest extends \PHPUnit_Framework_TestCase
{
    private $engine;
    private $template;

    protected function setUp()
    {
        $this->engine   = new StringEngine();
        $this->template = dirname(__DIR__).'/Fixtures/foobar.html';
    }

    public function testRender()
    {
        $content = $this->engine->render($this->template, ['{{title}}' => 'Foo']);

        $this->assertEquals('<h1>Foo</h1>', $content);
    }

    public function testSupports()
    {
        $this->assertTrue($this->engine->supports($this->template, 'html'));
        $this->assertTrue($this->engine->supports($this->template));
        $this->assertTrue($this->engine->supports(''));
    }
}
