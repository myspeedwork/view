<?php

/**
 * This file is part of the Speedwork framework.
 *
 * @license http://opensource.org/licenses/MIT
 *
 * @link http://github.com/speedwork
 *
 * @version 0.0.1
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Speedwork\View;

use Gigablah\Silex\View\View as MainView;

/**
 * Nestable view container capable of rendering itself.
 *
 * @author sankar <sankar.suda@gmail.com>
 */
class View extends MainView
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
