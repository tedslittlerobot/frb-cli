<?php

namespace Tlr\Frb;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

class Config
{
    /**
     * The specified environment
     *
     * @var string
     */
    protected $environment;

    /**
     * The project root directory
     *
     * @var string
     */
    protected $root;

    /**
     * The environment file root directory
     *
     * @var string
     */
    protected $envRoot;

    /**
     * The raw config data
     *
     * @var array
     */
    protected $raw;

    public function __construct(string $environment)
    {
        $this->environment = $environment;

        $this->root    = static::findRoot();
        $this->envRoot = sprintf('%s/.deploy', $this->root);
        $this->raw     = static::parseConfig($this->envRoot, $environment);
    }

    /**
     * Find the project root
     *
     * @return string
     */
    public static function findRoot()  : string
    {
        $currentDir = __DIR__;

        while ($currentDir) {
            if ($currentDir === '/') {
                break;
            }

            if (is_dir(sprintf('%s/.deploy', $currentDir))) {
                return $currentDir;
            }

            $currentDir = dirname($currentDir);
        }

        throw new \Exception('Unable to find a root directory with a .deploy folder');
    }

    /**
     * Parse the config for the given root and file
     *
     * @param  string $root
     * @param  string $environment
     * @return array
     */
    public static function parseConfig(string $root, string $environment) : array
    {
        $ymlFile = sprintf('%s/%s.yml', $root, $environment);
        $yamlFile = sprintf('%s/%s.yaml', $root, $environment);

        if (is_file($ymlFile)) {
            return Yaml::parseFile($ymlFile) ?? [];
        }

        if (is_file($yamlFile)) {
            return Yaml::parseFile($yamlFile) ?? [];
        }

        $cursor = (new Finder)
            ->files()
            ->in($root)
            ->name('*.yml')
            ->name('*.yaml')
        ;

        $envs = collect(iterator_to_array($cursor))
            ->values()
            ->map(function(SplFileInfo $file) {
                return explode('.', $file->getRelativePathname())[0];
            })
        ;

        if ($envs->isEmpty()) {
            throw new \Exception('There are no environments set up!');
        }

        throw new \Exception(sprintf(
            'Unable to find environment file [%s]. Available Environments: [%s]',
            $environment,
            $envs->implode(',')
        ));
    }

    /**
     * Get an item from the config
     *
     * @param  string $key
     * @param  string $default
     * @return mixed
     */
    public function get(string $key, $default = null)
    {
        return array_get($this->raw, $key, $default);
    }

    /**
     * Get an item from the config, erroring if nothing is in there
     *
     * @param  string $key
     * @return mixed
     */
    public function getOrError(string $key)
    {
        $default = function() {}; // a function object for strict uniqueness

        $value = array_get($this->raw, $key, $default);

        if ($value === $default) {
            throw new \Error('Unable to find option ' . $key);
        }

        return $value;
    }

    /**
     * Get the project root
     *
     * @return string
     */
    public function root() : string
    {
        return $this->root;
    }

    /**
     * Get the project environment
     *
     * @return string
     */
    public function environment() : string
    {
        return $this->environment;
    }

    /**
     * ================================ OUTPUT ================================
     */

    /**
     * Get the project name in fortrabbit
     *
     * @return string
     */
    public function projectName() : string
    {
        return (string) $this->getOrError('name');
    }

    /**
     * Get local branch to deploy from
     *
     * @return string
     */
    public function targetBranch() : string
    {
        return (string) $this->getOrError('target_branch');
    }

    /**
     * Get the remote fortrabbit branch to deploy to
     *
     * @return string
     */
    public function remoteBranch() : string
    {
        return (string) $this->getOrError('remote_branch');
    }

    /**
     * Get the build commands to run
     *
     * @return array
     */
    public function buildCommands() : array
    {
        return (array) $this->get('build_command', []);
    }

    /**
     * Get the build directories to deploy
     *
     * @return array
     */
    public function buildDirectories() : array
    {
        return (array) $this->get('build_directory', []);
    }

    /**
     * Get the git URL
     *
     * @return string
     */
    public function gitUrl() : string
    {
        return sprintf(
            '%s@%s:%s.git',
            $this->projectName(),
            $this->getOrError('frb_zone'),
            $this->projectName()
        );
    }

    /**
     * Get the ssh URL
     *
     * @return string
     */
    public function sshUrl() : string
    {
        return sprintf(
            '%s@%s',
            $this->projectName(),
            $this->getOrError('frb_zone')
        );
    }

    /**
     * Get the fortrabbit remote name
     *
     * @return string
     */
    public function fortrabbitRemoteName() : string
    {
        return 'frb-' . $this->environment();
    }
}
