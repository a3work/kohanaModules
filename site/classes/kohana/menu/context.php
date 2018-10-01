<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Context menu generator
 * @package 	Menu
 * @author 		Stanislav U. Alkimovich
 * @date 		16.12.13
 *
 * :TODO: rework menu subclasses as render strategy of Menu class 
 **/

class Kohana_Menu_Context extends Menu
{
	// css common class
	const CONTEXT_CLASS = 'cntxt';

	// marker for context double click handler
	const DBL_FUNC_MARK = 'dbl';
	
	// js replacements and separators
	const CALLBACK_BEGIN		= 'function(key,opt,obj){obj=obj||this;';
	const CALLBACK_END			= ";;;}";
	const SEPARATOR_VAR			= "-";
	const SEPARATOR_DISABLED	= "_";
	
	
	// dbl click handler flag
	protected $_dbl = FALSE;
	
	// css css of context
	protected $_css = 'context-hlight';
	
	// window switch
	protected $_use_window = FALSE;

	// window config
	protected $_win_config;
	
    // confirm switch
    protected $_use_confirm = FALSE;
    
	// confirmation message
	protected $_confirm_message;
	
	// multiple action flag
	protected $_is_multiple = FALSE;
	
	/** Kohana_Menu realization: Get new representative object
	 *
	 * @return 	Menu
	 */
	protected function _init_view( )
	{
		return array( );
	}
	
	/** Kohana_Menu realization: Render current Menu view
	 *
	 * @return mixed
	 */
	protected function _render($view)
	{
		$view['name'] = $this->_text;
		
		// bind double click to context
		if ($this->_dbl( ) === TRUE)
		{
			$view['name'] = '<b>'.$view['name'].'</b>';
		}
		
		$count = 0;
		
		foreach ($this->_list AS $item)
		{
			$key = $item->_key( ) !== NULL ? $item->_key( ) : $item->id( );
			
			$view['items'][$key] = $item->render(FALSE);
		}
		
		// generate check code for disabled flag
		if ($this->_key( ) !== NULL)
		{
			$view['disabled'] = 'this.attr(\'class\').indexOf(\''.$this->_make_disabled_css($this->_key( )).'\') != -1';
		}
		
		// add context multiple handler if need
		if ( ! $this->_is_multiple)
		{
			if (isset($view['disabled']))
			{
				$view['disabled'] .= ' || ';
			}
			else
			{
				$view['disabled'] = '';
			}
			
			$view['disabled'] .= '$(this).siblings(\'.ui-selected\').size( )';
		}
	
		if (isset($view['disabled']))
		{
			$view['disabled'] = Kohana_Menu_Context::CALLBACK_BEGIN.'return ('.$view['disabled'].')'.Kohana_Menu_Context::CALLBACK_END;
		}
	
		if ($this->_use_window( ) === TRUE)
		{
			$view['callback'] = Window::factory($this->_win_config( ))->call($this->_text( ), 'href', 'obj');
		}
		elseif ($this->_action( ) !== NULL)
		{
			$view['callback'] = $this->_action( ).'(obj, key, opt, href, '.str_replace(array('"', '\/'), array('\'', '/'), $this->_action_args( )).');';
		}
		
		if ($this->_href( ) !== NULL)
		{
			$href = 'MenuContext.link(\''.$this->_href( ).'\', obj);';
			
			if ( ! isset($view['callback']))
			{
				$view['callback'] = 'location.href = '.$href;
			}
			else
			{
				$view['callback'] = 'href = '.$href.$view['callback'];
			}
		}
		
		if ($this->_use_confirm( ) === TRUE)
		{
			$view['callback'] = 'if (!window.confirm(\''.str_replace(array('"', "'"), '', $this->_confirm_message( )).'\')) return true;'
								.(empty($view['callback']) ? '' : $view['callback']);
		}
		
		if (isset($view['callback']))
		{
			$view['callback'] = Kohana_Menu_Context::CALLBACK_BEGIN.$view['callback'].Kohana_Menu_Context::CALLBACK_END;
			
			// bind double click to context
			if ($this->_dbl( ) === TRUE)
			{
				// bind event
				$this->_root_data('dbl_handler', $view['callback']);
				
				// reserve mark dbl-click handler menu
				$this->_root_data('dbl_menu', $this);
			}
		}
		
// 		$view->bind('_list', $this->_list);
// 		$view->bind('_text', $this->_text);
// 		$view->bind('_href', $this->_href);
// 		$view->bind('_is_selected', $this->_is_selected);
		
		return $view;
	}
	
