<?php
namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use ApiPlatform\Core\EventListener\EventPriorities;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelEvents;
use App\Security\UserConfirmationService;

class UserConfirmationSubscriber implements EventSubscriberInterface
{
    private $userConfirmationService;

    public function __construct(
        UserConfirmationService $userConfirmationService
    ) {
        $this->userConfirmationService = $userConfirmationService;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['confirmUser', EventPriorities::POST_VALIDATE]
        ];
    }

    public function confirmUser(GetResponseForControllerResultEvent $event)
    {
        // $request = $event->getRequest();
        // if ('api_user_confirmations_post_collection' !== $request->get('_route')) {
        //     return;
        // }

        // /** @var UserConfirmation $confirmationToken */
        // $confirmationToken = $event->getControllerResult();

        // $this->userConfirmationService->confirmUser(
        //     $confirmationToken->confirmationToken
        // );

        // $event->setResponse(new JsonResponse(null, Response::HTTP_OK));
    }
}