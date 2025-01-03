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
    public function __construct(private Environment $environment, #[Autowire(param: 'spyck.visualization.config.chart.command')] private ?string $command, #[Autowire(param: 'spyck.visualization.config.chart.directory')] private ?string $directory)
    {
    }

    public function hasChart(): bool
    {
        return null === $this->command || null === $this->directory;
    }

    /**
     * @throws Exception
     */
    public function getChart(Block $block): string
    {
        if (false === $this->hasChart()) {
            throw new Exception('Chart not configured');
        }

        $type = $block->getCharts()[0];
        $widget = $block->getWidget();

        $output = sprintf('%s/%s-%s.png', $this->directory, $type, serialize($widget));

        if (file_exists($output)) {
            return $output;
        }

        $input = sprintf('%s/%s-%s.html', $this->directory, $type, serialize($widget));

        $content = $this->environment->render('@SpyckVisualization/chart/index.html.twig', [
            'type' => $type,
            'widget' => $widget,
        ]);

        if (false === file_put_contents($input, $content)) {
            throw new Exception('File not writable');
        }

        $commands = [
            $this->command,
            sprintf('--input=%s', $input),
            sprintf('--output=%s', $output),
        ];

        $process = new Process($commands);
        $process->setTimeout(300); // Set to 300 seconds instead of the default 60 seconds
        $process->run();

        if (false === $process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        if (file_exists($output)) {
            $filesystem = new Filesystem();
            $filesystem->remove($input);

            return $output;
        }

        throw new Exception(sprintf('Chart not found (%s)', $input));
    }
}
