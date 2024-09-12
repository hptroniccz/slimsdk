<?php

declare(strict_types=1);

namespace HPTronic\SlimSdk\Plugin;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Nette\Http\IRequest;
use Psr\Http\Message\RequestInterface;

class UserAgentPlugin implements Plugin
{
    private IRequest $request;

    public function __construct(IRequest $request)
    {
        $this->request = $request;
    }

    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
    {
        $request = $request->withHeader('X-User-Agent', $this->request->getHeader('User-Agent') ?? '');

        return $next($request);
    }
}
