<?php

namespace Tlr\Frb\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tlr\Frb\Config;
use Tlr\Frb\HeaderWriter;

abstract class AbstractEnvironmentCommand extends Command
{
    /**
     * The command title
     *
     * @var string
     */
    protected $title = '';

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
        $headers = new HeaderWriter($this, $output);

        $headers->mainTitle($this->title);

        $config = new Config($input->getArgument('environment'));

        $headers->envTitle($config);

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
