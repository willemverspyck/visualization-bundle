<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Format;

use DateTimeInterface;
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
    private ?Color $color;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private ?Color $colorBackground;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private bool $bold;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private string $operator;

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    private DateTimeInterface|float|int|string|null $value = null;

    public function __construct(?Color $color = null, ?Color $colorBackground = null, bool $bold = false, string $operator = ConditionFormat::OPERATOR_EQUAL, DateTimeInterface|float|int|string|null $value = null)
    {
        $this->setColor($color);
        $this->setColorBackground($colorBackground);
        $this->setBold($bold);
        $this->setOperator($operator);
        $this->setValue($value);
    }

    #[Serializer\Groups(groups: [WidgetController::GROUP_ITEM])]
    public function getName(): string
    {
        return 'condition';
    }

    public function getColor(): ?Color
    {
        return $this->color;
    }

    public function setColor(?Color $color): static
    {
        $this->color = $color;

        return $this;
    }

    public function getColorBackground(): ?Color
    {
        return $this->colorBackground;
    }

    public function setColorBackground(?Color $colorBackground): static
    {
        $this->colorBackground = $colorBackground;

        return $this;
    }

    public function isBold(): bool
    {
        return $this->bold;
    }

    public function setBold(bool $bold): static
    {
        $this->bold = $bold;

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

    public function getValue(): DateTimeInterface|float|int|string|null
    {
        return $this->value;
    }

    public function setValue(DateTimeInterface|float|int|string|null $value): static
    {
        $this->value = $value;

        return $this;
    }
}
