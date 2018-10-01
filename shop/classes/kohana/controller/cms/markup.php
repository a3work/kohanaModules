<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Markup controller
 * @package 	Shop
 * @author 		A. St.
 * @date 		
 *
 **/

class Kohana_Controller_Cms_Markup extends Controller_Cms
{
	/** reloaded Kohana Controller::before 
	 * 
	 * @return void
	 */
	public function before( )
	{
		parent::before( );
		
		if ( ! acl('shop_markup_viewing'))
		{
			throw new Access_Exception( );
		}
		
		$this->_left_menu = Cms_Submenu::factory( );
		$this->_left_menu
			->text(__u('markup table'))
			->child(__u('list'), Route::url('cms.common', array('controller' => 'Markup')))->css('cms-list');
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
		$orm = ORM::factory('markup')
			// switch on pagination
			->page( );
			
		$pricelists_select = array();
		
		$pricelists_orm = ORM::factory('pricelist');
							
		foreach ($pricelists_orm->find_all( ) AS $pricelist)
		{
			$pricelists_select[$pricelist->id] = 
				$pricelist->supplier->name
				.', '.$pricelist->logo
				.($pricelist->dtime > 0
				? __(', :dtime :(день|дня|дней)', array(':dtime' => $pricelist->dtime))
				: __('in stock'))
				.($pricelist->comment != '' ? ', '.$pricelist->comment : '');
		}
			
		
		
		$suppliers_select = array( );
		
		$suppliers_orm = ORM::factory('supplier');
		
		foreach ($suppliers_orm->find_all( ) AS $suppliers_item)
		{
			$suppliers_select[$suppliers_item->id] = $suppliers_item->name;
		}
		/* end of fetching values for inputs */
				
		/* filter settings begins */
		$filter = Form::factory('filter')
					->always_show(TRUE)
					->filter_name('Filter')
					->field('chosen', __('supplier'), 'supplier_id')->empty_option( )
						->options($suppliers_select)
					->field('chosen', __('pricelist'), 'pricelist_id')->empty_option( )
						->options($pricelists_select)
					->field('submit', 'Найти')
					->callback('supplier_id', function($value, $orm) {
						
						$orm
							->where('supplier_id', '=', $value);

					}, array('orm' => $orm))
					->callback('pricelist_id', function($value, $orm) {

						$orm
							->where('pricelist_id', '=', $value);

					}, array('orm' => $orm))
					->render( );
					
		$this->template->right = $filter;
		/* filter settings ends */
		
		
		
		/* context menu declaration begins */
		$menu = Menu_Context::factory( )->id('markup');
		
		$menu
			->child(__('delete'), Route::url('cms.common', array('controller' => 'Markup', 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'delete', 'id'=>':id')), 'delete')
				->multiple( )
				->confirm( )
				->ajax( );
			
		$menu->render( );
		/* context menu declaration ends */
		
		
		
		
		
		/* data table begins */
		// set up table header 
		$table = Html::factory('table')->header(array(
			__('#'),
			__('supplier'),
			__('pricelist'),
			__('markup'),
		));
		
		// table body
		foreach ($orm->find_all( ) AS $item)
		{
			$table
				->line(array(
					$item->id,
					$item->supplier->name ? Html::factory('anchor')->text($item->supplier->name)->href(Route::url('cms.common', array('controller' => 'Suppliers', 'action' => 'edit', 'id' => $item->supplier->id))) : '&mdash;',
					$item->pricelist->logo ? Html::factory('anchor')->text($item->pricelist->logo)->href(Route::url('cms.common', array('controller' => 'Pricelists', 'action' => 'edit', 'id' => $item->pricelist->id))) : '&mdash;',
					Editor::factory('Editor_Element_Digit', 'markup', 'value')->wrap_if(acl('shop_markup_manage'))->id($item->id)->wrap($item->value).'%',
				))
				->classes($menu->context(
					array(
						'id' => $item->id,
					)				
				));
		}
		
		
		$this->template->body = $orm->pagination( ).$table->render( ).$orm->pagination( );
		/* data table ends */
		
		$this->template->header = __u('markup table');
	}	

	/** Action: delete
	 *  Delete template
	 *
	 * @return void
	 */
	public function action_delete( )
	{
		if ( ! $this->request->is_ajax( ))
		{
			throw new Access_Exception( );
		}
		
		$this->auto_render = FALSE;
		
		// multiple ids support
		$ids = explode(Site::ID_SEPARATOR, $this->request->param('id'));
		
		foreach ($ids AS $id)
		{
			$orm = ORM::factory('markup', $id);
			
			if ($orm->loaded( ))
			{
				$orm->delete( );
			}
		}
		
		$body = Request::factory(Route::url('cms.common', array('controller' => 'Markup')))->execute( )->body( );
		
		$this->response->body(Basic::json_safe_encode(array(
			'body' => $body->body,
		)));
	}	
}