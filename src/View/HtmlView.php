<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

use Spyck\VisualizationBundle\Model\Dashboard;
use Exception;
use Twig\Environment;

final class HtmlView extends AbstractView
{
    public function __construct(private readonly Environment $environment)
    {
    }

    /**
     * @throws Exception
     */
    public function getContent(Dashboard $dashboard): string
    {
        return $this->environment->render('@SpyckVisualization/view/html.html.twig', [
            'dashboard' => $dashboard,
        ]);
    }

    public static function getContentType(): string
    {
        return 'text/html';
    }

    public static function getExtension(): string
    {
        return ViewInterface::HTML;
    }

    public static function getName(): string
    {
        return ViewInterface::HTML;
    }

    public static function getDescription(): string
    {
        return 'HTML';
    }
}
