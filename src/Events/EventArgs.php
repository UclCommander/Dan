<?php namespace Dan\Events; 


class EventArgs {

    /**
     * @var object[]
     */
    protected $args;

    public function __construct($args)
    {
        $this->args = $args;
    }

    /**
     * @param $var
     * @return null|object[]
     */
    public function __get($var)
    {
        if(isset($this->args[$var]))
            return $this->args[$var];

        return null;
    }
} 