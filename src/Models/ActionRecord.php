<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Models;

/**
 * Contains several links to actions.
 */
interface ActionRecord {
  /**
   * Get route to a named action.
   * @param string $action Action name.
   * @return array|Linkable|string|null A route, see {@see Routing}.
   */
  public function action($action);
}