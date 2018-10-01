<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	// заголовок роута меню
	'route_header' => 'form',
	// название переменной-активатора
	'activator_var' => 'activate',
	'activator_var_length' => 6,
	// типы сообщений
	'message_types' => (object) array(
		'error' => 'error',
		'success' => 'success',
	),
	// название классов полей
	'input_class_name' => '_ff',
	// название класса-отметки отношения
	'relation_mark' => '_fb',
	// название функций-правил отношений
	'relation_func' => '_fr',
	// название функций проверки отношений
	'relation_check_func' => '_fc',
	// класс поля с ошибкой
	'error_field_class_name' => 'missing',
	// класс заполненного поля
	'ready_field_class_name' => 'done',
	// функции и их антиподы
	'antipode_actions' => array(
		'show' 					=> 'hide',
		'enable' 				=> 'disable',
		'check'					=> 'uncheck',
		'make_editable' 	=> 'make_readonly',
		'val'						=> '',
	),
	// массив функций, влияющих на отключение валидации подчинённого поля
	// @key: название функции
	// @value: позитивная или негативная функции
	// пример: если к правилу приписана функция hide, то при выполнении этого правила проверка отключается
	// пример: если к правилу приписана функция enable, то при НЕвыполнении этого правила проверка отключается
	'no_validate_actions' => (object) array(
		'show'		=> FALSE,
		'hide'		=> TRUE,
		'enable'	=> FALSE,
		'disable'	=> TRUE,
	),
	// название дефолтных шаблонов формы
	'templates' => (object) array(
		// строка формы
		'unit' => 'form_unit',
		// форма
		'form' => 'form_body',
		// закрывающий тег формы
		'form_end' => 'form_body_end',
	),
	'form_css' => 'form.css',
	// размер одного символа для расчёта ширины фиксированных по размеру полей
	'sign_width' => 11,
	// fields count for animation switch off
	'animation_count' => 20,

	/** Настройки кэширования **/
	// выключатель кэширования
	'cache_enable' => TRUE,
	// драйвер
	'cache_driver' => 'file',
	// время жизни кэша
	'cache_lifetime' => 3600, 	// 1 час

	// минимальное количество элементов в дереве для включения ajax-режима
	'tree_ajax_minimum_count' => 20,
	// переменная подкачки деревьев
	'session_tree_swap_var' => 'tree_swap',

	// директивы управления выводом
	'directions' => array(
		'tree_output_filter' => 'tree_output_filter',
	),

	// название формы по-умолчанию
	'default_form_name' => 'f',

	// cannot use this words in field labels
	'reserved_words' => array(
		'label', 'guest', 'name'
	),

	// settings of fetching field options from base
	'db_opt_header' => 'header',
	'db_opt_key' => 'key',
	
	// default date format
	'default_date_format' => 'YYYY-mm-dd',
);