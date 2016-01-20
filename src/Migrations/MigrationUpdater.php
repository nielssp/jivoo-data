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
 * Migration updater. Migrates tables. 
 */
class MigrationUpdater extends InstallerSnippet {
  
  /**
   * @var string Name of database being migrated.
   */
  protected $dbName = 'default'; // TODO: set this somewhere
  
  /**
   * @var \Jivoo\Databases\Database Database being migrated.
   */
  protected $db = null; 

  /**
   * {@inheritdoc}
   */
  protected function setup() {
    $this->appendStep('migrate');
  }

  /**
   * {@inheritdoc}
   */
  public function before() {
    $this->m->units->run('Migrations');
    
    $name = $this->dbName;
    $this->db = $this->m->Databases->$name->getConnection();
  }

  /**
   * Installer step: Migrate tables.
   * clean or initialize.
   * @param array $data POST data.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function migrate($data = null) {
    $this->viewData['title'] = tr('Migrating database');
    $task = new MigrateTask($this->m->Migrations, $this->dbName);
    if ($this->runAsync($task)) {
      $this->m->Migrations->finalize($this->dbName);
      return $this->next();
    }
    return $this->render();
  }
}

/**
 * Asynchronous task for migrating tables.
 */
class MigrateTask extends AsyncTaskBase {
  /**
   * @var Migrations
   */
  private $migrations;
  
  /**
   * @var string
   */
  private $name;
  
  /**
   * @var string[]
   */
  private $missing = array();
  
  /**
   * @var int
   */
  private $count = 0;
  
  /**
   * Construct migration task.
   * @param Migrations $migrations Migrations module.
   * @param string $dbName Name of database to migrate.
   */
  public function __construct(Migrations $migrations, $dbName) {
    $this->migrations = $migrations;
    $this->name = $dbName;
  }

  /**
   * {@inheritdoc}
   */
  public function suspend() {
    return array(
      'missing' => $this->missing,
      'count' => $this->count
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resume(array $data) {
    if (isset($data['count']))
      $this->count = $data['count'];
    if (isset($data['waiting'])) {
      $this->missing = $data['missing'];
    }
    else {
      $this->missing = $this->migrations->check($this->name);
    } 
  }

  /**
   * {@inheritdoc}
   */
  public function isDone() {
    return count($this->missing) == 0;
  }

  /**
   * {@inheritdoc}
   */
  public function run() {
    $this->progress($this->count / ($this->count + count($this->missing)) * 100);
    $migration = $this->missing[0];
    $this->status(tr('Running migration "%1"...', $migration));
    $this->migrations->run($this->name, $migration);
    $migration = array_shift($this->missing);
    $this->count++;
  }
}