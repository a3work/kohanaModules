<?php defined('SYSPATH') or die('No direct script access.');

/**
 * @name		Controller editor
 * @category	common_modules
 * @package 	editor
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-05-29
 **/
class Controller_Editor extends Controller_Template
{
	public $template = 'cms_editor';

	/**
	 * Fetch editor by session setting ID and content ID
	 */
	public function action_id( )
	{
		try
		{
			$this->template->content = Editor::factory((int) $this->request->param('settings_id'))
										->id((int) $this->request->param('item_id'))
										->render( );
		}
		catch (Editor_Exception $e)
		{
			switch ($e->getCode( ))
			{
				case Editor::ERROR_LOADING_SESSION:
					$this->template->content = View::factory('editor_error_06')->render( );
					break;
				default:
					$this->template->content = $e->getMessage( );
			}
		}
	}

	/**
	 * Specify editor settings and generate editor code
	 */
	public function action_param( )
	{
		$this->template->content = 	Editor::factory(
										$this->request->param('class'),
										$this->request->param('orm'),
										$this->request->param('field')
									)
									->id((int) $this->request->param('item_id'))
									->render( );
	}
}