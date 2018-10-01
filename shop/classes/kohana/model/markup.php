<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_Markup extends ORM
{
	/**
	 * @const string	name of cache tag
	 */
	const MARKUP_CACHE_NAME = 'discount_cache';

	protected $_table_name = 'shop_markup';

	protected $_belongs_to = array(
		'pricelist'	=> array('model' => 'pricelist', 'foreign_key' => 'pricelist_id'),
		'supplier'	=> array('model' => 'supplier', 'foreign_key' => 'supplier_id'),
	);
	
	/** Get markup
	 *
	 * @param 	integer	user ID
	 * @return 	array
	 */
	protected function _data( )
	{
		
		if (Kohana::$caching)
		{
			$cache_name = Kohana_Model_Markup::MARKUP_CACHE_NAME;
			$data = Cache::instance( )->get($cache_name);
		}
		
		/* save markup cache */
		if (empty($data))
		{
			$data = array( );
			
			$markup = ORM::factory('markup');

			$expire = Site::config('cache')->get('default-expire');
			
			foreach ($markup->find_all( ) AS $markup_item)
			{
				if ($markup_item->pricelist_id != 0)
				{
					if ( ! isset($data['pricelists']))
					{
						$data['pricelists'] = array( );
					}
					
					$data['pricelists'][$markup_item->pricelist_id] = $markup_item->value;
				}
				elseif ($markup_item->supplier_id != 0)
				{
					if ( ! isset($data['suppliers']))
					{
						$data['suppliers'] = array( );
					}
					
					$data['suppliers'][$markup_item->supplier_id] = $markup_item->value;
				}
				else
				{
					$data['common'] = $markup_item->value;
				}
			}
			
			// common default markup
			if ( ! isset($data['common']))
			{
				$data['common'] = Site::config('shop')->margin_default;
			}
			
			// cache data
			if (Kohana::$caching)
			{
				Cache::instance( )->set_with_tags($cache_name, $data, NULL, array($cache_name));
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
		Cache::instance( )->delete(Kohana_Model_Markup::MARKUP_CACHE_NAME);
		
		Kohana::$log->add(
			Log::INFO,
			"markup has been changed (pricelist_id::pricelist_id, supplier_id::supplier_id, value::value)",
			array(
				':pricelist_id'	=> (int) $this->pricelist_id,
				':supplier_id'	=> (int) $this->supplier_id,
				':value'		=> $this->value,
			)
		);
		
		$return = parent::save($validation);
		
		return $return;
	}
	
	/** Reload delete function: add cleaning of cache
	 *
	 * @return 	this
	 */
	public function delete( )
	{
		Cache::instance( )->delete_tag(Kohana_Model_Markup::MARKUP_CACHE_NAME);
		
		Kohana::$log->add(
			Log::INFO,
			"markup has been deleted (pricelist_id::pricelist_id, supplier_id::supplier_id, value::value)",
			array(
				':pricelist_id'	=> (int) $this->pricelist_id,
				':supplier_id'	=> (int) $this->supplier_id,
				':value'		=> $this->value,
			)
		);
		
		return parent::delete( );
	}
	
	/** Get markup for specified supplier or pricelist
	 *
	 * @param	integer		supplier
	 * @param	integer		pricelist
	 *
	 * @return	integer		markup value
	 */
	public function get($pricelist_id = NULL, $supplier_id = NULL) 
	{
		$data = $this->_data( );
		
		if (isset($pricelist_id) && isset($data['pricelists'][$pricelist_id]))
		{
			$markup = $data['pricelists'][$pricelist_id];
		}
		elseif(isset($supplier_id) && isset($data['suppliers'][$supplier_id]))
		{
			$markup = $data['suppliers'][$supplier_id];
		}
		else
		{
			$markup = $data['common'];
		}
	
		return $markup;
	}
	
	/** Get markup factor for specified supplier or pricelist
	 *
	 * @param	integer	supplier
	 * @param	integer	pricelist
	 *
	 * @return	float	markup factor
	 */
	public function factor($pricelist_id = NULL, $supplier_id = NULL)
	{
		return 1+$this->get($pricelist_id, $supplier_id)/100;
	}
	
	/** Calculate markup for specified supplier or pricelist
	 *
	 * @param	float	price
	 * @param	integer	supplier
	 * @param	integer	pricelist
	 *
	 * @return	float	markup factor
	 */
	public function calc($price, $pricelist_id = NULL, $supplier_id = NULL)
	{
		return round($price*$this->factor($pricelist_id, $supplier_id), Site::config('shop')->accuracy);
	}
}