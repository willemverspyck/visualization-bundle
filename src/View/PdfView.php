<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\View;

use Dompdf\Dompdf;
use Dompdf\Options;
use Exception;
use Spyck\VisualizationBundle\Model\Dashboard;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Twig\Environment;

final class PdfView extends AbstractView
{
    public function __construct(private readonly Environment $environment, #[Autowire(param: 'spyck.visualization.config.chart.directory')] private readonly ?string $chartDirectory, #[Autowire(param: 'spyck.visualization.config.directory')] private readonly string $directory)
    {
    }

    /**
     * @throws Exception
     */
    public function getContent(Dashboard $dashboard): string
    {
        if (false === class_exists(Dompdf::class)) {
            throw new Exception('Install DomPDF to use PDF');
        }

        $options = new Options();
        $options->setChroot([
            $this->chartDirectory,
            $this->directory,
        ]);

        $html = $this->environment->render('@SpyckVisualization/view/pdf.html.twig', [
            'dashboard' => $dashboard,
        ]);

        $dompdf = new Dompdf($options);
        $dompdf->setPaper('A4');
        $dompdf->loadHtml($html);
        $dompdf->render();

        $content = $dompdf->output();

        if (null === $content) {
            throw new Exception('Error creating PDF');
        }

        return $content;
    }

    public static function getContentType(): string
    {
        return 'application/pdf';
    }

    public static function getExtension(): string
    {
        return ViewInterface::PDF;
    }

    public static function getName(): string
    {
        return 'pdf';
    }
}
