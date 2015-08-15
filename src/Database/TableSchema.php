<?php namespace Dan\Database;


use Exception;

class TableSchema {

    protected $table;

    /**
     * @var \Dan\Database\Database
     */
    private $database;

    /**
     * TableSchema constructor.
     *
     * @param \Dan\Database\Database $database
     * @param $table
     */
    public function __construct(Database $database, $table)
    {
        $this->table = $table;
        $this->database = $database;
    }

    /**
     * Creates a table.
     *
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public function create(array $data)
    {
        if($this->database->tableExists($this->table))
            throw new \Exception("Table {$this->table} already exists.");

        $this->database->data[$this->table] = [];

        $this->database->config[$this->table] = [
            'auto_increment' => 1,
            'columns' => []
        ];

        foreach($data as $column => $settings)
            $this->tableCreateColumn($column, $settings);

        $this->database->save();
        return true;

    }

    /**
     * Checks to see if the column exists.
     *
     * @param $column
     * @return bool
     * @throws \Exception
     */
    public function columnExists($column)
    {
        if(!$this->database->tableExists($this->table))
            throw new Exception("Table {$this->table} doesn't exist.");

        return array_key_exists($column, $this->database->config[$this->table]['columns']);
    }

    /**
     * Creates a table column.
     *
     * @param $column
     * @param $default
     * @return bool
     * @throws \Exception
     */
    public function addColumn($column, $default)
    {
        $this->tableCreateColumn($column, $default);

        foreach($this->database->data as $id => $data)
            $this->database->data[$this->table][$id][$column] = $default;

        $this->database->save();

        return true;
    }

    /**
     * Creates a column in the given table.
     *
     * @param $column
     * @param null $default
     * @throws \Exception
     */
    protected function tableCreateColumn($column, $default = null)
    {
        if(!$this->database->tableExists($this->table))
            throw new Exception("Table {$this->table} doesn't exist.");

        if($this->columnExists($column))
            throw new Exception("Column {$column} already exists.");

        $this->database->config[$this->table]['columns'][$column] = $default;
    }
}