<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Controller;

use Exception;
use OpenApi\Attributes as OpenApi;
use Psr\Cache\InvalidArgumentException;
use Spyck\VisualizationBundle\Exception\ParameterException;
use Spyck\VisualizationBundle\Service\ViewService;
use Spyck\VisualizationBundle\Service\WidgetService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[AsController]
#[OpenApi\Tag(name: 'Widgets')]
final class WidgetController extends AbstractController
{
    public const string GROUP_ITEM = 'spyck:visualization:widget:item';

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    #[Route(path: '/api/widget/{widgetId}.{_format}', name: 'spyck_visualization_widget_item', requirements: ['widgetId' => Requirement::DIGITS], methods: [Request::METHOD_GET])]
    public function item(Request $request, ViewService $viewService, WidgetService $widgetService, int $widgetId): Response
    {
        try {
            $variables = $request->query->all();

            $widget = $widgetService->getDashboardAsModelById($widgetId, $variables);

            $view = $viewService->getView($request->getRequestFormat());

            $content = $view->getContent($widget);

            $headers = [];

            if (null !== $view->getExtension()) {
                $headers['Content-Disposition'] = sprintf('attachment; filename="%s.%s"', $view->getFile($widget->getName(), $widget->getParametersAsStringForSlug()), $view->getExtension());
            }

            return new Response($content, Response::HTTP_OK, $headers);
        } catch (ParameterException $parameterException) {
            throw $this->createNotFoundException($parameterException->getMessage());
        }
    }
}
