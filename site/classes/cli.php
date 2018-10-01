<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		CLI helper
 * @package 	Site
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-11-04
 *
 * @usage		Cli::exec(Route::url('cli_test'), array('id' => 'testing param', 'data' => '123&!@#$%^&*', 'testing'=>'???////\\'), 'testing_tag');
 *
 **/

class CLI extends Kohana_CLI
{
	// basic states
	const STATUS_INIT	= 'init';
	const STATUS_START	= 'start';
	const STATUS_DONE	= 'done';
	const STATUS_PROCESS= 'processing';
	const STATUS_ERROR	= 'error';
	const STATUS_CANCEL	= 'canceled';
	const STATUS_DEAD	= 'dead';

	// max waiting time of execution starting, seconds
	const WAIT_TIME = 20;

	// separator of id for state func
	const IDS_SEPARATOR	= ',';

// 	// current cli-process holder
	protected static $_instance;

	// ORM object of current pid
	protected $_orm;

	// script param
	protected $_params = array( );

	/** Object constructor
	 *
	 * @return void
	 */
	protected function __construct( )
	{

	}


	/**
	 * Standart setter/getter
	 *
	 * @param string 	variable name
	 * @param array		parameters
	 *
	 * @return mixed
	 */
	public function __call($var, $args = NULL)
	{
		if (isset($args[0]))
		{
			$this->_orm( )->$var = $args[0];

			return $this;
		}
		else
		{
			return $this->_orm( )->$var;
		}
	}

	/** Check CLI-process
	 *
	 * @return	boolean
	 */
	public static function check( )
	{
		return Kohana::$is_cli;
	}

	/** Check CLI-process
	 *
	 * @param 	string	chain name
	 * @return	Cli		first executed process
	 */
	public static function chain_exec($chain)
	{
		$orm = ORM::factory('cli_process')
				->where('chain', '=', $chain)
				->where('pid', 'IS NOT', NULL)
				->where('pid', '=', 0)
				->order_by('id')
				->find( );

		$cli = NULL;
				
		if ($orm->loaded( ))
		{
			$cli = Cli::load_by_orm($orm)->exec( );
		}
		
		return $cli;
	}

	/** Check process existence
	 *
	 * @return	boolean
	 */
	public function is_alive( )
	{
		$result = FALSE;

		// process not execute
		if ( ! $this->is_exec( ))
		{
			return FALSE;
		}

		// process initialized but not started
		if ($this->pid( ) == 0)
		{
			// process can wait WAIT_TIME seconds
			// or process can wait end of similar process execution
			if ($this->chain( ) != '')
			{
				// check execution of similar process
				return Cli::check_chain($this->chain( )) !== FALSE;
			}
			else
			{
				// check live time
				return (time( ) - strtotime($this->ctime( ))) < Cli::WAIT_TIME;
			}
		}

		try
		{
			if (Kohana::$is_windows)
			{
				// :TODO:
			}
			else
			{
// 				$result = file_exists('/proc/'.$this->pid( ));
				$result = TRUE;
			}
		}
		catch (Exception $e)
		{
			// :TODO: 
		
			return TRUE;
		}

		return $result;
	}

	/** Get process execution state
	 *
	 * @return boolean
	 */
	public function is_exec( )
	{
		return $this->_orm( )->pid !== NULL;
	}

	/**
	 * Current process getter -- for CLI usage only
	 *
	 * @return Kohana_CLI or (boolean) FALSE
	 */
	public static function instance( )
	{
		// if not cli process -- exit
		if ( ! Cli::check( ))
		{
			return FALSE;
		}

		if ( ! isset(self::$_instance))
		{
			// add cli process object and define pid
			self::$_instance = Cli::load_by_pid(getmypid( ));
		}

		return self::$_instance;
	}

	/** Process factory
	 *
	 * @return CLI
	 */
	public static function factory( )
	{
		return new Cli;
	}

	/** Load process by PID
	 *
	 * @param 	integer	PID
	 * @return 	Cli or (boolean) FALSE
	 */
	public static function load_by_pid($pid)
	{
		$orm = ORM::factory('cli_process')->where('pid', '=', $pid)->find( );

		if ($orm->loaded( ))
		{
			return Cli::factory( )->orm($orm);
		}
		else
		{
			return FALSE;
		}
	}

