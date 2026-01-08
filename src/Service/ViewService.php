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
    public function __construct(private TranslatorInterface $translator, #[AutowireLocator(services: 'spyck.visualization.view', defaultIndexMethod: 'getName')] private ServiceLocator $serviceLocator, #[Autowire(param: 'spyck.visualization.config.view.exclude')] private readonly ?array $exclude)
    {
    }

    /**
     * @throws Exception
     */
    public function getView(string $name): ViewInterface
    {
        return $this->serviceLocator->get($name);
    }

    /**
     * @return array<string, string>
     *
     * @throws Exception
     */
    public function getViews(): array
    {
        $data = [];

        foreach (array_keys($this->serviceLocator->getProvidedServices()) as $name) {
            if (null === $this->exclude || false === in_array($name, $this->exclude, true)) {
                $data[$name] = $this->translator->trans(id: sprintf('view.%s.name', $name), domain: 'SpyckVisualizationBundle');
            }
        }

        return $data;
    }
}
