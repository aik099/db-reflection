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
	aik099\Database\ColumnReflection,
	aik099\Database\Exception\DriverException;

/**
 * Driver for accessing MySQL databases.
 */
class MySQLIDriver implements IDriver
{

	/**
	 * Reference to a database.
	 *
	 * @var \mysqli
	 * @access private
	 */
	private $_db = null;

	/**
	 * Determines if connection to database was made.
	 *
	 * @var boolean
	 * @access private
	 */
	private $_connected = false;

	/**
	 * Database connection settings.
	 *
	 * @var array
	 * @access private
	 */
	private $_settings = array(
		'host' => '',
		'user' => '',
		'pass' => '',
		'db' => '',
	);

	/**
	 * List of database tables.
	 *
	 * @var array
	 * @access protected
	 */
	protected $tableCache = array();

	/**
	 * Structure of scanned database tables.
	 *
	 * @var array
	 * @access protected
	 */
	protected $structureCache = array();

	/**
	 * Creates instance of MySQLIDriver.
	 *
	 * @param array $settings Connection settings.
	 *
	 * @access public
	 */
	public function __construct(array $settings)
	{
		$this->_settings = $settings;
	}

	/**
	 * Connects to a database.
	 *
	 * @param boolean $force Try reconnecting if already connected.
	 *
	 * @return boolean
	 * @access public
	 * @throws DriverException When connection wasn't made.
	 */
	public function connect($force = false)
	{
		if ( !$this->_connected || $force ) {
			$db = new \mysqli(
				$this->_settings['host'], $this->_settings['user'],
				$this->_settings['pass'], $this->_settings['db']
			);

			if ( $db->connect_error ) {
				throw new DriverException(
					sprintf('Error, while connecting to database: #%s: %s', $db->connect_errno, $db->connect_error)
				);
			}

			$this->_db = $db;
			$this->_connected = true;
		}

		return $this->_connected;
	}

	/**
	 * Tells, that connection to database is made.
	 *
	 * @return boolean
	 * @access protected
	 */
	public function isConnected()
	{
		return $this->_connected;
	}

	/**
	 * Returns name of database.
	 *
	 * @return string
	 * @access public
	 */
	public function getDatabaseName()
	{
		return $this->_settings['db'];
	}

	/**
	 * Returns list of table names in the database.
	 *
	 * @param boolean $force Query database for updated info.
	 *
	 * @return array
	 * @access public
	 */
	public function getTables($force = false)
	{
		if ( !$this->tableCache || $force ) {
			$this->tableCache = $this->_query('SHOW TABLES', null, true);
		}

		return $this->tableCache;
	}

	/**
	 * Returns structure of given table.
	 *
	 * @param string  $table_name Table name.
	 * @param boolean $force      Query database for updated info.
	 *
	 * @return ColumnOptions[]
	 * @access public
	 */
	public function getTableStructure($table_name, $force = false)
	{
		if ( !isset($this->structureCache[$table_name]) || $force ) {
			$ret = array();
			$table_structure = $this->_query('DESCRIBE ' . $table_name, 'Field');

			foreach ($table_structure as $name => $raw_options) {
				$ret[$name] = $this->getColumnOptions($raw_options);
			}

			$this->structureCache[$table_name] = $ret;
		}

		return $this->structureCache[$table_name];
	}

	/**
	 * Adds column to a database.
	 *
	 * @param ColumnReflection $column Column.
	 *
	 * @return boolean
	 * @access public
	 */
	public function addColumn(ColumnReflection $column)
	{
		$sql = 'ALTER TABLE ' . $column->getTable()->getName() . '
				ADD ' . $this->buildColumnClause($column);

		return $this->_query($sql);
	}

	/**
	 * Updates column in a database.
	 *
	 * @param ColumnReflection $column   Column.
	 * @param string           $new_name Column name override.
	 *
	 * @return boolean
	 * @access public
	 */
	public function updateColumn(ColumnReflection $column, $new_name = null)
	{
		if ( !isset($new_name) ) {
			// not a rename -> keep old name
			$new_name = $old_name = $column->getName();
		}
		else {
			$old_name = $column->getName();
		}

		$sql = 'ALTER TABLE ' . $column->getTable()->getName() . '
				CHANGE ' . $old_name . ' ' . $this->buildColumnClause($column, $new_name);

		return $this->_query($sql);
	}

