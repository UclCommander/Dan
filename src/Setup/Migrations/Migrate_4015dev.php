<?php namespace Dan\Setup\Migrations;


use Dan\Contracts\MigrationContract;
use Dan\Core\Config;

class Migrate_4015dev implements MigrationContract {

    /**
     *
     */
    public function migrate()
    {
        if(!filesystem()->exists(PLUGIN_DIR))
            filesystem()->makeDirectory(PLUGIN_DIR);

        if(!filesystem()->exists(COMMAND_DIR))
            filesystem()->makeDirectory(COMMAND_DIR);

        if(!filesystem()->exists(CONFIG_DIR))
            filesystem()->makeDirectory(CONFIG_DIR);

        if(!filesystem()->exists(STORAGE_DIR))
            filesystem()->makeDirectory(STORAGE_DIR);

        if(!filesystem()->exists(STORAGE_DIR . '/plugins/'))
            filesystem()->makeDirectory(STORAGE_DIR . '/plugins/');

        if(!filesystem()->exists(STORAGE_DIR . '/logs/'))
            filesystem()->makeDirectory(STORAGE_DIR . '/logs/');
    }

    /**
     * @throws \Exception
     */
    public function migrateDatabase()
    {
        if(!database()->exists('users'))
        {
            alert("Creating table users...");

            database()->create('users', [
                'nick'      => '',
                'user'      => '',
                'host'      => '',
                'messages'  => 0
            ]);
        }

        if(!database()->exists('channels'))
        {
            alert("Creating table channels...");

            database()->create('channels', [
                'name'      => '',
                'max_users' => 0,
                'messages'  => 0
            ]);
        }
    }

    /**
     *
     */
    public function migrateConfig()
    {
        $irc = new Config('irc');
        $irc->putIfNull('server', "irc.byteirc.org");
        $irc->putIfNull('port', 6667);
        $irc->putIfNull('user.nick', "Example");
        $irc->putIfNull('user.name', "Example");
        $irc->putIfNull('user.real', "Example Real Name");
        $irc->putIfNull('user.pass', "");
        $irc->putIfNull('channels', ["#DanTesting"]);
        $irc->putIfNull('nickserv_auth_command', 'PRIVMSG NickServ :IDENTIFY %s');
        $irc->putIfNull('autorun_commands', []);
        $irc->putIfNull('show_motd', false);
        $irc->putIfNull('join_on_invite', false);
        $irc->save();

        $commands = new Config('commands');
        $commands->renameKey('command_starter', 'command_prefix');
        $commands->putIfNull('command_prefix', '.');
        $commands->putIfNull('default_permissions', 'vhoaq');
        $commands->putIfNull('commands', []);
        $commands->putIfNull('commands.config', 'S');
        $commands->putIfNull('commands.join', 'AS');
        $commands->putIfNull('commands.kick', 'AS');
        $commands->putIfNull('commands.memory', 'AS');
        $commands->putIfNull('commands.part', 'AS');
        $commands->putIfNull('commands.plugin', 'S');
        $commands->putIfNull('commands.quit', 'S');
        $commands->putIfNull('commands.raw', 'S');
        $commands->putIfNull('commands.reloadhooks', 'S');
        $commands->putIfNull('commands.say', 'AS');
        $commands->save();

        $dan = new Config('dan');
        $dan->putIfNull('debug', false);
        $dan->putIfNull('control_channel', '#DanControl');
        $dan->putIfNull('owners', []);
        $dan->putIfNull('admins', []);
        $dan->putIfNull('plugins', []);
        $dan->save();
    }

}