<?php

declare(strict_types=1);

namespace SlimSdk\Plugin;

use Http\Client\Common\Plugin;
use Http\Promise\Promise;
use Psr\Http\Message\RequestInterface;

/**
 * This plugin forces response status code from Prism (API Mocking Server).
 * @link https://meta.stoplight.io/docs/prism/docs/getting-started/03-cli.md#force-response-status
 */
class PrismPlugin implements Plugin
{
    private static array $headers = [];

    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter
    {
        foreach (self::$headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $next($request);
    }

    public static function requestResponseCode(int $code): void
    {
        self::$headers['Prefer'] = "code=$code";
    }

    public static function cleanup(): void
    {
        self::$headers = [];
    }
}
