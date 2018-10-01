<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Form submit result array wrapper, handlers collection and service methods
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-11
 *
 **/

class Kohana_Form_Result implements Iterator
{
	// link to Form_Base object
	protected $form;

	// field list
	protected $_data = array( );


	/** ITERATOR IMPLEMENTATION **/
    public function rewind()
    {
        reset($this->_data);
    }

    public function current()
    {
        $value = current($this->_data);
        return $value->result( );
    }

    public function key()
    {
        $key = key($this->_data);
        return $key;
    }

    public function next()
    {
        $value = next($this->_data);
        return $value;
    }

    public function valid()
    {
        $key = key($this->_data);
        return ($key !== NULL && $key !== FALSE);
    }


	/**
	 * Object constructor
	 *
	 * @param Form_Base parent form
	 */
	public function __construct(Form_Base $obj)
	{
		$this->form($obj);
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

			return $this;
		}

		// return value by Form_Field ID
		if (isset($this->_data[$var]))
		{
			return $this->_data[$var]->result( );
		}

		if (property_exists($this, $var))
		{
			return $this->$var;
		}
		else
		{
			return call_user_func_array(array($this->form( ), $var), $args);
		}

		return $this->$var;
	}

	/** return attached form html
	 *
	 * @return string
	 */
	public function __toString( )
	{
		try
		{
			return $this->form( )->body( );
		}
		catch (Exception $e)
		{
			if (IN_PRODUCTION)
			{
				var_dump($e);
				die( );
			}
		}
	}

	public function loaded( )
	{
		return ($this->form( )->sent( ) !== FALSE);
	}

	/** Add Form_Field to data list
	 *
	 * @param Form_Field
	 * @return this
	 */
	public function add(Form_Field $field)
	{
		// if field has alias
		if (isset($field->alias))
		{
			$field_key = $field->alias( );
		}
		else
		{
			$field_key = $field->id( );
		}

		if ( ! isset($this->_data[$field_key]))
		{
			$this->_data[$field_key] = $field;

			$this->{$field_key} = $field->result( );
		}
	}

	/** Check Form_Field id and remove from results if it has behavior with pass_valid == TRUE
	 *
	 * @param mixed Form_Field / array
	 * @return boolean

	public function clear($data)
	{
		if (is_array($data))
		{
			foreach ($data AS &$field)
			{
				$this->clear($field);
			} unset($field);
		}
		else
		{
			if (isset($this->_data[$data->id( )]) && ($data->beh( )->pass_valid( ) || $data->is_private( )))
			{
				unset($this->_data[$data->id( )]);
			}
		}

		return TRUE;
	}*/

	/** Complete drop data array
	 *
	 * @return void
	 */
	public function drop()
	{
		$this->_data = array( );
	}

	protected function _parse_complex_keys($key, $field, &$out)
	{
		if (preg_match('/^([^\{]+)(\{([^\}]+)\})/', $key, $matches))
		{
			if ( ! isset($out[$matches[1]]))
			{
				$out[$matches[1]] = array();
			}
			
			$key = str_replace(array($matches[1], $matches[2]), array('', $matches[3]), $key);
			
			$this->_parse_complex_keys($key, $field, $out[$matches[1]]);
		}
		else
		{
			$out[$key] = $field->result( );
		}
	}
	
	/** return associative array of values
	 *
	 * @return array
	 */
	public function as_array( )
	{
		$out = array( );

		foreach ($this->_data( ) AS $key => $field)
		{
			$this->_parse_complex_keys($key, $field, $out);
		}

		return $out;
	}

}