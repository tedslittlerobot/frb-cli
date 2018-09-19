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
        $stage = $this
            ->runProcess(new Process('git status --porcelain'))
            ->getOutput()
        ;

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
     * @return Tlr\Frb\Tasks\Git
     */
    public function ensureStageIsClean() : Git
    {
        if ($this->stageIsDirty()) {
            throw new \Exception('The Git Staging area has uncommitted files. Clean up your current branch, then try again.');
        }

        return $this;
    }

    /**
     * Check if the fortrabbit remote has already been pushed to
     *
     * @return bool
     */
    public function fortrabbitRemoteNeedsConfiguring(Config $config) : bool
    {
        $process = $this->runProcess(new Process('git branch -a'));

        $remoteBranchExists = collect(explode(PHP_EOL, $process->getOutput()))
            ->map(function($branch) {
                return trim($branch);
            })
            ->first(function($branch) use ($config) {
                $target = sprintf('remotes/%s/%s', $config->fortrabbitRemoteName(), $config->remoteBranch());

                return starts_with($branch, $target);
            })
        ;

        return !$remoteBranchExists;
    }

    /**
     *   REMOTE HELPERS
     */

    /**
     * Run a git fetch command
     *
     * @param  boolean $prune
     * @return Tlr\Frb\Tasks\Git
     */
    public function fetch(string $branch = '--all', bool $prune = false)
    {
        $this->progress('Fetching latest code.');

        $command = 'git fetch ' . $branch;

        if ($prune) {
            $command .= ' --prune';
        }

        $this->runProcess(new Process($command));

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

        $process = $this->runProcess(new Process(sprintf(
            'git remote add %s %s',
            $name,
            $url
        )));

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
        $process = $this->runProcess(new Process('git remote'));

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

        $process = $this->runProcess(new Process('git checkout ' . $branch));

        return $this;
    }

    /**
     * Get the current branch
     *
     * @return string
     */
    public function getCurrentBranch() : string
    {
        $process = $this->runProcess(new Process('git branch'));

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

        $process = $this->runProcess(
            (new Process(sprintf(
                'git push %s %s:%s',
                $config->fortrabbitRemoteName(),
                $config->targetBranch(),
                $config->remoteBranch()
            )))->setTimeout(60 * 15) // 15 mins
        );

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

        $process = $this->runProcess(
            (new Process(sprintf(
                'git push -u %s %s:refs/heads/%s',
                $config->fortrabbitRemoteName(),
                $config->targetBranch(),
                $config->remoteBranch()
            )))->setTimeout(60 * 15) // 15 mins
        );

        return $this;
    }
}
