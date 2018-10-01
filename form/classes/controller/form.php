<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Form compiler
 *
 * @package    Kohana/Form
 * @category   Controllers
 * @author     A.St.
 *
 */
class Controller_Form extends Controller
{
	public function action_index( )
	{
		$map_id = Site::get_map_id(Site::get_current_uri( ));

		$this->response->body(
			Request::factory(
				Route::get('form_by_id')
					->uri(array(
						'id_type' 	=> 'map',
						'id' 		=> $map_id
					))
			)->execute( )->body( ));
	}

	// определяем id формы
	// и запускаем валидацию и генерирование
	public function action_init( )
	{
		$id_type = Security::xss_clean($this->request->param('id_type'));
		$id = Security::xss_clean($this->request->param('id'));

		$form = new Form_Engine($id_type, $id);

		if ($form->redirect( ))
		{
			$this->request->redirect($form->redirect_uri( ));
		}

		// публикуем форму
		$this->response->body($form->publish( ));
	}

	/**
	 * Выдаём сохранённый код уровня дерева
	 *
	 * @return void
	 */
	public function action_tree_level( )
	{
		$element = Security::xss_clean($this->request->param('element'));
		$value = Security::xss_clean($this->request->param('value'));

		// получаем данные из сессии
		$data = Session::instance()->get(Site::config('form')->session_tree_swap_var);
		if (isset($data[$element][$value]))
		{
			$this->response->body($data[$element][$value]);
		}
	}
}
?>