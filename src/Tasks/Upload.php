<?php

namespace Tlr\Frb\Tasks;

use Aws\S3\S3Client;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem as Flysystem;
use League\Flysystem\Sftp\SftpAdapter;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Tlr\Frb\Config;
use Tlr\Frb\Support\BatchFileToIndividualFileWrapper;
use Tlr\Frb\Support\RsyncAdapter;
use Tlr\Frb\Support\S3BatchFileAdapter;
use Tlr\Frb\Tasks\AbstractTask;
use Tlr\Frb\Tasks\FrbRemote;

class Upload extends AbstractTask
{
    /**
     * The "section" name for the task.
     *
     * @var string
     */
    protected $section = 'File Uploader';

    /**
     * The Flysystem disk
     *
     * @var League\Flysystem\Filesystem
     */
    protected $disk;

    public function disk(Config $config) : Flysystem
    {
        if ($this->disk) {
            return $this->disk;
        }

        return $this->disk = new Flysystem(
            $config->professional() ?
                $this->s3Adapter($config) :
                $this->sftpAdapter($config)
        );
    }

    public function sftpAdapter(Config $config)
    {
        return new RsyncAdapter($config);
    }

    public function s3Adapter(Config $config)
    {
        $process = $this->runProcess($this->sshProcess($config, 'cat /etc/secrets.json'));
        $secrets = json_decode(trim($process->getOutput()));
        $storage = $secrets->OBJECT_STORAGE;

        $credentials = [
            'credentials' => [
                'key'      => $storage->KEY,
                'secret'   => $storage->SECRET,
            ],
            'region'   => $storage->REGION,
            'bucket'   => $storage->BUCKET,
            'endpoint' => 'https://'. $storage->SERVER,
            'version'  => 'latest',
        ];

        $client = S3Client::factory($credentials);

        return new S3BatchFileAdapter($client, $credentials['bucket'], $config->assetRoot());
    }

    /**
     * Push the given path to the server
     *
     * @param  Config $config
     * @param  string $path
     * @return Tlr\Frb\Tasks\Upload
     */
    public function push(Config $config, string $path) : Upload
    {
        $this->formatProgress('Pushing [%s]', $path);

        $files = new Filesystem;
        $absolutePath = rootPath($path);

        if (!$files->exists($absolutePath)) {
            throw new \Exception(sprintf('Nothing exists at path [%s]', $path));
        }

        return $this->pushFile($config, '', $path);
    }

    /**
     * Push the given file to the server
     *
     * @param  Config $config
     * @param  string $file
     * @return Tlr\Frb\Tasks\Upload
     */
    public function pushFile(Config $config, string $file, string $path) : Upload
    {
        $localPath = rootPath(path_fragments($path, $file));
        $remotePath = path_fragments(
            $config->localAssetRoot() ?
                str_replace($config->localAssetRoot(), '', $path) :
                $path
            ,
            $file
        );

        $fileConfig = [
            'path' => $path,
            'local-path' => $localPath,
            'remote-path' => $remotePath,
        ];

        $this->disk($config)->put(
            $remotePath,
            file_get_contents($localPath),
            $fileConfig
        );

        return $this;
    }
}
