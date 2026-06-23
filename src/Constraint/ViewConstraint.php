<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Constraint;

use Attribute;
use Symfony\Component\Validator\Constraint;

#[Attribute]
final class ViewConstraint extends Constraint
{
    public string $message = 'The view "{{ value }}" is not valid';
}
