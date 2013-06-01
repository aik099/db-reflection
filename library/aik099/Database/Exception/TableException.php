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


use aik099\Database\TableReflection;

class TableException extends ReflectionException
{

	const NOT_FOUND = 100;

	const COLUMN_NOT_FOUND = 101;

	/**
	 * Table.
	 *
	 * @var TableReflection
	 * @access protected
	 */
	protected $table;

	/**
	 * Builds exception message automatically.
	 *
	 * @param TableReflection $table    Table.
	 * @param string          $message  Message.
	 * @param integer         $code     Code.
	 * @param \Exception      $previous Previous exception.
	 *
	 * @access public
	 */
	public function __construct(TableReflection $table, $message = '', $code = 0, \Exception $previous = null)
	{
		$this->table = $table;

		parent::__construct($this->generateMessage($message, $code), $code, $previous);
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
				$message = 'Table name "{TABLE}" not found in "{DATABASE}" database';
				break;

			case self::COLUMN_NOT_FOUND:
				$message = vsprintf('Column "%1$s" doesn\'t exist in "{TABLE}" table', (array)$message);
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
			'{TABLE}' => $this->table->getName(),
			'{DATABASE}' => $this->table->getDatabase()->getName(),
		);
	}

}