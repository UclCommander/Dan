<?php

namespace Dan\Console;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Throwable;

class Console
{
    /**
     * @var Connection
     */
    protected $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Get the value of a command argument.
     *
     * @param string $key
     *
     * @return string|array
     */
    public function argument($key = null)
    {
        if (is_null($key)) {
            return $this->connection->input->getArguments();
        }

        return $this->connection->input->getArgument($key);
    }

    /**
     * Get the value of a command option.
     *
     * @param string $key
     * @param null   $default
     *
     * @return array|string
     */
    public function option($key = null, $default = null)
    {
        if (is_null($key)) {
            return $this->connection->input->getOptions();
        }

        try {
            return $this->connection->input->getOption($key);
        } catch (InvalidArgumentException $e) {
            return $default;
        }
    }

    /**
     * Confirm a question with the user.
     *
     * @param string $question
     * @param bool   $default
     *
     * @return bool
     */
    public function confirm($question, $default = false)
    {
        return $this->connection->output->confirm($question, $default);
    }

    /**
     * Prompt the user for input.
     *
     * @param string $question
     * @param string $default
     *
     * @return string
     */
    public function ask($question, $default = null)
    {
        return $this->connection->output->ask($question, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param string $question
     * @param array  $choices
     * @param string $default
     *
     * @return string
     */
    public function anticipate($question, array $choices, $default = null)
    {
        return $this->askWithCompletion($question, $choices, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param string $question
     * @param array  $choices
     * @param string $default
     *
     * @return string
     */
    public function askWithCompletion($question, array $choices, $default = null)
    {
        $question = new Question($question, $default);

        $question->setAutocompleterValues($choices);

        return $this->connection->output->askQuestion($question);
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     *
     * @param string $question
     * @param bool   $fallback
     *
     * @return string
     */
    public function secret($question, $fallback = true)
    {
        $question = new Question($question);

        $question->setHidden(true)->setHiddenFallback($fallback);

        return $this->connection->output->askQuestion($question);
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param string $question
     * @param array  $choices
     * @param string $default
     * @param mixed  $attempts
     * @param bool   $multiple
     *
     * @return bool
     */
    public function choice($question, array $choices, $default = null, $attempts = null, $multiple = null)
    {
        $question = new ChoiceQuestion($question, $choices, $default);

        $question->setMaxAttempts($attempts)->setMultiselect($multiple);

        return $this->connection->output->askQuestion($question);
    }

    /**
     * Format input to textual table.
     *
     * @param array                                         $headers
     * @param array|\Illuminate\Contracts\Support\Arrayable $rows
     * @param string                                        $style
     *
     * @return void
     */
    public function table(array $headers, $rows, $style = 'default')
    {
        $table = new Table($this->connection->output);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders($headers)->setRows($rows)->setStyle($style)->render();
    }

    /**
     * Write a string as information output.
     *
     * @param string $string
     *
     * @return void
     */
    public function info($string)
    {
        $this->connection->write("[<cyan>INFO</cyan>] <info>$string</info>");
    }

    /**
     * Write a string as standard output.
     *
     * @param string $string
     *
     * @return void
     */
    public function line($string)
    {
        $this->connection->write($string);
    }

    /**
     * Write a string as comment output.
     *
     * @param string $string
     *
     * @return void
     */
    public function debug($string)
    {
        if (!config('dan.debug')) {
            return;
        }

        $this->connection->write("[<magenta>DEBUG</magenta>] <debug>$string</debug>");
    }

    /**
     * Write a string as comment output.
     *
     * @param string $string
     *
     * @return void
     */
    public function comment($string)
    {
        $this->connection->write("[COMMENT] <comment>$string</comment>");
    }

    /**
     * Write a string as question output.
     *
     * @param string $string
     *
     * @return void
     */
    public function question($string)
    {
        $this->connection->write("[QUEST] <question>$string</question>");
    }

    /**
     * Write a string as error output.
     *
     * @param string $string
     *
     * @return void
     */
    public function error($string)
    {
        $this->connection->write("[<red>ERROR</red>] <error>$string</error>");
    }

    /**
     * Write a string as warning output.
     *
     * @param string $string
     *
     * @return void
     */
    public function warn($string)
    {
        $this->connection->write("[<yellow>WARN</yellow>] <warning>$string</warning>");
    }

    /**
     * Write a string as warning output.
     *
     * @param string $string
     *
     * @return void
     */
    public function success($string)
    {
        $this->connection->write("[<green>OK</green>] <success>$string</success>");
    }

    /**
     * Sends a message.
     *
     * @param $message
     * @param array $styles
     */
    public function message($message, $styles = [])
    {
        $this->connection->write($message);
    }

    /**
     * Sends an action.
     *
     * @param $message
     */
    public function action($message)
    {
        $this->warn($message);
    }

    /**
     * Sends a notice.
     *
     * @param $message
     */
    public function notice($message)
    {
        $this->info($message);
    }

    /**
     * @param Throwable $exception
     */
    public function exception(Throwable $exception)
    {
        $this->error('Exception was thrown.');
        $this->error($exception->getMessage());
        $this->error("On line {$exception->getLine()}");
        $this->error("Of file {$exception->getFile()}");

        if (config('dan.debug')) {
            $this->error('Stack Trace:');
            echo $exception->getTraceAsString();
            echo PHP_EOL;
        }

        events()->fire('console.exception', [
            'exception' => $exception
        ]);
    }
}
