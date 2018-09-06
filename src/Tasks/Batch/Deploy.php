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
     * @param  Tlr\Frb\Config $config
     * @param  bool $withAssets
     * @return void
     */
    public function deploy(Config $config, bool $withAssets = true)
    {
        $this->task(FridayJumper::class)->run();

        $git = $this->task(Git::class);

        $git->ensureStageIsClean()->fetch();
        $needsRemoteConfig = $git->fortrabbitRemoteNeedsConfiguring($config);

        $git->onBranch($config->targetBranch(), function($git, $command, $input, $output) use ($config, $needsRemoteConfig, $withAssets) {

                if ($withAssets) {
                    $this->task(Assets::class)->build($config);
                }

                // @todo - pre deploy hooks (remote) - maintanence mode

                $needsRemoteConfig ?
                    $git->firstPushToFortrabbit($config) :
                    $git->pushToFortrabbit($config)
                ;

                if ($withAssets) {
                    $this->task(Assets::class)->push($config);
                }

                // @todo - pre deploy hooks (remote) - maintanence mode
            })
        ;
    }

    /**
     * Touch deploy - only run deploy push.
     *
     * @param  Config $config
     * @return void
     */
    public function touch(Config $config)
    {
        $this->deploy($config, false);
    }
}
