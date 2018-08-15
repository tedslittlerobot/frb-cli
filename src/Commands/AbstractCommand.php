<?php

namespace Tlr\Frb\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tlr\Frb\HeaderWriter;
use Tlr\Frb\Tasks\AbstractTask;

abstract class AbstractCommand extends Command
{
    /**
     * The command title
     *
     * @var string
     */
    protected $title = '';

    /**
     * Execute the command.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input  = $input;
        $this->output = $output;

        $headers = new HeaderWriter($this, $output);
        $headers->mainTitle($this->title);
    }

    /**
     * Build a task class
     *
     * @param  string $class
     * @return Tlr\Frb\Tasks\AbstractTask
     */
    public function task(string $class) : AbstractTask
    {
        return new $class($this, $this->input, $this->output);
    }
}
