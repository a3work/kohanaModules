<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Forms generator engine
 *
 * @package 	Kohana/Form
 * @author 	A.St.
 */

class Kohana_Form_Engine
{
	// данные формы
	protected $form;

	// сообщение
	protected $message;

	// тип сообщения
	protected $message_type;

	// флаг генерирования правил зависимостей
	protected $relation_loaded = FALSE;

	// массив чистых функций зависимости
	protected $clear_relations = array( );

	// js код проверки правил отношений
	protected $validation_js = array( );
	
	// name of validation functions 
	protected $valid_func_arr = array( );

	// код активатора
	protected $activator_code;

	// опции редиректа
	protected $redirect = FALSE;
	protected $redirect_uri = '';

	// стэк сообщений об ошибках
	protected static $errors = array( );

	/**	Construct form render engine
	 *
	 * @param Form_Base
	 * @return void
	 */
	public function __construct(Form_Base $form)
	{
		$this->form = $form;
	}

	/** Start form processing
	 *
	 * @return void
	 */
	public function start( )
	{
		// get data and check activator
		$this->form( )->get( );

		// запускаем обработку ошибок
		$errors = $this->validate_form( );

		// write js config
		InclStream::instance( )->config(array(
			'form_missing_field' => Site::config('form')->error_field_class_name,
			'form_ready_field' => Site::config('form')->ready_field_class_name,
		));
		
		if (is_bool($errors) && $errors === FALSE)
		{
			$this->form( )->sent(TRUE);

			// резервируем отправленные данные
			if ((bool) $this->form( )->enable_backup( ))
			{
				ORM::factory('form_backup')
					-> values(array(
						'data' 	=> Basic::json_safe_encode($this->form( )->values( )),
						'ip'	=> $_SERVER['REMOTE_ADDR'],
						'agent' => $_SERVER['HTTP_USER_AGENT'],
					))
					->save( );
			}
			// если метод передачи == route
			if ($this->form( )->method( ) == 'route')
			{
				// указываем, что форму нужно переадресовать
				$this->redirect = TRUE;
				$this->redirect_uri = Route::get($this->form( )->action( ))->uri($this->form( )->values( ));
			}
			else
			{
				// пишем сообщение о завершении заполнения формы
				if ($this->form( )->message( ) !== NULL && $this->form( )->message( ) != '')
				{
					$this->message = $this->form( )->message( );
				}
				// меняем тип сообщения на успех
				$this->message_type = Site::config('form')->message_types->success;

				// запускаем генерирование формы
				$this->process( );

				$this->form( )->values = array( );
			}
		}
		elseif (is_array($errors))
		{
			// drop result
			$this->form( )->sent(FALSE);
			$this->form( )->result( )->drop( );

			// извлекаем первую ошибку и записываем её в переменную класса
			$this->message = array_shift($errors);
			// меняем тип сообщения на ошибку
			$this->message_type = Site::config('form')->message_types->error;
			
			if ($this->form( )->allocate_errors( ) === FALSE)
			{
				// write error message
				$this->form( )->error($this->message);
			}
			
			// запускаем генерирование формы
			$this->process( );
		} else {
			// обнуляем массив значений, т.к. валидатор не сработал
			// (не совпал код проверки или не пришли данные)
			$this->form( )->values = array( );
			// запускаем генерирование формы
			$this->process( );
		}
	}


	/**
	 * Выдаём опции редиректа
	 *
	 */
	public function redirect( )
	{
		return $this->redirect;
	}
	public function redirect_uri( )
	{
		return $this->redirect_uri;
	}

	/** Проверка, является ли метод передачи данных равным post
		*
		* @return boolean
		*/
	protected function set_form_method( )
	{
		return in_array($this->form( )->method( ), array('post', 'route')) ? 'post' : 'get';
	}

