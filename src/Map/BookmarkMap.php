<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Map;

use Spyck\ApiExtension\Map\PaginationMapInterface;
use Spyck\ApiExtension\Map\PaginationMapTrait;

final class BookmarkMap implements PaginationMapInterface
{
    use PaginationMapTrait;
}
