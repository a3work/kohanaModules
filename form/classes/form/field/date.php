<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Date field description
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-08
 *
 **/

class Form_Field_Date extends Form_Field
{
	// input type
	public $view = 'date';
	
	protected $_format;

	public function __construct($name)
	{
		parent::__construct($name);

		InclStream::instance( )->add('datepicker.js', FALSE, 1);
		InclStream::instance( )->add('datepicker.css', FALSE, 1);
		InclStream::instance( )->add('form.date.init.js');
	}
	
	/** Set up format of date
	 * 
	 * @return this
	 */
	public function format($format = NULL)
	{
		if (isset($format))
		{
			$replacements = array(
				'd' => __('d'),
				'm' => __('m'),
				'Y' => __('Y'),
			);

			
			$this->placeholder(str_replace(array_keys($replacements), $replacements, $format));
			$this->mask(str_replace(array('y', 'm', 'd', 'Y'), '9', $format));
			
			$format = preg_replace("/d+/", 'd', $format);
			$format = preg_replace("/m+/", 'm', $format);
			$format = preg_replace("/Y+/", 'Y', $format);
			
			$this->_format($format);
		
			return $this;
		}
		else
		{
			if ($this->_format( ) === NULL)
			{
				$this->format(Site::config('form')->default_date_format);
			}
		
			return $this->_format( );
		}
	}
}