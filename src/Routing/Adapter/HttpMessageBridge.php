<?php

declare(strict_types=1);

namespace NGSOFT\Routing\Adapter;

use GuzzleHttp\Psr7\HttpFactory;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Bridge\PsrHttpMessage\HttpMessageFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Adapt Symfony Request/Response to PSR-7 ones and vice versa.
 */
class HttpMessageBridge
{
    private HttpFoundationFactoryInterface $symfony;
    private HttpMessageFactoryInterface $psr;

    public function __construct(
        ?HttpFoundationFactoryInterface $symfony = null,
        ?HttpMessageFactoryInterface $psr = null,
    ) {
        if ( ! $psr)
        {
            $factory = new HttpFactory();
            $psr     = new PsrHttpFactory($factory, $factory, $factory, $factory);
        }
        $this->symfony = $symfony ?? new HttpFoundationFactory();
        $this->psr     = $psr;
    }

    public function createRequest(Request $request): ServerRequestInterface
    {
        return $this->psr->createRequest($request);
    }

    public function createResponse(Response $response): ResponseInterface
    {
        return $this->psr->createResponse($response);
    }

    public function createFoundationRequest(ServerRequestInterface $request): Request
    {
        return $this->symfony->createRequest($request);
    }

    public function createFoundationResponse(ResponseInterface $response): Response
    {
        return $this->symfony->createResponse($response);
    }
}
