<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Xlsx file representation
 * @package 	Files
 * @author 		Stanislav U. Alkimovich
 * @date 		2014-09-29
 * 
 * :DEPRECATED: USE File_Xls instead
 *
 **/

class Kohana_File_Xlsx extends File_Regular
{
	/**
	 * @const integer	chunk size
	 */
	const MIN_CHUNK_SIZE = 5000;
	
	/**
	 * @const integer	:KLUDGE: correction factor for phpExcel_worksheet::getHighestRow( ) return value
	 */
	const CORRECTION_FACTOR = 2;
	
	/**
	 * @const integer	maximal filesize for XLSX files, bytes
	 */
	const MAX_FILESIZE = 4194304;
	
	/** rewind Kohana_File content
	 *
	 * @param 	Kohana_File	file
	 * @return 	void
	 */
	public function rewind(Kohana_File $file)
    {
		$this->_init($file);
    }
    
	/** Convert Memory Size To Bytes
	 * 
	 *	by Christopher Mullins (http://phpexcel.codeplex.com/discussions/259971)
	 *
	 * @return 	integer
	 */
	private function get_memory_size_as_bytes( )
	{
		$memory_size = ini_get('memory_limit');

		switch (substr($memory_size, -1))
		{
			case 'G':
			case 'g':
				return (integer) $memory_size * 1073741824;
			case 'M':
			case 'm':
				return (integer) $memory_size * 1048576;
			case 'K':
			case 'k':
				return (integer) $memory_size * 1024;
		}

		return $memory_size;
	}    
	/** initialize and return phpExcel object for specified file
	 *
	 * @param 	Kohana_File
	 * @return 	object
	 */
	protected function _init(Kohana_File $file)
	{
		// check filesize
		if ($file->size( ) > Kohana_File_Xlsx::MAX_FILESIZE)
		{
			throw new File_Exception('Xlsx file is too large (> :max bytes)', array(':max' => Kohana_File_Xlsx::MAX_FILESIZE));
		}
	
		/*  parameters initialization */
		if ( ! isset($file->xls_read_all_sheets ))
		{
			$file->xls_read_all_sheets = FALSE;
		}
		
		if ( ! isset($file->xls_read_data_only ))
		{
			$file->xls_read_data_only = TRUE;
		}
		/* end of parameters initialization */
	
		if ( ! isset($file->xls_begin_line))
		{
			$file->xls_begin_line = 0;
		}
	
		// set current key to 0
		$file->xls_line_num = $file->xls_begin_line;
		
		// set current sheet number
		$file->xls_sheet_num = 0;
		
		// sheets array
		$file->xls_sheets = NULL;
		
		$file->xls_chunk_end = -1;
		
		$this->_init_php_excel($file);
	}
    
    
	/** Set or get begin line
	 *
	 * @param 	Kohana_File
	 * @param 	integer	begin line number
	 * @return 	Kohana_File (setter) or integer (getter)
	 */
	public function begin_line(Kohana_File $file, $value = NULL)
	{
		if (isset($value))
		{
			$file->xls_begin_line = (integer) $value;
		
			return $file;
		}
		
		return $file->xls_begin_line;
	}
    
	/** initialize and return phpExcel object for specified file
	 *
	 * @param 	Kohana_File
	 * @return 	void
	 */
    protected function _init_php_excel(Kohana_File $file)
    {
		// create php_excel_reader object
		$file->xls_phpexcel = PHPExcel_IOFactory::createReaderForFile($file->path( ));

		// total rows count for current sheet
		$file->xls_total_rows = -1;

		// load data only
		$file->xls_phpexcel->setReadDataOnly($file->xls_read_data_only);
    }
    
	/** Return current worksheet
	 *
	 * @param 	Kohana_File	file
	 * @return 	Kohana_File
	 */
    public function current(Kohana_File $file)
    {
		// return current phpExcel object
		return $file->xls_sheet;
    }

	/** Get current key of Kohana_File content
	 *
	 * @param 	Kohana_File	file
	 * @return 	scalar
	 */
    public function key(Kohana_File $file)
    {
		// return current key
		return $file->xls_line_num;
    }

	/** Switch Kohana_File content to the next element
	 *
	 * @param 	Kohana_File	file
 	* @return 	void
	 */
    public function next(Kohana_File $file)
    {
		// increase key
		$file->xls_line_num ++;
    }

	/** Validate current content item
	 *
	 * @param 	Kohana_File	file
	 * @return 	boolean
	 */
    public function valid(Kohana_File $file)
    {
		// clear current sheet if need
		if ($file->xls_line_num == $file->xls_chunk_end)
		{
			unset($file->xls_sheet);
			$file->xls_data->disconnectWorksheets( );
			
			unset($file->xls_data);
			unset($file->xls_phpexcel);
			
			// switch worksheet if need
			if ($file->xls_line_num == $file->xls_total_rows)
			{
				if (
					$file->xls_read_all_sheets === TRUE
					&&
					$file->xls_sheet_num < count($file->xls_info)-1
				)
				{
					$file->xls_sheet_num ++;
					$file->xls_line_num = 0;
				}
				else
				{
					/* :TODO: unset all xls_* variables */
				
					return FALSE;
				}
			}
		}
		
    
		if ( ! isset($file->xls_sheet))
		{
			// init phpExcel object
			$this->_init_php_excel($file);

			// define chunk size
			$file->xls_chunk_size = Kohana_File_Xlsx::MIN_CHUNK_SIZE;

			// init chunk filter
			$file->xls_chunk_filter = new File_Xls_Chunk;
			
			$file
				->xls_phpexcel
				->setReadFilter($file->xls_chunk_filter);

			/* load data and start reading */
			// set filter values
			$file->xls_chunk_filter->setRows(
				$file->xls_line_num,
				$file->xls_chunk_size
			);

			// load file
			$file->xls_data = $file
					->xls_phpexcel
					->load($file->path( ));

			if ($file->xls_read_all_sheets === TRUE && $file->xls_sheets === NULL)
			{
				// save sheets list
				$file->xls_sheets = $file->xls_data->getSheetNames( );
			}

			// load active sheet
			$file->xls_data->setActiveSheetIndex($file->xls_sheet_num);

			// save sheet
			$file->xls_sheet = $file->xls_data->getActiveSheet( );

	
			$highest_row = $file->xls_sheet->getHighestRow( ) + Kohana_File_Xlsx::CORRECTION_FACTOR;
			if ($highest_row < $file->xls_chunk_size)
			{
				$file->xls_chunk_end = $file->xls_line_num + $highest_row;
				$file->xls_total_rows = $file->xls_chunk_end;
			}
			else
			{
				$file->xls_chunk_end = $file->xls_line_num + $file->xls_chunk_size;
			}
		}
		
		return TRUE;
    }
	
	/** Content setter / getter
	 *
	 * @param 	Kohana_File	
	 * @param 	string		text for saving
	 * @param	string		insert mode
	 * @return 	string
	 */	
	public function content(Kohana_File $file, $text = NULL, $mode = NULL)
    {
        // if saving queried
        // run as setter
        if ($text !== NULL)
        {
            /* if writing allowed */
            if (acl('file_write', $file))
            {
                // write text to file
                file_put_contents($file->path( ), $text);
            }
        }
        else
        {
            return file_get_contents($file->path( ));
        }
    }
}
