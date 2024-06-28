<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Format;

use DateTimeImmutable;
use Spyck\ApiExtension\Model\Response;
use Spyck\VisualizationBundle\Controller\WidgetController;
use Symfony\Component\Serializer\Annotation as Serializer;

final class ConditionFormat implements FormatInterface
{
    public const string OPERATOR_EQUAL = '=';
    public const string OPERATOR_GREATER_THAN = '>';
    public const string OPERATOR_GREATER_THAN_OR_EQUAL = '>=';
    public const string OPERATOR_LESS_THAN = '<';
    public const string OPERATOR_LESS_THAN_OR_EQUAL = '<=';

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private string $color;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private bool $background;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private string $operator;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private DateTimeImmutable|float|int|string|null $value = null;

    public function __construct(string $color, bool $background = false, string $operator = ConditionFormatInterface::OPERATOR_EQUAL, DateTimeImmutable|float|int|string|null $value = null)
    {
        $this->setColor($color);
        $this->setBackground($background);
        $this->setOperator($operator);
        $this->setValue($value);
    }

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    public function getName(): string
    {
        return 'condition';
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function setColor(string $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function isBackground(): bool
    {
        return $this->background;
    }

    public function setBackground(bool $background): static
    {
        $this->background = $background;

        return $this;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function setOperator(string $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    public function getValue(): DateTimeImmutable|float|int|string|null
    {
        return $this->value;
    }

    public function setValue(DateTimeImmutable|float|int|string|null $value): static
    {
        $this->value = $value;

        return $this;
    }
}
