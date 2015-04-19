<?php namespace Dan\Storage; 


use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class Storage {

    protected $filesystem;
    protected $file;
    /** @var Collection  */
    protected $data;

    /**
     * Loads a storage file.
     *
     * @param $file
     * @return static
     */
    public static function load($file)
    {
        return new static($file);
    }

    /**
     * @param $file
     */
    public function __construct($file)
    {
        $this->file = $file;
        $this->filesystem = new Filesystem();

        $this->loadFile();
    }

    /**
     * Saves the data as JSON format.
     */
    public function save()
    {
        $this->filesystem->put(STORAGE_DIR . '/database/' . $this->file . '.json', json_encode($this->data->toArray(), JSON_PRETTY_PRINT));
    }

    /**
     * Loads the file.
     *
     * @throws \Illuminate\Filesystem\FileNotFoundException
     */
    public function loadFile()
    {
        $file = STORAGE_DIR . '/database/' . $this->file . '.json';

        if(!$this->filesystem->exists($file))
        {
            $this->filesystem->put($file, json_encode([]));
            $this->data = new Collection();

            return;
        }

        $data = $this->filesystem->get($file);
        $json = json_decode($data, true);

        if($json == null)
            $json = [];

        $this->data = new Collection($json);
    }

    /**
     * Gets a row from the storage.
     *
     * @param $key
     * @return array|null
     */
    public function get($key = null)
    {
        if($key == null)
            return $this->data->toArray();

        return $this->data->get($key, null);
    }

    /**
     * Adds a row to the storage.
     *
     * @param $key
     * @param $value
     * @return $this
     */
    public function add($key, $value)
    {
        $this->data->put($key, $value);

        return $this;
    }

    /**
     * Checks to see if a row exists.
     *
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return $this->data->has($key);
    }

    /**
     * Removes a row from the storage.
     *
     * @param $key
     * @return $this
     */
    public function remove($key)
    {
        $this->data->forget($key);

        return $this;
    }
}