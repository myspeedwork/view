<?php

namespace Speedwork\View\Tests\Engine;

use Aura\View\EscaperFactory;
use Aura\View\HelperLocator;
use Aura\View\Template;
use Aura\View\TemplateFinder;
use Speedwork\View\Engine\AuraEngine;

class AuraEngineTest extends \PHPUnit_Framework_TestCase
{
    private $aura;
    private $engine;
    private $template;

    protected function setUp()
    {
        $this->aura     = new Template(new EscaperFactory(), new TemplateFinder([dirname(__DIR__).'/Fixtures']), new HelperLocator());
        $this->engine   = new AuraEngine($this->aura);
        $this->template = 'foobar.aura.php';
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
