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

use League\Plates\Template;

/**
 * Plates adapter.
 *
 * @author sankar <sankar.suda@gmail.com>>
 */
class PlatesEngine implements EngineInterface
{
    protected $plates;

    /**
     * Constructor.
     *
     * @param Template $plates
     */
    public function __construct(Template $plates)
    {
        $this->plates = $plates;
    }

    /**
     * {@inheritdoc}
     */
    public function render($template, $data = null)
    {
        $data = $data instanceof \ArrayObject ? $data->getArrayCopy() : (array) $data;

        $this->plates->data($data);

        return $this->plates->render($template);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($template, $type = null)
    {
        return in_array($type ?: pathinfo($template, PATHINFO_EXTENSION), ['php']);
    }
}
