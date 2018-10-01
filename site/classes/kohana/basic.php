<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Basic {
	public static $config;

/*
// вынужденное использование глобальной переменной
$param = "";
// сортировка многомерного массива
function multisort( $arr, $param_a)
{
	global $param;
	$param = $param_a;
	function sort_func( $a, $b)
	{
		global $param;
		if ( is_array( $param))
		{
			foreach ( $param AS $kk => $c_key)
			{
				$key_arr = explode( " ", $c_key);
				if ( is_string( $a[$key_arr[0]]))
				{
					$c_result = strcmp( $a[$key_arr[0]], $b[$key_arr[0]]);
				}
				else
				{
					$c_result = $a[$key_arr[0]] > $b[$key_arr[0]] ? 1 : ($a[$key_arr[0]] < $b[$key_arr[0]] ? -1 : 0);
				}
				if ( $c_result != 0)
				{
					return (isset($key_arr[1]) && $key_arr[1] == "asc") || !isset( $key_arr[1]) ? $c_result : ($c_result > 0 ? -1 : 1);
				}
			}
			return 0;
		}
		else
		{
			$key_arr = explode( " ", $param);
			if ( is_string( $a[$key_arr[0]]))
			{
				$c_result = strcmp( $a[$key_arr[0]], $b[$key_arr[0]]);
			}
			else
			{
				$c_result = $a[$key_arr[0]] > $b[$key_arr[0]] ? 1 : ($a[$key_arr[0]] < $b[$key_arr[0]] ? -1 : 0);
			}
			return $key_arr[1] == "asc" || !isset( $key_arr[1]) ? $c_result : ($c_result > 0 ? -1 : ($c_result < 0 ? 1 : 0));
		}
	}
	uasort( $arr, "sort_func");
	return $arr;
}
*/
	public static function e($data)
	{
		if (is_object($data) || is_array($data))
		{
			var_dump($data);
		}
		else
		{
			echo $data;
		}

		echo "\n<br>\n";
	}
	// правильное отображение кириллицы в формате json
	// кодирование
	public static function json_safe_encode($var, $options = 0)
	{
		return json_encode(Basic::json_fix_cyr($var), $options);
// 		return $var ? json_encode(Basic::json_fix_cyr($var)) : $var;
	}

	// декодирование
	public static function json_safe_decode($var, $as_object = FALSE)
	{
		$data = Basic::json_fix_cyr(json_decode($var, TRUE), FALSE);
		return $as_object ? (object) $data : $data;
	}

	public static function json_fix_cyr($var, $encode = TRUE)
	{
		if (is_array($var))
		{
			$new = array();
			foreach ($var as $k => $v)
			{
				$new[Basic::json_fix_cyr($k, $encode)] = Basic::json_fix_cyr($v, $encode);
			}
			$var = $new;
		}
		elseif (is_object($var))
		{
			$vars = get_object_vars($var);
			foreach ($vars as $m => $v)
			{
				$var->$m = Basic::json_fix_cyr($v, $encode);
			}
		}
		elseif (is_string($var))
		{
			if (self::get_config( )->encoding != 'utf-8')
			{
				if ($encode)
				{
					$var = iconv('cp1251', 'utf-8', $var);
				}
				else
				{
					$var = iconv('utf-8', 'cp1251', $var);
				}
			}
		}
		return $var;
	}

	public static function format_string_sec_num($number, $words)
	{
		if ($number != 0)
		{
			settype($number, "string");
		    $last_one = (int) substr( $number, strlen( $number)-1, strlen( $number));
		    $last_two = (int) substr( $number, strlen( $number)-2, strlen( $number));
		    if ( $last_two < 10 || $last_two > 19)
		    {
		   		if ( $last_one == 1)
		   		{
		   			return $words[0];
		   		}
		   		elseif ($last_one == 2 || $last_one == 3 || $last_one == 4)
		   		{
		   			return $words[1];
		   		}
		   		else
		   		{
		   			return $words[2];
		   		}
		   	}
		   	else
		   	{
		   		return $words[2];
		   	}
		}
		else
		{
			return $words[2];
		}
	}

	// склонение слов в соответствии с числительными
	public static function number_decline($message)
	{
		return I18n::decline($message);
	}

	// геттер конфига
	public static function get_config( )
	{
		return Site::config('basic');
	}

	// получаем код подключения внешнего файла
	public static function get_including_code($file, $force_refresh = FALSE)
	{
		// определяем тип файла
		$ext = pathinfo($file, PATHINFO_EXTENSION);
		// добавляем параметр для принудительного обновления файла
		$force_refresh_code = $force_refresh ? '?'.time() : '';
		// формируем код на основании типа
		switch ($ext)
		{
			case 'js':
				$code = "<script type='text/javascript' src='". Route::url('static_files', array('filetype'=>'js', 'file'=>$file)) ."{$force_refresh_code}'></script>";
				break;
			case 'css':
				$code = "<link rel='stylesheet' type='text/css' href='". Route::url('static_files', array('filetype'=>'css', 'file'=>$file)) ."{$force_refresh_code}'>";
				break;
		}

		return $code;
	}

	// генерируем хэш с использованием site salt
	public static function get_hash($value = NULL, $type = 'md5', $length = NULL)
	{
		if ( ! isset($value))
		{
			$value = mt_rand( );
		}

		switch ($type)
		{
			case 'md5':
				$hash = md5($value . Cookie::$salt);
				break;
			case 'sha1':
				$hash = sha1($value . Cookie::$salt);
				break;
			default:
				$hash = md5($value . Cookie::$salt);
				
		}
		
		if (isset($length))
		{
			$hash = substr($hash, 0, $length);
		}

		return $hash;
	}

	/**
	 * Преобразование букв латинского алфавита в номера и наоборот
	 *
	 * @param mixed входная строка
	 * @param boolean флаг прямого преобразования (букв в номера)
	 * @return string
	 */
	 public static function letter_to_number($s, $letter_to_number = TRUE)
	 {
		if (is_array($s) || is_object($s))
		{
			foreach ($s AS &$item)
			{
				$item = self::letter_to_number($item, $letter_to_number);
			}
		}
		else
		{
			$result = array('A'=>0, 'B'=>1, 'C'=>2, 'D'=>3, 'E'=>5, 'F'=>5, 'G'=>6, 'H'=>7, 'I'=>8, 'J'=>9, 'K'=>10, 'L'=>11, 'M'=>12, 'O'=>13,'P'=>14, 'Q'=>15,'R'=>16,'S'=>17, 'T'=>18, 'U'=>19, 'V'=>20, 'W'=>21, 'X'=>22,'Y'=>23,'Z'=>24);

			if ( ! $letter_to_number)
			{
				$result = array_flip($result);
			}
			$s = strtr(strtoupper($s), $result);
		}
		return $s;
	 }

	/**
	 * Преобразование кириллицы в URL
	 *
	 * @param string входная строка
	 * @return string
	 */
	 public static function url_encode($s)
	 {
		$s=  strtr ($s,  array (" "=> "%20", "а"=>"%D0%B0", "А"=>"%D0%90","б"=>"%D0%B1", "Б"=>"%D0%91", "в"=>"%D0%B2", "В"=>"%D0%92", "г"=>"%D0%B3", "Г"=>"%D0%93", "д"=>"%D0%B4", "Д"=>"%D0%94", "е"=>"%D0%B5", "Е"=>"%D0%95", "ё"=>"%D1%91", "Ё"=>"%D0%81", "ж"=>"%D0%B6", "Ж"=>"%D0%96", "з"=>"%D0%B7", "З"=>"%D0%97", "и"=>"%D0%B8", "И"=>"%D0%98", "й"=>"%D0%B9", "Й"=>"%D0%99", "к"=>"%D0%BA", "К"=>"%D0%9A", "л"=>"%D0%BB", "Л"=>"%D0%9B", "м"=>"%D0%BC", "М"=>"%D0%9C", "н"=>"%D0%BD", "Н"=>"%D0%9D", "о"=>"%D0%BE", "О"=>"%D0%9E", "п"=>"%D0%BF", "П"=>"%D0%9F", "р"=>"%D1%80", "Р"=>"%D0%A0", "с"=>"%D1%81", "С"=>"%D0%A1", "т"=>"%D1%82", "Т"=>"%D0%A2", "у"=>"%D1%83", "У"=>"%D0%A3", "ф"=>"%D1%84", "Ф"=>"%D0%A4", "х"=>"%D1%85", "Х"=>"%D0%A5", "ц"=>"%D1%86", "Ц"=>"%D0%A6", "ч"=>"%D1%87", "Ч"=>"%D0%A7", "ш"=>"%D1%88", "Ш"=>"%D0%A8", "щ"=>"%D1%89", "Щ"=>"%D0%A9", "ъ"=>"%D1%8A", "Ъ"=>"%D0%AA", "ы"=>"%D1%8B", "Ы"=>"%D0%AB", "ь"=>"%D1%8C", "Ь"=>"%D0%AC", "э"=>"%D1%8D", "Э"=>"%D0%AD", "ю"=>"%D1%8E", "Ю"=>"%D0%AE", "я"=>"%D1%8F", "Я"=>"%D0%AF"));
		return $s;
	 }

	/**
	 * Обратное преобразование кириллицы из URL
	 *
	 * @param string входная строка
	 * @return string
	 */
	 public static function url_decode($s)
	 {
		$s=  strtr($s,  array ("%20"=>" ", "%D0%B0"=>"а", "%D0%90"=>"А", "%D0%B1"=>"б", "%D0%91"=>"Б", "%D0%B2"=>"в", "%D0%92"=>"В", "%D0%B3"=>"г", "%D0%93"=>"Г", "%D0%B4"=>"д", "%D0%94"=>"Д", "%D0%B5"=>"е", "%D0%95"=>"Е", "%D1%91"=>"ё", "%D0%81"=>"Ё", "%D0%B6"=>"ж", "%D0%96"=>"Ж", "%D0%B7"=>"з", "%D0%97"=>"З", "%D0%B8"=>"и", "%D0%98"=>"И", "%D0%B9"=>"й", "%D0%99"=>"Й", "%D0%BA"=>"к", "%D0%9A"=>"К", "%D0%BB"=>"л", "%D0%9B"=>"Л", "%D0%BC"=>"м", "%D0%9C"=>"М", "%D0%BD"=>"н", "%D0%9D"=>"Н", "%D0%BE"=>"о", "%D0%9E"=>"О", "%D0%BF"=>"п", "%D0%9F"=>"П", "%D1%80"=>"р", "%D0%A0"=>"Р", "%D1%81"=>"с", "%D0%A1"=>"С", "%D1%82"=>"т", "%D0%A2"=>"Т", "%D1%83"=>"у", "%D0%A3"=>"У", "%D1%84"=>"ф", "%D0%A4"=>"Ф", "%D1%85"=>"х", "%D0%A5"=>"Х", "%D1%86"=>"ц", "%D0%A6"=>"Ц", "%D1%87"=>"ч", "%D0%A7"=>"Ч", "%D1%88"=>"ш", "%D0%A8"=>"Ш", "%D1%89"=>"щ", "%D0%A9"=>"Щ", "%D1%8A"=>"ъ", "%D0%AA"=>"Ъ", "%D1%8B"=>"ы", "%D0%AB"=>"Ы", "%D1%8C"=>"ь", "%D0%AC"=>"Ь", "%D1%8D"=>"э", "%D0%AD"=>"Э", "%D1%8E"=>"ю", "%D0%AE"=>"Ю", "%D1%8F"=>"я", "%D0%AF"=>"Я"));
		return $s;
	 }

	/**
	 * Текстовое представление времени
	 *
	 * @param mixed дата
	 * @param string формат
	 * @return string
	 */
	public static function get_time( $date, $type, $atr = "") {
		throw new Kohana_Exception('Basic::get_time deprecated, use Date::format.');
	}

	/**
	 * Построение ветки дерева правил
	 *
	 * @param string элемент
	 * @param array ссылка на результирующий массив
	 * @return mixed
	 */
	private static function get_tree_branch($item, &$branches, $orm_name)
	{
		// текущая ветка
		$branch = '';

		// длина "адреса" узла (id, дополненный нулями)
		$id_length = 3;

		// для некорневых узлов ищем ветку
		if ($item['id'] != 0)
		{
			// определяем текущий адрес узла
			$branch = str_repeat('0', $id_length - strlen($item['id'] )).$item['id'] ;

			// если полный адрес узла ещё не вычислялся
			if ( ! in_array($item['parent'], array_keys($branches)))
			{
				// вычисляем адрес узла для родителя и прибавляем к нему адрес текущего узла
				$branch = self::get_tree_branch(ORM::factory($orm_name, $item['parent'])->as_array( ), $branches, $orm_name).$branch;
			}
			else
			{
				// прибавляем адрес текущего узла к уже известному адресу родителя
				$branch = $branches[$item['parent']]['branch'].$branch;
			}

			// добавляем запись в результирующий массив
			$branches[$item['id']] = array(
				// адрес (ветка)
				'branch' 		=> $branch,
				// является ли элемент заголовком ветки
				'is_header' 	=> $item['name'] == '' ? 1 : 0,

				'value' => $item['id'],
				'header' => $item[Site::get_language( )],
			);

			// заносим все остальные данные элемента в результирующий массив
			$branches[$item['id']] = array_merge($branches[$item['id']], $item);
		}

		// возвращаем адрес (ветку) узла
		return $branch;
	}

	/**
	 * Построение дерева по конечным элементам на основе массива этих элементов и ORM
	 *
	 * @param array массив правил
	 * @return array дерево правил
	 */
	public static function get_tree($rights, $orm_name)
	{
		$out = array( );

		foreach ($rights AS $rights_item)
		{
			self::get_tree_branch($rights_item, $out, $orm_name);
		}

		$out = multisort($out, array('branch', 'is_header'));

		return $out;
	}

    /**
     * Ищем родителей элемента дерева, кэшируем, возвращаем массив родителей
     *
     * @param string 	имя модели
     * @param int 		ID элемента
     * @param mixed		Тэг кэширования
     * @param integer	Время кэширования
     * @return array
     */
    public static function get_parents($model_name, $model_id, $cache_tag = NULL, $cache_lifetime = NULL)
    {
		if (Kohana::$caching)
		{
			$cache_key = self::get_hash(__FILE__.__CLASS__.__FUNCTION__.self::json_safe_encode(func_get_args( )));
			$cache_data = Cache::instance(Site::config('site')->cache_driver)->get($cache_key);
			if ($cache_data != NULL)
			{
				return $cache_data;
			}
		}

		$orm = ORM::factory($model_name, $model_id);
		if ($orm->loaded( ))
		{
			if ($orm->id == 0)
			{
				$out = array( );
			}
			else
			{
				$out = array_merge(self::get_parents($model_name, $orm->parent, $cache_tag, $cache_lifetime), array(str_repeat('0', (Site::config('basic')->parents_item_length - strlen($orm->parent))).$orm->parent));
			}
		}
		else
		{
			$out = array( );
		}

		if (Kohana::$caching)
		{
			Cache::instance(Site::config('site')->cache_driver)->set($cache_key, $out, $cache_lifetime);
			if (isset($cache_tag))
			{
				Cache::set_tag($cache_key, $cache_tag);
			}
		}

		return $out;
    }

    /** Convert to latin text
     *
     * @param	string		string to convert
     * @param	boolean		direction (
     * 							TRUE: 	RU->EN
     * 							FALSE:	EN->RU
	 *						)
	 * @return 	string
	 */
    public static function tr($string, $fwd = TRUE)
	{
		$string = stripslashes($string);

		$converter = array('а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd',
			'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'j', 'к' =>
			'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' =>
			's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'cz', 'ч' => 'ch',
			'ш' => 'sh', 'щ' => 'sch', 'ь' => "", 'ы' => "y", 'ъ' => "", 'э' => "e", 'ю' =>
			'yu', 'я' => 'ya', ' ' => '_');

		if ($fwd)
		{
			return preg_replace('/[^a-zA-Z0-9_]+/', '', str_replace(array_keys($converter), $converter, mb_strtolower($string)));
		}
		else
		{
			return preg_replace('/[^а-яА-Я0-9_]+/', '', str_replace($converter, array_keys($converter), mb_strtolower($string)));
		}
	}
	
	/** get public object vars
	 *
	 * @param	object
	 * @return	array
	 */
	public static function get_object_vars($obj)
	{
		return get_object_vars($obj);
	}
	
	
}
