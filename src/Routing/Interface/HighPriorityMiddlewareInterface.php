<?php

declare(strict_types=1);

namespace NGSOFT\Routing\Interface;

use Reindeer\SymfonyMiddleware\Contracts\MiddlewareInterface;

/**
 * Defines middleware at the top of the queue (before routing middleware).
 */
interface HighPriorityMiddlewareInterface extends MiddlewareInterface {}
