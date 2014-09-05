<?php

namespace Mocktrine;

use Mocktrine\Storage\ApcStorage;
use Mocktrine\Storage\IStorage;
use Mocktrine\Storage\SHMStorage;

/**
 * Class Pool
 * @package Mocktrine
 */
class Pool
{
    public function __construct(IStorage $storage = null)
    {
        // apc by default
        if (is_null($storage)) {
            $storage = new ApcStorage();
        }
        $this->storage = $storage;
    }

    public function getStorage()
    {
        return $this->storage;
    }
}
