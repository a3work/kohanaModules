<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Form constructor superclass
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-21
 *
 * @todo		field types shortcuts
 * @todo		return changes mode: multiple results comparison
 * @todo		field clone and field type change
 **/

class Kohana_Form_Base/* extends Kohana_Form_Field_Shortcut*/
{
	// form id stub
	protected $id = NULL;

	// form pid stub
	protected $parent = NULL;

	// form label
	protected $label = '';

	// form class for css customization
	protected $class;

	// data sending method
	protected $method = 'post';

	// form enctype
	protected $enctype;

	// form action attribute
	protected $action;

	// form target attribute
	protected $target;

	// form template -- view file name
	protected $template;

	/* render flags begins */
	
	/**
	 * @var boolean		use form validation
	 */
	protected $use_validation = TRUE;

	/**
	 * @var boolean		generate javascript code of validation 
	 */
	protected $use_js_validation = FALSE;

	/**
	 * @var boolean		validate fields on keyup or blur
	 */
	protected $use_immediatly_check = FALSE;

	/**
	 * @var boolean		allocate errors on field
	 */
	protected $allocate_errors = FALSE;

	/**
	 * @var boolean		generate form ID and check it on 
	 */
	protected $use_activator = TRUE;

	/**
	 * @var boolean		show form on successfuly submit
	 */
	protected $show_on_success = TRUE;

	/**
	 * @var boolean		clear field values on successfuly submit
	 */
	protected $clear_on_success = TRUE;
	
	/**
	 * @var boolean		modify input length according to validation rules "mask", "max_length" and "exact_length"
	 */
	protected $modify_input_length = TRUE;
	
	protected $enable_backup = FALSE;
	protected $enable_cache = FALSE;
	protected $use_animation;
	/* render flags ends */
	
	// js callback -- success func
	protected $js_callback_success = NULL;

	// js callback -- error func
	protected $js_callback_error = NULL;
	
	// load flag
	protected $loaded = FALSE;

	// sent flag: TRUE if form has been submited
	protected $sent;

	// return changed values only
	protected $return_changes = FALSE;

	// wrap names into the array (for using of many forms in a single page)
	protected $wrap_field_names = TRUE;
	
	// success message
	protected $message;

	// error message
	protected $error;

	// comment
	protected $comment;

	// html code of form
	protected $html;

	// html code of opening form tag
	protected $tag_o;

	// html code of opening form tag
	protected $tag_c;

	// array of default values
	public $defaults;

	// fetched values
	public $values = array( );

	// fetches files
	public $files = array( );

	// fields list
	public $fields = array( );

	// fields order
	public $order = array( );

	// relations list
	public $relations = array( );

	// behavior objects list
	public $behavior_list = array( );

	// Form_Result object
	protected $result;

	// Form_Engine object
	protected $engine;

	// current activator value
	protected $activator_value;

	// activator code
	protected $activator_name;

	// handler function name
	protected $handler;

	// handler function parameters array
	protected $handler_param = array( );

	/** Object constructor
	 *
	 * @param string 	form name
	 * @return void
	 */
	public function __construct($label = NULL)
	{
		if ( ! isset($label))
		{
			$label 	= ($class = strtolower(get_class($this))) == 'form_base'
					? Form::name( )
					: Site::config('form')->default_form_name.count(Form::forms);

		}

		$this->label($label);

		$this->result = new Form_Result($this);
	}

	/** Standart setter/getter
	 *
	 * @param string 	variable name
	 * @param array		parameters
	 *
	 * @return mixed
	 */
	public function __call($var, $args = NULL)
	{
		// return value of property with name $var if exists
		if (property_exists($this, $var))
		{
			if (is_array($args) && count($args) > 0)
			{
				$this->$var = $args[0];

				return $this;
			}

			return $this->$var;
		}
		// if askable element is attached field return its
		elseif (isset($this->fields[$var]))
		{
			return $this->fields[$var];
		}
	}

	/** Bind serialization of Form_Base to get HTML
	 *
	 * @return	string
	 */
	public function __toString( )
	{
		try
		{
			return $this->body( );
		}
		catch (Exception $e)
		{
			if (IN_PRODUCTION)
			{
				var_dump($e);
				die( );
			}
		}
	}

	/** Get form HTML
	 *
	 * @return string
	 */
	public function body( )
	{
		return $this->engine( )->publish();
	}

	/** Add form handler or get it
	 *
	 * Setter:
	 * @param callback		handler
	 * @param mixed	 		handler parameter
	 * @return this
	 *
	 * Getter:
	 * @return handler func
	 */
	public function handler($func = NULL, $param = NULL)
	{
		if (isset($func))
		{
			// set handler func
			$this->handler = $func;

			// set parameters
			if (isset($param))
			{
				// wrap to array
				if ( ! is_array($param))
				{
					$this->handler_param(array($param));
				}
				else
				{
					$this->handler_param($param);
				}
			}

			return $this;
		}

		return $this->handler;
	}

