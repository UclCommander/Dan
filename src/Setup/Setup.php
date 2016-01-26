<?php

namespace Dan\Setup;

use Dan\Contracts\MigrationContract;
use Dan\Setup\Migrations\Migrate_500;
use Dan\Setup\Migrations\Migrate_510;
use Dan\Setup\Migrations\Migrate_511;
use Dan\Setup\Migrations\Migrate_512;
use Dan\Setup\Migrations\Migrate_513;
use Dan\Setup\Migrations\Migrate_514;
use Dan\Setup\Migrations\Migrate_515;

class Setup
{
    /**
     * @var array
     */
    protected static $migrations = [
        '5.0.0' => Migrate_500::class,
        '5.1.0' => Migrate_510::class,
        '5.1.1' => Migrate_511::class,
        '5.1.2' => Migrate_512::class,
        '5.1.3' => Migrate_513::class,
        '5.1.4' => Migrate_514::class,
        '5.1.5' => Migrate_515::class,
    ];

    public static function migrate()
    {
        foreach (static::$migrations as $version => $migration) {
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
        foreach (static::$migrations as $version => $migration) {
            /** @var MigrationContract $class */
            $class = new $migration();

            $class->migrateDatabase($name);
        }
    }
}
