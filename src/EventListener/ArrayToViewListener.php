<?php

namespace Speedwork\View\EventListener;

use Speedwork\View\Template\TemplateResolverInterface;
use Speedwork\View\ViewFactory;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Converts arrays to views.
 *
 * @author Chris Heng <bigblah@gmail.com>
 */
class ArrayToViewListener
{
    private $factory;
    private $resolver;

    public function __construct(ViewFactory $factory, TemplateResolverInterface $resolver)
    {
        $this->factory  = $factory;
        $this->resolver = $resolver;
    }

    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
            return;
        }

        $result = $event->getControllerResult();

        if (!is_array($result)) {
            return;
        }

        $result = $this->factory->create($this->resolver->resolve($event->getRequest(), $result), $result);

        $event->setResponse(new Response($result));
    }
}