	/** Загружаем данные формы
	 *
	 * @param object field
	 * @return boolean
	 */
	protected function form($type = NULL, $id = NULL)
	{
		if ( ! isset($this->form) && isset($type) && isset($id))
		{
			// определяем тип идентификатора
			// и используем соответствующий метод потрашения базы
			switch($type)
			{
				case 'id':
					$this->form = ORM::factory('form', $id)-> find();
					break;
				case 'label':
					$this->form = ORM::factory('form')
							-> where('label', '=', $id)
							-> find();
					break;
				case 'class':
					if ($id instanceof Form_Base)
					{
						$this->form = $id;
					}
					else
					{
						return FALSE;
					}
					break;
			}

			return $this->form->loaded( ) ? TRUE : FALSE;
		}
		elseif (isset($this->form))
		{
			return $this->form;
		}

	}

	// генерирование тела формы
	protected function get_form_body( )
	{
		$out = array(
			'visible' => '',
			'hidden' => '',
		);
		
		$ext_out = '';

		if ($this->form( )->html( ) !== NULL)
		{
			$ext_out = $this->form( )->html( );
		}

		// replacements of render marks
		$replacements = $replacements_keys = array( );

		foreach ($this->form( )->fields( ) AS $field)
		{
			if ($field->beh( ) !== NULL)
			{
				// запускаем генерирование правила
				$this->render_behavior($field->beh( ));
			}
			// if not used custom form output
			if ( ! $field->rendered( ))
			{
				if ($field->render_mark( ) !== NULL)
				{
					// generate html
					$html = $field->html(TRUE);

					// add replacement of field render mark
					$replacements_keys[] = $field->render_mark( );
					$replacements[] = ($this->form( )->sent( ) !== TRUE || $this->form( )->sent( ) === TRUE && $this->form( )->show_on_success( )) ? $html : '';

					unset($html);
				}
				else
				{
					if ($this->form( )->sent( ) !== TRUE || $this->form( )->sent( ) === TRUE && $this->form( )->show_on_success( ))
					{
						if ($field instanceof Form_Field_Hidden)
						{
							$out['hidden'] .= $field->html(TRUE);
						}
						else
						{
							$out['visible'] .= (string) View::factory($field->wrapper( ), array(
								'element' 	=> $field->html(TRUE),
								'label' 	=> $field->label( ),
								'class' 	=> $field->_class( ),
								'message'	=> $field->message( ),
							))->render( );
						}
						
					}
					// if form will be hide on success
					// generate result only
					else
					{
						$field->html(TRUE);
					}
				}
			}
		}
		
		$out['visible'] = $ext_out.$out['visible'];
		
		if (count($replacements) > 0)
		{
			$out['visible'] = str_replace($replacements_keys, $replacements, $out['visible']);
			$out['hidden'] = str_replace($replacements_keys, $replacements, $out['hidden']);
		}

		return $out;
	}

	/** Add activator code to form -- if need
	 *
	 * @return void
	 */
	protected function get_activation_field( )
	{
		if ( ! $this->form( )->use_activator( ))
		{
			$this->activator_code = FALSE;
		}
		elseif ($this->form( )->activator_value( ) === NULL)
		{
			// генерируем ключик
			// пишем его в сессию и создаём скрытый тэг с ним
			$this->form( )->activator_value(substr(md5(mt_rand( )), 0, Site::config('form')->activator_var_length));
			Session::instance( )->set($this->form( )->activator_name( ), $this->form( )->activator_value( ));

			$this->form( )->field('hidden', NULL, $this->form( )->activator_name( ))->value($this->form( )->activator_value( ))->hold_value(FALSE)->is_private(TRUE);
		}
	}

	/**
	 * Собираем js отношений
	 *
	 * @return string
	 */
	protected function combine_relation_js( )
	{
		$out = '';

		if (count($this->validation_js) > 0)
		{
			$out = implode('', $this->validation_js);

			// сортировка по уровню для исключения ситуации вызова функции до объявления
// 			$behaviors = multisort(, array('js_level desc'));

			foreach ($this->form( )->behavior_list( ) AS $beh)
			{
				$out .= $beh->js( );
			}
		}
		
		if ($this->form( )->use_js_validation( ) === TRUE)
		{
			// combine js func array
			$out .= 'Form.defineVld();Vld["'.$this->form( )->label( ).'"]='.Basic::json_safe_encode($this->valid_func_arr).';';
		}
		
		return $out;
	}

