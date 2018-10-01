<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Access module exceptions.
 *
 * @package    Access
 * @category   Exceptions
 * @author     Stanislav U. Alkimovich
 */
class Kohana_Access_Exception extends Kohana_Exception {
	
	/** Define default message of Access_Exception
	 *
	 * @param	string	message
	 * @param	array	variables
	 * @param	integer	code
	 *
	 * @return	void
	 */
	public function __construct($message = NULL, array $variables = NULL, $code = 0)
	{
		if (empty($message))
		{
			$message = __('access denied');
		}
		
		parent::__construct($message, $variables, $code);
	}
	
}
