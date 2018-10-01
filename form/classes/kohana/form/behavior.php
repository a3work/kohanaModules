<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Form element behavior
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-26
 *
 **/


class Kohana_Form_Behavior
{
	// behavior ID
	protected $id;

	// js check function name
	protected $check_func;

	// attached fields list
	public $fields = array( );

	// condition string
	protected $condition;

	// actions stack
	protected $actions = array( );

	// relations list
	protected $relations = array( );

	// check result
	protected $result;

	// js code
	protected $js;

	// classes of influencing fields
	protected $_classes;

	// adjacent activators for js
	protected $adj_act;

	// pass validation flag
	protected $pass_valid = FALSE;

	// behavior loaded and processed
	protected $loaded = FALSE;

	/** Object constructor
	 *
	 * @param string 		condition
	 * @param Form_Field 	field for addition
	 */
	public function __construct($condition)
	{
// 		$this->fields[] = $field;
		$this->condition = preg_replace('/!\s+/', '!', trim($condition));

		// write this behavior to form behavior obj list
// 		$this->field->form->behavior_list[$this->id( )] = $this;
	}

	/** Standart setter/getter
	 *  If method not found, search it in parent Form_Field object
	 *
	 * @param string 	variable name
	 * @param array		parameters
	 *
	 * @return mixed
	 */
	public function __call($var, $args = NULL)
	{
		return call_user_func_array(array($this->fields[count($this->fields) - 1], $var), $args);
	}

	public function __toString()
	{
		return ''.$this->fields[count($this->fields) - 1];
	}

	/** Add custom js action to action stack
	 *
	 * @param string 	action name
	 * @param array		arguments
	 * @return this
	 */
	public function call($name, $args = NULL)
	{
		$obj = new Form_Action_External;
		
		// add arguments
		$obj->args($args);
		$obj->name($name);
		
		// add to stack
		$this->actions[$name] = $obj;
		
		return $this;
	}
	
	/** Add action to action stack
	 *
	 * @param string 	action name
	 * @param array		arguments
	 * @return this
	 */
	public function action($name, $args = NULL)
	{
		if ( ! isset($this->actions[$name]))
		{
			$classname = "Form_Action_{$name}";

			// check class existence
			if (class_exists($classname))
			{
				$obj = new $classname;

				// check type
				if ($obj instanceof Form_Action)
				{
					// add arguments
					$obj->args($args);
					$this->actions[$name] = $obj;
				}
				else
				{
					throw new Form_Exception("Class '{$classname}' not extends Form_Action.");
				}
			}
			else
			{
				throw new Form_Exception("Cannot find '{$classname}' class.");
			}
		}

		return $this;
	}

	/** Get current Form_Behavior id
	 *
	 * @return string
	 */
	public function id( )
	{
		if ( ! isset($this->id))
		{
// 			$this->id = Site::config('form')->relation_mark.str_replace(array('[',']'), '-', $this->field->id( ));
			$this->id = Site::config('form')->relation_mark.substr(Basic::get_hash($this->fields[count($this->fields) - 1]->form( )->label( ).$this->condition( )), 26);
		}

		return $this->id;
	}

	/** Get check func name
	 *
	 * @return string
	 */
	public function check_func( )
	{
		if ( ! isset($this->check_func))
		{
			$this->check_func = Site::config('form')->relation_check_func.$this->id( );
		}

		return $this->check_func;
	}

	/** Get condition
	 *
	 * @return string
	 */
	public function condition( )
	{
		return $this->condition;
	}

	/** Get actions list
	 *
	 * @return array
	 */
	public function actions( )
	{
		return $this->actions;
	}


	/** Set / Get condition compilation result
	 *
	 * Setter:
	 * @param boolean
	 * @return this
	 *
	 * Getter:
	 * @return boolean
	 */
	public function result($result = NULL)
	{
		if (isset($result))
		{
			$this->result = (boolean) $result;

			return $this;
		}

		return $this->result;
	}

	/** Set / Get classes of influencing elements
	 *
	 * Setter:
	 * @param string
	 * @return this
	 *
	 * Getter:
	 * @return boolean
	 */
	public function _classes($_classes = NULL)
	{
		if (isset($_classes))
		{
			$this->_classes = $_classes;

			return $this;
		}

		return $this->_classes;
	}

	/** Set / Get js code
	 *
	 * Setter:
	 * @param string
	 * @return this
	 *
	 * Getter:
	 * @return boolean
	 */
	public function js($js = NULL)
	{
		if (isset($js))
		{
			$this->js = $js;

			return $this;
		}

		return $this->js;
	}

	/** Set / Get adjacent activators code
	 *
	 * Setter:
	 * @param string
	 * @return this
	 *
	 * Getter:
	 * @return boolean
	 */
	public function adj_act($adj_act = NULL)
	{
		if (isset($adj_act))
		{
			$this->adj_act = $adj_act;

			return $this;
		}

		return $this->adj_act;
	}

	/** Parse behavior condition to array and get
	 *
	 * @return boolean
	 */
	public function relations( )
	{
		if (count($this->relations) == 0)
		{
			$this->relations = explode(' ', str_replace(array('(', ')', '!'), '', str_replace(array(' && ', ' || '), ' ', $this->condition( ))));
		}

		return $this->relations;
	}

	/** Set / Get pass validation flag
	 *
	 * Setter:
	 * @param string
	 * @return this
	 *
	 * Getter:
	 * @return boolean
	 */
	public function pass_valid($pass_valid = NULL)
	{
		if (isset($pass_valid))
		{
			$this->pass_valid = $pass_valid;

			return $this;
		}

		return $this->pass_valid;
	}

	/** Set / Get loaded flag
	 *
	 * Setter:
	 * @param string
	 * @return this
	 *
	 * Getter:
	 * @return boolean
	 */
	public function loaded($loaded = NULL)
	{
		if (isset($loaded))
		{
			$this->loaded = $loaded;

			return $this;
		}

		return $this->loaded;
	}

}