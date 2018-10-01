<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Shop controller
 *
 **/

class Kohana_Controller_Shop extends Controller_Main
{
	/**
	 * @var boolean		check access rules and load page data from storage
	 */
	protected $_auto_load = TRUE;

	/**
	 * @var boolean		automatic rendering switch (TRUE by default for non-CLI requests) 
	 */
	protected $_auto_render = TRUE;
	
	/**
	 * @var boolean 	use breadcrumbs
	 */
	protected $_process_menu = TRUE;

	/**
	 * @var string 		current URI
	 */
	protected $_uri;
	
	/**
	 * fetch page content, metadata and menu
	 */
    public function before( )
    {
		// define URI of basic page
// 		$this->_uri = Route::url('search');
		
		if ($this->request->action( ) == 'load_models' || $this->request->query('auction_id') !== NULL && $this->request->query('auction_id') != '')
		{
			$this->_auto_load = FALSE;
			$this->_auto_render = FALSE;
		}

		if ($this->_uri === NULL)
		{
			$this->_uri = 'shop';
		}
		
		parent::before( );
		
		$this->_view_body->filter = FALSE;
	}
		
	/** Load models of cars
	 *
	 * @param	string		brand
	 * @return 	void
	 */
	function action_load_models($brand = NULL)
	{
		$brand = isset($brand) ? $brand : $this->request->query('brand');
	
		$models_select = array();
		$models = ORM::factory('car')
					->where('brand', '=', $brand)
					->group_by('model');
		
		foreach ($models->find_all( ) AS $model)
		{
			$models_select[$model->model] = $model->model;
		}
		
		$this->response->body(Basic::json_safe_encode($models_select));
		
		return $models_select;
	}
	
	/** 
	 * translate symcodes to id and load appropriate pages
	 *
	 * @return 	void
	 */
	public function action_translate()
	{
		$category	= $this->request->param('category');
		$id			= $this->request->param('id');
		
		// load category 
		$category_orm = ORM::factory('goods_category')->where('symcode', '=', $category)->find();
		
		if (!$category_orm->loaded())
		{
			$this->action_index( );
		}
		else
		{
			if (isset($id))
			{
				$item_orm = ORM::factory('goods')->where('category_id', '=', $category_orm->id)->where('symcode', '=', $id)->find();
			
				if (!$item_orm->loaded())
				{
					throw new HTTP_Exception_404('Cannot load item '.$id);
				}
				
				// define breadcrumbs
				$this->action_index(FALSE, $category_orm->id, TRUE);
				
				$this->action_details($item_orm->id);
			}
			else
			{
				$this->action_index(FALSE, $category_orm->id);
			}
		}
	}
	
	/** 
	 * Goods details
	 *
	 * @param 	integer	ID
	 * @return 	RETURN
	 */
	public function action_details($id = NULL)
	{
		$this->_view_body->set_filename('goods.details');
	
		$id = (int) (isset($id) ? $id : $this->request->param('id'));
		
		
		$item = ORM::factory('goods', $id);
		
		if ( ! $item->loaded())
		{
// 			throw new HTTP_Exception_404();
		}
		
// 		$cars = array();
// 		
// 		foreach ($item->car->find_all( ) AS $car)
// 		{
// 			if ( ! isset($cars[$car->brand]))
// 			{
// 				$cars[$car->brand] = array();
// 			}
// 		
// 			if ( ! isset($cars[$car->brand][$car->model]))
// 			{
// 				$cars[$car->brand][$car->model] = array();
// 			}
// 			
// 			if ($car->body != '')
// 			{
// 				$cars[$car->brand][$car->model][] = $car->body;
// 			}
// 		}
		
// 		$ico = NULL;
// 		$photos = array();
// 	
// 		if (ff(Site::config( )->goods_preview_dir.$item->id)->exists( ))
// 		{
// 			foreach (ff(Site::config( )->goods_photos_dir.$item->id)->find('*.jpg') AS $photo)
// 			{
// 				if ($photo->exists( ) === FALSE) {
// 					continue;
// 				}
// 				
// 				$photos[] = $photo->url( );
// 			}
// 			
// 			unset($photo_obj);
// 		}

		Breadcrumbs::instance( )->add($this->request->uri(), $item->name);
		
		$this->_settings['title'] = $item->title != '' ? $item->title : $item->name;
		$this->_settings['description'] = $item->description;
		$this->_settings['keywords'] = $item->keywords;
		
		$this->_view_body->name = Editor::factory('Editor_Element_Text', 'goods', 'name')->id($item->id)->wrap_if(acl('shop_goods_manage'))->wrap($item->name);
		$this->_view_body->cart_href = Route::url('cart', array('action' => 'add', 'id' => $item->id));
		$this->_view_body->price = $item->client_price_formatted( );
		$this->_view_body->text = Editor::factory('Editor_Element_CKE_Basic', 'goods', 'name')->id($item->id)->wrap_if(acl('shop_goods_manage'))->wrap($item->text);
// 		$this->_view_body->photos = $photos;
		//$this->_view_body->cars = $cars;
	}
	
