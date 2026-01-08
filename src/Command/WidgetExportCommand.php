<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Command;

use Exception;
use Psr\Cache\InvalidArgumentException;
use Spyck\VisualizationBundle\Exception\ParameterException;
use Spyck\VisualizationBundle\Service\ViewService;
use Spyck\VisualizationBundle\Service\WidgetService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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

    protected function configure(): void
    {
        $this
            ->addOption('widget', null, InputOption::VALUE_REQUIRED, 'Identifier of the widget')
            ->addOption('view', null, InputOption::VALUE_REQUIRED, 'View of the export file')
            ->addOption('file', null, InputOption::VALUE_REQUIRED, 'Location of the export file')
            ->addOption('variableKey', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Variable key')
            ->addOption('variableValue', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Variable value');
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

    /**
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $style = new SymfonyStyle($input, $output);

        $optionWidget = $input->getOption('widget');
        $optionView = $input->getOption('view');
        $optionFile = $input->getOption('file');

        $optionVariableKey = $input->getOption('variableKey');
        $optionVariableValue = $input->getOption('variableValue');

        $widgets = $this->getWidgets();

        if (false === array_key_exists($optionWidget, $widgets)) {
            $style->error(sprintf('Widget "%s" not found.', $optionWidget));

            return Command::FAILURE;
        }

        if (false === array_key_exists($optionView, $this->getViews())) {
            $style->error(sprintf('View "%s" not found.', $optionView));

            return Command::FAILURE;
        }

        if (count($optionVariableKey) !== count($optionVariableValue)) {
            $style->error('Parameter "variableKey" and "variableValue" must be equal.');

            return Command::FAILURE;
        }

        $variables = array_combine($optionVariableKey, $optionVariableValue);

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
