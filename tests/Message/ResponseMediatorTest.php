<?php

/** phpcs:disable PSR1.Files.SideEffects */

declare(strict_types=1);

namespace SlimSdk\Tests\Sdk\Message;

require_once __DIR__ . '/../bootstrap.php';

use Mockery;
use Nyholm\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use SlimSdk\Exception\BadRequestException;
use SlimSdk\Exception\ClientErrorException;
use SlimSdk\Exception\HttpException;
use SlimSdk\Exception\ServerErrorException;
use SlimSdk\Exception\UnprocessableEntityException;
use SlimSdk\Message\ResponseMediator;
use SlimSdk\Tests\TestCase;
use Tester\Assert;

class ResponseMediatorTest extends TestCase
{
    public function testGetParsedBody(): void
    {
        $mediator = new ResponseMediator(
            Mockery::mock(RequestInterface::class),
            new Response(200, ['Content-Type' => 'application/json'], '{"foo":"bar"}'),
        );

        Assert::same(['foo' => 'bar'], $mediator->getParsedBody());
    }

    public function testGetParsedBodyAsObject(): void
    {
        $mediator = new ResponseMediator(
            Mockery::mock(RequestInterface::class),
            new Response(200, ['Content-Type' => 'application/json'], '{"foo":"bar"}'),
        );

        $body = $mediator->getParsedBody(false);
        Assert::type('object', $body);
        Assert::true(isset($body->foo));
        Assert::same('bar', ((array) $body)['foo']);
    }

    public function testGetParsedBodyEmpty(): void
    {
        $mediator = new ResponseMediator(
            Mockery::mock(RequestInterface::class),
            new Response(200, [], 'OK'),
        );

        Assert::same('OK', $mediator->getParsedBody());
    }

    public function testProcessBadRequest(): void
    {
        Assert::exception(static function (): void {
            new ResponseMediator(
                Mockery::mock(RequestInterface::class),
                new Response(400),
            );
        }, BadRequestException::class, 'Unsuccessful response ({"code":400,"phrase":"Bad Request"})');
    }

    public function testProcessUnprocessableEntity(): void
    {
        Assert::exception(
            static function (): void {
                new ResponseMediator(
                    Mockery::mock(RequestInterface::class),
                    new Response(422, ['Content-Type' => 'application/json'], '{"message":"Something happened", "id": "xyz"}'),
                );
            },
            UnprocessableEntityException::class,
            'Unsuccessful response ({"code":422,"phrase":"Unprocessable Entity","msg":"Something happened","id":"xyz"})',
        );
    }

    public function testProcessClientErrorException(): void
    {
        Assert::exception(static function (): void {
            new ResponseMediator(
                Mockery::mock(RequestInterface::class),
                new Response(450),
            );
        }, ClientErrorException::class, 'Unsuccessful response ({"code":450,"phrase":""})');
    }

    public function testProcessServerErrorException(): void
    {
        Assert::exception(static function (): void {
            new ResponseMediator(
                Mockery::mock(RequestInterface::class),
                new Response(550),
            );
        }, ServerErrorException::class, 'Unsuccessful response ({"code":550,"phrase":""})');
    }

    public function testProcessHttpException(): void
    {
        Assert::exception(static function (): void {
            new ResponseMediator(
                Mockery::mock(RequestInterface::class),
                new Response(301),
            );
        }, HttpException::class, 'Unsuccessful response ({"code":301,"phrase":"Moved Permanently"})');
    }
}

(new ResponseMediatorTest())->run();
