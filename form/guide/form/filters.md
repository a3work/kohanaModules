# Filter generation

There is a simple example of news filter:
~~~
	// init ORM instance
	$orm = ORM::factory('news');
	
	// create and output filter form
	echo Form::factory('filter')
		
		// add filter name, will be used in spoiler switch
		->filter_name('my filter')
		
		/* add fields */
		->field('text', 'query', 'query')
			->min_length(3)
			
		->field('checkbox', 'show archived news', 'show_archived')
		
		->field('checkbox_group', 'group', 'groups')
			->options(array('General', 'Finance', 'Political'), TRUE)
			
		->field('submit', 'Find')
		
		/* add callbacks */
		// for query
		->callback('query', function($value, $orm) {
			$orm
				->where('name', 'LIKE', DB::expr('"%'.$value.'%"'));
		}, array('orm' => $orm))
		
		// for checkbox
		->callback('show_archived', function($value, $orm) {
			$orm
				->where('is_archived', '=', '1');
		}, array('orm' => $orm))
		
		// for checkbox group
		->callback('groups', function($value, $orm) {
			if (count($value) > 0)
			{
				$orm
					->where('group', 'IN', '("'.DB::expr(implode('","', $value)).'")');
			}
		}, array('orm' => $orm))
		->render( );
~~~
