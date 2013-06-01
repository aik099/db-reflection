<?php
/**
 * This file is part of the db-reflection library.
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link https://github.com/aik099/db-reflection
 */

namespace aik099\Database;


use aik099\Database\Driver\IDriver;

/**
 * Base class for database reflections.
 */
abstract class Reflection
{

	/**
	 * Reflected object name.
	 *
	 * @var string
	 * @access protected
	 */
	protected $name = '';

	/**
	 * Database driver reference.
	 *
	 * @var IDriver
	 * @access protected
	 */
	protected $driver;

	/**
	 * Creates reflection and associates it with a driver.
	 *
	 * @param IDriver $driver Driver.
	 *
	 * @access public
	 */
	public function __construct(IDriver $driver)
	{
		$this->driver = $driver;
	}

	/**
	 * Returns driver instance, that is currently in use.
	 *
	 * @return IDriver
	 * @access public
	 */
	public function getDriver()
	{
		return $this->driver;
	}

	/**
	 * Reflected object name.
	 *
	 * @return string
	 * @access public
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Tells, that reflected entity exists in the database.
	 *
	 * @return boolean
	 * @access public
	 */
	abstract public function exists();

	/**
	 * Allows using reflection object in methods, where their referred by name.
	 *
	 * @return string
	 * @access public
	 */
	public function __toString()
	{
		return $this->getName();
	}

}