<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Upload_Type extends Form_Rule
{
	// list of arguments
	public $args = array(
		'obj' 		=> ':value',
		'allowed' 	=> '["jpg","gif","png"]',
	);

	// error message
	protected $message = ':field filetype is impossible.';

	public static function exec($obj, $allowed)
	{
		if (is_string($allowed))
		{
			$allowed = Basic::json_safe_decode($allowed);
		}

		if ( ! isset($obj['name']) && ! isset($obj['tmp_name']) )
		{
			foreach ($obj AS $obj_item)
			{
				if (Upload::type($obj_item, $allowed) !== TRUE)
				{
					return FALSE;
				}
			}
			
			return TRUE;
		}
		else 
		{
			return Upload::type($obj, $allowed);
		}
		
	}
	
	
	/** add js mask to field
	 *
	 * @return void
	 */
	protected function field_mod()
	{
		// add placeholder
		if ($this->field( )->message( ) === NULL)
		{
			foreach (explode(',', trim($this->args('allowed'), '[]')) AS $value)
			{
				$value = trim($value, '"\'');
				$out[] = $value;
			}
		
			$this->field( )->message(__u('allowed types: :types', array(':types' => implode(', ', $out))).'.');
		}
	}
}