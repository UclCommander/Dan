<?php namespace Dan\Core;


class Console {
    private $text;
    private $color;
    private $backColor;

    public function __construct($t)
    {
        $this->text = $t;
    }

    /**
     * @return $this
     */
    public function success()
    {
        $this->color = ConsoleColor::Green;
        return $this;
    }
    /**
     * @return $this
     */
    public function info()
    {
        $this->color = ConsoleColor::Blue;
        return $this;
    }

    /**
     * @return $this
     */
    public function alert()
    {
        $this->color = ConsoleColor::Yellow;
        return $this;
    }

    /**
     * @return $this
     */
    public function warning()
    {
        $this->color = ConsoleColor::Red;
        return $this;
    }

    /**
     * @return $this
     */
    public function danger()
    {
        $this->backColor = ConsoleColor::BackMagenta;
        $this->color = ConsoleColor::Red;
        return $this;
    }

    /**
     * @param $color
     * @return $this
     */
    public function color($color)
    {
        $this->color = $color;
        return $this;
    }

    /**
     * @param $color
     * @return $this
     */
    public function backColor($color)
    {
        $this->backColor = $color;
        return $this;
    }

    /**
     *
     */
    public function push()
    {
        if (!file_exists(ROOT_DIR . '/logs')) {
            mkdir(ROOT_DIR . '/logs', 0777, true);
        }

        $log = fopen('logs/' . date('Ymd') . '_' . session_id() . '.log', 'a');
        fwrite($log, '[' . date('r') . '] ' . $this->text . "\n");
        fclose($log);

        echo $this->color . $this->text . ConsoleColor::Reset . "\n";
        unset($this);
        return $this->text;
    }

    /**
     * @param $text
     * @return Console
     */
    public static function text($text)
    {
        return new Console($text);
    }
}
 