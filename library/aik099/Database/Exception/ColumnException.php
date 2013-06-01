<?php
/**
 * This file is part of the db-reflection library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/db-reflection
 */

namespace aik099\Database\Exception;


use aik099\Database\ColumnReflection;

class ColumnException extends ReflectionException
{
	const NOT_FOUND = 100;

	const TYPE_MISMATCH = 101;

	/**
	 * Column.
	 *
	 * @var ColumnReflection
	 * @access protected
	 */
	protected $column;

	/**
	 * Builds exception message automatically.
	 *
	 * @param ColumnReflection $column   Column.
	 * @param string           $message  Message.
	 * @param integer          $code     Code.
	 * @param \Exception       $previous Previous exception.
	 *
	 * @access public
	 */
	public function __construct(ColumnReflection $column, $message = '', $code = 0, \Exception $previous = null)
	{
		$this->column = $column;

		parent::__construct($message, $code, $previous);
	}

	/**
	 * Returns message by code.
	 *
	 * @param string  $message Message.
	 * @param integer $code    Code.
	 *
	 * @return string
	 * @access protected
	 */
	protected function generateMessage($message, $code)
	{
		switch ( $code ) {
			case self::NOT_FOUND:
				$message = 'Column "{COLUMN}" doesn\'t exist in "{TABLE}" table';
				break;

			case self::TYPE_MISMATCH:
				$message = 'Column "{COLUMN}" must have ' . $message . ' data type';
				break;
		}

		return parent::generateMessage($message, $code);
	}

	/**
	 * Returns possible substitutions in exception message.
	 *
	 * @return array
	 * @access protected
	 */
	protected function getReplacements()
	{
		return array(
			'{COLUMN}' => $this->column->getName(),
			'{TABLE}' => $this->column->getTable()->getName(),
			'{DATABASE}' => $this->column->getTable()->getDatabase()->getName(),
		);
	}

}