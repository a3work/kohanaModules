<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_State extends ORM {

	protected $_table_name = 'shop_states';
	
	/** Get initial status
     *
     * @param	boolean return data or integer key
     * @return	mixed
     */
    public function initial( )
    {
		return ORM::factory('state')->where('is_initial', '=', 1)->find( );
    }
}
