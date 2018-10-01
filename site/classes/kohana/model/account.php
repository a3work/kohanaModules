<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * @package    Kohana/Personal
 * @author     A. St.
 */
class Kohana_Model_Account extends ORM {

	protected $_table_name = 'user_accounts';
	/**
	 * A user has many tokens and roles
	 *
	 * @var array Relationhips
	 */
	protected $_has_many = array(
		'groups'			=> array('model' => 'group', 'far_key' => 'group_id', 'foreign_key' => 'user_id', 'through' => 'user_groups'),
	);
	
	protected $_has_one = array(
		'attributes' 		=> array('model' => 'attributes', 'foreign_key' => 'user_id'),
	);

	/**
	 * Rules for the user model. Because the password is _always_ a hash
	 * when it's set,you need to run an additional not_empty rule in your controller
	 * to make sure you didn't hash an empty string. The password rules
	 * should be enforced outside the model or with a model helper method.
	 *
	 * @return array Rules
	 */
// 	public function rules()
// 	{
// 		return array(
// 			'username' => array(
// 				array('not_empty'),
// 				array('max_length', array(':value', 32)),
// 				array(array($this, 'unique'), array('username', ':value')),
// 			),
// 			'password' => array(
// 				array('not_empty'),
// 			),
// 			'email' => array(
// 				array('not_empty'),
// 				array('email'),
// 				array(array($this, 'unique'), array('email', ':value')),
// 			),
// 		);
// 	}*/

	/** Delete account
	 */
	public function delete( )
	{
		if ((boolean) $this->is_system) 
		{
			throw new User_Exception('Cannot remove a system account.');
		}
	
		$attr = $this->attributes->delete( );

		return parent::delete( );
	}

	/**
	 * Filters to run when data is set in this model. The password filter
	 * automatically hashes the password when it's set in the model.
	 *
	 * @return array Filters
	 */
	public function filters()
	{
		return array(
			'password' => array(
				array(array('User', 'hash'))
			)
		);
	}

	/**
	 * Labels for fields in this model
	 *
	 * @return array Labels
	 */
	public function labels()
	{
		return array(
			'username'         => 'username',
			'email'            => 'email address',
			'password'         => 'password',
		);
	}

	/**
	 * Complete the login for a user by incrementing the logins and saving login timestamp
	 *
	 * @return void
	 */
	public function complete_login()
	{
		if ($this->_loaded)
		{
			// Update the number of logins
			$this->logins = new Database_Expression('logins + 1');

			// Set the last login date
			$this->last_login = time();

			// Save the user
			$this->update();
		}
	}

	/**
	 * Tests if a unique key value exists in the database.
	 *
	 * @param   mixed    the value to test
	 * @param   string   field name
	 * @return  boolean
	 * /
	public function unique_key_exists($value, $field = NULL)
	{
		if ($field === NULL)
		{
			// Automatically determine field by looking at the value
			$field = $this->unique_key($value);
		}

		return (bool) DB::select(array('COUNT("*")', 'total_count'))
			->from($this->_table_name)
			->where($field, '=', $value)
			->where($this->_primary_key, '!=', $this->pk())
			->execute($this->_db)
			->get('total_count');
	}*/

	/**
	 * Allows a model use both email and username as unique identifiers for login
	 *
	 * @param   string  unique value
	 * @return  string  field name
	 * /
	public function unique_key($value)
	{
		return Valid::email($value) ? 'email' : 'username';
	}*/

	/**
	 * Password validation for plain passwords.
	 *
	 * @param array $values
	 * @return Validation
	 * /
	public static function get_password_validation($values)
	{
		return Validation::factory($values)
			->rule('password', 'min_length', array(':value', 8))
			->rule('password_confirm', 'matches', array(':validation', ':field', 'password'));
	}*/

	/**
	 * Выдаём массив id прикреплённых групп
	 *
	 * @return Array
	 */
	public function get_groups_id( )
	{
		if ($this->_loaded)
		{
			return array_keys(DB::query(Database::SELECT, 'SELECT group_id FROM `personal_users_groups` WHERE user_id = '.$this->id)->execute( )->as_array('group_id'));
		}
	}
}
