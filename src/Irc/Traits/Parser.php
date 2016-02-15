<?php

namespace Dan\Irc\Traits;

trait Parser
{
    protected $modesWithOptions = [
        'j', 'k', 'l', 'v', 'h', 'o', 'a', 'q',
    ];

    /**
     * Parses modes.
     *
     * @param $modes
     * @param $options
     *
     * @return array
     */
    public function parseModes($modes, $options) : array
    {
        $modes = str_split($modes);
        $index = 0;
        $parsed = [];
        $add = true;

        foreach ($modes as $mode) {
            if (in_array($mode, ['+', '-'])) {
                $add = ($mode == '+');
                continue;
            }

            $option = null;

            if (in_array($mode, $this->modesWithOptions)) {
                $option = $options[$index];
                $index++;
            }

            $parsed[] = [
                'mode'      => ($add ? '+' : '-').$mode,
                'option'    => $option,
            ];
        }

        return $parsed;
    }

    /**
     * Parses an IRC line.
     *
     * @param $line
     *
     * @return array
     */
    public function parseLine($line)
    {
        $data = str_split($line);
        $parsed = [];
        $userInfo = [];

        $buffer = '';
        $inString = false;
        $in005 = false;
        $userStr = false;
        $inMode = false;

        for ($i = 0; $i < count($data); $i++) {
            if (count($parsed) > 0) {
                if ($data[$i] == '=' && $parsed[0] == '005') {
                    $in005 = true;
                }

                if ($parsed[0] == 'MODE' || $parsed[0] == '324') {
                    $inMode = true;
                }
            }

            if ($i == 0 && $data[$i] == ':') {
                $userStr = true;
                continue;
            }

            if (($data[$i] == '!' || $data[$i] == '@' || $data[$i] == ' ') && $userStr) {
                $userInfo[] = $buffer;
                $buffer = '';

                if ($data[$i] == ' ') {
                    $userStr = false;
                }

                continue;
            }

            if ($data[$i] == ':' && (!$inString && !$in005 && !$userStr && !$inMode)) {
                $inString = true;
                continue;
            }

            if ($data[$i] == ' ' && !$inString) {
                $parsed[] = $buffer;
                $buffer = '';
                $in005 = false;
                continue;
            }

            $buffer .= $data[$i];
        }

        if ($buffer != '') {
            $parsed[] = trim($buffer);
        }

        $parsed = array_filter($parsed);

        return [
            'command'   => $parsed,
            'from'      => $userInfo,
        ];
    }

    /**
     * @param $data
     *
     * @return array
     */
    public function parse353($data) : array
    {
        $users = explode(' ', $data);
        $list = [];

        foreach ($users as $user) {
            $prefix = null;
            $first = substr($user, 0, 1);

            if (in_array($first, ['+', '%', '@', '&', '~'])) {
                $prefix = $first;
                $user = substr($user, 1);
            }

            $list[$user] = $prefix;
        }

        return $list;
    }
}
