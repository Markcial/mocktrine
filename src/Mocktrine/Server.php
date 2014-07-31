<?php

namespace Mocktrine;

use Mocktrine\Pool;

/**
 * Class Server
 * @package Mocktrine
 */
class Server
{
    const REFRESH_POOL_INTERVAL = 5000;
    protected $lazy = true;
    protected $pool;
    protected $serviceLocator;

    public function __construct(Pool $pool = null)
    {
        if (is_null($pool)) {
            $pool = new Pool();
        }
        $this->pool = $pool;
    }

    public function getPool()
    {
        return $this->pool;
    }

    public function serve()
    {
        while (true) {
            if (!$this->lazy) {
                $this->pool->getStorage()->refresh();
            }
            sleep(self::REFRESH_POOL_INTERVAL);
        }
    }
}