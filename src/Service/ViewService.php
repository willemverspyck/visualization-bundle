<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Exception;
use Spyck\VisualizationBundle\View\ViewInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class ViewService
{
    public function __construct(private TranslatorInterface $translator, #[AutowireLocator(services: 'spyck.visualization.view')] private ServiceLocator $serviceLocator, #[Autowire(param: 'spyck.visualization.config.view.exclude')] private readonly ?array $exclude)
    {
    }

    /**
     * @throws Exception
     */
    public function getView(string $code): ViewInterface
    {
        $view = array_find($this->getViews(), fn (ViewInterface $view) => $view->getCode() === $code);

        if (null === $view) {
            throw new Exception(sprintf('View "%s" not found', $code));
        }

        return $view;
    }

    /**
     * @return array<string, ViewInterface>
     *
     * @throws Exception
     */
    public function getViews(): array
    {
        $views = iterator_to_array($this->serviceLocator->getIterator());

        return array_filter($views, function (ViewInterface $view): bool {
            return null === $this->exclude || false === in_array($view->getCode(), $this->exclude, true);
        });
    }

    public function getViewsWithTranslation(): array
    {
        $data = [];

        foreach ($this->getViews() as $view) {
            $code = $view->getCode();

            $data[$code] = $this->translator->trans(id: sprintf('view.%s.name', $code), domain: 'SpyckVisualizationBundle');
        }

        return $data;
    }
}
