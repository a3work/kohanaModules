<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Page_Exists extends Form_Rule
{
	/**
	 * @var array		rule arguments
	 */
	public $args = array(
		'valid' => ':validation',
	);

	/**
	 * @var string		error message
	 */
	protected $message = "page already exists";

	/** rule description
	 *
	 * @param 	array	validation object
	 * @return 	boolean
	 */
	public static function exec($validation)
	{
		// generate link of new page
// 		if ($validation['href'] != '')
// 		{
// 			$href = File::encode_uri($validation['href']);
// 		}
// 		else
// 		{
		$href = $validation['parent'].DIRECTORY_SEPARATOR.File::filter_url($validation['alias'] != '' ? $validation['alias'] : $validation['header']);
// 		}

		if (Route::name(Request::factory($href)->route( )) == 'default')
		{
			// find page for current uri
			return ! Page::factory($href)->exists( );
		}
		else
		{
			// external route exists -- return FALSE
			return FALSE;
		}
	}
}