<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Stream of includes for including js and css files
 * @package 	inclStream
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-01
 *
 * :FIXME: doubling of including code of same files with different priority
 *
 **/

class Kohana_InclStream
{
	/**
	 * @const		includes cache tag
	 */
	const CACHE_TAG = 'include_stream';

	/**
	 * @const		main config name
	 */
	const MAIN_CONFIG_NAME = 'Cfg';

	/**
	 * @var	array	default priority for files
	 */
	protected $_priority_increase = 50;

	/**
	 * @var	array	includes code array
	 */
	protected $_includes = array( );

	/**
	 * @var	array	array of files
	 */
	protected $_files = array( );

	/**
	 * @var	array	stream of code text
	 */
	protected $_stream = array(
		'js' 	=> '',
		'css' 	=> '',
	);
	
	/**
	 * @var	array	supporting types
	 */
	protected $_supporting_types = array('js', 'less', 'css');

	/**
	 * @var	array	global config for js variables
	 */
	protected $_config = array( );
	
	/**
	 * @var	array	instance of class
	 */
	protected static $_instance;

	/**
	 * Singleton factory
	 *
	 * @return object
	 */
	public static function instance( )
	{
		if ( ! isset(self::$_instance))
		{
			self::$_instance = new InclStream;
		}

		return self::$_instance;
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
		if (is_array($args) && count($args) > 0)
		{
			$this->$var = $args[0];
		}

		return $this->$var;
	}

	/**
	 * Adding html / css file
	 *
	 * @param string 		filename
	 * @param boolean 		add force refresh code
	 * @param integer 		priority [-39, 49]
	 * @param type	 		filetype -- fetch from filename if null
	 * @return boolean 		FALSE -- including file already exists in includes array
	 * 						TRUE  -- include successfuly
	 */
	public function add($file, $force_refresh = FALSE, $priority = 0, $type = NULL)
	{
		if (isset($this->code))
		{
			throw new InclStream_Exception('Cannot add file: it is already rendered.');
		}
	
		if ($file == '')
		{
			return;
		}

		// recoursive adding file from array
		if (is_array($file))
		{
			foreach ($file AS &$file_item)
			{
				$this->add($file_item, $force_refresh, $priority, $type);
			}
			unset ($file_item);

			return TRUE;
		}
		$priority += $this->_priority_increase;

		if ( ! isset($type))
		{
			// get file extension
			$type = pathinfo($file, PATHINFO_EXTENSION);
		}

		if (in_array($file, array_keys($this->_files)))
		{
			// change priority
			if ($priority != $this->_files[$file]['priority'] && $this->_files[$file]['priority'] == $this->_priority_increase)
			{
				$new_key = $priority.'_'.$file;
				$old_key = $this->_files[$file]['priority'].'_'.$file;

				$this->_files[$file]['priority'] = $priority;

				$this->_includes[$type][$new_key] = $this->_files[$file]['code'];

				unset($this->_includes[$type][$old_key]);

				$this->_files[$file]['key'] = $new_key;
			}

			return FALSE;
		}

		$include_key = $priority.'_'.$file;

		// add force refresh parameter
		$force_refresh_code = $force_refresh ? '?'.time() : '';

		if (strpos($file, 'http') === FALSE)
		{
			// directory for search
			$dir = $type;

			// check path
			if (strpos($file, '/') !== FALSE)
			{
				$file_arr = explode('/', $file);
				$file = array_pop($file_arr);
				$dir = implode('/', $file_arr);
			}

			$file = Route::url('static_files', array('filetype'=>$dir, 'file'=>$file));
		}
		

		// if script/link tag already created skip creation
		if (preg_match('/^<script/', $file))
		{
			$code = $file;
			$type = strpos($file, '.js') ? 'js' : 'css';
		}
		else
		{
			// fetch code according to extension
			switch ($type)
			{
				case 'js':
					$code = "<script type='text/javascript' src='".$file."{$force_refresh_code}'></script>";
					break;
				case 'css':
					$code = "<link rel='stylesheet' type='text/css' href='".$file."{$force_refresh_code}'>";
					break;
				case 'less':
// 						$code = "<link rel='stylesheet' type='text/css' href='".$file."{$force_refresh_code}'>";
					$code = "<link rel='stylesheet/less' type='text/css' href='{$file}{$force_refresh_code}'>";
					InclStream::instance( )->add('http://cdnjs.cloudflare.com/ajax/libs/less.js/1.7.3/less.min.js');
					break;
			}
			
			if (IN_PRODUCTION && InclStream::BACKTRACE)
			{
				$backtrace = debug_backtrace( );
				
				$this->_files[$file]['called'] = array( );
				$code = '<!-- '.$backtrace[0]['file'].':'.$backtrace[0]['line']."-->\n".$code;
			}
		}
		/*
		if ( ! isset($this->_includes[$type]))
		{
			$this->_includes[$type] = array( );
		}

		// insert in stream
		$this->_includes[$type][$include_key] = array(
			'code' => $code,
			'file' => $file,
		);*/

		// insert in files array for future check
		$this->_files[$file] = array(
			'type' => $type,
			'path' => $file,
			'priority' 	=> $priority,
			'code'		=> $code,
		);
	}

	/**
	 * Add code text
	 *
	 * @param string 	code
	 * @param string	type
	 * @return boolean
	 */
	public function write($code, $type = 'js')
	{
		if (isset($this->code))
		{
			throw new InclStream_Exception('Cannot write code: it is already rendered.');
		}
	
		$this->_stream[$type] .= $code;

		return TRUE;
	}
	
