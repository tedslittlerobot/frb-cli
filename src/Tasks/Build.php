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
    public function run(Config $config) : Build
    {
        $commands = $config->buildCommands();

        if ($commands->isEmpty()) {
            return;
        }

        $this->formatProgress('Running Build Commands (%s)', $commands->count());

        $commands->each(function($command, $index) use ($commands) {
            if (is_numeric($index)) {
                $this->formatProgress(
                    'Running Build Command [%s/%s]',
                    ($index + 1),
                    $commands->count()
                );
            } else {
                $this->formatProgress(
                    'Running Build Command [%s of %s]',
                    $index,
                    $commands->count()
                );
            }

            $process = new Process($command);

            $process->run();

            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
        });

        return $this;
    }
}
