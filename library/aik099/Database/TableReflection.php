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


use aik099\Database\Exception\TableException;

/**
 * Reflects table.
 */
class TableReflection extends Reflection implements \Countable
{

	/**
	 * Database.
	 *
	 * @var DatabaseReflection
	 * @access protected
	 */
	protected $database;

	/**
	 * Columns in a table.
	 *
	 * @var ColumnReflection[]
	 * @access protected
	 */
	protected $columns = array();

	/**
	 * Creates table reflection.
	 *
	 * @param DatabaseReflection $database Database.
	 * @param string             $name     Table name.
	 *
	 * @access public
	 * @throws TableException When empty table name is given.
	 */
	public function __construct(DatabaseReflection $database, $name)
	{
		if ( !strlen($name) ) {
			throw new TableException($this, sprintf('Table name "%s" is invalid', $name));
		}

		parent::__construct($database->getDriver());

		$this->name = $name;
		$this->database = $database;
	}

	/**
	 * Returns used database instance.
	 *
	 * @return DatabaseReflection
	 * @access public
	 */
	public function getDatabase()
	{
		return $this->database;
	}

	/**
	 * Gets table columns list from a database.
	 *
	 * @param boolean $force Query database for updated info.
	 *
	 * @return self
	 * @access public
	 * @throws TableException When table doesn't exist in a database.
	 */
	public function scan($force = false)
	{
		if ( count($this->columns) && !$force ) {
			return $this;
		}

		if ( !$this->exists() ) {
			throw new TableException($this, '', TableException::NOT_FOUND);
		}

		$this->columns = array();

		foreach (array_keys($this->driver->getTableStructure($this->name, $force)) as $column_name) {
			$this->addColumn($this->createColumnReflection($column_name));
		}

		return $this;
	}

	/**
	 * Adds column to a table & database.
	 *
	 * @param ColumnReflection $column Column.
	 *
	 * @return self
	 */
	public function addColumn(ColumnReflection $column)
	{
		$this->columns[$column->getName()] = $column;

		if ( !$column->exists() ) {
			$this->driver->addColumn($column);
		}

		return $this;
	}

	/**
	 * Writes changes made to a column to a database.
	 *
	 * @param string|ColumnReflection $name     Column name.
	 * @param string|null             $new_name New column name (optional).
	 *
	 * @return self
	 * @access public
	 * @throws TableException When column can't be found in a database.
	 */
	public function updateColumn($name, $new_name = null)
	{
		$name = (string)$name;
		$column = $this->getColumn($name);

		if ( !$column->exists() ) {
			throw new TableException($this,	$name, TableException::COLUMN_NOT_FOUND);
		}

		$this->driver->updateColumn($column, $new_name);

		return $this;
	}

	/**
	 * Deletes column from table & database.
	 *
	 * @param string|ColumnReflection $name Column name.
	 *
	 * @return self
	 * @access public
	 * @throws TableException When column can't be found in a database.
	 */
	public function deleteColumn($name)
	{
		$name = (string)$name;
		$column = $this->getColumn($name);

		if ( !$column->exists() ) {
			throw new TableException($this,	$name, TableException::COLUMN_NOT_FOUND);
		}

		$this->driver->deleteColumn($column);
		unset($this->columns[$name]);

		return $this;
	}

	/**
	 * Returns columns in a table.
	 *
	 * @return ColumnReflection[]
	 * @access public
	 */
	public function getColumns()
	{
		return $this->columns;
	}

	/**
	 * Returns column by given name.
	 *
	 * @param string $name Column name.
	 *
	 * @return ColumnReflection
	 * @throws TableException When column can't be found.
	 */
	public function getColumn($name)
	{
		$name = (string)$name;

		if ( !$this->columnExists($name, false) ) {
			throw new TableException($this,	$name, TableException::COLUMN_NOT_FOUND);
		}

		return $this->columns[$name];
	}

	/**
	 * Creates a column reflection for current table.
	 *
	 * @param string     $name    Column name.
	 * @param array|null $options Column options.
	 *
	 * @return ColumnReflection
	 * @access public
	 */
	public function createColumnReflection($name, array $options = null)
	{
		return new ColumnReflection($this, $name, $options);
	}

	/**
	 * Detects if a table exists in a database.
	 *
	 * @return boolean
	 * @access public
	 */
	public function exists()
	{
		return in_array($this->name, $this->driver->getTables());
	}

	/**
	 * Tells if a column with given name exists in a table.
	 *
	 * @param string|ColumnReflection $name        Column name.
	 * @param boolean                 $in_database Checks existence in database.
	 *
	 * @return boolean
	 * @access public
	 */
	public function columnExists($name, $in_database = true)
	{
		$name = (string)$name;

		if ( $in_database ) {
			return $this->createColumnReflection($name, array())->exists();
		}

		return isset($this->columns[$name]);
	}

	/**
	 * Returns column count in a table.
	 *
	 * @return integer
	 * @access public
	 */
	public function count()
	{
		return count($this->columns);
	}

}