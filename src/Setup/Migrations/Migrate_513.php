<?php namespace Dan\Setup\Migrations;

use Dan\Contracts\MigrationContract;
use Dan\Core\Config;

class Migrate_513 implements MigrationContract {

    /**
     * @param $name
     * @throws \Exception
     */
    public function migrateDatabase($name) { }

    public function migrateConfig()
    {
        $servers = config('irc.servers');

        $irc = new Config('irc');

        foreach ($servers as $server => $data) {
            $irc->putIfNull("servers.{$server}.auto_reconnect", true);
        }

        $irc->save();
    }

    public function migrate() { }
}