<?php

namespace CodeDistortion\ClarityLogger\Tests;

use CodeDistortion\ClarityContext\ServiceProvider as ContextServiceProvider;
use CodeDistortion\ClarityControl\ServiceProvider as ControlServiceProvider;
use CodeDistortion\ClarityLogger\ServiceProvider as LoggerServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * The Laravel test case.
 */
abstract class LaravelTestCase extends BaseTestCase
{
    /**
     * Get package providers.
     *
     * @param Application $app The Laravel app.
     * @return array<int, class-string>
     */
    // phpcs:ignore
    protected function getPackageProviders($app)
    {
        $return = [];

        if (class_exists(ContextServiceProvider::class)) {
            $return[] = ContextServiceProvider::class;
        }

        if (class_exists(ControlServiceProvider::class)) {
            $return[] = ControlServiceProvider::class;
        }

        $return[] = LoggerServiceProvider::class;

        return $return;
    }
}
