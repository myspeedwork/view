<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\View;

use Speedwork\View\Logger\ViewLoggerInterface;

/**
 * View with logging functionality.
 *
 * @author sankar <sankar.suda@gmail.com>>
 */
class LoggableView extends View
{
    protected $logger;

    public function setLogger(ViewLoggerInterface $logger = null)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function render($context = [])
    {
        if (null === $this->logger) {
            return parent::render($context);
        }

        $this->logger->startRender($this);
        $content = parent::render($context);
        $this->logger->stopRender($this);

        return $content;
    }
}
