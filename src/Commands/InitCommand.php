<?php

namespace Tlr\Frb\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tlr\Frb\Commands\AbstractEnvironmentlessCommand;
use Tlr\Frb\Tasks\EnvironmentManager;

class InitCommand extends AbstractEnvironmentlessCommand
{
    /**
     * Command Title
     *
     * @var string
     */
    protected $title = 'Initialise Fortrabbit';

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Initialise the project for frb deployments.')
            ->addArgument('envs', InputArgument::IS_ARRAY, 'Environments to set up.')
        ;
    }

    /**
     * Run the command
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function handle(InputInterface $input, OutputInterface $output)
    {
        $envs = $this->task(EnvironmentManager::class);
        $envs->createEnvDirectory();

        foreach ($input->getArgument('envs') as $environment) {
            $envs->copySampleFileToEnv('sample.yml', "{$environment}.yml");
        }
    }
}
