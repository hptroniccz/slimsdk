<?php

/** phpcs:disable PSR1.Files.SideEffects */

declare(strict_types=1);

namespace HPTronic\SlimSdk\Tests\Sdk\Exception;

use HPTronic\SlimSdk\Exception\HttpException;
use HPTronic\SlimSdk\Tests\TestCase;
use Mockery;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class HttpExceptionTest extends TestCase
{
    private array $reponse = [
        'code' => 422,
        'error' => 'TESTING_HTTP_EXCEPTION',
        'message' => 'Testing HttpException',
    ];

    public function testGetParsedResponse(): void
    {
        $exception = $this->createException();
        Assert::equal($this->reponse, (array) $exception->getParsedResponse());
    }

    public function testResponseCode(): void
    {
        $exception = $this->createException();
        Assert::equal($this->reponse['code'], $exception->getResponseCode());
    }

    public function testResponseError(): void
    {
        $exception = $this->createException();
        Assert::equal($this->reponse['error'], $exception->getResponseError());
    }

    public function testResponseMessage(): void
    {
        $exception = $this->createException();
        Assert::equal($this->reponse['message'], $exception->getResponseMessage());
    }

    private function createException(): HttpException
    {
        $stream = Mockery::mock(StreamInterface::class);
        $stream
            ->shouldReceive('__toString')
            ->andReturn(json_encode($this->reponse, JSON_THROW_ON_ERROR));

        $response = Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getBody')
            ->andReturn($stream);

        $response
            ->shouldReceive('getStatusCode')
            ->andReturn(422);

        $request = Mockery::mock(RequestInterface::class);
        return new HttpException('Testing HttpException', $request, $response);
    }
}

(new HttpExceptionTest())->run();
