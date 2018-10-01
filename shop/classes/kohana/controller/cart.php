<?php defined('SYSPATH') or die('No direct script access.');

abstract class Kohana_Controller_Cart extends Controller_Main {
 

 	/**
	 * @const string	ids separator for multiple removing
	 */
	const IDS_SEPARATOR = '_';
 
	// config file name
	protected $_config = 'cms_cart';
	
	public function before( )
	{
		InclStream::instance( )->add('cart.css');
		InclStream::instance( )->config(array(
			'cart_ids_separator' => Kohana_Controller_Cart::IDS_SEPARATOR,
			'cart_modify_url' => Route::url('cart', array('action' => 'option')),
			'cart_prop_quan' => Kohana_Cart::PROP_QUANTITY,
		));
		

        if ($this->_uri === NULL)
        {
			$this->_uri = Route::url('cart');
		}
		
		if ($this->request->action( ) == 'include')
		{
			$this->_auto_load = FALSE;
			$this->_auto_render = FALSE;
		}
		
		
		if ( ! $this->request->is_initial( ))
		{
			$this->_use_main_template = FALSE;
		}
		
		parent::before( );

		
		$this->_back_page = $this->request->query('return');
		
		if ($this->_back_page == '')
		{
			$this->_back_page = $this->request->referrer( );
		}
	}
 
    /** Load data by specified id from database or other source
	 *
	 * @param	mixed	data ID
	 * 
	 * @return	mixed
	 */
    abstract protected function _data($id);

    /** Show cart
     *
     * @return	void
     */
    public function action_index( )
    {
		$cart = Cart::instance( );
		$count = count($cart);
		
		$out = '';
    
		if ($count > 0)
		{
			$form = Form::factory( )
					->return_changes(TRUE);
		
			// render header 
			$header = array( );
// 			foreach (Site::config($this->_config)->format AS $format_item)
// 			{
// 				$header[] = $format_item['label'];
// 			}
			
			$table = Html::factory('table')->header(array(
				__('артикул'),
				__('производитель'),
				__('описание'),
				__('доставка'),
				__('цена'),
				__('совместимость'),
				'',
			));
			
			
			foreach ($cart AS $key => $item)
			{
			
				$table->line(array(
					$item->data->code,
					$item->data->producer,
					$item->data->descr,
					__(':count :(день|дня|дней)', array(':count' => $item->data->pricelist->dtime)),

					View::factory('cart.quan', array('body' => $form->field('text', NULL, 'q'.$key)->value($item->{Kohana_Cart::PROP_QUANTITY})->not_empty( )->max_length(2).$form->field('submit', 'Ok')))->render( ),
					Model_Goods::format_price($item->{Kohana_Cart::PROP_PRICE}, $item->data->currency),
					
					HTML::anchor(Route::url('cart', array('action' => 'remove', 'id' => $key)), '', array('class' => 'cart-del', 'onclick' => 'return confirm("Удалить деталь из корзины?");')),
				));
			}
			
			$out = $form->render($table->render('basic search-results'));
			
			if ($form->sent( ))
			{
				foreach ($form->result( ) AS $result_key => $result_item)
				{
					$result_key = str_replace('q', '', $result_key);
				
					Cart::instance( )->modify($result_key, array(
						Kohana_Cart::PROP_QUANTITY => $result_item
					));
					
				}
			
				$this->request->redirect($this->request->uri( ));
			}
			
			$message = View::factory('cart.submit.btn', array('message' => ''));
			
			if ( ! User::check( ))
			{
				$message->message = __um('cart', 'need_auth', array(
					':reg' 	=> Route::url('register'),
					':login'	=> Route::url('login'),
				));
			}
			else
			{
				if ( ! acl('main_orders_add'))
				{
					$message->message = __um('cart', 'order_deny');
				}
				else
				{
					$message->show_button = TRUE;
				}
			}
			
			$out .= $message->render( );
			
		}
		else
		{
			$this->_auto_load = FALSE;
		
			$out = '<h2>'.__um('cart', 'empty').'</h2>';
		}
		
		$this->_view_body->extra = View::factory('table', array('body' => $out));
    }
    
