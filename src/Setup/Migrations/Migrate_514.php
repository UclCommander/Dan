<?php

namespace Dan\Setup\Migrations;

use Dan\Contracts\MigrationContract;

class Migrate_514 implements MigrationContract
{
    /**
     * @param $name
     *
     * @throws \Exception
     */
    public function migrateDatabase($name)
    {
        if (!database($name)->schema('channels')->columnExists('data')) {
            database($name)->schema('channels')->addColumn('data', []);
        }
    }

    public function migrateConfig()
    {
    }

    public function migrate()
    {
    }
}
