<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_Discount extends ORM
{
	/**
	 * @const string	name of cache tag
	 */
	const DISCOUNT_CACHE_NAME = 'discount_cache';
	
	protected $_table_name = 'shop_discount';

	protected $_belongs_to = array(
		'pricelist'	=> array('model' => 'pricelist', 'foreign_key' => 'pricelist_id'),
		'supplier'	=> array('model' => 'supplier', 'foreign_key' => 'supplier_id'),
		'user'	=> array('model' => 'account', 'foreign_key' => 'user_id'),
	);
	
	/** Get discount values for specified user
	 *
	 * @param 	integer	user ID
	 * @return 	array
	 */
	protected function _data($user_id = 0)
	{
		if (Kohana::$caching)
		{
			$cache_name = Kohana_Model_Discount::DISCOUNT_CACHE_NAME.'_'.$user_id;
			$data = Cache::instance( )->get($cache_name);
		}
		
		/* save discount cache */
		if (empty($data))
		{
			$data = array( );
			
			$discount = ORM::factory('discount')
						->where('user_id', '=', $user_id)
						->where('start', '<=', $_SERVER['REQUEST_TIME'])
						->and_where_open( )
						->where('end', '>=', $_SERVER['REQUEST_TIME'])
						->or_where('end', '=', 0)
						->and_where_close( )
						->order_by('supplier_id')
						->order_by('pricelist_id');

			$expire = Site::config('cache')->get('default-expire');
			
			$res = $discount->find_all( );

			foreach ($res AS $discount_item)
			{
				if ($discount_item->pricelist_id != 0)
				{
					if ( ! isset($data['pricelists']))
					{
						$data['pricelists'] = array( );
					}
					
					$data['pricelists'][$discount_item->pricelist_id] = $discount_item->value;
				}
				elseif ($discount_item->supplier_id != 0)
				{
					if ( ! isset($data['suppliers']))
					{
						$data['suppliers'] = array( );
					}
					
					$data['suppliers'][$discount_item->supplier_id] = $discount_item->value;
				}
				else
				{
					$data['common'] = $discount_item->value;
				}
				
				if ($discount_item->end != 0)
				{
					$current_remaining = $discount_item->end - $_SERVER['REQUEST_TIME'];
					
					if ($current_remaining < $expire)
					{
						$expire = $current_remaining;
					}
				}
			}
			
			// common default discount
			if ( ! isset($data['common']))
			{
				$data['common'] = 0;
			}

			// cache data
			if (Kohana::$caching)
			{
				Cache::instance( )->set_with_tags($cache_name, $data, $expire, array(Kohana_Model_Discount::DISCOUNT_CACHE_NAME));
			}
		}
		
		return $data;
	}
	
	/** Reload save function: add cleaning of cache
	 *
	 * @param	Validation
	 * @return 	this
	 */
	public function save(Validation $validation = NULL)
	{
		Cache::instance( )->delete_tag(Kohana_Model_Discount::DISCOUNT_CACHE_NAME);
// 		Cache::instance( )->delete(Kohana_Model_Discount::DISCOUNT_CACHE_NAME.'_'.$this->user_id);

		Kohana::$log->add(
			Log::INFO,
			"discount has been changed (user_id::user_id, pricelist_id::pricelist_id, supplier_id::supplier_id, value::value)",
			array(
				':user_id'		=> (int) $this->user_id,
				':pricelist_id'	=> (int) $this->pricelist_id,
				':supplier_id'	=> (int) $this->supplier_id,
				':value'		=> $this->value,
			)
		);
		
		return parent::save($validation);
	}
	
	/** Reload delete function: add cleaning of cache
	 *
	 * @return 	this
	 */
	public function delete( )
	{
// 		Cache::instance( )->delete(Kohana_Model_Discount::DISCOUNT_CACHE_NAME.'_'.$this->user_id);
		Cache::instance( )->delete_tag(Kohana_Model_Discount::DISCOUNT_CACHE_NAME);
		
		Kohana::$log->add(
			Log::INFO,
			"discount has been deleted (user_id::user_id, pricelist_id::pricelist_id, supplier_id::supplier_id, value::value)",
			array(
				':user_id'		=> (int) $this->user_id,
				':pricelist_id'	=> (int) $this->pricelist_id,
				':supplier_id'	=> (int) $this->supplier_id,
				':value'		=> $this->value,
			)
		);
		
		return parent::delete( );
	}
	
	/** Get discount for specified user, supplier or pricelist
	 *
	 * @param 	integer		supplier_id
	 * @param 	integer		pricelist_id
	 * @param	integer		user_id
	 * @return 	float		discount factor
	 */
	public function get($pricelist_id = NULL, $supplier_id = NULL, $user_id = NULL)
	{
		if (empty($user_id))
		{
			$user_id = User::get( )->id;
		}
	
		$data = $this->_data($user_id);
		
		if (isset($pricelist_id) && isset($data['pricelists'][$pricelist_id]))
		{
			$discount = $data['pricelists'][$pricelist_id];
		}
		elseif(isset($supplier_id) && isset($data['suppliers'][$supplier_id]))
		{
			$discount = $data['suppliers'][$supplier_id];
		}
		else
		{
			$discount = $data['common'];
		}
		
		return 1-$discount/100;
	}
	
	/** Calculate discount for specified user, supplier or pricelist
	 *
	 * @param	float	price
	 * @param	integer	supplier id
	 * @param	integer	pricelist id
	 * @param	integer	user id
	 *
	 * @return	float	markup factor
	 */
	public function calc($price, $pricelist_id = NULL, $supplier_id = NULL, $user_id = NULL)
	{
		return round($price*$this->get($pricelist_id, $supplier_id, $user_id), Site::config('shop')->accuracy);
	}
}