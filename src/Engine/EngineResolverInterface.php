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
 * Interface for template engine resolvers.
 *
 * @author sankar <sankar.suda@gmail.com>>
 */
interface EngineResolverInterface
{
    /**
     * Returns an engine able to load and render the given template.
     *
     * @param mixed  $template A template
     * @param string $type     The template type
     *
     * @return EngineInterface|false An EngineInterface instance
     */
    public function resolve($template, $type = null);
}
