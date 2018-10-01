<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Grid generator
 * @package 	Site/HTML
 * @date 		20.12.13
 *
 **/

class Kohana_Html_Grid
{

	// generated html
	protected $_html;

	// table header
	protected $_header = array( );

	// table body
	protected $_body = array( );
	
	// link to current row
	protected $_current_item;

	/** Object constructor
	 *
	 * @return void
	 */
	public function __construct( )
	{
		InclStream::instance( )->add('table.js');
		InclStream::instance( )->add('table.css');
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
	
	/** Mark values as small, make cell content
	* 
	* @param	mixed	data
	* @return	string
	*/
	protected function _divide_data($data)
	{
		if (is_array($data))
		{
			$out = array_shift($data).implode('', array_map('wrap_to_small', $data));
		}
		else
		{
			$out = $data;
		}
		
		return $out;
	}
	
	/** add line
	 * 
	 * @param	array	data
	 * @param	mixed	line key (RESERVED)
	 * @return	this
	 */
	public function line(array $data, $key = NULL)
	{
		$data = array_map(array($this, '_divide_data'), $data);
	
		// next key
		$key = count($this->_body);
	
		$this->_body[$key] = '';
		foreach ($data AS $cell)
		{
			/* :KLUDGE: */
			if (is_object($cell) && $cell instanceOf View)
			{
				$this->_body[$key] .= $cell;
			}
			else
			{
				$this->_body[$key] .= '<div>'.$cell.'</div>';
			}
			
		}
		$this->_body[$key] = '<li>'.$this->_body[$key].'</li>';
		
		// save link to current row
		$this->_current_item = &$this->_body[$key];
	
		return $this;
	} 
	
	/** init table
	 * 
	 * @param	array	data
	 * @param	mixed	line key
	 * @return	this
	 */
	public function header(array $data)
	{
		$data = array_map(array($this, '_divide_data'), $data);
	
		$key = count($this->_header);
	
		// :TODO: grid header
		//$this->_header[$key] = '<thead><tr><th>'.implode('</th><th>', $data).'</th></tr></thead>';
		
		// save link to current row
		$this->_current_item = &$this->_header[$key];
	
		return $this;
	}
	
	/** add css classes for current row
	 *
	 * @param	string	css classes
	 * @return	this
	 */
	public function classes($classes)
	{
		/* :TODO: make effective class setup identification */
		if ($this->_current_item( ) === NULL || preg_match('/<tr[^>]+class/', $this->_current_item( )))
		{
			return $this;
		}
		
		$this->_current_item(str_replace('<li', '<li class="'.$classes.'"', $this->_current_item( )));
		
		return $this;
	}
	
	/** render table
	* 
	* @param	string	css classes
	* @return	text
	*/
	public function render($classes = NULL)
	{
		if ($this->_html( ) === NULL)
		{
			if (count($this->_body( )) > 0)
			{
				$this->_html('<div class="cms-grid"><ul'.(isset($classes) ? ' class="'.$classes.'"' : '').'>'.implode('', $this->_header( )).implode('', $this->_body( )).'</ul></div>');
			}
			else
			{
				$this->_html(__u('found nothing').'.');
			}
		}
	
		return $this->_html( );
	}	
}

function wrap_to_small(&$data)
{
	return '<small>'.$data.'</small>';
}

