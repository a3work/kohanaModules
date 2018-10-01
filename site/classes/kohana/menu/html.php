<?php defined('SYSPATH') or die('No direct script access.');

/**
 * 
 * @name		HTML menu realization (concrete subclass of Kohana_Menu).
 * @package		Menu
 * @category	Exceptions
 * @author		A. St.
 */
 
class Kohana_Menu_Html extends Menu {
	
	// css classes
	public $classes = array( );
	
	// user defined template file
	public $template;
	
	// view file of item
	protected $_template;
	
	// view file of item
	protected $_template_root;
	
    // confirm switch
    protected $_use_confirm = FALSE;
    
    // confirmation message
    protected $_confirm_message;
    
	/** Define and return template name
	 *
	 * @return	string
	 */
	protected function _template( )
	{
		if ( ! isset($this->_template))
		{
			$this->_template = Site::config('menu')->default_item_view;
		}
	
		return $this->_template;
	}
	
	/** Define and return template name
	 *
	 * @return	string
	 */
	protected function _template_root( )
	{
		if ( ! isset($this->_template_root))
		{
			$this->_template_root = Site::config('menu')->default_root_view;
		}
	
		return $this->_template_root;
	}
	
	/** Kohana_Menu realization: get view object for saving
	 * 
	 * @return	View
	 */
	protected function _init_view( )
	{
		return View::factory( );
	}
	
	/** Kohana_Menu realization: Render current view object
	 *
	 * @param	mixed	current view
	 * @return void
	 */
	protected function _render($view)
	{
		if ($this->template( ) === NULL)
		{
			$this->template($this->_template( ));
		}

		$view->set_filename($this->template( ));
		
		// bind variables
		$view->bind('_list', $this->_list);
		$view->bind('_text', $this->_text);
		$view->bind('_href', $this->_href);
		$view->bind('_classes', $this->classes);
		$view->bind('_is_selected', $this->_is_selected);

        if ($this->_use_confirm( ) === TRUE)
        {
            $view->bind('_confirm', $this->_confirm_message);
        }
		
		// render View obj
		return $view->render( ); 
	}
	
	/** Render menu and return html
	 *
	 * @return	string
	 */
	public function __toString( )
	{
		try
		{
			return $this->render(TRUE);
		}
		catch (Exception $e)
		{
			if (IN_PRODUCTION)
			{
				return $e->getMessage().' in '.$e->getFile().', line '.$e->getLine();
			}
			else
			{
				return 'Error: cannot render menu.';
			}
		}
	}


	
	/** Kohana_Menu realization: Render root Menu item
	 *
	 * @param	mixed	current view
	 * @return	void
	 */
	protected function _render_root($view)
	{
		if ($this->template( ) === NULL)
		{
			$this->template($this->_template_root( ));
		}
	
		return $this->_render($view);
	}
	
	/** Set css class of menu item
	 *
	 * @param	string	css
	 * @return 	Menu
	 */
	public function css($css = NULL)
	{
		if ($this->_last_item( ) === NULL)
		{
			return $this;
		}
		
		if (isset($css))
		{
			$this->_last_item( )->classes[] = $css;
			
			return $this;
		}
		else
		{
			return $this->classes;
		}
	}

    /** Add request of command confirmation 
     * 
     * @param   string  message
     * @return  string or this obj
     */
    public function confirm($message = NULL)
    {
        if ($this->_last_item( ) === NULL)
        {
            return $this;
        }
        $this->_last_item( )->_use_confirm(TRUE);
        $this->_last_item( )->_confirm_message(empty($message) ? __('are you sure').'?' : $message);
    
        return $this;
    }
    
    
	
}