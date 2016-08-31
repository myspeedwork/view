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

use Aura\View\Template;

/**
 * Aura.View adapter.
 *
 * @author Chris Heng <bigblah@gmail.com>
 */
class AuraEngine implements EngineInterface
{
    protected $aura;

    /**
     * Constructor.
     *
     * @param Template $aura
     */
    public function __construct(Template $aura)
    {
        $this->aura = $aura;
    }

    /**
     * {@inheritdoc}
     */
    public function render($template, $data = null)
    {
        $data = $data instanceof \ArrayObject ? $data->getArrayCopy() : (array) $data;

        $this->aura->setData($data);

        return $this->aura->fetch($template);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($template, $type = null)
    {
        return in_array($type ?: pathinfo($template, PATHINFO_EXTENSION), ['php']);
    }
}
