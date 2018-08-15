<?php

namespace Tlr\Frb\Tasks;

use Carbon\Carbon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Tlr\Frb\Tasks\AbstractTask;

class FridayJumper extends AbstractTask
{
    /**
     * Friday Jump ASCII Graphic - taken from
     * https://gist.github.com/exAspArk/4f18795bc89b6e2666ee
     *
     * @var string
     */
    const JUMP = '
┓┏┓┏┓┃
┛┗┛┗┛┃⟍ ○⟋
┓┏┓┏┓┃  ∕       Friday
┛┗┛┗┛┃ノ)
┓┏┓┏┓┃          deploy,
┛┗┛┗┛┃
┓┏┓┏┓┃          good
┛┗┛┗┛┃
┓┏┓┏┓┃          luck!
┃┃┃┃┃┃
┻┻┻┻┻┻
    ';

    /**
     * Determine if it is Friday
     *
     * @return boolean
     */
    public function isFriday() : bool
    {
        return !Carbon::now()->isFriday();
    }

    /**
     * Jump if it's friday!
     *
     * @param  OutputInterface $output
     * @return void
     */
    public function run()
    {
        if (!$this->isFriday()) {
            return;
        }

        $this->confirm($this->input, $this->output, $this->command->getHelper('question'));

        $this->jump($this->output);
    }

    /**
     * Jump!
     *
     * @param  OutputInterface $output
     * @return void
     */
    public function jump(OutputInterface $output)
    {
        $output->writeln('');
        $output->writeln('');

        $output->writeln(
            trim(static::JUMP)
        );

        $output->writeln('');
        $output->writeln('');
    }

    /**
     * Confirm that a user wants to deploy on a Friday
     *
     * @param  InputInterface  $input
     * @param  OutputInterface $output
     * @param  QuestionHelper  $helper
     * @return void
     */
    public function confirm(InputInterface $input, OutputInterface $output, QuestionHelper $helper)
    {
        $question = new ConfirmationQuestion(
            'Are you sure you want to deploy on a Friday?' .
            PHP_EOL .
            '(y/n) > ',
            false,
            '/^(y|j)/i'
        );

        if ($helper->ask($input, $output, $question)) {
            return;
        }

        throw new \Exception('Aborting deploy... probably a wise choice.');
    }
}
