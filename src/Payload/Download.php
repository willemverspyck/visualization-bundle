<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Payload;

use Symfony\Component\Validator\Constraints as Validator;

final class Download
{
    #[Validator\NotBlank]
    #[Validator\Type(type: 'string')]
    private string $view;

    #[Validator\Type(type: 'array')]
    private array $variables;

    public function getView(): string
    {
        return $this->view;
    }

    public function setView(string $view): static
    {
        $this->view = $view;

        return $this;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function setVariables(array $variables): static
    {
        $this->variables = $variables;

        return $this;
    }
}
