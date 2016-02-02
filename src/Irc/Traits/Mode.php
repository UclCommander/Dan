<?php

namespace Dan\Irc\Traits;

use Illuminate\Support\Collection;

trait Mode
{
    protected $modes;

    protected $prefixMap = [
        '~' => 'q',
        '&' => 'a',
        '@' => 'o',
        '%' => 'h',
        '+' => 'v',
    ];

    /**
     * @return array
     */
    public function modes() : array
    {
        return array_values($this->modes);
    }

    /**
     * Sets modes.
     *
     * @param $m
     */
    public function setMode($m)
    {
        if ($m instanceof self) {
            $data = $m->modes();
        } elseif ($m instanceof Collection) {
            $data = $m->toArray();
        } else {
            $data = str_split($m);
        }

        $add = true;

        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i] == '+' || $data[$i] == '-') {
                $add = ($data[$i] == '+');
                continue;
            }

            if (!$add) {
                unset($this->modes[$data[$i]]);
                continue;
            }

            $this->modes[$data[$i]] = $data[$i];
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

        if ($mode != '') {
            $this->setMode("+{$mode}");
        }
    }

    /**
     * Checks to see if this object has the given mode.
     *
     * @param $mode
     *
     * @return bool
     */
    public function hasMode($mode) : bool
    {
        return array_key_exists($mode, $this->modes);
    }

    /**
     * Checks to see if this object has one of the givin modes.
     *
     * @param $mode
     *
     * @return bool
     */
    public function hasOneOf($mode) : bool
    {
        $modes = str_split($mode);

        foreach ($modes as $m) {
            if ($this->hasMode($m)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks to see if a user has a mode by prefix.
     *
     * @param $prefix
     *
     * @return bool
     */
    public function hasPrefix($prefix) : bool
    {
        $mode = array_key_exists($prefix, $this->prefixMap) ? $this->prefixMap[$prefix] : '';

        return $this->hasMode($mode);
    }
}
