<?php

namespace Dan\Database;

use Exception;
use Illuminate\Support\Collection;

class Table
{
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
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function insert(array $data)
    {
        $id = $this->database->config[$this->table]['auto_increment'];

        $this->database->data[$this->table][$id] = ['id' => $id];

        $values = array_merge($this->database->config[$this->table]['columns'], $data);

        foreach ($values as $column => $value) {
            if (!$this->database->schema($this->table)->columnExists($column)) {
                throw new Exception("Column {$column} doesn't exist in {$this->table}");
            }

            $this->database->data[$this->table][$id][$column] = $value;
        }

        $this->database->config[$this->table]['auto_increment']++;
        $this->database->save();

        return true;
    }

    /**
     * @param array $values
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function update(array $values)
    {
        if (isset($values['id'])) {
            unset($values['id']);
        }

        foreach ($this->getItems() as $id => $data) {
            $new = $data;

            foreach ($values as $key => $value) {
                if (!$this->database->schema($this->table)->columnExists($key)) {
                    throw new Exception("Column {$key} doesn't exist in {$this->table}");
                }

                if (is_array($this->database->config[$this->table]['columns'][$key])) {
                    if (!is_array($value)) {
                        throw new Exception("Column {$key} is an array, value given is not.");
                    }

                    $new[$key] = $value;
                } else {
                    if (is_string($this->database->config[$this->table]['columns'][$key])) {
                        if (is_array($value)) {
                            throw new Exception("Column {$key} is a string, value given is an array.");
                        }
                    }

                    $new[$key] = $value;
                }
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
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function insertOrUpdate(array $where, array $values)
    {
        $whereQuery = $this->where(...$where);

        if ($whereQuery->count()) {
            return $whereQuery->update($values);
        }

        return $this->insert($values);
    }

    /**
     * Deletes row(s) from the table.
     */
    public function delete()
    {
        foreach ($this->getItems() as $id => $data) {
            unset($this->database->data[$this->table][$id]);
        }

        $this->database->save();
    }

    /**
     * Gets information where column is value.
     *
     * @param $column
     * @param $is
     * @param null $value
     *
     * @return Table
     */
    public function where($column, $is, $value = null) : Table
    {
        if ($value == null) {
            $value = $is;
            $is = '=';
        }

        $items = $this->rowOffsetWhere($column, $is, $value);

        return new static($this->database, $this->table, $items);
    }

    /**
     * @param $column
     *
     * @throws \Exception
     */
    public function increment($column)
    {
        if (!$this->database->schema($this->table)->columnExists($column)) {
            throw new Exception("Column {$column} doesn't exist.");
        }

        if (!is_int($this->database->config[$this->table]['columns'][$column])) {
            throw new Exception("Column {$column} isn't an integer.");
        }

        foreach ($this->getItems() as $id => $data) {
            $this->database->data[$this->table][$id][$column]++;
        }
    }

    /**
     * Gets all data.
     *
     * @return Collection
     */
    public function get() : Collection
    {
        return new Collection($this->getItems());
    }

    /**
     * Gets the first item found.
     *
     * @return Collection
     */
    public function first() : Collection
    {
        if (empty($this->getItems())) {
            return new Collection([]);
        }

        $items = $this->getItems();

        $item = reset($items);

        if ($item === false) {
            return new Collection([]);
        }

        return new Collection($item);
    }

    /**
     * Gets a value of the first result.
     *
     * @param $column
     *
     * @throws \Exception
     *
     * @return mixed
     */
    public function value($column)
    {
        if (!$this->database->schema($this->table)->columnExists($column)) {
            throw new Exception("Column {$column} doesn't exist.");
        }

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
     *
     * @throws \Exception
     *
     * @return array
     */
    protected function rowOffsetWhere($column, $is, $value)
    {
        if (!$this->database->schema($this->table)->columnExists($column)) {
            throw new Exception("Column {$column} doesn't exist.");
        }

        $items = [];

        foreach ($this->getItems() as $id => $data) {
            if (is_array($value) || is_array($data[$column])) {
                if (!in_array($is, ['=', '!='])) {
                    throw new Exception('Only != and = can apply to array values.');
                }

                $current = $data[$column];
            } else {
                $current = strtolower($data[$column]);
                $value = strtolower($value);
            }

            if (Compare::is($current, $is, $value)) {
                $items[$id] = $data;
            }
        }

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
