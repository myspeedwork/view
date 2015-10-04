<?php

namespace Speedwork\View\Tests\Engine;

use League\Plates\Engine;
use League\Plates\Template;
use Speedwork\View\Engine\PlatesEngine;

class PlatesEngineTest extends \PHPUnit_Framework_TestCase
{
    private $plates;
    private $engine;
    private $template;

    protected function setUp()
    {
        $engine         = new Engine(dirname(__DIR__).'/Fixtures');
        $this->plates   = new Template($engine->setFileExtension(null));
        $this->engine   = new PlatesEngine($this->plates);
        $this->template = 'foobar.plates.php';
    }

    public function testRender()
    {
        $content = $this->engine->render($this->template, ['title' => 'Foo']);

        $this->assertEquals('<h1>Foo</h1>', $content);
    }

    public function testSupports()
    {
        $this->assertTrue($this->engine->supports($this->template, 'php'));
        $this->assertTrue($this->engine->supports($this->template));
        $this->assertFalse($this->engine->supports(''));
    }
}
