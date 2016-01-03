<?php

namespace Dan\Contracts;

interface MigrationContract
{
    public function migrate();

    public function migrateConfig();

    public function migrateDatabase($name);
}
