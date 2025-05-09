<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Spyck\VisualizationBundle\Entity\UserInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

readonly class UserService
{
    public function __construct(private TokenStorageInterface $tokenStorage, #[Autowire(param: 'spyck.visualization.config.authentication')] private bool $authentication)
    {
    }

    public function getUserAsString(): string
    {
        $user = $this->getUser();

        if (null === $user) {
            return 'Anonymous';
        }

        return null === $user->getName() ? $user->getUserIdentifier() : $user->getName();
    }

    /**
     * @throws AuthenticationException
     */
    public function getUser(): ?UserInterface
    {
        if (false === $this->authentication) {
            return null;
        }

        $user = $this->tokenStorage->getToken()?->getUser();

        if (null === $user) {
            throw new AuthenticationException('User not found');
        }

        return $user;
    }
}
