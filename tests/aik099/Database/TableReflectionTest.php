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


use aik099\Database\TableReflection,
	tests\aik099\ReflectionTestCase;

/**
 * Tests TableReflection class.
 */
class TableReflectionTest extends ReflectionTestCase
{

	/**
	 * Table reflection.
	 *
	 * @var TableReflection
	 */
	protected $table;

	/**
	 * Creates table reflection.
	 *
	 * @return void
	 * @access protected
	 */
	protected function setUp()
	{
		parent::setUp();

		$this->table = $this->getTable(self::TABLE_FIXTURE);
	}

	/**
	 * Tests, that associative array (key = column name) of ColumnReflection class objects are returned.
	 *
	 * @return void
	 * @access public
	 */
	public function testGetColumns()
	{
		$this->table->scan();
		$columns = $this->table->getColumns();

		$this->assertArrayHasKey(self::FIELD_FIXTURE, $columns);
		$this->assertInstanceOf('\aik099\Database\ColumnReflection', $columns[self::FIELD_FIXTURE]);
	}

	/**
	 * Tests, that correct database name is stored.
	 *
	 * @return void
	 * @access public
	 */
	public function testGetName()
	{
		$this->assertSame(self::TABLE_FIXTURE, $this->table->getName());
	}

	/**
	 * Tests, that used driver can be returned.
	 *
	 * @return void
	 * @access public
	 */
	public function testGetDriver()
	{
		$this->assertSame($this->driver, $this->table->getDriver());
	}

}