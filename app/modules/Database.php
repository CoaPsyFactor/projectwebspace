<?php

namespace App\Modules;

use App\Kernel\ExceptionHandler;
use PDO;
use PDOException;
use PDOStatement;

class Database
{

    /** @var PDO */
    private $db;

    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var string */
    private $user;

    /** @var string */
    private $password;

    /** @var string */
    private $dbName;

    public function __construct(Config $config)
    {
        $configData = $config->getDatabase();

        $this->host = $configData['host'];
        $this->user = $configData['user'];
        $this->password = $configData['password'];
        $this->dbName = $configData['db_name'];
        $this->port = (int) $configData['port'];

        $dsn = "mysql:host={$this->host};dbname={$this->dbName};port={$this->port}";

        try {
            $this->db = new PDO($dsn, $this->user, $this->password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        } catch (PDOException $exception) {
            ExceptionHandler::catchException($exception);
        }

        if (ExceptionHandler::hasExceptions()) {
            ExceptionHandler::throwExceptions();
        }
    }



    /**
     * 
     * Executes query and returs results as array
     * 
     * Use this for SELECT queries
     * 
     * @param string $query
     * @param array $bindings
     * @return array
     */
    public function select($query, array $bindings = [], $single = false)
    {
        $results = $this->query($query, $bindings)->fetchAll();

        if ($single && false === empty($results[0])) {
            return $results[0];
        }

        return empty($results) ? [] : $results;
    }

    /**
     * 
     * Executes query and returns last inser id
     * 
     * Use this for INSERT queries
     * 
     * @param string $query
     * @param array $bindings = []
     * @return int
     */
    public function insert($query, array $bindings)
    {
        $this->query($query, $bindings);

        return $this->db->lastInsertId();
    }

    /**
     * @param array $queries
     * @param array $bindings
     * @param \Closure $validator
     */
    public function safeInsertUpdate(array $queries, array $bindings, \Closure $validator)
    {
        $this->startTransaction();

        foreach ($queries as $index => $query) {
            $this->insert($query, $bindings[$index]);
        }

        if (false == $validator($this)) {
            $this->discardTransaction();
        } else {
            $this->saveTransaction();
        }
    }

    /**
     * 
     * Executes query and return number of affected rows
     * 
     * Use for UPDATE, DELETE
     * 
     * @param string $query
     * @param array $bindings
     * @return int
     */
    public function update($query, array $bindings)
    {
        return $this->query($query, $bindings)->rowCount();
    }

    /**
     * Begin transaction
     */
    public function startTransaction()
    {
        $this->db->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function saveTransaction()
    {
        if ($this->db->inTransaction()) {
            $this->db->commit();
        }
    }

    /**
     * Rollback transaction
     */
    public function discardTransaction()
    {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }
    }

    /**
     * Prepares and executes string and returns PDOStatenebt
     * 
     * @param string $query
     * @param array $bindings
     * @return PDOStatement
     */
    public function query($query, array $bindings = [])
    {
        $stmt = $this->db->prepare($query);

        $stmt->execute($bindings);

        return $stmt;
    }

}
