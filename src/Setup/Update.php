<?php namespace Dan\Setup;


use Dan\Contracts\MessagingContract;
use Dan\Core\Dan;
use Dan\Hooks\HookManager;
use Dan\Irc\Location\Location;

class Update {

    protected static $repo = 'dan5';

    /**
     * Returns true if there is an update. False otherwise.
     *
     * @return bool
     */
    public static function check()
    {
        $repo = static::$repo;

        $status = shell_exec("git remote update && git status {$repo}");

        return (strpos($status, "up-to-date") === false) === true;
    }


    public static function go(MessagingContract $messagingContract)
    {
        $repo = static::$repo;

        $shell = shell_exec(sprintf("cd %s && git pull origin {$repo}", ROOT_DIR));

        if(strpos($shell, 'src/'))
        {
            if(!function_exists('pcntl_exec'))
            {
                $messagingContract->message("Core files have been changed, but was unable to restart. PHP needs to be compiled with --enable-pcntl for automatic restarts.");
                return;
            }

            $messagingContract->message("Core files changed, restarting bot.");

            Dan::quit("Updating bot.");
            pcntl_exec(ROOT_DIR . '/dan');
            return;
        }

        if(strpos($shell, 'hooks/'))
        {
            $messagingContract->message("Hooks changed, reloading.");
            HookManager::loadHooks();
        }
    }

    /**
     *
     */
    public static function autoUpdate()
    {
        if(!config('dan.auto_check_for_updates'))
            return;

        if(!static::check())
            return;

        controlLog('Update found!');

        if(!config('dan.auto_install_updates'))
            return;

        controlLog('Running automatic update...');

        static::go(Dan::connection('console'));
    }
}