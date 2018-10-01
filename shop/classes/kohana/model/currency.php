<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_Currency extends ORM
{
	/**
	 * @var array		rates of current
	 */
	public static $rates;

	/**
	 * @const string	name of rates cache variable
	 */
	const RATES_CACHE_NAME = 'rates';

	protected $_table_name = 'shop_currency';

	/** Get char code of current currency
	 *
	 * @return 	string
	 */
	public static function current( )
	{
		$currency = Session::instance( )->get('currency');
		
		if (empty($currency))
		{
			$currency = Site::config('shop')->default_currency;
			
			Session::instance( )->set('currency', $currency);
		}
		
		return $currency;
	}
	
	/** Fetch rates of specified currency
	 *
	 * @param	string	char code of currency
	 * @return 	array
	 */
	public function _data($currency)
	{
		$currency = mb_strtoupper($currency);
		
		if ($currency == Site::config('shop')->default_currency)
		{
			return (object) array(
				'code'	=> $currency,
				'rate' 	=> 1,
				'count' => 1,
				'date'	=> NULL,
			);
		}
	
	
		if (Kohana::$caching)
		{
			Kohana_Model_Currency::$rates = Cache::instance( )->get(Kohana_Model_Currency::RATES_CACHE_NAME);
		}
		
		/* save discount cache */
		if (empty(Kohana_Model_Currency::$rates))
		{
			Kohana_Model_Currency::$rates = array( );
		}
		
		if (empty(Kohana_Model_Currency::$rates[$currency]))
		{
			$this
				->clear( )
				->where('code_char', '=', $currency)
				->order_by('date', 'desc')
				->find( );
			
			if ($this->loaded( ))
			{
				Kohana_Model_Currency::$rates[$currency] = (object) $this->as_array( );
			}
			else
			{
				/* :TODO: default rate ? */
				throw new Shop_Exception('cannot find rate for :currency', array(':currency' => $currency));
			}
			
			if (Kohana::$caching)
			{
				Cache::instance( )->set(Kohana_Model_Currency::RATES_CACHE_NAME, Kohana_Model_Currency::$rates);
			}
		}
		
		return Kohana_Model_Currency::$rates[$currency];
	}
	
	/** Convert price
	 *
	 * @param 	float	price
	 * @param 	string	code of output currency
	 * @param 	string	code of input currency
	 * @return 	float
	 */
	public function convert($price, $in, $out = NULL)
	{
		if (empty($out))
		{
			$out = Model_Currency::current( );
		}
		
		return round($price * $this->_data($in)->rate / $this->_data($out)->rate, Site::config('shop')->accuracy);
	}
	
	/** Format price
	 *
	 * @param	float
	 * @param	string	currency char code (RUR, USD etc.)
	 * @return	string
	 */
	public static function format($price, $currency = NULL)
	{
		switch ($currency)
		{
			case 'RUR':
				$currency = ' <span class="rub">p</span>';
			break;
			case 'USD':
				$currency = '$';
			break;
			case 'EUR':
				$currency = '&nbsp;&euro;';
			break;
			default:
				$currency = '';
		}
	
		$price = (float) $price;
	
		return str_replace(' ', '&nbsp;', number_format($price, 2, ',', $price > 10000 ? ' ' : '')).$currency;
	}
}