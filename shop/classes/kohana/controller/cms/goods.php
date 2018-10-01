<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Catalogue controller
 * @package 	Shop
 * @author 		A. St.
 * @date 		26.05.2015
 *
 **/

class Kohana_Controller_Cms_Goods extends Controller_Cms
{
//	const SESS_VAR_RESULT = "cart";

	/**
	 * @var string		name of controller
	 */
	protected $_controller = 'Goods';

	/**
	 * @var string		name of model
	 */
	protected $_model = 'goods';
	
	/**
	 * @var string		basic header
	 */
	protected $_header = 'Каталог товаров';
	
	/**
	 * @var string		header of addition form
	 */
	protected $_header_new = 'Новый товар';
	
	/**
	 * @var string		name of browse privelege
	 */
	protected $_privelege_browse = 'shop_goods_viewing';

	/**
	 * @var string		name of manage privelege
	 */
	protected $_privelege_manage = 'shop_goods_manage';
	


	/** reloaded Kohana Controller::before 
	 * 
	 * @return void
	 */
	public function before( )
	{
		parent::before( );
		
		if ( ! acl($this->_privelege_browse))
		{
			throw new Access_Exception( );
		}
		
		$this->_left_menu = Cms_Submenu::factory( );
		$this->_left_menu
			->text(__u($this->_header))
			->child(__u('list'), Route::url('cms.common', array('controller' => $this->_controller)))->css('cms-list')
			->child(__u('add'), Route::url('cms.common', array('controller' => $this->_controller, 'action' => 'add')))->css('cms-add');
// 			->child(__u('import'), Route::url('cms.common', array('controller' => $this->_controller, 'action' => 'import')))->css('cms-import');
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
		$orm = ORM::factory($this->_model)
			// switch on pagination
			->page( );
			
			
			
				
		/* filter settings begins */
		
			
		$pricelists_select = array();
		
		$pricelists_orm = ORM::factory('pricelist');
							
/*								
							->select_values(FALSE, DB::expr(
							'CONCAT(logo, ", ",
							IF (dtime > 0, CONCAT(dtime, " '.__('days').'"), "'.__('in stock').'"), IF(comment != "", CONCAT(", ", comment), ""))'));
							*/
		foreach ($pricelists_orm->find_all( ) AS $pricelist)
		{
			$pricelists_select[$pricelist->id] = 
				$pricelist->logo
				.', '.__('supplier').' '.$pricelist->supplier->name
				.($pricelist->dtime > 0
				? __(', :dtime :(день|дня|дней)', array(':dtime' => $pricelist->dtime))
				: ', '.__('in stock'))
				.($pricelist->comment != '' ? ', '.$pricelist->comment : '');
		}
	
		$suppliers_select = array( );
		
		/* :TODO: show suppliers which has template */
		$suppliers_orm = ORM::factory('supplier');
		
		foreach ($suppliers_orm->find_all( ) AS $suppliers_item)
		{
			$suppliers_select[$suppliers_item->id] =
				$suppliers_item->name.', '
				.($suppliers_item->markup->value
				? 
					__('markup').' '.$suppliers_item->markup->value
					.'%'
				: __('no markup')
				);
		}			
			

		$categories_select = array( );
		$categories_orm = ORM::factory('goods_category')
							->order_by('path')
							->order_by('name');
		
		foreach ($categories_orm->find_all( ) AS $categories_item)
		{
			$categories_select[$categories_item->id] =
				str_repeat('.....', $categories_item->level).$categories_item->name;
		}			
		
		$filter = Form::factory('filter');
		$filter
					->always_show(TRUE)
					->filter_name('Filter')
					->field('textarea', __('query'), 'query')->min_length(3)
// 					->field('chosen', __('supplier'), 'supplier_id')->empty_option( )->options($suppliers_select)
// 					->field('chosen', __('pricelist'), 'pricelist_id')->empty_option( )->options($pricelists_select)
					->field('chosen', __('category'), 'category_id')->empty_option( )->options($categories_select)
// 					->field('checkbox', __('shown on the index'), 'show_discounts')
					->field('hidden', NULL, 'auction_id')
					->field('chosen', __('sort'), 'sort_by')->options(array(
						'по цене',
						'по цене, по убыванию',
						'по количеству',
						'по количеству, по убыванию',
					), TRUE)
					->field('submit', 'Найти')
					->callback('query', function($value, $orm) {
						
						$value = trim($value);
						
						if (strlen($value) > 3)
						{
							$orm
// 								->select(array(DB::expr('MATCH `name`, `producer`, `code` AGAINST ("'.$value.'")'), 'relev'))
// 								->where(DB::expr('MATCH `name`, `producer`, `code` AGAINST ("'.$value.'")'), '>', 0)
// 								->or_where('name', 'LIKE', DB::expr('"%'.$value.'%"'))
								->where('name', 'LIKE', DB::expr('"%'.$value.'%"'))
// 								->order_by('relev', 'desc')
								;
						}

					}, array('orm' => $orm))
// 					->callback('supplier_id', function($value, $orm) {
// 						
// 						if (strlen($value) > 0)
// 						{
// 							$orm
// 								->where('supplier_id', '=', $value)
// 								;
// 						}
// 
// 					}, array('orm' => $orm))
// 					->callback('pricelist_id', function($value, $orm) {
// 						
// 						if (strlen($value) > 0)
// 						{
// 							$orm
// 								->where('pricelist_id', '=', $value)
// 								;
// 						}
// 					}, array('orm' => $orm))
					->callback('category_id', function($value, $orm) {
						
						if (strlen($value) > 0)
						{
							$orm
								->where('category_id', '=', $value)
								;
						}

					}, array('orm' => $orm))
// 					->callback('show_discounts', function($value, $orm) {
// 					
// 						if ($value === TRUE)
// 						{
// 							$orm->where('is_special', '=', 1);
// 						}
// 
// 					}, array('orm' => $orm))
					
// 					->callback('show_discounts', function($value, $orm) {
// 					
// 						if ($value === TRUE)
// 						{
// 							$orm->where('is_special', '=', 1);
// 						}
// 
// 					}, array('orm' => $orm))
// 					
// 					->callback('sort_by', function($value, $orm) {
// 					
// 						switch ($value)
// 						{
// 							case 'по цене':
// 								$orm->order_by('price');
// 								break;
// 							case 'по цене, по убыванию':
// 								$orm->order_by('price', 'desc');
// 								break;
// 							case 'по количеству':
// 								$orm->order_by('quantity');
// 								break;
// 							case 'по количеству, по убыванию':
// 								$orm->order_by('quantity', 'desc');
// 								break;
// 						}
// 
// 					}, array('orm' => $orm))
					
					->render( );
					
		$this->template->right = $filter;
		/* filter settings ends */
		
		
		
		/* context menu declaration begins */
		$menu = Menu_Context::factory( )->id('goods');
		
		$menu
			->child(__('edit'), Route::url('cms.common', array('controller' => $this->_controller, 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'edit', 'id'=>':id')), 'edit')
				->dbl( )		
			->child(__('images'), Route::url('cms.common', array('controller' => 'Filebrowser',  'mode'=>CMS::VIEW_MODE_SIMPLE), array('file'=>':imgdir', 'create_new' => 1, 'chroot' => ':chrootimg', 'show_hidden' => 0, 'ext_filter' => 'jpg|gif|png|jpeg')), 'images')
				->window("popup")
			->child(__('files'), Route::url('cms.common', array('controller' => 'Filebrowser',  'mode'=>CMS::VIEW_MODE_SIMPLE), array('file'=>':files', 'create_new' => 1, 'chroot' => ':chrootfiles', 'show_hidden' => 0)), 'files')
				->window("popup")
			->child(__('set category'), Route::url(
				'cms.common',
				array('controller' => 'goods_categories', 'mode'=>CMS::VIEW_MODE_SIMPLE, 'action'=>'select', 'id'=>':id'),
				array('h' =>
					Route::url('cms.common', array('controller' => 'goods', 'mode'=>CMS::VIEW_MODE_SIMPLE, 'action'=>'set_category', 'id'=>':id')))
				),
				'set_cat')
				->multiple()
				->window('popup')
			->child(__('delete'), Route::url('cms.common', array('controller' => $this->_controller, 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'delete', 'id'=>':id')), 'delete')
				->multiple( )
				->confirm( )
				->ajax( );
			
		$menu->render( );
		/* context menu declaration ends */
		
		
		
		
		
		/* data table begins */
		// set up table header 
		$table = Html::factory('table')->header(array(
			__('#'),
			array(__('name'), __('category')),
			__('producer'),
			__('code'),
			__('descr'),
			__('quantity'),
			__('income price'),
			__('client price'),
			__('поставщик'),
			__('позиция'),
		));
		
		// root directory for catalog files
		$catalog_files_dir = ff(Site::config('shop')->catalog_files_dir);
		
		if ($catalog_files_dir->exists() === FALSE) 
		{
			$catalog_files_dir->driver("File_Directory");
			$catalog_files_dir->create();
		}
		
		// table body
		foreach ($orm->find_all( ) AS $item)
		{
			$file_path = $catalog_files_dir->child($item->id.'/')->driver("File_Directory")->child(Site::config('shop')->catalog_item_files_dir_name)->path(FALSE);
			$img_path = $catalog_files_dir->child($item->id.'/')->driver("File_Directory")->child(Site::config('shop')->catalog_item_img_dir_name)->path(FALSE);
		
			$table
				->line(array(
					$item->id,
					array($item->name, $item->category->name),
					$item->producer,
					$item->code,
					$item->descr,
					array($item->quantity, (boolean) $item->use_quantity ? '&#10004; '.__('quantity management') : ''),
					$item->price,
					$item->price,
					$item->supplier->name,
					Editor::factory('Editor_Element_Text', $this->_model, 'seq')->id($item->id)->wrap($item->seq)
				))
				->classes($menu->context(
					array(
						'id' => $item->id,
						'files' => $file_path,
						'chrootfiles' => $file_path,
						'imgdir' => $img_path,
						'chrootimg' => $img_path,
					)				
				));
		}
		
		$this->template->body = $orm->pagination( ).$table->render("cms-table").$orm->pagination( );
		/* data table ends */
		
		$this->template->header = __u($this->_header);
	}	

