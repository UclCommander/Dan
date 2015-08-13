<?php namespace Dan\Database;

use Exception;

class Database {

    /**
     * @var array
     */
    public $config = [];

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var
     */
    protected $file;

    /**
     * @param $file
     */
    public function __construct($file)
    {
        $this->file = $file;

        $this->load();
    }

    /**
     * Gets a table manipulation object.
     *
     * @param $name
     * @return \Dan\Database\Table
     * @throws \Exception
     */
    public function table($name)
    {
        if(!$this->tableExists($name))
            throw new \Exception("Table {$name} doesn't exist.");

        return new Table($this, $name);
    }

    /**
     * @param $table
     * @return \Dan\Database\TableSchema
     * @throws \Exception
     */
    public function schema($table)
    {
        return new TableSchema($this, $table);
    }

    /**
     * Checks to see if a table exists.
     *
     * @param $table
     * @return bool
     */
    public function tableExists($table)
    {
        return array_key_exists($table, $this->config);
    }

    /**
     * @param $table
     * @param $column
     * @param $default
     */
    public function setTableConfig($table, $column, $default)
    {
        $this->config[$table]['columns'][$column][$default];
    }

    /**
     * Loads the database.
     *
     * @throws \Exception
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function load()
    {
        if(!filesystem()->exists(STORAGE_DIR . "/{$this->file}.json"))
            $this->save();

        $database = json_decode(filesystem()->get(STORAGE_DIR . "/{$this->file}.json"), true);

        if(!is_array($database))
            throw new Exception("Database {$this->file} is corrupted.");

        if(!empty($database))
        {
            $this->config = $database['config'];
            $this->data = $database['data'];
        }
    }

    /**
     * Saves the database.
     */
    public function save()
    {
        filesystem()->put(STORAGE_DIR . "/{$this->file}.json", json_encode(['data' => $this->data, 'config' => $this->config], JSON_PRETTY_PRINT));
    }
}