<?php

declare(strict_types=1);

namespace NGSOFT\Routing\Internal;

use FastRoute\Dispatcher;
use NGSOFT\Routing\Route;

/**
 * @internal
 */
readonly class FastRouteResult
{
    public function __construct(
        private int $status,
        private ?Route $route,
        private string $method,
        private string $path,
        private array $arguments = [],
        private array $allowed = [],
    ) {}

    public function getStatusCode(): int
    {
        return match ($this->status)
        {
            Dispatcher::METHOD_NOT_ALLOWED => 405,
            Dispatcher::NOT_FOUND          => 404,
            default                        => 200,
        };
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getAllowed(): array
    {
        return $this->allowed;
    }

    public function getRoute(): ?Route
    {
        return $this->route;
    }
}
