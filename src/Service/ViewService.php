<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Service;

use Countable;
use Exception;
use IteratorAggregate;
use Spyck\VisualizationBundle\View\ViewInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Contracts\Translation\TranslatorInterface;

readonly class ViewService
{
    /**
     * @param Countable&IteratorAggregate $views
     */
    public function __construct(private TranslatorInterface $translator, #[TaggedIterator(tag: 'spyck.visualization.view', defaultIndexMethod: 'getName')] private iterable $views)
    {
    }

    /**
     * @throws Exception
     */
    public function getView(string $name): ViewInterface
    {
        foreach ($this->views->getIterator() as $index => $view) {
            if ($index === $name) {
                return $view;
            }
        }

        throw new Exception(sprintf('View "%s" does not exist', $name));
    }

    /**
     * @return array<string, string>
     *
     * @throws Exception
     */
    public function getViews(): array
    {
        $data = [];

        foreach ($this->views->getIterator() as $view) {
            $name = $view->getName();

            $data[$name] = $this->translator->trans(id: sprintf('view.%s.name', $name), domain: 'SpyckVisualizationBundle');
        }

        return $data;
    }
}
