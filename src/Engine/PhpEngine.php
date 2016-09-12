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
 * Raw PHP adapter.
 *
 * @author sankar <sankar.suda@gmail.com>>
 */
class PhpEngine implements EngineInterface
{
    /**
     * {@inheritdoc}
     */
    public function render($template, $data = null)
    {
        $data = $data instanceof \ArrayObject ? $data->getArrayCopy() : (array) $data;

        extract($data);

        ob_start();
        ob_implicit_flush(0);

        try {
            include $template;
        } catch (\Exception $exception) {
            ob_end_clean();
            throw $exception;
        }

        return ob_get_clean();
    }

    /**
     * {@inheritdoc}
     */
    public function supports($template, $type = null)
    {
        return in_array($type ?: pathinfo($template, PATHINFO_EXTENSION), ['php']);
    }
}
