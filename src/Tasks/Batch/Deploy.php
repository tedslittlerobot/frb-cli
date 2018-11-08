<?php

namespace Tlr\Frb\Tasks\Batch;

use Illuminate\Support\Collection;
use Symfony\Component\Process\Process;
use Tlr\Frb\Config;
use Tlr\Frb\Tasks\AbstractTask;
use Tlr\Frb\Tasks\Batch\Assets;
use Tlr\Frb\Tasks\FrbRemote;
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
                    $this
                        ->task(Assets::class)
                        ->build($config)
                        ->push($config)
                    ;
                }

                if ($config->beforeCommands()->isNotEmpty()) {
                    $this->formatProgress('Running [before] hooks [%s]', $config->beforeCommands()->count());

                    $this->runHookSet($config, $config->beforeCommands());
                }

                $needsRemoteConfig ?
                    $git->firstPushToFortrabbit($config) :
                    $git->pushToFortrabbit($config)
                ;

                if ($config->afterCommands()->isNotEmpty()) {
                    $this->formatProgress('Running [after] hooks [%s]', $config->afterCommands());

                    $this->runHookSet($config, $config->afterCommands());
                }
            })
        ;
    }

    /**
     * Run a set of hooks
     *
     * @param  Config     $config
     * @param  Collection $commands
     * @return void
     */
    public function runHookSet(Config $config, Collection $commands)
    {
        $commands->each(function($command, $index) use ($config, $commands) {
            $target = $command['on'];
            $task = $command['run'];

            $this->formatProgress(
                'Hook [%s / %s] [%s]: [%s]',
                ($index + 1),
                $commands->count(),
                $target,
                $task
            );

            if ($target === 'remote') {
                $this->task(FrbRemote::class)->run($config, $task);
            } else {
                $this->runProcess(new Process(
                    $command['run'],
                    rootPath()
                ));
            }
        });
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
