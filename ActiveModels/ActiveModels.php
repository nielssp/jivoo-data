<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\ActiveModels;

use Jivoo\Core\LoadableModule;
use Jivoo\Core\Event;
use Jivoo\Routing\RenderEvent;

/**
 * Active record/model system.
 */
class ActiveModels extends LoadableModule {
  /**
   * {@inheritdoc}
   */
  protected static $loadAfter = array('Migrations');

  /**
   * {@inheritdoc}
   */
  protected $modules = array('Models', 'Databases');
  
  /**
   * @var ActiveModel[] Mapping of model names to models.
   */
  private $models = array();
  
  /**
   * {@inheritdoc}
   */
  protected function init() {
    $classes = $this->m->Models->getModelClasses();
    foreach ($classes as $class)
      $this->addActiveModel($class);
  }
  
  /**
   * Get all active models.
   * @return ActiveModel[] Array of active models.
   */
  public function getModels() {
    return $this->models;
  }
  
  /**
   * Get a single active model if it exists.
   * @param sring $model Model name.
   * @return ActiveModel|null Active model or null if undefined.
   */
  public function getModel($model) {
    if (!isset($this->models[$model]))
      return null;
    return $this->models[$model];
  }
  
  /**
   * Add an active model.
   * @param string $class Class name of active model.
   * @return True if successfull, false if table not found.
   */
  public function addActiveModel($class) {
    if (is_subclass_of($class, 'Jivoo\ActiveModels\ActiveModel')) {
      $model = new $class($this->app, $this->m->Databases);
      $this->m->Models->setModel($model->getName(), $model);
      $this->models[$model->getName()] = $model;
      return true;
    }
    return false;
  }
}
