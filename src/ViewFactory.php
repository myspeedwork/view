<?php

/**
 * This file is part of the Speedwork framework.
 *
 * @link http://github.com/speedwork
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Speedwork\View;

use Gigablah\Silex\View\ViewFactory as MainViewFactory;

/**
 * Nestable view container capable of rendering itself.
 *
 * @author sankar <sankar.suda@gmail.com>
 */
class ViewFactory extends MainViewFactory
{
    /**
     * {@inheritDoc}
     */
    public function assign($key, $value)
    {
        $this->sharedBag->set($key, $value);

        return $this;
    }
}
