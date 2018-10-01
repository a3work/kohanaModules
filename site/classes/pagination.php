<?php defined('SYSPATH') or die('No direct script access.');

class Pagination extends Kohana_Pagination {

	// number of rows per page
	const ROWS_PER_PAGE = 20;
	// page variable in query string
	const PAGE_QUERY_VAR = 'p';
}