<?php
namespace App\Controller;

use App\Entity\User;
use ApiPlatform\Core\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ResetPasswordAction
{
    /**
     * @var ValidatorInterface $validator
     */
    private $validator;

    /**
     *
     * @var UserPasswordEncoderInterface $userPasswordEncoder
     */
    private $userPasswordEncoder;

    /**
     * Entity Manager
     *
     * @var EntityManagerInterface $entityManager
     */
    private $entityManager;

    /**
     * @var JWTTokenManagerInterface $tokenManager
     */
    private $tokenManager;

    public function __construct(
        ValidatorInterface $validator,
        UserPasswordEncoderInterface $userPasswordEncoder,
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $tokenManager
    ) {
        $this->validator = $validator;   
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->entityManager = $entityManager;
        $this->tokenManager = $tokenManager;
    }

    public function __invoke(User $data)
    {
        // var_dump($data->getNewPassword(), $data->getNewRetypedPassword(), $data->getOldPassword());die;
        $this->validator->validate($data);

        $data->setPassword(
            $this->userPasswordEncoder->encodePassword(
                $data, $data->getNewPassword()
            )
        );

        // After password change, old tokens are still valid
        $data->setPasswordChangeDate(time());

        $this->entityManager->flush();

        $token = $this->tokenManager->create($data);

        return new JsonResponse(['token' => $token]);
        // Validator is only called after we return the data from this action
        //Only hear it checks for user current password, but we've just modified it!

        //Entity is persisted automatically, only if validation pass
    }
}