<?php

namespace Tlr\Frb\Tasks;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Tlr\Frb\Config;
use Tlr\Frb\Tasks\AbstractTask;

class Git extends AbstractTask
{
    /**
     * The "section" name for the task.
     *
     * @var string
     */
    protected $section = 'Git';

    /**
     *   STAGE HELPERS
     */

    /**
     * Check if the current git stage is dirty
     *
     * @return bool
     */
    public function stageIsDirty() : bool
    {
        $process = new Process('git status --porcelain');

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $stage = $process->getOutput();

        return !!trim($stage);
    }

    /**
     * Check if the current git stage is clean
     *
     * @return bool
     */
    public function stageIsClean() : bool
    {
        return !$this->stageIsDirty();
    }

    /**
     * Ensure the staging area is clear!
     *
     * @return Git
     */
    public function ensureStageIsClean() : Git
    {
        if ($this->stageIsDirty()) {
            throw new \Exception('The Git Staging area has uncommitted files. Clean up your current branch, then try again.');
        }

        return $this;
    }

    /**
     *   REMOTE HELPERS
     */

    /**
     * Run a git fetch command
     *
     * @return Tlr\Frb\Tasks\Git
     */
    public function fetch()
    {
        $this->progress('Fetching latest code.');

        $process = new Process('git fetch --all');
        $process->run();

        $this->log($process);

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

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

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

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
            $config->fortrabbitRemoteName(),
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
     * Determine if the fortrabbit remote exists
     *
     * @param  Tlr\Frb\Config $config
     * @return boolean
     */
    public function hasFortrabbitRemote(Config $config) : bool
    {
        return $this->hasRemote($config->fortrabbitRemoteName());
    }

    /**
     *   BRANCH HELPERS
     */

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

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $this;
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
        // @todo - check if current branch is a valid branch! error if its detached.
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

    /**
     *   DEPLOY HELPERS
     */

    /**
     * Deploy / Push to fortrabbit
     *
     * @param  Tlr\Frb\Config $config
     * @return Tlr\Frb\Tasks\Git
     */
    public function pushToFortrabbit(Config $config) : Git
    {
        $this->progress('Deploying New Release to Fortrabbit', 'FRB Git');

        $process = new Process(sprintf(
            'git push %s %s:%s',
            $config->fortrabbitRemoteName(),
            $config->targetBranch(),
            $config->remoteBranch()
        ));
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $this;
    }

    /**
     * Deploy / Push to fortrabbit for the first time
     *
     * @param  Tlr\Frb\Config $config
     * @return Tlr\Frb\Tasks\Git
     */
    public function firstPushToFortrabbit(Config $config) : Git
    {
        $this->progress('Pushing to Fortrabbit');

        $process = new Process(sprintf(
            'git push -u %s %s:refs/heads/%s',
            $config->fortrabbitRemoteName(),
            $config->targetBranch(),
            $config->remoteBranch()
        ));
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $this;
    }
}
