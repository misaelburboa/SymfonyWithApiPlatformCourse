<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use App\Entity\Image;
use Symfony\Component\Form\FormFactoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\Validator\ValidatorInterface;
use ApiPlatform\Core\Validator\Exception\ValidationException;
use App\Form\ImageType;

class UploadImageAction
{
    private $formFactory;

    private $entityManager;

    private $validator;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->validator = $validator;
    }

    public function __invoke(Request $request)
    {
        // Create a ne Image instance
        $image = new Image();
        // Validate the form
        $form = $this->formFactory->create(ImageType::class, $image);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            // Persist the new Image entity
            $this->entityManager->persist($image);
            $this->entityManager->flush();

            $image->setFile(null);

            return $image;
        }

        // Uploading done for us in background by VichUploader

        // Throw a validation exception, that something went wrong during
        // form validation
        throw new ValidationException(
            $this->validator->validate($image)
        );
    }
}