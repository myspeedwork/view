<?php

namespace Speedwork\View\Engine;

/**
 * Renders regular strings or files.
 *
 * @author Chris Heng <bigblah@gmail.com>
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
