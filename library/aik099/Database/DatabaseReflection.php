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


use aik099\Database\Driver\IDriver,
	aik099\Database\Exception\DatabaseException;

/**
 * Reflects database.
 */
class DatabaseReflection extends Reflection implements \Countable
{

	/**
	 * List of tables in a database.
	 *
	 * @var TableReflection[]
	 * @access protected
	 */
	protected $tables = array();

	/**
	 * Creates reflection and associates it with a driver.
	 *
	 * @param IDriver $driver Driver.
	 *
	 * @access public
	 */
	public function __construct(IDriver $driver)
	{
		parent::__construct($driver);

		$this->name = $this->driver->getDatabaseName();
	}

	/**
	 * Gets table list from a database.
	 *
	 * @param boolean $force Query database for updated info.
	 *
	 * @return self
	 * @access public
	 */
	public function scan($force = false)
	{
		if ( count($this->tables) && !$force ) {
			return $this;
		}

		$this->tables = array();

		foreach ($this->driver->getTables($force) as $table_name) {
			$this->addTable($this->createTableReflection($table_name));
		}

		return $this;
	}

	/**
	 * Returns all tables in a database.
	 *
	 * @return TableReflection[]
	 * @access public
	 */
	public function getTables()
	{
		return $this->tables;
	}

	/**
	 * Returns table by given name.
	 *
	 * @param string $name Table name.
	 *
	 * @return TableReflection
	 * @throws DatabaseException When table can't be found.
	 */
	public function getTable($name)
	{
		if ( !$this->tableExists(false) ) {
			throw new DatabaseException($this, $name, DatabaseException::TABLE_NOT_FOUND);
		}

		return $this->tables[$name];
	}

	/**
	 * Creates table reflection.
	 *
	 * @param string $table_name Table name.
	 *
	 * @return TableReflection
	 * @access public
	 */
	public function createTableReflection($table_name)
	{
		return new TableReflection($this, $table_name);
	}

	/**
	 * Adds table to database.
	 *
	 * @param TableReflection $table Table.
	 *
	 * @return self
	 * @access public
	 */
	public function addTable(TableReflection $table)
	{
		$this->tables[$table->getName()] = $table;

		return $this;
	}

	/**
	 * Detects if a database exists.
	 *
	 * @return boolean
	 * @access public
	 */
	public function exists()
	{
		return true;
	}

	/**
	 * Checks if a table exists in a database.
	 *
	 * @param string  $name        Table name.
	 * @param boolean $in_database Checks existence in database.
	 *
	 * @return boolean
	 * @access public
	 */
	public function tableExists($name, $in_database = true)
	{
		if ( $in_database ) {
			return $this->createTableReflection($name)->exists();
		}

		return isset($this->tables[$name]);
	}

	/**
	 * Returns table count.
	 *
	 * @return integer
	 * @access public
	 */
	public function count()
	{
		return count($this->tables);
	}

}