<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database\Drivers\PdoPostgresql;

use Jivoo\Data\Database\Common\PostgresqlTypeAdapter;
use Jivoo\Data\Database\Common\PdoDatabase;
use Jivoo\Data\Database\QueryException;
use Jivoo\Data\Database\ConnectionException;
use Jivoo\Data\Database\Jivoo\Data\Database;

/**
 * PDO PostgreSQL database driver.
 */
class PdoPostgresqlDatabase extends PdoDatabase
{

    /**
     * {@inheritdoc}
     */
    public function init($options = array())
    {
        $this->setTypeAdapter(new PostgresqlTypeAdapter($this));
        if (isset($options['tablePrefix'])) {
            $this->tablePrefix = $options['tablePrefix'];
        }
        try {
            if (isset($options['password'])) {
                $this->pdo = new \PDO(
                    'pgsql:host=' . $options['server'] . ';dbname=' . $options['database'],
                    $options['username'],
                    $options['password']
                );
            } else {
                $this->pdo = new \PDO(
                    'pgsql:host=' . $options['server'] . ';dbname=' . $options['database'],
                    $options['username']
                );
            }
        } catch (\PDOException $exception) {
            throw new ConnectionException($exception->getMessage(), 0, $exception);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function insert($sql, $pk = null)
    {
        if (isset($pk)) {
            $row = $this->rawQuery($sql . ' RETURNING ' . $this->quoteField($pk))
                ->fetch(\PDO::FETCH_NUM);
            return $row[0];
        }
        return parent::insert($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function quoteModel($name)
    {
        return '"' . $this->tableName($name) . '"';
    }

    /**
     * {@inheritdoc}
     */
    public function caseInsensitiveFields()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function sqlLimitOffset($limit, $offset = null)
    {
        if (isset($offset)) {
            return 'LIMIT ' . $limit . ' OFFSET ' . $offset;
        }
        return 'LIMIT ' . $limit;
    }
}
