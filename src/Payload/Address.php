<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Payload;

use Symfony\Component\Validator\Constraints as Validator;

final class Address
{
    #[Validator\Email]
    #[Validator\NotNull]
    #[Validator\Type(type: 'string')]
    private string $email;

    #[Validator\Type(type: 'string')]
    private ?string $name = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }
}
