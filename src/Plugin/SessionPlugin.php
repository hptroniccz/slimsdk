<?php

declare(strict_types=1);

namespace HPTronic\SlimSdk\Plugin;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Kasa\Session\DI\Session;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * This plugin adds request header `X-Session-Id` to every request and "reload session" after request made.
 */
class SessionPlugin implements Plugin
{
    private Session $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
    {
        $request = $request->withHeader('X-Session-Id', $this->session->getId());

        return $next($request)->then(function (ResponseInterface $response): ResponseInterface {
            $this->session->reload();
            return $response;
        });
    }
}
