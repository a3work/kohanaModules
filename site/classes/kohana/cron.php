<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Cron
{
	/**
	 * Устанавливаем расписание
	 *
	 * @param string метка действия
	 * @return void
	*/
	public static function install($label = NULL)
	{
		// читаем crontab в БД
		$actions = ORM::factory('cron_tab');

		if (isset($label))
		{
			if (is_numeric($label))
			{
				$actions->where('id', '=', $label);
			}
			else
			{
				$actions->where('label', '=', $label);
			}
		}

		$actions = $actions->find_all( );

		foreach ($actions AS $action)
		{
			// вычисляем дату следующего выполнения и записываем в таблицу
			$action->values(array(
				'next_execution' => Crontab::parse($action->rule),
// 				'count' => 0,
			));

			// сохраняем модель
			$action->save( );
		}
	}

	/**
	 * Возвращаем действия с указанной меткой
	 *
	 * @param mixed id / метка действия
	 * @return array
	*/
	public static function get($label)
	{
		$actions = ORM::factory('cron_tab')->order_by('label');

		if (is_numeric($label))
		{
			$actions->where('id', '=', $label);
		}
		else
		{
			$actions->where('label', '=', $label);
		}

		return $actions->find_all( );
	}

	/**
	 * Устанавливаем действие
	 *
	 * @param array массив значений [время, действие, метка]
	 * @return array
	*/
	public static function set($values)
	{
		if ( ! isset($values['action']))
		{
			throw new Exception('Не указана команда для выполнения.');
		}

		if ( ! isset($values['rule']))
		{
			throw new Exception('Не указано время выполнения.');
		}

		if ( ! isset($values['label']))
		{
			$values['label'] = '';
		}

		$orm = ORM::factory('cron_tab')
			-> values(array(
				'rule' => $values['rule'],
				'action' => $values['action'],
				'label' => $values['label'],
			))
			-> save( );

// 		var_dump($orm->last_query( ));
	}

	/** Replace action timetable
	 *
	 * @param 	array 	массив значений [время, действие, метка]
	 * @return 	void
	 */
	public static function replace($values)
	{
		$orm = ORM::factory('cron_tab')->where('label', '=', $values['label'])->find( );

		if ($orm->loaded( ))
		{
			$orm->delete( );
		}

		self::set($values);
	}

	/**
	 * редактируем данные действия
	 *
	 * @param mixed метка / id действия
	 * @return void
	*/
	public static function update($label, $values)
	{
		if (is_numeric($label))
		{
			ORM::factory('cron_tab')->where('id', '=', $label)->find( )->values($values)->save( );
		}
	}

	/**
	 * Удаляем действие
	 *
	 * @param mixed метка / id действия
	 * @return void
	*/
	public static function delete($label)
	{
		if (is_numeric($label))
		{
			ORM::factory('cron_tab', $label)->delete( );
		}
		else
		{
			$elements = ORM::factory('cron_tab')->where('label', '=', $label)->find_all( );
			foreach ($elements AS $item)
			{
				self::delete($item->id);
			}
		}
	}
	
	/**
	 * Execute specified task
	 * 
	 * @param 	Model_Cron_Tab	task
	 * @return	void
	 */
	public static function exec(Model_Cron_Tab $action)
	{
		$param = array( );
		
		if ($action->param != '')
		{
			parse_str($action->param, $param);
		}

		Cli::factory( )
			->name($action->label)
			->param($param)
			->task($action->action)
			->exec( );
// 				echo Crontab::parse($action->rule);
// 				echo "\n";
// 				ob_flush( );
	
		
		// обновляем время выполнения
		$action->next_execution = Crontab::parse($action->rule);
		
		// инкрементируем счётчик выполнения
		$action->count ++;
		
		// сохраняем модель
		$action->save( );
	}

}