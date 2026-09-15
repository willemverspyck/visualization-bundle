<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Controller;

use OpenApi\Attributes as OpenApi;
use Spyck\ApiExtension\Schema;
use Spyck\ApiExtension\Service\ResponseService;
use Spyck\VisualizationBundle\Entity\Category;
use Spyck\VisualizationBundle\Repository\CategoryRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[AsController]
#[OpenApi\Tag(name: 'Categories')]
final class CategoryController extends AbstractController
{
    public const string GROUP_LIST = 'spyck:visualization:category:list';
    public const string GROUP_ITEM = 'spyck:visualization:category:item';

    #[Route(path: '/api/categories', name: 'spyck_visualization_category_list', methods: [Request::METHOD_GET])]
    #[Schema\BadRequest]
    #[Schema\Forbidden]
    #[Schema\NotFound]
    #[Schema\ResponseForList(type: Category::class, groups: [self::GROUP_LIST])]
    public function list(CategoryRepository $categoryRepository, ResponseService $responseService): Response
    {
        $categories = $categoryRepository->getCategories();

        return $responseService->getResponseForList(data: $categories, groups: [self::GROUP_LIST]);
    }

    /**
     * @throws NotFoundHttpException
     */
    #[Route(path: '/api/category/{categoryId}', name: 'spyck_visualization_category_item', requirements: ['categoryId' => Requirement::DIGITS], methods: [Request::METHOD_GET])]
    #[Schema\BadRequest]
    #[Schema\Forbidden]
    #[Schema\NotFound]
    #[Schema\ResponseForItem(type: Category::class, groups: [self::GROUP_ITEM])]
    public function item(CategoryRepository $categoryRepository, ResponseService $responseService, int $categoryId): Response
    {
        $category = $categoryRepository->getCategoryById($categoryId);

        if (null === $category) {
            throw $this->createNotFoundException('Category not found');
        }

        return $responseService->getResponseForItem(data: $category, groups: [self::GROUP_ITEM]);
    }
}
