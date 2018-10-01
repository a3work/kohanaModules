<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_File extends ORM
{
	protected $_table_name = 'files';
	
	/**
	 * @var array		stack of parameters, which have been prepared for saving
	 */
	protected $_params = array( );
	
	/** save data on destroy
	 *
	 * @return 	void
	 */
	public function __destruct( )
	{
		$this->_save_params( );
	}
	
	/** Delete specified parameters for file
	 *
	 * @param 	string			filename
	 * @param 	array			array of parameters [key, key]
	 * @return 	Model_File 		this ORM
	 */
	public function delete_params($filename, $params)
	{
		if (count($params) == 0)
		{
			return $this;
		}
	
		$params = '("'.implode('","', $params).'")';
		
		$query = <<<HERE
DELETE
FROM
	{$this->_table_name}
WHERE
	`filename` = "{$filename}"
	AND
	`key` IN {$params}
HERE;
	
		// execute
		DB::query(Database::DELETE, $query)->execute( );

		return $this;
	}
	
	
	/** Add parameter to stack of prepared for saving
	 *
	 * @param 	string		filename
	 * @param 	mixed		key
	 * @param 	mixed		value
	 * @return 	Model_File
	 */
	public function param($filename, $key, $value = NULL)
	{
		if ( ! isset($this->_params[$filename]))
		{
			$this->_params[$filename] = array( );
		}
		
		$this->_params[$filename][$key] = $value;
		
		return $this;
	}
	
	/** Save parameters of specified file or files (if $filename = parameters array)
	 *
	 * @param 	string			filename
	 * @param 	array			array of parameters [key=>value, key=>value]
	 * @return 	Model_File		this ORM
	 */
	public function save_param($filename, $params)
	{
		if (count($params) == 0)
		{
			return $this;
		}
		
		$values = array( );
		
		foreach ($params AS $key=>$value)
		{
			$values[] = "(\"$filename\", \"$key\", ".($value === NULL ? 'NULL' : "\"$value\"") .")";
		}
		
		$values = implode(',', $values);
		
		$query = <<<HERE
INSERT INTO {$this->_table_name} (`filename`, `key`, `value`)
VALUES $values
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
HERE;

		// execute
		DB::query(Database::INSERT, $query)->execute( );

		return $this;
	}
	

	/** Save parameters of files
	 * 
	 * @return	Model_File	this
	 */
	protected function _save_params( )
	{
		if (count($this->_params) == 0)
		{
			return $this;
		}
		
		foreach ($this->_params AS $filename=>$params)
		{
			foreach ($params AS $key=>$value)
			{
				$values[] = "(\"$filename\", \"$key\", ".($value === NULL ? 'NULL' : "\"$value\"") .")";
			}
		}
		
		$values = implode(',', $values);
		
		$query = <<<HERE
INSERT INTO {$this->_table_name} (`filename`, `key`, `value`)
VALUES $values
ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)
HERE;

		// execute
		DB::query(Database::INSERT, $query)->execute( );

		$query = <<<HERE
DELETE
FROM {$this->_table_name}
WHERE `value` IS NULL
HERE;
		
		DB::query(Database::DELETE, $query)->execute( );
			
		return $this;
	}
}