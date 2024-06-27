<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Utility;

use Exception;
use Spyck\VisualizationBundle\Field\MultipleFieldInterface;
use Spyck\VisualizationBundle\Field\FieldInterface;

final class WidgetUtility
{
    /**
     * @param array<int, FieldInterface|MultipleFieldInterface> $fields
     */
    public static function mapFields(array $fields, callable $callback, bool $active = true, int &$index = 0): array
    {
        $data = [];

        foreach ($fields as $field) {
            if ($field instanceof MultipleFieldInterface) {
                $data = array_merge($data, self::mapFields($field->getChildren()->toArray(), $callback, $active, $index));
            } else {
                if (false === $active || $field->isActive()) {
                    $data[] = $callback($field, $index);

                    ++$index;
                }
            }
        }

        return $data;
    }

    /**
     * @param array<int, FieldInterface|MultipleFieldInterface> $fields
     */
    public static function walkFields(array $fields, callable $callback, bool $active = true, int &$index = 0): void
    {
        foreach ($fields as $field) {
            if ($field instanceof MultipleFieldInterface) {
                self::walkFields($field->getChildren()->toArray(), $callback, $active, $index);
            } else {
                if (false === $active || $field->isActive()) {
                    $callback($field, $index);

                    ++$index;
                }
            }
        }
    }

    /**
     * @param array<int, FieldInterface|MultipleFieldInterface> $fields
     */
    public static function walkMultipleFields(array $fields, callable $callback, bool $active = true, int &$index = 0): void
    {
        foreach ($fields as $field) {
            if ($field instanceof MultipleFieldInterface) {
                if (false === $active || $field->isActive()) {
                    $callback($field, $index);
                }

                self::walkMultipleFields($field->getChildren()->toArray(), $callback, $active, $index);
            } else {
                if (false === $active || $field->isActive()) {
                    ++$index;
                }
            }
        }
    }
}
