<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Editors factory: fetch parameters, load object of necessary class
 * @category	Common
 * @package 	Editor
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-05-30
 *
 **/

class Editor
{
	// error codes
	const ERROR_ARGS_COUNT 		= 1;
	const ERROR_WRONG_CLASS 	= 2;
	const ERROR_EMPTY_ID 		= 3;
	const ERROR_ACCESS_DENIED	= 4;
	const ERROR_LOADING_ORM		= 5;
	const ERROR_LOADING_SESSION	= 6;

	// session setttings array var name
	const SESS_SETTINGS_VAR = 'editor_settings';
	// session settings hashes array var name
	const SESS_HASHES_VAR = 'editor_setting_hashes';

	// instances array
	protected static $instances = array( );

	// Session settings array
	protected static $settings;

	// next key
	protected static $settings_key;

	/**
	 * Objects factory
	 *
	 * Loading by class name, orm name and field
	 * @param mixed			(integer) settings ID / (string) class name
	 * @param string		ORM name
	 * @param string 		field name
	 *
	 * @return object
	 */
	public static function factory($id, $orm = NULL, $field = NULL)
	{
		// fast check for class-request Editor::factory(<class_name>)
		if (is_string($id) && ! isset($orm) && isset(self::$instances[$id]))
		{
			return self::$instances[$id];
		}

		if (is_integer($id))
		{
			$current_settings = self::get($id);
			$class_name 	= $current_settings[0];
			$orm 			= $current_settings[1];
			$field 			= $current_settings[2];
			$settings_id	= &$id;

			unset($current_settings);
		}
		else
		{
			$class_name = $id;
		}


// 		self::clear( );
// 		switch (func_num_args( ))
// 		{
// 			case 1:
// 				$current_settings = self::get($args[0]);
// 				$settings_id = $args[0];
// 				break;
// 			case 3:
// 				$current_settings = $args;
// 				break;
// 			default:
// 				throw new Editor_Exception('Incorrect count of income arguments.', NULL, Editor::ERROR_ARGS_COUNT);
// 		}

		$key = (isset($field)) ? "{$class_name}_{$orm}_{$field}" : $class_name;

		// check exist of object
		if ( ! isset(self::$instances[$key]))
		{
			// create instance
			self::$instances[$key] = new $class_name;

			// set parameters
			if (isset($orm))
			{
				self::$instances[$key]->orm($orm);
			}
			if (isset($field))
			{
				self::$instances[$key]->field($field);
			}

			// check instance
			if ( ! self::$instances[$key] instanceof Editor_Base)
			{
				throw new Editor_Exception('Wrong class "'.$class_name.'".', NULL, Editor::ERROR_WRONG_CLASS);
			}

			if (isset($settings_id))
			{
				// set stored settings ID
				self::$instances[$key]->settings_id($settings_id);
			}


		}

		return self::$instances[$key];
	}

	/**
	 * Write to / read settings from session
	 *
	 * Read mode:
	 * no parameters
	 *
	 * Write mode:
	 * @param 	mixed		array current_session_settings / boolean save_flag
	 *
	 * @return 	array
	 */
	private static function settings($settings = NULL)
	{
		// save settings
		if (isset($settings))
		{
			// rewrite
			if (is_array($settings))
			{
				self::$settings = $settings;
			}

			Session::instance( )->set(Editor::SESS_SETTINGS_VAR, self::$settings);

		}
		elseif ( ! isset(self::$settings))
		{
			self::$settings = Session::instance( )->get(Editor::SESS_SETTINGS_VAR);


			if ( ! isset(self::$settings))
			{
				self::$settings = array( );
				self::$settings_key = 1;
			}
			else
			{
				ksort(self::$settings);

				$keys = array_keys(self::$settings);

				// get next key
				self::$settings_key = end($keys);
			}
		}

		return self::$settings;
	}

	/**
	 * Init internal request of referrer url for session initialization
	 */
	private static function init_sess( )
	{
		// initial session
		if (Session::instance( )->get(Editor::SESS_HASHES_VAR) == NULL)
		{
			Request::factory(str_replace(array('http://', $_SERVER['HTTP_HOST'], 'www.'), '', $_SERVER['HTTP_REFERER']))->execute( );
		}
	}

	/**
	 * Fetch editor settings from session by ID
	 *
	 * @param integer 	ID
	 * @return array	settings
	 */
	private static function get($id)
	{
		$settings = self::settings( );

		if ( ! isset($settings[$id]))
		{
			throw new Editor_Exception('Cannot load session.', NULL, Editor::ERROR_LOADING_SESSION);
		}

		return ( ! isset($settings[$id])) ? FALSE : $settings[$id];
	}

	/**
	 * Clear session service array
	 *
	 * @return void
	 */
	public static function clear( )
	{
		Session::instance( )->delete(Editor::SESS_SETTINGS_VAR);
		Session::instance( )->delete(Editor::SESS_HASHES_VAR);
	}

	/**
	 * Register editor settings in session
	 *
	 * @param array 		settings (class, ORM, field)
	 * @return integer		ID
	 */
	public static function set(array $settings)
	{
		$current_settings = self::settings( );

		$current_setting_hashes = Session::instance( )->get(self::SESS_HASHES_VAR);
		if ( ! isset($current_setting_hashes))
		{
			$current_setting_hashes = array( );
		}

		// get hash
		$hash = Basic::get_hash(implode('_', $settings));
		$id = array_search($hash, $current_setting_hashes);

		if ($id === FALSE)
		{
			$id = ++ self::$settings_key;

			// save into settings_array
			$current_settings[$id] = $settings;

			// save hash
			$current_setting_hashes[$id] = $hash;

			// save settings in session
			self::settings($current_settings);

			// save hashes in session
			Session::instance( )->set(self::SESS_HASHES_VAR, $current_setting_hashes);
		}

		return $id;
	}
}