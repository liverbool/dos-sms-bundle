<?php

namespace DoS\SMSBundle\EventListener;

use SmsSender\DelayedSenderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\IntrospectableContainerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class FlushSenderSubscriber implements EventSubscriberInterface
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public static function getSubscribedEvents()
    {
        return array(KernelEvents::TERMINATE => 'onKernelTerminate');
    }

    public function onKernelTerminate()
    {
        if (!$this->container->has('dos.sms.sender')) {
            return;
        }

        $senderInitialized = $this->container instanceof IntrospectableContainerInterface
            ? $this->container->initialized('dos.sms.sender')
            : true;

        if (!$senderInitialized || !$this->container->get('dos.sms.sender') instanceof DelayedSenderInterface) {
            return;
        }

        $this->container->get('dos.sms.sender')->flush();
    }
}
