<?php namespace Dan\Console;

use Dan\Contracts\MessagingContract;
use Dan\Contracts\SocketContract;
use Dan\Hooks\HookManager;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;

class Console implements SocketContract, MessagingContract {

    /**
     * @var Console
     */
    protected static $self;

    /**
     * @var resource
     */
    protected $stream;

    /**
     * @var string
     */
    protected $attached;

    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    protected $output;

    /**
     * @var \Symfony\Component\Console\Input\ArrayInput
     */
    protected $input;

    /**
     * @var \Illuminate\Support\Collection
     */
    public $config;

    /**
     *
     */
    public function __construct()
    {
        $this->config = new Collection([
            'command_prefix'   => '/',
        ]);

        $this->input    = new ArrayInput([]);
        $this->output   = new OutputStyle($this->input, new ConsoleOutput());

        $this->stream = fopen('php://stdin', 'r');
        stream_set_blocking($this->stream, 0);
    }

    /**
     * @return Console
     */
    public static function factory()
    {
        if(is_null(static::$self))
            static::$self = new static();

        return static::$self;
    }

    /**
     * @return array|string
     */
    public static function arguments()
    {
        return static::factory()->argument();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'console';
    }

    /**
     * @return resource
     */
    public function getStream()
    {
        return $this->stream;
    }

    /**
     * @param resource $resource
     */
    public function handle($resource)
    {
        $message = trim(fgets($resource));

        $hookData = [
            'connection'    => $this,
            'user'          => $this,
            'channel'       => $this,
            'message'       => $message,
            'console'       => true
        ];

        if(HookManager::data($hookData)->call('command'))
            return;
    }


    /**
     * Get the value of a command argument.
     *
     * @param  string  $key
     * @return string|array
     */
    public function argument($key = null)
    {
        if (is_null($key)) {
            return $this->input->getArguments();
        }

        return $this->input->getArgument($key);
    }

    /**
     * Get the value of a command option.
     *
     * @param  string  $key
     * @return string|array
     */
    public function option($key = null)
    {
        if (is_null($key)) {
            return $this->input->getOptions();
        }

        return $this->input->getOption($key);
    }

    /**
     * Confirm a question with the user.
     *
     * @param  string  $question
     * @param  bool    $default
     * @return bool
     */
    public function confirm($question, $default = false)
    {
        return $this->output->confirm($question, $default);
    }

    /**
     * Prompt the user for input.
     *
     * @param  string  $question
     * @param  string  $default
     * @return string
     */
    public function ask($question, $default = null)
    {
        return $this->output->ask($question, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param  string  $question
     * @param  array   $choices
     * @param  string  $default
     * @return string
     */
    public function anticipate($question, array $choices, $default = null)
    {
        return $this->askWithCompletion($question, $choices, $default);
    }

    /**
     * Prompt the user for input with auto completion.
     *
     * @param  string  $question
     * @param  array   $choices
     * @param  string  $default
     * @return string
     */
    public function askWithCompletion($question, array $choices, $default = null)
    {
        $question = new Question($question, $default);

        $question->setAutocompleterValues($choices);

        return $this->output->askQuestion($question);
    }

    /**
     * Prompt the user for input but hide the answer from the console.
     *
     * @param  string  $question
     * @param  bool    $fallback
     * @return string
     */
    public function secret($question, $fallback = true)
    {
        $question = new Question($question);

        $question->setHidden(true)->setHiddenFallback($fallback);

        return $this->output->askQuestion($question);
    }

    /**
     * Give the user a single choice from an array of answers.
     *
     * @param  string  $question
     * @param  array   $choices
     * @param  string  $default
     * @param  mixed   $attempts
     * @param  bool    $multiple
     * @return bool
     */
    public function choice($question, array $choices, $default = null, $attempts = null, $multiple = null)
    {
        $question = new ChoiceQuestion($question, $choices, $default);

        $question->setMaxAttempts($attempts)->setMultiselect($multiple);

        return $this->output->askQuestion($question);
    }

    /**
     * Format input to textual table.
     *
     * @param  array   $headers
     * @param  array|\Illuminate\Contracts\Support\Arrayable  $rows
     * @param  string  $style
     * @return void
     */
    public function table(array $headers, $rows, $style = 'default')
    {
        $table = new Table($this->output);

        if ($rows instanceof Arrayable) {
            $rows = $rows->toArray();
        }

        $table->setHeaders($headers)->setRows($rows)->setStyle($style)->render();
    }

    /**
     * Write a string as information output.
     *
     * @param  string  $string
     * @return void
     */
    public function info($string)
    {
        $this->output->writeln("[<cyan>INFO</cyan>] <info>$string</info>");
    }

    /**
     * Write a string as standard output.
     *
     * @param  string  $string
     * @return void
     */
    public function line($string)
    {
        $this->output->writeln($string);
    }

    /**
     * Write a string as comment output.
     *
     * @param  string  $string
     * @return void
     */
    public function debug($string)
    {
        if(!DEBUG)
            return;

        $this->output->writeln("[<magenta>DEBUG</magenta>] <debug>$string</debug>");
    }

    /**
     * Write a string as comment output.
     *
     * @param  string  $string
     * @return void
     */
    public function comment($string)
    {
        $this->output->writeln("[COMMENT] <comment>$string</comment>");
    }

    /**
     * Write a string as question output.
     *
     * @param  string  $string
     * @return void
     */
    public function question($string)
    {
        $this->output->writeln("[QUEST] <question>$string</question>");
    }

    /**
     * Write a string as error output.
     *
     * @param  string  $string
     * @return void
     */
    public function error($string)
    {
        $this->output->writeln("[<red>ERROR</red>] <error>$string</error>");
    }

    /**
     * Write a string as warning output.
     *
     * @param  string  $string
     * @return void
     */
    public function warn($string)
    {
        $this->output->writeln("[<yellow>WARN</yellow>] <warning>$string</warning>");
    }
    /**
     * Write a string as warning output.
     *
     * @param  string  $string
     * @return void
     */
    public function success($string)
    {
        $this->output->writeln("[<green>OK</green>] <success>$string</success>");
    }

    /**
     * Sends a message.
     *
     * @param $message
     * @param array $styles
     */
    public function message($message, $styles = [])
    {
        $this->output->writeln($message);
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
     * Backwards compatibility.
     *
     * @return string
     */
    public function nick()          { return 'console'; }
    public function getLocation()   { return 'console'; }
}