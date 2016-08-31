<?php

namespace Speedwork\View\Tests\Engine;

use Speedwork\View\Engine\DelegatingEngine;
use Speedwork\View\Engine\EngineResolver;
use Speedwork\View\Engine\MustacheEngine;
use Speedwork\View\Engine\PhpEngine;
use Speedwork\View\Engine\SmartyEngine;
use Speedwork\View\Engine\StringEngine;
use Speedwork\View\Engine\TwigEngine;

class DelegatingEngineTest extends \PHPUnit_Framework_TestCase
{
    private $mustache;
    private $smarty;
    private $twig;
    private $resolver;
    private $engine;

    protected function setUp()
    {
        $this->mustache = new \Mustache_Engine(['loader' => new \Mustache_Loader_FilesystemLoader(dirname(__DIR__).'/Fixtures')]);
        $this->smarty   = new \Smarty();
        $this->smarty->setTemplateDir(dirname(__DIR__).'/Fixtures');
        $this->smarty->setCompileDir('/tmp');
        $this->twig     = new \Twig_Environment(new \Twig_Loader_Filesystem(dirname(__DIR__).'/Fixtures'));
        $this->resolver = new EngineResolver([
            new MustacheEngine($this->mustache),
            new SmartyEngine($this->smarty),
            new TwigEngine($this->twig),
            new PhpEngine(),
            new StringEngine(),
        ]);
        $this->engine = new DelegatingEngine($this->resolver);
    }

    public function testRenderMustache()
    {
        $content = $this->engine->render('foobar.mustache', ['title' => 'Foo']);

        $this->assertEquals('<h1>Foo</h1>', $content);
    }

    public function testRenderSmarty()
    {
        $content = $this->engine->render('foobar.tpl', ['title' => 'Foo']);

        $this->assertEquals('<h1>Foo</h1>', $content);
    }

    public function testRenderTwig()
    {
        $content = $this->engine->render('foobar.twig', ['title' => 'Foo']);

        $this->assertEquals('<h1>Foo</h1>', $content);
    }

    public function testRenderPhp()
    {
        $content = $this->engine->render(dirname(__DIR__).'/Fixtures/foobar.php', ['title' => 'Foo']);

        $this->assertEquals('<h1>Foo</h1>', $content);
    }

    public function testRenderString()
    {
        $content = $this->engine->render(dirname(__DIR__).'/Fixtures/foobar.html', ['{{title}}' => 'Foo']);

        $this->assertEquals('<h1>Foo</h1>', $content);
    }

    /**
     * @expectedException \Speedwork\View\Exception\RenderException
     */
    public function testRenderWithException()
    {
        $resolver = new EngineResolver([]);
        $engine   = new DelegatingEngine($resolver);

        $content = $engine->render(dirname(__DIR__).'/Fixtures/foobar.invalid');
    }

    public function testSupports()
    {
        $this->assertTrue($this->engine->supports('', 'mustache'));
        $this->assertTrue($this->engine->supports('', 'ms'));
        $this->assertTrue($this->engine->supports('', 'tpl'));
        $this->assertTrue($this->engine->supports('', 'twig'));
        $this->assertTrue($this->engine->supports('', 'php'));
        $this->assertTrue($this->engine->supports('', 'html'));
        $this->assertTrue($this->engine->supports(''));
    }
}
