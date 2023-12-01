<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface as BaseUserInterface;

interface UserInterface extends BaseUserInterface
{
    public function getId(): int|null;

    public function getName(): string|null;

    public function getEmail(): string;

    public function getGroups(): Collection;

    public function isActive(): bool;
}
