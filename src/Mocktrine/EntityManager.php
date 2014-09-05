<?php

namespace Mocktrine;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\PessimisticLockException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Mocktrine\Mapping\ClassMetadataFactory;
use Mocktrine\Storage\IStorage;
use Mocktrine\Storage\PHPThreadStorage;
use Mocktrine\Storage\Table;

/**
 * Class EntityManager
 * @package Mocktrine
 */
class EntityManager implements EntityManagerInterface
{
    /**
     * @var IStorage
     */
    protected $database;

    protected $repositories;

    protected $pool;

    protected $tables = array();

    protected $annotationReader;

    public function __construct(Pool $pool = null)
    {
        if (is_null($this->pool)) {
            $this->pool = new Pool();
        }

        $this->getConnection();
    }

    /**
     * @param $class
     * @return Table
     */
    public function &getTable($class)
    {
        if (array_key_exists($class, $this->tables)) {
            return $this->tables[$class];
        }

        $key = sprintf('table:%s', $class);
        if ($this->database->has($key)) {
            $this->tables[$class] = $this->database->get($key);
        } else {
            $this->tables[$class] = new Table($this->getClassMetadata($class), $this);
        }

        return $this->tables[$class];
    }

    /**
     * Returns the cache API for managing the second level cache regions or NULL if the cache is not enabled.
     *
     * @return \Doctrine\ORM\Cache|null
     */
    public function getCache()
    {
        // TODO: Implement getCache() method.
    }

    /**
     * Gets the database connection object used by the EntityManager.
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function getConnection()
    {
        $this->database = $this->pool->getStorage();
    }

    /**
     * Gets an ExpressionBuilder used for object-oriented construction of query expressions.
     *
     * Example:
     *
     * <code>
     *     $qb = $em->createQueryBuilder();
     *     $expr = $em->getExpressionBuilder();
     *     $qb->select('u')->from('User', 'u')
     *         ->where($expr->orX($expr->eq('u.id', 1), $expr->eq('u.id', 2)));
     * </code>
     *
     * @return \Doctrine\ORM\Query\Expr
     */
    public function getExpressionBuilder()
    {
        // TODO: Implement getExpressionBuilder() method.
    }

    /**
     * Starts a transaction on the underlying database connection.
     *
     * @return void
     */
    public function beginTransaction()
    {
        // volatile engine, why the fuck would you need transactions?
    }

    /**
     * Executes a function in a transaction.
     *
     * The function gets passed this EntityManager instance as an (optional) parameter.
     *
     * {@link flush} is invoked prior to transaction commit.
     *
     * If an exception occurs during execution of the function or flushing or transaction commit,
     * the transaction is rolled back, the EntityManager closed and the exception re-thrown.
     *
     * @param callable $func The function to execute transactionally.
     *
     * @return mixed The non-empty value returned from the closure or true instead.
     */
    public function transactional($func)
    {
        // volatile engine, why the fuck would you need transactions?
    }

    /**
     * Commits a transaction on the underlying database connection.
     *
     * @return void
     */
    public function commit()
    {
        foreach ($this->tables as $table) {
            $this->database->save(
                sprintf('table:%s', $table->getFQDN()),
                $table,
                IStorage::OVERWRITE
            );
        }
    }

    /**
     * Performs a rollback on the underlying database connection.
     *
     * @return void
     */
    public function rollback()
    {
        /**
         * @var Table $table
         */
        foreach ($this->tables as $table) {
            $this->refresh($table->getFQDN());
        }
    }

    /**
     * Creates a new Query object.
     *
     * @param string $dql The DQL string.
     *
     * @return Query
     */
    public function createQuery($dql = '')
    {
        // TODO: Implement createQuery() method.
    }

    /**
     * Creates a Query from a named query.
     *
     * @param string $name
     *
     * @return Query
     */
    public function createNamedQuery($name)
    {
        // TODO: Implement createNamedQuery() method.
    }

    /**
     * Creates a native SQL query.
     *
     * @param string $sql
     * @param ResultSetMapping $rsm The ResultSetMapping to use.
     *
     * @return NativeQuery
     */
    public function createNativeQuery($sql, ResultSetMapping $rsm)
    {
        // TODO: Implement createNativeQuery() method.
    }

    /**
     * Creates a NativeQuery from a named native query.
     *
     * @param string $name
     *
     * @return NativeQuery
     */
    public function createNamedNativeQuery($name)
    {
        // TODO: Implement createNamedNativeQuery() method.
    }

    /**
     * Create a QueryBuilder instance
     *
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        // TODO: Implement createQueryBuilder() method.
    }

    /**
     * Gets a reference to the entity identified by the given type and identifier
     * without actually loading it, if the entity is not yet loaded.
     *
     * @param string $entityName The name of the entity type.
     * @param mixed $id The entity identifier.
     *
     * @return object The entity reference.
     *
     * @throws ORMException
     */
    public function getReference($entityName, $id)
    {
        // TODO: Implement getReference() method.
    }

