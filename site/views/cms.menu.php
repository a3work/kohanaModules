<?php
$base = URL::base( );
$out = $item_out = $css_out = '';
foreach ($menu AS $chapter)
{
	$item_out = '';
	$items = $chapter->items( );

	if (count($items) > 0)
	{
		foreach ($items AS $item)
		{
			$classes = array( );

			$name = $item->name( );
			$href = $item->url( );
			$params = $target = '';

			if ($item->add_opener( ))
			{
				$params .= Site::config('cms')->opener_uri_var.'='.Request::detect_uri( ).'&';
			}

			if (count($item->options( )) > 0)
			{
				foreach ($item->options( ) AS $o_key => $o_value)
				{
					$params .= $o_key.'='.$o_value.'&';
				}
			}
			
			if ($item->target( ) !== NULL)
			{
				$target = " target='".$item->target( )."'";
			}
			
			if ($params != '')
			{
				$href .= '?'.trim($params, '&');
			}

			$href = HTML::factory('anchor')->href($href)->text($name);
			
			if (count($classes) > 0)
			{
				$href->classes($classes);
			}
			
			if ($item->window( ) !== NULL)
			{
				if (is_string($item->window( )))
				{
					$href->window($item->window( ));
				}
				else
				{
					$href->window('client');
				}
			}

			$item_out .= $href;
/*			
			$item_out .= <<<HERE
		<a href='{$href}'{$classes}{$target}>{$name}</a>
HERE;
*/
		}

		$chapter_name = $chapter->name( );
		$out .= <<<HERE
	<div class='cms-menu-item'>{$chapter_name}<br>
		<div class='cms-menu-drop'>{$item_out}</div>
	</div>
HERE;
/*
	if (is_integer($chapter_name))
	{
		$out .= <<<HERE
	<a class='cms-menu-item' href='{$chapter_body['href']}'>{$chapter_body['name']}</a>
HERE;
	}
	else
	{
		$item_out = '';
		foreach ($chapter_body AS $href=>$href_data)
		{
			$classes = '';
			// отмечаем соответствующим классом
			// или выполняем инструкции
			// в зависимости от  значений в конфиге
			if (isset($href_data['confirm']) && (bool) $href_data['confirm'])
			{
				$classes = " class='cms-confirm'";
			}
			if (isset($href_data['ext']) && (bool) $href_data['ext'])
			{
				$classes = " class='cms-ext'";
			}
			if (isset($href_data['add_url']) && (bool) $href_data['add_url'])
			{
				$href .= Request::detect_uri( );
			}
			$item_out .= <<<HERE
		<a href='{$href}'{$classes}>{$href_data['name']}</a>
HERE;
		}

		*/
	}
}


?>
<!--[if IE 7]>
<style>
.cms-logout span{
	margin: -17px 0 0 2px;
}
</style>
<![endif]-->
<!--<div id='console' style='border:1px dashed red; width:300px; height:40px;'></div>-->
<div class='cms-top'>
	<div class='cms-top-wrapper'>
		<?=$out?>
	</div>
	<a href='<?=Route::url('logout')?>' class='cms-logout'>выход</a>
	<div class='cms-login'><a href=''><?=User::get( )->login?></a></div>

	<label class='cms-top-switch'>Редактирование: <a href='<?=URL::site(Route::get('cms')->uri(array('action'=>'toggle')), 'http')?>'><?=Cms::state( ) ? 'выключить' : 'включить'?></a></label>
	<label class='cms-top-switch'><a href='<?=URL::site(Route::get('cms')->uri(array('action'=>'clear_cache')), 'http')?>'><?=__('clear cache')?></a></label>

</div>