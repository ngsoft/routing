<?php

declare(strict_types=1);

namespace NGSOFT\Routing\Internal;

use NGSOFT\Routing\Container\DefaultContainerBuilder;
use NGSOFT\Routing\Interface\ContainerFactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * @internal
 */
trait ContainerBuilder
{
    private ?ContainerFactoryInterface $containerFactory = null;

    private array $definitions                           = [];

    private ?ContainerInterface $container               = null;

    public function getContainer(): ContainerInterface
    {
        return $this->container ??= $this->buildContainer();
    }

    public function setContainer(ContainerInterface $container): static
    {
        $this->container = $container;
        return $this;
    }

    public function addDefinitions(array $definitions): static
    {
        if ($this->container)
        {
            throw new \RuntimeException('Container is already loaded, cannot add definitions.');
        }

        $this->definitions = array_replace($this->definitions, $definitions);
        return $this;
    }

    /**
     * @param class-string $class
     */
    public function addDefinitionClass(string $class): static
    {
        $definitions = call_user_func(new $class());

        if ( ! is_array($definitions))
        {
            throw new \RuntimeException(sprintf('Definition class "%s" should returns an array', $class));
        }

        return $this->addDefinitions($definitions);
    }

    public function addDefinitionFile(string $file): static
    {
        $definitions = require $file;

        if ( ! is_array($definitions))
        {
            throw new \RuntimeException(sprintf('Definition file "%s" should returns an array', $file));
        }

        return $this->addDefinitions($definitions);
    }

    public function setContainerFactory(ContainerFactoryInterface $containerFactory): static
    {
        $this->containerFactory = $containerFactory;
        return $this;
    }

    private function buildContainer(): ContainerInterface
    {
        return ($this->containerFactory ??= new DefaultContainerBuilder())
            ->createContainer($this->definitions);
    }
}
