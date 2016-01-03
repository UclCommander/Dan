<?php

namespace Dan\Setup\Migrations;

use Dan\Contracts\MigrationContract;

class Migrate_512 implements MigrationContract
{
    /**
     * @param $name
     *
     * @throws \Exception
     */
    public function migrateDatabase($name)
    {
        if (!database($name)->tableExists('cache')) {
            info('Creating table cache...');

            database($name)->schema('cache')->create([
                'key'      => '',
                'value'    => '',
            ]);
        }
    }

    public function migrateConfig()
    {
    }

    public function migrate()
    {
    }
}