	/** Kohana_Menu realization: Render root view
	 *
	 * @return string
	 */
	protected function _render_root($view)
	{
		$view = $this->_render($view);
		
		$view['selector'] = '.'.$this->id( );
		
		// 
		if ($this->_css( ) !== NULL)
		{
			$view['events'] = array(
				'show' => Kohana_Menu_Context::CALLBACK_BEGIN.'$(this).addClass(\''.$this->_css( ).'\');'.Kohana_Menu_Context::CALLBACK_END,
				'hide' => Kohana_Menu_Context::CALLBACK_BEGIN.'$(this).removeClass(\''.$this->_css( ).'\');'.Kohana_Menu_Context::CALLBACK_END,
			);
		}

		// include library
		InclStream::instance( )->add('jquery.contextMenu.js', FALSE, 1);
		InclStream::instance( )->add('jquery.contextMenu.css');		
		InclStream::instance( )->add('menu.context.js');
		
		// write config
		InclStream::instance( )->write('$.contextMenu('.$this->_replace_json_func(Basic::json_safe_encode($view)).');');
		
		// attach jquery ui selectable if need
		if (isset($this->_data['use_selectable']))
		{
			InclStream::instance( )->jqueryui( );
			InclStream::instance( )->add('jquery.selectable.js');
			InclStream::instance( )->write('Selectable.init("'.'.'.Kohana_Menu_Context::CONTEXT_CLASS.$view['selector'].'");');
		}
		
		// bind double click to context if need
		if (isset($this->_data['dbl_handler']))
		{
			$func_name = Kohana_Menu_Context::DBL_FUNC_MARK.$this->id( );
			
			InclStream::instance( )->write($func_name.'='.$this->_data['dbl_handler'].";$(document).on('dblclick', '.".Kohana_Menu_Context::CONTEXT_CLASS."{$view['selector']}', function(e) {{$func_name}('".$this->_data['dbl_menu']->_key( )."', null, $(e.target).parents('.".Kohana_Menu_Context::CONTEXT_CLASS."{$view['selector']}').eq(0))});");
		}
		
		return '';
	}
	
	
	/** replace functions json declaration
 	 * 
	 * @param	string	json for replacing
	 * @return	string
	 */
	protected function _replace_json_func($json)
	{
		return
			str_replace(
				array(
					'"'.Kohana_Menu_Context::CALLBACK_BEGIN,
					Kohana_Menu_Context::CALLBACK_END.'"',
				),
				array(
					Kohana_Menu_Context::CALLBACK_BEGIN,
					Kohana_Menu_Context::CALLBACK_END,
				),
				$json
			);
	}
		
	/** Set css css for current item
	 *
	 * @param	string	css css name
	 * @return	string or this obj
	 */
	protected function _css($css = NULL)
	{
		if (isset($css))
		{
			$this->_css = $css;
		
			return $this;
		}
		
		return $this->_css;
	}
	
	/** Set css css for current item
	 * 
	 * @param	string	css css
	 * @return	string or this obj
	 */
	public function css($css = NULL)
	{
		return $this->_css($css);
	}
	
	/** Add request of command confirmation 
	 * 
	 * @param	string	message
	 * @return	string or this obj
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
	
	
	
	/** Mark action as multiline
	 * 
	 * @param	string	message
	 * @return	string or this obj
	 */
	public function multiple( )
	{
		if ($this->_last_item( ) === NULL)
		{
			return $this;
		}
		$this->_last_item( )->_is_multiple(TRUE);
		
		// set root flag
		$this->_root_data('use_selectable', TRUE);
	
		return $this;
	}
	
	/** Bind action of current item on double click to context
	 *
	 * @return	string or this obj
	 */
	public function dbl( )
	{
		if ($this->_last_item( ) === NULL)
		{
			return $this;
		}
		
		$this->_last_item( )->_dbl(TRUE);
	
		return $this;
	}
	
	/** Use window for this link
	 *
	 * @param 	string	window config (site.win.*)
	 * @return 	this
	 **/
	public function window($config = NULL)
	{
		if ($this->_last_item( ) === NULL)
		{
			return $this;
		}
		
		$this->_last_item( )->_use_window(TRUE);
		$this->_last_item( )->_win_config($config);
		
		return $this;
	}
	
	/** Set up context id and return context css-classes
	 *
	 * @param	array	array of variables
	 * @param	array	disabled items array
	 * @return	string OR NULL
	 */
	public function context(array $vars = NULL, array $disabled = NULL)
	{
		if ( ! $this->loaded( ))
		{
			return NULL;
		}
	
		$id = $this->id( );

		$out = array(Kohana_Menu_Context::CONTEXT_CLASS, $id);
		
		if (isset($vars))
		{
			foreach ($vars AS $var_key => $var_val)
			{
				$out[] = $this->id( ).Kohana_Menu_Context::SEPARATOR_VAR.str_replace(Kohana_Menu_Context::SEPARATOR_VAR, '',$var_key).Kohana_Menu_Context::SEPARATOR_VAR.$var_val;
			}
		}
	
		if (isset($disabled))
		{
			foreach ($disabled AS $var_val)
			{
				$out[] = $this->id( ).$this->_make_disabled_css($var_val);
			}
		}
		
		return implode(' ', $out);
	}
	
	/** Generate disabled Menu item mark
	 *
	 * @param	string	Menu item key
	 * @return	string
	 */
	protected function _make_disabled_css($key)
	{
		return Kohana_Menu_Context::SEPARATOR_DISABLED.str_replace(Kohana_Menu_Context::SEPARATOR_DISABLED, '',$key);
	}
}