	// генерирование формы
	protected function process( )
	{
		// add includes
		InclStream::instance( )->add('form.css');
		InclStream::instance( )->add('form.js');

		// generate new activator
		$this->get_activation_field( );
		
		// set HTTP query type to form
		$this->form->method($this->set_form_method( ));

		// process form body: render fields and relations
		$form_body = $this->get_form_body( );
		
		InclStream::instance( )->write($this->combine_relation_js( ));

		// write activator if JS validation is switch on
		if ($this->form( )->use_js_validation( ) === TRUE)
		{
			$label = str_replace('"', '\"', $this->form( )->label( ));
		
			$use_immediatly_check = $this->form( )->use_immediatly_check( ) === TRUE ? '1' : '0';
			
			$error_callback = $this->form( )->js_callback_error( ) !== NULL ? '"'.$this->form( )->js_callback_error( ).'"' : 'false';
			$success_callback = $this->form( )->js_callback_success( ) !== NULL ? '"'.$this->form( )->js_callback_success( ).'"' : 'false';
		
			$settings = Basic::json_safe_encode(array(
				'allocate_errors' => $this->form->allocate_errors( ),
			));
		
			InclStream::instance( )->write(<<<HERE
Form.init("$label",{$use_immediatly_check},{$error_callback},{$success_callback},{$settings});
HERE
			);
		}

		// write html to form tag
		$this->form( )->html(
			 $this->form->tag_o( )						// opening tag
			.$form_body['visible']						// visible body
			.$this->form->tag_c($form_body['hidden'])	// hidden fields and closing tag
		);
		
		if ($this->redirect_uri( ) !== NULL)
		{
// 			Request::current( )->redirect($this->redirect_uri( ));
		}
	}

	// публикуем форму
	public function publish( )
	{
		$message = '';
		
		if (isset($this->message)
			&&
			(
				$this->message_type != Site::config('form')->message_types->error
				||
				$this->form( )->allocate_errors( ) === FALSE
			)
		)
		{
			// формируем сообщение
			$message = View::factory('form_message', array(
				'type' 		=> $this->message_type,
				'message' 	=> $this->message,
			))->render( );
		}

		return $message.$this->form( )->html( );
	}

	// возвращаем имя управляющего элемента
//	protected function get_input_name($id)
//	{
//		$field = ORM::factory('form_field',$id);
//		if (in_array($field->type, array('value', 'optgroup', 'header', 'trigger')))
//		{
//			return;
//		}
//		if ($field->parent != 0)
//		{
//			$name = $this->get_input_name($field->parent).'['.$field->name.']';
//		}
//		else
//		{
//			$name = $this->form( )->label.'['.$field->name.']';
//		}
//
//		return $name;
//	}

	/** get wrapped js validation rule
	 *
	 * @param Form_Rule
	 * @param boolean		flag of validation mode (true if used in form validation context, false -- relation rule)
	 * @return mixed		string OR NULL if rule hasn't js representation
	 */
	protected function get_js_rule(Form_Rule $rule, $is_validation = FALSE)
	{
		InclStream::instance( )->add('jquery.validity.js');
		InclStream::instance( )->add('form.actions.js');

		$func_name = Site::config('form')->relation_func. $rule->id( );

		if ( ! $is_validation)
		{
			/* insert returning of validation results into checking function */
			$this->validation_js[] = "$func_name=function(){\$.v.s();\$('[name=\"". $rule->field( )->name( )."\"]')".$rule->js( )." return \$.v.e( ).valid;};";
		}
		else
		{
			
			/* insert returning of callback execution results instead of returning validation results */
			if ($rule->js( ) != '')
			{
				$repl = array(':field' => $rule->field->label( ));
				
				$key_i = 0;
				
				/* generation of replacements array -- according to Kohana_Validation::errors() */
				foreach ($rule->args( ) AS $arg_key => $arg_val)
				{
					if (in_array(trim($arg_key, ':'), array('obj', 'field')))
					{
						continue;
					}
				
					if (is_array($arg_val))
					{
						// All values must be strings
						$arg_val = implode(', ', Arr::flatten($arg_val));
					}
					elseif (is_object($arg_val))
					{
						// Objects cannot be used in message files
						continue;
					}
					
 					$repl[':param'.++$key_i] = $arg_val;
				}
			
				$error_msg = str_replace('"', '\"', $rule->message( ) !== NULL ? $rule->message( ) : __m('form', $rule->name( ), $repl));
				
				$this->validation_js[] = "$func_name=function(){\$.v.s();\$('[name=\"". $rule->field( )->name( )."\"]')".$rule->js( )." return Form.check('".$rule->name( )."',\$('[name=\"". $rule->field( )->name( )."\"]'), \"{$error_msg}\", \$.v.e( ).valid);};";
				
				if ( ! isset($this->valid_func_arr[$rule->field( )->name( )]))
				{
					$this->valid_func_arr[$rule->field( )->name( )] = array();
				}
				
				$this->valid_func_arr[$rule->field( )->name( )][] = $func_name;
			}
			else
			{
				return NULL;
			}
		}

		return $func_name.'()';
	}

