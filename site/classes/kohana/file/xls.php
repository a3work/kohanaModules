<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
This driver is a wrapper of phpExcel library.
 
###Simple reading and writing
~~~
		// get File instance
 		$xls = File::factory('INV791853RUR.xls');
 		
 		// use phpExcel_worksheet methods for manipulation
		$xls->SetCellValue('A3', 'Basic usage');
		
		// save result
		$xls->save( );
~~~
 
Switch on auto resize of columns:
PHPExcel_Shared_Font::setAutoSizeMethod(PHPExcel_Shared_Font::AUTOSIZE_METHOD_EXACT); 

(http://stackoverflow.com/questions/16761897/phpexcel-auto-size-column-width)
 
 
 * @name		Xls file representation
 * @package 	Files
 * @author 		Stanislav U. Alkimovich
 * @date 		2014-07-07
 * 
 * :TODO: xlsX support
 *
 **/

class Kohana_File_Xls extends File_Regular
{
	/**
	 * @const integer	chunk size
	 */
	const MIN_CHUNK_SIZE = 10000;

	/**
	 * @const integer	MODE: read file using chunks (for large files) -- DEFAULT mode
	 */
	const MODE_CHUNKS = 1;
	
	/**
	 * @const integer	MODE: read or write file (without chunks usage, may exceed available memory size)
	 */
	const MODE_FULL = 2;

	/**
	 * @const integer	:KLUDGE: correction factor for phpExcel_worksheet::getHighestRow( ) return value
	 */
	const CORRECTION_FACTOR = 2;
	
	/** Redirect queries to phpExcel_worksheet object
	 *
	 * @param 	Kohana_File
	 * @param 	mixed 		variable
	 * @return 	mixed		phpExcel functions output
	 */
	public function __call($var, $args = array( ))
	{
		$file = array_shift($args);
		
		return @call_user_func_array(array($this->_php_excel($file), $var), $args);
	}
	
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
		/*  parameters initialization */
		if ( ! isset($file->xls_read_all_sheets ))
		{
			$file->xls_read_all_sheets = FALSE;
		}
		
		if ( ! isset($file->xls_mode))
		{
			$file->xls_mode = Kohana_File_Xls::MODE_FULL;
		}

		if ($file->xls_mode == Kohana_File_Xls::MODE_CHUNKS && ! isset($file->xls_read_data_only ))
		{
			$file->xls_read_data_only = TRUE;
		}
		elseif ( ! isset($file->xls_read_data_only ))
		{
			$file->xls_read_data_only = FALSE;
		}
		
		// set up flag: should format return values if true
		if ( ! isset($file->xls_format_values))
		{
			$file->xls_format_values = FALSE;
		}

		/* end of parameters initialization */
	
		if ( ! isset($file->xls_begin_line))
		{
			$file->xls_begin_line = 0;
		}
	
		// set current key to 0
		$file->xls_line_num = $file->xls_begin_line;
		
		// set current sheet number
		if ( ! isset($file->xls_sheet_num))
		{
			$file->xls_sheet_num = 0;
		}
		
		// sheets array
		$file->xls_sheets = NULL;
		
		$file->xls_chunk_end = -1;
		
		// total rows count for current sheet
		$file->xls_total_rows = -1;
		
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
    
	/** Set or get flag of formatting
	 *
	 * @param 	Kohana_File
	 * @param 	boolean
	 * @return 	Kohana_File (setter) or boolean (getter)
	 */
	public function format_values(Kohana_File $file, $value = NULL)
	{
		if (isset($value))
		{
			$file->xls_formated_values  = (boolean) $value;
		
			return $file;
		}
		
		return $file->xls_begin_line;
	}
    
	/** Return current phpExcel_worksheet object for specified object
	 *
	 * @param 	Kohana_File
	 * @return 	phpExcel
	 */
    protected function _php_excel(Kohana_File $file)
    {
		if ( ! isset($file->xls_phpexcel))
		{
			// initialize phpExcel object and define all parameters
			$this->_init($file);
		}
		
		if ( ! isset($file->xls_sheet))
		{
			// initialize worksheet
			$this->_init_worksheet($file);
		}
		
		return $file->xls_sheet;
    }
    
	/** Initialize and return phpExcel object for specified file
	 *
	 * @param 	Kohana_File
	 * @return 	void
	 */
    protected function _init_php_excel(Kohana_File $file)
    {
		if ( ! isset($file->xls_phpexcel))
		{
			if ( ! acl('file_read', $file))
			{
				throw new Access_Exception( );
			}
		
			// create php_excel_reader object
			if ($file->ext() == 'xls' && $file->xls_mode == Kohana_File_Xls::MODE_CHUNKS)
			{
				$file->xls_phpexcel = PHPExcel_IOFactory::createReaderForFile($file->path( ));
				
				// load data only
				$file->xls_phpexcel->setReadDataOnly($file->xls_read_data_only);
			}
			// create phpexcel reader/writer object (don't support chunk reading)
			else
			{
				PHPExcel_Settings::setCacheStorageMethod(
					PHPExcel_CachedObjectStorageFactory::cache_to_phpTemp,
					array('memoryCacheSize ' => '8MB')
				);
				
				/* :KLUDGE: @ for suppress iconv warning
				 * 		 iconv(): Detected an illegal character in input string)
				 * 		(MODPATH/phpexcel/classes/vendor/phpexcel/PHPExcel/Shared/String.php [ 496 ])
				 */
				$file->xls_phpexcel = @PHPExcel_IOFactory::load($file->path( ), $file->ext() == 'xls' ? 'Excel5' : 'Excel2007');
			}
		}
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
			if ($file->xls_mode == Kohana_File_Xls::MODE_CHUNKS)
			{
				$this->unload($file);
			}
			
			// switch worksheet if need
			if ($file->xls_line_num == $file->xls_total_rows)
			{
				if (
					$file->xls_read_all_sheets === TRUE
					&&
					$file->xls_sheet_num < count($file->xls_sheets) - 1
				)
				{
					// switch to next sheet
					$file->xls_sheet_num ++;
					
					// drop line count
					$file->xls_line_num = $file->xls_begin_line;
					
					// unload current worksheet
					unset($file->xls_sheet);
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
			$this->_init_worksheet($file);
		}
		
		return TRUE;
    }
    

	/** Initialize current worksheet for specified document
	 *
	 * @param 	Kohana_File
	 * @return 	void
	 */
	protected function _init_worksheet(Kohana_File $file)
    {
		// init phpExcel object
		$this->_init_php_excel($file);

		if ( ! isset($file->xls_data))
		{
			if ($file->xls_mode == Kohana_File_Xls::MODE_CHUNKS)
			{
				// define chunk size
				$file->xls_chunk_size = Kohana_File_Xls::MIN_CHUNK_SIZE;

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
			}
			else
			{
				$file->xls_data = $file->xls_phpexcel;
			}
		}

		/* read sheet names */
		if ($file->xls_read_all_sheets === TRUE && $file->xls_sheets === NULL)
		{
			// save sheets list
			$file->xls_sheets = $file->xls_data->getSheetNames( );
		}
		
		// load sheet
		$file->xls_sheet = $file->xls_data->getSheet($file->xls_sheet_num);

		// calculate end of list
		$highest_row = $file->xls_sheet->getHighestRow( ) + Kohana_File_Xls::CORRECTION_FACTOR;
		
		if ($file->xls_mode != Kohana_File_Xls::MODE_CHUNKS || $highest_row < $file->xls_chunk_size)
		{
			$file->xls_chunk_end = $file->xls_line_num + $highest_row;
			$file->xls_total_rows = $file->xls_chunk_end;
		}
		else
		{
			$file->xls_chunk_end = $file->xls_line_num + $file->xls_chunk_size;
		}
	}
    
	
	/** Save content, disconnect worksheets and kill all phpExcel- objects
	 *
	 * @param 	Kohana_File	
	 * @return 	Kohana_File
	 */
	public function save(Kohana_File $file)
	{
		// initialize
		$this->_php_excel($file);
	
		/* :TODO: save changes for CHUNK read mode */
		if ($file->xls_mode != Kohana_File_Xls::MODE_CHUNKS)
		{
	
			// write file
			$obj_writer = new PHPExcel_Writer_Excel5($file->xls_phpexcel);

			// save file
			$obj_writer->save($file->path( ));
		}
		
		// reinit file: reload size and other params
		$file->init();
		
		return $file;
	}
	
	/** Set mode
	 *
	 * @param 	Kohana_File
	 * @param 	Integer 		Mode
	 * @return 	Kohana_File
	 */
	public function mode(Kohana_File $file, $mode)
	{
		if (
			in_array(
				$mode, 
				array(
					Kohana_File_Xls::MODE_CHUNKS, Kohana_File_Xls::MODE_FULL,
				)
			)
		)
		{
			$file->xls_mode = $mode;
		}
		else
		{
			throw new File_Exception('Unrecognized XLS mode :mode.', array(':mode' => $mode));
		}
		
		return $file;
	}
	
	/** Unload all phpExcel* objects for current File
	 *
	 * @param 	Kohana_File
	 * @return 	Kohana_File
	 */
	public function unload(Kohana_File $file)
	{
		unset($file->xls_sheet);
		$file->xls_data->disconnectWorksheets( );
		
		unset($file->xls_data);
		unset($file->xls_phpexcel);
		
		return $file;
	}
	
	/** Set background color for specified cell
	 *
	 * @param 	Kohana_File
	 * @param 	string	cell address
	 * @param 	string	color code
	 * @param 	string	end color -- used linear gradient if not null
	 * @param 	double	rotation
	 * @param 	string	color
	 * @author	Limitless isa 		http://stackoverflow.com/users/1256632/limitless-isa
	 * @return 	Kohana_File
	 */
	function set_cell_color(Kohana_File $file, $cells, $color, $end_color = NULL, $rotation = 0)
	{
		if (empty($end_color))
		{
			$this
				->_php_excel($file)
				->getStyle($cells)
				->getFill()
				->applyFromArray(array(
					'type' => PHPExcel_Style_Fill::FILL_SOLID,
					'startcolor' => array('rgb' => trim($color, '#')),
				));
		}
		else
		{
			$this
				->_php_excel($file)
				->getStyle($cells)
				->getFill()
				->applyFromArray(array(
					'type' => PHPExcel_Style_Fill::FILL_GRADIENT_LINEAR,
					'rotation' => $rotation,
					'startcolor' => array('rgb' => trim($color, '#')),
					'endcolor' => array('rgb' => trim($end_color, '#')),
				));
		}
		
		return $file;
	}
	
	/** Define cell by coordinates
	 *
	 * @param 	Kohana_File	VAR_DESCR
	 * @param 	mixed	string or array of coordinates
	 * @return 	PHPExcel_Cell 
	 */
	protected function _define_cell($file, $coord)
	{
        //column set by index
        if (is_array($coord))
        {
            $cell = $this->_php_excel($file)->getCellByColumnAndRow($coord[0], $coord[1]);
        }
        else
        {
            $cell = $this->_php_excel($file)->getCell($coord);
        }
        
        return $cell;
	}
	
	/** Set cell value
	 *
	 * @param 	Kohana_File	VAR_DESCR
	 * @param 	mixed	cell coordinates
	 * @param 	mixed	value
	 * @param 	mixed	format: available constants from PHPExcel_Cell_DataType (::TYPE_STRING etc.) OR FALSE if should set up value without definition of type
	 * @return 	Kohana_File
	 */	
	public function set_cell_value(Kohana_File $file, $cell, $value, $type = PHPExcel_Cell_DataType::TYPE_STRING)
	{
		// get cell by coordinates
		$cell = $this->_define_cell($file, $cell);
		
		if ($type === FALSE)
		{
			$cell->setValue($value);
		}
		else
		{
			$cell->setValueExplicit($value, $type);
		}
		
		return $file;
	}
	
	
	/** Get cell value
	 *
	 * @param 	Kohana_File
	 * @param 	mixed	cell coordinates
	 * @param 	string	date format
	 * @author	vitalets 		http://habrahabr.ru/users/vitalets/
	 * @return 	mixed
	 */
	public function get_cell_value(Kohana_File $file, $cell, $format = 'd.m.Y')
    {
		// get cell by coordinates
        $cell = $this->_define_cell($file, $cell);
        
        $file->merged_cells_range = $this->_php_excel($file)->getMergeCells();
        
        // try to find current coordinate in all merged cells ranges
        // if find -> get value from head cell
        foreach($file->merged_cells_range AS $curr_merged_range)
        {
            if($cell->isInRange($curr_merged_range))
            {
                $curr_merged_cells_array = PHPExcel_Cell::splitRange($curr_merged_range);
                $cell = $this->_php_excel($file)->getCell($curr_merged_cells_array[0][0]);
                break;
            }
        }

        // simple value
        if ($file->xls_format_values)
        {
			$val = $cell->getFormattedValue( );
        
        }
        else
        {
			$val = $cell->getValue();
        
			//date
			if (PHPExcel_Shared_Date::isDateTime($cell))
			{
				$val = date($format, PHPExcel_Shared_Date::ExcelToPHP($val)); 
			}
			
			// for incorrect formulas take old value
			if((substr($val,0,1) === '=' ) && (strlen($val) > 1))
			{
				$val = $cell->getOldCalculatedValue( );
			}
        }

        return $val === NULL ? '' : $val;
    }
    
	/** Get name of specified sheet
	 *
	 * @param 	Kohana_File
	 * @param 	integer	sheet num
	 * @return 	string
	 */
	public function get_sheet_name(Kohana_File $file, $sheet_num = NULL)
	{
		if (empty($sheet_num))
		{
			$sheet_num = $file->xls_sheet_num;
		}
		
		return $file->xls_sheets[$file->xls_sheet_num];
	}
}
