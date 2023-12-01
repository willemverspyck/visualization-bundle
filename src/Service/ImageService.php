<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Exception;
use Liip\ImagineBundle\Service\FilterService;
use stdClass;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Vich\UploaderBundle\Storage\StorageInterface;

readonly class ImageService
{
    public function __construct(#[Autowire(service: 'liip_imagine.service.filter')] private FilterService $filterService, private StorageInterface $storage)
    {
    }

    public function getImage(string $value, string $field, string $class): ?string
    {
        $object = new stdClass();
        $object->id = pathinfo($value, PATHINFO_FILENAME);
        $object->$field = $value;

        $path = $this->storage->resolveUri($object, sprintf('%sAsFile', $field), $class);

        if (null === $path) {
            return null;
        }

        return $path;
    }

    /**
     * @throws Exception
     */
    public function getThumbnail(string $value, string $filter): string
    {
        $url = $this->filterService->getUrlOfFilteredImage($value, $filter);
        $path = parse_url($url, PHP_URL_PATH);

        if (false === $path) {
            throw new Exception(sprintf('Path not found: %s', $url));
        }

        return $path;
    }
}
