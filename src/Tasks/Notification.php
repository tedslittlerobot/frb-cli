<?php

namespace Tlr\Frb\Tasks;

use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Process\Process;
use Tlr\Frb\Config;
use Tlr\Frb\Tasks\AbstractTask;

class Notification extends AbstractTask
{
    /**
     * Generate the command string
     *
     * @param  Tlr\Frb\Config      $config
     * @param  string      $message
     * @param  string|null $status
     * @return string
     */
    public function command(Config $config, string $message, string $status = null) : string
    {
        $command = sprintf(
            '%s -appIcon %s -group \'%s\' -message \'%s\' -title \'Fortrabbit\' -subtitle \'%s\'',
            frbCliPath('resources/terminal-notifier.app/Contents/MacOS/terminal-notifier'),
            frbCliPath('resources/icon-frb.png'),
            $config->projectName(),
            $message,
            $config->projectName()
        );

        if ($status) {
            $command .= sprintf(' -contentImage %s', frbCliPath("resources/status-{$status}.png"));
        }

        if ($config->appUrl()) {
            $command .= sprintf(' -open \'%s\'', $config->appUrl());
        }

        return $command;
    }

    /**
     * Determine if we should attempt to notify
     *
     * @return boolean
     */
    public function isOnOsx() : bool
    {
        return php_uname('s') !== 'Darwin';
    }

    /**
     * Display a success notification
     *
     * @param  Tlr\Frb\Config $config
     * @param  string $message
     * @return Tlr\Frb\Tasks\Notification
     */
    public function success(Config $config, string $message) : Notification
    {
        $process = new Process(sprintf(
            $this->command($config, $message, 'success')
        ));

        $process->run();

        return $this;
    }

    /**
     * Display a failure notification
     *
     * @param  Tlr\Frb\Config $config
     * @param  string $message
     * @return Tlr\Frb\Tasks\Notification
     */
    public function error(Config $config, string $message) : Notification
    {
        $process = new Process(sprintf(
            $this->command($config, $message, 'error')
        ));

        $process->run();

        return $this;
    }
}
