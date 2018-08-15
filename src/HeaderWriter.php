<?php

namespace Tlr\Frb;

use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Tlr\Frb\Config;

class HeaderWriter
{
    /**
     * The command instance
     *
     * @var Symfony\Component\Console\Command\Command
     */
    protected $command;

    /**
     * The output interface
     *
     * @var Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    public function __construct(Command $command, OutputInterface $output)
    {
        $this->command = $command;
        $this->output  = $output;
    }

    /**
     * Display the main title
     *
     * @param  string $title
     * @return void
     */
    public function mainTitle(string $title)
    {
        $title = " Fortrabbit CLI: $title ";
        $border = str_pad('', 80, '-');

        $this->output->writeLn($border);
        $this->output->writeLn(
            str_pad(
                str_pad($title, 80 - 20, ' ', STR_PAD_BOTH),
                80,
                ':',
                STR_PAD_BOTH
            )
        );
        $this->output->writeLn($border);
    }


    /**
     * Display the environment title
     *
     * @param  Tlr\Frb\Config $config
     * @return void
     */
    public function envTitle(Config $config)
    {
        $this->output->writeLn('Environment: ' . $config->environment());
        $this->output->writeLn('');
    }
}
