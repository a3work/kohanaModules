<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Form element constructor
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-21
 *
 **/

abstract class Kohana_Form_Field
{
	const VIEW_DIR = 'form';
	const BEHAVIOR_CLASS_VAR = 'beh_class';
    const EMPTY_OPTION_BEFORE = 'empty_before';
	const EMPTY_OPTION_AFTER = 'empty_after';

	// form id stub
	public $id;

	// form link
	public $form;

	// input name
	public $name;

	// is name generated automaticaly
	public $is_name_generated;

	// multiple flag
	public $multiple = FALSE;

	// extendable flag
	public $extendable = FALSE;

	// not empty flag
	public $mandatory = FALSE;

	// field switch
	public $disabled = FALSE;

	// is field file?
	public $file = FALSE;

	// save value after form submit
	public $hold_value = TRUE;

	// don't publish value -- reserved
	public $is_private;

	/**
	 * @var string		text of message
	 */
	public $message;
	
// 	/**
// 	 * @var string		text before field
// 	 */
// 	protected $text_before;
// 	
// 	/**
// 	 * @var string		text after field
// 	 */
// 	protected $text_after;
	
	// position in form
	public $position = 0;

	// options of select, checkbox and so on
	protected $options = array( );

	// custom settings
	protected $settings = array( );
	
	// validation rules
	protected $rules = array( );

	// relations list
	protected $relations = array( );

	// current relation object
	protected $relation = FALSE;

	// current value
	public $value;

	// hidden field don't hit to results
	public $hidden = FALSE;

	// validated stored value
	public $result;

	// flag of selected element
	public $selected;

	// current value
	public $value_old;

	// flag of selected element
	public $selected_old;

	// element template -- view file name
	public $view;

	// element unit
	public $unit;

	// element option template
	public $view_opt;

	// element optgroup view
	public $view_optgroup = 'optgroup';

	// individual form unit template (wrapper of two previous)
	public $wrapper;

	// input css3 placeholder
	public $placeholder;

	// input wrapper label
	public $label;

	// button / checkbox header
	public $header;

	// field length
	public $length = 0;

	// behavior object
	protected $beh;

	// HTML code of element
	protected $html;

    // empty option mode
    public $empty_option;

    // empty option label
    public $empty_option_label;

	// element css classes list (behavior and changes by validation rules)
	protected $classes = array( );

	// loaded flag: true if value load
	protected $loaded = FALSE;

	// render flag: true if html processed
	protected $rendered = FALSE;

	// render mark -- for replacing
	protected $render_mark;

	// field value input filter
	protected $filter_in;

	// field value output filter
	protected $filter_out;
	
	/**
	 * Object constructor
	 */
	public function __construct($name)
	{
		$name = str_replace(array('[', ']'), array('{', '}'), $name);
		
		$this->name($name);
		$this->id($name);
		
		$this->type(str_replace('form_field_', '', strtolower(get_class($this))));

		if ( ! isset($this->wrapper))
		{
			$this->wrapper(Site::config('form')->templates->unit);
		}
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
		if (property_exists($this, $var))
		{
			if (is_array($args) && count($args) > 0)
			{
				$this->$var = $args[0];

				return $this;
			}

			return $this->$var;
		}
		elseif ($this->form( ) !== NULL)
		{
			return call_user_func_array(array($this->form( ), $var), $args);
		}
	}

