<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Controller_Cron extends Controller
{

	/**
	 * Проверяем, существуют ли действия, подлежащие выполнению на текущий момент
	 * В случае существования выполняем.
	 * 
	 * @return void
	*/
	public function action_index( )
	{
		// текущая метка времени
		$current_timestamp = time( );

		// ищем текущее и просроченное
		$actions = ORM::factory('cron_tab')
			-> where('next_execution', '<=', $current_timestamp)
			-> where('is_active', '=', 1)
			-> find_all( );
		
		if (count($actions) > 0)
		{
		
			foreach ($actions AS $action)
			{
// 				echo $action->action;
// 				echo "\n";
// 				ob_flush( );
				Cron::exec($action);
			}
		}
/*			
			// получаем следующую метку выполнения
			$next_action = ORM::factory('cron_tab')
				-> order_by('next_execution')
				-> limit(1);
				-> find( );
			if (isset($next_action))
			{
				sleep($next_action->next_execution - time( ));
			}
			else
			{
				break;
			}*/
	}
	
	/**
	 * Устанавливаем расписание после обновления
	 * 
	 * @return  void
	*/
	public function action_install( )
	{
		Cron::install( );
	}
}