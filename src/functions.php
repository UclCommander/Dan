<?php

namespace {

    use Dan\Console\Console;
    use Dan\Core\Config;
    use Dan\Core\Dan;
    use Dan\Events\Event;
    use Dan\Events\EventPriority;
    use Dan\Irc\Location\User;

    /**
     * Gets the filesystem class.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    function filesystem()
    {
        return Dan::filesystem();
    }

    /**
     * Returns the database instance.
     *
     * @return \Dan\Database\Database
     */
    function database()
    {
        return Dan::database();
    }

    /**
     * Gets a config item.
     *
     * @param $name
     * @return \Dan\Core\Config|mixed
     */
    function config($name)
    {
        return Config::fetchByKey($name);
    }

    /**
     * Sends a DEBUG message to console.
     *
     * @param $text
     */
    function debug($text)
    {
        Console::debug($text);
    }

    /**
     * Sends an INFO message to console.
     *
     * @param $text
     */
    function info($text)
    {
        Console::info($text);
    }

    /**
     * Sends an ALERT message to console.
     *
     * @param $text
     */
    function alert($text)
    {
        Console::alert($text);
    }

    /**
     * Sends a CRITICAL message to console.
     *
     * @param $text
     * @param bool $die
     */
    function critical($text, $die = false)
    {
        Console::critical($text, $die);
    }

    /**
     * Sends a console message.
     *
     * @param $text
     * @param bool $color
     */
    function console($text, $color = true)
    {
        Console::send($text, $color);
    }

    /**
     * Var dump with colors!
     *
     * @param ...$params
     */
    function vd(...$params)
    {
        Console::send("{white}----- VAR DUMP-----");
        var_dump(...$params);
        Console::send("----- END VAR DUMP-----{reset}");
    }

    /**
     * Sends am IRC line using the message builder.
     *
     * @param ...$params
     */
    function send(...$params)
    {
        Dan::connection()->send(...$params);
    }

    /**
     * Sends a raw IRC line.
     *
     * @param $line
     */
    function raw($line)
    {
        Dan::connection()->raw($line);
    }

    /**
     * Gets the connection.
     *
     * @return \Dan\Irc\Connection
     */
    function connection()
    {
        return Dan::connection();
    }

    /**
     * Fires an event.
     *
     * @param $name
     * @param $data
     * @return mixed
     */
    function event($name, $data = null)
    {
        return Event::fire($name, $data);
    }

    /**
     * Subscribes to an event.
     *
     * @param $name
     * @param $function
     * @param int $priority
     * @return Event
     */
    function subscribe($name, $function, $priority = EventPriority::Normal)
    {
        return Event::subscribe($name, $function, $priority);
    }


    /**
     * Sends a message.
     *
     * @param $location
     * @param $message
     */
    function message($location, $message)
    {
        Dan::connection()->message($location, $message);
    }

    /**
     * Sends a notice.
     *
     * @param $location
     * @param $message
     */
    function notice($location, $message)
    {
        Dan::connection()->notice($location, $message);
    }

    /**
     * Returns a new user.
     *
     * @param $data
     * @return \Dan\Irc\Location\User
     */
    function user($data)
    {
        if(is_array($data))
        {
            $info['nick'] = isset($data['nick']) ? $data['nick'] : $data[0];
            $info['user'] = isset($data['user']) ? $data['user'] : $data[1];
            $info['host'] = isset($data['host']) ? $data['host'] : $data[2];
            $info['rank'] = isset($data['rank']) ? $data['rank'] : (isset($data[3]) ? $data[3] : null);
        }
        else
            $info = database()->get('users', ['nick' => $data]);

        return new User($info);
    }

    /**
     * Coverts a number to a human readable size.
     *
     * @param $size
     * @return string
     */
    function convert($size)
    {
        $unit=['b','kb','mb','gb','tb','pb'];
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }

    /**
     * Checks to see if the string is a channel.
     *
     * @param $channel
     * @return bool
     */
    function isChannel($channel)
    {
        return boolval(preg_match("/#([a-zA-Z0-9_\-\.]+)/", $channel));
    }

    /**
     * Checks to see if the given user is the server.
     *
     * @param $user
     * @return bool
     */
    function isServer($user)
    {
        if (is_array($user))
            $user = reset($user);

        if (!isUser($user))
            return true;

        return ($user == connection()->getNumeric('004')[1]);
    }

    /**
     * Checks to see if it matches the user pattern.
     *
     * @param $pattern
     * @return bool
     */
    function isUser($pattern)
    {
        if($pattern instanceof User)
            return true;

        return fnmatch($pattern, "*!*@*");
    }

    /**
     * Gets relative path from executable.
     *
     * @param $path
     * @return mixed
     */
    function relative($path)
    {
        return str_replace(ROOT_DIR, '', $path);
    }

    /**
     * Sends a message to the control channel.
     *
     * @param $message
     */
    function controlLog($message)
    {
        alert($message);

        if(connection())
        {
            $channel = config('dan.control_channel');

            if (empty($channel) || !connection()->inChannel($channel))
                return;

            connection()->message($channel, $message);
        }
    }

    /**
     * Returns the plugin manager.
     *
     * @return \Dan\Plugins\PluginManager
     */
    function plugins()
    {
        return Dan::plugins();
    }

    /**
     * Returns the command manager.
     *
     * @return \Dan\Commands\CommandManager
     */
    function commands()
    {
        return Dan::commands();
    }
}