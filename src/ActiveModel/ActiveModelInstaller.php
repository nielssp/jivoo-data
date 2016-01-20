<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\ActiveModels;

use Jivoo\Setup\InstallerSnippet;
use Jivoo\Setup\AsyncTaskBase;

/**
 * ActiveModel installer. Calls the 'install'-method of each ActiveModel unless
 * they are non-empty.
 */
class ActiveModelInstaller extends InstallerSnippet {
  /**
   * {@inheritdoc}
   */
  protected function setup() {
    $this->appendStep('install');
  }

  /**
   * {@inheritdoc}
   */
  public function before() {
    $this->m->units->run('ActiveModels');
  }

  /**
   * Installer step: Install empty models.
   * @param array $data POST data.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function install($data = null) {
    $this->viewData['title'] = tr('Installing default data');
    $task = new InstallTask($this->m->ActiveModels);
    if ($this->runAsync($task))
      return $this->next();
    return $this->render();
  }
}

/**
 * Asynchronous task for installing active models.
 */
class InstallTask extends AsyncTaskBase {
  /**
   * @var ActiveModels
   */
  private $ActiveModels;
  
  /**
   * @var string[]
   */
  private $models = array();

  /**
   * @var int
   */
  private $count = 0;
  
  /**
   * Construct task.
   * @param ActiveModels $ActiveModels ActiveModels module.
   */
  public function __construct(ActiveModels $ActiveModels) {
    $this->ActiveModels = $ActiveModels;
  }
  
  /**
   * {@inheritdoc}
   */
  public function suspend() {
    return array(
      'models' => $this->models,
      'count' => $this->count
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resume(array $data) {
    if (isset($data['count']))
      $this->count = $data['count'];
    if (isset($data['models'])) {
      $this->models = $data['models'];
    }
    else {
      $this->models = array_keys($this->ActiveModels->getModels());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isDone() {
    return count($this->models) == 0;
  }

  /**
   * {@inheritdoc}
   */
  public function run() {
    $this->progress($this->count / ($this->count + count($this->models)) * 100);
    $name = $this->models[0];
    $model = $this->ActiveModels->getModel($name);
    if (isset($model)) {
      if ($model->count() == 0) {
        $this->status(tr('Installing data into "%1"...', $name));
        $model->install();
      }
    }
    array_shift($this->models);
    $this->count++;
  }
}
