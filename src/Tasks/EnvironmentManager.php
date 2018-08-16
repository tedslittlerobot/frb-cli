<?php

namespace Tlr\Frb\Tasks;

use Symfony\Component\Filesystem\Filesystem;
use Tlr\Frb\Tasks\AbstractTask;

class EnvironmentManager extends AbstractTask
{
    /**
     * The "section" name for the task.
     *
     * @var string
     */
    protected $section = 'Environment';

    /**
     * Create the env directory
     *
     * @param  string $root
     * @return string
     */
    public function createEnvDirectory() : string
    {
        $files = new Filesystem;
        $envPath = runPath('.deploy');

        $files->mkdir($env);

        $this->copySampleFileToEnv('.gitignore');

        return $envPath;
    }

    /**
     * Copy the sample file to the env directory
     *
     * @param  string      $file
     * @param  string|null $name
     * @return Tlr\Frb\Tasks\EnvironmentManager
     */
    public function copySampleFileToEnv(string $file, string $name = null) : EnvironmentManager
    {
        $files = new Filesystem;
        $filename = $name ?? $file;

        $files->copy(frbCliPath("fragments/$file"), frbEnvPath($filename));

        return $this;
    }
}
