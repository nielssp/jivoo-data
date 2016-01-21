<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\ActiveModel;

use Jivoo\Event;

/**
 * Event data for an active model event.
 */
class ActiveModelEvent extends Event
{

    /**
     * @var ActiveRecord Subject of event.
     */
    public $record = null;

    /**
     * Construct active model event.
     *
     * @param mixed $sender
     *            Sender
     */
    public function __construct($sender)
    {
        parent::__construct($sender);
        if ($sender instanceof ActiveRecord) {
            $this->record = $sender;
        }
    }
}
