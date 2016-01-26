<?php

namespace Dan\Setup\Migrations;

use Dan\Contracts\MigrationContract;
use Dan\Core\Config;

class Migrate_515 implements MigrationContract
{
    public function migrateDatabase($name)
    {
    }

    public function migrateConfig()
    {
        $dan = new Config('dan');
        $dan->putIfNull('database_backup_interval', 20);
        $dan->save();
    }

    public function migrate()
    {
    }
}
