<?php
/**
 * This file is part of the db-reflection library.
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 *
 * @copyright Alexander Obuhovich <aik.bold@gmail.com>
 * @link      https://github.com/aik099/db-reflection
 */

namespace aik099\Database\Exception;


class ReflectionException extends \Exception
{

	/**
	 * Builds exception message automatically.
	 *
	 * @param string     $message  Message.
	 * @param integer    $code     Code.
	 * @param \Exception $previous Previous exception.
	 *
	 * @access public
	 */
	public function __construct($message = '', $code = 0, \Exception $previous = null)
	{
		parent::__construct($this->generateMessage($message, $code), $code, $previous);
	}

	/**
	 * Returns message by code.
	 *
	 * @param string  $message Message.
	 * @param integer $code    Code.
	 *
	 * @return string
	 * @access protected
	 */
	protected function generateMessage($message, $code)
	{
		if ( preg_match_all('/{(.*?)}/', $message, $regs) ) {
			$replacements = $this->getReplacements();

			foreach ($regs[1] as $tag) {
				$replacement = isset($replacements[$tag]) ? $replacements[$tag] : $tag;
				$message = str_replace($tag, $replacement, $message);
			}
		}

		return $message;
	}

	/**
	 * Returns possible substitutions in exception message.
	 *
	 * @return array
	 * @access protected
	 */
	protected function getReplacements()
	{
		return array();
	}

}