<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Form element behavior action
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-26
 *
 **/

class Form_Action_Enable extends Form_Action
{
	// action antipode
	protected $antipode = 'disable';

	// action impact to (switch off) validation
	protected $valid_impact = FALSE;
}