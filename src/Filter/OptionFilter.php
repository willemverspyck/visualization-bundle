<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Filter;

use Spyck\VisualizationBundle\Request\RequestInterface;

final class OptionFilter extends AbstractOptionFilter
{
    public function __construct(array $options = [])
    {
        $this->setConfig([
            'type' => FilterInterface::TYPE_CHECKBOX,
        ]);
        $this->setOptions($options);
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
