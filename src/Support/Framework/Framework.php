<?php

namespace CodeDistortion\ClarityLogger\Support\Framework;

use CodeDistortion\ClarityLogger\Exceptions\ClarityLoggerInitialisationException;
use CodeDistortion\ClarityLogger\Support\Environment;
use CodeDistortion\ClarityLogger\Support\Framework\Config\FrameworkConfigInterface;
use CodeDistortion\ClarityLogger\Support\Framework\Config\LaravelFrameworkConfig;

/**
 * Provide an easy pathway to access the correct config object, based on the current framework.
 */
class Framework
{
    /**
     * A cache of the object used to interact with the framework's configuration.
     *
     * Note: this is safe to store as a static property (which will bleed between test, jobs, octane requests, etc),
     * because the object itself doesn't contain any state. In fact, every method inside is static.
     *
     * @var FrameworkConfigInterface|null
     */
    private static ?FrameworkConfigInterface $frameworkConfig = null;



    /**
     * Resolve which config instance to use.
     *
     * @return FrameworkConfigInterface
     * @throws ClarityLoggerInitialisationException When the current framework can't be determined.
     */
    public static function config(): FrameworkConfigInterface
    {
        return self::$frameworkConfig ??= self::buildNewFrameworkConfig();
    }

    /**
     * Build a new config instance.
     *
     * @return FrameworkConfigInterface
     * @throws ClarityLoggerInitialisationException When the current framework can't be determined.
     */
    private static function buildNewFrameworkConfig(): FrameworkConfigInterface
    {
        if (Environment::isLaravel()) {
            return new LaravelFrameworkConfig();
        }
        throw ClarityLoggerInitialisationException::unknownFramework();
    }
}
