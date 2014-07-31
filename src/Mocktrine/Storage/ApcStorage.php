<?php


namespace Mocktrine\Storage;


/**
 * Class ApcStorage
 * @package Mocktrine\Storage
 */
class ApcStorage implements IStorage
{
    public function __construct()
    {
        //ini_set('apc.enable_cli', true);
    }

    public function save($key, $value)
    {
        var_dump($key, $value);
        apc_store($key, $value);
        var_dump(apc_fetch($key), $value);
        die;
    }

    public function get($key)
    {
        var_dump(apc_fetch($key));
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