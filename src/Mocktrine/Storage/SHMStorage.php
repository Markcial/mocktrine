<?php

namespace Mocktrine\Storage;

use Mocktrine\Exception;
use Mocktrine\Storage\SHM\MemoryResource;

/**
 * Class SHMStorage
 * @package Mocktrine\Storage
 */
class SHMStorage implements IStorage
{
    // reserved memory variables
    const RESERVED_KEY_POINTER = 0;
    const RESERVED_KEY_MAP = 1;
    private $sharedResource;

    private $identifier;

    public $map = array();

    public $counter;

    public function __construct(MemoryResource $memoryResource = null)
    {
        if (is_null($memoryResource)) {
            $memoryResource = new MemoryResource();
        }
        $this->sharedResource = $memoryResource->getFilePath();
        $this->identifier = $memoryResource->getFtOk();
        $this->resource = shm_attach($this->identifier);
        // load default vars
        $this->setup();
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    public function getSharedResource()
    {
        return $this->sharedResource;
    }

    // private shortcuts
    public function has($key)
    {
        if (array_key_exists($key, $this->map)) {
            $index = $this->map[$key];

            return shm_has_var($this->resource, $index);
        }

        return false;
    }

    public function get($key)
    {
        $this->refresh();
        if (array_key_exists($key, $this->map)) {
            return shm_get_var($this->resource, $this->map[$key]);
        }

        return false;
    }

    private function setup()
    {
        $this->counter = 3;
        $this->map = array();

        $this->refresh();
    }

    public function refresh()
    {
        if (shm_has_var($this->resource, self::RESERVED_KEY_POINTER)) {
            $this->counter = shm_get_var($this->resource, self::RESERVED_KEY_POINTER);
        }
        if (shm_has_var($this->resource, self::RESERVED_KEY_MAP)) {
            $this->map = shm_get_var($this->resource, self::RESERVED_KEY_MAP);
        }
    }

    public function persist()
    {
        shm_put_var($this->resource, self::RESERVED_KEY_POINTER, $this->counter);
        shm_put_var($this->resource, self::RESERVED_KEY_MAP, $this->map);
    }

    public function save($key, $obj, $overwrite = false)
    {
        if (array_key_exists($key, $this->map) && shm_has_var($this->resource, $this->map[$key]) && !$overwrite) {
            // needs to warn
            return;
        }

        $this->counter++;
        $this->map[$key] = $this->counter;
        shm_put_var($this->resource, $this->map[$key], $obj);

        $this->persist();
    }

    public function __destruct()
    {
        shm_detach($this->resource);
    }

    public function delete($key)
    {
        shm_remove_var($this->resource, $key);
    }

    public function flush()
    {
        shm_remove($this->resource);
    }
}
