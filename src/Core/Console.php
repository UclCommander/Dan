<?php namespace Dan\Core;


use Illuminate\Filesystem\Filesystem;

class Console {

    private $text;
    private $color;
    private $backColor;

    /** @var bool $critical */
    protected $critical = false;

    /** @var bool $debug */
    private $debug = false;

    protected $filesystem;


    /**
     * Sets the text.
     *
     * @param $text
     * @return Console
     */
    public static function text($text)
    {
        return new static($text);
    }


    public function __construct($text)
    {
        $this->text = $text;
        $this->filesystem = new Filesystem();
    }

    /**
     * Sets the message as a SUCCESS message.
     *
     * @return $this
     */
    public function success()
    {
        $this->color = ConsoleColor::Green;
        $this->text = "[SUCCESS] {$this->text}";
        return $this;
    }

    /**
     * Sets the message as an INFO message.
     *
     * @return $this
     */
    public function info()
    {
        $this->color = ConsoleColor::Blue;
        $this->text = "[INFO] {$this->text}";
        return $this;
    }

    /**
     * Sets the message as an ALERT message.
     *
     * @return $this
     */
    public function alert()
    {
        $this->color = ConsoleColor::Yellow;
        $this->text = "[ALERT] {$this->text}";
        return $this;
    }

    /**
     * Sets the message as a WARNING message.
     *
     * @return $this
     */
    public function warning()
    {
        $this->color = ConsoleColor::Red;
        $this->text = "[WARNING] {$this->text}";
        return $this;
    }

    /**
     * Sets the message as a DANGER message.
     *
     * @return $this
     */
    public function danger()
    {
        $this->backColor = ConsoleColor::BackMagenta;
        $this->color = ConsoleColor::Red;
        $this->text = "[DANGER] {$this->text}";
        return $this;
    }

    /**
     * Sets the message as a CRITICAL message.
     *
     * @return $this
     */
    public function critical()
    {
        $this->color = ConsoleColor::Red;
        $this->text = "[CRITICAL] {$this->text}";
        $this->critical = true;
        return $this;
    }

    /**
     * Sends a debug message ONLY if dan.debug is true.
     *
     * @return $this
     */
    public function debug()
    {
        $this->debug = true;
        return $this;
    }

    /**
     * Sets the text color.
     *
     * @param $color
     * @return $this
     */
    public function color($color)
    {
        $this->color = $color;
        return $this;
    }

    /**
     * Sets the text back color.
     *
     * @param $color
     * @return $this
     */
    public function backColor($color)
    {
        $this->backColor = $color;
        return $this;
    }

    /**
     * Pushes the message.
     */
    public function push()
    {
        $this->logToFile('[' . date('r') . '] ' . ($this->debug ? '[DEBUG] ' : '') . $this->text);

        if($this->debug && !Config::get('dan.debug'))
            return null;

        $text = ($this->debug ? ConsoleColor::Purple . "[DEBUG] " : '') . $this->color . $this->text;
        echo $text . ConsoleColor::Reset . "\n";

        if($this->critical)
            die;

        return $this->text;
    }

    public function logToFile($text)
    {
        if(!$this->filesystem->exists(ROOT_DIR . '/logs'))
            $this->filesystem->makeDirectory(ROOT_DIR . '/logs');

        $this->filesystem->append('logs/' . date('Ymd') . ($this->debug ? '_debug' : null) . '.log', "{$text}\n");
    }

    /**
     * Throws an exception
     *
     * @param \Exception $exception
     * @return \Dan\Core\Console
     */
    public static function exception(\Exception $exception)
    {
        $console = new Console($exception->getMessage());
        return $console->warning();
    }

    /**
     * Opens session in log
     */
    public static function open()
    {
        $console = new static('');
        $console->logToFile("----\n---- SESSION START\n-----");
        $console->debug()->logToFile("----\n---- SESSION START\n-----");

        unset($console);
    }
}
 