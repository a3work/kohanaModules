<?php

class Access_Shop extends Access_Module
{
	public function __construct( )
	{
		// Module name
		$this->name('Товары и заказы');

		// Module privileges
		$this->add('shop_preferences')->label('Управление настройками');
		$this->add('shop_orders_add')->label('Создание заказа');
		$this->add('shop_manage_goods')->label('Управление товарами');
		$this->add('shop_manage_orders')->label('Управление заказами');
		$this->add('shop_currency_viewing')->label('Просмотр курсов валют');
		$this->add('shop_currency_manage')->label('Управление курсами валют');
		$this->add('shop_markup_viewing')->label('Просмотр наценок');
		$this->add('shop_markup_manage')->label('Управление наценками');
		$this->add('shop_discount_viewing')->label('Просмотр наценок');
		$this->add('shop_discount_manage')->label('Управление наценками');
		$this->add('shop_suppliers_viewing')->label('Просмотр поставщиков');
		$this->add('shop_suppliers_manage')->label('Управление поставщиками');
		$this->add('shop_states_viewing')->label('Просмотр статусов заказов');
		$this->add('shop_states_manage')->label('Управление статусами заказов');
		$this->add('shop_pricelists_viewing')->label('Просмотр прайс-листов');
		$this->add('shop_pricelists_manage')->label('Управление прайс-листами');
		$this->add('shop_goods_viewing')->label('Просмотр товаров');
		$this->add('shop_goods_manage')->label('Управление товарами');
		$this->add('shop_orders_viewing')->label('Просмотр заказов');
		$this->add('shop_my_orders_manage')->label('Управление своими заказами');
		$this->add('shop_orders_manage')->label('Управление заказами');
		$this->add('shop_cart_usage')->label('Использование корзины');
		
		$this->template('Клиент')
			->attach('shop_preferences')
			->attach('shop_orders_add')
			->attach('shop_my_orders_manage');
			
		$this->template('Администратор')
			->attach('shop_manage_orders')
			->attach('shop_manage_goods');

		$this->template('Администратор')
			->attach('shop_manage_orders')
			->attach('shop_manage_goods');
	}
}
