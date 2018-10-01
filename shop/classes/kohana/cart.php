<?php defined('SYSPATH') or die('No direct script access.');


/**
 *
 * @name		Common cart class
 * @package 	Cart
 * @author 		Daniellz
 * @date 		2013-10-18
 *
 **/

abstract class Kohana_Cart implements Iterator, Countable, Serializable {

	/**
	 * @const string	DESCR
	 */
	const STORE_VAR = 'cart_data';
	
	/**
	 * @const string	quantity var name
	 */
	const PROP_QUANTITY = 'quantity';
	
	/**
	 * @const string	price var name
	 */
	const PROP_PRICE = 'price';
	
	/**
	 * @const string	total cost var name
	 */
	const PROP_TOTAL = 'total';
	
	/**
	 * @const string	id var name
	 */
	const PROP_KEY = 'id';
	
	/**
	 * @const string	id var name
	 */
	const PROP_DATA = 'data';
	
	// config name
	protected $_config = 'shop';

	// singleton holder
	protected static $_instance;

	// cart variables
	protected $_properties = array( );

	// array of Cart_Item objects
	protected $_list = array( );
	
	// need clear
	protected $_clear = FALSE;
	
	// totals
	protected $_quan;
	protected $_total;
	

	/** Cart object constructor
	 *
	 * @return void
	 */
	protected function __construct( )
	{

	}
	
	/** Cart object destructor
	 *
	 * @return	void
	 */
	public function __destruct( )
	{
		// don't save empty cart
		if (count($this->_list) != 0  || count($this->_properties) != 0)
		{
			Cookie::store(Kohana_Cart::STORE_VAR, serialize($this));
		}
		elseif ($this->_clear)
		{
			Cookie::store_clear(Kohana_Cart::STORE_VAR);
		}
	}
	
	/** Return config name
	* 
	* @return	string
	*/
	public function config( )
	{
		return $this->_config;
	}
	
	/** Load data to specified item
	 *
	 * 	default behaviour: use $data keys as names of Cart_Item parameters
	 * 
	 * @param	mixed		data
	 * @param	Cart_Item	item for loading
	 * 
	 * @return	void
	 */
    protected function _load($data, Cart_Item $item)
    {
		foreach ($data AS $key => $value)
		{
			$item->value($key, $data[$key]);
		}
    }
    
    /** Set or get cart property
     *
     * @param	mixed	key
     * @param	mixed	value
     * 
     * @return	mixed	value OR NULL if exists not
     */
    public function prop($key, $value = NULL)
    {
		// set property
		if (isset($value))
		{
			$this->_properties[$key] = $value;
			
			return $value;
		}
		
		// return value if property exists
		if (isset($this->_properties[$key]))
		{
			return $this->_properties[$key];
		}
		
		return NULL;
    }
    
	/** Count quantity and cost
	* 
	* @return	void
	*/
	protected function _count( )
	{
		foreach ($this AS $item)
		{
			$this->_quan += $item->value(Kohana_Cart::PROP_QUANTITY);
			$this->_total += $item->value(Kohana_Cart::PROP_PRICE)*$item->value(Kohana_Cart::PROP_QUANTITY);
		}
	}
    
    /** Drop totals
	* 
	* @return	void
	*/
	protected function _drop_totals( )
	{
		$this->_quan = NULL; 
		$this->_total = NULL; 
	}
    
    
    /** Return quantity
	 * 
	 * @return	integer
	 */
	public function quan( )
	{
		if ( ! isset($this->_quan))
		{
			$this->_count( );
		}
		
		return $this->_quan;
	}
	
	/** DESCR
	 * 
	 * @param	dafs	dafs
	 * @return	sadf
	 */
	public function total( )
	{
		if ( ! isset($this->_total))
		{
			$this->_count( );
		}
		
		return $this->_total;
	}
	
	
	
	
	/** Return add-to-cart link for specified id
	 * 
	 * :DEPRECATED: use controller (controller_cart or controller_cms_cart) action
	 * 
	 * @param	mixed	id
	 * @return	string
	 */
	public static function add_href($id)
	{
		InclStream::instance( )->add('cart.js');
	
		return HTML::anchor(Route::url('cart', array('action' => 'add', 'id' => $id)), '', array('class' => 'to-cart'));
	}
	
	
	
	/** Singleton factory
	 *
	 * @return Cart
	 */
	public static function instance( )
	{
        if (self::$_instance === NULL)
        {
			// add Cart instance
			$classname = get_called_class( );
			
			self::$_instance = new $classname;
			
			$stored_cart = Cookie::store(Kohana_Cart::STORE_VAR);

			if ($stored_cart !== NULL)
			{
				self::$_instance = unserialize($stored_cart);
			}
        }
        
        // return created instance
        return self::$_instance;
    }
    
