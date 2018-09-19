<?php

namespace Tlr\Frb\Tasks;

use AFM\Rsync\Rsync as Remote;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Tlr\Frb\Config;
use Tlr\Frb\Tasks\AbstractTask;
use Tlr\Frb\Tasks\FrbRemote;

class Rsync extends AbstractTask
{
    /**
     * The "section" name for the task.
     *
     * @var string
     */
    protected $section = 'R-Sync';

    /**
     * The cached rsync instance
     *
     * @var AFM\Rsync\Rsync
     */
    protected $remote;

    public function remote(Config $config) : Remote
    {
        if (!$this->remote) {
            $this->remote = new Remote([
                'ssh' => [
                    'username' => $config->projectName(),
                    'host' => $config->fortrabbitServer(),
                ],
            ]);
        }

        return $this->remote;
    }

    /**
     * Push the given path to the server
     *
     * @param  Config $config
     * @param  string $path
     * @return Tlr\Frb\Tasks\Rsync
     */
    public function pushPath(Config $config, string $path) : Rsync
    {
        $files = new Filesystem;
        $absolutePath = rootPath($path);

        if (!$files->exists($absolutePath)) {
            throw new \Exception(sprintf('Nothing exists at path [%s]', $path));
        }

        return is_dir($absolutePath) ?
            $this->pushDirectory($config, $path) :
            $this->pushFile($config, $path)
        ;
    }

    /**
     * Push the given directory to the server
     *
     * @param  Config $config
     * @param  string $directory
     * @return Tlr\Frb\Tasks\Rsync
     */
    public function pushDirectory(Config $config, string $directory) : Rsync
    {
        $localDir = rootPath($directory);
        $remoteDir = $config->remoteWebRootPath($directory);

        $this->task(FrbRemote::class)->ensureDirectoryExists($config, $remoteDir);

        $this->formatProgress('Pushing up build assets [%s]', $directory);

        $process = $this->runProcess(new Process(sprintf(
            'rsync -avz %s "%s:%s"',
            $localDir,
            $config->sshUrl(),
            dirname($remoteDir)
        )));

        return $this;
    }

    /**
     * Push the given file to the server
     *
     * @param  Config $config
     * @param  string $file
     * @return Tlr\Frb\Tasks\Rsync
     */
    public function pushFile(Config $config, string $file) : Rsync
    {
        $localFile = rootPath($file);
        $targetFile = $config->remoteWebRootPath($file);

        $this->task(FrbRemote::class)->ensureDirectoryExists($config, dirname($targetFile));

        $this->formatProgress('Pushing up build asset [%s]', $file);

        $process = $this->runProcess(new Process(sprintf(
            'rsync -avz %s "%s:%s"',
            $localFile,
            $config->sshUrl(),
            $targetFile
        )));

        return $this;
    }

    /**
     * Pull the given directory from the server
     *
     * @param  Config $config
     * @param  string $directory
     * @param  string $output
     * @return Tlr\Frb\Tasks\Rsync
     */
    public function pullDirectory(Config $config, string $directory, string $output = null) : Rsync
    {
        throw new \Exception('RSync pull is not finished yet');

        $localDir = rootPath($output ?? $directory);
        $remoteDir = $config->remoteWebRootPath($directory);

        $this->formatProgress('Pulling down files from [%s]', $directory);

        $this->runProcess(new Process(sprintf(
            'scp -r "%s:%s" %s',
            $remoteDir,
            $config->sshUrl(),
            $localDir
        )));

        return $this;
    }
}