	/** Search engine for parts
	 *
	 * @param	boolean		show special only
	 * @param	integer		category ID
	 * @param	boolean		define breadcrumbs and return categories if TRUE
	 * @return 	void
	 */
	function action_index($special_only = FALSE, $id = NULL, $define_cats_only = FALSE)
	{
		$search_query = Model_Goods::code_norm($this->request->param('id'));
		$auction_id = $this->request->query('auction_id');
		$current_category_id = (int) (isset($id) ? $id : $this->request->query('c'));
		
		$use_filter = ($this->request->query('query') !== NULL);
// 		$use_filter = TRUE;
		
		$limit = $this->request->query('limit');
		
		if ($auction_id == '')
		{
			unset($auction_id);
		}

		/* begins loading of categories */
		$menu = $all_categories = $categories = array();
		
		$categories_orm = ORM::factory('goods_category')
			// switch on pagination
			->order_by('path')
			->order_by('name');
			
		$active_category = NULL;
		
		Breadcrumbs::instance( )->remove();
		Breadcrumbs::instance( )->add(Route::url('goods'), $this->_settings['name']);
		
		foreach ($categories_orm->find_all() AS $category)
		{
			if ($current_category_id == 0 && $this->request->query('query') === NULL && $this->request->query('is_special') === NULL && !$special_only)
			{
				$current_category_id = $category->id;
			}
		
			if (isset($menu[$category->parent_id]))
			{
				$menu[$category->parent_id]['children'][$category->id] = array(
					'id' 		=> $category->id,
					'href'		=> Route::url('goods', array('category' => $category->symcode)),
					'name' 		=> $category->name,
					'text' 		=> $category->text,
					'symcode'	=> $category->symcode,
					'act'		=> ($current_category_id == $category->id),
					'children' 	=> array(),
				);
				
				$all_categories[$category->id] = &$menu[$category->parent_id]['children'][$category->id];
				
				
				if ($current_category_id == $category->id)
				{
					$active_category = &$menu[$category->parent_id]['children'][$category->id];
					$menu[$category->parent_id]['act'] = TRUE;
					
					Breadcrumbs::instance( )->add(Route::url('goods', array('category' => $all_categories[$category->parent_id]['symcode'])), $all_categories[$category->parent_id]['name']);
// 					
					Breadcrumbs::instance( )->add(Route::url('goods', array('category' => $all_categories[$category->id]['symcode'])), $all_categories[$category->id]['name']);
				}
				
			}
			elseif (empty($menu[$category->id]))
			{
				$menu[$category->id] = array(
					'id' 		=> $category->id,
					'href'		=> Route::url('goods', array('category' => $category->symcode)),
					'name' 		=> $category->name,
					'text' 		=> $category->text,
					'symcode'	=> $category->symcode,
					'act'		=> ($current_category_id == $category->id),
					'children' 	=> array(),
				);
				
				$all_categories[$category->id] = &$menu[$category->id];
				
				if (($current_category_id == $category->id))
				{
					$active_category = &$menu[$category->id];

					Breadcrumbs::instance( )->add(Route::url('goods', array('category' => $all_categories[$category->id]['symcode'])), $all_categories[$category->id]['name']);
				}
			
			}
		}
		
		if ($define_cats_only)
		{
			return $menu;
		}
		
		/** end loading of categories **/
		
		/* fetch unique brands */
// 		$brands_select = array();
// 		$brands = ORM::factory('car')->group_by('brand');
// 		
// 		foreach ($brands->find_all( ) AS $brand)
// 		{
// 			if ( ! isset($default_brand))
// 			{
// 				$default_brand = $brand->brand;
// 			}
// 			
// 			$brands_select[] = $brand->brand;
// 		}
		
		// init orm
		$orm = ORM::factory('goods');
			
		// show non-cat items in search results only
		if (!$use_filter)
		{
			$orm
				->where('category_id', '!=', 0)
				->order_by('price');
		}
		
		if ($this->request->query('is_special') || $special_only)
		{
			$orm
				->where('is_special', '=', 1);
		}
// 		if ( ! $special_only)
// 		{
			// switch on pagination
			$orm->page(isset($limit) ? (int) $limit : NULL);
// 		}

		if ($active_category !== NULL)
		{
			$active_children = array();
			
			foreach ($active_category['children'] AS $child)
			{
				$active_children[] = $child['id'];
			}
		
			$categories = DB::expr("('".implode("','", array_merge(array($active_category['id']), $active_children))."')");
		
			$orm->where('category_id', 'IN', $categories);
		}

		/* filter settings begins */
		if ($use_filter)
		{
			$filter = Form::factory('filter')->class('asldfk')
						->always_show(TRUE)
						->filter_name('Filter')
						->field('text', __('query'), 'query')->min_length(3)
						->field('hidden', NULL, 'is_special')
						->field('hidden', NULL, 'auction_id')
						->field('hidden', NULL, 'c')->value((int) $current_category_id)
						->field('submit', 'фильтровать')
						->callback('query',
							function($value, $orm)
							{
								if (strlen($value) > 3)
								{
									$orm
// 										->select(array(DB::expr('MATCH `name`, `text`, `producer`, `code` AGAINST ("'.$value.'")'), 'relev'))
// 										->where(DB::expr('MATCH `name`, `text`, `producer`, `code` AGAINST ("'.$value.'")'), '>', 0)
// 										->or_where('name', 'LIKE', DB::expr('"%'.$value.'%"'));
	// 									->order_by('relev', 'desc');
										->where('name', 'LIKE', DB::expr('"%'.$value.'%"'));
										
								}
								
							}, array('orm' => $orm))
						->callback('is_special', function($value, $orm) {
							
							if (strlen($value) > 0)
							{
								$orm
									->where('is_special', '=', 1)
									;
							}

						}, array('orm' => $orm));
						
			$filter->render( );
		}
// 		$this->template->right = $filter;
		/* filter settings ends */
		
		/* data table begins */
		// set up table header 
		$table = Html::factory('table')->header(array(
			__(''),
			__('артикул'),
			__('производитель'),
			__('описание'),
			__('доставка'),
			__('цена'),
			'',
			'',
		));
		
		$res = $orm->find_all( );
		
// 		var_dump($orm->last_query( ));
		
		// table body
		foreach ($res AS $item)
		{
// 			$ico = NULL;
// 			$photo = FALSE;
// 		
// 			if (ff(Site::config( )->goods_preview_dir.$item->id)->exists( ))
// 			{
// 				$photo_obj = ff(Site::config( )->goods_preview_dir.$item->id)->child('*.def.*');
// 				
// 				if ($photo_obj->exists( ))
// 				{
// 					$photo = $photo_obj->url( );
// 				}
// 				
// 				unset($photo_obj);
// 			}
			
			if ($item->symcode == '')
			{
				$item->save();
			}
			
			if ($item->category_id != 0) {
				$item_href = (string) Route::url('goods', array('category' =>  $all_categories[$item->category_id]['symcode'], 'id' => $item->symcode));
			}
			
			$table
				->line(array(
// 					$photo !== FALSE ? Html::factory('anchor')->text("<img src='{$photo}'>")->href($item_href) : '',
					'<b>'.$item->code.'</b>',
					$item->producer,
					$item->category_id != 0 ? Html::factory('anchor')->text((string) $item->name)->href($item_href) : $item->name,
					($item->pricelist->dtime == 0 ? __('в&nbsp;наличии') : __(':count :(день|дня|дней)', array(':count' => $item->pricelist->dtime))),
					$item->client_price_formatted( ),
					View::factory('cart.add', array('id' => $item->id)),
				));
// 				->classes($menu->context(
// 					array(
// 						'id' => $item->id,
// 					)				
// 				));
		}
		
		/* data table ends */
	
		if ($active_category['text'] != '')
		{
			$this->_view_body->body = '<h1>'.$active_category['name'].'</h1>';
			$this->_view_body->body .= $active_category['text'].'<br>';
		}
	
		$this->_view_body->filter = $use_filter ? $filter : FALSE;
		$this->_view_body->total = number_format(ORM::factory('goods')->count_all(), 0, '', ' ');
		$this->_view_body->pagination = $orm->pagination( );
		$this->_view_body->extra = View::factory('categories.menu', array('body' => $table->render('basic search-results'), 'menu' => $menu));

	}
	
	public function after( )
	{
		if ($this->_auto_render && $this->_view_body->filter !== FALSE)
		{
			$this->_view_body->filter->render( );
		}
	
		parent::after( );
	}
}