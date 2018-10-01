<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forms generator engine
 *
 * @package 	Kohana/Form
 * @author 	A.St.
 */

class Kohana_Form
{
	// массив выходных данных
	public static $input = array( );

	// массив входных данных
	public static $output = array( );

	// массив фидбэков
	public static $feedback = array( );

	// массив опций полей формы
	public static $options = array( );

	// массив директив полей для специфичного управления отображением
	public static $directions = array( );

	// массив аргументов правил отношения
	public static $relation_args = array( );

	// массив форм
	public static $forms = array( );

	public static function get_feedback( )
	{
		if ( ! isset(self::$feedback))
		{
			self::$feedback = Session::instance( )->get('feedback');
		}
		return self::$feedback;
	}

	public static function save_feedback( )
	{
		Session::instance( )->set('feedback', self::$feedback);
	}

	// геттер данных формы
	public static function get_data($label, $external = TRUE)
	{
		// для внешних вызовов (не из контроллера формы) отдаём данные выходного массива $output
		if ($external)
		{
			if (isset(self::$output[$label]))
			{
				return self::$output[$label];
			}
		}
		else
		{
			if (isset(self::$input[$label]))
			{
				return self::$input[$label];
			}
		}
	}

	// функция добавления данных формы
	public static function add_data($label, $data, $external = TRUE)
	{
		// внешние вызовы (не из контроллера формы) пишут во входной массив $input
		if ($external)
		{
			self::$input[$label] = $data;
		}
		else
		{
			self::$output[$label] = $data;
		}
	}


	/** Функция, возвращающая настройки элементов формы
	*	@param integer 	field_id
	*	@return array of settings
	*
	*	@param integer 	field_id
	*	@param array 		settings
	*	@return merged array of settings
	*
	*	@param string 		form label
	*	@param string 		field label
	*	@return array of settings
	*
	*	@param string 		form label
	*	@param string 		field label
	*	@param array 		settings
	*	@return merged array of settings
	**/
	public static function settings( )
	{
		// определяем список аргументов
		$args = func_get_args( );

		// определяем количество аргументов
		$argc = func_num_args( );

		switch($argc)
		{
			case 1:
				// определяем номер поля для поиска
				$field_id = $args[0];
				break;

			case 2:
				// определяем по типу второго аргумента
				// поведение функции
				$type = gettype($args[1]);

				if ($type == 'array')
				{
					$field_id = 			$args[0];
					$merged_array =	$args[1];
				}
				else
				{
					$form_label = 	$args[0];
					$field_label = 	$args[1];
				}
				break;
			case 3:
				$form_label = 		$args[0];
				$field_label = 		$args[1];
				$merged_array = 	$args[2];
				break;
		}

		// получаем настройки поля из БД по id или "адресу" <метка формы>.<метка поля>
		if (isset($field_id))
		{
			$result = ORM::factory('form_field', $field_id)->as_array( );
		}
		elseif (isset($form_label))
		{
			$form = ORM::factory('form')->where('label', '=', $form_label)->find( );

			if (isset($field_label))
			{
				$result = $form->form_field->where('name', '=', $field_label)->find( )->as_array( );
			}
			/* :TODO: */
// 			$result = ORM::factory('form_field', $field_id)->as_array( );
		}

		// если задан массив перегружаемых параметров
		// сливаем
		if (isset($merged_array))
		{
			$result = array_merge($result, $merged_array);
		}

		return $result;
	}

	/** Функция чтения и добавления аргументов правил отношения
	* @param		string   form label
	* @return  void
	*
	* @param		string   	form label
	* @param		array	fields and options array
	* @return		void
	*
	* @param		string   	form label
	* @param		mixed   	form parameter
	* @param		array	fields and options array
	* @return		void
	*/
	public static function relation_args( )
	{
		// определяем список аргументов
		$args = func_get_args( );

		// определяем количество аргументов
		$argc = func_num_args( );

		// изменяем поведение функции
		// в зависимости от количества и типа аргументов
		switch ($argc)
		{
			case 1:
				// если задана только метка формы
				// функция возвращает все аргументы правил отношений
				if (isset(self::$relation_args[$args[0]]))
				{
					return self::$relation_args[$args[0]];
				}
				else
				{
					return '';
				}

			case 2:
				// определяем тип второго аргумента
				$type = gettype($args[1]);

				// если второй аргумент -- строка или число
				if ($type == 'string' || $type == 'integer')
				{
					// возвращаем опции соответствующего поля формы
					if (isset(self::$relation_args[$args[0]][$args[1]]))
					{
						return self::$relation_args[$args[0]][$args[1]];
					}
					else
					{
						return '';
					}
				}
				else
				{
					// если массив существует
					if (isset(self::$relation_args[$args[0]]))
					{
						// сливаем опции формы и указанный массив
						self::$relation_args[$args[0]] = array_merge(self::$relation_args[$args[0]], $args[1]);
					}
					else
					{
						// присваиваем указанный массив
						self::$relation_args[$args[0]] = $args[1];
					}
				}
				break;

			case 3:
				// устанавливаем аргументы определённого правила
				self::$relation_args[$args[0]][$args[1]] = $args[2];

				break;
		}
	}

