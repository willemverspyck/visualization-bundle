<?php

declare(strict_types=1);

namespace Spyck\VisualizationBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;
use OpenApi\Attributes as OpenApi;
use Spyck\VisualizationBundle\Parameter\ParameterInterface;
use Symfony\Component\Serializer\Annotation as Serializer;

final class Dashboard
{
    private string $user;

    #[Serializer\Groups(['spyck:visualization:dashboard:item'])]
    private string $name;

    #[Serializer\Groups(['spyck:visualization:dashboard:item'])]
    private ?string $description = null;

    private ?string $copyright = null;

    #[Serializer\Groups(['spyck:visualization:dashboard:item'])]
    private ?string $url = null;

    #[Serializer\Groups(['spyck:visualization:dashboard:item'])]
    private ?string $callback = null;

    /**
     * @todo: Incorrect OpenApi
     */
    #[Serializer\Groups(['spyck:visualization:dashboard:item'])]
    #[OpenApi\Property(type: 'string')]
    private array $parameters = [];

    private array $parametersAsString = [];

    private array $parametersAsStringForSlug = [];

    /**
     * @todo: Can be replaced with object
     */
    #[OpenApi\Property(type: 'string')]
    #[Serializer\Groups(['spyck:visualization:dashboard:item'])]
    private array $downloads = [];

    /**
     * @var ArrayCollection<int, Block>
     */
    #[Serializer\Groups(['spyck:visualization:dashboard:item'])]
    private ArrayCollection $blocks;

    public function __construct()
    {
        $this->blocks = new ArrayCollection();
    }

    public function getUser(): string
    {
        return $this->user;
    }

    public function setUser(string $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCopyright(): ?string
    {
        return $this->copyright;
    }

    public function setCopyright(?string $copyright): static
    {
        $this->copyright = $copyright;

        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getCallback(): ?string
    {
        return $this->callback;
    }

    public function setCallback(?string $callback): static
    {
        $this->callback = $callback;

        return $this;
    }

    /**
     * @return array<int, ParameterInterface>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): static
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * @return array<int, ParameterInterface>
     */
    public function getParametersAsString(): array
    {
        return $this->parametersAsString;
    }

    public function setParametersAsString(array $parameters): static
    {
        $this->parametersAsString = $parameters;

        return $this;
    }

    /**
     * @return array<int, ParameterInterface>
     */
    public function getParametersAsStringForSlug(): array
    {
        return $this->parametersAsStringForSlug;
    }

    public function setParametersAsStringForSlug(array $parametersForSlug): static
    {
        $this->parametersAsStringForSlug = $parametersForSlug;

        return $this;
    }

    public function getDownloads(): array
    {
        return $this->downloads;
    }

    public function setDownloads(array $downloads): static
    {
        $this->downloads = $downloads;

        return $this;
    }

    public function addBlock(Block $block): static
    {
        $this->blocks->add($block);

        return $this;
    }

    public function clearBlocks(): void
    {
        $this->blocks->clear();
    }

    /**
     * @return ArrayCollection<int, Block>
     */
    public function getBlocks(): ArrayCollection
    {
        return $this->blocks;
    }

    public function removeBlock(Block $block): void
    {
        $this->blocks->removeElement($block);
    }
}
