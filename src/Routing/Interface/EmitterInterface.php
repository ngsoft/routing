<?php

namespace NGSOFT\Routing\Interface;

use Symfony\Component\HttpFoundation\Response;

interface EmitterInterface
{
    public function emit(Response $response): void;
}
