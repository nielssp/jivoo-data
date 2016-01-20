<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\ActiveModels;

/**
 * Mixin for automatically updating time stamps on records when they are 
 * created and updated. Assumes existence of DateTime-fields "updated"
 * and "created".
 */
class TimestampsMixin extends ActiveModelMixin {
  /**
   * {@inheritdoc}
   */
  public function beforeValidate(ActiveModelEvent $event) {
    if (!$event->record->isNew() and !$event->record->isSaved())
      $event->record->updated = time();
  }

  /**
   * {@inheritdoc}
   */
  public function afterCreate(ActiveModelEvent $event) {
    $event->record->created = time();
    $event->record->updated = time();
  }
}
