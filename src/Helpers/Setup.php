<?php namespace Dan\Helpers; 

use Dan\Core\Config;
use Dan\Core\Dan;

class Setup {

    /**
     * Checks it see if the bot is setup.
     *
     * @return bool
     */
    public static function isSetup()
    {
        if(!filesystem()->exists(CONFIG_DIR . '/dan.json'))
            return false;

        if(!filesystem()->exists(STORAGE_DIR . '/database.json'))
            return false;

        return config('dan.version') == Dan::VERSION;
    }

    /**
     * Runs the setup
     */
    public static function runSetup()
    {
        $first = filesystem()->exists(CONFIG_DIR . '/dan.json');

        static::createDirectories();
        static::createDefaultConfig();
        static::checkDatabase();

        return !$first;
    }

    /**
     * Creates the default config files
     */
    protected static function createDefaultConfig()
    {
        info('Updating irc.json...');
        $dan = new Config('irc');

        $dan->putIfNull('server', "irc.joebukkit.com");
        $dan->putIfNull('port', 6667);
        $dan->putIfNull('user.nick', "Example");
        $dan->putIfNull('user.name', "Example");
        $dan->putIfNull('user.real', "Example Real Name");
        $dan->putIfNull('user.pass', "");
        $dan->putIfNull('channels', ['#UclCommander']);
        $dan->putIfNull('nickserv_auth_command', 'PRIVMSG NickServ :IDENTIFY %s');
        $dan->putIfNull('autorun_commands', ['MODE {NICK} +B']);
        $dan->putIfNull('show_motd', false);
        $dan->putIfNull('join_on_invite', false);

        $dan->save();

        info('Updating commands.json...');
        $dan = new Config('commands');
        $dan->renameKey('command_starter', 'command_prefix');
        $dan->putIfNull('command_prefix', '.');
        $dan->putIfNull('default_permissions', 'vhoaq');
        $dan->putIfNull('commands', []);
        $dan->putIfNull('commands.config', 'S');
        $dan->putIfNull('commands.join', 'AS');
        $dan->putIfNull('commands.memory', 'AS');
        $dan->putIfNull('commands.part', 'AS');
        $dan->putIfNull('commands.plugin', 'S');
        $dan->putIfNull('commands.quit', 'S');
        $dan->putIfNull('commands.raw', 'S');
        $dan->putIfNull('commands.say', 'AS');
        $dan->save();

        // ALWAYS update dan.json last incase of errors
        info('Updating dan.json...');
        $dan = new Config('dan');

        $dan->put('version', Dan::VERSION);
        $dan->putIfNull('debug', false);
        $dan->putIfNull('control_channel', '#DanControl');
        $dan->putIfNull('owners', []);
        $dan->putIfNull('admins', []);
        $dan->putIfNull('plugins', []);

        $dan->save();
    }

    /**
     * Creates directories
     */
    public static function createDirectories()
    {
        info('Creating directories...');

        if(!filesystem()->exists(PLUGIN_DIR))
        {
            info("Directory '" . relative(PLUGIN_DIR) ."' not found, creating.");
            filesystem()->makeDirectory(PLUGIN_DIR);
        }

        if(!filesystem()->exists(COMMAND_DIR))
        {
            info("Directory '" . relative(COMMAND_DIR) ."' not found, creating.");
            filesystem()->makeDirectory(COMMAND_DIR);
        }

        if(!filesystem()->exists(CONFIG_DIR))
        {
            info("Directory '" . relative(CONFIG_DIR) ."' not found, creating.");
            filesystem()->makeDirectory(CONFIG_DIR);
        }

        if(!filesystem()->exists(STORAGE_DIR))
        {
            info("Directory '" . relative(STORAGE_DIR) ."' not found, creating.");
            filesystem()->makeDirectory(STORAGE_DIR);
        }

        if(!filesystem()->exists(STORAGE_DIR . '/plugins/'))
        {
            info("Directory '" . relative(STORAGE_DIR) ."/plugins/' not found, creating.");
            filesystem()->makeDirectory(STORAGE_DIR . '/plugins/');
        }

        if(!filesystem()->exists(STORAGE_DIR . '/logs/'))
        {
            info("Directory '" . relative(STORAGE_DIR) ."/logs/' not found, creating.");
            filesystem()->makeDirectory(STORAGE_DIR . '/logs/');
        }
    }

    /**
     *
     */
    public static function checkDatabase()
    {
        info('Updating database...');

        if(!database()->exists('users'))
        {
            info('Creating users table...');

            database()->create('users', [
                'nick'      => '',
                'user'      => '',
                'host'      => '',
                'messages'  => 0,
            ]);
        }

        if(!database()->exists('channels'))
        {
            info('Creating channels table...');

            database()->create('channels', [
                'name'      => '',
                'max_users' => 0,
                'messages'  => 0,
            ]);
        }
    }
}