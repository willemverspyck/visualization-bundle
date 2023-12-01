<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Repository;

use Spyck\VisualizationBundle\Widget\WidgetInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(tags: ['spyck.visualization.repository'])]
interface RepositoryInterface
{
    public static function getVisualizationName(): string;

    public function getVisualizationEntityById(int $id): ?object;

    public function getVisualizationEntityData(WidgetInterface $widget): array;
}
