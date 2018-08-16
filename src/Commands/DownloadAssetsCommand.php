<?php

namespace Tlr\Frb\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tlr\Frb\Commands\AbstractEnvironmentCommand;
use Tlr\Frb\Config;
use Tlr\Frb\Tasks\Batch\Assets;

class DownloadDirectoryCommand extends AbstractEnvironmentCommand
{
    /**
     * Command Title
     *
     * @var string
     */
    protected $title = 'Download Assets';

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('download:assets')
            ->setDescription('Download the given directory from the server.')
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
        $this->task(Assets::class)->download($config);
    }
}
