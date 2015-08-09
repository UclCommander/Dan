<?php namespace Dan\Setup\Migrations;

use Dan\Contracts\MigrationContract;
use Dan\Core\Config;

class Migrate_4016dev implements MigrationContract {

    public function migrate() { }

    public function migrateConfig()
    {
        $dan = new Config('commands');
        $dan->put('commands.update', 'S');
        $dan->save();
    }

    /**
     * Adds into column to users table in database
     */
    public function migrateDatabase()
    {
        alert('Adding new column to users table');
        database()->addColumn('users', 'info', []);
    }
}