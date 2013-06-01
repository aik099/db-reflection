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


use aik099\Database\DatabaseReflection;

class DatabaseException extends ReflectionException
{

	const TABLE_NOT_FOUND = 100;

	/**
	 * Database.
	 *
	 * @var DatabaseReflection
	 * @access protected
	 */
	protected $database;

	/**
	 * Builds exception message automatically.
	 *
	 * @param DatabaseReflection $database Database.
	 * @param string             $message  Message.
	 * @param integer            $code     Code.
	 * @param \Exception         $previous Previous exception.
	 *
	 * @access public
	 */
	public function __construct(DatabaseReflection $database, $message = '', $code = 0, \Exception $previous = null)
	{
		$this->database = $database;

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
			case self::TABLE_NOT_FOUND:
				$message = vsprintf('Table "%1$s" doesn\'t exist in "{DATABASE}" database', (array)$message);
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
			'{DATABASE}' => $this->database->getName(),
		);
	}

}