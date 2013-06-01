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


use aik099\Database\Exception\ColumnException;

/**
 * Reflects one column in a database table.
 */
class ColumnReflection extends Reflection
{

	/**
	 * Table.
	 *
	 * @var TableReflection
	 * @access protected
	 */
	protected $table;

	/**
	 * Options, parsed from data type.
	 *
	 * @var ColumnOptions
	 * @access protected
	 */
	protected $options;

	/**
	 * Creates instance of table reflection.
	 *
	 * @param TableReflection    $table   Table.
	 * @param string             $name    Column name.
	 * @param ColumnOptions|null $options Column options.
	 *
	 * @access public
	 */
	public function __construct(TableReflection $table, $name, ColumnOptions $options = null)
	{
		parent::__construct($table->getDriver());

		$this->table = $table;
		$this->name = $name;

		$this->setOptions($options);
	}

	/**
	 * Returns used table instance.
	 *
	 * @return TableReflection
	 * @access public
	 */
	public function getTable()
	{
		return $this->table;
	}

	/**
	 * Change column options.
	 *
	 * @param ColumnOptions|null $options Options.
	 *
	 * @return void
	 * @access public
	 */
	public function setOptions(ColumnOptions $options = null)
	{
		$this->options = isset($options) ? $options : $this->getOptionsFromDriver();
	}

	/**
	 * Returns current column options.
	 *
	 * @return ColumnOptions
	 * @access public
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * Tells if column accepts NULL as a value.
	 *
	 * @return boolean
	 * @access public
	 */
	public function isNull()
	{
		return $this->options->isNull;
	}

	/**
	 * Tells, that this column is a primary key in this table.
	 *
	 * @return boolean
	 * @access protected
	 */
	public function isPrimaryKey()
	{
		return $this->options->indexType == ColumnOptions::INDEX_PRIMARY;
	}

	/**
	 * Tells if field is unsigned.
	 *
	 * @return boolean
	 * @access public
	 * @throws ColumnException When column isn't of numeric data type.
	 */
	public function isUnsigned()
	{
		if ( !$this->isNumeric() ) {
			throw new ColumnException($this, 'numeric', ColumnException::TYPE_MISMATCH);
		}

		return in_array('unsigned', $this->options->typeOptions);
	}

	/**
	 * Returns precision for float data type.
	 *
	 * @return integer
	 * @throws ColumnException When column isn't of float data type.
	 * @access public
	 */
	public function getPrecision()
	{
		if ( !$this->isFloat() ) {
			throw new ColumnException($this, 'float', ColumnException::TYPE_MISMATCH);
		}

		list ($precision,) = explode(',', $this->options->typeLength, 2);

		return $precision;
	}

	/**
	 * Returns scale for float data type.
	 *
	 * @return integer
	 * @throws ColumnException When column isn't of float data type.
	 * @access public
	 */
	public function getScale()
	{
		if ( !$this->isFloat() ) {
			throw new ColumnException($this, 'float', ColumnException::TYPE_MISMATCH);
		}

		list (, $scale) = explode(',', $this->options->typeLength, 2);

		return $scale;
	}

	/**
	 * Returns length restriction for string data type.
	 *
	 * @return integer
	 * @throws ColumnException When column isn't of string data type.
	 * @access public
	 */
	public function getMaxLength()
	{
		if ( !$this->isString() ) {
			throw new ColumnException($this, 'string', ColumnException::TYPE_MISMATCH);
		}

		return $this->options->typeLength;
	}

	/**
	 * Returns minimal allowed value in a column.
	 *
	 * @return integer
	 * @access public
	 */
	public function getMinValue()
	{
		if ( $this->isString() || $this->isUnsigned() ) {
			return 0;
		}

		// if unsigned then max length DIV/2 * (-1)

		// TODO: get length from driver
	}

	/**
	 * Returns maximal allowed value in a column.
	 *
	 * @return integer
	 * @access public
	 */
	public function getMaxValue()
	{
		if ( $this->isString() ) {
			return $this->getMaxLength();
		}

		// if unsigned then max length DIV/2

		// TODO: get length from driver
	}

	/**
	 * Tells, that this is string data type.
	 *
	 * @return boolean
	 * @access public
	 */
	public function isString()
	{
		return $this->driver->isString($this->options->typeName);
	}

	/**
	 * Tells, that this is numeric data type.
	 *
	 * @return boolean
	 * @access public
	 */
	public function isNumeric()
	{
		return $this->isInteger() || $this->isFloat();
	}

	/**
	 * Tells, that this is integer data type.
	 *
	 * @return boolean
	 * @access public
	 */
	public function isInteger()
	{
		return $this->driver->isInteger($this->options->typeName);
	}

	/**
	 * Tells, that this is float data type.
	 *
	 * @return boolean
	 * @access public
	 */
	public function isFloat()
	{
		return $this->driver->isFloat($this->options->typeName);
	}

	/**
	 * Detects if a column exists in a table.
	 *
	 * @return boolean
	 * @access public
	 */
	public function exists()
	{
		try {
			$this->getOptionsFromDriver();
		}
		catch ( ColumnException $e ) {
			return false;
		}

		return true;
	}

	/**
	 * Determines if column definition was changed since it was originally created.
	 *
	 * @return boolean
	 * @access public
	 */
	public function changed()
	{
		if ( !$this->exists() ) {
			return false;
		}

		$column = $this->table->createColumnReflection($this->name);

		return $this->same($column);
	}

	/**
	 * Checks, that given column is a copy of this column.
	 *
	 * @param ColumnReflection $column Column to be compared.
	 *
	 * @return boolean
	 * @access public
	 */
	public function same(ColumnReflection $column)
	{
		if ( $this->name == $column->name && $this->options->same($column->getOptions()) ) {
			return true;
		}

		return $this->table->getName() == $column->table->getName();
	}

	/**
	 * Returns column options as defined in database.
	 *
	 * @return ColumnOptions Column options.
	 * @access protected
	 * @throws ColumnException When column doesn't exist in database.
	 */
	protected function getOptionsFromDriver()
	{
		$columns = $this->driver->getTableStructure($this->table->getName());

		if ( !isset($columns[$this->name]) ) {
			throw new ColumnException($this, '', ColumnException::NOT_FOUND);
		}

		return $columns[$this->name];
	}

}