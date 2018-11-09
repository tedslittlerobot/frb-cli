<?php

namespace Tlr\Frb\Tasks;

use League\Flysystem\Adapter\CanOverwriteFiles;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Tlr\Frb\Config;
use Tlr\Frb\Tasks\AbstractTask;

class FrbRemote extends AbstractTask implements CanOverwriteFiles
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
     * Run a generic command against the server
     *
     * @param  Config $config
     * @return Tlr\Frb\Tasks\FrbRemote
     */
    public function run(Config $config, string $command) : FrbRemote
    {
        $this->progress('Running Command against the remote...');

        $process = $this->runProcess($this->sshProcess($config, $command));

        $result = trim($process->getOutput());

        if (!$result) {
            $this->progress('Success.');
        } else {
            $this->progress('Output:');

            $this->output->writeLn($result);
        }

        return $this;
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

        $process = $this->runProcess($this->sshProcess($config, 'reset'));

        $this->progress('Done.');

        return $this;
    }

    /**
     * Make sure the given directory exists on the server
     *
     * @param  Config $config
     * @param  string $directory
     * @return Tlr\Frb\Tasks\FrbRemote
     */
    public function ensureDirectoryExists(Config $config, string $directory) : FrbRemote
    {
        $this->formatProgress('Checking for directory [%s]', $directory);

        $process = $this->runProcess($this->sshProcess($config, 'mkdir -p ' . $directory));

        return $this;
    }
}
