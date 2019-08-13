<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Security\TokenGenerator;
use App\Email\Mailer;

class UserRegisterSubscriber implements EventSubscriberInterface 
{
    /** @var UserPasswordEncoderInterface */
    private $passwordEncoder;

    /** @var TokenGenerator */
    private $tokenGenerator;

    private $mailer;

    public function __construct(
        UserPasswordEncoderInterface $passwordEncoder,
        TokenGenerator $tokenGenerator,
        Mailer $mailer

    ) {
        $this->passwordEncoder = $passwordEncoder;
        $this->tokenGenerator = $tokenGenerator;
        $this->mailer = $mailer;
    }
    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['userRegistered', EventPriorities::PRE_WRITE]
        ];
    }

    public function userRegistered(GetResponseForControllerResultEvent $event)
    {

        /**
         * @var User $user
         */
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if(!$user instanceof User || !in_array(
                $method,
                [Request::METHOD_POST]
            )
        ) {
            return;
        }

        //It is an User, we need to hash password here
        $user->setPassword(
            $this->passwordEncoder->encodePassword($user, $user->getPassword())
        );

        // Create confirmation token
        $user->setConfirmationToken(
            $this->tokenGenerator->getRandomSecureToken()
        );

        // Send email here
        $this->mailer->sendConfirmationEmail($user);
    }
}