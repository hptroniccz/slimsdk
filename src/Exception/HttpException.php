<?php

declare(strict_types=1);

namespace SlimSdk\Exception;

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
        return $this->getParsedResponse()->code;
    }

    public function getResponseError(): string
    {
        return $this->getParsedResponse()->error;
    }

    public function getResponseMessage(): string
    {
        return $this->getParsedResponse()->message;
    }

    public function getResponseId(): string
    {
        return $this->getParsedResponse()->id;
    }
}
