<?php

namespace Dan\Update;

use Dan\Events\Event;

class Updater
{
    /**
     * @var string
     */
    protected $branch = 'master';

    /**
     * Updater constructor.
     */
    public function __construct()
    {
        $this->branch = config('dan.branch', 'master');

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

        if (!$this->check()) {
            return;
        }

        if (!config('dan.updates.auto_install')) {
            return;
        }

        try {
            $this->update();
        } catch (\Exception $e) {
            console()->error($e->getMessage());
        }
    }

    /**
     * Checks for updates.
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function check() : bool
    {
        if (!file_exists(rootPath('.git'))) {
            throw new \Exception('Unable to check for updates. You must setup Dan as a git clone.');
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
     * @param callable $callback
     *
     * @return bool
     */
    public function update($callback = null) : bool
    {
        if (!is_callable($callback)) {
            $callback = function ($message) {
                console()->message($message);
            };
        }

        $callback('Updating bot...');

        $shell = shell_exec(sprintf("cd %s && git pull origin {$this->branch}", ROOT_DIR));

        // RIP GitHub Jan 27th, 2016
        if (strpos($shell, 'remote error')) {
            $callback('ERROR: Unable to connect to GitHub to check for updates.');

            return true;
        }

        if (strpos($shell, 'error: Your local changes')) {
            $callback("ERROR: There's local changes that are preventing the update.");

            return true;
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

            // Lets try to chmod +x now
            shell_exec('chmod +x '.ROOT_DIR.'/dan');

            if (!is_executable(ROOT_DIR.'/dan')) {
                $callback("Core files have been changed, but was unable to restart. The dan file isn't executable. Please <i>chmod +x dan</i> for automatic restarts.");

                return true;
            }

            $callback('Core files changed, restarting bot.');
            connection()->disconnectFromAll();

            pcntl_exec(ROOT_DIR.'/dan', ['--no-interaction-setup']);

            return true;
        }

        if (strpos($shell, 'addons/')) {
            $callback('Addons changed, reloading.');

            dan()->make('addons')->loadAll();
        }

        $callback('Update complete. Now on version '.dan()->versionHash());

        return true;
    }
}
