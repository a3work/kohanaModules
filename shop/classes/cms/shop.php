<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Shop cms module
 * @package 	Shop
 * @author 		Stanislav U. Alkimovich
 * @date 		2014-01-04
 *
 **/

class CMS_Shop extends CMS_Module
{
	/** Object constructor
	 *
	 * @return void
	 */
	public function __construct( )
	{
// 		$menu = $this->menu('ТМЦ');

		if (acl('shop_orders_add'))
		{
// 			$menu->item(__('cart'), Route::url('cms_cart', array('mode' => CMS::VIEW_MODE_FULL)));
		}
		
		$menu = $this->menu(__u('shop'));
		
		if (acl('shop_goods_viewing'))
		{
			$menu->item(__('goods'), Route::url('cms.common', array('controller' => 'Goods')));
			$menu->item(__('categories of goods'), Route::url('cms.common', array('controller' => 'Goods_Categories')));
		}
		if (acl('shop_suppliers_viewing'))
		{
			$menu->item(__('suppliers'), Route::url('cms.common', array('controller' => 'Suppliers')));
		}
		if (acl('shop_pricelists_viewing'))
		{
			$menu->item(__('pricelists'), Route::url('cms.common', array('controller' => 'Pricelists')));
		}
		if (acl('shop_currency_viewing'))
		{
			$menu->item(__('rates of currency'), Route::url('cms.common', array('controller' => 'Currency')));
		}
		if (acl('shop_markup_viewing'))
		{
			$menu->item(__('markup table'), Route::url('cms.common', array('controller' => 'Markup')));
		}
		if (acl('shop_currency_viewing'))
		{
			$menu->item(__('discount table'), Route::url('cms.common', array('controller' => 'Discount')));
		}
		if (acl('shop_orders_viewing'))
		{
			$menu->item(__('orders table'), Route::url('cms.common', array('controller' => 'Orders')));
		}
		if (acl('shop_states_viewing'))
		{
			$menu->item(__('states'), Route::url('cms.common', array('controller' => 'states')));
		}
	}
}
