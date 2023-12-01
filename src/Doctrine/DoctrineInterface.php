<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Spyck\VisualizationBundle\Widget\WidgetInterface;

interface DoctrineInterface extends WidgetInterface
{
    /**
     * Get data from Doctrine with QueryBuilder.
     */
    public function getDataFromDoctrine(): QueryBuilder;
}
