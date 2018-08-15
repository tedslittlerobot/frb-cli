<?php

namespace Tlr\Frb\Tasks\Batch;

use Tlr\Frb\Config;
use Tlr\Frb\Tasks\AbstractTask;
use Tlr\Frb\Tasks\Git;

class CheckGitSetup extends AbstractTask
{
    /**
     * The "section" name for the task.
     *
     * @var string
     */
    protected $section = 'Git Remote Check';

    /**
     * Run the task!
     *
     * @param  Config $config
     * @return void
     */
    public function run(Config $config)
    {
        $git = $this->task(Git::class);

        $this->progress('Checking Fortrabbit Git setup...');

        if ($git->hasFortrabbitRemote($config)) {
            $this->progress('Check passed.');

            return;
        }

        $this->progress('Check failed... Setup now?');

        $this->task(SetupGit::class)->setup($config);
    }
}