	/** save data to cart
	* 
	* @return	integer		new order number
	*/
	abstract protected function _submit( );

    /** Submit cart
     *
     * @return	void
     */
    public function action_submit( )
    {
		$this->_auto_load = FALSE;

		if ( ! acl('main_orders_add'))
		{
			throw new Access_Exception('Permission denied');
		}
		
		// execute order register
		$new_order_num = $this->_submit( );
		Cart::instance()->set_state();

		$cart = Cart::instance( );
		
		$count = count($cart);
		
		$out = '';
    
		if ($count == 0)
		{
			$this->request->redirect('/', 307);
		}
		
		$out = '';
		
		foreach ($cart AS $item)
		{
			$out .= '<tr><td>'.implode('</td><td>', $item->as_array( )).'</td></tr>';
		}

		$header = array( );
		
		// render header :TODO:
// 		foreach (Site::config('cart')->format AS $format_item)
// 		{
// 			$header[] = $format_item['label'];
// 		}

		$out = '<table><tr><th>'.implode('</th><th>', $header).'</th></tr>'.$out.'</table>';
		
		$site_name = Site::config( )->common_display_name;
		
		$mail_body  = <<<HERE
<h2>Заказ на {$site_name} оформлен</h2>
<p>Номер вашего заказа: <b>{$new_order_num}</b></p>
<br>
<p>Данные заказа:{$out}</p>
HERE;
		// заголовок письма
		$mail_subj = $site_name.': заказ оформлен';

		// send email
		Email::factory( )
			->from(Site::config( )->email_from)
			->to(User::get( )->email)
			->subj($mail_subj)
			->text($mail_body)
			->send( );
			
		$mail_body  = <<<HERE
<h2>Заказ на {$site_name} оформлен</h2>
<br>
<p>Данные заказа:{$out}</p>
HERE;
		// заголовок письма
		$mail_subj = $site_name.': Новый заказ';

		// send email
		Email::factory( )
			->from(Site::config( )->email_from)
			->to(explode(',', Site::config('cart')->email_admin))
			->subj($mail_subj)
			->text($mail_body)
			->send( );
			
		Cart::instance( )->clear( );
		
		$this->template->body = View::factory('cart.submit', array('num' => $new_order_num));
		
	}
    
    
	/** Add item with some id to cart
	 *
	 * @return void
	 */
    public function action_add( )
    {
		$this->_auto_load = FALSE;
		
		$data = $this->_data($this->request->param('id'));
		Cart::instance()->set_state();
		
		if ($data !== NULL)
		{
			Cart::instance( )->add($data);
			$this->response->body('Ok');
		}
		else
		{
			$this->response->body(__('error'));
		}
/*		
		$this->response->headers('Expires', date('r', time()-3600)); 
		$this->response->headers('Cache-Control', 'no-store, no-cache, must-revalidate'); 
		$this->response->headers('Cache-Control', 'post-check=0, pre-check=0'); 
		$this->response->headers('Pragma', 'no-cache'); 
		
    	$this->request->redirect($this->_back_page, 307);*/
    }

    /** Clear whole cart or remove item
	 *
	 * @return void
	 */
    public function action_clear( )
    {
		$this->_auto_load = FALSE;
		
    	Cart::instance()->clear( );
    	Cart::instance()->set_state();
    	
    	$referrer = Route::url('cart');
    	
    	if ($this->request->referrer( ) != '')
    	{
			$referrer = $this->request->referrer( );
    	}
    	
		$this->response->headers('Expires', date('r', time()-3600)); 
		$this->response->headers('Cache-Control', 'no-store, no-cache, must-revalidate'); 
		$this->response->headers('Cache-Control', 'post-check=0, pre-check=0'); 
		$this->response->headers('Pragma', 'no-cache'); 
    	$this->request->redirect($referrer, 307);
    }

