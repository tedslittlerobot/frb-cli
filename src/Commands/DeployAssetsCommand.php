<?php

namespace Tlr\Frb\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Tlr\Frb\Commands\AbstractEnvironmentCommand;
use Tlr\Frb\Config;
use Tlr\Frb\Tasks\Batch\Assets;
use Tlr\Frb\Tasks\Notification;

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
                'push-only',
                'p',
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
        $scpOnly = $input->getOption('push-only');
        $buildOnly = $input->getOption('build-only');

        if ($scpOnly && $buildOnly) {
            throw new \Exception('You cannot have both "only" flags set at once.');
        }

        $shouldScp   = (!$scpOnly && !$buildOnly) || $scpOnly;
        $shouldBuild = (!$scpOnly && !$buildOnly) || $buildOnly;

        $assets = $this->task(Assets::class);

        if ($shouldBuild) {
            $assets->build($config);
        }

        if ($shouldScp) {
            $assets->push($config);
        }

        $message = 'Assets Built & Deployed!';

        if ($scpOnly) {
            $message = 'Assets Uploaded!';
        } elseif ($buildOnly) {
            $message = 'Assets Built!';
        }

        $this->task(Notification::class)->success($config, $message);

    }
}
