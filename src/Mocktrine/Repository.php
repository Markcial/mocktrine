<?php

namespace Mocktrine;

use Doctrine\Common\Persistence\ObjectRepository;
use Mocktrine\Pool;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use Mocktrine\Storage\Table;

/**
 * Class Repository
 * @package Mocktrine
 */
class Repository extends EntityRepository
{
    protected $table;


    protected $reflectedEntity;
    protected $shortName;
    protected $package;
    protected $fullName;

    public function __construct(EntityManager $em, $class)
    {
        $this->_entityName = $class->name;
        $this->_em         = $em;
        $this->_class      = $class;
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
        return $this->_em->find($this->_entityName, $id);
    }

    /**
     * Finds all objects in the repository.
     *
     * @return array The objects.
     */
    public function findAll()
    {
        return $this->findBy(array());
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
        $table = $this->_em->getTable($this->_entityName);

        return $table->getByCriteria($criteria);
    }

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria The criteria.
     *
     * @param array $orderBy
     * @param null $limit
     * @param null $offset
     * @return object The object.
     */
    public function findOneBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        /**
         * @var ArrayCollection $items
         */
        $items = $this->findBy($criteria, $orderBy, $limit, $offset);

        return $items ? $items->first() : null;
    }

    /**
     * Select all elements from a selectable that match the expression and
     * return a new collection containing these elements.
     *
     * @param \Doctrine\Common\Collections\Criteria $criteria
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function matching(Criteria $criteria)
    {
        $persister = $this->_em->getUnitOfWork()->getEntityPersister($this->_entityName);

        return new ArrayCollection($persister->loadCriteria($criteria));
    }

    /**
     * Adds support for magic finders.
     *
     * @param string $method
     * @param array  $arguments
     *
     * @return array|object The found entity/entities.
     *
     * @throws ORMException
     * @throws \BadMethodCallException If the method called is an invalid find* method
     *                                 or no find* method at all and therefore an invalid
     *                                 method call.
     */
    public function __call($method, $arguments)
    {
        switch (true) {
            case (0 === strpos($method, 'findBy')):
                $by = substr($method, 6);
                $method = 'findBy';
                break;

            case (0 === strpos($method, 'findOneBy')):
                $by = substr($method, 9);
                $method = 'findOneBy';
                break;

            default:
                throw new \BadMethodCallException(
                    "Undefined method '$method'. The method name must start with ".
                    "either findBy or findOneBy!"
                );
        }

        if (empty($arguments)) {
            throw ORMException::findByRequiresParameter($method . $by);
        }

        $fieldName = lcfirst(\Doctrine\Common\Util\Inflector::classify($by));

        if ($this->_class->hasField($fieldName) || $this->_class->hasAssociation($fieldName)) {
            switch (count($arguments)) {
                case 1:
                    return $this->$method(array($fieldName => $arguments[0]));

                case 2:
                    return $this->$method(array($fieldName => $arguments[0]), $arguments[1]);

                case 3:
                    return $this->$method(array($fieldName => $arguments[0]), $arguments[1], $arguments[2]);

                case 4:
                    return $this->$method(array($fieldName => $arguments[0]), $arguments[1], $arguments[2], $arguments[3]);

                default:
                    // Do nothing
            }
        }

        throw ORMException::invalidFindByCall($this->_entityName, $fieldName, $method.$by);
    }

    /**
     * Returns the class name of the object managed by the repository.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->_class->getName();
    }
}
