<?php

namespace Speedwork\View;

use Speedwork\View\Logger\ViewLoggerInterface;

/**
 * View with logging functionality.
 *
 * @author Chris Heng <bigblah@gmail.com>
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
