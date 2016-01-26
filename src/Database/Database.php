<?php

namespace Dan\Database;

use Exception;

class Database
{
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
     *
     * @throws \Exception
     *
     * @return \Dan\Database\Table
     */
    public function table($name)
    {
        if (!$this->tableExists($name)) {
            throw new \Exception("Table {$name} doesn't exist.");
        }

        return new Table($this, $name, $this->data[$name]);
    }

    /**
     * @param $table
     *
     * @throws \Exception
     *
     * @return \Dan\Database\TableSchema
     */
    public function schema($table)
    {
        return new TableSchema($this, $table);
    }

    /**
     * Checks to see if a table exists.
     *
     * @param $table
     *
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
        if (!file_exists(DATABASE_DIR."/{$this->file}.json")) {
            $this->save();
        }

        $database = json_decode(filesystem()->get(DATABASE_DIR."/{$this->file}.json"), true);

        if (!is_array($database)) {
            throw new Exception("Database {$this->file} is corrupted.");
        }

        if (!empty($database)) {
            $this->config = $database['config'];
            $this->data = $database['data'];
        }
    }

    /**
     * Saves the database.
     *
     * @param bool $backup
     */
    public function save($backup = false)
    {
        $json = json_encode(['data' => $this->data, 'config' => $this->config], JSON_PRETTY_PRINT);

        if($json === false) {
            return;
        }

        if ($backup) {
            $date = date("Ymd-His");
            filesystem()->copy(DATABASE_DIR."/{$this->file}.json", BACKUP_DIR."/{$this->file}-{$date}.json");
        }

        filesystem()->put(DATABASE_DIR."/{$this->file}.json", $json);
    }
}
