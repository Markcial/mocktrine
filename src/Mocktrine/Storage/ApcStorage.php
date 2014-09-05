<?php


namespace Mocktrine\Storage;


/**
 * Class ApcStorage
 * @package Mocktrine\Storage
 */
class ApcStorage implements IStorage
{
    public function save($key, $value, $overwrite = false)
    {
        if ($overwrite) {
            apc_store($key, $value);
        }

        return false;
    }

    public function get($key)
    {
        if ($this->has($key)) {
            return apc_fetch($key);
        }

        return false;
    }

    public function has($key)
    {
        return apc_exists($key);
    }

    public function delete($key)
    {
        apc_delete($key);
    }

    public function flush()
    {
        apc_clear_cache();
    }

    public function persist()
    {
        // it is already a persisted engine
        return true;
    }
}
