<?php

namespace Tlr\Frb;

use Illuminate\Support\Collection;
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
     * The raw config data
     *
     * @var array
     */
    protected $raw;

    public function __construct(string $environment)
    {
        $this->environment = $environment;

        $this->raw = static::parseConfig(frbEnvPath(), $environment);
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
     * Get the server name in fortrabbit
     *
     * @return string
     */
    public function fortrabbitServer() : string
    {
        return (string) $this->getOrError('frb_zone');
    }

    /**
     * Get the server name in fortrabbit
     *
     * @return string
     */
    public function appUrl()
    {
        return $this->get('url');
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
     * @return Illuminate\Support\Collection
     */
    public function buildCommands() : Collection
    {
        return collect((array) $this->get('build_commands', []))
            ->map(function($command) {
                if (is_array($command)) {
                    return $command;
                }

                return [
                    'run' => $command,
                    'in' => null,
                ];
            });
    }

    /**
     * Get the build files to deploy
     *
     * @return Illuminate\Support\Collection
     */
    public function buildOutputs() : Collection
    {
        return collect((array) $this->get('build_output', []));
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
            $this->fortrabbitServer(),
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
            $this->fortrabbitServer()
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

    /**
     * Get the remote web root path
     *
     * @return string
     */
    public function remoteWebRootPath(string $path = null) : string
    {
        $root = sprintf('/srv/app/%s/htdocs', $this->projectName());

        return $path ? "$root/$path" : $root;
    }
}
