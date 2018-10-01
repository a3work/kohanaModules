<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @name		File database-based collection engine
 * @package 	Files
 * @author 		Stanislav U. Alkimovich <a3.work@gmail.com>
 * @date 		2013-10-09
 *
 **/
class Kohana_File_Collection_Database extends File_Collection_Virtual
{
	// view name
	protected $_item_template = 'files.item.directory';
	
	/** rewind Kohana_File content
	 *
	 * @param 	Kohana_File	file
	 * @return 	void
	 */
	public function rewind(Kohana_File $file)
    {
		if ( ! isset($file->db_query_result))
		{
		
			$file->db_query_result = $this->_db_query($file)->execute( );

			$file->content = array( );
		}
		
		reset($file->content);
    }

	/** Create and return current item of Kohana_File content
	 *
	 * @param 	Kohana_File	file
	 * @return 	Kohana_File
	 */
    public function current(Kohana_File $file)
    {
		if ($file->loaded( ) === FALSE)
		{
			$key = $file->db_query_result->key( );
			
			$current_data = $file->db_query_result->current( );
			
			$file->content[$key] = call_user_func_array(array($file->item_class, 'factory'), array($current_data->filename));
			
			$file->content[$key]->db_set($current_data->key, $current_data->value, File::DBSYNC_NEVER);
		}

        return $file->content[$key];
    }

	/** Get current key of Kohana_File content
	 *
	 * @param 	Kohana_File	file
	 * @return 	scalar
	 */
    public function key(Kohana_File $file)
    {
        return key($file->content);
    }

	/** Switch Kohana_File content to the next element
	 *
	 * @param 	Kohana_File	file
 	* @return 	void
	 */
    public function next(Kohana_File $file)
    {
		if ($file->loaded( ) === FALSE)
		{
			$file->db_query_result->next( );
		}
		
		next($file->content);
    }

	/** Validate current content item
	 *
	 * @param 	Kohana_File	file
	 * @return 	boolean
	 */
    public function valid(Kohana_File $file)
    {
		if ($file->loaded( ) === FALSE)
		{
			return $file->db_query_result->valid( );
		}
		else
		{
			if (key($file->content) !== NULL)
			{
				return TRUE;
			}
			else
			{
				// mark file as loaded
				$file->loaded(TRUE);
				
				return FALSE;
			}
		}
    }
    

	/** Create and return Database_Query_Builder_Select instance for current File object
	 *
	 * @param 	Kohana_File
	 * @return 	 Database_Query_Builder_Select
	 */
	protected function _db_query(Kohana_File $file)
	{
		if ( ! isset($file->db_query))
		{
			$file->db_query = DB::select( )
								->from(ORM::factory('file')->table_name( ))
								->as_object( );
		}
		
		return $file->db_query;
	}
    
	/**
	 * Redirect query to Database_Query_Builder_Select instance
	 *
	 * @param string 	method name
	 * @param array		parameters
	 *
	 * @return mixed
	 */
	public function __call($var, $args = array( ))
	{
		// fetch link to current file object
		$file = array_shift($args);
		
		$result = call_user_func_array(array($this->_db_query($file), $var), $args);
		
		return $file;
	}
	
	/**
	 * limit the collection by children 
	 * 
	 * @param Kohana_File $file
	 * @return Kohana_File
	 */
	public function find_children(Kohana_File $file)
	{
		$this->where($file, 'filename', 'like', DB::expr("'".$file->path(FALSE).'%'."'"));
		$this->where($file, 'filename', 'not like', DB::expr("'".$file->path(FALSE)."'"));
		
		return $file;
	}
}