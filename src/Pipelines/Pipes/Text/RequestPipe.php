<?php

namespace CodeDistortion\ClarityLogger\Pipelines\Pipes\Text;

use CodeDistortion\ClarityLogger\Pipelines\PipelineInput;
use CodeDistortion\ClarityLogger\Pipelines\PipelineOutput;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\AbstractPipe;
use CodeDistortion\ClarityLogger\Settings;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

/**
 * Render the current request details.
 */
class RequestPipe extends AbstractPipe
{
    /**
     * Constructor.
     *
     * Properties are resolved using Laravel's dependency injection.
     *
     * @param Request        $request The current request.
     * @param PipelineInput  $input   The input being reported.
     * @param PipelineOutput $output  The object managing the output.
     */
    public function __construct(
        private Request $request,
        private PipelineInput $input,
        private PipelineOutput $output,
    ) {
    }



    /**
     * Determine if this pipe step should be run.
     *
     * @return boolean
     */
    private function shouldRun(): bool
    {
        return !$this->input->getRunningInConsole();
    }



    /**
     * Run the pipe step.
     *
     * @return void
     */
    public function run(): void
    {
        if (!$this->shouldRun()) {
            return;
        }

        $this->addRequestAndReferrer();

        ($route = $this->getRoute())
            ? $this->addRouteInfo($route)
            : $this->addNoRouteInfo();
    }



    /**
     * Add the request and referrer info to the table.
     *
     * @return void
     */
    private function addRequestAndReferrer(): void
    {
        $table = $this->output->reuseTableOrNew();

        $requestLine = $this->request->method() . ' ' . $this->request->fullUrl();
        $referrer = (string) $this->request->headers->get('referer');

        if (mb_strlen($referrer)) {
            $table->row('request', $requestLine);
            $table->row(Settings::INDENT1 . 'referrer', $referrer);
        } else {
            $table->row('request', $requestLine . ' (no referrer)');
        }
    }

    /**
     * Retrieve the route info.
     *
     * @return Route|null
     */
    private function getRoute(): ?Route
    {
        $route = $this->request->route();
        return $route instanceof Route
            ? $route
            : null;
    }

    /**
     * Add the route info to the table.
     *
     * @param Route $route The route info to add.
     * @return void
     */
    private function addRouteInfo(Route $route): void
    {
        $table = $this->output->reuseTableOrNew();

        $routeName = $route->getName() ?? '(unnamed)';

        /** @var string[] $middleware */
        $middleware = $route->middleware();
        $middleware = count($middleware)
            ? implode(', ', $middleware)
            : 'n/a';

        /** @var string[] $excludedMiddleware */
        $excludedMiddleware = $route->excludedMiddleware();
        $excludedMiddleware = count($excludedMiddleware)
            ? implode(', ', $excludedMiddleware)
            : null;

        $action = $route->action;
        $controllerAction = is_callable($action['uses'] ?? null)
            ? '(closure)'
            : ($action['uses'] ?? 'n/a');

        $table->row(Settings::INDENT1 . 'route', $routeName);
        $table->row(Settings::INDENT1 . 'middleware', $middleware);
        $table->row(Settings::INDENT1 . 'excl. middleware', $excludedMiddleware);
        $table->row(Settings::INDENT1 . 'action', $controllerAction);

        $this->addTraceIdentifiers();
    }

    /**
     * Add "no-route" info to the table.
     *
     * @return void
     */
    private function addNoRouteInfo(): void
    {
        $table = $this->output->reuseTableOrNew();

        $table->row(Settings::INDENT1 . 'route', '(unavailable)');

        $this->addTraceIdentifiers();
    }



    /**
     * Add the trace identifiers to the table.
     *
     * @return void
     */
    private function addTraceIdentifiers(): void
    {
        $context = $this->input->getClarityContext();
        if (!$context) {
            return;
        }

        $identifiers = $this->buildTraceIdentifiersList($context->getTraceIdentifiers());
        if (!$identifiers) {
            return;
        }

        $this->output->reuseTableOrNew()->row(
            Settings::INDENT1 . (count($identifiers) == 1 ? 'trace-id' : 'trace-ids'),
            implode(PHP_EOL, $identifiers)
        );
    }

    /**
     * Build a readable list of trace identifiers.
     *
     * @param array<string,string|integer> $traceIdentifiers The trace identifiers to build a list of.
     * @return string[]
     */
    private function buildTraceIdentifiersList(array $traceIdentifiers): array
    {
        $identifiers = [];
        foreach ($traceIdentifiers as $name => $id) {
            $identifiers[] = mb_strlen($name)
                ? "$name: $id"
                : "$id";
        }

        return $identifiers;
    }
}
