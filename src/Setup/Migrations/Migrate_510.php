<?php

namespace Dan\Setup\Migrations;

use Dan\Contracts\MigrationContract;
use Dan\Core\Config;

class Migrate_510 implements MigrationContract
{
    /**
     * @param $name
     *
     * @throws \Exception
     */
    public function migrateDatabase($name)
    {
    }

    /**
     *
     */
    public function migrateConfig()
    {
        $web = new Config('web');
        $web->putIfNull('enabled', false);
        $web->putIfNull('host', '127.0.0.1');
        $web->putIfNull('port', '6969');
        $web->putIfNull('routes', []);
        $web->save();
    }

    public function migrate()
    {
        // TODO: Implement migrate() method.
    }
}
