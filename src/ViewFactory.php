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

use Speedwork\View\Bag\DataBag;
use Speedwork\View\Bag\ExceptionBag;
use Speedwork\View\Engine\EngineInterface;

/**
 * Factory for creating view objects.
 *
 * @author sankar <sankar.suda@gmail.com>>
 */
class ViewFactory
{
    protected $engine;
    protected $sharedBag;
    protected $exceptionBag;

    /**
     * Create a view factory.
     *
     * @param EngineInterface $engine The rendering engine
     */
    public function __construct(EngineInterface $engine)
    {
        $this->engine       = $engine;
        $this->sharedBag    = new DataBag();
        $this->exceptionBag = new ExceptionBag();
    }

    /**
     * Sets the global context.
     *
     * @param mixed $context The global context
     *
     * @return ViewFactory
     */
    public function share($context)
    {
        foreach ((array) $context as $key => $value) {
            $this->sharedBag->set($key, $value);
        }

        return $this;
    }

    /**
     * Returns the shared data container.
     *
     * @return DataBag
     */
    public function getSharedBag()
    {
        return $this->sharedBag;
    }

    /**
     * Returns the shared exception container.
     *
     * @return ExceptionBag
     */
    public function getExceptionBag()
    {
        return $this->exceptionBag;
    }

    /**
     * Create new view instances.
     *
     * @param mixed $template
     * @param mixed $data
     *
     * @return ViewInterface
     */
    public function create($template, $data = [])
    {
        if ($template instanceof ViewInterface) {
            return $template->with($data);
        }

        return new View($template, $data, $this->engine, $this->sharedBag, $this->exceptionBag);
    }

    /**
     * Add the key value pair view bag.
     *
     * @param string $key   Key name
     * @param mixed  $value values to store
     *
     * @return object
     */
    public function assign($key, $value)
    {
        $this->sharedBag->set($key, $value);

        return $this;
    }

    /**
     * Get value from view bag.
     *
     * @param string $key Key name
     *
     * @return object
     */
    public function release($key)
    {
        return $this->sharedBag->get($key);
    }
}
