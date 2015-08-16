<?php namespace Dan\Database;


class DatabaseManager {

    /** @var Database[] */
    protected static $databases = [];

    /**
     * Loads a database.
     *
     * @param $name
     * @throws \Exception
     */
    public function loadDatabase($name)
    {
        if(!$this->exists($name))
            throw new \Exception("Database {$name} doesn't exist.");

        if($this->loaded($name))
            throw new \Exception("Database {$name} is already loaded.");

        $load = new Database($name);

        static::$databases[$name] = $load;
    }

    /**
     * Gets a loaded database.
     *
     * @param $name
     * @return \Dan\Database\Database
     * @throws \Exception
     */
    public function get($name)
    {
        if(!$this->loaded($name))
            $this->loadDatabase($name);

        return static::$databases[$name];
    }

    /**
     * Checks to see if a database exists.
     *
     * @param $name
     * @return bool
     */
    public function exists($name)
    {
        return filesystem()->exists(STORAGE_DIR . "/{$name}.json");
    }

    /**
     * Creates a database.
     *
     * @param $name
     * @return mixed
     * @throws \Exception
     */
    public function create($name)
    {
        if($this->exists($name))
            throw new \Exception("Database {$name} already exist.");

        $new = new Database($name);
        $new->save();

        static::$databases[$name] = $new;

        return static::$databases[$name];
    }

    /**
     * Checks to see if a database is loaded.
     *
     * @param $database
     * @return bool
     */
    public function loaded($database)
    {
        return array_key_exists($database, static::$databases);
    }
}