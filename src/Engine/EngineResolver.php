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
 * Selects an appropriate rendering engine for a given template.
 *
 * @author sankar <sankar.suda@gmail.com>>
 */
class EngineResolver implements EngineResolverInterface
{
    protected $engines;

    /**
     * Constructor.
     *
     * @param EngineInterface[] $engines An array of engines
     */
    public function __construct(array $engines = [])
    {
        $this->engines = [];
        foreach ($engines as $engine) {
            $this->addEngine($engine);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resolve($template, $type = null)
    {
        foreach ($this->getEngines() as $engine) {
            if ($engine->supports($template, $type)) {
                return $engine;
            }
        }

        return false;
    }

    /**
     * Adds an engine.
     *
     * @param EngineInterface $engine An EngineInterface instance
     */
    public function addEngine(EngineInterface $engine)
    {
        $this->engines[] = $engine;
    }

    /**
     * Returns the registered engines.
     *
     * @return EngineInterface[] An array of EngineInterface instances
     */
    public function getEngines()
    {
        return $this->engines;
    }
}
