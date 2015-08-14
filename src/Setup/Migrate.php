<?php namespace Dan\Setup;

use Dan\Contracts\MigrationContract;
use Dan\Core\Config;
use Dan\Core\Dan;

class Migrate {

    protected $first = false;

    /**
     *
     */
    public static function checkAndDo()
    {
        if(!static::versionCheck())
        {
            alert("It appears this is a first time run, or there was an update. Setting defaults up.");

            $migrate = new static();
            $migrate->doMigration();

            alert("Setup complete.");

            if($migrate->wasFirst() && !Dan::args('--skip-setup', false))
            {
                alert("This was a first time setup. You can now configure the bot in the config directory.");
                die;
            }

            return;
        }

        alert("There's nothing to migrate!");
    }

    /**
     * @return bool
     */
    public static function versionCheck()
    {
        if(!filesystem()->exists(STORAGE_DIR . '/migrated.json'))
            return false;

        if(!filesystem()->exists(CONFIG_DIR . '/dan.json'))
            return false;

        if(!filesystem()->exists(STORAGE_DIR . '/database.json'))
            return false;

        return config('dan.version') == Dan::VERSION;
    }

    /**
     *
     */
    public function doMigration()
    {
        alert("Running migrations...");

        $all        = false;
        $migrated   = [];

        if(!filesystem()->exists(CONFIG_DIR . '/dan.json'))
            $this->first = $all = true;

        if(!filesystem()->exists(STORAGE_DIR . '/database.json'))
        {
            $all = true;
            databaseManager()->create('database');
        }
        else
            database()->load();

        if(filesystem()->exists(STORAGE_DIR . '/migrated.json'))
            $migrated = json_decode(filesystem()->get(STORAGE_DIR . '/migrated.json'), true);

        foreach(glob(BASE_DIR . "/src/Setup/Migrations/Migrate_*.php") as $migration)
        {
            $file = basename($migration);

            if(in_array($file, $migrated) && !$all)
                continue;

            $safe = basename($file, '.php');

            alert("Migrating {$safe}");

            $class = "Dan\\Setup\\Migrations\\{$safe}";

            /** @var MigrationContract $migrate */
            $migrate = new $class();
            $migrate->migrate();
            $migrate->migrateDatabase();
            $migrate->migrateConfig();

            $migrated[] = $file;
        }

        filesystem()->put(STORAGE_DIR . '/migrated.json', json_encode($migrated, JSON_PRETTY_PRINT));

        alert("Bumping version...");

        $this->bumpVersion();
    }

    /**
     * @return bool
     */
    public function wasFirst()
    {
        return $this->first;
    }

    /**
     *
     */
    protected function bumpVersion()
    {
        $dan = new Config('dan');
        $dan->put('version', Dan::VERSION);
        $dan->save();
    }
}
