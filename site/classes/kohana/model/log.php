<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Модель для обработки лога ошибок
 *
 * @package    system
 * @author Maxim Nagaychenko maxnag[at]meta.ua
 * @license
 */
 
class Kohana_Model_Log extends ORM {
	
	protected $_table_name = 'site_logs';
	
	protected $_belongs_to = array(
		'account' 	=> array ('model' => 'account','foreign_key' => 'user_id'),
	);
}