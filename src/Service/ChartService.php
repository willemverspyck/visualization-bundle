<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Exception;
use Spyck\VisualizationBundle\Model\Block;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Twig\Environment;

readonly class ChartService
{
    public function __construct(private Environment $environment, #[Autowire(param: 'spyck.visualization.chart.command')] private string $command, #[Autowire(param: 'spyck.visualization.chart.directory')] private string $directory)
    {
    }

    /**
     * @throws Exception
     */
    public function getChart(Block $block): string
    {
        $widget = $block->getWidget();
        $type = $block->getCharts()[0];

        $name = md5(sprintf('%s-%s', serialize($widget), $type));

        $chart = sprintf('%s/%s.png', $this->directory, $name);

        if (file_exists($chart)) {
            return $chart;
        }

        $content = $this->environment->render('@SpyckVisualization/chart/index.html.twig', [
            'block' => $block,
            'type' => $type,
            'name' => $name,
        ]);

        $file = sprintf('%s/%s.html', $this->directory, $name);

        $result = file_put_contents($file, $content);

        if (false === $result) {
            throw new Exception('File not writable');
        }

        $commands = [
            $this->command,
            sprintf('--file=%s', $file),
            sprintf('--directory=%s', $this->directory),
        ];

        $process = new Process($commands);
        $process->setTimeout(300); // Set to 300 seconds instead of the default 60 seconds
        $process->run();

        if (false === $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if (file_exists($chart)) {
            $filesystem = new Filesystem();
            $filesystem->remove($file);

            return $chart;
        }

        throw new Exception(sprintf('Chart "%s" not found', $name));
    }
}
