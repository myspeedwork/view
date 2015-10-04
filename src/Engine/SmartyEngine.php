<?php

namespace Speedwork\View\Engine;

/**
 * Smarty adapter.
 *
 * @author Chris Heng <bigblah@gmail.com>
 */
class SmartyEngine implements EngineInterface
{
    protected $smarty;

    /**
     * Constructor.
     *
     * @param \Smarty $smarty
     */
    public function __construct(\Smarty $smarty)
    {
        $this->smarty = $smarty;
    }

    /**
     * {@inheritdoc}
     */
    public function render($template, $data = null)
    {
        $data = $data instanceof \ArrayObject ? $data->getArrayCopy() : (array) $data;

        foreach ($data as $key => $value) {
            $this->smarty->assign($key, $value);
        }

        return $this->smarty->fetch($template);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($template, $type = null)
    {
        return in_array($type ?: pathinfo($template, PATHINFO_EXTENSION), ['tpl']);
    }
}
