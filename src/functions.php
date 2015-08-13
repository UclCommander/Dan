<?php

namespace {

    use Dan\Console\Console;
    use Dan\Core\Config;
    use Dan\Core\Dan;
    use Dan\Events\Event;
    use Dan\Events\EventPriority;
    use Dan\Helpers\Hooks;
    use Dan\Irc\Location\User;

    #region class fetchers

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
     * Returns the database manager instance.
     *
     * @return \Dan\Database\DatabaseManager
     */
    function databaseManager()
    {
        return Dan::databaseManager();
    }

    /**
     * Gets a config item.
     *
     * @param $name
     * @return \Dan\Core\Config|array|string|mixed
     */
    function config($name)
    {
        return Config::fetchByKey($name);
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

    #endregion

    #region console

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
     * Sends a message to the control channel.
     *
     * @param $message
     * @param bool $debug
     */
    function controlLog($message, $debug = false)
    {
        if($debug)
            debug($message);
        else
            alert($message);

        if(connection())
        {
            $channel = config('dan.control_channel');

            if(empty($channel) || !connection()->inChannel($channel))
                return;

            connection()->message($channel, $message);
        }
    }

    #endregion

    #region irc

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
     * @param bool $save
     * @return \Dan\Irc\Location\User
     */
    function user($data, $save = true)
    {
        if(is_array($data))
        {
            $info['nick'] = isset($data['nick']) ? $data['nick'] : $data[0];
            $info['user'] = isset($data['user']) ? $data['user'] : $data[1];
            $info['host'] = isset($data['host']) ? $data['host'] : $data[2];
            $info['rank'] = isset($data['rank']) ? $data['rank'] : (isset($data[3]) ? $data[3] : null);
        }
        else
            $info = database()->table('users')->where('nick', $data)->first()->toArray();

        return new User($info, $save);
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
        if(is_array($user))
            $user = reset($user);

        if($user == 'AUTH' || $user == '*')
            return true;

        if(isUser($user))
            return false;

        return true;
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

        if(is_array($pattern) && count($pattern) == 3)
            return true;

        if(is_array($pattern))
            $pattern = reset($pattern);

        if(fnmatch("*!*@*", $pattern))
            return true;

        return database()->table('users')->where('nick', $pattern)->count() != 0;
    }

    #endregion

    #region events

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
     * Registers a hook.
     *
     * @param $data
     * @param $callback
     */
    function hook($data, $callback)
    {
        Hooks::defineHook($data, $callback);
    }

    /**
     * Calls a hook.
     *
     * @param $name
     * @param array $data
     * @return bool
     */
    function callHook($name, $data = [])
    {
        return Hooks::callHook($name, $data);
    }

    #endregion

    #region utility

    /**
     * Coverts a number to a human readable size.
     *
     * @param $size
     * @return string
     */
    function convert($size)
    {
        $unit = ['b', 'kb', 'mb', 'gb', 'tb', 'pb'];
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . ' ' . $unit[$i];
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
     * @param $cmd
     * @return bool
     */
    function commandExists($cmd)
    {
        $returnVal = shell_exec("which $cmd");
        return (empty($returnVal) ? false : true);
    }

    /**
     * Parses a string and replaces by key.
     *
     * @param $format
     * @param array $data
     * @return mixed
     */
    function parseFormat($format, array $data)
    {
        foreach($data as $key => $value)
            $format = str_replace("{" . strtoupper($key) . "}", $value, $format);

        return $format;
    }

    /**
     * Gets webpage headers and normalizes the keys.
     *
     * @param $url
     * @return array
     */
    function getHeaders($url)
    {
        $headers = get_headers($url, true);

        $new = [];

        foreach($headers as $key => $value)
            $new[strtolower($key)] = $value;

        return $new;
    }

    #endregion

    function xmlToArray($data)
    {
        return json_decode(json_encode($data), true);
    }


    // ALL code below found here because im lazy: http://stackoverflow.com/a/4102293

    /**
     * get_redirect_url()
     * Gets the address that the provided URL redirects to,
     * or FALSE if there's no redirect.
     *
     * @param string $url
     * @return string
     */
    function get_redirect_url($url)
    {
        $redirect_url = null;
        $url_parts = @parse_url($url);

        if(!$url_parts)
            return false;

        if(!isset($url_parts['host']))
            return false;

        if(!isset($url_parts['path']))
            $url_parts['path'] = '/';

        $sock = fsockopen($url_parts['host'], (isset($url_parts['port']) ? (int)$url_parts['port'] : 80), $errno, $errstr, 30);

        if(!$sock)
            return false;

        $request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ? '?' . $url_parts['query'] : '') . " HTTP/1.1\r\n";
        $request .= 'Host: ' . $url_parts['host'] . "\r\n";
        $request .= "Connection: Close\r\n\r\n";

        fwrite($sock, $request);

        $response = '';

        while(!feof($sock))
            $response .= fread($sock, 8192);

        fclose($sock);

        if(preg_match('/^Location: (.+?)$/m', $response, $matches))
        {
            if(substr($matches[1], 0, 1) == "/")
                return $url_parts['scheme'] . "://" . $url_parts['host'] . trim($matches[1]);

            return trim($matches[1]);
        }

        return false;
    }

    /**
     * get_all_redirects()
     * Follows and collects all redirects, in order, for the given URL.
     *
     * @param string $url
     * @return array
     */
    function get_all_redirects($url)
    {
        $redirects = [];

        while($newurl = get_redirect_url($url))
        {
            if(in_array($newurl, $redirects))
                break;

            $redirects[] = $newurl;
            $url = $newurl;
        }

        return $redirects;
    }

    /**
     * get_final_url()
     * Gets the address that the URL ultimately leads to.
     * Returns $url itself if it isn't a redirect.
     *
     * @param string $url
     * @return string
     */
    function get_final_url($url)
    {
        $redirects = get_all_redirects($url);

        if(count($redirects) > 0)
            return array_pop($redirects);

        return $url;
    }

}