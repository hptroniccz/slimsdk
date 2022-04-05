<?php

/** phpcs:disable PSR1.Files.SideEffects */

declare(strict_types=1);

namespace SlimSdk\Tests\Sdk\Message;

use Http\Message\CookieJar;
use Mockery;
use Nette\Http\IRequest;
use SlimSdk\Message\BrowserCookies;
use SlimSdk\Tests\TestCase;
use Tester\Assert;

require_once __DIR__ . '/../bootstrap.php';

class BrowserCookiesTest extends TestCase
{
    public function testCreate(): void
    {
        $netteRequest = Mockery::mock(IRequest::class);
        $netteRequest->shouldReceive('getCookies')->andReturn(['FOO' => 'BAR', 'COOKIE' => 'TASTY']);

        $browserCookies = new BrowserCookies(['COOKIE'], $netteRequest);
        $cookieJar = $browserCookies->create();

        Assert::type(CookieJar::class, $cookieJar);
        Assert::true($cookieJar->hasCookies());
        Assert::same(1, $cookieJar->count());
    }
}

(new BrowserCookiesTest())->run();