	/** Функция чтения и добавления директив полей в форму
	* @param		string   	form label
	* @return  void
	*
	* @param		string   	form label
	* @param		string		field name
	* @return		mixed
	*
	* @param		string   	form label
	* @param		array		fields and directions array
	* @return		void
	*
	* @param		string   	form label
	* @param		string		field name
	* @param		string		directive name
	* @return		mixed
	*
	* @param		string   	form label
	* @param		string   	field name
	* @param		array		directions array
	* @return		void
	*/
	public static function directions( )
	{
		// определяем список аргументов
		$args = func_get_args( );

		// определяем количество аргументов
		$argc = func_num_args( );

		// изменяем поведение функции
		// в зависимости от количества и типа аргументов
		switch ($argc)
		{
			case 1:
				// если задана только метка формы
				// функция возвращает все заданные опции формы
				if (isset(self::$d[$args[0]]))
				{
					return self::$directions[$args[0]];
				}
				else
				{
					return NULL;
				}

			case 2:
				// определяем тип второго аргумента
				$type = gettype($args[1]);

				// если второй аргумент -- строка или число
				if ($type == 'string' || $type == 'integer')
				{
					// возвращаем опции соответствующего поля формы
					if (isset(self::$directions[$args[0]][$args[1]]))
					{
						return self::$directions[$args[0]][$args[1]];
					}
					else
					{
						return NULL;
					}
				}
				else
				{
					// если массив существует
					if (isset(self::$directions[$args[0]]))
					{
						// сливаем опции формы и указанный массив
						self::$directions[$args[0]] = array_merge(self::$directions[$args[0]], $args[1]);
					}
					else
					{
						// присваиваем указанный массив
						self::$directions[$args[0]] = $args[1];
					}
				}

				break;

			case 3:
				if (is_array($args[2]))
				{
					// устанавливаем опции определённого поля формы
					self::$directions[$args[0]][$args[1]] = $args[2];
				}
				elseif (isset(self::$directions[$args[0]][$args[1]][$args[2]]))
				{
					return self::$directions[$args[0]][$args[1]][$args[2]];
				}

				break;
		}
	}


	/** Функция чтения и добавления опций в форму
	* @param		string   form label
	* @return  void
	*
	* @param		string   	form label
	* @param		array	fields and options array
	* @return		void
	*
	* @param		string   	form label
	* @param		mixed   	form parameter
	* @param		array	fields and options array
	* @return		void
	*/
	public static function options( )
	{
		// определяем список аргументов
		$args = func_get_args( );

		// определяем количество аргументов
		$argc = func_num_args( );

		// изменяем поведение функции
		// в зависимости от количества и типа аргументов
		switch ($argc)
		{
			case 1:
				// если задана только метка формы
				// функция возвращает все заданные опции формы
				if (isset(self::$options[$args[0]]))
				{
					return self::$options[$args[0]];
				}
				else
				{
					return NULL;
				}

			case 2:
				// определяем тип второго аргумента
				$type = gettype($args[1]);

				// если второй аргумент -- строка или число
				if ($type == 'string' || $type == 'integer')
				{
					// возвращаем опции соответствующего поля формы
					if (isset(self::$options[$args[0]][$args[1]]))
					{
						return self::$options[$args[0]][$args[1]];
					}
					else
					{
						return NULL;
					}
				}
				else
				{
					// если массив существует
					if (isset(self::$options[$args[0]]))
					{
						// сливаем опции формы и указанный массив
						self::$options[$args[0]] = array_merge(self::$options[$args[0]], $args[1]);
					}
					else
					{
						// присваиваем указанный массив
						self::$options[$args[0]] = $args[1];
					}
				}
				break;

			case 3:
				// устанавливаем опции определённого поля формы
				self::$options[$args[0]][$args[1]] = $args[2];

				break;
		}
	}

	/** Регистрация страницы возврата
	 *
	 * @param string
	 * @param string
	 * @param string
	 * @return void
	 */
	public static function add_feedback($page, $form, $url)
	{
		$data = self::get_feedback( );
		if ( ! isset($data[$form]))
		{
			$data[$form] = array( );
		}
		$data[$form][count($data[$form])] = $url;
		$data[$page] = &$data[$form][count($data[$form])];
		self::save_feedback( );
	}

	/** Строим форму по метке
	 * 		возвращаем код
	 *
	 * @param string 	form label
	 * @return string 	html
	 */
	public static function render($form_label)
	{
		return Request::factory(Route::get('form_by_id')->uri(array('id_type' => 'label', 'id' => $form_label)))->execute( )->body( );
	}

	/** Adding Form_Bjase or subclass instance
	 *
	 * @param string classname or text label
	 * @return Form_Base
	 */
	public static function factory($label = NULL)
	{
		if ( ! isset($label))
		{
			$count = 0;
			$label = Site::config('form')->default_form_name;
			
			// generate form label
			while (isset(self::$forms[$label.$count]))
			{
				$count++;
			}
			
			$label .= $count;
		}

		$class = 'form_'.$label;

		if (class_exists($class))
		{
		
			if (isset(self::$forms[$label]))
			{
				$count = 0;
				
				// generate form label
				while (isset(self::$forms[$label.$count]))
				{
					$count++;
				}
				
				$label .= $count;
			}
			
			$obj = new $class($label);

			if ($obj instanceof Form_Base)
			{
				self::$forms[$label] = $obj;
			}
			else
			{
				throw new Form_Exception("Class ':class'' must be a Form_Base subclass", array(":class"=>$class));
			}
		}
		else
		{
			if ( ! isset(self::$forms[$label]))
			{
				self::$forms[$label] = new Form_Base($label);
			}
		}

		return self::$forms[$label];
	}

	/** Generate name for new form
	 *
	 * @return string
	 */
	public static function name( )
	{
		return Site::config('form')->default_form_name.count(self::$forms);
	}
}