	/** Add new item
	 *
	 * @param	mixed	data id
	 * @return	boolean
	 */
    public function add($data)
    {
		$this->_drop_totals( );
		
		// drop quantity
		if (empty($data[Kohana_Cart::PROP_QUANTITY]))
		{
			$data[Kohana_Cart::PROP_QUANTITY] = 1;
		}
		
		// create cart item
		$item = new Cart_Item;
		
		// load data to item
		$this->_load($data, $item);
		
		// append item to list
		$this->append($item);
		
		return TRUE;
    }

	/** Remove item
	 *
	 * @param	mixed	item key
	 * @return	boolean
	 */
    public function remove($key)
    {
		// remove specified Cart_Item
		if (is_object($key) && $key instanceOf Cart_Item)
		{
			$key = array_search($key, $this->_list, TRUE);
			
			if ($key === FALSE)
			{
				return FALSE;
			}
		}
		
		$this->_drop_totals( );
		
		// load item by key	
		if (isset($this->_list[$key]))
		{
			unset($this->_list[$key]);
			
			// clear store data if items existn't
			if (count($this->_list) == 0)
			{
				$this->_clear = TRUE;
			}
			
			return TRUE;
		}

		return FALSE;
    }
    
	/** Remove item
	 *
	 * @param	mixed	item key
	 * @param	mixed	data
	 * @return	boolean
	 */
    public function modify($key, $data)
    {
		// check item existence	
		if ( ! isset($this->_list[$key]))
		{
			return FALSE;
		}
		
		// change types of values
		if (isset($data[Kohana_Cart::PROP_QUANTITY]))
		{
			$this->_drop_totals( );
			$data[Kohana_Cart::PROP_QUANTITY] = (int) $data[Kohana_Cart::PROP_QUANTITY];
		}
		
		// change types of values
		if (isset($data[Kohana_Cart::PROP_PRICE]))
		{
			$this->_drop_totals( );
			$data[Kohana_Cart::PROP_PRICE] = (float) $data[Kohana_Cart::PROP_PRICE];
		}
		
		// rewrite data
		$this->_load($data, $this->_list[$key]);
		
		if ($this->_list[$key]->quan( ) == 0)
		{
			$this->remove($key);
		}
		
		return TRUE;
    }

	/** Return item
	 *
	 * @param	mixed	item key
	 * @return	mixed	Cart_Item or FALSE if it exists not
	 */
    public function item($key)
    {
		// check item existence	
		if ( ! isset($this->_list[$key]))
		{
			return FALSE;
		}
		
		return $this->_list[$key];
    }

    /** Add new item or find already created and return it
	 *
	 * @param	mixed	item id
	 * @return	Cart
	 */
	public function append(Cart_Item $item)
    {
		if (Site::config($this->_config)->distinct_key)
		{
			// :TODO: addition modes: use unique id or increase quantity
			$this->_list[$item->key( )] = $item;
		}
		else
		{
			$this->_list[] = $item;
		}
    }
    
    /** Clear cart data
     *
     * @return	void
     */
    public function clear( )
    {
		$this->_properties	= array( );
		$this->_list		= array( );
		$this->_clear 		= TRUE;
    }
	
	

	/** IMPLEMENTATION OF INTERFACES **/
	/** iterator **/
     public function rewind()
    {
        reset($this->_list);
    }

    public function current()
    {
         return current($this->_list);
    }

    public function key()
    {
         return key($this->_list);
    }

    public function next()
    {
        return next($this->_list);
    }

    public function valid()
    {
        $key = key($this->_list);
        $var = ($key !== NULL && $key !== FALSE);
        return $var;
    }

    
    /** countable **/
    public function count( )
    {
		return $this->quan( );
    }
    
    
    /** serializable **/
    
    public function serialize( )
    {
		return serialize(array($this->_list, $this->_properties));
    }
    
    public function unserialize($data)
    {
		list($this->_list, $this->_properties) = unserialize($data);
    }
    
	/** 
	 * set state mark
	 *
	 * @return 	string
	 */
	public function get_state()
	{
		return Session::instance()->get('cart_change');
	}

	/** 
	 * set state mark
	 *
	 * @return 	void
	 */
	public function set_state()
	{
		Session::instance()->set('cart_change', time());
		
		return TRUE;
	}
	
	/** 
	 * Find item by key and value
	 *
	 * @param		string		key
	 * @param		mixed		value
	 * @return 	void
	 */
	public function find($key, $value) {
		foreach ($this->_list AS $item) {
			if ($item !== NULL && $item->data && $item->data->$key == $value) {
				return $item;
			}
		}
		
		return NULL;
	}
}