<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		controller of currency
 * @package 	Shop
 * @author 		A. St.
 * @date 		20.04.2015
 *
 **/

class Kohana_Controller_Cms_Currency extends Controller_Cms
{
	/** reloaded Kohana Controller::before 
	 * 
	 * @return void
	 */
	public function before( )
	{
		parent::before( );
		
		if ( ! acl('shop_currency_viewing'))
		{
			throw new Access_Exception(__('access denied: you can not browse rates of currency'));
		}
		
		$this->_left_menu = Cms_Submenu::factory( );
		$this->_left_menu
			->text(__u('rates of currency'))
			->child(__u('list'), Route::url('cms.common', array('controller' => 'Currency')))->css('cms-list')
			->child(__u('load from cbr.ru'), Route::url('cms.common', array('controller' => 'Currency', 'action' => 'load')))->css('cms-clear');
	}

	/** reloaded Kohana Controller::after 
	 * 
	 * @return void
	 */
	public function after( )
	{
		$this->template->left = $this->_left_menu->render( );
	
		parent::after( );
	}

	/** Action: index
	 *  list
	 *
	 * @return void
	 */
	public function action_index( )
	{
		// init orm
		$orm = ORM::factory('currency')
				->order_by('date', 'desc')
				->where('code_char', 'IN', DB::expr('("'.implode('","', Site::config('shop')->available_currency).'")'));
				
		$date = date('Y-m-d 00:00:00');
		$filter = '';
				
		/* filter settings begins */
					
		$orm->where('date', '>=', $date);
					
		$this->template->right = $filter;
		/* filter settings ends */
		
		
		/* data table begins */
		// set up table header 
		$table = Html::factory('table')->header(array_merge(
			array(
				__('date'),
			),
			Site::config('shop')->available_currency
		));
		
		
		// table body
		$res = $orm->find_all( );
		$count = count($res);
		$i = 0;
		$current_date = NULL;
		
		foreach ($res AS $item)
		{
			$out[$item->code_char] = $item->rate;
			
			if ($count == ++$i || $current_date != $item->date)
			{
				if (isset($current_date))
				{
					$table
						->line(array_merge(
							array(Date::format($current_date)),
							$out
						));
				}
				
				$current_date = $item->date;
			}
		}
		
		$this->template->body = $table->render( ).Chart::factory('currency');
		/* data table ends */
		
		$this->template->header = __u('rates of currency');
	}	
	
	
	/** Send request to cbr.ru, fetch answer and load data to DB
	 *
	 * @return 	void
	 */
	public function action_cbr_request( )
	{
		if (CLI::check( ))
		{
			$args = Security::xss_clean(CLI::options('date'));
		}
		
		
		if (empty($args['date']))
		{
			$date = date('Y-m-d');
		}
	
		if (ORM::factory('currency')->where('date', 'LIKE', DB::expr('"'.$date.'%"'))->find( )->loaded( ) === FALSE)
		{
			Cache::instance( )->delete(Kohana_Model_Currency::RATES_CACHE_NAME);
			
// 			for ($i = 1; $i <= 120; $i++)
// 			{
// 				$date = date('Y-m-d', time( )-86400*$i);
// 				
			$cbrf = new SoapClient("http://www.cbr.ru/DailyInfoWebServ/DailyInfo.asmx?wsdl", array('trace' => 1));
			$currency_courses = $cbrf->GetCursOnDate(array("On_date"=>$date))->GetCursOnDateResult->any;
			
			$simple_xml = simplexml_load_string($currency_courses);
			
			foreach($simple_xml->ValuteData->ValuteCursOnDate AS $curr)
			{
				ORM::factory('currency')
					->values(array(
						'code' => $curr->Vcode,
						'code_char' => $curr->VchCode,
						'rate' => $curr->Vcurs,
						'nom' => $curr->Vnom,
						'date' => $date.' 00:00:00',
					))
					->save( )
					->clear( );
			}

// 			}

		}
	}
	
	/** Load rates from cbr.ru
	 *
	 * @return 	void
	 */
	public function action_load( )
	{
		$cli = Cli::factory( )
			->name('loading from cbr.ru')
			->task(Route::url('cms.common', array('controller' => 'Currency', 'action' => 'cbr_request')))
			->exec();
        
		$this->template->body = $cli->html( );
		/* data table ends */
		
		$this->template->parent_header = __u('rates of currency');
		$this->template->parent_link = Route::url('cms.common', array('controller' => 'Currency'));
		$this->template->header = __u('load from cbr.ru');
	}
}