	// возвращаем результат проверки выполнения правила отношения
	// результат записываем в массив $relations
	protected function render_behavior(Form_Behavior $behavior)
	{
		if ($behavior->loaded( ))
		{
			return $behavior;
		}

		if ( ! $this->relation_loaded)
		{
			// process relation rules
			$this->process_relation_rules( );

			// process all behavior objects
			foreach ($this->form( )->behavior_list AS $beh)
			{
				$this->process_beh_cond($beh);
			}

			// mark all relations loaded
			$this->relation_loaded = TRUE;
		}

		// массив действий
		$actions = array(
			'positive' 	=> '',
			'negative'	=> '',
		);

		$relation_mark = $behavior->id( );

		// получаем действия для правила
		foreach ($behavior->actions( ) AS $action)
		{
			// получаем массив аргументов вызываемой функции, если он есть
			$args = '';

			if ($action->args( ) !== NULL)
			{
				$args = ', "'.implode('","', $action->args( )).'"';
			}
			// заполняем массив действий js-кодом
			// прямое действие
			// @now - скорость анимации
			$actions['positive'] .= "$('.{$relation_mark}').".$action->name( )."(now$args);";

			// если пока не понятно, влияет ли правило на валидацию
			// (влияние не задано или сказано, что "вроде как" не влияет)
			if ( $behavior->pass_valid( ) === NULL || $behavior->pass_valid( ) === FALSE)
			{
				// если прикреплённое действие есть в массиве влияющих на валидацию
				// и на данный момент правило соответствует условию отключения валидации из конфига,
				// то валидация отключается
				$behavior->pass_valid($action->valid_impact( ) !== NULL && $action->valid_impact( ) === $behavior->result( ));

			}
			// если в конфиге есть антипод функции (обратное действие),
			// то записываем его в соответствующий массив
			if ($action->antipode( ) !== NULL)
			{
				$actions['negative'] .= "$('.{$relation_mark}').".$action->antipode( )."(now$args);";
			}
		}

		$current_func = $behavior->check_func( );

		// load variables from object properties
		$adjacent_activators = $behavior->adj_act( );
		$cond_js = $behavior->js( );
		$classes = $behavior->_classes( );

// 		$message = "Условие '".$behavior->condition( )."' поля '".$behavior->field->ru_RU( )."'";

// 			$animation_js = <<<HERE
// now = null;
// HERE;
// 		if ($this->form( )->use_animation( ))
// 		{
// 			$animation_js = <<<HERE
// now = now || 0;
// now = now == 1 ? null : 'medium';
// HERE;
// 		}
//
// 		$js = <<<HERE
// function {$current_func}(now, ngt) {
// 	$animation_js
// 	ngt = ngt || false;
// 	if (($cond_js) && !ngt) {
// 		{$actions['positive']}
// 		{$adjacent_activators['positive']}
// HERE;
// 		// если есть функция-антипод
// 		if ($actions['negative'] != '')
// 		{
// 				$js .= <<<HERE
// 	} else {
// 		{$actions['negative']}
// 		{$adjacent_activators['negative']}
// HERE;
// 		}
// 		$js .= <<<HERE
// 	}
// }
// $('{$classes}').bind({
// 	change: {$current_func},
// 	keydown: {$current_func}
// });
// HERE;


			$animation_js = <<<HERE
now = 1;
HERE;
		if ($this->form( )->use_animation( ))
		{
// 		now = now == 1 ? null : 'medium';
			$animation_js = <<<HERE
now = now || 0;
HERE;
		}

		$js = <<<HERE
function {$current_func}(now, ngt) {{$animation_js}ngt = ngt || false;if(($cond_js) && !ngt){{$actions['positive']}{$adjacent_activators['positive']}
HERE;
		// если есть функция-антипод
		if ($actions['negative'] != '')
		{
				$js .= <<<HERE
}else{{$actions['negative']}{$adjacent_activators['negative']}
HERE;
		}
		$js .= <<<HERE
}}$('{$classes}').bind({change:{$current_func},keyup:{$current_func}});
HERE;
		// behavior based on "clear" relation functions only start automaticaly
		if (count(array_intersect($behavior->relations( ), $this->clear_relations)) == count($behavior->relations( )))
		{
			// duration on init = -1!
			$js .= <<<HERE
$current_func(-1);
HERE;
		}

		// save js code to object
		$behavior->js($js);

		// mark this behavior as loaded
		$behavior->loaded(TRUE);

		return $behavior;
	}

