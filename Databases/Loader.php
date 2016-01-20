<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

use Jivoo\Core\Utilities;
use Jivoo\Core\Module;
use Jivoo\Core\App;
use Jivoo\Core\Assume;
use Jivoo\Core\Store\Document;
use Jivoo\InvalidPropertyException;
use Jivoo\Core\Json;
use Psr\Log\LoggerAwareInterface as LoggerAware;
use Psr\Log\LoggerInterface as Logger;
use Jivoo\Core\Log\NullLogger;

/**
 * Connects to databases.
 */
class Loader implements LoggerAware {
  /**
   * @var Document
   */
  private $config;
  
  /**
   * @var string
   */
  private $drivers;
  
  /**
   * @var LodableDatabase[] Named database connections.
   */
  private $connections = array();
  
  /**
   * @var Logger
   */
  private $logger;

  /**
   * Construct database loader.
   */
  public function __construct(Document $config) {
    $this->logger = new NullLogger();
    $this->config = $config;
    $this->drivers = dirname(__FILE__) . '/Drivers';
  }
  
  /**
   * {@inheritdoc}
   */
  public function setLogger(Logger $logger) {
    $this->logger = $logger;
  }

  /**
   * Get a database connection.
   * @param string $name Connection name.
   * @return LoadableDatabase Database.
   */
  public function __get($name) {
    if (isset($this->connections[$name]))
      return $this->connections[$name];
    throw new InvalidPropertyException('No connection named: ' . $name);
  }

  /**
   * {@inheritdoc}
   */
  public function __isset($name) {
    return isset($this->connections[$name]);
  }

  /**
   * Get all database connections.
   * @return LoadableDatabase[] Associative array of database names and
   * connections.
   */
  public function getConnections() {
    return $this->connections;
  }

  /**
   * Get information about a database driver.
   *
   * The returned information array is of the format:
   * <code>
   * array(
   *   'driver' => ..., // Driver name (string)
   *   'name' => ..., // Formal name, e.g. 'MySQL' instead of 'MySql' (string)
   *   'requiredOptions' => array(...), // List of required options (string[])
   *   'optionalOptions' => array(...), // List of optional options (string[])
   *   'isAvailable' => ..., // Whether or not driver is available (bool)
   *   'missingExtensions => array(...) // List of missing extensions (string[])
   * )
   * </code>
   * @param string $driver Driver name
   * @return array Driver information as an associative array.
   * @throws InvalidDriverException If driver is missing or invalid.
   */
  public function checkDriver($driver) {
    if (!file_exists($this->drivers . '/' . $driver . '/' . $driver . 'Database.php')) {
      throw new InvalidDriverException(tr('Driver class not found: %1', $driver));
    }
    if (!file_exists($this->drivers . '/' . $driver . '/driver.json')) {
      throw new InvalidDriverException(tr('Driver manifest not found: %1', $driver));
    }
    try {
      $info = Json::decodeFile($this->drivers . '/' . $driver . '/driver.json');
    }
    catch (JsonException $e) {
      throw new InvalidDriverException(tr('Invalid driver manifest: %1 (%2)', $driver, $e->getMessage()), 0, $e);
    }
    if (!isset($info['required']))
      $info['required'] = array();
    if (!isset($info['optional']))
      $info['optional'] = array();
    if (!isset($info['phpExtensions']))
      $info['phpExtensions'] = array();
    $missing = array();
    foreach ($info['phpExtensions'] as $dependency) {
      if (!extension_loaded($dependency)) {
        $missing[] = $dependency;
      }
    }
    return array(
      'driver' => $driver,
      'name' => $info['name'],
      'requiredOptions' => $info['required'],
      'optionalOptions' => $info['optional'],
      'isAvailable' => count($missing) < 1,
      'missingExtensions' => $missing
    );
  }
  
  /**
   * Get an array of all drivers and their information.
   * @return array An associative array of driver names and driver information
   * as returned by {@see Database::checkDriver()}.
   */
  public function listDrivers() {
    $drivers = array();
    $files = scandir($this->drivers);
    if ($files !== false) {
      foreach ($files as $driver) {
        if (is_dir($this->drivers . '/' . $driver)) {
          try {
            $drivers[$driver] = $this->checkDriver($driver);
          }
          catch (InvalidDriverException $e) {
            $this->logger->warning($e->getMessage(), array('exception' => $e));
          }
        }
      }
    }
    return $drivers;
  }


  /**
   * Read schema classes from a namespace.
   * @param string $namespace Namespace of schema classes.
   * @param string $dir Location of schema classes.
   * @return DatabaseSchemaBuilder Database schema.
   */
  public function readSchema($namespace, $dir) {
    $dbSchema = new DatabaseSchemaBuilder();
    assume(is_dir($dir));
    $files = scandir($dir);
    if ($files !== false) {
      foreach ($files as $file) {
        $split = explode('.', $file);
        if (isset($split[1]) and $split[1] == 'php') {
          $class = rtrim($namespace, '\\') . '\\' . $split[0];
          Assume::isSubclassOf($class, 'Jivoo\Databases\SchemaBuilder');
          $dbSchema->addSchema(new $class());
        }
      }
    }
    return $dbSchema;
  }
  
  /**
   * Make a database connection.
   * @param string $name Name of database connection.
   * @param DatabaseSchema $schema Database schema (collecton of table schemas).
   * @throws ConfigurationException If the $options-array does not
   * contain the necessary information for a connection to be made.
   * @throws InvalidSchemaException If one of the schema names listed
   * in the $schemas-parameter is unknown.
   * @throws ConnectionException If the connection fails.
   * @return LoadableDatabase A database object.
   */
  public function connect($name, DatabaseSchema $schema = null) {
    if (!isset($this->config[$name])) {
      throw new ConfigurationException(
        tr('Database "%1" not configured', $name)
      );
    }
    $config = $this->config->getSubset($name);
    $driver = $config->get('driver', null);
    if (!isset($driver))
      throw new ConfigurationException(tr(
        'Database driver not set'
      ));
    try {
      $driverInfo = $this->checkDriver($driver);
    }
    catch (InvalidDriverException $e) {
      throw new ConnectionException(tr('Invalid database driver: %1', $e->getMessage()), 0, $e);
    }
    foreach ($driverInfo['requiredOptions'] as $option) {
      if (!isset($config[$option])) {
        throw new ConfigurationException(
          tr('Database option missing: "%1"', $option)
        );
      }
    }
    try {
      $class = 'Jivoo\Databases\Drivers\\' . $driver  . '\\' . $driver . 'Database';
      Assume::isSubclassOf($class, 'Jivoo\Databases\LoadableDatabase');
      if (!isset($schema))
        $schema = new DynamicDatabaseSchema();
      $object = new $class($schema, $config);
      $object->setLogger($this->logger);
      $this->connections[$name] = new DatabaseConnection($object);
      return $object;
    }
    catch (ConnectionException $exception) {
      throw new ConnectionException(
        tr('Database connection failed (%1): %2', $driver, $exception->getMessage()),
        0, $exception
      );
    }
  }
  
  /**
   * Close all connections.
   */
  public function close() {
    foreach ($this->connections as $connection)
      $connection->close();
  }
  
  /**
   * Begin transaction in all connections.
   */
  public function beginTransaction() {
    foreach ($this->connections as $connection)
      $connection->beginTransaction();
  }
  
  /**
   * Commit all transactions.
   */
  public function commit() {
    foreach ($this->connections as $connection)
      $connection->commit();
  }
  
  /**
   * Rollback all transactions.
   */
  public function rollback() {
    foreach ($this->connections as $connection)
      $connection->rollback();
  }
}
