<?php defined('SYSPATH') or die('No direct script access.');

class Arr extends Kohana_Arr implements Countable, Iterator, ArrayAccess {

	// data
	protected $_data;

	/** START INTERFACES IMPLEMENTATION **/

	/* countable */
	public function count() 
	{ 
		return count($this->_data); 
	}
	
	/* iterator */
	function rewind() {
		reset($this->_data);
	}

	function current() {
		return current($this->_data);
	}

	function key() {
		return key($this->_data);
	}

	function next() {
		next($this->_data);
	}
	function valid() {
		return isset($this->_data[key($this->_data)]);
	}	
	
	/* ArrayAccess */
    public function offsetSet($offset, $value)
	{
        if (is_null($offset))
		{
            $this->_data[] = $value;
        }
        else 
        {
            $this->_data[$offset] = $value;
        }
    }
    
    public function offsetExists($offset)
	{
        return isset($this->_data[$offset]);
    }
    
    public function offsetUnset($offset)
	{
        unset($this->_data[$offset]);
    }
    
    public function offsetGet($offset)
	{
        return isset($this->_data[$offset]) ? $this->_data[$offset] : null;
    }
    
	/** END INTERFACES IMPLEMENTATION **/

	/** Construct object and add item to array
	 *
	 * @param	mixed	items
	 * @param 	...
	 * @return	void
	 */
	public function __construct( )
	{
		// add all arguments to array
		$this->_data = func_get_args( );
	}
	
	public function __toString( )
	{
		return implode('', $this->_data);
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
			$this->_data[$var] = $args[0];

			return $this;
		}

		return $this->$var;
	}

	/** Array simple factory
	 *  all parameters pass to __construct
	 * 
	 * @return	object
	 */
	public static function factory( )
	{
		$reflection = new ReflectionClass('Arr');
		
		return $reflection->newInstanceArgs(func_get_args( ));
	}
}