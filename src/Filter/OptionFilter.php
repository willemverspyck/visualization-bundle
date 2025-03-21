<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

use Spyck\VisualizationBundle\Request\RequestInterface;

final class OptionFilter extends AbstractOptionFilter
{
    public function __construct(array $options = [], bool $multiple = true)
    {
        $this->setOptions($options);
        $this->setMultiple($multiple);
    }

    public static function getField(): string
    {
        return 'options';
    }

    public static function getName(): string
    {
        return RequestInterface::OPTION;
    }
}