    /**
     * Gets a partial reference to the entity identified by the given type and identifier
     * without actually loading it, if the entity is not yet loaded.
     *
     * The returned reference may be a partial object if the entity is not yet loaded/managed.
     * If it is a partial object it will not initialize the rest of the entity state on access.
     * Thus you can only ever safely access the identifier of an entity obtained through
     * this method.
     *
     * The use-cases for partial references involve maintaining bidirectional associations
     * without loading one side of the association or to update an entity without loading it.
     * Note, however, that in the latter case the original (persistent) entity data will
     * never be visible to the application (especially not event listeners) as it will
     * never be loaded in the first place.
     *
     * @param string $entityName The name of the entity type.
     * @param mixed $identifier The entity identifier.
     *
     * @return object The (partial) entity reference.
     */
    public function getPartialReference($entityName, $identifier)
    {
        // TODO: Implement getPartialReference() method.
    }

    /**
     * Closes the EntityManager. All entities that are currently managed
     * by this EntityManager become detached. The EntityManager may no longer
     * be used after it is closed.
     *
     * @return void
     */
    public function close()
    {
        // TODO: Implement close() method.
    }

    /**
     * Creates a copy of the given entity. Can create a shallow or a deep copy.
     *
     * @param object $entity The entity to copy.
     * @param boolean $deep FALSE for a shallow copy, TRUE for a deep copy.
     *
     * @return object The new entity.
     *
     * @throws \BadMethodCallException
     */
    public function copy($entity, $deep = false)
    {
        // TODO: Implement copy() method.
    }

    /**
     * Acquire a lock on the given entity.
     *
     * @param object $entity
     * @param int $lockMode
     * @param int|null $lockVersion
     *
     * @return void
     *
     * @throws OptimisticLockException
     * @throws PessimisticLockException
     */
    public function lock($entity, $lockMode, $lockVersion = null)
    {
        // TODO: Implement lock() method.
    }

    /**
     * Gets the EventManager used by the EntityManager.
     *
     * @return \Doctrine\Common\EventManager
     */
    public function getEventManager()
    {
        // TODO: Implement getEventManager() method.
    }

    /**
     * Gets the Configuration used by the EntityManager.
     *
     * @return Configuration
     */
    public function getConfiguration()
    {
        // TODO: Implement getConfiguration() method.
    }

    /**
     * Check if the Entity manager is open or closed.
     *
     * @return bool
     */
    public function isOpen()
    {
        // TODO: Implement isOpen() method.
    }

    /**
     * Gets the UnitOfWork used by the EntityManager to coordinate operations.
     *
     * @return UnitOfWork
     */
    public function getUnitOfWork()
    {
        // TODO: Implement getUnitOfWork() method.
    }

    /**
     * Gets a hydrator for the given hydration mode.
     *
     * This method caches the hydrator instances which is used for all queries that don't
     * selectively iterate over the result.
     *
     * @deprecated
     *
     * @param int $hydrationMode
     *
     * @return \Doctrine\ORM\Internal\Hydration\AbstractHydrator
     */
    public function getHydrator($hydrationMode)
    {
        // TODO: Implement getHydrator() method.
    }

    /**
     * Create a new instance for the given hydration mode.
     *
     * @param int $hydrationMode
     *
     * @return \Doctrine\ORM\Internal\Hydration\AbstractHydrator
     *
     * @throws ORMException
     */
    public function newHydrator($hydrationMode)
    {
        // TODO: Implement newHydrator() method.
    }

    /**
     * Gets the proxy factory used by the EntityManager to create entity proxies.
     *
     * @return \Doctrine\ORM\Proxy\ProxyFactory
     */
    public function getProxyFactory()
    {
        // TODO: Implement getProxyFactory() method.
    }

    /**
     * Gets the enabled filters.
     *
     * @return \Doctrine\ORM\Query\FilterCollection The active filter collection.
     */
    public function getFilters()
    {
        // TODO: Implement getFilters() method.
    }

    /**
     * Checks whether the state of the filter collection is clean.
     *
     * @return boolean True, if the filter collection is clean.
     */
    public function isFiltersStateClean()
    {
        // TODO: Implement isFiltersStateClean() method.
    }

    /**
     * Checks whether the Entity Manager has filters.
     *
     * @return boolean True, if the EM has a filter collection.
     */
    public function hasFilters()
    {
        // TODO: Implement hasFilters() method.
    }

    /**
     * Finds an object by its identifier.
     *
     * This is just a convenient shortcut for getRepository($className)->find($id).
     *
     * @param string $className The class name of the object to find.
     * @param mixed $id The identity of the object to find.
     *
     * @throws Exception
     * @return object The found object.
     */
    public function find($className, $id)
    {
        $table = $this->getTable($className);

        if ($result = $table->getByIdentifier($id)) {
            return $result;
        }

        throw new Exception(sprintf("Entity '%s' with identifier : %s, not found", $className, $id));
    }