	/**
	 * Deletes column from a database.
	 *
	 * @param ColumnReflection $column Column.
	 *
	 * @return boolean
	 * @access public
	 */
	public function deleteColumn(ColumnReflection $column)
	{
		$sql = 'ALTER TABLE ' . $column->getTable()->getName() . '
				DROP ' . $column->getName();

		return $this->_query($sql);
	}

	/**
	 * Builds clause, that would represent this column in ALTER queries.
	 *
	 * @param ColumnReflection $column Column.
	 * @param boolean          $name   Use another name instead of column's own name.
	 *
	 * @return string
	 * @access protected
	 */
	protected function buildColumnClause(ColumnReflection $column, $name = null)
	{
		$parts = array();
		$options = $column->getOptions();

		$parts[] = isset($name) ? $name : $column->getName();
		$parts[] = $options->getRawType();
		$parts[] = $options->isNull ? '' : 'NOT NULL';

		if ( $options->extra ) {
			$parts = array_merge($parts, $options->extra);
		}
		else {
			$parts[] = 'DEFAULT ' . $this->_escape($options->default);
		}

		return implode(' ', $parts);
	}

	/**
	 * Creates column options object from given raw options.
	 *
	 * @param array $raw_options Raw options.
	 *
	 * @return ColumnOptions
	 * @access protected
	 */
	protected function getColumnOptions(array $raw_options)
	{
		$options = $this->createColumnOptions();

		$options->setRawType($raw_options['Type']);
		$options->isNull = $raw_options['Null'] == 'YES';
		$options->indexType = $raw_options['Key'];
		$options->default = $raw_options['Default'];
		$options->extra = explode(' ', $raw_options['Extra']);

		return $options;
	}

	/**
	 * Creates empty column options.
	 *
	 * @return ColumnOptions
	 * @access protected
	 */
	protected function createColumnOptions()
	{
		return new ColumnOptions();
	}

	/**
	 * Tells, that this is string data type.
	 *
	 * @param string $data_type Data type.
	 *
	 * @return boolean
	 * @access public
	 */
	public function isString($data_type)
	{
		$regexp = '/^(varchar|text|mediumtext|longtext|date|datetime|time|timestamp|char|year|enum|set)$/i';

		return preg_match($regexp, $data_type) > 0;
	}

	/**
	 * Tells, that this is integer data type.
	 *
	 * @param string $data_type Data type.
	 *
	 * @return boolean
	 * @access public
	 */
	public function isInteger($data_type)
	{
		return preg_match('/^(smallint|mediumint|int|bigint|tinyint)$/i', $data_type) > 0;
	}

	/**
	 * Tells, that this is float data type.
	 *
	 * @param string $data_type Data type.
	 *
	 * @return boolean
	 * @access public
	 */
	public function isFloat($data_type)
	{
		return preg_match('/^(float|double|decimal)$/i', $data_type) > 0;
	}

	/**
	 * Retrieves data from database.
	 *
	 * @param string      $sql               Database query.
	 * @param string|null $key_field         Key field.
	 * @param boolean     $first_column_only Return only value of first column.
	 *
	 * @return array|boolean
	 * @throws DriverException When sql error occurs.
	 */
	private function _query($sql, $key_field = null, $first_column_only = false)
	{
		$this->connect();

		$rows = array();
		$rs = $this->_db->query($sql);

		if ( $rs === true ) {
			// query succeeded, but haven't returned any data
			return true;
		}
		elseif ( $rs === false ) {
			throw new DriverException(
				sprintf('Query error: #%s: %s.' . PHP_EOL . 'Query: %s', $this->_db->errno, $this->_db->error, $sql)
			);
		}

		$i = 0;

		while ( $row = $rs->fetch_assoc() ) {
			$key = isset($key_field) ? $row[$key_field] : $i;
			$rows[$key] = $first_column_only ? reset($row) : $row;
			$i++;
		}

		$rs->close();

		return $rows;
	}

	/**
	 * Escapes string to prevent SQL injections.
	 *
	 * @param string $string String to escape.
	 *
	 * @return string
	 * @access private
	 */
	private function _escape($string)
	{
		if ( is_null($string) ) {
			return 'NULL';
		}

		return "'" . $this->_db->real_escape_string($string) . "'";
	}

}