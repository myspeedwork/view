<?php

namespace Speedwork\View\Tests;

use Speedwork\View\Bag\DataBag;
use Speedwork\View\Bag\ExceptionBag;
use Speedwork\View\Engine\DelegatingEngine;
use Speedwork\View\Engine\EngineResolver;
use Speedwork\View\Engine\MustacheEngine;
use Speedwork\View\Engine\PhpEngine;
use Speedwork\View\Engine\SmartyEngine;
use Speedwork\View\Engine\StringEngine;
use Speedwork\View\Engine\TwigEngine;
use Speedwork\View\View;
use Speedwork\View\ViewCollection;

class ViewCollectionTest extends \PHPUnit_Framework_TestCase
{
    private $sharedBag;
    private $exceptionBag;
    private $mustache;
    private $smarty;
    private $twig;
    private $resolver;
    private $engine;
    private $view;

    protected function setUp()
    {
        $this->sharedBag    = new DataBag();
        $this->exceptionBag = new ExceptionBag();
        $this->mustache     = new \Mustache_Engine(['loader' => new \Mustache_Loader_FilesystemLoader(__DIR__.'/Fixtures')]);
        $this->smarty       = new \Smarty();
        $this->smarty->setTemplateDir(__DIR__.'/Fixtures');
        $this->smarty->setCompileDir('/tmp');
        $this->twig     = new \Twig_Environment(new \Twig_Loader_Filesystem(__DIR__.'/Fixtures'));
        $this->resolver = new EngineResolver([
            new MustacheEngine($this->mustache),
            new SmartyEngine($this->smarty),
            new TwigEngine($this->twig),
            new PhpEngine(),
            new StringEngine(),
        ]);
        $this->engine = new DelegatingEngine($this->resolver);
        $this->view   = new ViewCollection(new View(__DIR__.'/Fixtures/foobar.html', [], $this->engine, $this->sharedBag, $this->exceptionBag));
    }

    public function testGetTemplate()
    {
        $this->assertNull($this->view->getTemplate());
    }

    public function testGetEngine()
    {
        $this->assertNull($this->view->getEngine());
    }

    public function testRender()
    {
        $content = $this->view->render(['{{title}}' => 'Foo']);

        $this->assertEquals('<h1>Foo</h1>', $content);
    }

    public function testRenderMultipleNested()
    {
        $child      = new View(__DIR__.'/Fixtures/foobar.html', [], $this->engine, $this->sharedBag, $this->exceptionBag);
        $grandchild = clone $child;
        $content    = $this->view->render(
            [
                '{{title}}' => $child->nest(
                    $grandchild->with(
                        [
                            '{{title}}' => 'Foo',
                        ]
                    ), '{{title}}'
                ),
            ]
        );

        $this->assertEquals('<h1><h1><h1>Foo</h1></h1></h1>', $content);
    }

    public function testNest()
    {
        $view = new View(__DIR__.'/Fixtures/foobar.html', ['{{title}}' => 'bar'], $this->engine);

        $this->view->with(['{{title}}' => 'foo'])->nest($view);

        $this->assertEquals('<h1>foo</h1><h1>bar</h1>', $this->view->render());
    }

    public function testWrap()
    {
        $view = new View(__DIR__.'/Fixtures/foobar.html', [], $this->engine);
        $this->view->with(['{{title}}' => 'bar']);
        $view = $this->view->wrap($view, '{{title}}');

        $this->assertEquals($this->view, $view['{{title}}']);
        $this->assertEquals('<h1><h1>bar</h1></h1>', $view->render());
    }

    public function testWith()
    {
        $content = $this->view->with(['{{title}}' => 'Bar'])->render();

        $this->assertEquals('<h1>Bar</h1>', $content);
    }

    public function testShare()
    {
        $view = new View(__DIR__.'/Fixtures/foobar.html', [], $this->engine, $this->sharedBag);

        $this->assertNull($view['foo']);
        $this->assertNull($view['bar']);
        $this->assertNull($this->view['foo']);
        $this->assertNull($this->view['bar']);

        $view->share(['foo' => 'bar']);

        $this->assertEquals('bar', $view['foo']);
        $this->assertEquals('bar', $this->view['foo']);

        $this->view->share(['bar' => 'foo']);

        $this->assertEquals('foo', $this->view['bar']);
        $this->assertEquals('foo', $view['bar']);
    }

