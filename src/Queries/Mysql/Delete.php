<?php
namespace SimpleCrud\Queries\Mysql;

use SimpleCrud\Queries\QueryInterface;
use SimpleCrud\RowCollection;
use SimpleCrud\Row;
use SimpleCrud\Entity;
use SimpleCrud\SimpleCrudException;
use PDOStatement;
use PDO;

/**
 * Manages a database delete query in Mysql databases
 */
class Delete implements QueryInterface
{
    protected $entity;

    protected $where = [];
    protected $marks = [];
    protected $limit;
    protected $offset;

    /**
     * @see QueryInterface
     * 
     * {@inheritdoc}
     */
    public static function getInstance(Entity $entity)
    {
        return new static($entity);
    }

    /**
     * @see QueryInterface
     * 
     * $entity->delete($where, $marks, $limit)
     * 
     * {@inheritdoc}
     */
    public static function execute(Entity $entity, array $args)
    {
        $delete = self::getInstance($entity);

        if (isset($args[0])) {
            $delete->where($args[0], isset($args[1]) ? $args[1] : null);
        }

        if (isset($args[2])) {
            $delete->limit($args[2]);
        }

        return $delete->run();
    }

    /**
     * Constructor
     * 
     * @param Entity $entity
     */
    public function __construct(Entity $entity)
    {
        $this->entity = $entity;
    }

    /**
     * Adds a WHERE clause
     * 
     * @param string     $where
     * @param null|array $marks
     * 
     * @return self
     */
    public function where($where, $marks = null)
    {
        $this->where[] = $where;

        if ($marks) {
            $this->marks += $marks;
        }

        return $this;
    }

    /**
     * Adds a LIMIT clause
     * 
     * @param integer $limit
     * 
     * @return self
     */
    public function limit($limit)
    {
        $this->limit = $limit;

        return $this;
    }

    /**
     * Adds an offset to the LIMIT clause
     * 
     * @param integer $offset
     * 
     * @return self
     */
    public function offset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * Adds new marks to the query
     * 
     * @param array $marks
     * 
     * @return self
     */
    public function marks(array $marks)
    {
        $this->marks += $marks;

        return $this;
    }

    /**
     * Run the query and return all values
     * 
     * @return PDOStatement
     */
    public function run()
    {
        return $this->entity->getAdapter->execute((string) $this, $this->marks);
    }

    /**
     * Build and return the query
     * 
     * @return string
     */
    public function __toString()
    {
        $query = "DELETE FROM `{$this->entity->table}`";

        if (!empty($this->where)) {
            $query .= ' WHERE ('.implode(') AND (', $this->where).')';
        }

        if (!empty($this->limit)) {
            $query .= ' LIMIT';

            if (!empty($this->offset)) {
                $query .= ' '.$this->offset.',';
            }

            $query .= ' '.$this->limit;
        }

        return $query;
    }
}