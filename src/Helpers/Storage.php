<?php namespace Dan\Helpers; 


use League\Flysystem\Exception;

class Storage {

    /** @var DotCollection[] $data */
    protected $data = [];

    protected $file = "database.json";


    /**
     * @param $table
     * @return mixed
     */
    public function create($table)
    {
        $this->data[$table] = new DotCollection();

        $this->save();

        return $this->data[$table];
    }

    /**
     * @param $table
     * @return DotCollection
     */
    public function table($table)
    {
        return $this->data[$table];
    }

    /**
     *
     */
    public function save()
    {
        filesystem()->put(STORAGE_DIR . "/{$this->file}", $this->jsonify());
    }

    /**
     *
     */
    public function load()
    {
        $json = filesystem()->get(STORAGE_DIR . "/{$this->file}");

        $tables = json_decode($json, true);

        if(!is_array($tables))
            throw new Exception("Database file corrupted.");

        foreach($tables as $table => $data)
            $this->data[$table] = new DotCollection($data);

        unset($tables);
    }

    /**
     * @return string
     */
    protected function jsonify()
    {
        $data = [];

        foreach($this->data as $key => $table)
            $data[$key] = $table->toArray();

        return json_encode($data, JSON_PRETTY_PRINT);
    }
}