    /**
     * Tells the ObjectManager to make an instance managed and persistent.
     *
     * The object will be entered into the database as a result of the flush operation.
     *
     * NOTE: The persist operation always considers objects that are not yet known to
     * this ObjectManager as NEW. Do not pass detached objects to the persist operation.
     *
     * @param object $object The instance to make managed and persistent.
     *
     * @return void
     */
    public function persist($object)
    {
        $data = [];
        $className = get_class($object);
        $table = $this->getTable($className);
        $metadata = $this->getClassMetadata($className);
        $identifiers = $metadata->getIdentifier();
        $keys = array();
        foreach ($identifiers as $identifier) {
            $getter = sprintf('get%s', $identifier);
            $keys[$identifier] = call_user_func(array($object, $getter));
        }

        foreach ($metadata->getFieldNames() as $field) {
            $getter = sprintf('get%s', ucfirst($field));
            $data[$field] = call_user_func(array($object, $getter));
        }

        foreach ($metadata->getAssociationNames() as $assoc) {
            $associationClass = $metadata->getAssociationTargetClass('roles');
            $foreignMetadata = $this->getClassMetadata($associationClass);
            $identifiers = $foreignMetadata->getIdentifier();
            $getter = sprintf('get%s', ucfirst($assoc));
            $associations = call_user_func(array($object, $getter));
            if ($associations instanceof Collection) {
                foreach ($associations as $association) {
                    foreach ($identifiers as $id) {
                        $fgetter = sprintf('get%s', $id);
                        $data[$assoc][$id][] = call_user_func(array($association, $fgetter));
                    }
                    $this->persist($association);
                }
            } else {
                foreach ($identifiers as $id) {
                    $fgetter = sprintf('get%s', $id);
                    $data[$assoc][$id] = call_user_func(array($associations, $fgetter));
                }
                $this->persist($associations);
            }
        }

        $table->addEntity($keys, $data);
        $this->database->persist();
    }

    /**
     * Removes an object instance.
     *
     * A removed object will be removed from the database as a result of the flush operation.
     *
     * @param object $object The object instance to remove.
     *
     * @return void
     */
    public function remove($object)
    {
        // delete entity
        // TODO: Implement remove() method.
    }

    /**
     * Merges the state of a detached object into the persistence context
     * of this ObjectManager and returns the managed copy of the object.
     * The object passed to merge will not become associated/managed with this ObjectManager.
     *
     * @param object $object
     *
     * @return object
     */
    public function merge($object)
    {
        // TODO: Implement merge() method.
    }

    /**
     * Clears the ObjectManager. All objects that are currently managed
     * by this ObjectManager become detached.
     *
     * @param string|null $objectName if given, only objects of this type will get detached.
     *
     * @return void
     */
    public function clear($objectName = null)
    {
        // TODO: Implement clear() method.
    }

    /**
     * Detaches an object from the ObjectManager, causing a managed object to
     * become detached. Unflushed changes made to the object if any
     * (including removal of the object), will not be synchronized to the database.
     * Objects which previously referenced the detached object will continue to
     * reference it.
     *
     * @param object $object The object to detach.
     *
     * @return void
     */
    public function detach($object)
    {
        // TODO: Implement detach() method.
    }

    /**
     * Refreshes the persistent state of an object from the database,
     * overriding any local changes that have not yet been persisted.
     *
     * @param object $object The object to refresh.
     *
     * @return void
     */
    public function refresh($object)
    {
        $class = get_class($object);
        $key = sprintf('table:%s', $class);
        if ($this->database->has($key)) {
            $this->tables[$class] = $this->database->get($key);
        } else {
            $this->tables[$class] = new Table($this->getClassMetadata($class), $this);
        }
    }

    /**
     * Flushes all changes to objects that have been queued up to now to the database.
     * This effectively synchronizes the in-memory state of managed objects with the
     * database.
     *
     * @return void
     */
    public function flush()
    {
        $this->commit();
    }

    /**
     * Gets the repository for a class.
     *
     * @param string $className
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository($className)
    {
        $reflection = new \ReflectionClass($className);
        return new Repository($this, $this->getClassMetadata($reflection->getName()));
    }

    /**
     * Returns the ClassMetadata descriptor for a class.
     *
     * The class name must be the fully-qualified class name without a leading backslash
     * (as it is returned by get_class($obj)).
     *
     * @param string $className
     *
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    public function getClassMetadata($className)
    {
        $factory = $this->getMetadataFactory();
        $factory->setEntityManager($this);
        return $factory->getMetadataFor($className);
    }

    /**
     * Gets the metadata factory used to gather the metadata of classes.
     *
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory
     */
    public function getMetadataFactory()
    {
        return new ClassMetadataFactory();
    }

    /**
     * Helper method to initialize a lazy loading proxy or persistent collection.
     *
     * This method is a no-op for other objects.
     *
     * @param object $obj
     *
     * @return void
     */
    public function initializeObject($obj)
    {
        // TODO: Implement initializeObject() method.
    }

    /**
     * Checks if the object is part of the current UnitOfWork and therefore managed.
     *
     * @param object $object
     *
     * @return bool
     */
    public function contains($object)
    {
        // TODO: Implement contains() method.
    }
}