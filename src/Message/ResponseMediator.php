<?php

declare(strict_types=1);

namespace SlimSdk\Message;

use JsonException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SlimSdk\Exception\BadRequestException;
use SlimSdk\Exception\ClientErrorException;
use SlimSdk\Exception\HttpException;
use SlimSdk\Exception\ServerErrorException;
use SlimSdk\Exception\UnprocessableEntityException;

class ResponseMediator
{
    private RequestInterface $request;

    private ResponseInterface $response;

    public function __construct(RequestInterface $request, ResponseInterface $response)
    {
        $this->request = $request;
        $this->response = $response;

        $this->process();
    }

    /**
     * @throws BadRequestException when response code is 400
     * @throws UnprocessableEntityException when response code is 422
     * @throws ClientErrorException when response code is 4xx
     * @throws ServerErrorException when response code is 5xx
     * @throws HttpException when response code is not successful
     */
    private function process(): void
    {
        $exceptionClass = null;
        $statusCode = $this->response->getStatusCode();
        $code2error = [
            400 => BadRequestException::class,
            422 => UnprocessableEntityException::class,
        ];

        if (isset($code2error[$statusCode])) {
            $exceptionClass = $code2error[$statusCode];

        } elseif ($statusCode >= 400 && $statusCode < 500) {
            $exceptionClass = ClientErrorException::class;

        } elseif ($statusCode >= 500 && $statusCode < 600) {
            $exceptionClass = ServerErrorException::class;

        } elseif ($statusCode >= 300) {
            $exceptionClass = HttpException::class;
        }

        if ($exceptionClass !== null) {
            $data = [
                'code' => $statusCode,
            ];

            $body = $this->getParsedBody();
            if (is_array($body)) {
                $data['msg'] = $body['message'] ?? '';
                $data['id'] = $body['id'] ?? '';
                if (isset($body['exception'])) {
                    $data['file'] = sprintf('%s(%d)', $body['exception']['file'] ?? '', $body['exception']['line'] ?? 0);
                    $data['trace'] = $body['exception']['trace'] ?? '';
                }
            }

            throw new $exceptionClass(
                sprintf('Unsuccessful response (%s)', json_encode($data)),
                $this->request,
                $this->response
            );
        }
    }

    /**
     * @return array|string
     * @throws JsonException
     */
    public function getParsedBody()
    {
        $body = (string) $this->response->getBody();
        if (stripos($this->response->getHeaderLine('Content-Type'), 'application/json') === 0) {
            return json_decode($body, true, 512, JSON_THROW_ON_ERROR);
        }

        return $body;
    }
}
