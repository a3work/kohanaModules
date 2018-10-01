<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Editor base class: fetch and save data
 * @category	Common
 * @package 	Editor
 * @author 		A.St.
 * @date 		2013-05-22
 *
 **/

abstract class Editor_Base extends Editor_Permissions
{
	const VALUE_KEY = 'data';

	// settings session ID array(orm_field => id)
	protected $settings_id = array( );

	// orm object
	protected $orm_instance;

	// ORM name
	protected $orm;

	// field name
	protected $field;

	// ORM item ID
	protected $id;

	// editable object's data
	protected $data;

	// edit result
	protected $processed_data;

	// javascript callback function
	protected $js_callback;

	// attendant data for save
	protected $attendant = array( );

	// wrap condition
	protected $wrap_if = TRUE;

	/**
	 * Render form
	 *
	 * @return 	string HTML / array values
	 */
	abstract protected function form( );

	/**
	 * Convert data before insert into database
	 *
	 * @param array 	data
	 * @return data
	 */
	protected function prepare( )
	{
		/** Don't need to convert processed_data, reload this method in your subclass **/
	}


	/**
	 * Render content for replacing html of current item
	 *
	 * @return void
	 */
	protected function prepare_response( )
	{
// 		return $this->processed_data( );
	}

	/**
	 * Object constructor
	 *
	 * @param string 		ORM name
	 * @param string		field
	 */
	public function __construct($orm = NULL, $field = NULL)
	{
		// set orm name
		if (isset($orm))
		{
			$this->orm($orm);
		}

		// set field name
		if (isset($field))
		{
			$this->field($field);
		}
		
		InclStream::instance( )->add('editor.css');
	}

	/**
	 * Standart setter/getter
	 *
	 * @param string 	variable name
	 * @param array		parameters
	 *
	 * @return mixed
	 */
	public function __call($var, $args = NULL)
	{
		if (is_array($args) && count($args) > 0)
		{
			$this->$var = $args[0];
			return $this;
		}

		return $this->$var;
	}

	/**
	 * specify and return ID of DB record
	 *
	 * @param int 	ID;
	 * @return int
	 */
	public function id($id = NULL)
	{
		if (isset($id))
		{
			$this->id = (int) $id;

			$this->clear( );

			return $this;
		}

		if ( ! isset($this->id) || $this->id == 0)
		{
			throw new Editor_Exception('Empty record ID', NULL, Editor::ERROR_EMPTY_ID);
		}

		return $this->id;
	}

	/**
	 * Set and get attendant data for execution one update query
	 *
	 * @param mixed		key
	 * @param mixed		value for save
	 * @return mixed	setter: (object) this | getter: (array) data
	 */
	protected function attendant($key = NULL, $value = NULL)
	{
		if (isset($key))
		{
			$this->attendant[$key] = $value;

			return $this;
		}

		return $this->attendant;
	}

	/**
	 * Clear attendant data array
	 *
	 * @return object 	this
	 */
	protected function clear_attendant( )
	{
		$this->attendant = array( );
	}

	/**
	 * Define ORM object and save it in variable
	 *
	 * @return object
	 */
	protected function orm_instance( )
	{
		if ( ! isset($this->orm_instance) || ! $this->orm_instance->loaded( ))
		{
			try
			{
				$this->orm_instance = ORM::factory($this->orm( ), $this->id( ));
			}
			catch (Exception $e)
			{
				throw new Editor_Exception('Cannot load ORM.', NULL, Editor::ERROR_LOADING_ORM, array('error' => (string) $e));
			}
		}

		return $this->orm_instance;
	}

	/**
	 * load data from database
	 */
	protected function data( )
	{
		if ( ! isset($this->data))
		{
			$this->data = $this->orm_instance( )->{$this->field( )};
		}

		return $this->data;
	}

	/**
	 * save data into database
	 *
	 * @param array		data for save
	 * @return void
	 */
	protected function save( )
	{
		// prepare data
		$this->prepare( );

		// save data
		$this->orm_instance( )->values(array_merge(array($this->field( ) => $this->processed_data( )), $this->attendant( )))->save( );

		// clear attendant data
		$this->clear_attendant( );
	}

	/**
	 * publish answer
	 */
	public function publish_response( )
	{
		$this->prepare_response( );

		return View::factory(	'editor_response',
								array(
									'html' 			=> $this->processed_data( ),
									'js_callback' 	=> $this->js_callback( ),
								)
							)
				->render( );
	}


	/**
	 * specify and return key of session settings array
	 *
	 * @param int 	key;
	 * @return int
	 */
	public function settings_id($id = NULL)
	{
		if ( ! isset($this->settings_id[$this->field( )]))
		{
			if ( ! isset($id))
			{
				$this->settings_id[$this->field( )] = Editor::set(array(
					get_class($this),
					$this->orm( ),
					$this->field( ),
				));
			}
			else
			{
				$this->settings_id[$this->field( )] = $id;
			}
		}

		return $this->settings_id[$this->field( )];
	}

	/**
	 * Wrap edited text to marker tags
	 *
	 * @param string 	text
	 * @return string 	wrapped text
	 */
	public function wrap($text)
	{
		if ( ! $this->wrap_if( ) || ! $this->check_permissions( ) || $this->check_permissions( ) && Cookie::store(Site::config('cms')->edit_switch_var) != 'checked')
		{
			// set wrap condition for next wrap to default
			$this->wrap_if(TRUE);

			return $text;
		}

		InclStream::instance( )->add('cms.init.js', FALSE, 1);

		if ( ! isset($view))
		{
			$view = View::factory('editor_wrapper');
		}

		$view->settings_id 	= $this->settings_id( );

		try
		{
			$view->id = $this->id( );
		}
		catch(Editor_Exception $e)
		{
			return $text;
		}

		$view->text = $text;

		return $view->render( );
	}

	/**
	 * Render form and save data
	 *
	 * @return string 		output HTML
	 */
	public function render( )
	{
		// check permissions
		if ( ! $this->check_permissions( ))
		{
			throw new Editor_Exception('Access denied', NULL, Editor::ERROR_ACCESS_DENIED);
		}

		// preform data
		// render form
		$out = $this->form( );

		// if fetch data
		if (is_array($out))
		{
			$this->processed_data($out[Editor_Base::VALUE_KEY]);

			// save data
			$this->save( );

			// generate final message
			$out = $this->publish_response( );
		}

		return $out;
	}

	/**
	 * Reset orm and data
	 *
	 * @return this
	 */
	public function clear( )
	{
		$this->orm_instance( )->clear( );

		$this->data = NULL;

		return $this;
	}
}