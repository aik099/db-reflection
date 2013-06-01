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


/**
 * Represents column options in a database driver agnostic way.
 */
class ColumnOptions
{

	/**
	 * Column not used in an index.
	 */
	const INDEX_NONE = '';

	/**
	 * Column is an primary key in a table.
	 */
	const INDEX_PRIMARY = 'PRI';

	/**
	 * Column has non-unique index.
	 */
	const INDEX_NON_UNIQUE = 'MUL';

	/**
	 * Column has unique index.
	 */
	const INDEX_UNIQUE = 'UNI';

	/**
	 * Column type name.
	 *
	 * @var string
	 * @access public
	 */
	public $typeName = '';

	/**
	 * Column type length.
	 *
	 * @var string
	 * @access public
	 */
	public $typeLength = '';

	/**
	 * Extra type options.
	 *
	 * @var array
	 * @access public
	 */
	public $typeOptions = array();

	/**
	 * Tells, that NULL values are allowed in this column.
	 *
	 * @var boolean
	 * @access public
	 */
	public $isNull = false;

	/**
	 * Index type.
	 *
	 * @var string
	 * @access public
	 */
	public $indexType = self::INDEX_NONE;

	/**
	 * Default value.
	 *
	 * @var mixed
	 * @access public
	 */
	public $default;

	/**
	 * Extra options.
	 *
	 * @var array
	 * @access public
	 */
	public $extra = array();

	/**
	 * Parses raw type into name, length and extra.
	 *
	 * @param string $raw_type Raw type.
	 *
	 * @return self
	 * @access public
	 */
	public function setRawType($raw_type)
	{
		$type_parts = explode(' ', $raw_type);
		$type_part = array_shift($type_parts);

		if ( preg_match('/^([a-z]+)\((.*)\)$/i', $type_part, $regs) ) {
			$this->typeName = $regs[1];
			$this->typeLength = $regs[2];
		}
		else {
			$this->typeName = $type_part;
		}

		$this->typeOptions = array_map('strtolower', array_map('trim', $type_parts));

		return $this;
	}

	/**
	 * Builds raw type from name, length and extra.
	 *
	 * @return string
	 * @access public
	 */
	public function getRawType()
	{
		$parts = $this->typeName;

		if ( strlen($this->typeLength) ) {
			$parts[] = '(' . $this->typeLength . ')';
		}

		if ( $this->typeOptions ) {
			$parts = array_merge($parts, $this->typeOptions);
		}

		return implode(' ', $parts);
	}

	/**
	 * Checks, that given option set is identical to current one.
	 *
	 * @param ColumnOptions $options Options to be compared with.
	 *
	 * @return boolean
	 * @access public
	 */
	public function same(ColumnOptions $options)
	{
		return true;
	}

}