	/** Execute form handler
	 *
	 * @return mixed	return value of handler
	 */
	public function handler_exec( )
	{
		$args = array_merge(array($this->result( )), $this->handler_param( ));

		return call_user_func_array($this->handler, $args);
	}

	/** Fetch and save activator field name
	 *
	 * @return string
	 */
	public function activator_name( )
	{
		if ( ! isset($this->activator_name))
		{
			$this->activator_name = substr(Basic::get_hash(Site::config('form')->activator_var . $this->label( )), 0, Site::config('form')->activator_var_length);
		}

		return $this->activator_name;
	}


	/** Get data from specified PHP built-in array
	 *  Check activator existence and values
	 *
	 * @return FALSE;
	 */
	public function get( )
	{
		if ($this->sent( ) === NULL)
		{
			/* :TODO:  check and clear data! */
			switch ($this->method( )) {
				case 'get':
					/* :TODO: create switch: fetch data from wrapped $_GET */
// 					$this->values = $_GET;
					$this->values = Request::current( )->query( );

					break;

				default:
				
					if ($this->wrap_field_names())
					{
						if (isset($_POST[$this->label( )]))
						{
							$this->values = $_POST[$this->label( )];
						}

						if (isset($_FILES[$this->label( )]))
						{
							// записываем входные файлы в массив form_files
							$this->files = $_FILES[$this->label( )];
						}
					}
					else
					{
						$this->values = $_POST;
						$this->files = $_FILES;
					}
			}
			
			if (
				count($this->values) > 0
					&&
				$this->use_activator( )
					&&
				isset($this->values[$this->activator_name( )])
			)
			{
				if ($this->values[$this->activator_name( )] == Session::instance( )->get($this->activator_name( )))
				{
					// check activator value and set "sent" form status = FALSE (fetch, but not validate)
					$this->sent(FALSE);
				}

				// clear variables
				$this->activator_value = NULL;
				Session::instance( )->delete($this->activator_name( ));
			}
			elseif (count($this->values( )) > 0 && ! $this->use_activator( ))
			{
				$this->sent(FALSE);
			}
		}
	}


	/** Standart array setter / getter
	 * 	Merge external and existent fields or get it
	 *
	 * Setter:
	 * - one argument:
	 * @param array
	 * @return this
	 *
	 * - many arguments:
	 * @param mixed		key (if null generate automatic)
	 * @param mixed		value
	 *
	 * Getter:
	 * @param string	argument name
	 * @return string
	 *
	 * without args
	 * @return array
	 */
	public function fields($param0 = NULL, $param1 = NULL)
	{
		if (isset($param0))
		{
			// single getter mode
			if ( ! isset($param1))
			{
				if ( ! isset($this->fields[$param0]))
				{
// 					throw new Form_Exception('Cannot find field named ":name"', array(':name' => $param0));
					return NULL;
				}
			
				return $this->fields[$param0];
			}
			// single setter mode
			else
			{
				$this->fields[$param0] = $param1;
			}
		}
		else
		{
			// array getter mode
			if ( ! isset($param1))
			{
				return $this->fields;
			}
			// single setter mode with numeric key
			else
			{
				$this->fields[] = $param1;
			}
		}

		return $this;
	}

	/** clear name of field
	 *
	 * @param 	string	name
	 * @return 	string
	 */
	protected function _clear_name($name)
	{
		return str_replace(array('\'', '"', '&', '*', '.'), '', $name);
	}
	
	/** add custom text as form field
	 *
	 * @param 	string	text
	 * @param 	string	label
	 * @return 	Form_Field
	 */
	public function text($text, $label = NULL)
	{
		return $this->field('html', $text, $label);
	}

	/** Attach form element to this form
	 *
	 * @param string 		field class
	 * @param string 		label
	 * @param mixed 		input name
	 * @return Form_Field
	 */
	public function field($class = 'text', $label = NULL, $name = NULL)
	{
		$obj = NULL;
		$is_name_generated = FALSE;
		
		if ( ! isset($name))
		{
			$name = count($this->fields( ));
			$name = "_".(strlen($name) == 1 ? '0'.$name : $name);
			$is_name_generated = TRUE;
		}
		else
		{
			$name = $this->_clear_name($name);
		}

		if (is_string($class))
		{
			$class = "Form_Field_$class";

			if (class_exists($class))
			{
				$obj = new $class($name);
			}
			else
			{
				throw new Form_Exception("Cannot load class '{$class}'.");
			}
		}

		if ( ! $obj instanceof Form_Field)
		{
			throw new Form_Exception('Cannot add field: object must be a Form_Field instance.');
		}

		if (isset($obj))
		{
			/* set up properties */
			if (isset($label))
			{
				$obj->label((string) $label);
				$obj->header((string) $label);
			}
			
			$obj->is_name_generated($is_name_generated);
			$obj->form($this);
		}

		return $obj;
	}

