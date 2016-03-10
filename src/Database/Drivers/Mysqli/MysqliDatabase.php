<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database\Drivers\Mysqli;

use Jivoo\Data\Database\Common\SqlDatabaseBase;
use Jivoo\Data\Database\Common\MysqlTypeAdapter;
use Jivoo\Data\Database\ConnectionException;
use Jivoo\Data\Database\QueryException;

/**
 * MySQLi database driver.
 */
class MysqliDatabase extends SqlDatabaseBase
{

    /**
     * @var mysqli MySQLi object.
     */
    private $handle;

    /**
     * {@inheritdoc}
     */
    public function init($options = array())
    {
        $this->setTypeAdapter(new MysqlTypeAdapter($this));
        if (isset($options['tablePrefix'])) {
            $this->tablePrefix = $options['tablePrefix'];
        }
        $this->handle = new \mysqli(
            $options['server'],
            $options['username'],
            $options['password'],
            $options['database']
        );
        if ($this->handle->connect_error) {
            throw new ConnectionException($this->handle->connect_error);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        $this->handle->close();
    }

    /**
     * {@inheritdoc}
     */
    public function quoteString($string)
    {
        return '"' . $this->handle->real_escape_string($string) . '"';
    }

    /**
     * Execute raw query on database.
     *
     * @param string $sql SQL query.
     * @return resource Result.
     * @throws QueryException On error.
     */
    protected function rawQuery($sql)
    {
        $this->logger->debug('MySQLi query: {query}', array(
            'query' => $sql
        ));
        $result = $this->handle->query($sql);
        if (! $result) {
            throw new QueryException($this->handle->error);
        }
        return $result;
    }
    
    /**
     * {@inheritdoc}
     */
    public function query($sql)
    {
        return new MysqliResultSet($this->rawQuery($sql));
    }

    /**
     * {@inheritdoc}
     */
    public function insert($sql, $pk = null)
    {
        $this->rawQuery($sql);
        return $this->handle->insert_id;
    }
    
    /**
     * {@inheritdoc}
     */
    public function execute($sql)
    {
        $this->rawQuery($sql);
        return $this->handle->affected_rows;
    }
}
