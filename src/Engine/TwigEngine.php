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
 * Twig adapter.
 *
 * @author sankar <sankar.suda@gmail.com>>
 */
class TwigEngine implements EngineInterface
{
    protected $twig;

    /**
     * Constructor.
     *
     * @param \Twig_Environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public function render($template, $data = null)
    {
        $data = $data instanceof \ArrayObject ? $data->getArrayCopy() : (array) $data;

        return $this->twig->render($template, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($template, $type = null)
    {
        return in_array($type ?: pathinfo($template, PATHINFO_EXTENSION), ['twig']);
    }
}
