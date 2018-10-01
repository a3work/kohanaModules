<?php defined('SYSPATH') or die('No direct script access.');

/** Basic filter: add handlers for every field and wrap form to spoiler 
 *  Extends form_base
 * 
 *
 * @name		filter form
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		23.12.13
 *
 **/

class Kohana_Form_Filter extends Form_Base
{
	const SESS_VAR_FILTER 	= 'fltr_';
	const QUERY_CLEAR		= 'clr';
	const SESS_VAR_LENGTH	= 8;

	protected $method = 'get';
	protected $clear_on_success = FALSE;
	
	// bind name
	protected $_bind = array( );

	// filter name
	public $filter_name;
	
	// don't wrap to spoiler
	public $always_show = FALSE;

	// filter switch status
	protected $_filtered = FALSE;
	
// 	protected $return_changes = TRUE;
	protected $use_activator = FALSE;
	
	public $restore_values = TRUE;
	
	// link to ORM
	protected $_orm;
	
	// session key 
	protected $_session_key;
	
	/** Return session key
	 *
	 * @return	string
	 */
	public function _sess_key( )
	{
		if (empty($this->_session_key))
		{
			$this->_session_key = substr(Basic::get_hash(Kohana_Form_Filter::SESS_VAR_FILTER.Request::current( )->uri( ).$this->label( )), 0, Kohana_Form_Filter::SESS_VAR_LENGTH);
		}
		
		return $this->_session_key;
	}
	
	/** Start form render
	 *  Add Form_Engine object and load specified param
	 *
	 * @param string 	custom form body
	 * @return this
	 */
	public function render($body = NULL)
	{
		$result = parent::render($body);
		
		if ($this->restore_values && $this->_filtered( ) === TRUE)
		{
			$this->sent(TRUE);
		}

		return $result;
	}
	
	/** Object constructor
	 *
	 * @param string 	form name
	 * @return void
	 */
	public function __construct($label = NULL)
	{
		parent::__construct($label);
		
		if ($this->restore_values)
		{
			// drop filter
			$clear_filter = Request::current( )->query(Kohana_Form_Filter::QUERY_CLEAR.$this->_sess_key( ));

			if (isset($clear_filter))
			{
				Session::instance( )->delete($this->_sess_key( ));
			}

			// load session data
			if ($this->restore_values === TRUE)
			{
			
				$session_data = unserialize(Session::instance( )->get($this->_sess_key( )));
			}
			
			if (isset($session_data) && $session_data !== FALSE)
			{
				$this->defaults($session_data);
				
				$this->filtered(TRUE);
			}
		}
		
		InclStream::instance( )->add('form.filter.css');
		$this->class = 'filter';
	}
	
	/** Add form element to field stack
	 *
	 * @param Form_Field
	 * @return this
	 */
	public function reg(Form_Field $field)
	{
		// set default ouput filter -- set form flag "filtered"
		$field->filter_out(
			function($value, $selected, $form)
			{
				if ($value !== NULL && $value !== '' || $selected === TRUE)
				{
					$form->filtered(TRUE);
				}
				
				return $value;
			},
			array(
				'form'		=> $this,
			)
		);
		
		return parent::reg($field);
	}
	
	/** Wrap filter html to spoiler
	 * 
	 * @param	string	html
	 * @return string
	 **/
	public function html($data = NULL)
	{
		if (isset($data))
		{
			if ($this->_filtered( ))
			{
				if ($this->restore_values === TRUE)
				{
					Session::instance( )->set(
						$this->_sess_key( ),
						serialize($this->result( )->as_array( ))
					);
				
				
					$anchor = $this->clear_button();
					$data = $anchor.$data;
					$this->text($anchor, 'reset');
				
				}
// 				$data = '<a href=?'.Request::GET_VARS_STORE_CLEAR.'>очистить фильтр</a>'.$data;
			}
			
			if ($this->always_show( ) !== TRUE)
			{
				$data = Site::spoiler($data, $this->_filtered( ), $this->filter_name( ));
			}
			
			parent::html($data);
			
			return $this;
		}
		else
		{
			return $this->html;
		}
	}
	
	/** Get filter clear link
	 *
	 * @param	string	text
	 * @return	string
	 */
	public function clear_button($text = NULL)
	{
		return $anchor = '<a class="drop-filter" href="?'.Kohana_Form_Filter::QUERY_CLEAR.$this->_sess_key( ).'=1">'.(isset($text) ? $text : __('drop filter')).'</a>';
	}
	
	
	/** set filtered status
	 * 
	 * @param	boolean	filtered flag
	 * @return	mixed
	 */
	public function filtered($filtered = NULL)
	{
		return $this->_filtered($filtered);
	}
	
	/** bind arguments for conditions
	 * 
	 * @param	array	common filter data
	 * @return	this
	 
	public function bind($data)
	{
		$this->_bind = $data;
		
		return $this;
	}
	*/
	/** Add action to field
	 *
	 * @param	string		field name
	 * @param	callback	callback
	 * @param	array		callback arguments
	 * @return	this
	 */
	public function callback($field, $callback, $args = array( ))
	{
		$this->fields($field)->filter_out(
			function($value, $selected, $callback, $args, $form, $field)
			{
				// add binded form values
//  				$args['bind'] = $bind;
				if ($value !== NULL && $value !== '' || $selected === TRUE)
				{
					if ($field instanceOf Form_Field_Checkbox)
					{
						// set value as first argument
						array_unshift($args, $selected);
					}
					else
					{
						// set value as first argument
						array_unshift($args, $value);
					}
				
 				
 					// execute action
					call_user_func_array($callback, $args);
 					
					$form->filtered(TRUE);
				}
				
				return $value;
			},
			array(
				'callback'	=> $callback,
				'args'		=> $args,
				'form'		=> $this,
				'field'		=> $this->fields($field),
			)
		);
	
		return $this;
	}
}