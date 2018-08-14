<?php

namespace Tlr\Frb\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tlr\Frb\Config;

abstract class AbstractEnvironmentCommand extends Command
{
    public function addEnvironmentArgument() : AbstractEnvironmentCommand
    {
        return $this->addArgument('environment', InputArgument::REQUIRED);
    }

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = new Config($input->getArgument('environment'));

        $this->handle($config, $input, $output);
    }

    /**
     * Run the command
     *
     * @param  Config $config
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    abstract protected function handle(Config $config, InputInterface $input, OutputInterface $output);
}
