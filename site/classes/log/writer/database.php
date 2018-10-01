<?php defined('SYSPATH') or die('No direct script access.');

class Log_Writer_Database extends Log_Writer {

	public function __construct()
	{
		register_shutdown_function(array($this, 'update_session'));
	}

	/**
	 * Запись лог в БД
	 *
	 * @see Kohana_Log_Writer::write()
	 * @return void
	 */
	public function write(array $messages)
	{
		$log = new Model_Log;

		foreach ($messages as $message)
		{
			$log->values($this->_format($message))->create();
                                                     $log->clear();
		}
	}

	/**
	 * Форматирование ошибок для записи в БД
	 *
	 * @param array $message
	 * @return array
	 */
	protected function _format($message)
	{
		$return = array();
		$return['time']			 = Date::formatted_time('now', 'Y-m-d H:i:s', Log::$timezone);
		$return['level']		 = strtoupper($this->_log_levels[$message['level']]);
		$return['message']		 = __($message['body'], $message['values']);
		$return['values']		 = serialize($message['values']);
		$return['client']		 = Request::$client_ip;
		$return['uri']			 = Request::detect_uri( );
		$return['referer']		 = is_object(Request::current( )) ? Request::current( )->referrer( ) : '';
		$return['agent']		 = is_object(Request::current( )) ? implode(' ', Request::current( )->user_agent(array('browser', 'version', 'robot', 'mobile', 'platform'))) : '';
		$return['cookie']		 = var_export($_COOKIE, true);
		$return['user_id']		 = User::get( )->id;
		$return['logo']			 = isset($message['params']['logo']) ? $message['params']['logo'] : NULL;
		$return['external_id_0'] = isset($message['params']['external_id_0']) ? $message['params']['external_id_0'] : NULL;
		$return['external_id_1'] = isset($message['params']['external_id_1']) ? $message['params']['external_id_1'] : NULL;

		return $return;
	}

	public function update_session()
	{
		Session::instance()->set('log_writer', __CLASS__);
	}

} // End Log_Writer_Database

