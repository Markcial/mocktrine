<?php

namespace Mocktrine;

use Doctrine\Common\Persistence\ObjectRepository;
use Mocktrine\Pool;

/**
 * Class Repository
 * @package Mocktrine
 */
abstract class Repository implements ObjectRepository
{
    private $shortName;
    private $package;
    private $fullName;

    public function __construct($class, Pool $pool = null)
    {
        if (is_null($pool)) {
            $pool = new Pool();
        }

        $this->database = $pool->getStorage();

        $refl = new \ReflectionClass($class);
        $this->shortName = $refl->getShortName();
        $this->package = $refl->getNamespaceName();
        $this->fullName = $refl->getName();
    }

    /**
     * Finds an object by its primary key / identifier.
     *
     * @param mixed $id The identifier.
     *
     * @return object The object.
     */
    public function find($id)
    {
        $key = sprintf('%s:%d', strtolower($this->shortName), $id);
        $entity = $this->database->get($key);
        if ($entity) {
            $mocked = \Mockery::mock($entity, array(
                'getId' => $id
            ))->makePartial();
        } else {
            $class = $this->fullName;
            $mocked = \Mockery::mock($class, array(
                'getId' => $id
            ))->makePartial();
        }
        return $mocked;
    }

    /**
     * Finds all objects in the repository.
     *
     * @return array The objects.
     */
    public function findAll()
    {
        return false;
    }

    /**
     * Finds objects by a set of criteria.
     *
     * Optionally sorting and limiting details can be passed. An implementation may throw
     * an UnexpectedValueException if certain values of the sorting or limiting details are
     * not supported.
     *
     * @param array $criteria
     * @param array|null $orderBy
     * @param int|null $limit
     * @param int|null $offset
     *
     * @return array The objects.
     *
     * @throws \UnexpectedValueException
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        return false;
    }

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria The criteria.
     *
     * @return object The object.
     */
    public function findOneBy(array $criteria)
    {
        return false;
    }

    /**
     * Returns the class name of the object managed by the repository.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->fullName;
    }
}