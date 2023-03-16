<?php

namespace CodeDistortion\ClarityLogger\Tests\Integration;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use CodeDistortion\ClarityContext\Clarity;
use CodeDistortion\ClarityContext\Context;
use CodeDistortion\ClarityControl\Control;
use CodeDistortion\ClarityLogger\Pipelines\PipelineInput;
use CodeDistortion\ClarityLogger\Renderers\Laravel\TextRenderer;
use CodeDistortion\ClarityLogger\Renderers\RendererInterface;
use CodeDistortion\ClarityLogger\Settings;
use CodeDistortion\ClarityLogger\Support\Framework\Framework;
use CodeDistortion\ClarityLogger\Tests\LaravelTestCase;
use CodeDistortion\ClarityLogger\Tests\TestSupport\UserModel;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Throwable;

/**
 * Test the TextRenderer pipeline.
 *
 * @phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
class LaravelTextRendererPipelineIntegrationTest extends LaravelTestCase
{
    /**
     * Test the output that the TextRenderer pipeline generates.
     *
     * @test
     * @dataProvider renderDataProvider
     *
     * @param string                      $projectRootDir       The project root-dir.
     * @param boolean                     $runningInConsole     Whether the code is running from the console or not.
     * @param string|null                 $consoleCommand       The console command being run.
     * @param boolean                     $useRequest           Whether to use a request or not.
     * @param class-string                $defaultRenderer      The default renderer to use.
     * @param array<string, class-string> $channelRenderers     The renderers for a particular channels.
     * @param string[]                    $timezones            The timezones to render dates/times in.
     * @param string[]                    $dateTimeFormat       The format to render dates/times in.
     * @param string                      $prefix               The prefix to use.
     * @param string                      $channel              The channel being reported to.
     * @param string                      $level                The reporting level being used.
     * @param string|null                 $message              The caller-specified message.
     * @param integer|null                $useExceptionNumber   Which exception to use (if any).
     * @param mixed[]                     $callerContextArray   The array of context details the caller reported.
     * @param boolean                     $useClarityContext    Should a Clarity context object be used.
     * @param integer                     $occurredAtTimestamp  When the exception occurred.
     * @param integer|null                $useKnownIssuesNumber How many "known" issues to add via Clarity.
     * @param boolean                     $useReferrer          Whether to add a referrer or not.
     * @param boolean                     $userIsLoggedIn       Whether to build a user or not.
     * @param boolean                     $useRoute             Whether to build the route or not.
     * @param boolean                     $useRouteName         Whether to add a route name or not.
     * @param boolean                     $useRouteMiddleware   Whether to add middleware or not.
     * @param boolean                     $useRouteController   Whether to use a Controller route (or a closure).
     * @return void
     * @throws Throwable Any exception that occurs.
     */
    public static function test_text_renderer_output(
        string $projectRootDir,
        bool $runningInConsole,
        ?string $consoleCommand,
        bool $useRequest,
        string $defaultRenderer,
        array $channelRenderers,
        array $timezones,
        array $dateTimeFormat,
        string $prefix,
        string $channel,
        string $level,
        ?string $message,
        ?int $useExceptionNumber,
        array $callerContextArray,
        bool $useClarityContext,
        int $occurredAtTimestamp,
        ?int $useKnownIssuesNumber,
        bool $useReferrer,
        bool $userIsLoggedIn,
        bool $useRoute,
        bool $useRouteName,
        bool $useRouteMiddleware,
        bool $useRouteController,
    ): void {

        // stop if the Clarity Context or Clarity Control packages aren't installed
        // Note: it's ok that this test isn't performed (Github actions runs the tests with and without)
        if ((!class_exists(Clarity::class)) || (!class_exists(Control::class))) {
            self::assertTrue(true);
            return;
        }



        $projectRootDir = str_replace('/', DIRECTORY_SEPARATOR, $projectRootDir);

        $request = self::buildRequest($useRequest, $useReferrer);
        $route = self::buildRoute($useRoute, $useRouteName, $useRouteMiddleware, $useRouteController);
        $userModel = self::buildUserModel($userIsLoggedIn);
        self::overrideRequest($request, $route, $userModel);

        // add context details before the exceptions are generated
        $contextLine = null;
        if ($useClarityContext) {
            Clarity::context('something');
            Clarity::context(['a' => 'b']);
            $contextLine = __LINE__ - 2;
        }

        // throw (and catch) an exception if needed
        $exception1Line = $exception2Line = $exception3Line = null;
        $exceptionThrownLine = $exceptionCaughtLine = null;
        $exception = null;
        $clarityContext = null;
        if ($useExceptionNumber) {

            $closure = function () use (
                $useExceptionNumber,
                &$exception,
                &$exception1Line,
                &$exception2Line,
                &$exception3Line,
                &$exceptionThrownLine
            ) {
                $exception1 = new Exception('abc');
                $exception1Line = __LINE__ - 1;
                $exception2 = new Exception('def', 0, $exception1);
                $exception2Line = __LINE__ - 1;
                $exception3 = new Exception('ghi', 123, $exception2);
                $exception3Line = __LINE__ - 1;

                $exceptionThrownLine = match ($useExceptionNumber) {
                    1 => $exception1Line,
                    2 => $exception2Line,
                    3 => $exception3Line,
                };

                throw $exception = match ($useExceptionNumber) {
                    1 => $exception1,
                    2 => $exception2,
                    3 => $exception3,
                };
            };

            $control = Control::prepare($closure)->report(false);
            if ($useClarityContext) {
                $control->callback(function (Context $context) use (&$clarityContext) {
                    $clarityContext = $context;
                });
            }
            for ($count = 0; $count < (int) $useKnownIssuesNumber; $count++) {
                $control->known("https://something.com/$count");
            }

            $control->execute();
            $exceptionCaughtLine = __LINE__ - 1;
        }



        // resolve the Clarity context object to use if needed
        if ($useClarityContext) {
            $clarityContext = $exception
                ? $clarityContext // captured above when catching the exception
                : Clarity::buildContextHere();
        }



        $input = new PipelineInput(
            $projectRootDir, // todo - populate inside the test method above(?)
            $runningInConsole,
            $consoleCommand,
            $defaultRenderer,
            $channelRenderers,
            $timezones,
            $dateTimeFormat,
            $prefix,
            true, // todo - check if this needs to be tested more here
            $channel,
            $level,
            $message,
            $exception,
            $callerContextArray,
            $clarityContext,
            CarbonImmutable::createFromTimestamp($occurredAtTimestamp, 'UTC'),
        );



        $renderer = self::buildRenderer($input->resolveRendererClass($channel));
        $output = $renderer->render($input);
        $triggerLine = __LINE__ - 1;

        $expectedOutput = self::buildExpectedOutput(
            $input,
            $triggerLine,
            $exception1Line,
            $exception2Line,
            $exception3Line,
            $contextLine,
            $exceptionThrownLine,
            $exceptionCaughtLine,
        );



        try {
            self::assertSame($expectedOutput, $output);
        } catch (Throwable $e) {
            dump($expectedOutput, $output);
            throw $e;
        }

//        dump($useClarityContext, 'after: ' . memory_get_usage() / (1024 * 1024));
    }



    /**
     * DataProvider for test_text_renderer_output().
     *
     * @return array<array<string, mixed>>
     */
    public static function renderDataProvider()
    {
        $defaultProjectRoot = '';
        $defaultRunningInConsole = true;
        $defaultConsoleCommand = 'test-command';
        $defaultUseRequest = false;
        $defaultRenderer = TextRenderer::class;
        $defaultChannelRenderers = [];
        $defaultTimezones = ['UTC'];
        $defaultDateTimeFormat = ['l', 'jS', 'F', '\a\t g:ia', '(e)', '', 'Y-m-d H:i:s', 'T', 'P'];
        $defaultPrefix = '';
        $defaultChannel = 'stack';
        $defaultLevel = Settings::REPORTING_LEVEL_DEBUG;
        $defaultMessage = 'hello';
        $defaultUseExceptionNumber = null;
        $defaultCallerContextArray = ['a' => 'b'];
        $defaultUseClarityContext = false;

        $defaultOccurredAtTimestamp = mt_rand(0, time());
        $defaultUseKnownIssuesNumber = null;
        $defaultUseReferrer = false;
        $defaultUserIsLoggedIn = false;
        $defaultUseRoute = false;
        $defaultUseRouteName = false;
        $defaultUseRouteMiddleware = false;
        $defaultUseRouteController = false;



        $consoleCommandCombinations = [
            '',
            isset($_SERVER['argv'])
                ? implode(' ', $_SERVER['argv'])
                : '(unknown)'
        ];

        $timezoneCombinations = [
            ['UTC'],
            ['UTC', 'Australia/Sydney'],
            ['Australia/Sydney', 'UTC'],
        ];

        $dateTimeFormatCombinations = [
            ['l', 'jS', 'F', '\a\t g:ia', '(e)', '', 'Y-m-d H:i:s', 'T', 'P'],
            ['r']
        ];

        $prefixCombinations = [
            '',
            'ABC',
            '%level%',
            '%LEVEL%',
        ];

        $channelCombinations = [
            'stack',
//            'slack',
        ];

        $levelCombinations = [
            Settings::REPORTING_LEVEL_DEBUG,
            Settings::REPORTING_LEVEL_EMERGENCY,
        ];

        $messageCombinations = [
            null,
            'hello',
            "hello" . PHP_EOL . "there",
        ];

        $useExceptionNumberCombinations = [null, 1, 2, 3];

        $callerContextArrayCombinations = [
            [],
            ['a' => 'b'],
            ['a' => 'b', 'c' => 'd'],
            ['a' => ['b' => 'c']],
        ];

        $useClarityContextCombinations = [
            false,
            true,
        ];

        $useKnownIssuesNumberCombinations = [null, 1, 2];



        $defaultInputs = [
            'projectRootDir' => $defaultProjectRoot, // todo - populate inside the test method above(?)
            'runningInConsole' => $defaultRunningInConsole,
            'consoleCommand' => $defaultConsoleCommand,
            'useRequest' => $defaultUseRequest,
            'defaultRenderer' => $defaultRenderer,
            'channelRenderers' => $defaultChannelRenderers,
            'timezones' => $defaultTimezones,
            'dateTimeFormat' => $defaultDateTimeFormat,
            'prefix' => $defaultPrefix,
            'channel' => $defaultChannel,
            'level' => $defaultLevel,
            'message' => $defaultMessage,
            'useExceptionNumber' => $defaultUseExceptionNumber,
            'callerContextArray' => $defaultCallerContextArray,
            'useClarityContext' => $defaultUseClarityContext,
            'occurredAtTimestamp' => $defaultOccurredAtTimestamp,
            'useKnownIssuesNumber' => $defaultUseKnownIssuesNumber,
            'useReferrer' => $defaultUseReferrer,
            'userIsLoggedIn' => $defaultUserIsLoggedIn,
            'useRoute' => $defaultUseRoute,
            'useRouteName' => $defaultUseRouteName,
            'useRouteMiddleware' => $defaultUseRouteMiddleware,
            'useRouteController' => $defaultUseRouteController,
        ];



        $return = [];

//        foreach ($consoleCommandCombinations as $consoleCommand) {
//            foreach ($useRequestCombinations as $useRequest) {
//
//                // make sure there's either a $consoleCommand or a $request
//                if (($consoleCommand) && ($useRequest)) {
//                    continue;
//                }
//                if ((!$consoleCommand) && (!$useRequest)) {
//                    continue;
//                }
//
//                foreach ($timezoneCombinations as $timezones) {
//                    foreach ($dateTimeFormatCombinations as $dateTimeFormat) {
//                        foreach ($prefixCombinations as $prefix) {
//                            foreach ($channelCombinations as $channel) {
//                                foreach ($levelCombinations as $level) {
//                                    foreach ($messageCombinations as $message) {
//                                        foreach ($useExceptionNumberCombinations as $useExceptionNumber) {
//
//                                            // make sure there's either a $message or an $exception
//                                            if (($message) && ($useExceptionNumber)) {
//                                                continue;
//                                            }
//                                            if ((!$message) && (!$useExceptionNumber)) {
//                                                continue;
//                                            }
//
//                                            foreach ($callerContextArrayCombinations as $callerContextArray) {
//                                                foreach ($useClarityContextCombinations as $useClarityContext) {
//                                                    foreach ($useKnownIssuesNumberCombinations as $useKnownIssuesNumber) {
//
//                                                        $return[] = [
//                                                            'projectRootDir' => $defaultProjectRoot, // todo - populate inside the test method above(?)
//                                                            'runningInConsole' => (bool) $consoleCommand,
//                                                            'consoleCommand' => $consoleCommand,
//                                                            'useRequest' => $useRequest,
//                                                            'defaultRenderer' => $defaultRenderer,
//                                                            'channelRenderers' => $defaultChannelRenderers,
//                                                            'timezones' => $timezones,
//                                                            'dateTimeFormat' => $dateTimeFormat,
//                                                            'prefix' => $prefix,
//                                                            'channel' => $channel,
//                                                            'level' => $level,
//                                                            'message' => $message,
//                                                            'useExceptionNumber' => $useExceptionNumber,
//                                                            'callerContextArray' => $callerContextArray,
//                                                            'occurredAtTimestamp' => $defaultOccurredAtTimestamp,
//                                                            'useClarityContext' => $useClarityContext,
//                                                            'useKnownIssuesNumber' => $useKnownIssuesNumber,
//                                                        ];
//                                                    }
//                                                }
//                                            }
//                                        }
//                                    }
//                                }
//                            }
//                        }
//                    }
//                }
//            }
//        }

        foreach ($messageCombinations as $message) {
            foreach ($useClarityContextCombinations as $useClarityContext) {
                foreach ($useKnownIssuesNumberCombinations as $useKnownIssuesNumber) {
                    $return[] = array_merge($defaultInputs, [
                        'message' => $message,
                        'useExceptionNumber' => null,
                        'useClarityContext' => $useClarityContext,
                        'useKnownIssuesNumber' => $useKnownIssuesNumber,
                    ]);
                }
            }
        }

        foreach ($useExceptionNumberCombinations as $useExceptionNumber) {
            foreach ($useClarityContextCombinations as $useClarityContext) {
                foreach ($useKnownIssuesNumberCombinations as $useKnownIssuesNumber) {
                    $return[] = array_merge($defaultInputs, [
                        'message' => null,
                        'useExceptionNumber' => $useExceptionNumber,
                        'useClarityContext' => $useClarityContext,
                        'useKnownIssuesNumber' => $useKnownIssuesNumber,
                    ]);
                }
            }
        }

        foreach ($consoleCommandCombinations as $consoleCommand) {
            $return[] = array_merge($defaultInputs, [
                'runningInConsole' => (bool) $consoleCommand,
                'consoleCommand' => $consoleCommand,
            ]);
        }

        foreach ([true, false] as $useReferrer) {
            foreach ([true, false] as $userIsLoggedIn) {
                foreach ([true, false] as $useRoute) {
                    foreach ([true, false] as $useRouteName) {
                        foreach ([true, false] as $useRouteMiddleware) {
                            foreach ([true, false] as $useRouteController) {
                                $return[] = array_merge($defaultInputs, [
                                    'useRequest' => true,
                                    'runningInConsole' => false,
                                    'consoleCommand' => null,
                                    'useReferrer' => $useReferrer,
                                    'userIsLoggedIn' => $userIsLoggedIn,
                                    'useRoute' => $useRoute,
                                    'useRouteName' => $useRouteName,
                                    'useRouteMiddleware' => $useRouteMiddleware,
                                    'useRouteController' => $useRouteController,
                                ]);
                            }
                        }
                    }
                }
            }
        }

        foreach ($timezoneCombinations as $timezones) {
            foreach ($dateTimeFormatCombinations as $dateTimeFormat) {
                $return[] = array_merge($defaultInputs, [
                    'timezones' => $timezones,
                    'dateTimeFormat' => $dateTimeFormat,
                ]);
            }
        }

        foreach ($channelCombinations as $channel) {
            foreach ($levelCombinations as $level) {
                foreach ($prefixCombinations as $prefix) {
                    $return[] = array_merge($defaultInputs, [
                        'prefix' => $prefix,
                        'channel' => $channel,
                        'level' => $level,
                    ]);
                }
            }
        }

        foreach ($callerContextArrayCombinations as $callerContextArray) {
            $return[] = array_merge($defaultInputs, [
                'callerContextArray' => $callerContextArray,
            ]);
        }

        return $return;
    }





    /**
     * Generate the expected output.
     *
     * @param PipelineInput $input               The PipelineInput to use.
     * @param integer       $triggerLine         The line being reported.
     * @param integer|null  $exception1Line      The line exception 1 was triggered on.
     * @param integer|null  $exception2Line      The line exception 2 was triggered on.
     * @param integer|null  $exception3Line      The line exception 3 was triggered on.
     * @param integer|null  $contextLine         The line that context was added on.
     * @param integer|null  $exceptionThrownLine The line the exception was thrown on.
     * @param integer|null  $exceptionCaughtLine The line the exception was caught on.
     * @return string
     */
    private static function buildExpectedOutput(
        PipelineInput $input,
        int $triggerLine,
        ?int $exception1Line, // todo - remove?
        ?int $exception2Line, // todo - remove?
        ?int $exception3Line, // todo - remove?
        ?int $contextLine,
        ?int $exceptionThrownLine,
        ?int $exceptionCaughtLine,
    ): string {

        // output generation
        $output = self::renderException($input);
        $output .= self::renderMessage($input, $triggerLine);
        $output .= self::renderCommand($input);
        $output .= self::renderRequest($input);
        $output .= self::renderUser($input);
        $output .= self::renderOccurredAt($input);
        $output .= self::renderKnownIssues($input);
        $output .= self::renderCallerContextArray($input);
        $output .= self::renderClarityContext(
            $input,
            $contextLine,
            $exceptionThrownLine,
            $exceptionCaughtLine,
        );
        $output = rtrim($output, PHP_EOL);

        // post-processing
        $output = self::processTableTitles($output, ['~~~~']);
        return self::applyPrefix($input, $output);
    }



    /**
     * Render an exception's details.
     *
     * @param PipelineInput $input The PipelineInput to use.
     * @return string
     */
    private static function renderException(PipelineInput $input): string
    {
        $e = $input->getException();
        if (!$e) {
            return '';
        }

        $return = "EXCEPTION (CAUGHT):" . PHP_EOL;
        $return .= PHP_EOL;

        $count = 0;
        while ($e) {

            $class = get_class($e);
            $file = mb_substr($e->getFile(), mb_strlen($input->getProjectRootDir()));
            $file = ltrim($file, DIRECTORY_SEPARATOR);

            $title = !$count
                ? 'exception'
                : 'prev-ex.';

            if ($count > 1) {
                $title = "$title $count"; // e.g. "prev-ex. 2"
            }

            $code = $e->getCode()
                ? " (code {$e->getCode()})"
                : '';

            $return .= "$title ~~~~ $class: \"{$e->getMessage()}\"$code" . PHP_EOL;
            $return .= "- location ~~~~ $file on line {$e->getLine()} (closure)" . PHP_EOL;

            $e = $e->getPrevious();

            $count++;
        }

        return $return;
    }

    /**
     * Render a custom message.
     *
     * @param PipelineInput $input      The PipelineInput to use.
     * @param integer       $lineNumber The line the message was reported on.
     * @return string
     */
    private static function renderMessage(PipelineInput $input, int $lineNumber): string
    {
        $message = $input->getCallerMessage();
        if (!$message) {

            $return = '';
            if (!$input->getException()) {
                $return = "CUSTOM MESSAGE:" . PHP_EOL;
                $return .= PHP_EOL;
            }
            return $return;
        }

        $lines = explode(PHP_EOL, $message);
        $count = 0;
        foreach ($lines as $index => $line) {

            $newLine = $count < count($lines) - 1
                ? PHP_EOL
                : '';

            $lines[$index] = !$count
                ? "$line$newLine"
                : " ~~~~ $line$newLine";

            $count++;
        }

        $message = implode($lines);

        $file = mb_substr(__FILE__, mb_strlen($input->getProjectRootDir()));
        $file = ltrim($file, DIRECTORY_SEPARATOR);

        $return = "CUSTOM MESSAGE:" . PHP_EOL;
        $return .= PHP_EOL;
        $return .= "message ~~~~ \"$message\"" . PHP_EOL;
        $return .= "- location ~~~~ $file on line $lineNumber (method \"test_text_renderer_output\")" . PHP_EOL;

        return $return;
    }

    /**
     * Render the current console command.
     *
     * @param PipelineInput $input The PipelineInput to use.
     * @return string
     */
    private static function renderCommand(PipelineInput $input): string
    {
        $consoleCommand = $input->getConsoleCommand();
        if (!$consoleCommand) {
            return '';
        }

        $messageUser = get_current_user();

        $return = "command ~~~~ $consoleCommand" . PHP_EOL;
        $return .= "- user ~~~~ $messageUser" . PHP_EOL;
        return $return;
    }

    /**
     * Render the current request.
     *
     * @param PipelineInput $input The PipelineInput to use.
     * @return string
     */
    private static function renderRequest(PipelineInput $input): string
    {
        if ($input->getConsoleCommand()) {
            return '';
        }

        /** @var Request $request */
        $request = Framework::depInj()->make(Request::class);

        $method = mb_strtoupper($request->getMethod());
        $url = $request->fullUrl();
        $referrer = (string) $request->headers->get('referer');



        $return = '';
        if ($referrer) {
            $return .= "request ~~~~ $method $url" . PHP_EOL;
            $return .= "- referrer ~~~~ $referrer" . PHP_EOL;
        } else {
            $return .= "request ~~~~ $method $url (no referrer)" . PHP_EOL;
        }



        $route = $request->route();
        $route = $route instanceof Route
            ? $route
            : null;

        if ($route) {

            $action = $route->action;

            $routeName = $action['as'] ?? '(unnamed)';

            $middleware = $action['middleware'] ?? [];
            $middleware = count($middleware)
                ? implode(', ', $middleware)
                : 'n/a';

            $controllerAction = is_callable($action['uses'] ?? null)
                ? '(closure)'
                : ($action['uses'] ?? 'n/a');

            $return .= "- route ~~~~ $routeName" . PHP_EOL;
            $return .= "- middleware ~~~~ $middleware" . PHP_EOL;
            $return .= "- action ~~~~ $controllerAction" . PHP_EOL;
        } else {
            $return .= "- route ~~~~ (unavailable)" . PHP_EOL;
        }

        return $return;
    }

    /**
     * Render the current request's user.
     *
     * @param PipelineInput $input The PipelineInput to use.
     * @return string
     */
    private static function renderUser(PipelineInput $input): string
    {
        if ($input->getConsoleCommand()) {
            return '';
        }

        /** @var Request $request */
        $request = Framework::depInj()->make(Request::class);

        /** @var UserModel|null $user */
        $user = $request->user();
        $userString = $user instanceof Model
            ? "$user->id - $user->name - $user->email"
            : "(guest)";
        $userString .= " ({$request->getClientIp()})";

        $agent = $request->userAgent();

        $return = "user ~~~~ $userString" . PHP_EOL;
        $return .= "- agent ~~~~ $agent" . PHP_EOL;
        return $return;
    }

    /**
     * Render the occurred-at details.
     *
     * @param PipelineInput $input The PipelineInput to use.
     * @return string
     */
    private static function renderOccurredAt(PipelineInput $input): string
    {
        $occurredAt = $input->getOccurredAt();
        if (!$occurredAt) {
            return '';
        }

        // render the format-parts in each timezone
        $tzFormattedParts = [];
        $maxLengths = [];
        foreach ($input->getTimezones() as $timezone) {

            /** @var Carbon|CarbonImmutable $occurredAt */
            $occurredAt = $occurredAt->clone()->tz($timezone);

            $count = 0;
            foreach ($input->getDateTimeFormat() as $formatPart) {

                $formatted = $occurredAt->format($formatPart);
                $tzFormattedParts[$timezone][] = $formatted;

                $maxLengths[$count] ??= 0;
                $maxLengths[$count] = max($maxLengths[$count], mb_strlen($formatted));
                $count++;
            }
        }

        // piece them together, with the correct padding
        $lines = [];
        foreach ($input->getTimezones() as $timezone) {
            $count = 0;
            foreach ($tzFormattedParts[$timezone] as $index => $formattedPart) {
                $tzFormattedParts[$timezone][$index] = str_pad($formattedPart, $maxLengths[$count]);
                $count++;
            }
            $lines[] = implode(' ', $tzFormattedParts[$timezone]);
        }

        // piece the lines together
        $return = '';
        $count = 0;
        foreach ($lines as $line) {
            $return .= $count++ == 0
                ? "date/time ~~~~ $line" . PHP_EOL
                : " ~~~~ $line" . PHP_EOL;
        }

        return $return;
    }

    /**
     * Render the Clarity context known issues.
     *
     * @param PipelineInput $input The PipelineInput to use.
     * @return string
     */
    private static function renderKnownIssues(PipelineInput $input): string
    {
        $context = $input->getClarityContext();
        if (!$context) {
            return '';
        }
        if (!$context->hasKnown()) {
            return '';
        }

        $known = $context->getKnown();
        foreach ($known as $index => $issue) {
            $known[$index] = " ~~~~ $issue";
        }

        return "known" . implode(PHP_EOL, $known) . PHP_EOL;
    }

    /**
     * Render the caller's context array.
     *
     * @param PipelineInput $input The PipelineInput to use.
     * @return string
     */
    private static function renderCallerContextArray(PipelineInput $input): string
    {
        $callerContextArray = $input->getCallerContextArray();
        if (!$callerContextArray) {
            return '';
        }

        return match ($callerContextArray) {
            ['a' => 'b'] =>             "context ~~~~ a = 'b'" . PHP_EOL,
            ['a' => 'b', 'c' => 'd'] => "context ~~~~ a = 'b'" . PHP_EOL
                                             . " ~~~~ c = 'd'" . PHP_EOL,
            ['a' => ['b' => 'c']] =>    "context ~~~~ a = [" . PHP_EOL
                                             . " ~~~~   'b' => 'c'," . PHP_EOL
                                             . " ~~~~ ]" . PHP_EOL,
            default =>                  '',
        };
    }

    /**
     * Render the Clarity context information.
     *
     * @param PipelineInput $input               The PipelineInput to use.
     * @param integer|null  $contextLine         The line that context was added on.
     * @param integer|null  $exceptionThrownLine The line the exception was thrown on.
     * @param integer|null  $exceptionCaughtLine The line the exception was caught on.
     * @return string
     */
    private static function renderClarityContext(
        PipelineInput $input,
        ?int $contextLine,
        ?int $exceptionThrownLine,
        ?int $exceptionCaughtLine,
    ): string {

        $context = $input->getClarityContext();
        if (!$context) {
            return '';
        }
        if (!$context->detailsAreWorthListing()) {
            return '';
        }

        $path = 'tests/Integration/LaravelTextRendererPipelineIntegrationTest.php';
        $path = str_replace('/', DIRECTORY_SEPARATOR, $path);

        if ($exceptionThrownLine) {

            return PHP_EOL
                . "CONTEXT DETAILS:" . PHP_EOL
                . PHP_EOL
                . "$path on line $contextLine "
                    . "(method \"test_text_renderer_output\")" . PHP_EOL
                . "- \"something\"" . PHP_EOL
                . "- a = 'b'" . PHP_EOL
                . PHP_EOL
                . "$path on line $exceptionCaughtLine "
                    . "(method \"test_text_renderer_output\")" . PHP_EOL
                . "- The exception was caught (by Clarity)" . PHP_EOL
                . PHP_EOL
                . "$path on line $exceptionThrownLine "
                    . "(closure)" . PHP_EOL
                . "- The exception was thrown";
        } else {

            return PHP_EOL
                . "CONTEXT DETAILS:" . PHP_EOL
                . PHP_EOL
                . "$path on line $contextLine "
                    . "(method \"test_text_renderer_output\")" . PHP_EOL
                . "- \"something\"" . PHP_EOL
                . "- a = 'b'" . PHP_EOL;
        }
    }





    /**
     * Apply the prefix to the output.
     *
     * @param PipelineInput $input  The PipelineInput to use.
     * @param string        $output The output that's been generated so far.
     * @return string
     */
    private static function applyPrefix(PipelineInput $input, string $output): string
    {
        $prefix = $input->getPrefix();
        if (!$prefix) {
            return $output;
        }

        $replacements = [
            '%level%' => $input->getLevel(),
            '%LEVEL%' => mb_strtoupper($input->getLevel()),
        ];
        $prefix = str_replace(array_keys($replacements), array_values($replacements), $prefix);

        $output = PHP_EOL . $output . PHP_EOL;

        $lines = explode(PHP_EOL, $output);
        foreach ($lines as $index => $line) {
            $lines[$index] = "$prefix$line";
        }

        return PHP_EOL
            . PHP_EOL
            . implode(PHP_EOL, $lines)
            . PHP_EOL;
    }

    /**
     * Evenly pad table titles in a string.
     *
     * @param string   $string        The string to update.
     * @param string[] $tableDividers The strings dividing titles and content.
     * @return string
     */
    private static function processTableTitles(string $string, array $tableDividers): string
    {
        foreach ($tableDividers as $divider) {

            $divider = preg_quote($divider);

            $longest = 0;
            foreach (explode(PHP_EOL, $string) as $line) {
                if (preg_match("/^([^~]*) $divider (.+)$/", $line, $matches)) {
                    $longest = max($longest, mb_strlen($matches[1]));
                }
            }

            $lines = [];
            foreach (explode(PHP_EOL, $string) as $line) {
                if (preg_match("/^([^~]*) $divider (.+)$/", $line, $matches)) {
                    $title = str_pad($matches[1], $longest);
                    $content = $matches[2];
                    $lines[] = "$title  $content";
                } else {
                    $lines[] = "$line";
                }
            }

            $string = implode(PHP_EOL, $lines);
        }

        return $string;
    }



    /**
     * Override the "current" request with a new one.
     *
     * @param Request|null   $request   The request to use.
     * @param Route|null     $route     The "current route".
     * @param UserModel|null $userModel The "logged-in user".
     * @return void
     */
    private static function overrideRequest(
        ?Request $request,
        ?Route $route,
        ?UserModel $userModel,
    ): void {

        if (!$request) {
            return;
        }

        if ($route) {
            $request->setRouteResolver(fn() => $route);
        }

        if ($userModel) {
            $request->setUserResolver(fn() => $userModel);
        }

        Framework::depInj()->set(Request::class, $request);
    }

    /**
     * Build a Request.
     *
     * @param boolean $useRequest  Whether to build the request or not.
     * @param boolean $useReferrer Whether to add a referrer or not.
     * @return Request|null
     */
    private static function buildRequest(bool $useRequest, bool $useReferrer): ?Request
    {
        if (!$useRequest) {
            return null;
        }

        $method = mt_rand(0, 1)
            ? 'GET'
            : 'POST';

        $data = mt_rand(0, 1)
            ? []
            : ['a' => 'b'];

        $request = Request::create('/path/to/page', $method, $data);

        if ($useReferrer) {
            $request->headers->add(['referer' => 'https://somewhere.else/']);
        }

        return $request;
    }

    /**
     * Build a route.
     *
     * @param boolean $build              Whether to build the route or not.
     * @param boolean $useRouteName       Whether to add a route name or not.
     * @param boolean $useRouteMiddleware Whether to add middleware or not.
     * @param boolean $useRouteController Whether to use a Controller route (or a closure).
     * @return Route|null
     */
    private static function buildRoute(
        bool $build,
        bool $useRouteName,
        bool $useRouteMiddleware,
        bool $useRouteController
    ): ?Route {

        if (!$build) {
            return null;
        }

        $action = $useRouteController
            ? ['SomeController', 'actionName']
            : fn() => true;

        $route = new Route(['GET', 'POST'], '/blah/', $action);

        if ($useRouteName) {
            $route->name('route.name');
        }

        if ($useRouteMiddleware) {
            $route->middleware(['SomeMiddleware']);
        }

        return $route;
    }

    /**
     * Build a user model.
     *
     * @param boolean $build Whether to build the user or not.
     * @return UserModel|null
     */
    private static function buildUserModel(bool $build): ?UserModel
    {
        if (!$build) {
            return null;
        }

        return new UserModel([
            'id' => mt_rand(1, 1000),
            'name' => 'Bob',
            'email' => 'test@test.com'
        ]);
    }

    /**
     * Build a new renderer instance.
     *
     * @param class-string $rendererClass The class to use.
     * @return RendererInterface
     *
     */
    private static function buildRenderer(string $rendererClass): RendererInterface
    {
        /** @var RendererInterface $renderer */
        $renderer = Framework::depInj()->make($rendererClass);

        return $renderer;
    }
}
