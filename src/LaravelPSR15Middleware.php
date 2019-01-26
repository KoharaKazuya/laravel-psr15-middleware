<?php

namespace KoharaKazuya\LaravelPSR15Middleware;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;

class LaravelPSR15Middleware
{
    private $diactorosFactory;
    private $httpFoundationFactory;
    private $psrMiddleware;

    public function __construct(DiactorosFactory $diactorosFactory, HttpFoundationFactory $httpFoundationFactory, MiddlewareInterface $psrMiddleware)
    {
        $this->diactorosFactory = $diactorosFactory;
        $this->httpFoundationFactory = $httpFoundationFactory;
        $this->psrMiddleware = $psrMiddleware;
    }

    /**
     * Laravel compatible middleware handle method
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     * @throws
     */
    public function handle($request, Closure $next)
    {
        if ($this->psrMiddleware === null) {
            throw new \Exception('LaravelMiddlewareDelegatesPSRMiddleware::psrMiddleware is null. Make sure that LaravelMiddlewareDelegatesPSRMiddleware::setMiddleware(...) is called.');
        }

        $psrRequest = $this->diactorosFactory->createRequest($request);
        $handler = new PSRRequestHandlerDelegatesLaravelNextClosure(
            $request,
            $psrRequest,
            $next,
            $this->diactorosFactory,
            $this->httpFoundationFactory
        );
        $psrResponse = $this->psrMiddleware->process($psrRequest, $handler);
        [$originalResponse, $originalPSRResponse] = $handler->getOriginalResponses();
        $response = $psrResponse === $originalPSRResponse
            ? $originalResponse
            : $this->httpFoundationFactory->createResponse($psrResponse);

        return $response;
    }
}

class PSRRequestHandlerDelegatesLaravelNextClosure implements RequestHandlerInterface
{
    private $originalRequest;
    private $originalPSRRequest;
    private $originalResponse;
    private $originalPSRResponse;
    private $next;
    private $diactorosFactory;
    private $httpFoundationFactory;

    /**
     * @param \Illuminate\Http\Request $originalRequest
     * @param \Psr\Http\Message\ServerRequestInterface $originalPSRRequest
     * @param \Closure $next
     * @param DiactorosFactory $diactorosFactory
     * @param HttpFoundationFactory $httpFoundationFactory
     */
    public function __construct(
        $originalRequest,
        ServerRequestInterface $originalPSRRequest,
        Closure $next,
        DiactorosFactory $diactorosFactory,
        HttpFoundationFactory $httpFoundationFactory
    )
    {
        $this->originalRequest = $originalRequest;
        $this->originalPSRRequest = $originalPSRRequest;
        $this->next = $next;
        $this->diactorosFactory = $diactorosFactory;
        $this->httpFoundationFactory = $httpFoundationFactory;
    }

    public function handle(ServerRequestInterface $psrRequest): ResponseInterface
    {
        $request = $psrRequest === $this->originalPSRRequest
            ? $this->originalRequest
            : $this->httpFoundationFactory->createRequest($psrRequest);

        $this->originalResponse = ($this->next)($request);

        $this->originalPSRResponse = $this->diactorosFactory->createResponse($this->originalResponse);
        return $this->originalPSRResponse;
    }

    public function getOriginalResponses()
    {
        return [$this->originalResponse, $this->originalPSRResponse];
    }
}