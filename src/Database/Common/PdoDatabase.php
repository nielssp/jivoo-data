<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database\Common;

use Jivoo\Data\Database\QueryException;

/**
 * A generic PDO SQL database.
 */
abstract class PdoDatabase extends SqlDatabaseBase
{

    /**
     * @var PDO PDO Connection.
     */
    protected $pdo;

    /**
     * {@inheritdoc}
     */
    public function close()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function quoteString($string)
    {
        return $this->pdo->quote($string);
    }
    
    /**
     * Execute raw query on database.
     *
     * @param string $sql SQL query.
     * @return \PDOStatement Result.
     * @throws QueryException On error.
     */
    protected function rawQuery($sql)
    {
        $this->logger->debug('PDO query: {query}', [
            'query' => $sql
        ]);
        $result = $this->pdo->query($sql);
        if (! $result) {
            $errorInfo = $this->pdo->errorInfo();
            throw new QueryException($errorInfo[0] . ' - ' . $errorInfo[1] . ' - ' . $errorInfo[2]);
        }
        return $result;
    }
    
    /**
     * {@inheritdoc}
     */
    public function query($sql)
    {
        return new PdoResultSet($this->rawQuery($sql));
    }

    /**
     * {@inheritdoc}
     */
    public function insert($sql, $pk = null)
    {
        $this->rawQuery($sql);
        return $this->pdo->lastInsertId();
    }
    
    /**
     * {@inheritdoc}
     */
    public function execute($sql)
    {
        return $this->rawQuery($sql)->rowCount();
    }
}