	/** Load process by tag
	 *
	 * @param 	string	tag
	 * @return 	Cli or (boolean) FALSE
	 */
	public static function load_by_tag($tag)
	{
		$orm = ORM::factory('cli_process')->where('tag', '=', $tag)->order_by('id', 'desc')->limit(1)->find( );
		if ($orm->loaded( ))
		{
			return Cli::factory( )->orm($orm);
		}
		else
		{
			return FALSE;
		}
	}

	/** Load process by id
	 *
	 * @param 	integer	id
	 * @return 	Cli or (boolean) FALSE
	 */
	public static function load_by_id($id)
	{
		$orm = ORM::factory('cli_process', $id);

		if ($orm->loaded( ))
		{
			return Cli::factory( )->orm($orm);
		}
		else
		{
			return FALSE;
		}
	}


	/** Load process by Model_Cli_Process
	 *
	 * @param 	Model_Cli_Process
	 * @return 	Cli
	 */
	public static function load_by_orm(Model_Cli_Process $orm)
	{
		return Cli::factory( )->orm($orm);
	}

	/** Clear cli task parameter
	 *
	 * @param 	string	param
	 * @return 	string
	 */
	public static function clear($param)
	{
		return str_replace(array('\\', '!', '@', '#', '$', '%', '^', '&', '?', '~', '*'), '', str_replace(' ', '\ ', $param));
	}


	/** add parameters
	 *
	 * @param	array	parameters
	 * @return 	string	home
	 */
	public function param(array $param = NULL)
	{
		if (isset($param))
		{
			// create command parameters
			$out = array();

			foreach ($param AS $key=>$value)
			{
				$out[] = "--$key=\"".Cli::clear($value).'"';
			}

			$this->_orm( )->param = implode(' ', $out);

			return $this;
		}
		else
		{
			return $this->_orm( )->param;
		}
	}

	public function clear_pid( )
	{
		$this->_orm( )->pid = DB::expr('NULL');

		return $this;
	}

	/** Execute task
	 *
	 * @return 	string	home
	 */
	public function exec( )
	{
		try
		{
			// check task existence
			if ($this->task( ) == '')
			{
				throw new Cli_Exception('Command is empty');
			}

			if ($this->tag( ) != '' && $this->chain( ) != '')
			{
				throw new Cli_Exception('You must set either tag or chain.');
			}

			if ( ! $this->_orm( )->id > 0)
			{
				$this->save( );
			}
		}
		catch (Cli_Exception $e)
		{

			Kohana::$log->add(
				Log::NOTICE,
				"Cannot start task :task: :message",
				array(
					':task' => $this->task( ).' '.$this->param( ),
					':message' => $e->getMessage( ),
				)
			);

			return FALSE;
		}

		if (Kohana::$is_windows)
		{
			// :TODO:
		}
		else
		{
			$init_task = Site::config('site')->cli_command.' '.DOCROOT.'index.php --uri='.Route::url('cli', array('action' => 'exec', 'task' => $this->id( ))).' '.$this->param( ).' > /dev/null &';
// 			var_dump($init_task);
			exec($init_task);
		}

		return $this;
	}

	/** Check existence of process with specified tag
	 *
	 * @param	string	tag
	 * @return 	boolean
	 */
	public static function check_tag($tag)
	{
		$orm = ORM::factory('cli_process')
				->where('tag', '=', $tag)
				->where('pid', 'IS NOT', NULL)
				->find( );

		if ($orm->loaded( ))
		{
			$result = Cli::load_by_orm($orm)->is_alive( );
		}
		else
		{
			$result = FALSE;
		}

		Kohana::$log->add(
			Log::DEBUG,
			'result: '.(int) $orm->loaded( )."\nquery: ".$orm->last_query( ). "\nreturn: ".(int) $result
		);

		return $result;
	}

	/** Check existence of process with specified chain
	 *
	 * @param	string	chain
	 * @return 	mixed	(object) CLI or (boolean) FALSE
	 */
	public static function check_chain($chain)
	{
		$orm = ORM::factory('cli_process')
				->where('chain', '=', $chain)
				->where('pid', '>', 0)
				->find_all( );

		$result = FALSE;

		if (count($orm) > 0)
		{
			foreach ($orm AS $proc)
			{
				$cli = Cli::load_by_orm($proc);

				if ($cli->is_alive( ))
				{
					$result = $cli;

					break;
				}
				else
				{
					$cli->dead( );
				}
			}
		}

		return $result;
	}

