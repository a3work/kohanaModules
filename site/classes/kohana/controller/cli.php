<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 

 
###Run task using cli and output a loading bar
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
 
###Run chain of tasks
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
 
###Add tag to task
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


###Set state of execution (progress, state label, comments)
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

 
 * @name		controller for execution of CLI
 * @package 	Files
 * @author 		Stanislav U. Alkimovich
 * @date 		2015-04-07
 *
 **/
 
class Kohana_Controller_Cli extends Controller
{
	/** Execute wrapped task
	 *
	 * @return 	void
	 */
	public function action_exec( )
	{
		$task = trim($this->request->param('task'));

		// load by id
		if (preg_match('/^\d+$/', $task))
		{
			if (($process = Cli::load_by_id($task)) === FALSE)
			{
				throw new Cli_Exception('Cannot load process :id', array(':id' => $task));
			}
		}
		else
		// create new task
		{
			$process = Cli::factory( )
						->task($task)
						->param($_SERVER['argv']);
		}

		// set start state
		$process
			->status(CLI::STATUS_START)
			->pid(getmypid( ))
			->processed(0)
			->save( );

		try
		{
			// force login user CLI
			User::instance( )->force_login(Site::config('user')->cli_name);
		
			// execute command
			Request::factory($process->task( ))->execute( );

			// save end state to DB
			$process
				->reload( )
				->status(CLI::STATUS_DONE)
				->clear_pid( );

/*
			Kohana::$log->add(
				Log::DEBUG,
				"PROGRESS: ".$process->progress( )
			);*/

			Kohana::$log->write( );
			
			if ($process->progress( ) !== NULL)
			{
				$process->progress(100);
			}

		}
		catch (Exception $e)
		{
			// save error state
			$process
				->status(CLI::STATUS_ERROR)
				->clear_pid( )
				->comment(print_r($e, TRUE));
				
			Kohana::$log->add(
				Log::ERROR,
				$e->getMessage( ).' in '.$e->getFile( ).' ('.$e->getLine( ).')',
				array(
				)
			);
			
			Kohana::$log->write( );

		}

		if ($process->chain( ) != '')
		{
			// execute next process of current chain
			Cli::chain_exec($process->chain( ));
		}

		// save final state of current process
		$process->save( );
	}

	/** Action: kill process by ID **/
	public function action_cancel( )
	{
		$id = (int) $this->request->param('task');

		$result = FALSE;

		$cli = CLI::load_by_id($id);

		if ($cli !== FALSE)
		{
			$result = $cli->cancel( );
		}

// 		$this->response->body((int) $result);
		$this->request->redirect($this->request->referrer( ));
	}

	/** Action: get state of processes **/
	public function action_state( )
	{
		if ( ! preg_match('/^[0-9 '.Cli::IDS_SEPARATOR.']+$/', trim($this->request->query('ids'), Cli::IDS_SEPARATOR)))
		{
			return FALSE;
		}

		$list = ORM::factory('cli_process')->where('id', 'IN', DB::expr('('.trim($this->request->query('ids')).')'))->find_all( );

		$out = array( );

		foreach ($list AS $proc)
		{
			$proc = Cli::load_by_orm($proc);

			$out[$proc->id( )] = $proc->state( );
		}

		$this->response->body(Basic::json_safe_encode($out));
	}



	public function action_test( )
	{
		$args = CLI::options('id', 'data', 'testing');

		Kohana::$log->add(
			Log::INFO,
			"Testing task executed successfuly. PID: :pid. Parameters: :param",
			array(
				':pid' => Cli::instance( )->pid( ),
				':param' 	=> print_r($args, TRUE),
			)
		);

		Kohana::$log->write( );

		$end = 6;

		for ($i = 0; $i < $end; $i ++)
		{
			Cli::instance( )
				->status('process')
				->comment(print_r($args, TRUE))
				->processed($i * 123)
				->progress(ceil($i / $end * 100))
				->save( );


// 			sleep(1);
		}
	}

	public function action_demo( )
	{
// 		Cli::exec(Route::url('cli', array('action' => 'test')), NULL, 'testing_tag');

		$chain = 'testing_chain';

		$out = '';

		$cli = Cli::factory( )
			->tag($chain)
			->name('Тестовая генерация (с тэгом)')
			->param(array('id' => '728392'))
			->task(Route::url('cli', array('action' => 'test')))
			->exec( );

		$cli = Cli::factory( )
			->name('Тестовая генерация (простая)')
			->param(array('id' => '728392'))
			->task(Route::url('cli', array('action' => 'test')))
			->exec( );

		if ( ! Cli::check_chain($chain))
		{
			for ($i = 1; $i < 10; $i ++)
			{
				$cli = Cli::factory( )
					->chain($chain)
					->name('Тестовая генерация (цепочная)')
					->param(array('id' => '728392'))
					->task(Route::url('cli', array('action' => 'test')))
					->save( );
			}

			Cli::chain_exec($chain);
		}


		foreach (ORM::factory('cli_process')->where('pid', 'IS NOT', NULL)->order_by('id', 'desc')->find_all( ) AS $item)
		{
			$out .= Cli::load_by_orm($item)->html( );
		}

		$this->response->body($out);
	}
}