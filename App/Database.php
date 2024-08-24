<?php
namespace App;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private PDO $PDO;
    private bool $inTransaction = false;

    /**
     * @throws PDOException
     */
    public function __construct(string $dbname = '')
    {
        if (!$dbname) {
            $dbname = DB_CONFIG['dbname'];
        }
        $this->connect($dbname);
    }

    /**
     * @throws PDOException
     */
    private function connect(string $dbname): void
    {
        $dsn = DB_CONFIG['driver'] . ":host=" . DB_CONFIG['host'] . ";dbname=" . $dbname . ";charset=utf8mb4";
        $this->PDO = new PDO($dsn, DB_CONFIG['username'], DB_CONFIG['password'], DB_CONFIG['options']);
        //$this->PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        //$this->PDO->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    }

    public function beginTransaction(): bool
    {
        if (!$this->inTransaction) {
            $this->inTransaction = $this->PDO->beginTransaction();
            return $this->inTransaction;
        }
        return false;
    }

    public function commit(): bool
    {
        if ($this->inTransaction) {
            $this->inTransaction = false;
            return $this->PDO->commit();
        }
        return false;
    }

    public function rollback(): bool
    {
        if ($this->inTransaction) {
            $this->inTransaction = false;
            return $this->PDO->rollBack();
        }
        return false;
    }

    public function insert(string $sql, array $binds = []): bool
    {
        $stmt = $this->prepareAndExecute($sql, $binds);
        return $stmt->rowCount() > 0;
    }

    public function select(string $sql, array $binds = []): PDOStatement
    {
        return $this->prepareAndExecute($sql, $binds);
    }

    public function update(string $sql, array $binds = []): bool
    {
        $stmt = $this->prepareAndExecute($sql, $binds);
        return $stmt->rowCount() > 0;
    }

    public function delete(string $sql, array $binds = []): bool
    {
        $stmt = $this->prepareAndExecute($sql, $binds);
        return $stmt->rowCount() > 0;
    }

    private function prepareAndExecute(string $sql, array $binds = []): PDOStatement
    {
        $stmt = $this->PDO->prepare($sql);
        $stmt->execute($binds);
        return $stmt;
    }

    public function lastInsertId(?string $name = null): string
    {
        return $this->PDO->lastInsertId($name);
    }

    public static function getErrorMessage(PDOException $e): string
    {
        $errorMessage = PRODUCTION ? "<b>{$e->getCode()}</b>" : "<b>{$e->getMessage()}</b>";
        return "Ops, houve um erro na conexão com o BD: $errorMessage";
    }
}
