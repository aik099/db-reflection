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


use aik099\Database\ColumnReflection,
	tests\aik099\ReflectionTestCase;

/**
 * Tests ColumnReflection class.
 */
class ColumnReflectionTest extends ReflectionTestCase
{

	/**
	 * Column reflection.
	 *
	 * @var ColumnReflection
	 */
	protected $column;

	/**
	 * Creates table reflection.
	 *
	 * @return void
	 * @access protected
	 */
	protected function setUp()
	{
		parent::setUp();

		switch ( $this->getName() ) {
			case 'xx':

				break;

			default:
				$this->column = $this->getColumn(self::TABLE_FIXTURE, self::FIELD_FIXTURE, array());
				break;
		}
	}

	/**
	 * Tests, that exception is thrown when unknown column is given.
	 *
	 * @return void
	 * @access public
	 * @expectedException \InvalidArgumentException
	 */
	public function testUnknownColumn()
	{
		new ColumnReflection($this->driver, self::TABLE_FIXTURE, 'UnknownColumn');
	}

	/**
	 * Tests, that correct database name is stored.
	 *
	 * @return void
	 * @access public
	 */
	public function testGetName()
	{
		$this->assertSame(self::FIELD_FIXTURE, $this->column->getName());
	}

	/**
	 * Tests, that used driver can be returned.
	 *
	 * @return void
	 * @access public
	 */
	public function testGetDriver()
	{
		$this->assertSame($this->driver, $this->column->getDriver());
	}

}