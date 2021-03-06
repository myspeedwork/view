<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\View;

use Speedwork\View\Engine\EngineInterface;
use Speedwork\View\Logger\ViewLoggerInterface;

/**
 * Factory for creating loggable view objects.
 *
 * @author sankar <sankar.suda@gmail.com>>
 */
class LoggableViewFactory extends ViewFactory
{
    protected $logger;

    /**
     * Create a loggable view factory.
     *
     * @param EngineInterface     $engine The rendering engine
     * @param ViewLoggerInterface $logger View logger for debugging
     */
    public function __construct(EngineInterface $engine, ViewLoggerInterface $logger)
    {
        parent::__construct($engine);

        $this->logger = $logger;
    }

    /**
     * Create loggable view instances.
     *
     * @param mixed $template
     * @param mixed $data
     *
     * @return ViewInterface
     */
    public function create($template, $data = [])
    {
        if ($template instanceof LoggableView) {
            return $template->with($data);
        }

        $view = new LoggableView($template, $data, $this->engine, $this->sharedBag, $this->exceptionBag);
        $view->setLogger($this->logger);

        return $view;
    }
}
