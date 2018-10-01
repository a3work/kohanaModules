<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Common Cart_Item class
 * @package 	Cart
 * @author 		Daniellz
 * @date 		2013-11-12
 *
 **/

class Kohana_Cart_Item implements Serializable {

	// empty values stub
	const EMPTY_STUB = '&mdash;';

	// config name
	protected $_config = 'cms_cart';
	
	// values
	protected $_values = array( );

	/** Object constructor
	 *
	 * @return void
	 */
	public function __construct( )
	{
		
	}
	
	/** set property
	 *
	 * @param 	string	property name
	 * @param 	mixed	value
	 * @return 	void
	 */
	public function __set ($name , $value)
	{
		$this->_values[$name] = $value;
	}
	
	/** get property
	 *
	 * @param 	string	property name
	 * @return 	mixed
	 */
	public function __get ($name)
	{
		return $this->_values[$name];
	}
	
	/** handler of isset or empty call on cart property
	 *
	 * @param 	string	property name
	 * @return 	boolean
	 */
	public function __isset($name)
	{
		return isset($this->_values[$name]);
	}

	/** unset cart properties
	 *
	 * @param 	string	property name
	 * @return 	void
	 */
	public function  __unset($name)
	{
		unset($this->_values[$name]);
	}
	
	
	public function key( )
	{
		return $this->_values[Kohana_Cart::PROP_KEY];
	}
	
	public function price( )
	{
		return $this->_values[Kohana_Cart::PROP_PRICE];
	}

	public function quan( )
	{
		return $this->_values[Kohana_Cart::PROP_QUANTITY];
	}
	
	public function total( )
	{
		return $this->_values[Kohana_Cart::PROP_PRICE] * $this->_values[Kohana_Cart::PROP_QUANTITY];
	}
    /** Set or get item value
     *
     * @param	mixed	key
     * @param	mixed	value
     * 
     * @return	mixed	value OR NULL if exists not
     */
    public function value($key, $value = NULL)
    {
		// set property
		if (isset($value))
		{
			$this->_values[$key] = $value;
			
			return $value;
		}
		
		// return value if property exists
		if (isset($this->_values[$key]))
		{
			return $this->_values[$key];
		}
		
		return NULL;
    }
    
    /** Return values array
     *
     * @return	array
     */
    public function as_array( )
    {
		return array_merge($this->_values, array('total' => $this->total()));
    }
    
    /** serializable **/
    
    /** serialize Cart_Item instance
     *
     * @return	string
     */
    public function serialize( )
    {
		return serialize($this->_values);
    }
    
	/** unserialize Cart_Item object
     *
     * @return	void
     */
    public function unserialize($data)
    {
		$this->__construct( );
		
		foreach (unserialize($data) AS $key => $value)
		{
			$this->_values[$key] = $value;
		}
    }
}