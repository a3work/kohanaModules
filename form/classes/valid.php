<?php defined('SYSPATH') or die('No direct script access.');

class Valid extends Kohana_Valid {

	/**
	 * Checks whether a string consists of alphabetical characters only.
	 *
	 * @param   string   input string
	 * @param   boolean  trigger UTF-8 compatibility
	 * @return  boolean
	 */
	public static function alpha($str, $utf8 = FALSE)
	{
		$str = (string) $str;

		if ($utf8 === TRUE)
		{
			return (bool) preg_match('/^\pL++$/uD', $str);
		}
		else
		{
			// :KLUDGE: must use locale
			return preg_match('/^[a-zA-Zа-яА-Я]*$/', $str);
// 			return ctype_alpha($str); :ORIGINAL:
		}
	}


}
