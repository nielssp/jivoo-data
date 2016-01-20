<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases\Common;

use Jivoo\Databases\LoadableDatabase;
use Jivoo\Databases\MigrationTypeAdapter;
use Jivoo\Core\Utilities;
use Jivoo\Models\DataType;
use Jivoo\Models\BasicModel;
use Jivoo\Models\Condition\Quoter;

/**
 * A generic SQL database.
 */
abstract class SqlDatabaseBase extends LoadableDatabase implements SqlDatabase, Quoter {
  /**
   * @var string Table prefix.
   */
  protected $tablePrefix = '';

  /**
   * @var MigrationTypeAdapter Type/migration adapter.
   */
  private $typeAdapter = null;
  
  /**
   * @var array Associative array of table names and {@see SqlTable} objects.
   */
  protected $tables = array();
  
  /**
   * Destruct and close database.
   */
  function __destruct() {
    $this->close();
  }

  /**
   * Create new table object.
   * @param string $table Table name.
   * @return SqlTable Table object.
   */
  protected function getTable($table) {
    return new SqlTable($this, $table);
  }

  /**
   * {@inheritdoc}
   */
  protected function getMigrationAdapter() {
    return $this->typeAdapter;
  }
  
  /**
   * Set migration/type adapter.
   * @param MigrationTypeAdapter $typeAdapter Adapter.
   */
  protected function setTypeAdapter(MigrationTypeAdapter $typeAdapter) {
    $this->typeAdapter = $typeAdapter;
  }

  /**
   * Convert table name. E.g. "UserSession" to "prefix_user_session".
   * @param string $name Table name.
   * @return string Real table name.
   */
  public function tableName($name) {
    return $this->tablePrefix . Utilities::camelCaseToUnderscores($name);
  }
  
  /**
   * {@inheritdoc}
   */
  public function quoteModel($model) {
    return '`' . $this->tableName($model) . '`';
  }

  /**
   * {@inheritdoc}
   */
  public function quoteLiteral(DataType $type, $value) {
    return $this->typeAdapter->encode($type, $value);
  }
  
  /**
   * {@inheritdoc}
   */
  public function quoteField($field) {
    return $field;
  }

  /**
   * Escape a string and surround with quotation marks.
   * @param string $string String.
   * @return string String surrounded with quotation marks.
   */
  public abstract function quoteString($string);

  /**
   * {@inheritdoc}
   */
  public function getTypeAdapter() {
    return $this->typeAdapter;
  }

  /**
   * Whether or not a table exists.
   * @param string $table Table name.
   * @return bool True if table exists, false otherwise.
   */
  public function tableExists($table) {
    return $this->typeAdapter->tableExists($table);
  }
  
  /**
   * Get SQL for LIMIT / OFFSET. Default style ("LIMIT 0,1") is used by SQLite
   * and MySQL, override for other implementations.
   * @param int $limit Limit.
   * @param int|null $offset Optional offset.
   * @return string SQL.
   */
  public function sqlLimitOffset($limit, $offset = null) {
    if (isset($offset))
      return 'LIMIT ' . $offset . ', ' . $limit;
    return 'LIMIT ' . $limit;
  }
  
  /**
   * Whether fields returned by database may be case insensitive.
   * @todo This is a temporary solution to PostgreSQL problems.
   * @return bool True if case insensitive.
   */
  public function caseInsensitiveFields() {
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function beginTransaction() {
    $this->rawQuery('BEGIN');
  }

  /**
   * {@inheritdoc}
   */
  public function commit() {
    $this->rawQuery('COMMIT');
  }

  /**
   * {@inheritdoc}
   */  
  public function rollback() {
    $this->rawQuery('ROLLBACK');
  }
}

