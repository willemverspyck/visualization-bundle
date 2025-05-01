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

        if ($loader instanceof FilesystemLoader && $this->isDirectory()) {
            $loader->addPath($this->directory, 'SpyckVisualizationForCharts');
        }
    }

    public function hasChart(): bool
    {
        return $this->isCommand() && $this->isDirectory();
    }

    /**
     * @throws Exception
     */
    public function getChart(Block $block): string
    {
        $command = $this->getCommand();
        $directory = $this->getDirectory();

        $blockAsArray = $this->serializer->normalize($block);

        $output = sprintf('%s/%s.png', $directory, md5(serialize($blockAsArray)));

        if (file_exists($output)) {
            return $output;
        }

        $input = sprintf('%s/%s.html', $directory, md5(serialize($blockAsArray)));

        $content = $this->environment->render('@SpyckVisualization/chart/index.html.twig', [
            'block' => $blockAsArray,
        ]);

        if (false === file_put_contents($input, $content)) {
            throw new Exception('File not writable');
        }

        $commands = [
            $command,
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

    private function getCommand(): string
    {
        if ($this->isCommand()) {
            return $this->command;
        }

        throw new Exception(sprintf('Command not found (%s)', $this->command));
    }

    private function isCommand(): bool
    {
        if (null === $this->command) {
            return false;
        }

        $filesystem = new Filesystem();

        return $filesystem->exists($this->command);
    }

    private function getDirectory(): string
    {
        if ($this->isDirectory()) {
            return $this->directory;
        }

        throw new Exception(sprintf('Directory not found (%s)', $this->directory));
    }

    private function isDirectory(): bool
    {
        if (null === $this->directory) {
            return false;
        }

        $filesystem = new Filesystem();

        return $filesystem->exists($this->directory);
    }
}
