<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Extends Kohana_HTML helper
 *
 * Add html composite elements factory
 *
 * @category	Common
 * @package		Site
 * @author		Stanislav <a3.work@gmail.com>
 * @date 		20.12.13
 */
 
class Html extends Kohana_HTML {
	/** HTML composite elements factory
	* 
	* @param	string	classname
	* @param	array	constructor parameters
	* @return	object
	*/
	public static function factory($classname, array $parameters = array( ))
	{
		$classname = 'html_'.$classname;
		
		if (class_exists($classname))
		{
			$reflection = new ReflectionClass($classname);
			
			$obj = $reflection->newInstanceArgs($parameters);
				

			/* :TODO: add check of instance */
// 			if ( ! $obj instanceof Menu)
// 			{
// 				throw new Menu_Exception("Class :menu not extends Menu.", array(':menu' => $classname));
// 			}
		}
		else
		{
			throw new Menu_Exception("Cannot find :classname.", array(':classname' => $classname));
		}
		
		return $obj;
	}

}