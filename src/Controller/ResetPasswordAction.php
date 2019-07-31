<?php
namespace App\Controller;

use App\Entity\User;
use ApiPlatform\Core\Validator\ValidatorInterface;

class ResetPasswordAction
{
    /**
     * @var ValidatorInterface $validator
     */
    private $validator;

    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;    
    }

    public function __invoke(User $data)
    {
        // var_dump($data->getNewPassword(), $data->getNewRetypedPassword(), $data->getOldPassword());die;
        $this->validator->validate($data);
        // Validator is only called after we return the data from this action
    }
}