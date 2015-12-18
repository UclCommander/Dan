<?php namespace Dan\Setup;

use Dan\Contracts\MigrationContract;
use Dan\Setup\Migrations\Migrate_500;
use Dan\Setup\Migrations\Migrate_510;


class Setup {

    /**
     * @var array
     *
     */
    protected static $migrations = [
        '5.0.0' => Migrate_500::class,
        '5.1.0' => Migrate_510::class
    ];

    public static function migrate()
    {
        foreach(static::$migrations as $version => $migration)
        {
            /** @var MigrationContract $class */
            $class = new $migration();

            $class->migrate();
            $class->migrateConfig();
        }
    }

    /**
     * Populates the given database.
     *
     * @param $name
     */
    public static function populateDatabase($name)
    {
        foreach(static::$migrations as $version => $migration)
        {
            /** @var MigrationContract $class */
            $class = new $migration();

            $class->migrateDatabase($name);
        }
    }
}
