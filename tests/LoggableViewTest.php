<?php

namespace Speedwork\View\Tests;

use Speedwork\View\Engine\StringEngine;
use Speedwork\View\LoggableView;

class LoggableViewTest extends \PHPUnit_Framework_TestCase
{
    private $engine;
    private $view;

    protected function setUp()
    {
        $this->engine = new StringEngine();
        $this->logger = $this->getMockBuilder('Speedwork\\View\\Logger\\ViewLogger')->disableOriginalConstructor()->getMock();
        $this->view   = new LoggableView(__DIR__.'/Fixtures/foobar.html', [], $this->engine);
    }

    public function testRender()
    {
        $this->logger->expects($this->once())->method('startRender')->with($this->equalTo($this->view));
        $this->logger->expects($this->once())->method('stopRender')->with($this->equalTo($this->view));

        $content = $this->view->render(['{{title}}' => 'Foo']);

        $this->view->setLogger($this->logger);

        $content = $this->view->render(['{{title}}' => 'Foo']);
    }
}
