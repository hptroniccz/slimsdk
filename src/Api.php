<?php

declare(strict_types=1);

namespace SlimSdk\Api;

use Psr\Http\Client\ClientInterface as Client;
use Psr\Http\Message\RequestFactoryInterface as RequestFactory;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\StreamFactoryInterface as StreamFactory;
use SlimSdk\Message\ResponseMediator;

abstract class Api
{
    protected Client $client;

    protected RequestFactory $requestFactory;

    protected StreamFactory $streamFactory;

    public function __construct(Client $client, RequestFactory $request, StreamFactory $stream)
    {
        $this->client = $client;
        $this->requestFactory = $request;
        $this->streamFactory = $stream;
    }

    protected function createRequest(string $method, string $path, array $data = []): Request
    {
        $request = $this->requestFactory->createRequest($method, $path);

        if ($data !== []) {
            $request = $request
                ->withHeader('content-type', 'application/json')
                ->withBody($this->streamFactory->createStream(json_encode($data)));
        }

        return $request;
    }

    protected function createResponse(Request $request): ResponseMediator
    {
        $response = $this->sendRequest($request);
        return new ResponseMediator($request, $response);
    }

    protected function sendRequest(Request $request): Response
    {
        return $this->client->sendRequest($request);
    }

    protected function post(string $path, array $data = []): ResponseMediator
    {
        $request = $this->createRequest('POST', $path, $data);
        return $this->createResponse($request);
    }

    protected function put(string $path, array $data = []): ResponseMediator
    {
        $request = $this->createRequest('PUT', $path, $data);
        return $this->createResponse($request);
    }

    protected function patch(string $path, array $data): ResponseMediator
    {
        $request = $this->createRequest('PATCH', $path, $data);
        return $this->createResponse($request);
    }

    protected function delete(string $path): ResponseMediator
    {
        $request = $this->createRequest('DELETE', $path);
        return $this->createResponse($request);
    }
}
