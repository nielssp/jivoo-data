<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Migrations;

use Jivoo\Setup\InstallerSnippet;
use Jivoo\Setup\AsyncTaskBase;
use Jivoo\Databases\MigratableDatabase;

/**
 * Migration installer. Checks database, cleans/migrates data, creates tables.
 */
class MigrationInstaller extends MigrationUpdater {
  /**
   * {@inheritdoc}
   */
  protected function setup() {
    $this->appendStep('check');
    $this->appendStep('clean');
    $this->appendStep('initialize');
    $this->appendStep('migrate');
    $this->appendStep('create');
  }
    
  /**
   * Installer step: Check state of database and allow user to either migrate,
   * clean or initialize.
   * @param array $data POST data.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function check($data = null) {
    // if schema_revision exists
    $this->viewData['enableNext'] = false;
    $this->viewData['title'] = tr('Existing data detected');
    if ($this->m->Migrations->isInitialized($this->dbName)) {
      if (isset($data)) {
        if (isset($data['migrate']))
          return $this->jump('migrate');
        else if (isset($data['clean']))
          return $this->jump('clean');
      }
    }
    else {
      if ($this->m->Migrations->isClean($this->dbName))
        return $this->jump('initialize');
      $existing = array();
      $schema = $this->db->getSchema();
      foreach ($schema->getTables() as $table) {
        if (isset($this->db->$table))
          $existing[] = $table;
      }
      if (count($existing) == 0)
        return $this->jump('initialize');
      else if (isset($data) and isset($data['clean']))
        return $this->jump('clean');
      $this->viewData['existing'] = $existing;
    }
    return $this->render();
  }

  /**
   * Installer step: Clean database (delete all tables),
   * clean or initialize.
   * @param array $data POST data.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function clean($data = null) {
    $this->m->Migrations->clean($this->dbName);
    return $this->next();
  }

  /**
   * Installer step: Initialize database (create SchemaRevision),
   * clean or initialize.
   * @param array $data POST data.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function initialize($data = null) {
    $this->m->Migrations->initialize($this->dbName);
    return $this->jump('create');
  }
  
  /**
   * Undo initialization step.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function undoInitialize() {
    return $this->jump('check');
  }

  /**
   * Installer step: Create missing tables.
   * clean or initialize.
   * @param array $data POST data.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function create($data = null) {
    $this->viewData['title'] = tr('Creating tables');
    $task = new CreateTask($this->db);
    if ($this->runAsync($task)) {
      $this->m->Migrations->finalize($this->dbName);
      return $this->next();
    }
    return $this->render();
  }
}

/**
 * Asynchronous task for creating tables.
 */
class CreateTask extends AsyncTaskBase {
  /**
   * @var MigratableDatabase
   */
  private $db;
  
  /**
   * @var \Jivoo\Databases\DatabaseSchema
   */
  private $schema;
  
  /**
   * @var string[]
   */
  private $tables = array();

  /**
   * @var int
   */
  private $count = 0;
  
  /**
   * Construct task.
   * @param MigratableDatabase $db Database to create tables in.
   */
  public function __construct(MigratableDatabase $db) {
    $this->db = $db;
    $this->schema = $db->getSchema();
  }
  
  /**
   * {@inheritdoc}
   */
  public function suspend() {
    return array(
      'tables' => $this->tables,
      'count' => $this->count
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resume(array $data) {
    if (isset($data['count']))
      $this->count = $data['count'];
    if (isset($data['tables'])) {
      $this->tables = $data['tables'];
    }
    else {
      $this->tables = $this->schema->getTables();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isDone() {
    return count($this->tables) == 0;
  }

  /**
   * {@inheritdoc}
   */
  public function run() {
    $this->progress($this->count / ($this->count + count($this->tables)) * 100);
    $table = $this->tables[0];
    if (!isset($this->db->$table)) {
      $this->status(tr('Creating table "%1"...', $table));
      $this->db->createTable($this->schema->getSchema($table));
    }
    array_shift($this->tables);
    $this->count++;
  }
}
