<?php namespace Dan\Database;


use \Exception;

class Database {

    protected $file;

    protected $data = [];

    protected $config = [];


    /**
     * @param $file
     */
    public function __construct($file)
    {
        $this->file = $file;
    }

    /**
     * Checks to see if the table exists.
     *
     * @param $table
     * @return bool
     */
    public function exists($table)
    {
        return array_key_exists($table, $this->data);
    }

    /**
     * Inserts an entry.
     *
     * @param string $table
     * @param array $values
     * @return bool
     * @throws \Exception
     */
    public function insert($table, array $values)
    {
        if(!$this->exists($table))
            throw new Exception("Table {$table} doesn't exist.");

        $id = $this->config[$table]['auto_increment'];

        $this->data[$table][$id] = ['id' => $id];

        $values = array_merge($this->config[$table]['columns'], $values);

        foreach($values as $column => $value)
        {
            if(!$this->columnExists($table, $column))
                throw new Exception("Column {$column} doesn't exist in {$table}");

            $this->data[$table][$id][$column] = $value;
        }

        $this->config[$table]['auto_increment']++;
        $this->save();
        return true;
    }

    /**
     * Updates an entry.
     *
     * @param string $table
     * @param array $where
     * @param array $values
     * @return bool
     * @throws \Exception
     */
    public function update($table, array $where, array $values)
    {
        if(!$this->exists($table))
            throw new Exception("Table {$table} doesn't exist.");

        $id = $this->rowOffsetWhere($table, key($where), current($where));

        if($id === 0)
            return false;

        foreach($values as $column => $value)
        {
            if(!$this->columnExists($table, $column))
                throw new Exception("Column {$column} doesn't exist in {$table}");

            $this->data[$table][$id][$column] = $value;
        }

        $this->save();
        return true;
    }

    /**
     * Inserts or updates a row.
     *
     * @param $table
     * @param array $where
     * @param array $values
     * @return bool
     * @throws \Exception
     */
    public function insertOrUpdate($table, array $where, array $values)
    {
        if($this->has($table, key($where), current($where)))
            return $this->update($table, $where, $values);

        return $this->insert($table, $values);
    }

    /**
     * Deletes a row in a table.
     *
     * @param $table
     * @param array $where
     * @return bool
     * @throws \Exception
     */
    public function delete($table, array $where)
    {
        $id = $this->rowOffsetWhere($table, key($where), current($where));

        if($id === 0)
            return false;

        unset($this->data[$id]);

        return true;
    }

    /**
     * Checks to see if something exists in the database by column and value.
     *
     * @param $table
     * @param $column
     * @param $value
     * @return bool
     */
    public function has($table, $column, $value)
    {
        return $this->rowOffsetWhere($table, $column, $value) !== 0;
    }

    /**
     * Gets rows from a table.
     *
     * @param $table
     * @param array $where
     * @return null
     * @throws \Exception
     */
    public function get($table, array $where = [])
    {
        if(empty($where))
            return $this->data[$table];

        $id = $this->rowOffsetWhere($table, key($where), current($where));

        if($id === 0)
            return null;

        return $this->data[$table][$id];
    }

    /**
     * Creates a table.
     *
     * @param $table
     * @param $data
     * @return bool
     * @throws \Exception
     */
    public function create($table, $data)
    {
        if($this->exists($table))
            throw new Exception("Table {$table} already exists.");

        $this->data[$table] = [];

        $this->config[$table] = [
            'auto_increment' => 1,
            'columns' => []
        ];

        foreach($data as $column => $settings)
        {
            $this->tableCreateColumn($table, $column, $settings);
        }

        $this->save();

        return true;
    }

    /**
     * Increments a column.
     *
     * @param $table
     * @param $where
     * @param $column
     * @return bool
     * @throws \Exception
     */
    public function increment($table, $where, $column)
    {
        if(!$this->exists($table))
            throw new Exception("Table {$table} doesn't exist.");

        $id = $this->rowOffsetWhere($table, key($where), current($where));

        if($id === 0)
            return false;

        if(!$this->columnExists($table, $column))
            throw new Exception("Column {$column} doesn't exist.");

        if(!is_int($this->data[$table][$id][$column]))
            throw new Exception("Column {$column} isn't an integer.");

        $this->data[$table][$id][$column]++;

        $this->save();

        return true;
    }

    /**
     * Checks to see if a column exists.
     *
     * @param $table
     * @param $column
     * @return bool
     * @throws \Exception
     */
    public function columnExists($table, $column)
    {
        if(!$this->exists($table))
            throw new Exception("Table {$table} doesn't exist.");

        return array_key_exists($column, $this->config[$table]['columns']);
    }

    /**
     * Adds a column to the given table.
     *
     * @param $table
     * @param $column
     * @param null $default
     * @throws \Exception
     */
    public function addColumn($table, $column, $default = null)
    {
        $this->tableCreateColumn($table, $column, $default);
    }

    //------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------
    //------------------------------------------------------------------------------------------------------------------

    protected function tableCreateColumn($table, $column, $default = null)
    {
        if(!$this->exists($table))
            throw new Exception("Table {$table} doesn't exist.");

        if($this->columnExists($table, $column))
            throw new Exception("Column {$column} already exists.");

        $this->config[$table]['columns'][$column] = $default;
    }


    /**
     * Checks to see if a row exists by column and value.
     *
     * @param $table
     * @param $column
     * @param $value
     * @return int|string
     * @throws \Exception
     */
    protected function rowOffsetWhere($table, $column, $value)
    {
        if(!$this->columnExists($table, $column))
            throw new Exception("Column {$column} doesn't exist in {$table}");

        $tableData = $this->data[$table];

        foreach ($tableData as $id => $data)
            if($data[$column] == $value)
                return $id;

        return 0;
    }

    /**
     * Loads the database.
     */
    public function load()
    {
        if(!filesystem()->exists(STORAGE_DIR . "/{$this->file}.json"))
            $this->save();

        $database = json_decode(filesystem()->get(STORAGE_DIR . "/{$this->file}.json"), true);

        if(!is_array($database))
            throw new Exception("Database {$this->file} is corrupted.");

        $this->config = $database['config'];
        $this->data   = $database['data'];
    }

    /**
     * Saves the database.
     */
    public function save()
    {
        filesystem()->put(STORAGE_DIR . "/{$this->file}.json", json_encode(['data' => $this->data, 'config' => $this->config], JSON_PRETTY_PRINT));
    }
}