<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Security\UserConfirmationService;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="default_index")
     */
    public function index()
    {
        return $this->render(
            'base.html.twig'
        );
    }

    /**
     * @Route("/confirm-user/{token}", name="default_confirm_token")
     */
    public function confirmUser(
        string $token, 
        UserConfirmationService $userConfirmationService
    ) {
        $userConfirmationService->confirmUser($token);

        return $this->redirectToRoute('default_index');
    }
}
