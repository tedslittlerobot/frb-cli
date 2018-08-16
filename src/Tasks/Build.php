<?php

namespace Tlr\Frb\Tasks;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Tlr\Frb\Config;
use Tlr\Frb\Tasks\AbstractTask;

class Build extends AbstractTask
{
    /**
     * The "section" name for the task.
     *
     * @var string
     */
    protected $section = 'Build';

    /**
     * Run all the build commands
     *
     * @param  Config $config
     * @return Tlr\Frb\Tasks\Build
     */
    public function build(Config $config, string $command) : Build
    {
        $this->formatProgress('Running Build Command [%s]', $command);

        $process = new Process($command);

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $this;
    }
}
