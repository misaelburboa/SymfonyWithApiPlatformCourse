<?php
namespace App\Security;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use App\Entity\User;
use Symfony\Component\Security\Core\Exception\DisabledException;

class UserEnabledChecker implements UserCheckerInterface
{
    /**
     * Checks the user account before authentication.
     */
    public function checkPreAuth(UserInterface $user)
    {
        if (!$user instanceof User) {
            return;
        }

        if (!$user->getEnabled()) {
            throw new DisabledException();
        }
    }

    /**
     * Checks the user account after authentication.
    */
    public function checkPostAuth(UserInterface $user)
    {

    }
}