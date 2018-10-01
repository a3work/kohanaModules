<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_Pricelist extends ORM
{
	protected $_table_name = 'shop_pricelists';
	
	/**
	 * @var boolean		use supplier markup
	 */
	protected $_use_supplier_markup;

	/**
	 * @var integer		value of markup
	 */
	protected $_markup;

	protected $_has_one = array(
		'discount'	=> array('model' => 'discount', 'foreign_key' => 'pricelist_id'),
		'markup'	=> array('model' => 'markup', 'foreign_key' => 'pricelist_id'),
	);

	protected $_belongs_to = array(
		'supplier'	=> array('model' => 'supplier', 'foreign_key' => 'supplier_id'),
	);
	
	/** Reload delete function: remove markup and discount
	 *
	 * @return 	this
	 */
	public function delete( )
	{
		if ($this->markup->loaded( ))
		{
			$this->markup->delete( );
		}		
		
		return parent::delete( );
	}
	
	/**
	 * Set values from an array with support for one-one relationships.  This method should be used
	 * for loading in post data, etc.
	 *
	 * @param  array $values   Array of column => val
	 * @param  array $expected Array of keys to take from $values
	 * @return ORM
	 */
	public function values(array $values, array $expected = NULL)
	{
		$this->_use_supplier_markup = isset($values['use_supplier_markup']);
		
		if (isset($values['markup']))
		{
			$this->_markup = $values['markup'];
		}
	
		return parent::values($values, $expected);
	}
	
	/** Reload save function: add cleaning of cache
	 *
	 * @param	Validation
	 * @return 	this
	 */
	public function save(Validation $validation = NULL)
	{
		$result = parent::save($validation);
	
		if ( ! $this->_use_supplier_markup)
		{
			if (isset($this->_markup))
			{
				$this->markup
					->values(array(
						'pricelist_id' => $this->id,
						'value' => $this->_markup,
					))
					->save( );
			}
		}
		else
		{
			if ($this->markup->loaded( ))
			{
				$this->markup->delete( );
			}
		}
		
		return $result;
	}
	
	/** Clear goods of current pricelist
	 *
	 * @return 	this
	 */
	public function clear_goods( )
	{
		if ( ! $this->_loaded) return $this;
		
		DB::delete(ORM::factory('goods')->table_name( ))->where('pricelist_id', '=', $this->id)->execute( );
		
		$this->set_count( );
		
		return $this;
	}
	
	/** set count of goods for current pricelist
	 *
	 * @param	integer		count
	 * @return 	this
	 */
	public function set_count($count = NULL)
	{
		if ( ! $this->_loaded) return $this;
		
		$this
			->values(array(
				'count' => $count !== NULL ? $count : ORM::factory('goods')->where('pricelist_id', '=', $this->id)->count_all( ),
			))
			->save( );
		
		return $this;
	}
	
}