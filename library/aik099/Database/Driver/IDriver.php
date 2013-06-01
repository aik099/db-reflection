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

namespace aik099\Database\Driver;


use aik099\Database\ColumnOptions,
	aik099\Database\ColumnReflection;

/*
 * Interface, that all drivers, that are to be used in reflections must implement.
 */
interface IDriver
{

	/**
	 * Connects to a database.
	 *
	 * @param boolean $force Try reconnecting if already connected.
	 *
	 * @return boolean
	 * @access public
	 */
	public function connect($force = false);

	/**
	 * Tells, that connection to database is made.
	 *
	 * @return boolean
	 * @access protected
	 */
	public function isConnected();

	/**
	 * Returns name of database.
	 *
	 * @return string
	 * @access public
	 */
	public function getDatabaseName();

	/**
	 * Returns list of table names in the database.
	 *
	 * @param boolean $force Query database for updated info.
	 *
	 * @return array
	 * @access public
	 */
	public function getTables($force = false);

	/**
	 * Returns structure of given table.
	 *
	 * @param string  $table_name Table name.
	 * @param boolean $force      Query database for updated info.
	 *
	 * @return ColumnOptions[]
	 * @access public
	 */
	public function getTableStructure($table_name, $force = false);

	/**
	 * Adds column to a database.
	 *
	 * @param ColumnReflection $column Column.
	 *
	 * @return boolean
	 * @access public
	 */
	public function addColumn(ColumnReflection $column);

	/**
	 * Updates column in a database.
	 *
	 * @param ColumnReflection $column   Column.
	 * @param string           $new_name Column name override.
	 *
	 * @return boolean
	 * @access public
	 */
	public function updateColumn(ColumnReflection $column, $new_name = null);

	/**
	 * Deletes column from a database.
	 *
	 * @param ColumnReflection $column Column.
	 *
	 * @return boolean
	 * @access public
	 */
	public function deleteColumn(ColumnReflection $column);

	/**
	 * Tells, that this is string data type.
	 *
	 * @param string $data_type Data type.
	 *
	 * @return boolean
	 * @access public
	 */
	public function isString($data_type);

	/**
	 * Tells, that this is integer data type.
	 *
	 * @param string $data_type Data type.
	 *
	 * @return boolean
	 * @access public
	 */
	public function isInteger($data_type);

	/**
	 * Tells, that this is float data type.
	 *
	 * @param string $data_type Data type.
	 *
	 * @return boolean
	 * @access public
	 */
	public function isFloat($data_type);

}