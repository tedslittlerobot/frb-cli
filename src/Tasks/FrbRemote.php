<?php

namespace Tlr\Frb\Tasks;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Tlr\Frb\Config;
use Tlr\Frb\Tasks\AbstractTask;

class FrbRemote extends AbstractTask
{
    /**
     * The "section" name for the task.
     *
     * Should be overridden
     *
     * @var string
     */
    protected $section = 'Fortrabbit Remote';

    /**
     * Generate a raw Process for the given command
     *
     * @param  Config $config
     * @param  string $command
     * @return Symfony\Component\Process\Process
     */
    protected function sshProcess(Config $config, string $command) : Process {
        return new Process(sprintf(
            'ssh % %s',
            $config->sshUrl(),
            $command
        ));
    }

    /**
     * Run a reset command
     *
     * @param  Config $config
     * @return Tlr\Frb\Tasks\FrbRemote
     */
    public function reset(Config $config) : FrbRemote
    {
        $this->progress('Running Reset Command...');

        $process = $this->sshProcess('reset');

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $this->progress('Done.');

        return $this;
    }
}