	/** Process elemental relation rules
	 *  save result, js code and parameters
	 *
	 *  @return void
	 */
	protected function process_relation_rules( )
	{
		// current language
		$lang = Site::get_language( );

		// fetch relation rules list
		$relations = $this->form( )->relations( );

		foreach ($relations AS $rule_id => $relation)
		{
			/** :KLUDGE: **/
			// костыль -- замещение пустого значения на хэш пустого значения для обхода вечного true при проверке поля, необязательного для заполнения
// 			print_r($this->current_values);
// 				var_dump($this->form( )->values);

			$current_value 	= $relation->field( )->val( ) !== NULL
							? $relation->field( )->val( )
							: Basic::get_hash('');

// 			var_dump($relation->label( ));
// 			var_dump($current_value);
// 			$current_value = isset($this->current_values[$relation->field( )->id]) ? $this->current_values[$relation->field( )->id] : md5('');
			// для корректной обработки multiple-селектов
			// приводим любое значение к массиву
			if ( ! is_array($current_value))
			{
				$current_value = (array) $current_value;
			}
			// дефолтное значение результатов проверки
			$current_value_item_result = FALSE;
			// проверяем значения массива до первого выполнения условия
			// (хотя бы одно из значений должно удовлетворять условию)

			foreach ($current_value AS $current_value_item)
			{
				// получаем значение параметра
				$validation = Validation::factory(array($relation->field( )->id( ) => $current_value_item));

				foreach ($relation->rules( ) AS $rule)
				{
					$validation
					->rule($relation->field( )->id( ), $rule->func( ), $rule->args( ));

					// js выражение для проверки
					$relation->js_expr($this->get_js_rule($rule));
				}

				$current_value_item_result = $validation->check( );

				if ($current_value_item_result)
				{
					break;
				}
			}

			// пишем name элемента в массив классов
			$relation->classes[] = '[name="'.$relation->field( )->name( ).'"]';

			// результат php проверки
			$relation->result($current_value_item_result);

			if ($relation->field( )->beh( ) === NULL)
			{
				$this->clear_relations[] = $rule_id;
			}
		}
	}

	/** Add behavior result item -- for cascade "parent" behaviors
	 *
	 * @param Form_Behavior		behavior object to add
	 * @param boolean 			result
	 *
	 * @return void
	 */
	protected function modify_beh_result(Form_Behavior $beh, $result)
	{
		if ($beh->result( ) === NULL)
		{
			$beh->result($result);
		}
		else
		{
			$beh->result($beh->result( ) && $result);
		}
	}

