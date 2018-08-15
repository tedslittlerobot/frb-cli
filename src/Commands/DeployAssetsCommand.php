<?php

namespace Tlr\Frb\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tlr\Frb\Commands\AbstractEnvironmentCommand;
use Tlr\Frb\Config;
use Tlr\Frb\Tasks\Build;

class DeployAssetsCommand extends AbstractEnvironmentCommand
{
    /**
     * Command Title
     *
     * @var string
     */
    protected $title = 'Deploy (Assets Only)';

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('deploy:assets')
            ->setDescription('Deploy only the assets of the application.')
            ->addOption(
                'scp-only',
                's',
                InputOption::VALUE_NONE,
                'Do not attempt to build any assets - only push what is currently there.'
            )
            ->addOption(
                'build-only',
                'b',
                InputOption::VALUE_NONE,
                'Only build the assets - do not push them to the server.'
            )
            ->addEnvironmentArgument()
        ;
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function handle(Config $config, InputInterface $input, OutputInterface $output)
    {
        $scpOnly = $input->getOption('scp-only');
        $buildOnly = $input->getOption('build-only');

        if ($scpOnly && $buildOnly) {
            throw new \Exception('You cannot have both "only" flags set at once.');
        }

        $shouldScp   = (!$scpOnly && !$buildOnly) || $scpOnly;
        $shouldBuild = (!$scpOnly && !$buildOnly) || $buildOnly;

        if ($shouldBuild) {
            $this->task(Build::class)->run($config);
        }

        if ($shouldScp) {
            $this->output->writeLn('SCP');
        }
    }
}
