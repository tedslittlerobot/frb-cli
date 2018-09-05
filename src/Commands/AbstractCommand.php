<?php

namespace Tlr\Frb\Commands;

use Carbon\Carbon;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
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
     * The logger instance
     *
     * @var string
     */
    protected static $logger;

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
     * The log file name
     *
     * @return string
     */
    public function logName() : string
    {
        if ($this->input->hasArgument('environment')) {
            return sprintf(
                '[%s][%s]%s.log',
                $this->input->getArgument('environment'),
                $this->getName(),
                Carbon::now()->toDateTimeString()
            );
        }

        return sprintf(
            '[%s]%s.log',
            $this->getName(),
            Carbon::now()->toDateTimeString()
        );
    }

    /**
     * Build or retrieve from cache a logger
     *
     * @return Monolog\Logger
     */
    public function logger() : Logger
    {
        if (!static::$logger) {
            static::$logger = new Logger('frb');
            static::$logger->pushHandler(new StreamHandler(
                frbEnvPath($this->logName()),
                Logger::INFO
            ));
        }

        return static::$logger;
    }

    /**
     * Build a task class
     *
     * @param  string $class
     * @return Tlr\Frb\Tasks\AbstractTask
     */
    public function task(string $class) : AbstractTask
    {
        return new $class(
            $this,
            $this->input,
            $this->output,
            $this->logger()
        );
    }
}
