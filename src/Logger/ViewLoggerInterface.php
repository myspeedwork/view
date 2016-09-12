<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\View\Logger;

use Speedwork\View\ViewInterface;

/**
 * Function signatures for logging and profiling views.
 *
 * @author sankar <sankar.suda@gmail.com>>
 */
interface ViewLoggerInterface
{
    /**
     * Logs the start of view rendering.
     *
     * @param ViewInterface $view
     */
    public function startRender(ViewInterface $view);

    /**
     * Logs the completion of view rendering.
     *
     * @param ViewInterface $view
     */
    public function stopRender(ViewInterface $view);

    /**
     * Returns all logged information.
     *
     * @return array
     */
    public function getViews();
}
