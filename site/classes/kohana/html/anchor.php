<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Html anchor generator
 * @package 	Site
 * @author 		A. St.
 * @date 		02.01.14
 *
 **/

class Kohana_Html_Anchor
{
	const WIN_TARGET		= '_window';
	const ID_LENGTH = 8;

	/**
	 * @var string 	generated html
	 */
	protected $_html;
	
	/**
	 * @var array 	css classes
	 */
	protected $_classes;
	
	/**
	 * @var string 	js action
	 */
	protected $_action;

	/**
	 * @var mixed 	confirm message or false
	 */
	protected $_confirm = FALSE;
	

	/** Object constructor
	 *
	 * @return void
	 */
	public function __construct( )
	{

	}
	
	/** Bind html render on transformation to string
	 *
	 * @return	string
	 */
	public function __toString( )
	{
		try
		{
			return $this->render( );
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
	
	/** Set attribute
	 * 
	 * @param	string	attribute name
	 * @param	string	attribute value
	 * @return	this
	 */
	public function attr($key, $value = NULL)
	{
		if ($value === NULL)
		{
			return isset($this->_attr[$key]) ? $this->_attr[$key] : NULL;
		}
		
		$this->_attr[$key] = $value;
	
		return $this;
	}
	
	/** Set or get css classes
	 *
	 * @param	array	array of css classes
	 * @return	mixed
	 */
	public function classes($classes = NULL)
	{
		if (empty($this->_attr['class']))
		{
			$this->_attr['class'] = array( );
		}

		if (empty($classes))
		{
			return $this->_attr['class'];
		}
		
		$this->_attr['class'] = array_merge($this->_attr['class'], $classes);
		
		return $this;
	}
	
	/** Get window activator id or css class
	 *
	 * @return	string
	 */
	protected function _id()
	{
	
		if (($classes = $this->classes( )) !== NULL)
		{
			if (is_array($classes))
			{
				if (count($classes) != 0)
				{
					return $classes[0];
				}
			}
			else
			{
				return '.'.$classes;
			}
		}
		
		if (empty($this->_id))
		{
			$this->_id = '#'.substr(Basic::get_hash( ), 0, Kohana_Html_Anchor::ID_LENGTH);
		}
		
		return $this->_id;
	}
	
	/** use ajax mode
	 *
	 * @return 	this
	 */
	public function ajax( )
	{
		$this->_action = 'Cms.action';
		
		return $this;
	}

	/** Use confirmation confirmation
	 *
	 * @param 	mixed	confirm message (use default if TRUE)
	 * @return 	this
	 */
	public function confirm($message = TRUE)
	{
		$this->_confirm = $message;
		
		return $this;
	}

	/** Attach jquery.window to anchor
	 * 
	 * @param	string	preset config name or js config
	 * @return	this
	 */
	public function window($config = NULL)
	{
		Window::factory($config, $this->_attr)->bind($this->_id( ).'[target="'.Kohana_Html_Anchor::WIN_TARGET.'"]');
		
		$this->attr('target', Kohana_Html_Anchor::WIN_TARGET);
		
		return $this;
	}
	
	/** Render html
	* 
	* @return	string
	*/
	public function render( )
	{
		if ($this->_html( ) === NULL)
		{
			$out = '<a';
			
			if (isset($this->_action))
			{
				if ($this->_action == 'Cms.action')
				{
					InclStream::instance( )->add('cms.js');
					$this->classes(array('ajax'));
				}
			}

			if ($this->_confirm( ) !== FALSE)
			{
				$this->classes(array('confirm'));
				
				if (is_string($this->_confirm( )))
				{
					$out .= ' data-confirm-msg="'.$this->_confirm( ).'"';
				}
			}
		
			
			$text = '';
		
			if ($this->_id( ))
			{
				$out .= ' id="'.str_replace('#', '', $this->_id( )).'"';
			}
			
		
			foreach ($this->_attr( ) AS $key => $value)
			{
				if ($key == 'text')
				{
					$text = $value;
					
					continue;
				}
				
				if ($key == 'class' && is_array($value))
				{
					$value = implode(' ', $value);
				}
			
				$out .= ' '.$key.'=\''.str_replace('\'', '\\\'', $value).'\'';
			}
			
			$out .= '>'.$text.'</a>';
		
			$this->_html($out);
		}
		
		return $this->_html;
	}

	
	
	/* ATTRIBUTES SETTERS SHORTCUTS */
	
	/** Href attribute setter
	* 
	* @return	string
	*/
	public function href($href = NULL)
	{
		return $this->attr('href', $href);
	}
	
	/** Text attribute setter
	* 
	* @return	string
	*/
	public function text($text = NULL)
	{
		return $this->attr('text', $text);
	}

	/** Target attribute setter
	* 
	* @return	string
	*/
	public function target($title = NULL)
	{
		return $this->attr('target', $title);
	}
	
	/** Title attribute setter
	* 
	* @return	string
	*/
	public function title($title = NULL)
	{
		return $this->attr('title', $title);
	}
	
	
}