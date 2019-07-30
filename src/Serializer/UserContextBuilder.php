<?php
namespace App\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;

class UserContextBuilder implements SerializerContextBuilderInterface
{
    private $decorated;

    private $authorizationChecker;

    public function __construct(
        SerializerContextBuilderInterface $decorated,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;    
    }

    public function createFromRequest(
        Request $request,
        bool $normalization,
        array $extractedAttributes = null
    ):array {
        $context = $this->decorated->createFromRequest(
            $request,
            $normalization,
            $extractedAttributes
        );

        //class being serialized/deserialized
        $resourceClass = $context['resource_class'] ?? null; //Default null if not set
        if (
            User::class === $resourceClass &&
            isset($context['groups']) &&
            $normalization === true &&
            $this->authorizationChecker->isGranted(User::ROLE_ADMIN)
        ) {
            $context['groups'][] = 'get-admin';
        }

        return $context;
    }
}