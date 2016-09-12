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
 * Renders regular strings or files.
 *
 * @author sankar <sankar.suda@gmail.com>>
 */
class StringEngine implements EngineInterface
{
    /**
     * {@inheritdoc}
     */
    public function render($template, $data = null)
    {
        if (file_exists($template)) {
            $template = file_get_contents($template);
        }

        $data = $data instanceof \ArrayObject ? $data->getArrayCopy() : (array) $data;

        return strtr($template, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($template, $type = null)
    {
        return true;
    }
}
