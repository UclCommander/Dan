<?php namespace Dan\Setup\Migrations;

use Dan\Contracts\MigrationContract;
use Dan\Core\Config;
use Dan\Core\Dan;

class Migrate_500 implements MigrationContract {

    /**
     * @param $name
     * @throws \Exception
     */
    public function migrateDatabase($name)
    {
        if(!database($name)->tableExists('users'))
        {
            info("Creating table users...");

            database($name)->schema('users')->create([
                'nick'      => '',
                'user'      => '',
                'host'      => '',
                'real'      => '',
                'info'      => [],
            ]);
        }

        if(!database($name)->tableExists('channels'))
        {
            info("Creating table channels...");

            database($name)->schema('channels')->create([
                'name'      => '',
                'max_users' => 0,
                'info'      => [],
            ]);
        }
    }

    /**
     *
     */
    public function migrateConfig()
    {
        $irc = new Config('irc');
        $irc->putIfNull('show_motd', false);
        $irc->putIfNull('enabled', []);
        $irc->putIfNull('servers', []);
        $irc->putIfNull('servers.byteirc', []);
        $irc->putIfNull('servers.byteirc.server', Dan::args('--irc-server', "irc.byteirc.org"));
        $irc->putIfNull('servers.byteirc.port', Dan::args('--irc-port', 6667));
        $irc->putIfNull('servers.byteirc.user.nick', "Example");
        $irc->putIfNull('servers.byteirc.user.name', "Example");
        $irc->putIfNull('servers.byteirc.user.real', "Example Real Name");
        $irc->putIfNull('servers.byteirc.user.pass', "");
        $irc->putIfNull('servers.byteirc.channels', ["#DanTesting"]);
        $irc->putIfNull('servers.byteirc.nickserv_auth_command', 'PRIVMSG NickServ :IDENTIFY %s');
        $irc->putIfNull('servers.byteirc.autorun_commands', []);
        $irc->putIfNull('servers.byteirc.join_on_invite', false);
        $irc->putIfNull('servers.byteirc.command_prefix', '.');
        $irc->putIfNull('servers.byteirc.control_channel', '#DanControl');
        $irc->save();

        $commands = new Config('commands');
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
        $commands->putIfNull('permissions.restart', 'S');
        $commands->putIfNull('permissions.reloadhooks', 'S');
        $commands->putIfNull('permissions.say', 'AS');
        $commands->putIfNull('permissions.update', 'S');
        $commands->save();

        $ignore = new Config('ignore');
        $ignore->putIfNull('masks', []);
        $ignore->save();

        $dan = new Config('dan');
        $dan->putIfNull('auto_check_for_updates', true);
        $dan->putIfNull('auto_install_updates', true);
        $dan->putIfNull('owners', []);
        $dan->putIfNull('admins', []);
        $dan->save();
    }

    public function migrate()
    {
        // TODO: Implement migrate() method.
    }
}