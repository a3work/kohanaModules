<?php defined('SYSPATH') or die('No direct script access.');

abstract class Kohana_Controller_Cms_Cart extends Controller_Cms {
 
	// config file name
	protected $_config = 'cms_cart';
	
	public function before( )
	{
		if ( ! acl('shop_orders_add'))
		{
			throw new Access_Exception( );
		}
			
		InclStream::instance( )->add('cart.css');
		
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
	
	/** Modify cart table row content
	 * 
	 * @param	array	row data
	 * @return	array	modified row data
	 */
	protected function _line_out($item)
	{
		return $item;
	}
 
    /** Show cart
     *
     * @return	void
     */
    public function action_index( )
    {
		$cart = Cms_Cart::instance( );
		
		$count = count($cart);
		
		$out = '';
    
		if ($count > 0)
		{
			$form = Form::factory( )
					->return_changes(TRUE);
		
			// render header 
			$header = array( );
			foreach (Site::config($this->_config)->format AS $format_key => $format_item)
			{
				// don't show parameters with empty label
				if (empty($format_item['label']))
				{
					continue;
				}
			
				$header[$format_key] = $format_item['label'];
			}
			
			// actions column
			$header[] = '';
			
			$table = Html::factory('table')->header($header);
		
			foreach ($cart AS $key => $item)
			{
				$item = $item->as_array( );
				$row = array( );
				
				$item[Site::config($this->_config)->prop_quantity] = View::factory('cart.quan', array('body' => $form->field('text', NULL, 'q'.$key)->value($item[Site::config($this->_config)->prop_quantity])->not_empty( )->max_length(2).$form->field('submit', 'Ok')))->render( );
				
				$item[Site::config($this->_config)->prop_price] = str_replace(' ', '&nbsp;', number_format($item[Site::config($this->_config)->prop_price], 2, ',', $item[Site::config($this->_config)->prop_price] > 10000 ? ' ' : ''));
			
				$item[Site::config($this->_config)->prop_reference] = View::factory('cart.quan', array('body' =>
					$form->field('text', NULL, 'r'.$key)->value($item[Site::config($this->_config)->prop_reference])
					.$form->field('submit', 'Ok')
				))->render( );
				
				$item = $this->_line_out($item);
				
				foreach (Site::config($this->_config)->format AS $format_key => $format_item)
				{
					// don't show parameters with empty label
					if (empty($format_item['label']))
					{
						continue;
					}
					$row[] = $item[$format_key];
				
				}
				
				$row['add_href'] = HTML::anchor(Route::url('cms_cart', array('action' => 'remove', 'id' => $key)), '', array('class' => 'cms-del'));
				
				
				$table->line($row);
			}
			
			unset($row);
			
			$out = $form->render($table->render('full'));
			
			if ($form->sent( ))
			{
				foreach ($form->result( ) AS $result_key => $result_item)
				{
					if (preg_match("/^q(\d+)/", $result_key, $q_matches))
					{
						Cms_Cart::instance( )->modify($q_matches[1], array(
							Site::config($this->_config)->prop_quantity => $result_item
						));
					}
					if (preg_match("/^r(\d+)/", $result_key, $r_matches))
					{
						Cms_Cart::instance( )->modify($r_matches[1], array(
							Site::config($this->_config)->prop_reference => $result_item
						));
					}
				}
			
				$this->request->redirect($this->request->uri( ));
			}
			
			$message = View::factory('cart.submit.btn', array('show_button' => TRUE, 'message' => ''));
			
			$out .= $message->render( );
			
		}
		else
		{
			$this->_auto_load = FALSE;
		
			$out = __um('cart', 'empty');
		}
		
		$this->template->header = __u('cart');
		$this->template->body .= $out;
    }
    
	/** save data to cart
	* 
	* @param	integer 	user id
	* @return	integer		new order number
	*/
	abstract protected function _submit($uid);

    /** Submit cart
     *
     * @return	void
     */
    public function action_submit( )
    {
		$this->_auto_load = FALSE;

		$cart = Cms_Cart::instance( );
		
		$count = count($cart);
		
		$out = '';
    
		if ($count == 0)
		{
			$this->request->redirect(Route::url('cms_cart'), 307);
		}
		
		$out = '';
		
		/* render tables for every email */
		$tables = array( );
		
		// render header 
		$header = $hidden_keys = array( );
		foreach (Site::config($this->_config)->format AS $format_key => $format_item)
		{
			// don't show parameters with empty label
			if (empty($format_item['label']))
			{
				$hidden_keys[] = $format_key;
				continue;
			}
		
			$header[$format_key] = $format_item['label'];
		}
		
		// render body
		foreach ($cart AS $item)
		{
			$item = $item->as_array( );
			
			$uid = $item['uid'];
			
			if (empty($tables[$uid]))
			{
				$tables[$uid] = Html::factory('table')->header($header);
			}
			
			$item[Site::config($this->_config)->prop_price] = Pricing::format($item[Site::config($this->_config)->prop_price]);
		
			$item = $this->_line_out($item);
			
			foreach ($hidden_keys AS $hidden_key)
			{
				unset($item[$hidden_key]);
			}
			
			$tables[$uid]->line($item);
		}

		/** send mail to every client **/
		foreach ($tables AS $uid => $out)
		{
			$out = $out->render( );
			
			// execute order register and fetch order number
			$new_order_num = $this->_submit($uid);
			
			$site_name = Site::config( )->common_display_name;
			
			/* :FIXME: don't send email */
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
				->to(User::load((int) $uid)->email)
				->subj($mail_subj)
				->text($mail_body)
				->send( );
				
			$orders[] = array(
				'user'	=> User::load((int) $uid),
				'num'	=> $new_order_num,
			);
			
			Kohana::$log->add(
				Log::INFO,
				"order #:id has been added",
				array(
					':id' => $new_order_num,
				)
			);

		}
		
		Cms_Cart::instance( )->clear( );

		$this->template->header = __u('orders has been registered');
		$this->template->body = View::factory('cart.cms.submit', array('orders' => $orders));
	}
    
    

	/** Add item with some id to cart
	 *
	 * @return void
	 */
    public function action_add( )
    {
		$this->_auto_load = FALSE;
		
		if (($data = $this->_data($this->request->param('id'))) !== NULL)
		{
			Cms_Cart::instance( )->add($data);
		}
		
		$this->response->headers('Expires', date('r', time()-3600)); 
		$this->response->headers('Cache-Control', 'no-store, no-cache, must-revalidate'); 
		$this->response->headers('Cache-Control', 'post-check=0, pre-check=0'); 
		$this->response->headers('Pragma', 'no-cache'); 
		
//     	$this->request->redirect($this->_back_page, 307);
    }
    
    /** Clear whole cart or remove item
	 *
	 * @return void
	 */
    public function action_clear( )
    {
		$this->_auto_load = FALSE;
		
    	Cms_Cart::instance()->clear( );

    	$referrer = Route::url('cms_cart');
    	
    	if ($this->request->referrer( ) != '')
    	{
			$referrer = $this->request->referrer( );
    	}
    	
    	$this->request->redirect($referrer, 307);
    }

    /** Clear whole cart or remove item
	 *
	 * @return void
	 */
    public function action_remove( )
    {
		$this->_auto_load = FALSE;
		
    	Cms_Cart::instance()->remove($this->request->param('id'));

		$this->response->headers('Expires', date('r', time()-3600)); 
		$this->response->headers('Cache-Control', 'no-store, no-cache, must-revalidate'); 
		$this->response->headers('Cache-Control', 'post-check=0, pre-check=0'); 
		$this->response->headers('Pragma', 'no-cache'); 
    	$this->request->redirect($this->request->referrer( ), 307);
    }

    /** NEED? Work with options of cart or item
	 *
	 * @return void
	 */
    public function action_option( )
    {
		$this->_auto_load = FALSE;
		
    	$id = $this->request->param('id');
    	$param = $this->request->param('param');
    	$value = $this->request->param('value');
    	
    	Cms_Cart::instance()->option($param, $value, $id);
		$this->response->headers('Expires', date('r', time()-3600)); 
		$this->response->headers('Cache-Control', 'no-store, no-cache, must-revalidate'); 
		$this->response->headers('Cache-Control', 'post-check=0, pre-check=0'); 
		$this->response->headers('Pragma', 'no-cache'); 
    	$this->request->redirect(Route::url('cart'), 307);
    }

	/** Show whole cart
	 *
	 * @return void
	 */
    public function action_include()
    {
    	Cms_Cart::instance()->show_cart();
    }
    
    /** print link to cart add action
     *
     * @return 	void
     */
    public function action_href( )
    {
		$this->auto_render = FALSE;
		
		InclStream::instance( )->add('cart.js');
	
		$href = HTML::factory('anchor')
				->href(Route::url(Route::name($this->request->route( )), array('action' => 'add', 'id' => $this->request->param('id'))))
				->text(__('order it'))
				->title(__('settings of order'))
				->attr('class', 'to-cart')
				->window( );
		
		$this->response->body($href);
    }
    
}