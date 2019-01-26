<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use KoharaKazuya\LaravelPSR15Middleware\LaravelPSR15Middleware;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;

class LaravelPSR15MiddlewareTest extends TestCase
{
    public function testInstanceIsLaravelMiddleware()
    {
        $instance = $this->createInstance(new NoopMiddleware());
        $request = Request::create('http://localhost:8080/');
        $response = $instance->handle($request, function($request) {
            return Response::create();
        });
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testGivenPSRMiddlewareAppliedAsLaravelMiddleware()
    {
        $noopMiddleware = new NoopMiddleware();
        $instance = $this->createInstance($noopMiddleware);
        $request = Request::create('http://localhost:8080/');
        $response = $instance->handle($request, function($request) {
            return Response::create();
        });
        $this->assertTrue($noopMiddleware->applied);
    }

    public function testPassThroughtRequestOrResponseWhenMiddlewareDoesNothing()
    {
        $instance = $this->createInstance(new NoopMiddleware());
        $originalRequest = Request::create('http://localhost:8080/');
        $originalResponse = Response::create();
        $request;
        $response = $instance->handle($originalRequest, function($req) use (&$request, $originalResponse) {
            $request = $req;
            return $originalResponse;
        });
        $this->assertEquals($originalRequest, $request);
        $this->assertEquals($originalResponse, $response);
    }

    private function createInstance($psrMiddleware)
    {
        return new LaravelPSR15Middleware(
            new DiactorosFactory(),
            new HttpFoundationFactory(),
            $psrMiddleware
        );
    }
}

class NoopMiddleware implements MiddlewareInterface
{
    public $applied;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $this->applied = true;
        return $handler->handle($request);
    }
}
