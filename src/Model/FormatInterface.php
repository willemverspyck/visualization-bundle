<?php

namespace Spyck\VisualizationBundle\Model;

interface FormatInterface
{
    public function getName(): string;

    public function toArray(): array;
}
