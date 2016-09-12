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
 * Interface that rendering engine adapters should implement.
 *
 * @author sankar <sankar.suda@gmail.com>>
 */
interface EngineInterface
{
    /**
     * Renders a template.
     *
     * @param mixed $template The template
     * @param mixed $data     The template data
     *
     * @return string The rendered template
     */
    public function render($template, $data = null);

    /**
     * Returns true if this engine supports the given template type.
     *
     * @param mixed  $template A template
     * @param string $type     The template type
     *
     * @return bool true if this engine supports the given template, false otherwise
     */
    public function supports($template, $type = null);
}
