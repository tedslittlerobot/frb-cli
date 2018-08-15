<?php

namespace Tlr\Frb\Tasks;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Tlr\Frb\Config;

class Git extends AbstractTask
{
    /**
     * The "section" name for the task.
     *
     * @var string
     */
    protected $section = 'Git';

    /**
     * Run a git fetch command
     *
     * @return Tlr\Frb\Tasks\Git
     */
    public function fetch()
    {
        $this->progress('Fetching...');

        $process = new Process('git fetch --all');
        $process->run();

        $this->log($process);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->progress('Fetched.');

        return $this;
    }

    /**
     * Checkout the given branch
     *
     * @param  string $branch
     * @return Tlr\Frb\Tasks\Git
     */
    public function checkout(string $branch) : Git
    {
        $this->progress('Checking out ' . $branch);

        $process = new Process('git checkout ' . $branch);
        $process->run();

        return $this;
    }

    /**
     * Add the given remote repository
     *
     * @param  string $name
     * @param  string $url
     * @return Tlr\Frb\Tasks\Git
     */
    public function addRemote(string $name, string $url) : Git
    {
        $this->formatProgress('Adding Remote: [%s : %s]', $name, $url);

        $process = new Process(sprintf(
            'git remote add %s %s',
            $name,
            $url
        ));

        $process->run();

        return $this;
    }

    /**
     * Add the environment's fortrabbit remote
     *
     * @param  Tlr\Frb\Config $config
     * @return Tlr\Frb\Tasks\Git
     */
    public function addEnvironmentRemote(Config $config) : Git
    {
        return $this->addRemote(
            'fortrabbit-' . $config->environment(),
            $config->gitUrl()
        );
    }

    /**
     * Determine if the remote exists
     *
     * @return boolean
     */
    public function hasRemote(string $remote) : bool
    {
        $process = new Process('git remote');
        $process->run();

        return str_contains($process->getOutput(), $remote);
    }

    /**
     * Get the current branch
     *
     * @return string
     */
    public function getCurrentBranch() : string
    {
        $process = new Process('git branch');
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $branches = collect(explode(PHP_EOL, $process->getOutput()));

        $currentBranch = $branches->first(function(string $branch) {
            return str_contains($branch, '*');
        });

        return trim(str_replace('*', '', $currentBranch));
    }

    /**
     * Execute the given callback on the given branch
     *
     * @param  string   $branch
     * @param  Callable $callback
     * @return Tlr\Frb\Tasks\Git
     */
    public function onBranch(string $branch, Callable $callback) : Git
    {
        $initialHead = $this->getCurrentBranch();
        $this->checkout($branch);

        try {
            call_user_func_array(
                $callback,
                [$this, $this->command, $this->input, $this->output]
            );
        } catch (\Exception $e) {
            // always restore context to previous, even if an error happens
            $this->checkout($initialHead);

            throw $e;
        }

        // restore context to previous
        $this->checkout($initialHead);

        return $this;
    }
}
