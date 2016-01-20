<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Databases;

use Jivoo\Models\Form;
use Jivoo\Setup\InstallerSnippet;

/**
 * Installer for setting up database configuration. 
 */
class DatabaseInstaller extends InstallerSnippet {
  /**
   * {@inheritdoc}
   */
  protected $helpers = array('Html', 'Form', 'Notify', 'Jivoo\Databases\DatabaseDrivers');

  /**
   * {@inheritdoc}
   */
  protected function setup() {
    $this->appendStep('select');
    $this->appendStep('configure', true);
  }
  
  /**
   * {@inheritdoc}
   */
  public function before() {
    $this->config = $this->config['Databases']['default'];
    $this->config->defaults = array(
      'server' => 'localhost',
      'database' => strtolower($this->app->name),
      'filename' => $this->p('user', 'db.sqlite3'),
    );
  }

  /**
   * Installer step: Select database driver.
   * @param array $data POST data.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function select($data = null) {
    if (isset($this->config['driver']))
      return $this->next();
    $this->viewData['title'] = tr('Select database driver');
    $this->viewData['drivers'] = $this->DatabaseDrivers->listDrivers();
    $this->viewData['enableNext'] = false;
    if (isset($data)) {
      foreach ($this->viewData['drivers'] as $driver) {
        if ($driver['isAvailable'] and isset($data[$driver['driver']])) {
          $this->config['driver'] = $driver['driver'];
          return $this->saveConfig();
        }
      }
    }
    return $this->render();
  }
  
  /**
   * Undo step: Select database driver.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function undoSelect() {
    unset($this->config['driver']);
    return $this->saveConfig();
  }

  /**
   * Get label for a driver option.
   * @param string $option Option name.
   * @return string Translated label.
   */
  private function getOptionLabel($option) {
    switch ($option) {
      case 'tablePrefix':
        return tr('Table prefix');
      default:
        return tr(ucfirst($option));
    }
  }

  /**
   * Installer step: Configure database driver.
   * @param array $data POST data.
   * @return \Jivoo\Routing\Response|string Response.
   */
  public function configure($data = null) {
    if (!isset($this->config['driver']))
      return $this->back();
    $driver = $this->DatabaseDrivers->checkDriver($this->config['driver']);
    if (!isset($driver) or $driver['isAvailable'] !== true) {
      unset($this->config['driver']);
      if ($this->config->save())
        return $this->back();
      else
        return $this->saveConfig();
    }
    $this->viewData['title'] = tr('Configure %1', $driver['name']);
    $form = new Form('driver');
    foreach ($driver['requiredOptions'] as $option) {
      $form->addString($option, $this->getOptionLabel($option));
    }
    foreach ($driver['optionalOptions'] as $option) {
      $form->addString($option, $this->getOptionLabel($option), false);
    }
    if (isset($data)) {
      $form->addData($data['driver']);
      if ($form->isValid()) {
        $class = 'Jivoo\Databases\Drivers\\' . $driver['driver'] . '\\' . $driver['driver'] . 'Database';
        try {
          new $class(new DatabaseSchemaBuilder(), $form->getData());
          $options = array_flip(
            array_merge(
              $driver['requiredOptions'],
              $driver['optionalOptions']
            )
          );
          foreach ($form->getData() as $key => $value) {
            if (isset($options[$key])) {
              $this->config[$key] = $value;
            }
          }
          unset($this->config['migration']);
          return $this->saveConfigAndContinue();
        }
        catch (ConnectionException $exception) {
          $this->Notify->error = tr(
            'An error occured: %1', $exception->getMessage()
          );
        }
      }
    }
    else {
      $form->addData($this->config->toArray());
    }
    $this->viewData['driver'] = $driver;
    $this->viewData['driverForm'] = $form;
    return $this->render();
  }
}