	/** Calculate behavior condition and write js-actions
	 *
	 * @param Form_Behavior
	 */
	protected function process_beh_cond(Form_Behavior $beh)
	{
		$classes = array( );

		$cond_php = $cond_js = $beh->condition( );

		// replace relation names to boolean values and js function calls
		foreach ($beh->relations( ) AS $elem)
		{
			$cond_php = str_replace($elem, (int) $this->form( )->relations[$elem]->result( ), $cond_php);

			$cond_js = str_replace($elem, $this->form( )->relations[$elem]->js_expr( ), $cond_js);

			$classes = array_merge($classes, $this->form( )->relations[$elem]->classes( ));
		}

		// construct and eval expression to fetch boolean result of condition calculation
// 		var_dump($beh->condition( ));
		$expr = "\$expr_res = (boolean) ($cond_php);";
		eval($expr);

		// формируем строку классов элементов, участвующих в построении правила
		$beh->_classes(implode(',', array_unique($classes)));

		// add current expression result to behavior condition calculation result
		$this->modify_beh_result($beh, $expr_res);

		$beh->js($cond_js);

		// формируем активаторы зависимых правил
		$adjacent_activators = array(
			// ... на случай выполнения текущего правила
			'positive' => array(),
			// ... если текущее правило не выполнено
			'negative' => array(),
		);

		// fetch adjacent behaviors list
		foreach ($this->form( )->behavior_list( ) AS $adj_beh)
		{
			if ($beh !== $adj_beh)
			{
				$chk_func = $adj_beh->check_func( );

				foreach ($beh->fields AS $field_item)
				{
					if (count(array_intersect($adj_beh->relations( ), array_keys($field_item->relations( )))) > 0)
					{
						// add parent expression calculation to child Form_Behavior result property
						$this->modify_beh_result($adj_beh, $beh->result( ));


						if ( ! isset($adjacent_activators['positive'][$chk_func]))
						{
							$adjacent_activators['positive'][$chk_func] = "$chk_func(now);";

							$adjacent_activators['negative'][$chk_func] = "$chk_func(now, true);";
						}
// 						break;
					}
				}
			}
		}

		$adjacent_activators['positive'] = implode('',$adjacent_activators['positive']);
		$adjacent_activators['negative'] = implode('',$adjacent_activators['negative']);

		// save activators to object
		$beh->adj_act($adjacent_activators);

	}
	
