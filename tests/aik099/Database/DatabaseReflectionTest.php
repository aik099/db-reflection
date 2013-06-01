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


use aik099\Database\DatabaseReflection,
	tests\aik099\ReflectionTestCase;

/**
 * Tests DatabaseReflection class.
 */
class DatabaseReflectionTest extends ReflectionTestCase
{

	/**
	 * Database reflection fixture.
	 *
	 * @var DatabaseReflection
	 */
	protected $database;

	/**
	 * Creates database reflection.
	 *
	 * @return void
	 * @access protected
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->database = $this->getDatabase();
	}

	/**
	 * Tests, that initially database doesn't have any tables in it.
	 *
	 * @return void
	 * @access public
	 */
	public function testEmpty()
	{
		$this->assertCount(0, $this->database);
	}

	/**
	 * Tests, that method supports chaining.
	 *
	 * @param boolean $force Force.
	 *
	 * @return void
	 * @access public
	 * @dataProvider scanDataProvider
	 */
	public function testScanChaining($force)
	{
		$this->assertSame($this->database, $this->database->scan($force));
	}

	/**
	 * Returns test data for "scan" method.
	 *
	 * @return array
	 * @access public
	 */
	public function scanDataProvider()
	{
		return array(
			array(false),
			array(true),
		);
	}

	/**
	 * Tests, that tables are scanned.
	 *
	 * @return void
	 * @access public
	 */
	public function testScanRegular()
	{
		$this->database->scan();
		$this->assertCount(count($this->getTablesFixture()), $this->database);
	}

	/**
	 * Tests, that tables are re-scanned.
	 *
	 * @return void
	 * @access public
	 * @depends testScanRegular
	 */
	public function testScanForce()
	{
		$this->database->addTable($this->getTable('DummyTable'));

		$this->database->scan(true);
		$this->assertCount(count($this->getTablesFixture()), $this->database);
	}

	/**
	 * Tests, that database is created with empty tables list.
	 *
	 * @return void
	 * @access protected
	 */
	public function testGetTablesEmpty()
	{
		$this->assertCount(0, $this->database->getTables());
	}

	/**
	 * Tests, that associative array (key = table name) of TableReflection class objects are returned.
	 *
	 * @return void
	 * @access public
	 * @depends testScanRegular
	 */
	public function testGetTablesNonEmpty()
	{
		$tables = $this->database->scan()->getTables();
		$this->assertArrayHasKey(self::TABLE_FIXTURE, $tables);
		$this->assertInstanceOf('\aik099\Database\TableReflection', $tables[self::TABLE_FIXTURE]);
	}

	/**
	 * Tests, that method supports chaining.
	 *
	 * @return void
	 * @access public
	 */
	public function testAddChaining()
	{
		$this->assertSame($this->database, $this->database->addTable($this->getTable('DummyTable')));
	}

	/**
	 * Tests, that item can be added.
	 *
	 * @return void
	 * @access public
	 * @depends testEmpty
	 */
	public function testAdd()
	{
		$this->database->addTable($this->getTable('DummyTable'));
		$this->assertCount(1, $this->database);
	}

	/**
	 * Checks, that previously added table can be accessed.
	 *
	 * @return void
	 * @access public
	 * @depends testAdd
	 */
	public function testGetTableExisting()
	{
		$this->database->addTable($this->getTable('DummyTable'));
		$table = $this->database->getTable('DummyTable');

		$this->assertInstanceOf('\aik099\Database\TableReflection', $table);
		$this->assertEquals('DummyTable', $table->getName());
	}

	/**
	 * Tests, that exception is thrown when missing table is accessed.
	 *
	 * @return void
	 * @access public
	 * @expectedException \InvalidArgumentException
	 */
	public function testGetTableMissing()
	{
		$this->database->getTable('MissingTable');
	}

	/**
	 * Tests, that database exists.
	 *
	 * @return void
	 * @access public
	 */
	public function testExists()
	{
		$this->assertTrue($this->database->exists());
	}

	/**
	 * Tests, that exists after it's added.
	 *
	 * @return void
	 * @access public
	 */
	public function testTableExists()
	{
		$this->assertTrue($this->database->tableExists(self::TABLE_FIXTURE));
	}

	/**
	 * Tests, that table doesn't exist in a database.
	 *
	 * @return void
	 * @access public
	 */
	public function testTableNotExists()
	{
		$this->assertFalse($this->database->tableExists('DummyTable'));
	}

	/**
	 * Tests, that correct database name is stored.
	 *
	 * @return void
	 * @access public
	 */
	public function testGetName()
	{
		$this->assertSame(self::DATABASE_FIXTURE, $this->database->getName());
	}

	/**
	 * Tests, that used driver can be returned.
	 *
	 * @return void
	 * @access public
	 */
	public function testGetDriver()
	{
		$this->assertSame($this->driver, $this->database->getDriver());
	}

}