<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_Goods_Category extends ORM {

    protected $_table_name = 'shop_categories';
    
	protected $_has_many = array(
		'item' => array('model' => 'order_item', 'foreign_key' => 'category_id'),
		'children' => array('model' => 'goods_category', 'foreign_key' => 'parent_id'),
	);

	protected $_belongs_to = array(
		'parent' => array('model' => 'goods_category', 'foreign_key' => 'parent_id'),
	);

	/** 
	 * update path and level of children
	 *
	 * @return 	ORM
	 */
	protected function _update_children_path()
	{
		$children_orm = $this->children
// 							->where('path', 'LIKE', DB::expr("'{$orm->path}%'"))
						->order_by('level');
		
		foreach ($children_orm->find_all() AS $child)
		{
			$child->path = '';
			$child->save();
		}
	}
	
	
	/** 
	 * define path and level
	 *
	 * @return 	ORM
	 */
	protected function _define_path()
	{
		$id_length = 9;
	
		$this->path = str_pad($this->id, $id_length, '0', STR_PAD_LEFT);
		$this->symcode =  $this->name != '' ? Basic::tr($this->name) : $this->id;
	
		if ($this->parent_id != 0)
		{
			$parent = ORM::factory('goods_category', $this->parent_id);
		
			if ($parent->loaded())
			{
				$this->path = $parent->path.$this->path;
				$this->symcode = $parent->symcode.'/'.$this->symcode;
			}
		}
		
		$this->level = strlen($this->path)/$id_length;
	}
	
	/** Reload save function: add searchbox
	 *
	 * @param	Validation
	 * @return 	this
	 */
	public function save(Validation $validation = NULL)
	{
		$define_path_later = FALSE;
	
		if ($this->id == 0)
		{
			$define_path_later = TRUE;
		}
		else
		{
			$this->_define_path();
		}
	
		parent::save($validation);
		
		if ($define_path_later)
		{
			$this->_define_path();
			
			parent::save($validation);
		}
		
		$this->_update_children_path();
	}
	
	
}