	/** Generate html if request this obj as string
	 *
	 * @return string
	 */
	public function __toString( )
	{
		try
		{
			return $this->html($this->form( )->loaded( ));
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

	/** Mark field as multiple and modify name
	 *
	 * @param boolean 	multiple flag value
	 *
	 * @return mixed
	 */
	public function multiple($multiple = NULL)
	{
		if (isset($multiple))
		{
			$this->multiple = (boolean) $multiple;

			$this->name = preg_replace('/\[\]$/', '', $this->name);
			
			if ($this->multiple)
			{
				$this->name .= '[]';
			}

			return $this;
		}
		
		return $this->multiple;
	}

	/** Set or get output filter
	 *
	 * @param	callback	callback
	 * @param	array		arguments
	 * @return	mixed
	 */
	public function filter_out($callback = NULL, $args = array( ))
	{
		if (isset($callback))
		{
			$this->filter_out = array('callback'=>$callback, 'args'=>$args);
			
			return $this;
		}
		
		return empty($this->filter_out)
				? NULL
				: (object) $this->filter_out;
	}
	
	/** Set or get input filter
	 *
	 * @param	callback	callback
	 * @param	array		arguments
	 * @return	mixed
	 */
	public function filter_in($callback = NULL, $args = array( ))
	{
		if (isset($callback))
		{
			$this->filter_in = array('callback'=>$callback, 'args'=>$args);
			
			return $this;
		}
		
		return empty($this->filter_in)
				? NULL
				: (object) $this->filter_in;
	}
	
	/** Label and header setter
	 *
	 * Setter:
	 * @param string
	 * @return this
	 *
	 * Getter:
	 * @return string
	 */
	public function label($label = NULL)
	{
		if (isset($label))
		{
			$this->label = $this->header = $label;
		}

		return $this->label;
	}

	/** Remove validation rules
	 *
	 * @return this
	 */
	public function clear_rules( )
	{
		if ($this->loaded( ))
		{
			throw new Form_Exception('Cannot remove rules of loaded field ":obj".', array(':obj'=>$this->id( )));
		}

		foreach ($this->rules AS $key=>$rule)
		{
			unset($this->rules[$key]);
		}

		return $this;
	}

	/** Attach this field to specified Form_Base object or get link to it
	 *
	 * Setter:
	 * @param mixed (Form_Base) object / (string) form label
	 * @return this
	 *
	 * Getter:
	 * @return Form_Base
	 */
	public function form($link = NULL)
	{
		if (isset($link))
		{
			// call "on_attach" event handler
			
			if (is_string($link))
			{
				$link = Form::factory($link);
			}

			if ($link instanceof Form_Base)
			{
				$this->on_before_attach($link);
				
				$link->reg($this);
				$this->form = $link;
			}

			// change field name: add form label
			if ($this->form->method( ) != 'get' && $this->form->wrap_field_names( ) === TRUE)
			{
//				$match = preg_match('/^([^\[]+)\[/', $this->name( ), $matches);
//				
//				if ($match)
//				{
//					$this->name($this->form( )->label( ).str_replace($matches[1], '['.$matches[1].']', $this->name()));
//				}
//				else
//				{
					$this->name($this->form( )->label( ).'['. $this->name( ) .']');
//				}
				
//				var_dump($this->name());
			}

			if ($this->multiple( ))
			{
				$this->name .= '[]';
			}

			// call "on_attach" event handler
			$this->on_attach( );

			return $this;
		}
		else
		{
			return $this->form;
		}
	}

	/** Hold field before other field in parent form
	 *
	 * @param string Field_Name
	 * @return this
	 */
	public function before($field)
	{
		// remove this field from old position
		$from = array_search($this->id( ), $this->form( )->order);
		unset($this->form( )->order[$from]);

		// check field existence
		$to = array_search($field, $this->form( )->order);
		if ($to === FALSE)
		{
			throw new Form_Exception('Cannot hold field before ":field": field existn\'t', array(":field" => $field));
		}


		array_splice($this->form( )->order, $to, 0, $this->id( ));

		return $this;
	}

	/** Hold field after other field in parent form
	 *
	 * @param string Field_Name
	 * @return this
	 */
	public function after($field)
	{
		// remove this field from old position
		$from = array_search($this->id( ), $this->form( )->order);
		unset($this->form( )->order[$from]);

		// check field existence
		$to = array_search($field, $this->form( )->order);
		if ($to === FALSE)
		{
			throw new Form_Exception('Cannot hold field after ":field": field existn\'t', array(":field" => $field));
		}

		array_splice($this->form( )->order, $to+1, 0, $this->id( ));

		return $this;
	}


	/** Fetch data handler
	 *
	 * @return this
	 */
	public function on_submit( )
	{

	}

	/** Attach form event handler
	 *
	 * @return this
	 */
	public function on_attach( )
	{

	}

	/** Call when field register in form object
	 *
	 * @param	Form_Base 	form object
	 * @return this
	 */
	public function on_before_attach(Form_Base $link)
	{

	}

	/** Add new field option (it possible for selects, radio, multiple and so on) or redirect to parent form (if exists)
	 */
	public function field()
	{
		return call_user_func_array(array($this->form( ), "field"), func_get_args( ));
	}

	/** Start form render
	 */
	public function render()
	{
		return call_user_func_array(array($this->form( ), "render"), func_get_args( ));
	}


	/** Check exist and return list of validation rules
	 *
	 * with argument:
	 * @param string 	rule name
	 * @return mixed 	(Field_Rule) object / (boolean) FALSE if rule not found
	 *
	 * without parameters:
	 * @return array 	rules
	 */
	public function rules($name = NULL)
	{

		if (isset($name))
		{
			$key = "{$name}_{$this->id}";

			if (isset($this->rules[$key]))
			{
				return $this->rules[$key];
			}
			else
			{
				return FALSE;
			}
		}
		else
		{
			return $this->rules;
		}
	}

	/** Add validation rule
	 *
	 * @param string name
	 * @param array	 args
	 * @param string message
	 */
	public function rule($name, $args = NULL, $message = NULL)
	{
		$key = '_r'.Basic::get_hash($this->form( )->label( )."_{$name}_{$this->id}", 'md5', 7);
// 		$key = "{$name}_{$this->id}";

		// if set relation for rules addition
		if ($this->relation( ) !== FALSE)
		{
			$this->relation( )->rule($name, $key, $args);
		}
		elseif ( ! isset($this->rules[$key]))
		{
			$this->rules[$key] = Form_Rule::factory($name)->id($key);

			if (isset($args))
			{
				$this->rules[$key]->args($args);
			}
			if (isset($message))
			{
				$this->rules[$key]->message($message);
			}

			// create link to this field
			$this->rules[$key]->field($this);
		}

		return $this;
	}

	/** Add relation rule with current field
	 *
	 * @param string rule_ID
	 * @return Form_Relation
	 */
	public function rel($name)
	{
		$relation = $this->form( )->rel($name);

		if ($relation === FALSE)
		{
			throw new Form_Exception("Relation rule :name already exists.", array('name'=>$name));
		}
		else
		{
			$relation->field($this);

			// register relation as object for attach rules
			$this->relation($relation);

			// add relation to list of attached relations
			$this->relations($relation);
		}

		return $this;
	}

	/** Add relation rule to relations list or get list of relations
	 *
	 * Setter:
	 * @param Form_Relation rule
	 * @return this
	 *
	 * Getter:
	 * @return array
	 */
	public function relations(Form_Relation $relation = NULL)
	{
		if (isset($relation))
		{
			$this->relations[$relation->label( )] = $relation;

			return $this;
		}

		return $this->relations;
	}

	/** Add behavior to this field or get attached Form_Behavior object
	 *
	 * @param string 	behavior condition
	 * @return Form_Behavior
	 */
	public function beh($init = NULL)
	{
		if (isset($init))
		{
			// remove exist object
			if (isset($this->beh))
			{
				unset($this->form->behavior_list[$this->beh->id( )]);
				unset($this->beh);
			}
	// 		$this->beh = new Form_Behavior($init, $this);
	// 		return $this->beh;
			if ( ! isset($this->form( )->behavior_list[$init]))
			{
				$this->form( )->behavior_list[$init] = new Form_Behavior($init);
			}

			// attach field to behavior: add field in behavior field list
			$this->form( )->behavior_list[$init]->fields[] = $this;

			// add behavior class to element classes list
			$this->classes(Kohana_Form_Field::BEHAVIOR_CLASS_VAR, $this->form( )->behavior_list[$init]->id( ));

			// add link to behavior obj and return
			return ($this->beh = $this->form( )->behavior_list[$init]);
		}
		else
		{
			return $this->beh;
		}
	}

	/** Merge external and existent classes or get it
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
	public function classes($param0 = NULL, $param1 = NULL)
	{
		if (isset($param0))
		{
			// array setter mode
			if (is_array($param0))
			{
				$this->classes = array_merge($this->classes, $param0);

				return $this;
			}
			else
			{
				// single getter mode
				if ( ! isset($param1))
				{
					return $this->classes[$param0];
				}
				// single setter mode
				else
				{
					$this->classes[$param0] = $param1;
				}
			}
		}
		else
		{
			// array getter mode
			if ( ! isset($param1))
			{
				return $this->classes;
			}
			// single setter mode with numeric key
			else
			{
				$this->classes[] = $param1;
			}
		}

		return $this;
	}

	public function _class( )
	{
		return implode(' ', $this->classes( ));
	}

	/** Add option to options array
	 *
	 * @param string 	header / value
	 * @param string 	value
	 * @return this
	 */
	public function option($header, $value = NULL)
	{
		if ( ! isset($value))
		{
			$this->options[] = $header;
		}
		else
		{
			$this->options[$header] = $value;
		}


		return $this;
	}

	/** Set or get options array
	 *
	 * @param	mixed 		values
	 * @param	boolean 	combine values: add values as keys
	 * @return	this
	 */
	public function options($options = NULL, $combine_values = FALSE)
	{
		if ($combine_values && is_array($options))
		{
			$options = array_combine($options, $options);
		}
	
		if (isset($options))
		{
			$this->options = $options;

			return $this;
		}

		return $this->options;
	}
	
	/** Add empty options
	 *
	 * @param   integer place where add empty option (Kohana_Form_Field::EMPTY_OPTION_AFTER or Kohana_Form_Field::EMPTY_OPTION_BEFORE)
     * @return  this
     */
    public function empty_option($where = NULL)
    {
		if (empty($this->empty_option_label))
		{
			$this->empty_option_label = '- '.__('not selected').' -';
		}
    
        if (empty($where))
        {
            if ($this->options( ) != array( ))
            {
                $this->empty_option = Kohana_Form_Field::EMPTY_OPTION_AFTER;
            }
            else
            {
                $this->empty_option = Kohana_Form_Field::EMPTY_OPTION_BEFORE;
            }
        }
        elseif ($this->empty_option != Kohana_Form_Field::EMPTY_OPTION_AFTER)
        {
            $this->empty_option = Kohana_Form_Field::EMPTY_OPTION_BEFORE;
        }
        
        return $this;
    }
	
	/** Set or get option view file name
	 *
	 * @param string 	view name
	 * @return string
	 */
	public function view_opt($view = NULL)
	{
		if (isset($view))
		{
			$this->view_opt = $view;
			return $this;
		}

		if (isset($this->view_opt))
		{
			return $this->view_opt;
		}
		elseif( ! isset($this->view_opt) && isset($this->view))
		{
			return $this->view;
		}
		else
		{
			throw new Form_Exception('Empty option template.');
		}
	}


	/** Render options
	 *
	 * @return string options html
	 */
	public function process_opt($options = NULL, $optgroup_num = NULL)
	{
		$out = '';

		if ( ! isset($options))
		{
			$options = $this->options( );
		}

		$db_options = ($options instanceof Database_Result);

		// смотрим, нужно ли помечать опции селектов и радио-кнопки
		foreach ($options AS $option_key => $option)
		{
            // add optgroups
			if ( ! $db_options && is_array($option))
			{
				// key response mode switch
				// FALSE: set $option_key as result array key
				// TRUE: set numeric keys ([] in name)
				$is_integer_key = NULL;

				$out .= View::factory(Kohana_Form_Field::VIEW_DIR.DIRECTORY_SEPARATOR.$this->view_optgroup( ), array(
					'label' => $option_key,
					'options' => $this->process_opt($option, $option_key),
				))->render( );
			}
			// add option
			else
			{
				if ($db_options)
				{
					$optgroup_num = '';
					$is_integer_key = FALSE;

					$option_key = is_object($option) ? $option->{Site::config('form')->db_opt_key} : $option[Site::config('form')->db_opt_key];
					$option =  is_object($option) ? $option->{Site::config('form')->db_opt_header} : $option[Site::config('form')->db_opt_header];
				}
				else
				{
					if ( ! isset($is_integer_key))
					{
						if ($option_key === 0)
						{
							if (isset($optgroup_num))
							{
								$optgroup_num = str_replace(' ', '_', $optgroup_num).'_';
							}
							else
							{
								$optgroup_num = '';
							}

							$is_integer_key = TRUE;
						}
						else
						{
							$optgroup_num = '';

							$is_integer_key = FALSE;
						}
					}
				}

				$option = View::factory(Kohana_Form_Field::VIEW_DIR.DIRECTORY_SEPARATOR.$this->view_opt( ), array(
					'name'		=> ! $is_integer_key && $option_key != $option ? str_replace('[]', "[$option_key]", $this->name( )) : $this->name( ),		// for checkbox_group and so on
					'value' 	=> $optgroup_num.$option_key,
					'header' 	=> $option,
					'field'		=> $this,
				));

				$option->selected = FALSE;

				// fetch check status of option
				if
				(
					! $this->multiple( )
						&&
					$this->value( ) == $option->value

					||

					$this->multiple( )
						&&
					in_array($option->value, $this->value( ))
				)
				{
					$option->selected = TRUE;
				}

				// add option html to output
				$out .= $option->render( );
			}
		}

        // add empty options if queried
        if ($this->empty_option == Kohana_Form_Field::EMPTY_OPTION_BEFORE)
        {
            $this->empty_option = '';
            $out = $this->process_opt(array("" => $this->empty_option_label)).$out;
        }
        // add empty options if queried
        elseif ($this->empty_option == Kohana_Form_Field::EMPTY_OPTION_AFTER)
        {
            $this->empty_option = '';
            $out .= $this->process_opt(array("" => $this->empty_option_label));
        }
        
		return $out;
	}

	/** Set default value
	 *
	 * @return void
	 */
	protected function defaults( )
	{
		// get external defaults from array/object
		if ($this->form( )->defaults( ) !== NULL)
		{
			// fetch input value according to input type
			if (is_array($this->form( )->defaults( )) && isset($this->form( )->defaults[$this->id( )]))
			{
				$input = $this->form( )->defaults[$this->id( )];
			}
			elseif (is_object($this->form( )->defaults( )) && $this->form( )->defaults->__isset($this->id( )) && $this->form( )->defaults->{$this->id( )} !== NULL)
			{
				$input = $this->form( )->defaults->{$this->id( )};
			}

			// if input is defined save it
			if (isset($input))
			{
				// execute input filter if exists
				if ($this->filter_in( ) !== NULL && (boolean) $input !== FALSE)
				{
					$input = call_user_func_array($this->filter_in( )->callback, array_merge(array($input), $this->filter_in( )->args));
				}

				// wrap simple values to array for multiple fields
				if ($this->multiple( ) && ! is_array($input))
				{
					$this->value = array($input);
				}
				else
				{
					$this->value = $input;
					$this->selected((boolean) $input);
				}
			}
		}

		// set defaults if external value not exists
		if ( ! isset($this->value))
		{
			if ($this->multiple( ))
			{
				$this->value = array( );
			}
			else
			{
				$this->value = '';
			}
		}

		$this->selected((boolean) $this->selected( ));
	}

	/** Save value in Form_Base::data for external usage
	 *
	 * @return void
	 */
	protected function publish( )
	{
		// write to output data array
		$this->form( )->result( )->add($this);

		if ($this->form( )->sent( ))
		{
			// execute submit handler
			$this->on_submit( );
		}
	}

	/** value setter behavior if fetch NULL value (param not found in income values array)
	 *
	 * @return void
	 */
	public function null_behavior( )
	{
		$this->selected(FALSE);

		if ($this->multiple)
		{
			$this->value = array( );
		}
		else
		{
			$this->value = NULL;
		}
	}

	/** Set and get current value of element
	 *
	 * Setter:
	 * @param mixed		value
	 * @return
	 *
	 * Getter
	 * @return mixed
	 */
	public function value($value = NULL)
	{
		if (isset($value))
		{
			if ($this->multiple( ))
			{
				if ( ! is_array($value))
				{
					$value = array($value);
				}
				
				if ( ! isset($this->value))
				{
					$this->value = array( );
				}

				$this->value = array_merge($this->value, $value);
			}
			
						

			
			$this->value = $value;

			return $this;
		}


		if ( ! $this->loaded( ))
		{
			$this->defaults( );
			
			// save unchanged values
			$this->value_old($this->value);
			$this->selected_old($this->selected( ));

			// fetch value from result array if form has been submit
			if ($this->form( )->sent( ) !== NULL && $this->hold_value( ))
			{
				if (isset($this->form( )->values[$this->id( )]))
				{
					$this->value = $this->form( )->values[$this->id( )];
					$this->selected(TRUE);
				}
				elseif (isset($this->form( )->files[$this->id( )]))
				{
					$this->value = $this->form( )->files[$this->id( )];
					$this->selected(TRUE);
				}
				else
				{
					$this->null_behavior( );
				}
			}
			
			
			// check this as loaded field
			$this->loaded(TRUE);
		}
			
		return $this->value;
	}
	
	/** Get value, filtered by htmlspecialchars
	 *
	 * @return 	string
	 */
	public function value_encoded( )
	{
		return htmlspecialchars($this->value( ), ENT_QUOTES | ENT_HTML5);
	}

	/** Add value to Form_Result object
	 *
	 * @return void
	 */
	protected function add_result( )
	{
		// value will be added to result:
		// - if form has not validation errors
		// - if field is not private
		// - if field behavior not switch off this field
		// - if form returns all results OR form returns changes only and this field value has been changed
		if (
			$this->form( )->sent( ) !== FALSE
				&&
			! $this->is_private( )
				&&
			(
				! $this->beh( )
				||
				! $this->beh( )->pass_valid( )
			)
			&&
			(
				! $this->form( )->return_changes( )
				||
				(
					$this->value( ) != $this->value_old( )
					||
					$this->selected( ) !== $this->selected_old( )
				)
			)
		)
		{
			// execute output filter if exists
			if ($this->filter_out( ) !== NULL && $this->value( ) !== NULL)
			{
				$this->result(call_user_func_array(
								$this->filter_out( )->callback,
								array_merge(array(
										'value' => $this->value( ),
										'selected' => $this->selected( ),
									),
									$this->filter_out( )->args
								)
							));
			}
			else
			{
				// store value
				$this->result($this->value( ));
			}

			// add to Form_Result
			$this->publish( );
		}
	}

	/** get actual element value for validation
	 * 	for the most part = value( )
	 *
	 * @return mixed
	 */
	public function val( )
	{
		return $this->value( );
	}

	/** Generate render mark
	 *
	 * @param boolean 	generate flag
	 * @return string
	 */
	public function render_mark($generate = NULL)
	{
		if (isset($generate))
		{
			$this->render_mark = ':'.substr(Basic::get_hash($this->name), 26).':';
		}

		return $this->render_mark;
	}

	/** Render field
	 *
	 * @param boolean 		render flag: if FALSE generate render_mark
	 * @return string 		HTML code of element
	 */
	public function html($render = FALSE)
	{
		if ($this->form( ) === NULL)
		{
			throw new Form_Exception('Cannot load unattached field.');
		}

		// if form is not rendering now return render_mark
		// this mark will be replaced in future to real HTML
		if ( ! $render)
		{
			return $this->render_mark(TRUE);
		}

		if ( ! $this->rendered( ))
		{
			$this->placeholder(htmlspecialchars($this->placeholder( )));

			// add value to result
			$this->add_result( );

			// drop values to defaults if form submit, fetch no errors and set "Clear_on_success" flag
			if ($this->hold_value( ) && $this->form( )->sent( ) === TRUE && $this->form( )->clear_on_success( ))
			{
				$this->selected((boolean) $this->selected_old( ));
				$this->value = $this->value_old( );
			}

			if ($this->form( )->sent( ) !== TRUE || $this->form( )->sent( ) === TRUE && $this->form( )->show_on_success( ))
			{
				// если поле "расширяемое"
				// (имеет опции/несколько значений для выбора и пр.)
				if ((boolean) $this->extendable)
				{
					// options output
					$out = $this->process_opt( );

					if ($this->view( ) !== NULL)
					{
						$out = View::factory(Kohana_Form_Field::VIEW_DIR.DIRECTORY_SEPARATOR.$this->view( ), array(
							'field'		=> $this,
							'options'	=> $out,
						))->render( );
					}
				}
				else
				{
					if ($this->view( ) !== NULL)
					{
						$out = View::factory(Kohana_Form_Field::VIEW_DIR.DIRECTORY_SEPARATOR.$this->view( ), array(
							'field'		=> $this,
						));
					}
					else
					{
						throw new Form_Exception('Empty template.');
					}

					$out = $out->render( );
				}

				$this->html = $out;
			}
			$this->rendered(TRUE);
		}

		return $this->html;
	}





	/** SHORTCUTS **/

	/** not_empty shortcut
	 *
	 * @param string message
	 * @return this
	 */
	public function not_empty($message = NULL)
	{
		return $this->rule('not_empty', NULL, $message);
	}

	/** checked shortcut
	 *
	 * @param string message
	 * @return this
	 */
	public function checked($message = NULL)
	{
		return $this->rule('checked', NULL, $message);
	}

	/** max_length shortcut
	 *
	 * @param integer max length
	 * @param string message
	 * @return this
	 */
	public function max_length($length, $message = NULL)
	{
		return $this->rule('max_length', array('length' => $length), $message);
	}

	/** min_length shortcut
	 *
	 * @param integer min length
	 * @param string message
	 * @return this
	 */
	public function min_length($length, $message = NULL)
	{
		return $this->rule('min_length', array('length' => $length), $message);
	}


	/** exact_length shortcut
	 *
	 * @param integer exact length
	 * @param string message
	 * @return this
	 */
	public function exact_length($length, $message = NULL)
	{
		return $this->rule('exact_length', array('length' => $length), $message);
	}

	/** matches shortcut
	 *
	 * @param string match
	 * @return this
	 */
	public function matches($match, $message = NULL)
	{
		return $this->rule('matches', array('field' => $this->id( ), 'match' => $match), $message);
	}

	/** equals shortcut
	 *
	 * @param string required string
	 * @param string message
	 * @return this
	 */
	public function equals($required = NULL, $message = NULL)
	{
		return $this->rule('equals', array('required' => (string) $required), $message);
	}

	/** regular expression shortcut
	 *
	 * @param string expression
	 * @param string message
	 * @return this
	 */
	public function regex($expression, $message = NULL)
	{
		return $this->rule('regex', array('expression' => $expression), $message);
	}

	/** masked input shortcut
	 *
	 * @param string mask
	 * @param string message
	 * @return this
	 */
	public function mask($mask, $message = NULL)
	{
		return $this->rule('mask', array('mask' => $mask), $message);
	}

	/** numeric shortcut
	 *
	 * @param string mask
	 * @param string message
	 * @return this
	 */
	public function numeric($message = NULL)
	{
		return $this->rule('numeric', NULL, $message);
	}
	
	/** range shortcut
	 *
	 * @param	integer	min
	 * @param	integer	max
	 * @param	string	message
	 * @return	this
	 */
	public function range($min, $max, $message = NULL)
	{
		return $this->rule('range', array('min' => $min, 'max' => $max), $message);
	}
}
