<?php

namespace Tlr\Frb\Tasks;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Tlr\Frb\Tasks\AbstractTask;
use Tlr\Frb\Tasks\FrbRemote;

class Scp extends AbstractTask
{
    /**
     * The "section" name for the task.
     *
     * @var string
     */
    protected $section = 'SCP';

    /**
     * Push the given directory to the server
     *
     * @param  Config $config
     * @param  string $directory
     * @return Tlr\Frb\Tasks\Scp
     */
    public function pushDirectory(Config $config, string $directory) : Scp
    {
        $localDir = $config->root($directory);
        $remoteDir = $config->remoteWebRootPath($directory);

        $this->task(FrbRemote::class)->ensureDirectoryExists($remoteDir);

        $this->formatProgress('Pushing up build assets [%s]', $directory);

        new Process(sprintf(
            'scp -r %s "%s:%s"',
            $localDir,
            $config->sshUrl(),
            $remoteDir
        ));

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $this;
    }

    /**
     * Pull the given directory from the server
     *
     * @param  Config $config
     * @param  string $directory
     * @param  string $output
     * @return Tlr\Frb\Tasks\Scp
     */
    public function pullDirectory(Config $config, string $directory, string $output = null) : Scp
    {
        $localDir = $config->root($output ?? $directory);
        $remoteDir = $config->remoteWebRootPath($directory);

        $this->formatProgress('Pulling down files from [%s]', $directory);

        new Process(sprintf(
            'scp -r "%s:%s" %s',
            $remoteDir,
            $config->sshUrl(),
            $localDir
        ));

        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $this;
    }
}
