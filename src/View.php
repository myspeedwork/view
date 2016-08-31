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
use Speedwork\View\Engine\StringEngine;

/**
 * Nestable view container capable of rendering itself.
 *
 * @author Chris Heng <bigblah@gmail.com>
 */
class View extends \ArrayObject implements ViewInterface
{
    protected $template;
    protected $engine;
    protected $sharedBag;
    protected $parentBag;
    protected $exceptionBag;

    /**
     * Create a new view instance.
     *
     * @param string          $template     The template
     * @param mixed           $context      Template data
     * @param EngineInterface $engine       The rendering engine
     * @param DataBag         $sharedBag    A bag instance for shared data
     * @param ExceptionBag    $exceptionBag A bag instance for logging exceptions
     */
    public function __construct($template, $context = [], EngineInterface $engine = null, DataBag $sharedBag = null, ExceptionBag $exceptionBag = null)
    {
        parent::__construct([], self::ARRAY_AS_PROPS);

        $this->with($context);
        $this->template     = $template;
        $this->engine       = $engine ?: new StringEngine();
        $this->sharedBag    = $sharedBag ?: new DataBag();
        $this->parentBag    = new DataBag();
        $this->exceptionBag = $exceptionBag ?: new ExceptionBag();
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * {@inheritdoc}
     */
    public function getEngine()
    {
        return $this->engine;
    }

    /**
     * {@inheritdoc}
     */
    public function render($context = [])
    {
        foreach ($this as $item) {
            if ($item instanceof ViewInterface) {
                $item->inherit($this->all() + $this->parentBag->all());
            }
        }

        try {
            return $this->getEngine()->render($this->template, $this->with($context));
        } catch (\Exception $exception) {
            $this->exceptionBag->add($exception);
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function nest(ViewInterface $view, $key = 'content')
    {
        if (isset($this[$key]) && $this[$key] instanceof ViewInterface) {
            if (!$this[$key] instanceof ViewCollection) {
                $this[$key] = new ViewCollection($this[$key]);
            }
            $this[$key]->nest($view);
        } else {
            $this[$key] = $view;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function wrap(ViewInterface $view, $key = 'content')
    {
        $view[$key] = $this;

        return $view;
    }

    /**
     * {@inheritdoc}
     */
    public function with($context)
    {
        foreach ((array) $context as $key => $value) {
            $this[$key] = $value;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function inherit($context)
    {
        foreach ((array) $context as $key => $value) {
            if (!$value instanceof ViewInterface) {
                $this->parentBag->set($key, $value);
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function share($context)
    {
        foreach ((array) $context as $key => $value) {
            $this->sharedBag->set($key, $value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function globals()
    {
        return $this->sharedBag->all();
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        return parent::getArrayCopy();
    }

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        $content = $this->all();

        foreach ($content as $key => $value) {
            if ($value instanceof ViewInterface) {
                $content[$key] = $value->toArray();
            }
        }

        if (null !== $this->template) {
            $content += ['_template' => $this->template];
        }

        return $content;
    }

    /**
     * Returns all view data. Use all() to exclude shared data.
     *
     * @return array The complete view data
     */
    public function getArrayCopy()
    {
        return $this->all() + $this->parentBag->all() + $this->globals();
    }

    /**
     * Returns whether the requested index exists.
     *
     * @param string $id The unique identifier for the parameter or object
     *
     * @return bool True if the requested index exists, false otherwise
     */
    public function offsetExists($id)
    {
        return parent::offsetExists($id) ?: $this->parentBag->has($id) ?: $this->sharedBag->has($id);
    }

    /**
     * Gets a parameter or an object.
     *
     * @param string $id The unique identifier for the parameter or object
     *
     * @return mixed The value of the parameter or an object
     */
    public function offsetGet($id)
    {
        if (parent::offsetExists($id)) {
            return parent::offsetGet($id);
        }

        return $this->parentBag->get($id, $this->sharedBag->get($id, null));
    }

    /**
     * Sets a parameter or an object.
     *
     * @param string $id    The unique identifier for the parameter or object
     * @param mixed  $value The value of the parameter or object
     */
    public function offsetSet($id, $value)
    {
        parent::offsetSet($id, $value);
    }
}
