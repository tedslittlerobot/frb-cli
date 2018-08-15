<?php

namespace Tlr\Frb\Tasks\Batch;

use Tlr\Frb\Config;
use Tlr\Frb\Tasks\AbstractTask;
use Tlr\Frb\Tasks\FridayJumper;
use Tlr\Frb\Tasks\Git;

class Deploy extends AbstractTask
{
    /**
     * The "section" name for the task.
     *
     * @var string
     */
    protected $section = 'Deploy';

    /**
     * Run the task!
     *
     * @param  Config $config
     * @return void
     */
    public function run(Config $config)
    {
        $this->task(FridayJumper::class)->run();

        $this->task(Git::class)->stageIsDirty();

        $this->task(Git::class)
            ->ensureStageIsClean()
            ->fetch()
            ->onBranch($config->targetBranch(), function($git, $command, $input, $output) {
                $git->fetch();
                // @todo - do more stuff!
                // - run deploy tasks
                // - push to frb
                // - upload???
                // - run post scripts
            })
        ;
    }
}
