<?php

namespace Tlr\Frb\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tlr\Frb\Commands\AbstractEnvironmentCommand;
use Tlr\Frb\Config;
use Tlr\Frb\Tasks\Scp;

class DownloadDirectoryCommand extends AbstractEnvironmentCommand
{
    /**
     * Command Title
     *
     * @var string
     */
    protected $title = 'Download Directory';

    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('download:directory')
            ->setDescription('Download the given directory from the server.')
            ->addEnvironmentArgument()
            ->addArgument('directory', InputArgument::REQUIRED, 'The directory to pull down.')
            ->addArgument('target', InputArgument::OPTIONAL, 'The destination directory. Leave blank to match the download directory.')
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
        $this
            ->task(Scp::class)
            ->pullDirectory(
                $input->getArgument('directory'),
                $input->getArgument('target')
            )
        ;
    }
}
