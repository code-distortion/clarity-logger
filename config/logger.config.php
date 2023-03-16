<?php

use CodeDistortion\ClarityLogger\Renderers\Laravel\TextRenderer;
use CodeDistortion\ClarityLogger\Settings;

return [

    /*
     |--------------------------------------------------------------------------
     | Renderer
     |--------------------------------------------------------------------------
     |
     | ...
     |
     */

    'renderers' => [

        'default' => TextRenderer::class,

        'channels' => [
//            'slack' => TextRenderer::class,
//            'stack' => TextRenderer::class,
        ],

    ],

    /*
     |--------------------------------------------------------------------------
     | Default Reporting Levels
     |--------------------------------------------------------------------------
     |
     | The default log reporting level to use when one isn't set at call-time.
     | See https://laravel.com/docs/10.x/logging#writing-log-messages
     | for more details.
     |
     | string
     |
     */

    'levels' => [
        'message' => Settings::REPORTING_LEVEL_INFO,
        'exception' => Settings::REPORTING_LEVEL_ERROR,
    ],

    /*
     |--------------------------------------------------------------------------
     | Date/Time
     |--------------------------------------------------------------------------
     |
     | Specify how dates and times will be shown.
     |
     | https://www.php.net/manual/en/timezones.php
     |
     | timezones - array or comma separated string (e.g. 'Australia/Sydney,UTC')
     | defaults to Laravel's "app.timezone" setting.
     |
     */

    'time' => [
        'timezones' => env('CLARITY_LOGGER__TIMEZONES'),
        'format' => ['l', 'jS', 'F', '\a\t g:ia', '(e)', '', 'Y-m-d H:i:s', 'T', 'P'],
    ],

    /*
     |--------------------------------------------------------------------------
     | Prefix
     |--------------------------------------------------------------------------
     |
     | This prefix is added to the beginning of each line of the log message
     | when using the TextRenderer class.
     |
     | string
     |
     | '%level%' and '%LEVEL%' will be replaced with the log level.
     |
     */

    'prefix' => '',
//    'prefix' => '%level%> ',
//    'prefix' => '%LEVEL%> ',

    /*
     |--------------------------------------------------------------------------
     | Order
     |--------------------------------------------------------------------------
     |
     | When using code-distortion/clarity-context, context data is added to the
     | log. Use either the "stack trace" order (most recent to oldest), or
     | "call stack" order (oldest to newest) for this context data.
     |
     | boolean
     |
     */

    'oldest_first' => env('CLARITY_LOGGER__OLDEST_FIRST', true),

];
