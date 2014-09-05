<?php

namespace Mocktrine\Storage;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Mocktrine\EntityManager;

/**
 * Class Table
 * @package Mocktrine\Storage
 */
class Table
{
    protected $data;

    protected $identifiers;

    protected $classMetadata;

    protected $entityManager;

    public function __construct(ClassMetadata $classMetadata, EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->classMetadata = $classMetadata;
        $this->data = array();
    }

    public function addEntity($keys, $data)
    {
        foreach ($keys as $column => $key) {
            $this->data[sprintf('%s:%s', $column, $key)] = $data;
        }
    }

    public function getFQDN()
    {
        return $this->classMetadata->getName();
    }

    protected function getIdentifiers()
    {
        if (is_null($this->identifiers)) {
            $this->identifiers = $this->classMetadata->getIdentifier();
        }

        return $this->identifiers;
    }

    public function getByCriteria(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $coll = new ArrayCollection();

        foreach ($this->data as $data) {
            $results = array_map(function ($key, $val) use ($data) {
                return $data[$key] == $val;
            }, array_keys($criteria), $criteria);

            if (count(array_filter($results))) {
                $coll[] = $this->hydrateEntity($data);
            }
        }
//        if ($orderBy) {

  //      }


        return $coll->count() == 0 ? $coll->first() : $coll;
    }

    public function getByIdentifier($identifier)
    {
        $found = array();
        foreach ($this->getIdentifiers() as $id) {
            $key = sprintf('%s:%s', $id, $identifier);
            if (array_key_exists($key, $this->data)) {
                $found[] = $this->hydrateEntity($this->data[$key]);
            }
        }
        if (count($found) == 1) {
            return reset($found);
        }
        return !empty($found)?$found:null;
    }

    protected function hydrateEntity($data)
    {
        $class = $this->classMetadata->getName();
        $entity = new $class;
        foreach ($this->classMetadata->getFieldNames() as $field) {
            $setter = sprintf('set%s', ucfirst($field));
            call_user_func_array(array($entity, $setter), array($data[$field]));
        }

        foreach ($this->classMetadata->getAssociationNames() as $foreign) {
            $collection = new ArrayCollection();
            $foreignClass = $this->classMetadata->getAssociationTargetClass($foreign);
            foreach ($data[$foreign] as $identifier => $identifierValue) {
                if (is_array($identifierValue)) {
                    foreach ($identifierValue as $idValue) {
                        $collection->add($this->entityManager->find($foreignClass, $idValue));
                    }
                } else {
                    $collection->add($this->entityManager->find($foreignClass, $identifierValue));
                }
            }
            $setter = sprintf('set%s', ucfirst($foreign));
            call_user_func_array(array($entity, $setter), array($collection));
        }

        return $entity;
    }
}
