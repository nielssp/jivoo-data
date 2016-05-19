<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query;

use Jivoo\Data\Query\Expression\Quoter;

/**
 * A record expression.
 */
interface Expression
{

    /**
     * Apply expression to record data.
     *
     * @param array $data
     *            Record data.
     * @return mixed Result of expression.
     */
    public function __invoke(array $data);

    /**
     * Convert expression to a string.
     *
     * @param Quoter $quoter
     *            String quoter.
     * @return string SQL expression.
     */
    public function toString(Quoter $quoter);
}
