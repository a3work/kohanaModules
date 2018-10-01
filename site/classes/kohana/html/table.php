<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Table generator
 * @package 	Site/HTML
 * @author 		Stanislav U. Alkimovich
 * @date 		20.12.13
 *
 **/

class Kohana_Html_Table
{

	// generated html
	protected $_html;

	// table header
	protected $_header = array( );

	// table body
	protected $_body = array( );
	
	// link to current row
	protected $_current_item;
	
	// empty table message
	protected $_empty_message;

	/** Object constructor
	 *
	 * @return void
	 */
	public function __construct( )
	{
		InclStream::instance( )->add('table.js');
		InclStream::instance( )->add('table.css');
		
		$this->_empty_message(__u('found nothing').'.');
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
				$this->_body[$key] .= '<td>'.$cell.'</td>';
			}
			
		}
		$this->_body[$key] = '<tr>'.$this->_body[$key].'</tr>';
		
		// save link to current row
		$this->_current_item = &$this->_body[$key];
	
		return $this;
	} 
	
	/** 
	 * fetch current count of rows
	 *
	 * @return int
	 */
	public function rows_count()
	{
		return count($this->_body);
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
	
		$this->_header[$key] = '<thead><tr><th>'.implode('</th><th>', $data).'</th></tr></thead>';
		
		// save link to current row
		$this->_current_item = &$this->_header[$key];
	
		return $this;
	}
	
	/** add href for current row
	 *
	 * :DEPRECATED: use Menu_Context::dbl method for handler binding
	 *
	 * @param	string	href
	 * @return	this
	 */
	public function href($href)
	{
		if ($this->_current_item( ) == NULL || strpos($this->_current_item( ), ' title') !== FALSE)
		{
			return $this;
		}
		
		$this->_current_item(str_replace('<tr', '<tr title="'.__('go to').' '.Html::chars($href).'"', $this->_current_item( )));
		
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
		
		$this->_current_item(str_replace('<tr', '<tr class="'.$classes.'"', $this->_current_item( )));
		
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
				$this->_html('<table'.(isset($classes) ? ' class="'.$classes.'"' : '').'>'.implode('', $this->_header( )).implode('', $this->_body( )).'</table>');
			}
			else
			{
				$this->_html('<div>'.$this->_empty_message().'</div>');
			}
		}
	
		return $this->_html( );
	}	
}

function wrap_to_small(&$data)
{
	return '<small>'.$data.'</small>';
}

