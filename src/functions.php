<?php

namespace {

    use Dan\Console\Console;
    use Dan\Contracts\MessagingContract;
    use Dan\Contracts\SocketContract;
    use Dan\Core\Config;
    use Dan\Core\Dan;
    use Dan\Database\Database;
    use Dan\Database\DatabaseManager;
    use Dan\Events\Event;
    use Dan\Events\EventPriority;
    use Dan\Hooks\Hook;
    use Dan\Hooks\HookManager;
    use Dan\Irc\Connection;
    use Dan\Irc\Location\Channel;
    use Dan\Irc\Location\User;
    use Illuminate\Filesystem\Filesystem;

    #region class fetchers

    /**
     * Gets the filesystem class.
     *
     * @return \Illuminate\Filesystem\Filesystem
     */
    function filesystem() : Filesystem
    {
        return Dan::filesystem();
    }

    /**
     * Returns the database instance.
     *
     * @param string $name
     * @return \Dan\Database\Database
     */
    function database($name = null) : Database
    {
        return Dan::database($name);
    }

    /**
     * Returns the database manager instance.
     *
     * @return \Dan\Database\DatabaseManager
     */
    function databaseManager() : DatabaseManager
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

    #endregion

    #region console

    /**
     * Sends a DEBUG message to console.
     *
     * @param $text
     */
    function debug($text)
    {
        Console::factory()->debug($text);
    }

    /**
     * Sends an INFO message to console.
     *
     * @param $text
     */
    function error($text)
    {
        Console::factory()->error($text);
    }

    /**
     * Sends an INFO message to console.
     *
     * @param $text
     */
    function success($text)
    {
        Console::factory()->success($text);
    }

    /**
     * Sends an INFO message to console.
     *
     * @param $text
     */
    function info($text)
    {
        Console::factory()->info($text);
    }

    /**
     * @param $text
     * @deprecated use warn()
     */
    function alert($text)
    {
        warn($text);
    }

    /**
     * Sends an ALERT message to console.
     *
     * @param $text
     */
    function warn($text)
    {
        Console::factory()->warn($text);
    }

    /**
     * Sends a CRITICAL message to console.
     *
     * @deprecated
     * @param $text
     * @param bool $die
     */
    function critical($text, $die = false)
    {
        error($text);
    }

    /**
     * Sends a console message.
     *
     * @param $text
     * @param bool $color
     */
    function console($text, $color = true)
    {
        Console::factory()->line($text);
    }

    /**
     * Var dump with colors!
     *
     * @param ...$params
     */
    function vd(...$params)
    {
        Console::factory()->line("----- VAR DUMP -----");
        var_dump(...$params);
        Console::factory()->line("----- END VAR DUMP -----");
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
            warn($message);

        if(connection())
        {
            $channel = connection()->config->get('control_channel');

            if(empty($channel) || !connection()->inChannel($channel))
                return;

            connection()->message($channel, $message);
        }
    }

    #endregion

    #region irc

    /**
     * @param null $name
     * @return \Dan\Irc\Connection|SocketContract|MessagingContract
     */
    function connection($name = null)
    {
        return Dan::connection($name);
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
     * Sends a message.
     *
     * @param $location
     * @param $message
     * @param array $styles
     */
    function message($location, $message, $styles = [])
    {
        Dan::connection()->message($location, $message, $styles);
    }

    /**
     * Sends an action.
     *
     * @param $location
     * @param $message
     */
    function action($location, $message)
    {
        Dan::connection()->action($location, $message);
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
            $info['user'] = isset($data['user']) ? $data['user'] : $data[1] ?? null;
            $info['host'] = isset($data['host']) ? $data['host'] : $data[2] ?? null;
            $info['rank'] = isset($data['rank']) ? $data['rank'] : $data[3] ?? null;
        }
        else
            $info = database()->table('users')->where('nick', $data)->first()->toArray();

        return new User($info, null, $save);
    }

    /**
     * Checks to see if the string is a channel.
     *
     * @param $channel
     * @param null $connection
     * @return bool
     */
    function isChannel($channel, $connection = null) : bool
    {
        if($channel instanceof Channel)
            return true;

        $types = preg_quote(Dan::connection($connection)->support->get('CHANTYPES'));

        if($types == null)
            return false;

        return boolval(preg_match("/[{$types}]([a-zA-Z0-9_\-\.]+)/", $channel));
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

     */
    function hook($name) : Hook
    {
        return HookManager::registerHook($name);
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
     * Checks to if the command exists.
     *
     * @param $cmd
     * @return bool
     */
    function commandExists($cmd)
    {
        if(isWin())
        {
            $returnVal = shell_exec($cmd);
            return !strpos($returnVal, 'is not recognized');
        }

        $returnVal = shell_exec("which {$cmd}");
        return (empty($returnVal) ? false : true);
    }

    /**
     * May discord have mercy on your soul.
     */
    function isWin()
    {
        return strpos(strtolower(PHP_OS), 'win') === 0;
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

    /**
     * Because array_rand doesn't do what's expected of it.
     *
     * @param $array
     * @return mixed
     */
    function array_random($array)
    {
        return $array[array_rand($array)];
    }

    function xmlToArray($data)
    {
        return json_decode(json_encode($data), true);
    }

    #endregion

    #region url

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

    #endregion
}