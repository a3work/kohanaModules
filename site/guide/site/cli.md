# Run task using cli and output a loading bar
 
First you need to initialize cli task:
~~~
	$cli = Cli::factory( )
			->name('testing task ')
			->param(array(
				'param0' => 'foo',
				'param1' => 'bar',
			))
			->task(Route::url('testing_route', array('action' => 'testing')))
			->exec();
~~~

Then you can show loading bar:
~~~
	echo $cli;
~~~
 
# Run chain of tasks
~~~
	// define chain name
	$chain = 'testing_chain';

	// add task to chain
	$cli = Cli::factory( )
			->chain($chain)				
			->name('testing task 0')
			->param(array(
				'param0' => 'foo',
				'param1' => 'bar',
			))
			->task(Route::url('testing_route_0', array('action' => 'testing')))
			
			// then save task instead of execution
			->save();
			
	// add another task to chain
	$cli2 = Cli::factory( )
			->chain($chain)				
			->name('testing task 1')
			->param(array(
				'param0' => 'foo',
				'param1' => 'bar',
			))
			->task(Route::url('testing_route_1', array('action' => 'testing')))
			
			// then save task instead of execution
			->save();
			
	// execute chain: run testing task 0 and then testing task 1
	Cli::chain_exec($chain);
~~~
 
# Add tag to task
~~~
$cli = Cli::factory( )
			->tag($tag)
			->name('testing task')
			->param(array(
				'foo' => 'bar'
			))
			->task(Route::url('testing_route', array('action' => 'testing')))
			->exec( );
~~~


# Set up a state of execution (progress, state label, comments)
Use simple code in your cli-task:
~~~
public function action_testing( )
{
	$arr = array(
		1,
		2,
		3
		// ... 
	);

	$count ++;
	foreach ($arr AS $val)
	{
		// ... 
	
		Cli::instance( )
			->status(Cli::STATUS_PROCESS)
			->comment('All\' right')
			->processed(++$count)
			->progress($count/count($arr)*100)
			->save( );
			
		sleep(2);
	}
}
~~~