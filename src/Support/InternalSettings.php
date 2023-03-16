<?php

namespace CodeDistortion\ClarityLogger\Support;

/**
 * Common values, shared throughout XXX.
 */
abstract class InternalSettings
{
    // Laravel specific settings

    /** @var string The Clarity Logger config file that gets published. */
    public const LARAVEL_LOGGER__CONFIG_PATH = '/config/logger.config.php';

    /** @var string The name of the Clarity Logger config file. */
    public const LARAVEL_LOGGER__CONFIG_NAME = 'code_distortion.clarity_logger';
}
