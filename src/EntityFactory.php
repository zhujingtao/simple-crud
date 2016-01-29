<?php

namespace SimpleCrud;

/**
 * Class to create instances of entities.
 */
class EntityFactory implements EntityFactoryInterface
{
    protected $db;
    protected $tables;
    protected $namespaces = [];
    protected $defaultEntity;
    protected $queryFactory;
    protected $fieldFactory;

    /**
     * Constructor.
     *
     * @param QueryFactory|null $queryFactory
     * @param FieldFactory|null $fieldFactory
     */
    public function __construct(QueryFactory $queryFactory = null, FieldFactory $fieldFactory = null)
    {
        $this->setQueryFactory($queryFactory ?: new QueryFactory());
        $this->setFieldFactory($fieldFactory ?: new FieldFactory());
    }

    /**
     * @see EntityFactoryInterface
     *
     * {@inheritdoc}
     */
    public function setDb(SimpleCrud $db)
    {
        $this->db = $db;

        return $this;
    }

    /**
     * Add a namespace for the entities classes.
     *
     * @param string $namespace
     *
     * @return self
     */
    public function addNamespace($namespace)
    {
        array_unshift($this->namespaces, $namespace);

        return $this;
    }

    /**
     * Set the QueryFactory instance used by the entities.
     *
     * @param QueryFactory $queryFactory
     *
     * @return self
     */
    public function setQueryFactory(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;

        return $this;
    }

    /**
     * Returns the QueryFactory instance used by the entities.
     *
     * @return QueryFactory
     */
    public function getQueryFactory()
    {
        return $this->queryFactory;
    }

    /**
     * Set the FieldFactory instance used by the entities.
     *
     * @param FieldFactory $fieldFactory
     *
     * @return self
     */
    public function setFieldFactory(FieldFactory $fieldFactory)
    {
        $this->fieldFactory = $fieldFactory;

        return $this;
    }

    /**
     * Returns the FieldFactory instance used by the entities.
     *
     * @return FieldFactory
     */
    public function getFieldFactory()
    {
        return $this->fieldFactory;
    }

    /**
     * Set whether the entities are autocreated or not.
     *
     * @param string $defaultEntity Default class used by the entities
     *
     * @return self
     */
    public function setAutocreate($defaultEntity = 'SimpleCrud\\Entity')
    {
        $this->defaultEntity = $defaultEntity;

        return $this;
    }

    /**
     * @see EntityFactoryInterface
     *
     * {@inheritdoc}
     */
    public function has($name)
    {
        if ($this->defaultEntity && in_array($name, $this->getTables())) {
            return true;
        }

        $ucname = ucfirst($name);

        foreach ($this->namespaces as $namespace) {
            $class = $namespace.$ucname;

            if (class_exists($class)) {
                return true;
            }
        }
    }

    /**
     * @see EntityFactoryInterface
     *
     * {@inheritdoc}
     */
    public function get($name)
    {
        try {
            $queryFactory = clone $this->queryFactory;
            $fieldFactory = clone $this->fieldFactory;

            $ucname = ucfirst($name);

            foreach ($this->namespaces as $namespace) {
                $class = $namespace.$ucname;

                if (class_exists($class)) {
                    return new $class($name, $this->db, $queryFactory, $fieldFactory);
                }
            }

            if ($this->defaultEntity && in_array($name, $this->getTables())) {
                $class = $this->defaultEntity;

                return new $class($name, $this->db, $queryFactory, $fieldFactory);
            }
        } catch (\Exception $exception) {
            throw new SimpleCrudException("Error getting the '{$name}' entity", 0, $exception);
        }

        throw new SimpleCrudException("Entity '{$name}' not found");
    }

    /**
     * Returns all tables in the database.
     *
     * @return array
     */
    private function getTables()
    {
        if ($this->tables === null) {
            $this->tables = $this->db->getTables();
        }

        return $this->tables;
    }
}
