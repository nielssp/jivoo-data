<?php
// Jivoo
// Copyright (c) 2015 Niels Sonnich Poulsen (http://nielssp.dk)
// Licensed under the MIT license.
// See the LICENSE file or http://opensource.org/licenses/MIT for more information.
namespace Jivoo\Data\Query\Expression;

interface Expression {}

interface InfixExpresion extends Expression {
  /**
   * @return string
   */
  public function getOperator();

  /**
   * @return Node
   */
  public function getLeft();

  /**
   * @return Node
   */
  public function getRight();
}

interface PrefixExpression extends Expression {
  /**
   * @return string
   */
  public function getOperator();

  /**
   * @return Node
   */
  public function getOperand();
}


interface LiteralExpression extends Expression {
  /**
   * @return DataType
   */
  public function getType();

  /**
   * @return mixed
   */
  public function getValue();
}


