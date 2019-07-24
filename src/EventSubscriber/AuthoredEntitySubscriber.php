<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use App\Entity\BlogPost;
use Symfony\Component\HttpFoundation\Request;

class AuthoredEntitySubscriber implements EventSubscriberInterface
{
    /**
     * @param TokenStorageInterface $tokenStorage
     */
    private $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['getAuthenticateUser', EventPriorities::PRE_WRITE]
        ];
    }

    public function getAuthenticateUser(GetResponseForControllerResultEvent $event)
    {
        $entity = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        /**
         * @var UserInterface $author
         */
        $author = $this->tokenStorage->getToken()->getUser();
        if (!$entity instanceof BlogPost || Request::METHOD_POST !== $method) {
            return;
        }

        $entity->setAuthor($author);
    }
}