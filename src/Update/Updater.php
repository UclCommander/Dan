<?php

namespace Dan\Update;

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
        if (!config('dan.updates.auto_check')) {
            return;
        }

        try {
            $this->update(console());
        } catch (\Exception $e) {
            console()->error($e->getMessage());
        }
    }

    /**
     * Checks for updates.
     *
     * @return bool
     * @throws \Exception
     */
    public function check() : bool
    {
        if (!file_exists(rootPath('.git'))) {
            throw new \Exception('Unable to auto update. You must setup Dan as a git clone.');
        }

        $status = shell_exec("git remote update && git status {$this->branch}");

        // RIP GitHub Jan 27th, 2016
        if (strpos($status, 'remote error')) {
            throw new \Exception('Unable to connect to GitHub to check for updates.');
        }

        return (strpos($status, 'up-to-date') === false) === true;
    }

    /**
     * Updates and restarts the bot.
     *
     * @param bool $force
     *
     * @param callable $callback
     *
     * @return bool
     */
    public function update($force = false, $callback = null) : bool
    {
        if (!$this->check()) {
            return false;
        }

        if (!config('dan.updates.auto_install') && !$force) {
            return false;
        }

        if(!is_callable($callback)) {
            $callback = function ($message) {
                console()->message($message);
            };
        }

        $shell = shell_exec(sprintf("cd %s && git pull origin {$this->branch}", ROOT_DIR));

        // RIP GitHub Jan 27th, 2016
        if (strpos($shell, 'remote error')) {
            $callback('ERROR: Unable to connect to GitHub to check for updates.');
        }

        if (strpos($shell, 'composer.lock')) {
            $callback('composer.lock changed, installing new packages.');
            shell_exec(sprintf('cd %s && composer install', ROOT_DIR));
        }

        if (strpos($shell, 'src/')) {
            if (!function_exists('pcntl_exec')) {
                $callback('Core files have been changed, but was unable to restart. PHP needs to be compiled with --enable-pcntl for automatic restarts.');
                return true;
            }

            $callback('Core files changed, restarting bot.');
            connection()->disconnectFromAll();

            pcntl_exec(ROOT_DIR.'/dan');

            return true;
        }

        if (strpos($shell, 'addons/')) {
            $callback('Addons changed, reloading.');

            dan()->make('addons')->loadAll();
        }

        return true;
    }
}