	/** Init and return orm or set already initialized ORM object
	 *
	 * @param	Model_Cli_Process
	 * @return 	Model_Cli_Process
	 */
	protected function _orm($orm = NULL)
	{
		if (isset($orm))
		{
			if ( ! $orm instanceOf Model_Cli_Process)
			{
				throw new Cli_Exception('Cannot load ORM: invalid type of object');
			}

			$this->_orm = $orm;

			return $this;
		}

		if ( ! isset($this->_orm))
		{
			$this->_orm = ORM::factory('cli_process')->values(array(
				'stime' => DB::expr('NOW( )'),
			));
		}

		return $this->_orm;
	}

	/** Init and return or set already initialized ORM object
	 *
	 * @param	Model_Cli_Process
	 * @return 	Model_Cli_Process
	 */
	public function orm($orm = NULL)
	{
		return $this->_orm($orm);
	}

	/** Return process id
	 *
	 * @return 	int
	 */
	public function id( )
	{
		return $this->_orm( )->id;
	}

	/** Save current state to DB
	 *
	 * @return	CLI
	 */
	public function save( )
	{
		// execute
		// - if tag is not empty and process with this tag isn't execute now
		// - if chain is not empty and processes of that chain don't start now
		if ( ! $this->_orm( )->id > 0 && $this->tag( ) != '' && Cli::check_tag($this->tag( )))
		{
			throw new Cli_Exception('Process with specified tag :tag is already execute.', array(':tag' => $this->tag( )));
		}
		elseif ( ! $this->_orm( )->id > 0 && $this->chain( ) != '' && Cli::check_chain($this->chain( )) !== FALSE)
		{
			throw new Cli_Exception('Process of specified chain :chain is already execute.', array(':chain' => $this->chain( )));
		}

		$data = array(
			'memory_usage' => memory_get_usage(TRUE),
		);

		if ( ! $this->_orm( )->id > 0)
		{
			$data['pid'] = 0;
			$data['status'] = CLI::STATUS_INIT;
		}

		$this->_orm( )->values($data)->save( );

		return $this;
	}

	/** Reload current orm from DB
	 *
	 * @return CLI
	 */
	public function reload( )
	{
		if ($this->_orm( )->id > 0)
		{
			$this->_orm( )->reload( );
		}

		return $this;
	}

	/** Cancel process execution
	 *
	 * @return boolean
	 */
	public function cancel( )
	{
		// if proc already dead return 0
		if ( ! $this->is_exec( ))
		{
			return FALSE;
		}

		if (Kohana::$is_windows)
		{
			/** FROM http://php.net/manual/ru/function.posix-kill.php **/
			$wmi = new COM("winmgmts:{impersonationLevel=impersonate}!\\\\.\\root\\cimv2");
			$procs = $wmi->ExecQuery("SELECT * FROM Win32_Process WHERE ProcessId='".$this->_orm( )->id."'");

			foreach($procs as $proc)
			{
				$proc->Terminate();
			}

		}
		else
		{
			exec('kill '.$this->_orm( )->pid);
		}

		$this->status(CLI::STATUS_CANCEL)->clear_pid( )->save( );

		return TRUE;
	}

	/** Mark process as dead
	 *
	 * @return this
	 */
	public function dead( )
	{
		$this->status(CLI::STATUS_DEAD)->clear_pid( )->save( );

		return $this;
	}

	/** Get process state
	 *
	 * @return array
	 */
	public function state( )
	{
		if ($this->is_exec( ) && ! $this->is_alive( ))
		{
			$this->dead( );
		}

		return array(
			'pid'		=> $this->_orm( )->pid,
			'status' 	=> __u($this->_orm( )->status),
			'mem' 		=> $this->_orm( )->memory_usage,
			'processed'	=> $this->_orm( )->processed.'&nbsp;'.__('processed'),
			'progress'	=> $this->_orm( )->progress,
			'name'		=> $this->_orm( )->name,
			'comment'	=> $this->_orm( )->comment,
			'alive'		=> $this->is_exec( ),
			'is_final'	=> (boolean) in_array($this->_orm( )->status, Site::config('site')->cli_final_states),
		);
	}

	/** Get html visualization
	 *
	 * @return string
	 */
	public function html( )
	{
		InclStream::instance( )->add('cli.proc.js');
		InclStream::instance( )->add('cli.proc.css');

		$out = View::factory('cli.process', array(
			'id' => $this->_orm( )->id,
		));

		return $out;
	}
}
