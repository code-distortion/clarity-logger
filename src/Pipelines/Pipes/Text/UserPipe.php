<?php

namespace CodeDistortion\ClarityLogger\Pipelines\Pipes\Text;

use CodeDistortion\ClarityLogger\Pipelines\PipelineInput;
use CodeDistortion\ClarityLogger\Pipelines\PipelineOutput;
use CodeDistortion\ClarityLogger\Pipelines\Pipes\AbstractPipe;
use CodeDistortion\ClarityLogger\Settings;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Throwable;

/**
 * Render the user's details.
 */
class UserPipe extends AbstractPipe
{
    /** @var boolean Allow addUser() to throw an exception when for tests. */
    private bool $throwTestingException = false;



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
     * @throws Throwable When an exception occurs while loading the user details.
     * @throws Exception Phpcs expects this to be here.
     */
    public function run(): void
    {
        if (!$this->shouldRun()) {
            return;
        }

        try {

            if ($this->throwTestingException) {
                throw new Exception('testing');
            }

            $this->addUser();

        } catch (Throwable $e) {
            $this->addUserIpOnly();
            throw $e;
        } finally {
            $this->addUserAgent();
        }
    }

    /**
     * Add the current user.
     *
     * @return void
     */
    private function addUser(): void
    {
        /** @var Model|null $user */
        $user = $this->request->user();
//        $user = UserModel::find(2);
//        $user = null;

        $userString = $user instanceof Model
            ? "$user->id - $user->name - $user->email"
            : "(guest)";
        $userString .= " ({$this->request->getClientIp()})";

        $table = $this->output->reuseTableOrNew();
        $table->row('user', $userString);
    }

    /**
     * Add the user's ip address only.
     *
     * @return void
     */
    private function addUserIpOnly(): void
    {
        $userString = "({$this->request->getClientIp()})";

        $table = $this->output->reuseTableOrNew();
        $table->row('user', $userString);
    }

    /**
     * Add the user-agent.
     *
     * @return void
     */
    private function addUserAgent(): void
    {
        $table = $this->output->reuseTableOrNew();
        $table->row(Settings::INDENT1 . 'agent', $this->request->userAgent());
    }
}