    /** Clear whole cart or remove item
	 *
	 * @return void
	 */
    public function action_remove( )
    {
		$this->_auto_load = FALSE;
    	
    	$ids = explode(Kohana_Controller_Cart::IDS_SEPARATOR, $this->request->param('id'));
    	if (count($ids) > 0)
    	{
			$result = TRUE;
			foreach ($ids AS $id)
			{
				$result = $result && Cart::instance()->remove($id);
			}
    	}
    	
    	Cart::instance()->set_state();

    	if ($this->request->is_ajax( ) === FALSE)
		{
		
			$this->response->headers('Expires', date('r', time()-3600)); 
			$this->response->headers('Cache-Control', 'no-store, no-cache, must-revalidate'); 
			$this->response->headers('Cache-Control', 'post-check=0, pre-check=0'); 
			$this->response->headers('Pragma', 'no-cache'); 
			$this->request->redirect($this->request->referrer( ), 307);
		}
		else
		{
			$this->_auto_render = FALSE;
			$result = array(
				'result'	=> (int) $result,
				'total'		=> (int) Cart::instance()->total(),
				'total_f'	=> Model_Goods::format_price(Cart::instance()->total(), Model_Currency::current()),
				'count'		=> Cart::instance()->quan(),
			);
		
			$this->response->body(Basic::json_safe_encode($result));
		}
    }

    /** NEED? Work with options of cart or item
	 *
	 * @return void
	 */
    public function action_option( )
    {
		$this->_auto_load = FALSE;
   	
    	$id = (int) $this->request->query('id');
    	$param = $this->request->query('param');
    	$value = $this->request->query('value');

		$result = Cart::instance()->modify($id, array($param => $value));
		Cart::instance()->set_state();
		
    	if ($this->request->is_ajax( ) === FALSE)
		{
			
			$this->response->headers('Expires', date('r', time()-3600)); 
			$this->response->headers('Cache-Control', 'no-store, no-cache, must-revalidate'); 
			$this->response->headers('Cache-Control', 'post-check=0, pre-check=0'); 
			$this->response->headers('Pragma', 'no-cache'); 
			$this->request->redirect(Route::url('cart'), 307);
			
    	}
    	else
    	{
			$this->_auto_render = FALSE;
			$item = Cart::instance()->item($id);
			if ($result === TRUE && $item !== FALSE)
			{
				$item_arr = $item->as_array();
				$item_arr = array(
					'price_f' => Model_Goods::format_price($item_arr[Kohana_Cart::PROP_PRICE], Model_Currency::current()),
					'total_f' => Model_Goods::format_price($item_arr[Kohana_Cart::PROP_TOTAL], Model_Currency::current()),
					Kohana_Cart::PROP_PRICE => $item_arr[Kohana_Cart::PROP_PRICE],
					Kohana_Cart::PROP_QUANTITY => $item_arr[Kohana_Cart::PROP_QUANTITY],
				);
			
				$result = array(
					'result'	=> 1,
					'total'		=> (int) Cart::instance()->total(),
					'total_f'	=> Model_Goods::format_price(Cart::instance()->total(), Model_Currency::current()),
					'item'		=> $item_arr,
					'count'		=> Cart::instance()->quan(),
				);
			}
			else
			{
				$result = array(
					'result'	=> 0,
					'total'		=> (int) Cart::instance()->total(),
					'total_f'	=> Model_Goods::format_price(Cart::instance()->total(), Model_Currency::current()),
					'item'		=> 0,
					'count'		=> Cart::instance()->quan(),
				);
			}
		
			$this->response->body(Basic::json_safe_encode($result));
    	}
    }

	/** Show whole cart
	 *
	 * @return void
	 */
    public function action_include()
    {
    	Cart::instance()->show_cart();
    }
    
    /** print link to cart add action
     *
     * @return 	void
     */
    public function action_href( )
    {
		$this->auto_render = FALSE;
		
		InclStream::instance( )->add('cart.js');
	
		$this->response->body(HTML::anchor(Route::url(Route::name($this->request->route( )), array('action' => 'add', 'id' => $this->request->param('id'))), '', array('class' => 'to-cart')));
    }
}