    public function testGlobals()
    {
        $this->assertEquals([], $this->view->globals());

        $this->view->share(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $this->view->globals());
    }

    public function testAll()
    {
        $this->assertEquals([[]], $this->view->all());

        $this->view->with(['foo' => 'bar']);

        $this->assertEquals([['foo' => 'bar']], $this->view->all());

        $this->view->nest(new View(__DIR__.'/Fixtures/foobar.html', ['bar' => 'baz'], $this->engine));

        $this->assertEquals([['foo' => 'bar'], ['bar' => 'baz']], $this->view->all());
    }

    public function testToString()
    {
        $view = $this->view->with(['{{title}}' => 'Bar']);

        $this->assertEquals('<h1>Bar</h1>', (string) $view);
    }

    public function testToArray()
    {
        $template = __DIR__.'/Fixtures/foobar.html';

        $this->assertEquals([['_template' => $template]], $this->view->toArray());

        $this->view->with(['foo' => new View($template, ['bar' => 'foo'], $this->engine)]);

        $this->assertEquals([['foo' => ['bar' => 'foo', '_template' => $template], '_template' => $template]], $this->view->toArray());

        $this->view->nest(new View($template, ['bar' => 'baz'], $this->engine));

        $this->assertEquals([['foo' => ['bar' => 'foo', '_template' => $template], '_template' => $template], ['bar' => 'baz', '_template' => $template]], $this->view->toArray());
    }

    public function testGetArrayCopy()
    {
        $this->assertEquals([[]], $this->view->all());

        $this->view->with(['foo' => 'bar']);

        $this->assertEquals([['foo' => 'bar']], $this->view->getArrayCopy());

        $this->view->share(['foo' => 'foo', 'bar' => 'foo']);

        $this->assertEquals([['foo' => 'bar', 'bar' => 'foo']], $this->view->getArrayCopy());

        $this->view->nest(new View(__DIR__.'/Fixtures/foobar.html', ['bar' => 'baz'], $this->engine));

        $this->assertEquals([['foo' => 'bar', 'bar' => 'foo'], ['bar' => 'baz']], $this->view->getArrayCopy());
    }

    public function testArrayAccess()
    {
        $this->assertFalse(isset($this->view['foo']));
        $this->assertFalse($this->view->offsetExists('foo'));
        $this->assertFalse(isset($this->view['bar']));
        $this->assertFalse($this->view->offsetExists('bar'));

        $this->view['foo'] = 'bar';

        $this->assertTrue(isset($this->view['foo']));
        $this->assertTrue($this->view->offsetExists('foo'));
        $this->assertEquals('bar', $this->view->offsetGet('foo'));

        $this->view->offsetSet('bar', 'baz');

        $this->assertTrue(isset($this->view['bar']));
        $this->assertTrue($this->view->offsetExists('bar'));
        $this->assertEquals('baz', $this->view->offsetGet('bar'));

        unset($this->view['foo']);

        $this->assertFalse(isset($this->view['foo']));
        $this->assertNull($this->view['foo']);

        $this->view->offsetUnset('bar');

        $this->assertFalse(isset($this->view['bar']));
        $this->assertNull($this->view['bar']);
    }

    public function testRenderCompositeViews()
    {
        $htmlView     = new View(__DIR__.'/Fixtures/foobar.html', [], $this->engine, $this->sharedBag, $this->exceptionBag);
        $mustacheView = new View('foobar.mustache', ['title' => $htmlView], $this->engine, $this->sharedBag, $this->exceptionBag);
        $smartyView   = new View('foobar.tpl', ['title' => $mustacheView], $this->engine, $this->sharedBag, $this->exceptionBag);
        $twigView     = new View('foobar.twig', ['title' => $smartyView], $this->engine, $this->sharedBag, $this->exceptionBag);
        $nestView     = clone $htmlView;
        $twigView->nest($nestView->with(['{{title}}' => 'bar']), 'title');
        $phpView = new View(__DIR__.'/Fixtures/foobar.php', ['title' => $twigView], $this->engine, $this->sharedBag, $this->exceptionBag);
        $phpView->share(['{{title}}' => 'foo']);

        $this->assertEquals('<h1><h1><h1><h1><h1>foo</h1></h1></h1><h1>bar</h1></h1></h1>', $phpView->render());
    }
}
