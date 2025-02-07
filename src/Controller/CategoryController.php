<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Controller;

use OpenApi\Attributes as OpenApi;
use Spyck\ApiExtension\Schema;
use Spyck\ApiExtension\Service\ResponseService;
use Spyck\VisualizationBundle\Entity\Menu;
use Spyck\VisualizationBundle\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[OpenApi\Tag(name: 'Category')]
final class CategoryController extends AbstractController
{
    public const string GROUP_LIST = 'spyck:visualization:category:list';

    #[Route(path: '/api/categories', name: 'spyck_visualization_category_list', methods: [Request::METHOD_GET])]
    #[Schema\BadRequest]
    #[Schema\Forbidden]
    #[Schema\NotFound]
    #[Schema\ResponseForList(type: Menu::class, groups: [self::GROUP_LIST])]
    public function list(CategoryRepository $categoryRepository, ResponseService $responseService): Response
    {
        $categories = $categoryRepository->getCategories();

        return $responseService->getResponseForList(data: $categories, groups: [self::GROUP_LIST]);
    }
}
