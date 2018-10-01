<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Basic file controller
 * @package 	Files
 * @author 		Stanislav U. Alkimovich <a3.work@gmail.com>
 * @date 		20.07.14
 *
 **/
class Kohana_Controller_Filesystem extends Controller_Cms
{
	/**
	 * @var Kohana_File		file object
	 */
	protected $_file;

	/** Standard kohana function
	 *
	 * @return 	void
	 */
	public function before( )
	{
		parent::before( );
	
		$type = $this->request->param('type');
		
		if ($type != 'File' && ! is_subclass_of($type, 'File'))
		{
			throw new File_Exception('Class :class shall be a subclass of File');
		}

		$this->_file = call_user_func_array(array($type, 'factory'), array($this->request->param('path')));
	}

	/** call queried method of file object
	 *
	 * @param 	string	method name
	 * @param 	array	parameters
	 * @return 	void
	 */
	public function __call($action, $params = array( ))
	{
		/* bind controller's variables */
		$this->_file->request = $this->request;
		$this->_file->response = $this->response;
		$this->_file->template = $this->template;
		
		$this->_file->controller = $this;
	
// 		die('done');
	
		// call action of current file object, fetch result
		// File's methods named action_* will return result or processed request and return void.
		$result = call_user_func_array(array($this->_file, $action), array_merge($params, $this->request->query( )));

		/* unset controller's variables */
		unset($this->_file->request);
		unset($this->_file->response);
		unset($this->_file->template);
		unset($this->_file->controller);
		
		// publish result if exists
		if (is_array($result))
		{
			if ($this->request->is_ajax( ))
			{
				$this->auto_render = FALSE;
				$this->response->body($result);
			}
			else
			{
				// load parameters to cms view
				$this->template->set($result);
			}
		}
		else 
		{
			if ($this->request->is_ajax( ))
			{
				$this->auto_render = FALSE;
				$this->response->body($result);
			}
		}
			
	}
}