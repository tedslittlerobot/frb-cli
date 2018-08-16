<?php

namespace Tlr\Frb\Tasks\Batch;

use Tlr\Frb\Config;
use Tlr\Frb\Tasks\AbstractTask;
use Tlr\Frb\Tasks\Batch\Assets;
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
     * @param  boolean $firstTime
     * @return void
     */
    public function deploy(Config $config, bool $firstTime = false)
    {
        $this->task(FridayJumper::class)->run();

        $this->task(Git::class)
            ->ensureStageIsClean()
            ->fetch()
            ->onBranch($config->targetBranch(), function($git, $command, $input, $output) use ($config, $firstTime) {
                $this->task(Assets::class)->build($config);

                $firstTime ?
                    $git->firstPushToFortrabbit($config) :
                    $git->pushToFortrabbit($config)
                ;

                $this->task(Assets::class)->build($config);
            })
        ;
    }

    /**
     * Run the first deploy!
     *
     * @param  Config $config
     * @return void
     */
    public function firstDeploy(Config $config)
    {
        return $this->deploy($config, true);
    }

    /**
     * Touch deploy - only run deploy push.
     *
     * @param  Config $config
     * @return void
     */
    public function touch(Config $config)
    {
        $this->task(FridayJumper::class)->run();

        $this->task(Git::class)
            ->ensureStageIsClean()
            ->fetch()
            ->onBranch($config->targetBranch(), function($git, $command, $input, $output) use ($config) {
                $git->pushToFortrabbit($config);
            })
        ;
    }
}
