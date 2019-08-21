<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\Request;
use App\Exception\EmptyBodyException;

class EmptyBodySubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => ['handleEmptyBody', EventPriorities::POST_DESERIALIZE]
        ];
    }

    public function handleEmptyBody(GetResponseEvent $event)
    {
        $method = $event->getRequest()->getMethod();

        if (!in_array($method, [Request::METHOD_POST, Request::METHOD_PUT])) {
            return;
        }

        $data = $event->getRequest()->get('data');
        if (null === $data) {
            throw new EmptyBodyException();
        }
    }
}