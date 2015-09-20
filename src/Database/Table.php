<?php namespace Dan\Database;


use \Exception;
use Illuminate\Support\Collection;

class Table {

    protected $table;

    protected $database;

    /**
     * @var array
     */
    private $data;

    public function __construct(Database $database, $table, $data = [])
    {
        $this->table = $table;
        $this->database = $database;
        $this->data = $data;
    }

    /**
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public function insert(array $data)
    {
        $id = $this->database->config[$this->table]['auto_increment'];

        $this->database->data[$this->table][$id] = ['id' => $id];

        $values = array_merge($this->database->config[$this->table]['columns'], $data);

        foreach($values as $column => $value)
        {
            if(!$this->database->schema($this->table)->columnExists($column))
                throw new Exception("Column {$column} doesn't exist in {$this->table}");

            $this->database->data[$this->table][$id][$column] = $value;
        }

        $this->database->config[$this->table]['auto_increment']++;
        $this->database->save();
        return true;
    }

    /**
     * @param array $values
     * @return bool
     * @throws \Exception
     */
    public function update(array $values)
    {
        if(isset($values['id']))
            unset($values['id']);

        foreach($this->getItems() as $id => $data)
        {
            $new = $data;

            foreach($values as $key => $value)
            {
                if(!$this->database->schema($this->table)->columnExists($key))
                    throw new Exception("Column {$key} doesn't exist in {$this->table}");

                if(is_array($this->database->config[$this->table]['columns'][$key]))
                {
                    if(!is_array($value))
                        throw new Exception("Column {$key} is an array, value given is not.");

                    if(isset($new[$key]))
                        $new[$key] = array_merge($new[$key], $value);
                    else
                        $new[$key] = $value;
                }
                else
                    $new[$key] = $value;
            }

            $this->database->data[$this->table][$id] = $new;
        }

        $this->database->save();
        return true;
    }

    /**
     * Inserts or updates a value.
     *
     * @param array $where
     * @param array $values
     * @return bool
     * @throws \Exception
     */
    public function insertOrUpdate(array $where, array $values)
    {
        $whereQuery = $this->where(...$where);

        if($whereQuery->count())
            return $whereQuery->update($values);

        return $this->insert($values);
    }

    /**
     * Deletes row(s) from the table.
     */
    public function delete()
    {
        foreach($this->getItems() as $id => $data)
            unset($this->database->data[$id]);

        $this->database->save();
    }

    /**
     * Gets information where column is value.
     *
     * @param $column
     * @param $is
     * @param null $value
     * @return $this
     */
    public function where($column, $is, $value = null)
    {
        if($value == null)
        {
            $value = $is;
            $is = '=';
        }

        $items = $this->rowOffsetWhere($column, $is, $value);

        return new static($this->database, $this->table, $items);
    }

    /**
     * @param $column
     * @throws \Exception
     */
    public function increment($column)
    {
        if(!$this->database->schema($this->table)->columnExists($column))
            throw new Exception("Column {$column} doesn't exist.");

        if(!is_int($this->database->config[$this->table]['columns'][$column]))
            throw new Exception("Column {$column} isn't an integer.");

        foreach($this->getItems() as $id => $data)
            $this->database->data[$this->table][$id][$column]++;
    }

    /**
     * Gets all data.
     *
     * @return Collection
     */
    public function get()
    {
        return new Collection($this->getItems());
    }

    /**
     * Gets the first item found.
     *
     * @return Collection|null
     */
    public function first()
    {
        if(empty($this->getItems()))
            return new Collection([]);

        $items = $this->getItems();

        $item = reset($items);

        if($item === false)
            return new Collection([]);

        return new Collection($item);
    }

    /**
     * Gets a value of the first result.
     *
     * @param $column
     * @return mixed
     * @throws \Exception
     */
    public function value($column)
    {
        if(!$this->database->schema($this->table)->columnExists($column))
            throw new Exception("Column {$column} doesn't exist.");

        return $this->first()[$column];
    }

    /**
     * Returns the count.
     *
     * @return int
     */
    public function count()
    {
        return count($this->getItems());
    }

    /**
     * Gets all items with the given information.
     *
     * @param $column
     * @param $is
     * @param $value
     * @return array
     * @throws \Exception
     */
    protected function rowOffsetWhere($column, $is, $value)
    {
        if(!$this->database->schema($this->table)->columnExists($column))
            throw new Exception("Column {$column} doesn't exist.");

        $items = [];

        foreach($this->getItems() as $id => $data)
            if(Compare::is(strtolower($data[$column]), $is, strtolower($value)))
                $items[$id] = $data;

        return $items;
    }

    /**
     * @return array
     */
    protected function getItems()
    {
        return $this->data;
    }
}