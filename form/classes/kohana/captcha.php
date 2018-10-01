<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Captcha functions
 *
 * @package    Kohana/Form
 * @category   Controllers
 * @author     A.St.
 */
 
class Kohana_Captcha
{
	private $image;
	private $worms;
	private $colors;
	private $min_block;
	private $max_block;

	/** Check current value of CAPTCHA
	 *
	 * @param 	mixed	value
	 * @return 	boolean
	 */
	public static function check($value)
	{
		return (Session::instance()->get(Captcha::get_session_name()) == $value);
	}
	
	/**
	 * Геттер цвета
	 * 
	 * @param string объект (text|border|background)
	 * @return resource
	 */
	private function get_color($type)
	{
		if ( ! isset($this->colors[$type]))
		{
			$color = Site::config('captcha')->{$type."_color"};
			
			$this->colors[$type] = imagecolorallocate($this->image, Captcha::randomize_color($color[0]), Captcha::randomize_color($color[1]), Captcha::randomize_color($color[2]));
		}
		
		return $this->colors[$type];
	}
	 
	
	/**
	 * Генерирование имени сессионной переменной для каптчи
	 * 
	 * @return string
	 */
	public static function get_session_name( )
	{
		return Basic::get_hash(Site::config('captcha')->session_variable);
	}
	
	/**
	 * Генерируем случайное значение цветовой составляющей
	 * в пределах указанного диапазона
	 * 
	 * @param integer цвет (0-255)
	 * @param integer диапазон изменения
	 * 
	 * @return integer
	 */
	public static function randomize_color($value, $range = 50)
	{
		$min = ( $value - $range < 0 ) ? 0 : $value - $range;
		$max = ( $value + $range > 255 ) ? 255 : $value + $range;
		
		return mt_rand( $min, $max);
	}
	
	/**
	 * Генерируем шум
	 * 
	 * @return void
	 */
	 public function get_square_noize( )
	 {
		// инициализируем двух первых червяков и записываем их в массив
		$this->worms[] = new Worm(0, 0, "x");
		$this->worms[] = new Worm(0, 0, "y");

		$total = 1;
		
		// счётчик шагов
		$counter = 1;
		
		while ($this->is_ready( ))
		{
			foreach ( $this->worms AS $key=>$worm)
			{
				$this->worms[$key]->move( );
				if (@imagecolorat( $this->image, $this->worms[$key]->x, $this->worms[$key]->y) != $this->get_color('border'))
				{
					imagesetpixel( $this->image, $this->worms[$key]->x, $this->worms[$key]->y, $this->get_color('border'));
				}
				else
				{
					unset( $this->worms[$key]);
				}
			}
			
			if ($counter >= Site::config('captcha')->min_block && $counter <= (Site::config('captcha')->max_block - Site::config('captcha')->check_step))
			{
				$randomize = mt_rand( 0, 1000);
				if ( $randomize > 500)
				{
					$this->change_direction();
					$counter = 1;
				}
			}
			elseif ( $counter > Site::config('captcha')->max_block)
			{
				$this->change_direction();
				$counter = 1;
			}
			elseif ( $counter < Site::config('captcha')->min_block || $counter > ( Site::config('captcha')->max_block - Site::config('captcha')->check_step) && $counter <= Site::config('captcha')->max_block)
			{
				$counter ++;
			}
			
			$total ++;
		}
	 }
		
	/**
	 * Проверяем -- все ли червяки достигли края изображения
	 * 
	 * @return boolean
	 */
	private function is_ready( ) {
	
		foreach ( $this->worms AS $key=>$worm)
		{
			if ( $worm->x >= imagesx($this->image) || $worm->y >= imagesy($this->image))
			{
				unset( $this->worms[$key]);
			}
		}
		
		if ( count( $this->worms) == 0)
		{
			return FALSE;
		}
		else
		{
			return TRUE;
		}
	}

	/**
	 * Запрещаем червякам плодиться, если до ближайшего следа очень маленькое расстояние
 	 * проверяем ближайшие три пикселя по ходу движения
	 * 
	 * @return boolean
	 */
	private function ban_reproduction( $key)
	{
		for ( $i = -1; $i <= Site::config('captcha')->check_step; $i ++)
		{
			if ( $i == 0)
			{
				continue;
			}
			if ( $this->worms[$key]->direction == "y")
			{
				$codition = (@imagecolorat( $this->image, ($this->worms[$key]->x + $i), ( $this->worms[$key]->y + 1)) == $this->get_color('border'));
			}
			else
			{
				$codition = (@imagecolorat( $this->image, ($this->worms[$key]->x + 1), ($this->worms[$key]->y + $i)) == $this->get_color('border'));
			}
			
			if ($codition === TRUE)
			{
				return FALSE;
			}
		}
		return TRUE;
	}

