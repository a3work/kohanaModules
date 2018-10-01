<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		CMS Database log viewer
 * @package 	Site
 * @author 		A. St.
 * @date 		08.01.14
 *
 **/

class Kohana_Controller_Cms_Log extends Controller_Cms
{

	const VAR_LAST_READ = 'log_last_read';
	
	/** reloaded Kohana Controller::before 
	 * 
	 * @return void
	 */
	public function before( )
	{
		parent::before( );
		
		if ( ! acl('log_browse'))
		{
			throw new Access_Exception('Permission denied');
		}
		
		$menu = Cms_Submenu::factory( )
			->text(__u('event log'))
			->render( );
			
		$this->template->left = $menu;
	}

	/** Action: index
	 *  list of log
	 *
	 * @return void
	 */
	public function action_index( )
	{
		$orm = ORM::factory('log')
				->with('account')
				->order_by('log.id', 'desc')
				->page( );
				
		if ( ! User::is_root( ))
		{
			$orm->where('account.is_system', '=', 0);
		}
		
		
		// add filter 
		$filter = Form::factory('filter')
					->always_show(TRUE)
					->filter_name('log filter')
					->field('text', 'запрос', 'query')/*->min_length(3)*/
					->field('submit', __u('find'))
					->callback('query', function($value, $orm) {
						$value_arr = explode(' ', trim($value));
						foreach ($value_arr AS $value)
						{
							$orm
								->and_where_open( )
								->where('account.username', 'LIKE', DB::expr('"%'.$value.'%"'))
								->or_where('level', '=', $value)
								->or_where('message', 'LIKE', DB::expr('"%'.$value.'%"'))
								->and_where_close( );
						}
						
					}, array('orm' => $orm))
					->render( );		
					
		$this->template->right = $filter;

		if ( ! acl('log_browse_sys'))
		{
			// limit log level
			$orm->where('level', '=', 'INFO');

			$header = array(
				__u('time'),
				__u('user'),
				__u('message'),
			);
		}
		else
		{
			$header = array(
				__u('#'),
				array(__u('time'), __u('level')),
				__u('user'),
				__u('message'),
				array(__u('client IP'), __u('agent')),
				array(__u('URI'), __u('referer')),
				__u('cookie'),
			);
		}
		
		$table = Html::factory('table')->header($header);
		
		// mark unread messages
		$last_read = Cookie::store(Controller_Cms_Log::VAR_LAST_READ);
		$max_read = 0;
		
		if ( ! acl('log_browse_sys'))
		{
			foreach ($orm->find_all( ) AS $item)
			{
				$class = '';
			
				if ($item->id > $last_read)
				{
					$class = 'unread';
				
					if ($max_read == 0)
					{
						$max_read = $item->id;
					}
				}
				
				// limit log level
				$orm->where('level', '=', 'INFO');

				$values = unserialize($item->values);
				
				$line = array(
					$item->time,
					Html::factory('anchor')->href(Route::url('user_manage', array('id' => $item->user_id)))->text($item->account->username),
					$values !== FALSE ? __($item->message, $values) : __($item->message),
				);
				
				$table
					->line($line)
					->classes($class)
					/*->href( )
					->classes()*/;
			}
		}
		else
		{
			foreach ($orm->find_all( ) AS $item)
			{
				$class = '';
			
				if ($item->id > $last_read)
				{
					$class = 'unread';
				
					if ($max_read == 0)
					{
						$max_read = $item->id;
					}
				}
				
				$values = unserialize($item->values);
				
				$line = array(
					$item->id,
					array($item->time, $item->level),
					Html::factory('anchor')->href(Route::url('user_manage', array('id' => $item->user_id)))->text($item->account->username),
					$values !== FALSE ? __($item->message, $values) : __($item->message),
					array($item->client, $item->agent),
// 					array($item->uri, $item->referer),
// 					$item->cookie,
				);
				
				$table
					->line($line)
					->classes($class);
					/*->href( )
					*/
			}
		}
		
		if ($max_read != 0)
		{
			Cookie::store(Controller_Cms_Log::VAR_LAST_READ, $max_read);
		}
		
		$this->template->header = __u('event log');
		$this->template->body = $orm->pagination( ).$table->render( ).$orm->pagination( );
	}	
	
}