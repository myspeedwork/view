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

/**
 * An array-like collection of ViewInterface objects.
 *
 * @author sankar <sankar.suda@gmail.com>>
 */
class ViewCollection extends \ArrayObject implements ViewInterface
{
    /**
     * Create a new view collection.
     *
     * @param ViewInterface $view
     */
    public function __construct(ViewInterface $view)
    {
        parent::__construct([$view]);
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getEngine()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function render($context = [])
    {
        $content = '';

        foreach ($this as $view) {
            $content .= $view->render($context);
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function nest(ViewInterface $view, $key = 'content')
    {
        parent::offsetSet(null, $view);

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
        foreach ($this as $view) {
            $view->with($context);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function inherit($context)
    {
        foreach ($this as $view) {
            $view->inherit($context);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function share($context)
    {
        $view = parent::offsetGet(0);

        return $view->share($context);
    }

    /**
     * {@inheritdoc}
     */
    public function globals()
    {
        $view = parent::offsetGet(0);

        return $view->globals();
    }

    /**
     * {@inheritdoc}
     */
    public function all()
    {
        $context = [];

        foreach ($this as $view) {
            $context[] = $view->all();
        }

        return $context;
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
        $content = [];

        foreach ($this as $view) {
            $content[] = $view->toArray();
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function getArrayCopy()
    {
        $content = [];

        foreach ($this as $view) {
            $content[] = $view->getArrayCopy();
        }

        return $content;
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
        foreach ($this as $view) {
            if ($view->offsetExists($id)) {
                return true;
            }
        }

        return false;
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
        foreach ($this as $view) {
            if ($view->offsetExists($id)) {
                return $view->offsetGet($id);
            }
        }
    }

    /**
     * Sets a parameter or an object.
     *
     * @param string $id    The unique identifier for the parameter or object
     * @param mixed  $value The value of the parameter or object
     */
    public function offsetSet($id, $value)
    {
        foreach ($this as $view) {
            $view->offsetSet($id, $value);
        }
    }

    /**
     * Unsets a parameter or an object.
     *
     * @param string $id The unique identifier for the parameter or object
     */
    public function offsetUnset($id)
    {
        foreach ($this as $view) {
            $view->offsetUnset($id);
        }
    }
}
