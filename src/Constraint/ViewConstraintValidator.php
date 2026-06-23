<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Constraint;

use Spyck\VisualizationBundle\Service\ViewService;
use Spyck\VisualizationBundle\View\ViewInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

final class ViewConstraintValidator extends ConstraintValidator
{
    public function __construct(private ViewService $viewService)
    {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (null === $value || '' === $value) {
            return;
        }

        if (false === is_string($value)) {
            return;
        }

        $view = array_find($this->viewService->getViews(), fn (ViewInterface $view) => $view->getCode() === $value);

        if (null === $view) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('{{ value }}', $value)
                ->addViolation();
        }
    }
}
