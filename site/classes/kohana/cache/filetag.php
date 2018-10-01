<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Cache_Filetag extends Cache /*implements Kohana_Cache_Tagging*/ {
	protected static $cache_dir;
	protected static $cache_tag_dir;

	protected function __construct(array $config)
	{
		parent::__construct($config);

		self::$cache_dir  = Kohana::$config->load('cache')->get('file');
		self::$cache_dir = self::$cache_dir['cache_dir'];
		self::$cache_tag_dir = self::$cache_dir.DIRECTORY_SEPARATOR.'_tags';

		try
		{
			$dir = new SplFileInfo(self::$cache_dir);
			if (!$dir->isDir())
			{
				if (!mkdir($dir, 0777, TRUE))
				{
					throw new Kohana_Cache_Exception(__METHOD__.' unable to create directory : :directory', array(':directory' => $dir));
				}
				chmod($dir, 0777);
			}
			$dir = new SplFileInfo(self::$cache_dir.DIRECTORY_SEPARATOR.'/_tags');
			if (!$dir->isDir())
			{
				if (!mkdir($dir, 0777, TRUE))
				{
					throw new Kohana_Cache_Exception(__METHOD__.' unable to create directory : :directory', array(':directory' => $dir));
				}
				chmod($dir, 0777);
			}
		}
		catch(Exception $e)
		{
			if(Kohana::$environment =='development')
			{
				throw $e;
			}
		}
	}

	public function set_with_tags($id, $data, $lifetime = NULL, array $tags = array(), $directory = false)
	{
		try
		{
			$filename	= self::filename($id);
			if($directory)
			{
				$directory	= self::dirname($id, $directory);
			}
			else
			{
				$directory	= self::dirname($id);
			}

			if ($lifetime === NULL)
			{
				$lifetime = Arr::get($this->_config, 'default_expire', Cache::DEFAULT_EXPIRE);
			}

			//tag em up
			foreach($tags as $tag)
			{
				$tag_dir = new SplFileInfo(self::$cache_tag_dir.DIRECTORY_SEPARATOR.$tag);
				if (!$tag_dir->isDir())
				{
					if (!mkdir($tag_dir, 0777, TRUE))
					{
						throw new Kohana_Cache_Exception(__METHOD__.' unable to create directory : :directory', array(':directory' => $tag_dir));
					}
					chmod($tag_dir, 0777);
				}
				$tagfilename = $tag_dir.DIRECTORY_SEPARATOR.self::filename($id,0);
				file_put_contents($tagfilename,$lifetime);
			}

			//create the cache hexxor prefixxed dir
			$dir = new SplFileInfo($directory);
			if (!$dir->isDir())
			{
				if (!mkdir($directory, 0777, TRUE))
				{
					throw new Kohana_Cache_Exception(__METHOD__.' unable to create directory : :directory', array(':directory' => $directory));
				}
				chmod($directory, 0777);
			}
			$data = serialize(array('data'=>$data, 'expires'=>time()+$lifetime));
			file_put_contents($directory.$filename,$data);
		}
		catch (Exception $e)
		{
			if(Kohana::$environment =='development')
			{
				throw $e;
			}
			else
			{
				return false;
			}
		}
		return true;
	}

	public function get($id, $default = NULL, $directory = false)
	{
		try
		{
			$filename = self::filename($id);
			if($directory)
			{
				$directory	= self::dirname($id, $directory);
			}
			else
			{
				$directory	= self::dirname($id);
			}

			$content = @file_get_contents($directory.$filename);
			$content = unserialize($content);
			if($content['expires'] <= time())
			{
				@unlink($directory.$filename);
				//check the tag lifetime too?..
			}
			else
			{
				return $content['data'];
			}
		}
		catch (ErrorException $e)
		{
			if (Kohana::$environment == 'development' && $e->getCode() != 2)
			{
			    throw $e;
			}
		}
		return $default;
	}

	public function delete_tag($tag)
	{
		$this->delete_tags(array($tag));
	}

    public function delete_tags($tags, $other_dir = false)
	{
		try
		{
			foreach($tags as $tag)
			{
				$pat = self::$cache_tag_dir.DIRECTORY_SEPARATOR.$tag;

				$d = dir($pat);
				while($filename = $d->read())
				{
					if ($filename != "." && $filename != "..")
					{
						$cid = explode('.',$filename);
						$cid = $cid[0];

						if($other_dir)
							$cd = str_replace('/dev/shm/','/dev/shm/'.$other_dir.'/', self::$cache_dir);
						else
							$cd = self::$cache_dir;

						$cache_file = $cd.DIRECTORY_SEPARATOR.substr($filename,0,2).DIRECTORY_SEPARATOR.substr($filename,2);
						@unlink($cache_file); //if it fails here, the other files wont be removed.
					}
				}
				self::rm_safe($pat.'/*');
			}
		}
		catch(Exception $e)
		{
			//ignore errors
		}
	}

    public function find($tags){}
	public function set($id, $data, $lifetime = NULL){$this->set_with_tags($id, $data, $lifetime);}
	public function delete($id){}
	public function delete_all($cache_dir = false)
    {
    	//@todo: cache_dir...
    	self::rm_safe(self::$cache_dir);
    }

    private function rm_safe($dir)
    {
		if(substr($dir,0,8) == '/dev/shm')
    	{
    		shell_exec('rm '.$dir.' -rf');
    	}
    }
	protected static function dirname($cache_id, $other_dir = false)
	{
		$cache_id = self::filename($cache_id,0);
		if($other_dir)
		{
			$cd = str_replace('/dev/shm/','/dev/shm/'.$other_dir.'/', self::$cache_dir);
		}
		else
		{
			$cd = self::$cache_dir;
		}

		$dname = $cd.DIRECTORY_SEPARATOR.substr($cache_id,0,2).DIRECTORY_SEPARATOR;
		return $dname;
	}
	protected static function filename($cache_id,$substr = 2)
	{
		$cache_id = md5($cache_id);
		$fname = substr($cache_id,$substr).'.txt';
		return $fname;
	}

}