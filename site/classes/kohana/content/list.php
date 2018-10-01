<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Site content list iterator
 *
 * @package    Kohana/Site
 * @author     A.St.
 *
 *
 */
class Kohana_Content_List implements Iterator
{
    protected $_position = 0;

    protected $_result = null;

    /**
     * Обёртываем Database_MYSQL_Result
     * для изменения данных во время обхода
     *
     * @param object Database_MYSQL_Result obj
     */
    public function __construct(Database_MySQL_Result $db_result_obj)
    {
        $this->_position = 0;
        $this->_result = $db_result_obj;
    }

    public function rewind()
    {
        $this->_result->rewind( );
    }

    public function current()
    {
        return new Content_Item($this->_result->current( ));
    }

    public function key()
    {
        return $this->_result->key( );
    }

    public function next()
    {
		$this->_result->key( );
    }

    public function valid( )
    {
        return $this->_result->valid( );
    }
}
