<?php namespace Dan\Core;


class Console {

    private $text;
    private $color;
    private $backColor;

    /**
     * @var bool
     */
    private $debug = false;

    public function __construct($t)
    {
        $this->text = $t;
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
        if($this->debug && !Config::get('dan.debug'))
            return null;

        if (!file_exists(ROOT_DIR . '/logs')) {
            mkdir(ROOT_DIR . '/logs', 0777, true);
        }

        $log = fopen('logs/' . date('Ymd') . '_' . session_id() . '.log', 'a');
        fwrite($log, '[' . date('r') . '] ' . ($this->debug ? '[DEBUG] ' : '') . $this->text . "\n");
        fclose($log);

        $text = ($this->debug ? ConsoleColor::Purple . "[DEBUG] " : '') . $this->color . $this->text;
        echo $text . ConsoleColor::Reset . "\n";

        return $this->text;
    }

    /**
     * Sets the text.
     *
     * @param $text
     * @return Console
     */
    public static function text($text)
    {
        return new Console($text);
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
}
 