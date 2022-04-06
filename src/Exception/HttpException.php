<?php
// https://datatracker.ietf.org/doc/html/rfc7807#section-3.1


declare(strict_types=1);

namespace HPTronic\SlimSdk\Exception;

use Http\Client\Exception\HttpException as BaseHttpException;
use stdClass;

class HttpException extends BaseHttpException
{
    public function getParsedResponse(): stdClass
    {
        return json_decode((string) $this->response->getBody(), false, 512, JSON_THROW_ON_ERROR);
    }

    public function getResponseCode(): int
    {
        if ($this->isAppProblem()) {
            return $this->response->getStatusCode();
        } else {
            return $this->getParsedResponse()->code;
        }
    }

    public function getResponseError(): string
    {
        if ($this->isAppProblem()) {
            return $this->getParsedResponse()->title;
        } else {
            return $this->getParsedResponse()->error;
        }
    }

    public function getResponseMessage(): string
    {
        if ($this->isAppProblem()) {
            return $this->getParsedResponse()->detail;
        } else {
            return $this->getParsedResponse()->message;
        }
    }

    public function getResponseId(): string
    {
        return $this->getParsedResponse()->id ?? '';
    }

    private function isAppProblem(): bool
    {
        return strpos($this->response->getHeaderLine('Content-Type'), 'application/problem+json') === 0;
    }
}
