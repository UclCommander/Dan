<?php namespace Dan\Setup\Migrations;

use Dan\Contracts\MigrationContract;
use Dan\Core\Config;

class Migrate_4017dev implements MigrationContract {

    public function migrate() { }

    public function migrateConfig()
    {
        $dan = new Config('commands');
        $dan->renameKey('commands', 'permission');
        $dan->putIfNull('permission.restart', 'S');
        $dan->save();
    }

    public function migrateDatabase() { }
}