	/** Add form element to field stack
	 *
	 * @param Form_Field
	 * @return this
	 */
	public function reg(Form_Field $field)
	{
		if ( ! isset($this->fields[$field->name( )]))
		{
			$this->fields[$field->name( )] = $field;

			// insert into order list
			$this->order[] = $field->name( );
		}
		else
		{
			throw new Form_Exception("Cannot add field with name '".$field->name( )."': field exists.");
		}
		
		return $this;
	}


	/** Remove form element from attached Form_Base::fields array and destroy it
	 *
	 * @param string	field ID
	 * @return boolean
	 */
	public function remove($id)
	{
		if (isset($this->fields[$id]))
		{
			// check usage in behavior of other elements
			if (count($this->fields[$id]->relations( )) > 0)
			{
				throw new Form_Exception('Impossible to remove field ":id": it used in behavior of other field.', array(':id'=>$id));
			}

			// clear field validation rules
			$this->fields[$id]->clear_rules( );

			unset($this->fields[$id]);
		}
		else
		{
			throw new Form_Exception('Cannot find field ":id".', array(':id'=>$id));
		}

		return $this;
	}

	/** Callback function for fields array sorting
	 *
	 * @param string first key
	 * @param string second key
	 * @return integer
	 */
	protected function fields_sort($a, $b)
	{
		return ($this->order[$a] > $this->order[$b]) ? 1 : -1;
	}

	/** Start form render
	 *  Add Form_Engine object and load specified param
	 *
	 * @param string 	custom form body
	 * @return this
	 */
	public function render($body = NULL)
	{
		if ( ! $this->loaded( ))
		{
			// flip order array
			$this->order = array_flip($this->order);

			// sort fields
			uksort($this->fields, array($this, 'fields_sort'));

			if (isset($body))
			{
				$this->html($body);
			}

			if ($this->use_animation( ) === NULL)
			{
				// switch off animation if registered field bulk
				if (count($this->fields( )) > Site::config('form')->animation_count)
				{
					$this->use_animation(FALSE);
				}
				else
				{
					$this->use_animation(TRUE);
				}
			}

			$this->engine(new Form_Engine($this));

			$this->engine( )->start( );

			$this->loaded(TRUE);

			// execute handler
			if ($this->sent( ) && $this->handler( ) !== NULL)
			{
				// return handler result if handler exists
				return $this->handler_exec( );
			}
		}

		return $this->result( );
	}

	/** Add wrapped view
	 *
	 * @param 	string	view
	 * @return 	this
	 */
	public function field_view($view)
	{
		$this->field('view')->_text($view);
		
		return $this;
	}
	

	/** Get fields validation rules
	 *
	 * @return array
	 */
	public function rules( )
	{
		$rules = array( );

		foreach ($this->fields( ) AS $field)
		{
			$rules = $rules + $field->rules( );

		}

		return $rules;
	}

	/** Register relation rule
	 *
	 * @param string relation name
	 * @return count
	 */
	public function rel($name)
	{
		// check relation existence
		if ( ! isset($this->relations[$name]))
		{
			return ($this->relations[$name] = new Form_Relation($name));
		}
		else
		{
			return FALSE;
		}
	}
	
	/** Get activator
	 *
	 * @return Form_Field
	 */
	public function activator( )
	{
		return $this->fields($this->activator_name( ));
	}
	
	/** Get opening tag
	 *
	 * @return 	View
	 */
	public function tag_o( )
	{
		if ($this->tag_o === NULL)
		{
			$this->tag_o = View::factory(Site::config('form')->templates->form, array(
				'name'		=> $this->label( ),
				'method' 	=> $this->method( ),
				'action'	=> $this->action( ) !== NULL ? $this->action( ) : NULL,
				'target'	=> $this->target( ),
				'enctype' 	=> $this->enctype( ),
				'class'		=> $this->class( ),
			));
		}
		
		return $this->tag_o;
	}

	/** Get closing tag
	 *
	 * @param	string		html addition which will be inserted before closing tag
	 * @return 	View
	 */
	public function tag_c($html = '')
	{
		if ($this->tag_c === NULL)
		{
			$this->tag_c = View::factory(Site::config('form')->templates->form_end, array('html' => $html));
		}
		
		return $this->tag_c;
	}
	
	public function set_error($message) {
		$this->sent(FALSE);
		$this->engine()->message($message, Site::config('form')->message_types->error);

		return $this;
	}
}