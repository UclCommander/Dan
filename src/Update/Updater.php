<?php namespace Dan\Update;


use Dan\Events\Event;

class Updater
{
    /**
     * @var string
     */
    protected $branch = 'dan6.0';

    /**
     * Updater constructor.
     */
    public function __construct()
    {
        events()->subscribe('irc.ping', [$this, 'autoUpdate'], Event::VeryHigh);
    }

    /**
     * Automatically checks and updates the bot.
     */
    public function autoUpdate()
    {
        if (!$this->check()) {
            return;
        }

        $this->update();
    }

    /**
     * Checks for updates.
     *
     * @return bool
     */
    public function check() : bool
    {
        if (!file_exists(rootPath('.git'))) {
            console()->error('Unable to auto update. You must setup dan as a git clone.');
            return false;
        }

        if (!config('dan.updates.auto_check')) {
            return false;
        }

        $status = shell_exec("git remote update && git status {$this->branch}");

        // RIP GitHub Jan 27th, 2016
        if (strpos($status, 'remote error')) {
            console()->error('Unable to connect to GitHub to check for updates.');

            return false;
        }

        return (strpos($status, 'up-to-date') === false) === true;
    }

    /**
     * Updates and restarts the bot.
     */
    public function update()
    {
        if (!config('dan.updates.auto_install')) {
            return;
        }

        $shell = shell_exec(sprintf("cd %s && git pull origin {$this->branch}", ROOT_DIR));

        // RIP GitHub Jan 27th, 2016
        if (strpos($shell, 'remote error')) {
            console()->error('Unable to connect to GitHub to check for updates.');

            return;
        }

        if (strpos($shell, 'composer.lock')) {
            console()->notice('composer.lock changed, installing new packages.');
            shell_exec(sprintf('cd %s && composer install', ROOT_DIR));
        }

        if (strpos($shell, 'src/')) {
            if (!function_exists('pcntl_exec')) {
                console()->notice('Core files have been changed, but was unable to restart. PHP needs to be compiled with --enable-pcntl for automatic restarts.');
                return;
            }

            console()->notice('Core files changed, restarting bot.');
            connection()->disconnectFromAll();

            pcntl_exec(ROOT_DIR.'/dan');

            return;
        }

        if (strpos($shell, 'addons/')) {
            console()->notice('Addons changed, reloading.');

            dan()->make('addons')->loadAll();
        }
    }
}