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

namespace tests\aik099\Database\Driver;


use aik099\Database\Driver\MySQLIDriver,
	tests\aik099\ReflectionTestCase;

/**
 * Tests MySQLIDriver class.
 *
 * @requires extension mysqli
 */
class MySQLIDriverTest extends ReflectionTestCase
{

	/**
	 * Creates driver.
	 *
	 * @return void
	 * @access protected
	 */
	protected function setUp()
	{
		$this->driver = new MySQLIDriver(array(
			'host' => $_SERVER['FIXTURE_HOST'],
			'user' => $_SERVER['FIXTURE_USER'],
			'pass' => $_SERVER['FIXTURE_PASS'],
			'db' => $_SERVER['FIXTURE_DB'],
		));
	}

	/**
	 * Tests, that on connection failure an exception is thrown.
	 *
	 * @return void
	 * @access public
	 * @expectedException \aik099\Database\Driver\DriverException
	 */
	public function testConnectionError()
	{
		$driver = new MySQLIDriver(array(
			'host' => 'localhost',
			'user' => 'wrong-user',
			'pass' => 'wrong-pass',
			'db' => 'wrong-db',
		));

		$driver->connect();
	}

	/**
	 * Tests, that connection to database can be made.
	 *
	 * @return void
	 * @access public
	 */
	public function testConnectionSuccessful()
	{
		$this->assertTrue($this->driver->connect());
		$this->assertTrue($this->driver->isConnected());
	}

	/**
	 * Tests, that tables names in a database are returned in a proper format.
	 *
	 * @return void
	 * @access public
	 */
	public function testGetTables()
	{
		$this->assertContains(self::TABLE_FIXTURE, $this->driver->getTables());
	}

	/**
	 * Tests, that table structure is returned correctly.
	 *
	 * @return void
	 * @access public
	 */
	public function testGetTableStructure()
	{
		$table_structure = $this->driver->getTableStructure(self::TABLE_FIXTURE);
		$this->assertArrayHasKey(self::FIELD_FIXTURE, $table_structure);

		$expected = array(
			'Field' => 'AddressId',
			'Type' => 'int(11)',
			'Null' => 'NO',
			'Key' => 'PRI',
			'Default' => null,
			'Extra' => 'auto_increment',
		);

		$this->assertSame($expected, $table_structure[self::FIELD_FIXTURE]);
	}

	/**
	 * Checks, that string data types are properly recognized.
	 *
	 * @param string $data_type
	 *
	 * @return void
	 * @access public
	 * @dataProvider stringTypeDataProvider
	 */
	public function testStringTypeDetection($data_type)
	{
		$this->assertTrue($this->driver->isString($data_type));
	}

	/**
	 * @return array
	 */
	public function stringTypeDataProvider()
	{
		$ret = array(
			'varchar', 'text', 'mediumtext', 'longtext', 'date',
			'datetime', 'time', 'timestamp', 'char', 'year', 'enum',
			'set',
		);

		return $this->prepareProviderData($ret);
	}

	/**
	 * Checks, that integer data types are properly recognized.
	 *
	 * @param string $data_type
	 *
	 * @return void
	 * @access public
	 * @dataProvider integerTypeDataProvider
	 */
	public function testIntegerTypeDetection($data_type)
	{
		$this->assertTrue($this->driver->isInteger($data_type));
	}

	/**
	 * @return array
	 */
	public function integerTypeDataProvider()
	{
		$ret = array('smallint', 'mediumint', 'int', 'bigint', 'tinyint');

		return $this->prepareProviderData($ret);
	}

	/**
	 * Checks, that string data types are properly recognized.
	 *
	 * @param string $data_type
	 *
	 * @return void
	 * @access public
	 * @dataProvider floatTypeDataProvider
	 */
	public function testFloatTypeDetection($data_type)
	{
		$this->assertTrue($this->driver->isFloat($data_type));
	}

	/**
	 * @return array
	 */
	public function floatTypeDataProvider()
	{
		$ret = array('float', 'double', 'decimal');

		return $this->prepareProviderData($ret);
	}

	/**
	 * Prepares given data to act as data provider data.
	 *
	 * @param array $data Data.
	 *
	 * @return array
	 * @access protected
	 */
	protected function prepareProviderData($data)
	{
		$ret = array();

		foreach ($data as $data_type) {
			$ret[] = (array)$data_type;
		}

		return $ret;
	}

	/**
	 * Tests, that driver properly returns database name.
	 *
	 * @return void
	 * @access public
	 */
	public function testGetDatabaseName()
	{
		$this->assertSame($_SERVER['FIXTURE_DB'], $this->driver->getDatabaseName());
	}

}