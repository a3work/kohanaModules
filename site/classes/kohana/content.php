<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Basic site contents handlers v.2
 *
 * @package    Kohana/Site
 * @author     A.St.
 *
 *
 */
class Kohana_Content
{
	// массив объектов класса
	protected static $instances = array( );

	// РЕЖИМЫ РАБОТЫ
	// данные запрошенного элемента
	public static $_MODE_CURRENT 	= 1;

	// данные детей запрошенного элемента
	public static $_MODE_CHILDREN 	= 2;

	// uri страницы
	protected $uri;

	// хэш uri
	protected $uri_hash;

	// map_id страницы
	protected $map_id;

	// флаг загрузки данных
	protected $is_loaded = FALSE;

	// метка контента
	protected $label = NULL;

	// тип контента
	protected $type = NULL;

	// режим работы
	protected $mode;

	// опции
	protected $options;

	// ошибка
	protected $error;

	// содержимое
	protected $data;

	/**
	 *
	 */
	public static function convert( )
	{

		$maps = ORM::factory('site_map')/*->where('uri_hash', '=', '')*/->where('id', '>', 0)->order_by('id')->find_all( );
		foreach ($maps AS $map_item)
		{
			echo 'page '. $map_item->id;
			ob_flush( );
			echo ' -- ';
			Page::refresh_uri($map_item);
// 			$href = Site::get_href($map_item->id);
// 			echo $href;
// 			echo "\n";
// 			$parent = $map_item->parent;
// 			$address = array($map_item->id);
// 			$item = $map_item;
// 			while ($item->parent > -1)
// 			{
// 				array_unshift($address, $item->parent);
// 				$item = ORM::factory('site_map', $item->parent);
// 			}
// 			$address = implode(',', $address);
// // 			var_dump($address);
// 			echo "\n";
// 			$uri = '/'.trim($href, '/');
// 			var_dump($uri);
// 			echo Basic::get_hash($uri);
// 			$map_item->values(array(
// 				'uri'	=> $uri,
// 				'uri_hash' => Basic::get_hash($uri),
// 				'address' => $address,
// 			))
// 			->save( );
// 			echo "\n";
// 			ob_flush( );
		}
	}

	public static function factory($uri = NULL)
	{
		if ( ! isset(self::$instances[$uri]))
		{
			$obj = new Content($uri);
			self::$instances[$uri] = $obj;
		}
		else
		{
			$obj = self::$instances[$uri];
		}

		return $obj;
	}


	/** return is_loaded status
	 *
	 * @return boolean
	 */
	public function loaded( )
	{
		return $this->is_loaded( );
	}

	/**
	 * Конструктор
	 *
	 * @param string URI страницы
	 * @return void
	 */
	public function __construct($uri = NULL)
	{
		if ( ! isset($uri))
		{
			$uri = Request::detect_uri( );
		}

		$this->uri = '/'.trim($uri, '/');

		$this->hash( );

		// дефолтный режим -- поиск данных запрошенной страницы
		$this->mode(Content::$_MODE_CURRENT);
	}

	public function __call($name, $args)
	{
		if (isset($args) && count($args) > 0)
		{
			$this->$name = $args[0];
			return $this;
		}

		return $this->$name;
	}

	/**
	 * Определяем хэш uri
	 *
	 * @return string
	 */
	public function hash( )
	{
		if ( ! isset($this->uri_hash))
		{
			$this->uri_hash = Page::hash($this->uri);
		}

		return $this->uri_hash;
	}

	/**
	 * Определяем опции для загрузки модуля
	 *
	 * @return array
	 */
	public function options($options = NULL)
	{
		if (isset($options))
		{
			$this->options = $options;
			return $this;
		}
		else
		{
			return $this->options;
		}
	}

	/**
	 * Определяем режим поиска
	 * current: данные заданной страницы
	 * list: данные детей заданной страницы
	 *
	 * Без параметров: возвращаем текущий тип
	 * @return mixed
	 *
	 * С параметром: назначаем тип
	 * @param string тип контента
	 * @return object
	 */
	public function mode($mode = NULL)
	{
		if (isset($mode))
		{
			$this->mode = $mode;
			return $this;
		}
		else
		{
			return $this->mode;
		}
	}

	/**
	 * Определяем метку
	 * используется для выдачи контента только с указанным лэйблом
	 *
	 * Без параметров: возвращаем текущую метку
	 * @return mixed
	 *
	 * С параметром: назначаем метку
	 * @param string имя метки
	 * @return object
	 */
	public function label($label = NULL)
	{
		if (isset($label))
		{
			$this->label = $label;
			return $this;
		}
		else
		{
			return $this->label;
		}

	}

	/**
	 * Определяем тип
	 * используется для выдачи контента только указанного типа
	 *
	 * Без параметров: возвращаем текущий тип
	 * @return mixed
	 *
	 * С параметром: назначаем тип
	 * @param string тип контента
	 * @return object
	 */
	public function type($type = NULL)
	{
		if (isset($type))
		{
			$this->type = $type;
			return $this;
		}
		else
		{
			return $this->type;
		}
	}

	/**
	 * Сохраняем и возвращаем данные, полученные из БД
	 *
	 * Без параметров: возвращаем загруженные данные
	 * @return mixed
	 *
	 * С параметром: сохраняем данные
	 * @param mixed Данные
	 * @return object
	 */
	public function data($data = NULL)
	{
		if (isset($data))
		{
			$this->data = $data;
			return $this;
		}
		else
		{
			return $this->data;
		}
	}
	
	/** :KLUDGE: get ORM with selected parent
	 *
	 * @return Model_Site_Map
	 
	public function orm( )
	{
	
		switch ($this->mode)
		{
			case Content::$_MODE_CHILDREN:
			
				$data = ORM::factory('site_map')
					->where('uri_hash', '=', $this->hash( ))
					->find( )
					->children;
					
				if ( ! User::check(Site::config('user')->root_name))
				{
					$data->join()
				}
					
				break;
			case Content::$_MODE_CURRENT;
				break;
		}
	}
*/
	/**
	 * Загружаем содержимое с выбранными параметрами
	 *
	 * @param boolean возвратить данные или объект
	 * @return object
	 */
	public function load($load_data = TRUE)
	{
		// повторно контент не загружаем
		if ($this->is_loaded)
		{
			$this->error('Object is already loaded');
			return $this;
		}

		$data = ORM::factory('site_map')->data(
			$this->mode( ),
			$this->hash( ),
			$this->label( ),
			$this->type( ),
			$this->options( )
		);

		if ( ! isset($data))
		{
			$this->error(404);
		}
		else
		{

			// записываем данные в выходной объект
			$this->data((object) $data);
			// отмечаем, что данные загружены
			$this->is_loaded = TRUE;
		}

		return ($load_data) ? $this->data( ) : $this;
	}

	/**
	 * Проверяем загрузку данных,
	 * в случае отсутствия выкидываем 404
	 *
	 * @return void
	 */
	public function check( )
	{
		if ($this->error( ) == 404)
		{
			throw new HTTP_Exception_404;
		}
	}
}
