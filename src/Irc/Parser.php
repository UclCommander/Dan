<?php namespace Dan\Irc; 


class Parser {

    /**
     * Parses IRC lines to make them easy to use.
     *
     * @param $line
     * @return array
     */
    public static function parseLine($line)
    {
        $data      = str_split($line);
        $parsed     = [];
        $userInfo   = [];

        $buffer     = "";
        $inString   = false;
        $in005      = false;
        $userStr    = false;

        for($i = 0; $i < count($data); $i++)
        {
            if(count($parsed) > 0)
                if($data[$i] == '=' && $parsed[0] == '005')
                    $in005 = true;

            if($i == 0 && $data[$i] == ':')
            {
                $userStr = true;
                continue;
            }

            if(($data[$i] == '!' || $data[$i] == '@' || $data[$i] == ' ') && $userStr)
            {
                $userInfo[] = $buffer;
                $buffer = '';

                if($data[$i] == ' ')
                    $userStr = false;

                continue;
            }

            if ($data[$i] == ':' && (!$inString && !$in005))
            {
                $inString = true;
                continue;
            }

            if ($data[$i] == ' ' && !$inString)
            {
                $parsed[]   = $buffer;
                $buffer     = "";
                $in005      = false;
                continue;
            }

            $buffer .= $data[$i];
        }

        if($buffer != '')
            $parsed[] = trim($buffer);

        $parsed = array_filter($parsed);

        return ['cmd' => $parsed, 'user' => $userInfo];
    }

    /**
     * @param $line
     * @return \Dan\Irc\User
     */
    public static function parseUser($line)
    {
        $rank = '';
        $nick = '';
        $user = '';
        $host = '';

        $data       = str_split($line);
        $prefixList = Support::get('PREFIX')[1];

        $index = 0;

        for($i = 0; $i < count($data); $i++)
        {
            if($i == 0)
            {
                if(in_array($data[$i], $prefixList))
                {
                    $rank = $data[$i];
                    continue;
                }
            }

            if($i == '!')
            {
                $index = 1;
                continue;
            }

            if($i == '@')
            {
                $index = 2;
                continue;
            }

            if($index == 0)
                $nick .= $data[$i];
            else if ($index == 1)
                $user .= $data[$i];
            else if ($index == 2)
                $host .= $data[$i];
        }

        return new User([$nick, $user, $host, $rank]);
    }

    /**
     * Parses the NAMES (352) command
     *
     * Example:
     * :Tyrol.GeekShed.net 353 UclCommander @ #DanTestChannel :+TestBot!~TestBot@example.net @UclCommander!~UclComman@uclcommander.net
     * :Ainur.GeekShed.net 353 TestBot @ #DanTestChannel :TestBot @UclCommander
     *
     * @param $line
     * @return User[]
     */
    public static function parseNames($line)
    {
        $people = explode(' ', $line);

        $names = [];

        foreach($people as $person)
            $names[] = static::parseUser($person);

        return $names;
    }
}
 