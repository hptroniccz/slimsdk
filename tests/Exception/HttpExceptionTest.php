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

    private array $reponseRfc7807 = [
        'type' => 'https:\/\/tools.ietf.org\/html\/rfc2616#section-10',
        'title' => 'An error occurred',
        'detail' => 'Testing HttpException',
    ];

    public function testGetParsedResponse(): void
    {
        $exception = $this->createException();
        Assert::equal($this->reponse, (array) $exception->getParsedResponse());
        $exception = $this->createException(true);
        Assert::equal($this->reponseRfc7807, (array) $exception->getParsedResponse());
    }

    public function testResponseCode(): void
    {
        $exception = $this->createException();
        Assert::equal($this->reponse['code'], $exception->getResponseCode());
        $exception = $this->createException(true);
        Assert::equal(422, $exception->getResponseCode());
    }

    public function testResponseError(): void
    {
        $exception = $this->createException();
        Assert::equal($this->reponse['error'], $exception->getResponseError());
        $exception = $this->createException(true);
        Assert::equal($this->reponseRfc7807['title'], $exception->getResponseError());
    }

    public function testResponseMessage(): void
    {
        $exception = $this->createException();
        Assert::equal($this->reponse['message'], $exception->getResponseMessage());
        $exception = $this->createException(true);
        Assert::equal($this->reponseRfc7807['detail'], $exception->getResponseMessage());
    }

    private function createException(bool $rfc7807 = false): HttpException
    {
        $stream = Mockery::mock(StreamInterface::class);
        $stream
            ->shouldReceive('__toString')
            ->andReturn(json_encode($rfc7807 ? $this->reponseRfc7807 : $this->reponse, JSON_THROW_ON_ERROR));

        $response = Mockery::mock(ResponseInterface::class);
        $response
            ->shouldReceive('getBody')
            ->andReturn($stream);

        $response
            ->shouldReceive('getStatusCode')
            ->andReturn(422);

        if ($rfc7807) {
            $response
                ->shouldReceive('getHeaderLine')
                ->with('Content-Type')
                ->andReturn('application/problem+json');
        } else {
            $response
                ->shouldReceive('getHeaderLine')
                ->with('Content-Type')
                ->andReturn('application/json');
        }

        $request = Mockery::mock(RequestInterface::class);
        return new HttpException('Testing HttpException', $request, $response);
    }
}

(new HttpExceptionTest())->run();
