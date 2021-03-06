<?php

declare(strict_types=1);

namespace HPTronic\SlimSdk\Message;

use HPTronic\SlimSdk\Exception\BadRequestException;
use HPTronic\SlimSdk\Exception\ClientErrorException;
use HPTronic\SlimSdk\Exception\HttpException;
use HPTronic\SlimSdk\Exception\ServerErrorException;
use HPTronic\SlimSdk\Exception\UnprocessableEntityException;
use JsonException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use stdClass;

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
     * @param bool $asArray
     * @return array|stdClass|string
     * @throws JsonException
     */
    public function getParsedBody(bool $asArray = true)
    {
        $body = (string) $this->response->getBody();
        if (stripos($this->response->getHeaderLine('Content-Type'), 'application/json') === 0) {
            return json_decode($body, $asArray, 512, JSON_THROW_ON_ERROR);
        } elseif (strpos($this->response->getHeaderLine('Content-Type'), 'application/problem+json') === 0) {
            return json_decode($body, $asArray, 512, JSON_THROW_ON_ERROR);
        }

        return $body;
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
                'phrase' => $this->response->getReasonPhrase(),
            ];

            $body = $this->getParsedBody();
            if (is_array($body)) {
                $data['msg'] = $body['message'] ?? $body['detail'] ?? $body['title'] ?? '';
                $data['id'] = $body['id'] ?? '';
                if (isset($body['exception'])) {
                    $data['file'] = sprintf('%s(%d)', $body['exception']['file'] ?? '', $body['exception']['line'] ?? 0);
                    $data['trace'] = $body['exception']['trace'] ?? '';
                }
            }

            throw new $exceptionClass(
                sprintf('Unsuccessful response (%s)', json_encode($data)),
                $this->request,
                $this->response,
            );
        }
    }
}
