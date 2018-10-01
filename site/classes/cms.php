<?php defined('SYSPATH') or die('No direct script access.');

class CMS extends Kohana_CMS {

	// parent method will be executed instead of method of subclass 
	const EXEC_PARENT_FLAG = 'parent';

	// viewing modes
	const VIEW_MODE_FULL 	= 'full';
	const VIEW_MODE_SIMPLE 	= 'simple';
	
}
