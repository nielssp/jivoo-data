<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Database\Drivers\Sqlite3;

use Jivoo\Data\Database\Common\SqlDatabaseBase;
use Jivoo\Data\Database\Common\SqliteTypeAdapter;
use Jivoo\Data\Database\QueryException;
use Jivoo\Data\Database\ConnectionException;

/**
 * SQLite3 database driver.
 */
class Sqlite3Database extends SqlDatabaseBase
{

    /**
     * @var SQLite3 SQLite3 object.
     */
    private $handle;

    /**
     * {@inheritdoc}
     */
    public function init($options = array())
    {
        $this->setTypeAdapter(new SqliteTypeAdapter($this));
        if (isset($options['tablePrefix'])) {
            $this->tablePrefix = $options['tablePrefix'];
        }
        try {
            $this->handle = new \SQLite3($options['filename']);
        } catch (\Exception $exception) {
            throw new ConnectionException(
                'SQLite database does not exist and could not be created: ' . $options['filename'],
                0,
                $exception
            );
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
        return '"' . $this->handle->escapeString($string) . '"';
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
        $this->logger->debug('SQLite3 query: {query}', array(
            'query' => $sql
        ));
        $result = $this->handle->query($sql);
        if (! $result) {
            throw new QueryException($this->handle->lastErrorMsg());
        }
        return $result;
    }
    
    /**
     * {@inheritdoc}
     */
    public function query($sql)
    {
        return new Sqlite3ResultSet($this->rawQuery($sql));
    }

    /**
     * {@inheritdoc}
     */
    public function insert($sql, $pk = null)
    {
        $this->rawQuery($sql);
        return $this->handle->lastInsertRowID();
    }
    
    /**
     * {@inheritdoc}
     */
    public function execute($sql)
    {
        $this->rawQuery($sql);
        return $this->handle->changes();
    }
}
