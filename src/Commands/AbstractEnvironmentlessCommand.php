<?php

namespace Tlr\Frb\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tlr\Frb\Commands\AbstractCommand;

abstract class AbstractEnvironmentlessCommand extends AbstractCommand
{
    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        parent::execute($input, $output);

        $this->handle($input, $output);
    }

    /**
     * Run the command
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    abstract protected function handle(InputInterface $input, OutputInterface $output);
}
