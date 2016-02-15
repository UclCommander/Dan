<?php

namespace Dan\Irc\Traits;

trait Mode
{
    /**
     * @var array
     */
    protected $modes = [];

    /**
     * @var array
     */
    protected $permissions = [
        'kick'  => 'hoaq',
        'ban'   => 'hoaq',
        'op'    => 'oaq',
        'voice' => 'hoaq',
    ];

    /**
     * @var array
     */
    protected $prefixMap = [
        '~' => 'q',
        '&' => 'a',
        '@' => 'o',
        '%' => 'h',
        '+' => 'v',
    ];

    /**
     * Gets all set modes on the object.
     *
     * @return array
     */
    public function modes() : array
    {
        return $this->modes;
    }

    /**
     * Returns a mode option.
     *
     * @param $mode
     *
     * @return mixed
     */
    public function option($mode)
    {
        return $this->modes[$mode];
    }

    /**
     * Sets a single mode.
     *
     * @param $mode
     * @param null $option
     *
     * @throws \Exception
     */
    public function setMode($mode, $option = null)
    {
        if (strlen($mode) > 2) {
            throw new \Exception("Mode {$mode} is too long. Did you mean to use setModes()?");
        }

        if (strpos($mode, '-') === 0) {
            unset($this->modes[substr($mode, 1)]);

            return;
        }

        $this->modes[substr($mode, 1)] = $option;
    }

    /**
     * Sets modes. Must be in array format with mode and option keys.
     *
     * @param $modes
     */
    public function setModes($modes)
    {
        foreach ($modes as $mode) {
            $this->setMode($mode['mode'], $mode['option']);
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
     * Checks to see if this object has one of the given modes.
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

    /**
     * Checks to see if the location has permission to do a predefined action.
     *
     * @param $do
     *
     * @return bool
     */
    public function hasPermissionTo($do) : bool
    {
        if (array_key_exists($do, $this->permissions)) {
            return $this->hasOneOf($this->permissions[$do]);
        }

        return false;
    }
}
