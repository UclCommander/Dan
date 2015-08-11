<?php namespace Dan\Setup\Migrations;

use Dan\Contracts\MigrationContract;
use Dan\Core\Config;

class Migrate_4017dev implements MigrationContract {

    public function migrate() { }

    public function migrateConfig()
    {
        $ignore = new Config('ignore');
        $ignore->putIfNull('masks', []);
        $ignore->save();

        $dan = new Config('commands');
        $dan->renameKey('commands', 'permission');
        $dan->putIfNull('permission.ignore', 'AS');
        $dan->save();
    }

    public function migrateDatabase() { }
}