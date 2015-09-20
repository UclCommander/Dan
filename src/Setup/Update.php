<?php namespace Dan\Setup;


use Dan\Core\Dan;
use Dan\Hooks\HookManager;

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

        $status = shell_exec("git remote update && git status ");

        return !strpos($status, "Your branch is up-to-date") === false;
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

        $shell = shell_exec(sprintf("cd %s && git pull origin dan4", ROOT_DIR));

        if(strpos($shell, 'src/'))
        {
            if(!function_exists('pcntl_exec'))
            {
                controlLog("Core files have been changed, but was unable to restart. PHP needs to be compiled with --enable-pcntl for automatic restarts.");
                return;
            }

            controlLog("Core files changed, restarting bot.");

            Dan::quit("Updating bot.");
            pcntl_exec(ROOT_DIR . '/dan');
            return;
        }

        if(strpos($shell, 'hooks/'))
        {
            controlLog("Hooks changed, reloading.");
            HookManager::loadHooks();
        }
    }
}