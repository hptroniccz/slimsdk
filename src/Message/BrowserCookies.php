<?php

declare(strict_types=1);

namespace SlimSdk\Message;

use Http\Message\Cookie;
use Http\Message\CookieJar;
use Nette\Http\IRequest;

/**
 * @link http://docs.php-http.org/en/latest/plugins/cookie.html
 */
class BrowserCookies
{
    private array $selectedCookies;

    private IRequest $browserRequest;

    public function __construct(array $selectedCookies, IRequest $browserRequest)
    {
        $this->selectedCookies = $selectedCookies;
        $this->browserRequest = $browserRequest;
    }

    public function create(): CookieJar
    {
        $cookies = [];
        $browserCookies = $this->browserRequest->getCookies();
        foreach ($this->selectedCookies as $key) {
            if (isset($browserCookies[$key])) {
                $cookies[] = new Cookie($key, $browserCookies[$key]);
            }
        }

        $cookieJar = new CookieJar();
        $cookieJar->addCookies($cookies);

        return $cookieJar;
    }
}
