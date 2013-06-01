<?php
/**
 * This file is part of the db-reflection library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/db-reflection
 */

namespace tests\aik099;


use Mockery as m,
	aik099\Database\Driver\IDriver,
	aik099\Database\Driver\MySQLIDriver,
	aik099\Database\DatabaseReflection,
	aik099\Database\TableReflection,
	aik099\Database\ColumnReflection,
	aik099\Database\ColumnOptions;

class ReflectionTestCase extends \PHPUnit_Framework_TestCase
{

	const DATABASE_FIXTURE = 'example_database';

	const TABLE_FIXTURE = '52x_Addresses';

	const FIELD_FIXTURE = 'AddressId';

	/**
	 * Driver fixture.
	 *
	 * @var MySQLIDriver
	 * @access protected
	 */
	protected $driver;

	/**
	 * Creates driver.
	 *
	 * @return void
	 * @access protected
	 */
	protected function setUp()
	{
		$this->driver = $this->getDriverMock();
	}

	/**
	 * Creates driver mock using Mockery.
	 *
	 * @return IDriver
	 * @access protected
	 */
	protected function getDriverMock()
	{
		$driver = m::mock('\aik099\Database\Driver\MySQLIDriver');

		$driver->shouldReceive('isConnected')->andReturn(true);
		$driver->shouldReceive('getTables')->andReturn($this->getTablesFixture());
		$driver->shouldReceive('getTableStructure')->with(self::TABLE_FIXTURE)->andReturn($this->getTableStructureFixture());
		$driver->shouldReceive('getDatabaseName')->andReturn(self::DATABASE_FIXTURE);

		return $driver;
	}

	/**
	 * Creates database.
	 *
	 * @return DatabaseReflection
	 * @access protected
	 */
	protected function getDatabase()
	{
		return new DatabaseReflection($this->driver);
	}

	/**
	 * Creates table.
	 *
	 * @param string $name Table name.
	 *
	 * @return TableReflection
	 * @access protected
	 */
	protected function getTable($name)
	{
		return new TableReflection($this->getDatabase(), $name);
	}

	/**
	 * Creates column.
	 *
	 * @param string        $table_name     Table name.
	 * @param string        $column_name    Column name.
	 * @param ColumnOptions $column_options Column options.
	 *
	 * @return ColumnReflection
	 * @access protected
	 */
	protected function getColumn($table_name, $column_name, ColumnOptions $column_options = null)
	{
		return new ColumnReflection($this->getTable($table_name), $column_name, $column_options);
	}

	/**
	 * Returns dummy table list.
	 *
	 * @return array
	 * @access protected
	 */
	protected function getTablesFixture()
	{
		return array(self::TABLE_FIXTURE, 'SomeOtherTable');
	}

	/**
	 * Returns table structure used in tests.
	 *
	 * @return array
	 * @access protected
	 */
	protected function getTableStructureFixture()
	{
		return array(
			'AddressId' => array(
				'Field' => 'AddressId',
				'Type' => 'int(11)',
				'Null' => 'NO',
				'Key' => 'PRI',
				'Default' => null,
				'Extra' => 'auto_increment',
			),

			'To' => array(
				'Field' => 'To',
				'Type' => 'varchar(255)',
				'Null' => 'NO',
				'Key' => '',
				'Default' => '',
				'Extra' => '',
			),

			'LastUsedAsBilling' => array(
				'Field' => 'LastUsedAsBilling',
				'Type' => 'tinyint(4) unsigned',
				'Null' => 'NO',
				'Key' => 'MUL',
				'Default' => '0',
				'Extra' => '',
			),
		);
	}

}