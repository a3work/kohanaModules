<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * @package    Kohana/Personal
 * @author     A. St.
 */
class Kohana_Model_Attributes extends ORM {

	protected $_table_name = 'user_attributes';

	protected $_belongs_to = array(
		'account' => array('model' => 'account', 'foreign_key' => 'user_id'),
	);

}