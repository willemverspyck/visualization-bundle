<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Exception;
use Spyck\VisualizationBundle\Model\Block;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Serializer\SerializerInterface;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

readonly class ChartService
{
    public function __construct(private Environment $environment, private SerializerInterface $serializer, #[Autowire(param: 'spyck.visualization.config.chart.command')] private ?string $command, #[Autowire(param: 'spyck.visualization.config.chart.directory')] private ?string $directory)
    {
        $loader = $environment->getLoader();

        if ($loader instanceof FilesystemLoader) {
            $loader->addPath($this->directory, 'SpyckVisualizationForCharts');
        }
    }

    public function hasChart(): bool
    {
        return null !== $this->command && null !== $this->directory;
    }

    /**
     * @throws Exception
     */
    public function getChart(Block $block): string
    {
        if (false === $this->hasChart()) {
            throw new Exception('Chart not configured');
        }

        $blockAsArray = $this->serializer->normalize($block);

        $output = sprintf('%s/%s.png', $this->directory, md5(serialize($blockAsArray)));

        if (file_exists($output)) {
            return $output;
        }

        $input = sprintf('%s/%s.html', $this->directory, md5(serialize($blockAsArray)));

        $content = $this->environment->render('@SpyckVisualization/chart/index.html.twig', [
            'block' => $blockAsArray,
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
