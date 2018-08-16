<?php

namespace Tlr\Frb\Tasks\Batch;

use Tlr\Frb\Config;
use Tlr\Frb\Tasks\AbstractTask;
use Tlr\Frb\Tasks\Build;
use Tlr\Frb\Tasks\Scp;

class Assets extends AbstractTask
{
    /**
     * The "section" name for the task.
     *
     * @var string
     */
    protected $section = 'Assets';

    /**
     * Build all assets!
     *
     * @param  Config $config
     * @return void
     */
    public function build(Config $config)
    {
        $builder = $this->task(Build::class);
        $commands = $config->buildCommands();

        if ($commands->isEmpty()) {
            return;
        }

        $this->formatProgress('Running Build Commands [%s]', $commands->count());

        $commands->each(function($command) use ($builder, $config) {
            $builder->build($config, $command);
        });

        return $this;
    }

    /**
     * Push all assets!
     *
     * @param  Config $config
     * @return void
     */
    public function push(Config $config)
    {
        $scp = $this->task(Scp::class);
        $directories = $config->buildDirectories();

        if ($directories->isEmpty()) {
            return;
        }

        $this->formatProgress('Pushing Directories [%s]', $directories->count());

        $directories->each(function($directory) use ($scp, $config) {
            $scp->pushDirectory($config, $directory);
        });

        return $this;
    }
}
