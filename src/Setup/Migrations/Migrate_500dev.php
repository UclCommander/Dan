<?php namespace Dan\Setup\Migrations;

use Dan\Contracts\MigrationContract;
use Dan\Core\Config;
use Dan\Core\Dan;

class Migrate_500dev implements MigrationContract {

    /**
     *
     */
    public function migrate()
    {
        if(!filesystem()->exists(CONFIG_DIR))
            filesystem()->makeDirectory(CONFIG_DIR);

        if(!filesystem()->exists(STORAGE_DIR))
            filesystem()->makeDirectory(STORAGE_DIR);
    }

    /**
     * @throws \Exception
     */
    public function migrateDatabase()
    {
        if(!database()->tableExists('users'))
        {
            alert("Creating table users...");

            database()->schema('users')->create([
                'nick'      => '',
                'user'      => '',
                'host'      => '',
                'messages'  => 0,
                'info'      => [],
            ]);
        }

        if(!database()->tableExists('channels'))
        {
            alert("Creating table channels...");

            database()->schema('channels')->create([
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
        $irc->putIfNull('server', Dan::args('--irc-server', "irc.byteirc.org"));
        $irc->putIfNull('port', Dan::args('--irc-port', 6667));
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
        $commands->renameKey('commands', 'permissions');
        $commands->renameKey('permission', 'permissions');
        $commands->renameKey('command_starter', 'command_prefix');
        $commands->putIfNull('command_prefix', '.');
        $commands->putIfNull('default_permissions', 'vhoaq');
        $commands->putIfNull('permissions', []);
        $commands->putIfNull('permissions.config', 'S');
        $commands->putIfNull('permissions.ignore', 'AS');
        $commands->putIfNull('permissions.join', 'AS');
        $commands->putIfNull('permissions.kick', 'AS');
        $commands->putIfNull('permissions.memory', 'AS');
        $commands->putIfNull('permissions.part', 'AS');
        $commands->putIfNull('permissions.plugin', 'S');
        $commands->putIfNull('permissions.quit', 'S');
        $commands->putIfNull('permissions.raw', 'S');
        $commands->putIfNull('permissions.reloadhooks', 'S');
        $commands->putIfNull('permissions.say', 'AS');
        $commands->putIfNull('permissions.update', 'S');
        $commands->save();

        $ignore = new Config('ignore');
        $ignore->putIfNull('masks', []);
        $ignore->save();

        $dan = new Config('dan');
        $dan->putIfNull('debug', false);
        $dan->putIfNull('control_channel', '#DanControl');
        $dan->putIfNull('owners', []);
        $dan->putIfNull('admins', []);
        $dan->putIfNull('plugins', []);
        $dan->save();
    }

}