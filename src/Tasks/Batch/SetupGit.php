<?php

namespace Tlr\Frb\Tasks\Batch;

use Tlr\Frb\Config;
use Tlr\Frb\Tasks\AbstractTask;
use Tlr\Frb\Tasks\Git;

class SetupGit extends AbstractTask
{
    /**
     * The "section" name for the task.
     *
     * @var string
     */
    protected $section = 'Setup Fortrabbit Git';

    /**
     * Run the task!
     *
     * @param  Config $config
     * @return void
     */
    public function setup(Config $config)
    {
        $git = $this->task(Git::class);

        $this->progress('Adding remote');

        $git
            ->addEnvironmentRemote($config)
            ->fetch()
        ;

        $this->progress('done.');
    }
}
