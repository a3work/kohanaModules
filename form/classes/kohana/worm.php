<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Square noize engine
 *
 * @package    Kohana/Form
 * @category   Controllers
 * @author     A.St.
 */
 
class Kohana_Worm {

	public $x;
	public $y;
	public $direction;
	
	function __construct($x, $y, $direction) {
		$this->x = $x;
		$this->y = $y;
		$this->direction = $direction;
	}

	public function move( ) {
		global $image;
		// двигаемся на один пиксель в заданном направлении
		if ( $this->direction == "x") {
			$this->x ++;
		} else {
			$this->y ++;
		}
	}

	public function change_direction( ) {
		$this->direction = ( $this->direction == "x") ? "y" : "x";
	}
}
?>