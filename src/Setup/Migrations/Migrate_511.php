<?php namespace Dan\Setup\Migrations;

use Dan\Contracts\MigrationContract;
use Dan\Core\Config;

class Migrate_511 implements MigrationContract {

    /**
     * @param $name
     * @throws \Exception
     */
    public function migrateDatabase($name) { }

    /**
     *
     */
    public function migrateConfig()
    {
        $dan = new Config('dan');
        $dan->putIfNull('use_short_links', true);
        $dan->putIfNull('short_link_api', \Dan\Services\ShortLinks\Links::class);
        $dan->save();
    }

    public function migrate()
    {
        // TODO: Implement migrate() method.
    }
}