	/** 
	 * set category for goods
	 *
	 * @return 	void
	 */
	public function action_set_category()
	{
		if ( ! $this->request->is_ajax( ))
		{
			throw new Access_Exception( );
		}
		
		$this->auto_render = FALSE;
		
		$category_id = (int) $this->request->param('id');
		
		// multiple ids support
		$ids = explode(Site::ID_SEPARATOR, $this->request->query('id'));
		
		foreach ($ids AS $id)
		{
			$orm = ORM::factory('goods', $id)
					->values(array('category_id' => $category_id))
					->save();
		}
		
		if ($this->request->referrer( ) !== NULL)
		{
			$body = Request::factory(str_replace(URL::site(NULL, 'http'), '', $this->request->referrer( )))->execute( )->body( );
			
			$this->response->body(Basic::json_safe_encode(array(
				'body' => $body->body,
			)));
		}}
	
	/** Action: edit
	 *  Edit template
	 *
	 * @return void
	 */
	public function action_edit( )
	{
		if ( ! acl($this->_privelege_manage))
		{
			throw new Access_Exception( );
		}
		
		$id = (int) $this->request->param('id');

		$form = Form::factory( )
				->show_on_success(TRUE)
				->clear_on_success(FALSE);
		
		if ($id != 0)
		{
			$orm = ORM::factory($this->_model, $id);
			
			if ( ! $orm->loaded( ))
			{
				throw new Shop_Exception('Cannot load text #:id', array(':id' => $id));
			}
			
			$form->defaults($orm->as_array( ));
		}
		else
		{
			$orm = ORM::factory($this->_model);
		}
		
		$categories_select = array( );
		$categories_orm = ORM::factory('goods_category')
							->order_by('path')
							->order_by('name');
		
		foreach ($categories_orm->find_all( ) AS $categories_item)
		{
			$categories_select[$categories_item->id] =
				str_repeat('.....', $categories_item->level).$categories_item->name;
		}			

		
		$columns = $orm->table_columns( );
				
		$form
			->message(__u('data has been saved successfuly'))
			->field('text', __('name'), 'name')
			->field('chosen', __('category'), 'category_id')->options($categories_select)->not_empty()
			->field('editor_basic', __('text'), 'text')
			->field('text', __('title'), 'title')->max_length(255)
			->field('textarea', __('meta.description'), 'description')->max_length(255)
			->field('textarea', __('meta.keywords'), 'keywords')->max_length(255)
			->field('text', __('price').($orm->currency ? ', '.$orm->currency : ''), 'price')
				->not_empty( )
				->max_length(10)
			->field('checkbox', __('define quantity'), 'use_quantity')->rel('use_quantity')->checked()
			->field('text', __('quantity'), 'quantity')->value(0)
				->not_empty()
				->rule('digit')
				->max_length(6)
				->beh('use_quantity')->action('show')
			->field('submit', __('save'));
				
		$form->render( );
		
		if ($form->sent( ))
		{
			$data = $form->result( )->as_array( );
			$data['use_quantity'] = (int) isset($data['use_quantity']);
			
			$orm->values($data)->save( );
		}
	
		$this->template->parent = __u($this->_header);
		$this->template->parent_href = Route::url('cms.common', array('controller' => $this->_controller));
		$this->template->header = __u(':name', array(':name' => $orm->name));
		$this->template->body = $form;	
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
			$orm = ORM::factory($this->_model, $id);
			
			if ($orm->loaded( ))
			{
				$orm->delete( );
			}
		}
		
