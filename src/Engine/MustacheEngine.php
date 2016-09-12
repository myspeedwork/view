<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\View\Engine;

/**
 * Mustache adapter.
 *
 * @author sankar <sankar.suda@gmail.com>>
 */
class MustacheEngine implements EngineInterface
{
    protected $mustache;

    /**
     * Constructor.
     *
     * @param \Mustache_Engine $mustache
     * @param array            $extensions
     */
    public function __construct(\Mustache_Engine $mustache)
    {
        $this->mustache = $mustache;
    }

    /**
     * {@inheritdoc}
     */
    public function render($template, $data = null)
    {
        return $this->mustache->loadTemplate($template)->render($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($template, $type = null)
    {
        return in_array($type ?: pathinfo($template, PATHINFO_EXTENSION), ['ms', 'mustache']);
    }
}
