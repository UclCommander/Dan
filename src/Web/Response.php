<?php namespace Dan\Web;


class Response
{

    /**
     * @var mixed
     */
    protected $message;

    /**
     * @var int
     */
    protected $code;

    public function __construct($message = null, $code = 200)
    {
        $this->message = $message;
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }
}