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

use Speedwork\View\Exception\RenderException;

/**
 * Delegates rendering using an EngineResolver.
 *
 * @author Chris Heng <bigblah@gmail.com>
 */
class DelegatingEngine implements EngineInterface
{
    protected $resolver;

    /**
     * Constructor.
     *
     * @param EngineResolverInterface $resolver
     */
    public function __construct(EngineResolverInterface $resolver)
    {
        $this->resolver = $resolver;
    }

    /**
     * {@inheritdoc}
     */
    public function render($template, $data = null)
    {
        if (false === $engine = $this->resolver->resolve($template)) {
            throw new RenderException(sprintf('Could not resolve engine for template "%s"', $template));
        }

        return $engine->render($template, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($template, $type = null)
    {
        return false === $this->resolver->resolve($template, $type) ? false : true;
    }
}
