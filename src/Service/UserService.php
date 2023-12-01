<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Spyck\VisualizationBundle\Entity\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

readonly class UserService
{
    public function __construct(private TokenStorageInterface $tokenStorage)
    {
    }

    public function getUser(): string
    {
        $token = $this->tokenStorage->getToken();

        if (null === $token) {
            return 'Anonymous';
        }

        if (null === $token->getUser()) {
            throw new AuthenticationException('User not found');
        }

        $user = $token->getUser();

        return $user instanceof UserInterface && null !== $user->getName() ? $user->getName() : $user->getUserIdentifier();
    }
}
