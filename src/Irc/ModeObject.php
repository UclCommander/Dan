<?php namespace Dan\Irc;


use Dan\Core\Console;
use Illuminate\Support\Collection;

abstract class ModeObject {

    protected $modes;

    protected $prefixMap = [
        '~' => 'q',
        '&' => 'a',
        '@' => 'o',
        '%' => 'h',
        '+' => 'v',
    ];

    public function __construct()
    {
        $this->modes = new Collection();
    }

    /**
     * @return array
     */
    public function modes()
    {
        return $this->modes->toArray();
    }

    /**
     * Sets modes.
     *
     * @param $m
     */
    public function setMode($m)
    {
        $data   = str_split($m);
        $add    = true;

        for($i = 0; $i < count($data); $i++)
        {
            if($data[$i] == '+' || $data[$i] == '-')
            {
                $add = ($add == '+');
                continue;
            }

            if(!$add)
            {
                $this->modes->forget($data[$i]);
                continue;
            }

            $this->modes->put($data[$i], $data[$i]);
        }
    }

    /**
     * Sets a mode by prefix.
     *
     * @param $prefix
     */
    public function setPrefix($prefix)
    {
        $mode = array_key_exists($prefix, $this->prefixMap) ? $this->prefixMap[$prefix] : '';

        if($mode != '')
            $this->setMode("+{$mode}");
    }

    /**
     * Checks to see if this object has the given mode.
     *
     * @param $mode
     * @return bool
     */
    public function hasMode($mode)
    {
        return $this->modes->has($mode);
    }

    /**
     * Checks to see if this object has one of the givin modes.
     *
     * @param $mode
     * @return bool
     */
    public function hasOneOf($mode)
    {
        $modes = str_split($mode);

        foreach($modes as $m)
            if($this->modes->has($m))
                return true;

        return false;
    }

    /**
     * Checks to see if a user has a mode by prefix.
     *
     * @param $prefix
     * @return bool
     */
    public function hasPrifix($prefix)
    {
        $mode = array_key_exists($prefix, $this->prefixMap) ? $this->prefixMap[$prefix] : '';

        return $this->hasMode($mode);
    }
}