		$body = Request::factory(Route::url('cms.common', array('controller' => $this->_controller)))->execute( )->body( );
		
		$this->response->body(Basic::json_safe_encode(array(
			'body' => $body->body,
		)));
	}	
	
	
	/** Action: add
	 *  add new template
	 *
	 * @return void
	 */
	public function action_add( )
	{
		$this->action_edit( );
	
		$this->template->header = __u($this->_header_new);
	}
	
	/** Import goods list
	 *
	 * @param mixed Form
	 *
	 * @return mixed Result
	 */
	public function action_import()
	{
		if ( ! acl($this->_privelege_browse))
		{
			throw new Access_Exception('Access denied');
		}
		
		if ( ! acl('files_upload'))
		{
			throw new Access_Exception(__u('you can not upload files: permission denied'));
		}

		$this->template->header = 'Импорт товаров';
		$this->template->parent = __('goods database');
		$this->template->parent_href = Route::url('cms.common', array('controller' => $this->_controller));
			
		$chain = 'goods_import';
				
		if (($cli = Cli::check_chain($chain)) === FALSE) 
		{
			unset($cli);
		
			$pricelist_id = (int) $this->request->query('pricelist_id');
		
			$pricelists_select = array();
			
			$pricelists_orm = ORM::factory('pricelist');
								
/*								
								->select_values(FALSE, DB::expr(
								'CONCAT(logo, ", ",
								IF (dtime > 0, CONCAT(dtime, " '.__('days').'"), "'.__('in stock').'"), IF(comment != "", CONCAT(", ", comment), ""))'));
								*/
			foreach ($pricelists_orm->find_all( ) AS $pricelist)
			{
				$pricelists_select[$pricelist->id] = 
					$pricelist->logo
					.', '.__('supplier').' '.$pricelist->supplier->name
					.($pricelist->dtime > 0
					? __(', :dtime :(день|дня|дней)', array(':dtime' => $pricelist->dtime))
					: ', '.__('in stock'))
					.($pricelist->comment != '' ? ', '.$pricelist->comment : '');
			}
		
			
		
			$form = Form::factory()
					->show_on_success(FALSE);
			if ($pricelist_id != 0)
			{
				$form->defaults(array(
					'add_pricelist' => 1,
					'pricelist_id' => $pricelist_id,
				));
			}
			
			$form
				->field('file', __u('file'), 'files')	// :FIXME:      file_ext don't work with rules properly
					->not_empty( )
					->rule('Upload_Type', array('allowed'=>'["csv","txt","xls","xlsx"]'))
					->upload_dir(Site::config('site')->upload_dir);
						
			if (count($pricelists_select) != 0)
			{
				$form
					->field('radio', NULL, 'add_pricelist')->value(0)->options(array( __('создать новый прайс-лист'), __('выбрать существующий'), ))
						->rel('add_pricelist')->equals(0);
			}
			else
			{
				$form
					->field('hidden', NULL, 'add_pricelist')->value(0)
						->rel('add_pricelist')->equals(0);
			}
			
			$suppliers_select = array( );
			
			/* :TODO: show suppliers which has template */
			$suppliers_orm = ORM::factory('supplier');
			
			foreach ($suppliers_orm->find_all( ) AS $suppliers_item)
			{
				$suppliers_select[$suppliers_item->id] =
					$suppliers_item->name.', '
					.($suppliers_item->markup->value
					? 
						__('markup').' '.$suppliers_item->markup->value
						.'%'
					: __('no markup')
					);
			}
			
			
			$form
				->field('select', __u('pricelist'), 'pricelist_id')
					->options(
						$pricelists_select
					)
					->not_empty( )
					->beh('!add_pricelist')->action('show')
				->field('select', NULL, 'mode')->value(0)->options(array(
					__('обновить цены, добавить отсутствующие'),
					__('добавить к существующим товарам'),
					__('удалить существующие товары'),
				))
				->not_empty( )
				->beh('!add_pricelist')->action('show')
				
				->field('text', __u('label for pricelist'), 'logo')
					->not_empty( )
					->max_length(4)
					->beh('add_pricelist')->action('show')
				->field('select', __u('supplier'), 'supplier_id')
					->options(
						$suppliers_select
					)
					->beh('add_pricelist')->action('show')
				->field('checkbox', __('in stock'), 'in_stock')->selected(TRUE)
					->rel('in_stock')->checked( )
					->beh('add_pricelist')->action('show')
				->field('text', __u('delivery time'), 'dtime')
					->not_empty( )
					->max_length(2)
					->rule('numeric')
					->unit(__('days'))
					->beh('!in_stock')->action('show')
				->field('checkbox', __('use markup for supplier'), 'use_supplier_markup')->selected(TRUE)
					->rel('supplier_markup')->checked( )
					->beh('add_pricelist')->action('show')
				->field('text', __u('markup for pricelist'), 'markup')
					->unit('%')
					->not_empty( )
					->rule('numeric')
					->max_length(3)
					->beh('!supplier_markup')->action('show')
				->field('submit', __u('upload'))
				->render( );
			$this->template->body .= $form;
		}
		
		if (isset($form) && $form->sent( ))
		{

			$form_arr = $form->result( )->as_array();
			
			// if creation of pricelist has been queried 
			if ($form_arr['add_pricelist'] == 0)
			{
				$pricelist_data = array(
					'supplier_id'	=> $form_arr['supplier_id'],
					'logo'		 	=> $form_arr['logo'],
					'dtime'		 	=> isset($form_arr['in_stock']) ? 0 : $form_arr['dtime'],
				);
				
				if (empty($form_arr['use_supplier_markup']))
				{
					$pricelist_data['markup'] = $form_arr['markup'];
				}
				else
				{
					$pricelist_data['use_supplier_markup'] = 1;
				}
			
				$pricelist_id = ORM::factory('pricelist')
					->values($pricelist_data)
					->save()
					->id;
			}
			else
			{
				$pricelist_id = $form_arr['pricelist_id'];
			}
			
			$proc = Cli::factory( )
				->chain($chain)
				->name('Импорт товаров: '. ff($form_arr['files'])->name( ))
				->param(array(
					'file' 		=> $form_arr['files'],
					'mode' 		=> 	empty($form_arr['mode']) ? 2 : $form_arr['mode'],
					'pricelist'	=> $pricelist_id,
				))
				->task(Route::url('cms.common', array('controller' => $this->_controller, 'action' => 'parse')))
				->save();
			
			sleep(2);	// :TODO: Dummy workaround for chain_exec write progress
		}

		// execute chain and write loader to body
		if ( ! isset($cli))
		{
			$cli = Cli::chain_exec($chain);
		}
		
		if ($cli !== NULL)
		{
			$this->template->body .= $cli->html( );
		}
		
	}	

	
	/** Parse imported goods list
	 * @param	string	File name
	 * @param	bool	Remove success parsed file
	 *
	 * @return	int	Return code
	 */
	public function action_parse()
	{
		$args =  Security::xss_clean(CLI::options('file', 'remove', 'template', 'supplier', 'pricelist', 'mode'));
		$fname = $args['file'];
		$mode = $args['mode'];
		
		$pricelist	= ORM::factory('pricelist', (int) $args['pricelist']);
		$supplier	= $pricelist->supplier;
		$template	= $supplier->templates->find( );
		
		if ( ! $supplier->loaded( ) || ! $template->loaded( ))
		{
			throw new Shop_Exception('Cannot start parsing: supplier or template of pricelist not loaded');
		}
		
		if ($mode == 2)
		{
			// set state
			Cli::instance( )
				->status(__('starting database clear'))
				->save( );
				
			ORM::factory('goods')->clear_pricelist((int) $args['pricelist']);

			Cli::instance( )
				->status(__('database clear done'))
				->save( );
				
		}
		
		$step = 50;
		$i = 0;
		
		sleep(2);
		
		Cli::instance( )
			->status(__('starting import'))
			->save( );
		
		switch (pathinfo($fname, PATHINFO_EXTENSION)) 
		{
			case 'xls': 
			case 'xlsx': 
			
				$orm = ORM::factory('goods');
				foreach (ff($fname)->begin_line($template->begin_line) AS $line => $data)
				{
					if ($data->getCellByColumnAndRow($template->code-1, $line)->getValue( ) == '')
					{
						continue;
					}
				
					if ($mode == 0)
					{
						$orm
							->where('supplier_id', '=', $supplier->id)
							->where('pricelist_id', '=', $pricelist->id)
							->where('producer', '=', $data->getCellByColumnAndRow($template->producer-1, $line)->getValue( ))
							->where('code', '=', $data->getCellByColumnAndRow($template->code-1, $line)->getValue( ))
							->find( );
					}
				
					$data_arr = array(
						'producer'		=> $data->getCellByColumnAndRow($template->producer-1, $line)->getValue( ),
						'code'			=> Model_Goods::code_norm($data->getCellByColumnAndRow($template->code-1, $line)->getValue( )),
						'descr'			=> $template->descr != '' ? $data->getCellByColumnAndRow($template->descr-1, $line)->getValue( ) : '',
						'price'			=> $data->getCellByColumnAndRow($template->price-1, $line)->getValue( ),
						'quantity'		=> $template->quan != '' ? $data->getCellByColumnAndRow($template->quan-1, $line)->getValue( ) : '',
						'dtime'			=> $template->dtime != '' ? $data->getCellByColumnAndRow($template->dtime-1, $line)->getValue( ) : $pricelist->dtime,
						'currency'   	=> $template->currency != '' ? $data->getCellByColumnAndRow($template->currency-1, $line)->getValue( ) : $template->currency_global,
						'pricelist_id'  => $pricelist->id,
						'supplier_id'   => $supplier->id,
					);
				
					$orm
						->values($data_arr)
						->save();
					
					$orm->clear( );
					
					echo Model_Goods::code_norm($data->getCellByColumnAndRow($template->code-1, $line)->getValue( ));
					echo "\n";
					ob_flush( );
					
					// set progress
					if (++$i % $step == 0)
					{
						Cli::instance( )
							->status(Cli::STATUS_PROCESS)
// 							->comment()
							->processed($i)
// 							->progress($this->_progress( ))
							->save( );
					}
				}
				
				unset($data_arr);
			break;
			case 'csv':
			case 'txt':
						Kohana::$log->add(
			Log::INFO,
			$fname,
			array(
			)
		);
;
				$orm = ORM::factory('goods');
				foreach (ff($fname) AS $line => $data)
				{
					$data = explode($template->separator, $data);
					
					if ( ! isset($data[$template->code]) || $data[$template->code] == '')
					{
						continue;
					}

					if ($mode == 0)
					{
						$orm
							->where('supplier_id', '=', $supplier->id)
							->where('pricelist_id', '=', $pricelist->id)
							->where('producer', '=', $data[$template->producer])
							->where('code', '=', $data[$template->code])
							->find( );
					}
					
					foreach ($data AS &$value)
					{
						$value = trim($value, $template->delimiter);
					}
				
					$data_arr = array(
						'producer'		=> $data[$template->producer],
						'code'			=> Model_Goods::code_norm($data[$template->code]),
						'descr'			=> $template->descr != '' ? $data[$template->descr]: '',
						'price'			=> $data[$template->price],
						'quantity'		=> $template->quan != '' ? $data[$template->quan] : '',
						'dtime'			=> $template->dtime != '' ? $data[$template->dtime] : $pricelist->dtime,
						'currency'   	=> $template->currency != '' ? $data[$template->currency] : $template->currency_global,
						'supplier_id'   => $supplier->id,
						'pricelist_id'  => $pricelist->id,
					);
				
					$orm
						->values($data_arr)
						->save();
					
					$orm->clear( );
					
					// set progress
					if (++$i % $step == 0)
					{
						Cli::instance( )
							->status(Cli::STATUS_PROCESS)
// 							->comment()
							->processed($i)
// 							->progress($this->_progress( ))
							->save( );
					}
				}
			/*
				$parser = Parser::factory('txt')->id(Controller_CMS_Goods::PARSER_NAME)->file($fname)->format( )
					->separator($template->separator)
					->delimiter($template->delimiter)
					->begin_line($template->begin_line)
						->col($template->producer, 'producer')
						->col($template->code, 'code')
						->col($template->descr, 'descr')
						->col($template->price, 'price')
						->col($template->quan, 'quan')
					->execute( );
					
				$orm = ORM::factory('goods');
				
				foreach ($parser AS $data)
				{
					$data = $data->as_array( );
					
					if (isset($data['price']))
					{
						$data['price'] 			= str_replace(',', '.', $data['price']);
						$data['descr'] 			= iconv('CP1251', 'UTF-8', $data['descr']);
						$data['supplier_id'] 	= $supplier->id;
						$data['logo'] 			= $logo;

						$orm->values($data)->save();
						
						$orm->clear( );
					}
				}
					*/
			break;
		}
		
		Cli::instance( )
			->status(Cli::STATUS_PROCESS)
			->processed($i)
			->progress(100)
			->save( );
		
		// save count of goods
		$pricelist->values(array('count' => $pricelist->count+$i))->save( );
		
		// Remove file after import
		ff($fname)->remove();
	}
	
}