	/**
	 * Меняем направление движения червяков
	 * 
	 * @return void
	 */
	private function change_direction()
	{
		$temp_array = array();
		$counter = 0;
		
		foreach ( $this->worms AS $key=>$worm)
		{
			if ( count($this->worms) <= 50)
			{
				if ( $this->ban_reproduction( $key))
				{
					// червяки плодятся
					$temp_array[] = new Worm( $this->worms[$key]->x, $this->worms[$key]->y, $this->worms[$key]->direction);
					$this->worms[$key]->change_direction( );
				}
				else
				{
// 					$this->get_color('border') = $red;
				}
			}
		}
		
		$this->worms = array_merge( $this->worms, $temp_array);
	}
	
	
	/**
	 * Конструктор
	 * 
	 * @return object
	 */
	public function __construct( )
	{
		// имя сессионной переменной
		$sess_var = Captcha::get_session_name( );
		
		// картинка
		$this->image = imagecreatetruecolor(Site::config('captcha')->width, Site::config('captcha')->height);
		
		imagefill( $this->image, 0, 0, $this->get_color('background'));

		$action_array = array( "&minus;", "+", "&times;");
		$rand_action = mt_rand( 0, 2);
		$rand_1 = mt_rand( 1, 10);
		$rand_2 = mt_rand( 1, 10);
		
		if ( $rand_2 >= $rand_1 && $rand_action == 0)
		{
			$rand_action = 1;
		}

		if ( $rand_action == 0)
		{
			$answer = $rand_1 - $rand_2;
		}
		elseif ( $rand_action == 1)
		{
			$answer = $rand_1 + $rand_2;
		}
		else
		{
			$answer = $rand_1 * $rand_2;
		}
		
		Session::instance( )->set($sess_var, $answer);

		
		$first_params = array(
			mt_rand( floor( 0.62 * imagesy( $this->image)), floor( 0.74 * imagesy( $this->image))),
			(mt_rand( 0, 20) - 10),
			mt_rand( 0, floor( 0.08 * imagesx( $this->image))),
			mt_rand( floor( 0.56 * imagesy( $this->image)), floor( 0.9 * imagesy( $this->image)))
		);
		$second_params = array(
			mt_rand( floor( 0.8 * imagesy( $this->image)), floor( 0.94 * imagesy( $this->image))),
			(mt_rand( 0, 10) - 5),
			mt_rand( ($first_params[2] + strlen( $rand_1) * floor( 0.2 * imagesx( $this->image))),($first_params[2] + strlen( $rand_1) * floor( 0.25 * imagesx( $this->image)))),
			mt_rand( floor( 0.7 * imagesy( $this->image)), floor( 0.9 * imagesy( $this->image)))
		);
		$third_params = array(
			mt_rand( floor( 0.62 * imagesy( $this->image)), floor( 0.74 * imagesy( $this->image))),
			(mt_rand( 0, 20) - 10),
			mt_rand( ($second_params[2] + floor( 0.3 * imagesx( $this->image))), ($second_params[2] + floor( 0.35 * imagesx( $this->image)))),
			mt_rand( floor( 0.56 * imagesy( $this->image)), floor( 0.9 * imagesy( $this->image)))
		);
		
		
		$font_rand = Kohana::find_file('media', 'font/'. Site::config('captcha')->fonts->text, 'ttf');
		$font_rand_2 = Kohana::find_file('media', 'font/'. Site::config('captcha')->fonts->sign, 'ttf');
		
		$action_array[$rand_action] = iconv ( "CP1251", "UTF-8", $action_array[$rand_action]);
		
		imagettftext( $this->image, $first_params[0], $first_params[1], $first_params[2], $first_params[3], $this->get_color('text'), $font_rand, $rand_1);
		imagettftext( $this->image, $second_params[0], $second_params[1], $second_params[2], $second_params[3], $this->get_color('text'), $font_rand_2, $action_array[$rand_action]);
		imagettftext( $this->image, $third_params[0], $third_params[1], $third_params[2], $third_params[3], $this->get_color('text'), $font_rand, $rand_2);

		$this->get_square_noize( );
	}
	
	public function publish( )
	{
		imagejpeg( $this->image, NULL, 70);
		imagedestroy( $this->image);
	}

}