	// проверяем данНые формы
	// ! правила можно задавать только для не вложенных элементов
	protected function validate_form( )
	{
		// если форма была отправлена
		if ($this->form->sent( ) === FALSE || $this->form->use_js_validation( ))
		{
			// если валидация выключена, возвращаем FALSE
			if ( ! (bool) $this->form( )->use_validation( ))
			{
				return FALSE;
			}

			// текущий язык
			$lang = Site::get_language( );

			// создаём объект валидации
			$validation = Validation::factory($this->form( )->values( ));
			// очередь сообщений об ошибке
			// используем для вывода сообщений об ошибках поля file в общем списке, а не после всех
			$error_stack = array( );

			foreach ($this->form( )->rules( ) AS $rule)
			{
				// если поле, к которому прикреплено правило, выключено (стоит флаг disabled)
				// пропускаем
				if ( (bool) $rule->field( )->disabled( ))
				{
					continue;
				}

				// если правило показа поля существует и его выполнение согласовано с наклонением прикреплённого действия (! ...),
				// а это действие требует отключения проверки,
				// то валидацию поля пропускаем
				// а значение - анулируем (анулирование пока не сделано)
				if ($rule->field( )->beh( ) !== NULL)
				{
					if ($this->render_behavior($rule->field( )->beh( ))->pass_valid( ))
					{
						continue;
					}
				} 

				// добавляем индивидуальные ошибки для каждого правила
				$this->error($rule);

				// если тип поля -- файл
				// то записываем правило в объект валидации файлов
				if ($rule->field( )->file( ))
				{
					if ( ! isset($validation_files))
					{
						$validation_files = Validation::factory($this->form( )->files);
					}

					$validation_files
						-> label($rule->field( )->id( ), $rule->field( )->label( ))
						-> rule($rule->field( )->id( ), $rule->func( ), $rule->args( ));
				}
				else
				{
					$validation
						-> label($rule->field( )->id( ), $rule->field( )->label( ))
						-> rule($rule->field( )->id( ), $rule->func( ), $rule->args( ));
				}
				
				// заполняем очередь
				$error_stack[] = $rule->field( )->name;
			}

			// render JS rules 
			if ($this->form->use_js_validation( ))
			{
				// если поле, к которому прикреплено правило, выключено (стоит флаг disabled)
				// пропускаем
				if ( isset($rule) && (bool) $rule->field( )->disabled( ) === FALSE)
				{
					foreach ($this->form( )->rules( ) AS $rule)
					{
						$this->get_js_rule($rule, TRUE);
					}
				}
			}
			

			if ($this->form->sent( ) === FALSE)
			{
				// запускаем проверку
				$result_data = $validation->check( );
				$result_files = TRUE;

				if (isset($validation_files))
				{
					$result_files = $validation_files->check( );
				}

				if ($result_data && $result_files)
				{
					return FALSE;
				}
				else
				{
					$errors = $validation->errors('form', Site::get_language( ));
					
					if (isset($validation_files))
					{
						$errors = array_merge($errors, $validation_files->errors('form', Site::get_language( )));
					}

					foreach ($errors AS &$error)
					{
						if (preg_match('/^([^.]+)\.([^.]+)\.([^.:]+)/', $error, $matches))
						{
							$field = $this->form( )->fields($matches[2]);

							foreach ($field->rules( ) AS &$field_rule)
							{
								if (get_class($field_rule) == $matches[3])
								{
									$repl = array( );
									$key_i = 0;
								
									/* generation of replacements array -- according to Kohana_Validation::errors() */
									foreach ($field_rule->args( ) AS $arg_key => $arg_val)
									{
										$arg_key = trim($arg_key, ':');

										if ($arg_key == 'obj')
										{
											$repl[':field'] = $field->label( );
											
											continue;
										}
									
										if (is_array($arg_val))
										{
											// All values must be strings
											$arg_val = implode(', ', Arr::flatten($arg_val));
										}
										elseif (is_object($arg_val))
										{
											// Objects cannot be used in message files
											continue;
										}
										
										$repl[':param'.++$key_i] = $arg_val;
									}
									
									$error = __($field_rule->message( ), $repl);									
								}
							} unset($field_rule);
						}
					} unset($error);
					
					
					// выставляем сообщения в правильном порядке
					$errors = array_merge(array_intersect_key(array_flip(array_unique($error_stack)), $errors), $errors);

					if ($this->form( )->allocate_errors( ) === TRUE)
					{
						$last_key = NULL;
					
						// add errors to fields
						foreach ($errors AS $err_key => $err_msg)
						{
							$msg = $this->form( )->fields($err_key)->message( );
							
							$this->form( )->fields($err_key)->message($err_msg.($msg != '' ? '<br>'.$msg : ''));
							$this->form( )->fields($err_key)->classes(NULL, Site::config('form')->error_field_class_name);
						}
						
						// mark ready fields
						$missing_fields = array_keys($errors);
						
						foreach ($this->form( )->rules( ) AS $rule)
						{
							// check behavior
							if ($rule->field( )->beh( ) !== NULL)
							{
								if ($this->render_behavior($rule->field( )->beh( ))->pass_valid( ))
								{
									continue;
								}
							}
							
							if ( ! in_array($rule->field( )->id( ), $missing_fields))
							{
								/* :KLUDGE:
								 *
								 * 	call message() with space instead of using empty message 
								 * 	for writing of div.form-message without a tag "span" for text (see view form_unit)
								 */
								$rule->field( )->message(' ')->classes(NULL, Site::config('form')->ready_field_class_name);
							}
						}
					}

					return $errors;
				}
			}
		}

		return TRUE;
	}

	// добавляем индивидуальные ошибки для каждого правила, если они есть
	public function error($rule)
	{
		return;
		if ($rule->message( ) !== NULL)
		{
			if ( ! isset(Form_Engine::$errors[$rule->field( )->id( )]))
			{
				Form_Engine::$errors[$rule->field( )->id( )] = array( );
			}
			
			Form_Engine::$errors[$rule->field( )->id( )][$rule->func( )] = $rule->message( );
		}
	}

	public static function specified_errors( )
	{
		return self::$errors;
	}

	// выдача данных обработчику
	public function process_data( )
	{
		// для передачи данных обработчику
		$this->form( )->data(array_merge($this->form( )->data( ), $this->form( )->files( )));
	}
	
	
	/**
	 * Set custom message 
	 * @param string $message	message
	 * @param string $type		type of message (success or error)
	 */
	public function message($message, $type = NULL)
	{
		$this->message = $message;
		if (isset($type))
		{
			$this->message_type = $type;
		}
	}
}
