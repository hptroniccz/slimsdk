<?php
/**
 * phpcs:disable
 * inspirace pro test @see https://github.com/nette/application/blob/e76758fa921a2fc2daff54c2c910da4c59b0e373/tests/Application/Application.run.phpt
 */

declare(strict_types=1);

namespace SlimSdk\Tests;

use Mockery;
use Tester;

require_once __DIR__ . '/../vendor/autoload.php';

// configure environment
Tester\Environment::setup();
date_default_timezone_set('Europe/Prague');

// output buffer level check
register_shutdown_function(static function ($level): void {
    Tester\Assert::same($level, ob_get_level());
}, ob_get_level());

class TestCase extends Tester\TestCase
{
    protected function tearDown()
    {
        Mockery::close();
    }
}
