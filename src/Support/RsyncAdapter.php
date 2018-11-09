<?php

namespace Tlr\Frb\Support;

use League\Flysystem\Adapter\AbstractAdapter;
use League\Flysystem\Config;
use Symfony\Component\Process\Process;
use Tlr\Frb\Config as FrbConfig;

class RsyncAdapter extends AbstractAdapter
{
    protected $config;

    public function __construct(FrbConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Generate a raw Process for the given command
     *
     * @param  Config $config
     * @param  string $command
     * @return Symfony\Component\Process\Process
     */
    protected function sshProcess(string $command) : Process
    {
        return new Process(sprintf(
            'ssh %s %s',
            $this->config->sshUrl(),
            $command
        ));
    }

    /**
     * Run an ssh task against the server
     *
     * @param  string $command
     * @return Symfony\Component\Process\Process
     */
    public function ssh($command) : Process
    {
        $process = $this->sshProcess($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception(sprintf(
                'Error running command [%s] %s %s',
                $command,
                PHP_EOL,
                (string)$process->getErrorOutput()
            ));
        }

        return $process;
    }

    public function rsyncProcess(string $from, string $to) : Process
    {
        return new Process(sprintf(
            'rsync -avz %s "%s:%s"',
            rootPath($from),
            $this->config->sshUrl(),
            $this->config->remoteWebRootPath($to)
        ));
    }

    public function rsync(string $from, string $to) : Process
    {
        $process = $this->rsyncProcess($from, $to);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \Exception(sprintf(
                'Error running command [%s] %s %s',
                $command,
                PHP_EOL,
                (string)$process->getErrorOutput()
            ));
        }

        return $process;
    }

    /**
     * Set the config.
     *
     * @param array $config
     *
     * @return $this
     */
    public function setConfig(array $config)
    {
        foreach ($this->configurable as $setting) {
            if ( ! isset($config[$setting])) {
                continue;
            }

            $method = 'set' . ucfirst($setting);

            if (method_exists($this, $method)) {
                $this->$method($config[$setting]);
            }
        }

        return $this;
    }

    /**
     * Write a new file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config   Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function write($path, $contents, Config $config)
    {
        $this->rsync($path, $path);

        return ['path' => $path];
    }

    /**
     * Write a new file using a stream.
     *
     * @param string   $path
     * @param resource $resource
     * @param Config   $config   Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function writeStream($path, $resource, Config $config)
    {
        throw new \Exception('Method writeStream() is not implemented.');
    }

    /**
     * Update a file.
     *
     * @param string $path
     * @param string $contents
     * @param Config $config   Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function update($path, $contents, Config $config)
    {
        return $this->write($path, $contents, $config);
    }

    /**
     * Update a file using a stream.
     *
     * @param string   $path
     * @param resource $resource
     * @param Config   $config   Config object
     *
     * @return array|false false on failure file meta data on success
     */
    public function updateStream($path, $resource, Config $config)
    {
        throw new \Exception('Method updateStream() is not implemented.');
    }

    /**
     * Rename a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function rename($path, $newpath)
    {
        throw new \Exception('Method rename() is not implemented.');
    }

    /**
     * Copy a file.
     *
     * @param string $path
     * @param string $newpath
     *
     * @return bool
     */
    public function copy($path, $newpath)
    {
        throw new \Exception('Method copy() is not implemented.');
    }

    /**
     * Delete a file.
     *
     * @param string $path
     *
     * @return bool
     */
    public function delete($path)
    {
        throw new \Exception('Method delete() is not implemented.');
    }

    /**
     * Delete a directory.
     *
     * @param string $dirname
     *
     * @return bool
     */
    public function deleteDir($dirname)
    {
        throw new \Exception('Method deleteDir() is not implemented.');
    }

    /**
     * Create a directory.
     *
     * @param string $dirname directory name
     * @param Config $config
     *
     * @return array|false
     */
    public function createDir($dirname, Config $config)
    {
        throw new \Exception('Method createDir() is not implemented.');
    }

    /**
     * Set the visibility for a file.
     *
     * @param string $path
     * @param string $visibility
     *
     * @return array|false file meta data
     */
    public function setVisibility($path, $visibility)
    {
        throw new \Exception('Method setVisibility() is not implemented.');
    }

    /**
     * Check whether a file exists.
     *
     * @param string $path
     *
     * @return array|bool|null
     */
    public function has($path)
    {
        try {
            $this->ssh('ls ' . $path);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'No such file or directory')) {
                return false;
            }

            throw $e;
        }

        return true;
    }

    /**
     * Read a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function read($path)
    {
        throw new \Exception('Method read() is not implemented.');
    }

    /**
     * Read a file as a stream.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function readStream($path)
    {
        throw new \Exception('Method readStream() is not implemented.');
    }

    /**
     * List contents of a directory.
     *
     * @param string $directory
     * @param bool   $recursive
     *
     * @return array
     */
    public function listContents($directory = '', $recursive = false)
    {
        throw new \Exception('Method listContents() is not implemented.');
    }

    /**
     * Get all the meta data of a file or directory.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMetadata($path)
    {
        throw new \Exception('Method getMetadata() is not implemented.');
    }

    /**
     * Get the size of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getSize($path)
    {
        throw new \Exception('Method getSize() is not implemented.');
    }

    /**
     * Get the mimetype of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getMimetype($path)
    {
        throw new \Exception('Method getMimetype() is not implemented.');
    }

    /**
     * Get the last modified time of a file as a timestamp.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getTimestamp($path)
    {
        throw new \Exception('Method getTimestamp() is not implemented.');
    }

    /**
     * Get the visibility of a file.
     *
     * @param string $path
     *
     * @return array|false
     */
    public function getVisibility($path)
    {
        throw new \Exception('Method getVisibility() is not implemented.');
    }
}
