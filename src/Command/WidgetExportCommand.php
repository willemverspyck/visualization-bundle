<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Command;

use Exception;
use Psr\Cache\InvalidArgumentException;
use Spyck\VisualizationBundle\Exception\ParameterException;
use Spyck\VisualizationBundle\Service\ViewService;
use Spyck\VisualizationBundle\Service\WidgetService;
use Spyck\VisualizationBundle\View\ViewInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'spyck:visualization:widget:export', description: 'Command to export a widget.')]
final class WidgetExportCommand extends Command
{
    public function __construct(private readonly WidgetService $widgetService, private readonly ViewService $viewService)
    {
        parent::__construct();
    }

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function __invoke(SymfonyStyle $style, #[Option(name: 'widget')] ?string $optionWidget = null, #[Option(name: 'view')] string $optionView = ViewInterface::CSV, #[Option(name: 'file')] ?string $optionFile = null, #[Option(name: 'variableKey')] array $optionVariableKeys = [], #[Option(name: 'variableValue')] array $optionVariableValues = []): int
    {
        if (null === $optionWidget) {
            $style->error('Widget "%s" not found.');

            return Command::FAILURE;
        }

        $widgets = $this->getWidgets();

        if (false === array_key_exists($optionWidget, $widgets)) {
            $style->error(sprintf('Widget "%s" not found.', $optionWidget));

            return Command::FAILURE;
        }

        if (false === array_key_exists($optionView, $this->getViews())) {
            $style->error(sprintf('View "%s" not found.', $optionView));

            return Command::FAILURE;
        }

        if (null === $optionFile) {
            $style->error('File "%s" not found.');

            return Command::FAILURE;
        }

        if (count($optionVariableKeys) !== count($optionVariableValues)) {
            $style->error('Parameter "variableKey" and "variableValue" must be equal.');

            return Command::FAILURE;
        }

        $variables = array_combine($optionVariableKeys, $optionVariableValues);

        try {
            $dashboard = $this->widgetService->getDashboardAsModelByAdapter($widgets[$optionWidget], $variables, $optionView);
        } catch (ParameterException $parameterException) {
            $style->error($parameterException->getMessage());

            return Command::FAILURE;
        }

        $view = $this->viewService->getView($optionView);

        $content = $view->getContent($dashboard);

        if (false === file_put_contents($optionFile, $content)) {
            $style->error('Failed to write file.');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    protected function interact(InputInterface $input, OutputInterface $output): void
    {
        $widget = $input->getOption('widget');

        if (null === $widget) {
            $data = $this->getWidgets();

            $option = $this->getOption($input, $output, 'widget', $data);

            $input->setOption('widget', $option);
        }

        $view = $input->getOption('view');

        if (null === $view) {
            $data = $this->getViews();

            $option = $this->getOption($input, $output, 'view', $data);

            $input->setOption('view', $option);
        }
    }

    private function getOption(InputInterface $input, OutputInterface $output, string $name, array $data): string
    {
        $question = new ChoiceQuestion(sprintf('Please select a %s:', $name), $data);
        $question->setMaxAttempts(2);
        $question->setValidator(function (?string $id) use ($name, $data): string {
            if (in_array($id, $data, true)) {
                throw new RuntimeException(sprintf('Unknown %s', $name));
            }

            return $id;
        });

        return $this->getHelper('question')->ask($input, $output, $question);
    }

    private function getWidgets(): array
    {
        return $this->widgetService->getWidgets();
    }

    private function getViews(): array
    {
        return $this->viewService->getViews();
    }
}
