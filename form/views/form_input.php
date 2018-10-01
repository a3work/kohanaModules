<?php
$out = $input_length = '';

switch ($field->type)
{
	case 'tree':
		if (count($options) > 0)
		{
			// Флаг ajax-режима отображения
			$is_ajax_mode = (boolean) (count($options) > Site::config('form')->tree_ajax_minimum_count);

			if ($is_ajax_mode)
			{
				// Массив переменных показа составляющих вывода
				$visibility = array( );
			}

			// забираем директивы для управления выводом
// 			if (isset(Form::directions('')))
// 			{

// 			}

			$path = array( );
			foreach ($options AS $key => $option)
			{
				$item = '';
				$create_empty_div = FALSE;
				if (isset($options[$key+1]) && ($options[$key+1]->parent == $option->value || $options[$key+1]->level > $option->level))
				{
					$item .= '<span>&minus;</span>';
				}
				elseif (isset($option->is_parent) && (boolean) $option->is_parent && ( ! isset($options[$key+1]) || $options[$key+1]->parent != $option->value || $options[$key+1]->level <= $option->level))
				{
					$item .= '<span class="tree-empty">&minus;</span>';
// 					$item .= '<span>'. (count($path) == 0 ? '&minus;' : '+') .'</span>';
					$create_empty_div = TRUE;
				}
				$option->disabled = isset($option->disabled) && (boolean) $option->disabled ? ' disabled' : '';

				// показываем уровень, если пункт отмечен
				if ($is_ajax_mode && $option->selected != '')
				{
// 					var_dump($option);
					$visibility[$option->parent] = TRUE;
				}

				$option_class = (isset($option->class)) && $option->class != '' ? ' class="'.$option->class.'"' : '';

				$item .= <<<HERE
<label{$option_class}><p><input type='radio' name='{$field->name}'{$class} value='{$option->value}' {$option->selected}{$option->disabled}>{$option->header}</p></label>
HERE;
				if ($create_empty_div)
				{
					$item .= '<div class=\'form-tree-empty'. (count($path) == 0 ? ' form-tree-root' : '') .'\'>[ Пусто ]</div>';
				}

				// если уровень, на котором находится элемент, уже открыт
				// (родитель присутствует в текущем адресе)
				if (in_array($option->parent, $path))
				{
					// если родитель элемента не последний в пути
					if (($item_position = array_search($option->parent, $path)) != ($current_count = count($path) - 1))
					{
						while (count($path) > 0)
						{
							$i = array_pop($path);
							$parents_key = count($path) - 1;

							if ($is_ajax_mode)
							{
								if (isset($visibility[$i]))
								{
									// если есть родитель, добавляем код текущего слоя в него.
									if (isset($path[$parents_key]))
									{
										$html[$path[$parents_key]] .= $html[$i].'</div>';
										unset($html[$i]);
									}
									else
									{
										$html[$i] .= '</div>';
									}
								}

								// если ребёнок показывается, то родитель, если он есть, тоже показывается
								if (isset($visibility[$i]) && isset($path[$parents_key]))
								{
									unset($visibility[$i]);
									$visibility[$path[$parents_key]] = TRUE;
								}

								$ajax[$i] .= '</div>';
							}
							else
							{
								$out .= '</div>';
							}

							// прерываем обход, если достигли текущего уровня
							if (count($path) == $item_position + 1)
							{
								break;
							}
						}
// 							$path =  array_slice($path, 0, $item_position + 1);
					}

					if ($is_ajax_mode)
					{
						// добавляем элемент в код уровней
						$html[$option->parent] .= $item;
						$ajax[$option->parent] .= $item;
					}
					else
					{
						$out .= $item;
					}
				}
				else
				{
					$path[] = $option->parent;
					$current_out = '<div'. (count($path) == 1 ? ' class=\'form-tree-root\'' : '') .'>'.$item;
					if ($is_ajax_mode)
					{
						$html[$option->parent] = $current_out;
						$ajax[$option->parent] = $current_out;
					}
					else
					{
						$out .= $current_out;
					}
				}

			}

			// если элементов для показа нет, то добавляем корень текущего дерева в выдачу
			if ($is_ajax_mode && isset($path[0]))
			{
				$visibility[$path[0]] = TRUE;
			}

			while (count($path) > 0)
			{
				$i = array_pop($path);
				$parents_key = count($path) - 1;

				if ($is_ajax_mode)
				{
					if (isset($visibility[$i]))
					{
						// если есть родитель, добавляем код текущего слоя в него.
						if (isset($path[$parents_key]))
						{
							$html[$path[$parents_key]] .= $html[$i].'</div>';
							unset($html[$i]);
						}
						else
						{
							$html[$i] .= '</div>';
						}
					}

					// если ребёнок показывается, то родитель, если он есть, тоже показывается
					if (isset($visibility[$i]) && isset($path[$parents_key]))
					{
						unset($visibility[$i]);
						$visibility[$path[$parents_key]] = TRUE;
					}

					$ajax[$i] .= '</div>';
				}
				else
				{
					$out .= '</div>';
				}
			}

			if ($is_ajax_mode)
			{
				$visibility = array_keys($visibility);
				foreach ($visibility AS $key)
				{
					$out .= $html[$key];
				}

				$tree_swap = Session::instance( )->get(Site::config('form')->session_tree_swap_var);

				if ( ! isset($tree_swap))
				{
					$tree_swap = array( );
				}

				$tree_swap[$field->name] = $ajax;
				Session::instance( )->set(Site::config('form')->session_tree_swap_var, $tree_swap);
			}
			unset($html);
			$out = "<div class='form-tree".($is_ajax_mode ? ' form-ajax-tree':'')."'>$out</div>";
		}
		else
		{
			$out .= "данные не определены";
		}

		break;

	case 'tree_multiple':
		if (count($options) > 0)
		{
			$path = array( );
			foreach ($options AS $key => $option)
			{
				$item = '';
				$create_empty_div = FALSE;
				if (isset($options[$key+1]) && ($options[$key+1]->parent == $option->value || isset($option->level) && $options[$key+1]->level > $option->level))
				{
					$item .= '<span>&minus;</span>';
				}
				elseif (isset($option->is_parent) && (boolean) $option->is_parent && ( ! isset($options[$key+1]) || $options[$key+1]->parent != $option->value || $options[$key+1]->level <= $option->level))
				{
					$item .= '<span>&minus;</span>';
// 					$item .= '<span>'. (count($path) == 0 ? '&minus;' : '+') .'</span>';
					$create_empty_div = TRUE;
				}
				$option->disabled = isset($option->disabled) && (boolean) $option->disabled ? ' disabled' : '';
				$item .= <<<HERE
<label><input type='checkbox' name='{$field->name}'{$class} value='{$option->value}' {$option->selected}{$option->disabled}>{$option->header}</label>
HERE;
				if (in_array($option->parent, $path))
				{
					if (($item_position = array_search($option->parent, $path)) != ($current_count = count($path) - 1))
					{
						$out .= str_repeat('</div>', ($current_count - $item_position)).$item;
						$path =  array_slice($path, 0, $item_position + 1);
					}
					else
					{
						$out .= $item;
					}
				}
				else
				{
					$path[] = $option->parent;
					$out .= '<div'. (count($path) == 1 ? ' class=\'form-tree-root\'' : '') .'>'.$item;
				}

				if ($create_empty_div)
				{
					$out .= '<div class=\'form-tree-empty'. (count($path) == 1 ? ' form-tree-root' : '') .'\'>[ Пусто ]</div>';
				}
			}
			$out .= str_repeat('</div>', count($path));

			$out = "<div class='form-tree'>$out</div>";
		}
		else
		{
			$out .= "данные не определены";
		}

		break;
}

echo $out;
/*
<div class='form-line'>
	<div class='form-label'><?=$label?></div>
	<div class='form-element'><?=$element?></div>
</div>
*/
?>
