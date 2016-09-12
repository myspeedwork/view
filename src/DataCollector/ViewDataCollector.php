<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\View\DataCollector;

use Speedwork\View\Logger\ViewLoggerInterface;
use Speedwork\View\ViewFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\DataCollector;

/**
 * Collects view rendering data for the Symfony2 profiler.
 *
 * @author sankar <sankar.suda@gmail.com>>
 */
class ViewDataCollector extends DataCollector
{
    protected $factory;
    protected $logger;

    /**
     * Constructor.
     *
     * @param ViewFactory         $factory
     * @param ViewLoggerInterface $logger
     */
    public function __construct(ViewFactory $factory, ViewLoggerInterface $logger)
    {
        $this->factory = $factory;
        $this->logger  = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Exception $exception = null)
    {
        $this->data['views'] = [];

        foreach ($this->logger->getViews() as $viewData) {
            $view = $viewData['view'];
            $time = $viewData['time'];
            $data = [];

            foreach ($view as $key => $value) {
                $data[$key] = $this->varToString($value);
            }

            $template = null !== $view->getTemplate()
                ? $view->getTemplate()
                : get_class($view);

            $engine = null !== $view->getEngine()
                ? basename(str_replace('\\', '/', get_class($view->getEngine())))
                : null;

            $datum = [
                'template' => $template,
                'engine'   => $engine,
                'data'     => $data,
                'time'     => $time,
            ];

            $this->data['views'][] = $datum;
        }

        $this->data['views']   = array_reverse($this->data['views']);
        $this->data['globals'] = $this->factory->getSharedBag()->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'view';
    }

    /**
     * Returns the total time spend on rendering.
     *
     * @return float
     */
    public function getTime()
    {
        $time = 0;
        foreach ($this->data['views'] as $view) {
            $time += (float) $view['time'];
        }

        return $time;
    }
}