	/** 
	 * Write config, optionaly filteren by first letters of keys ("group")
	 *
	 * @param 	string	config name
	 * @return 	this
	 */
	public function config_file($config, $group = NULL)
	{
		$config = Site::config($config);
		$out = array();
		
		if (isset($group))
		{
			foreach ($config AS $key => $value)
			{
				if (strpos($key, $group) === 0)
				{
					$out[$key] = $value;
				}
			}
		}
		else
		{
			$out = (array) $config;
		}
		
		$this->config($out);
		
		return $this;
	}

	/**
	 * Get including code
	 *
	 * @return string		including code
	 */
	public function render( )
	{
		if ( ! isset($this->code))
		{
			$this->code = '';
			
			$multisort_determ = array();
			
			foreach ($this->_files AS $path => &$file)
			{
				$multisort_determ[$path] = $file['priority'];
			}
			unset($file);
			
			array_multisort($multisort_determ, SORT_DESC, SORT_NUMERIC, $this->_files);
			
			$includes = $this->_files;
			
			$output = $output_code = array(
				'js' => '',
				'css' => '',
				'less' => '',
			);
			
			if (Kohana::$caching === TRUE && Site::config('inclStream')->catenation_mode)
			{
				$hash = Basic::get_hash(implode('',array_keys($this->_files)));
				$cache_values = Cache::instance( )->get($hash);
				if ($cache_values === NULL)
				{
					foreach ($this->_files AS $file_key => &$file_attr)
					{
						$file = $file_attr['path'];
					
						if (preg_match('/(?:js|css)\/(.+)$/', $file, $matches))
						{
							$file = $matches[1];
						}
						
						$file = Kohana::find_file('media/'.$file_attr['type'], str_replace('.'.$file_attr['type'], '', $file), $file_attr['type']);
						
						if ($file !== FALSE)
						{
							// get file and save
							ob_start( );
							
							include $file;
							
							$body = ob_get_contents( );
							ob_end_clean( );
							
							$output_code[$file_attr['type']] .= $body."\n";
							
							unset($includes[$file_key]);
						}
					}
					
					$cache_values = (object) array(
						'modify_time' => time( ),
						'body' => $output_code,
						'unprocessed' => $includes,
					);
					
					Cache::instance( )->set_with_tags($hash, $cache_values, NULL, array(Kohana_InclStream::CACHE_TAG));
				}
				else
				{
					$includes = $cache_values->unprocessed;
				}
					
					
				$force_refresh_code = '';
					
				foreach ($output AS $type => &$value)
				{
					// fetch code according to extension
					switch ($type)
					{
						case 'js':
							$output[$type] .= "<script type='text/javascript' src='".Route::url('includes', array('type' => $type, 'id' => $hash))."{$force_refresh_code}'></script>";
							break;
						case 'css':
							$output[$type] .= "<link rel='stylesheet' type='text/css' href='".Route::url('includes', array('type' => $type, 'id' => $hash))."{$force_refresh_code}'>";
							break;
					}
				}
			}
			
			if (count($includes) > 0)
			{
				foreach ($includes AS &$item)
				{
					$output[$item['type']] .= "\n".$item['code'];
				}
			/*
				if (isset($includes['js']) && count($includes['js']) > 0)
				{
	// 				var_dump($includes['js']);
					krsort($includes['js']);
	// 				echo "\n----\n";
	// 				var_dump($includes['js']);
					$this->code .= implode("\n", $includes['js']);
				}

				if (isset($includes['less']) && count($includes['less']) > 0)
				{
					krsort($includes['less']);
					$this->code .= implode("\n", $includes['less']);
				}

				if (isset($includes['css']) && count($includes['css']) > 0)
				{
					krsort($includes['css']);
					$this->code .= implode("\n", $includes['css']);
				}
				*/
			}
			
			$this->code = implode('', $output);
			
			if (count($this->_config) > 0)
			{
				$cfg_name = Kohana_InclStream::MAIN_CONFIG_NAME;
				$config = Basic::json_safe_encode($this->_config);
				$this->code .= "<script type='text/javascript'>{$cfg_name}={$config}</script>";
			}
			
			if ($this->_stream['js'] != '')
			{
				$this->code .= "<script type='text/javascript'>$(document).ready(function() {\n".$this->_stream['js']."\n});</script>";
			}

			if ($this->_stream['css'] != '')
			{
				$this->code .= '<style type="text/css">'.$this->_stream['css'].'</style>';
			}
		}

		return $this->code;
	}

	
	/** Include jquery
	 * 
	 * @return	void
	 */
	public function jquery( )
	{
		InclStream::instance( )->add('jquery-1.11.0.min.js', NULL, 11);
		InclStream::instance( )->add('jquery.browser.min.js', NULL, 10);
	}
	
	/** Include jquery ui
	 * 
	 * @return	void
	 */
	public function jqueryui( )
	{
		InclStream::instance( )->add('jquery-ui-1.11.0.custom.min.js', NULL, 9);
		InclStream::instance( )->add('jquery-ui-1.11.0.custom.min.css', NULL, 9);
		InclStream::instance( )->add('jquery.ba-outside-events.js');
	}
	
	/** Write values of variables into JS config
	 * 	
	 * 	This can be used for transporting values of variables into javascript.
	 * 	All variables will be written into js-array named as Kohana_InclStream::MAIN_CONFIG_NAME
	 *
	 * @param 	array	array of values or key
	 * @param 	mixed	value
	 * @return 	this
	 */
	public function config($data, $value = NULL)
	{
		if (is_array($data) && $value === NULL)
		{
			$this->_config = array_merge($this->_config, $data);
		}
		elseif($value !== NULL)
		{
			$this->_config = array_merge($this->_config, array((string) $data => $value));
		}
		
		return $this;
	}
}