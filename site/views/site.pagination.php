<?php

$max_quan = 7;

$page_quan = ceil($max / $rows_per_page);

$out = '<span class="pgn-total">'.__('total :count', array(':count' => $max)).'</span>';

if ($page_quan > 1)
{

	if ($page_quan > $max_quan)
	{
		switch ($page)
		{
			case 1:
				$view_arr = array(1, $page_quan, $page, $page+2, $page+1, ceil(($page_quan - $page+2)/2));
				break;
			case $page_quan;
				$view_arr = array(1, $page_quan, $page, $page-1, $page-2, ceil(($page-1)/2));
				break;
			default:
				$view_arr = array(1, $page_quan, $page, $page-1, $page+1, ceil(($page-1)/2), ceil(($page_quan - $page+1)/2 + $page));

		}

		for ($i = 1; $i <= $page_quan; $i ++)
		{
			if ( ! in_array($i, $view_arr))
				continue;

			$title = 'Перейти на страницу '.$i;
			$class = $delimiter_l = $delimiter_r = '';

			if ($i == $page)
			{
				$class = 'class="curr" ';
			}
			elseif ($i == $view_arr[5] && $view_arr[5] != 1 || isset($view_arr[6]) && $i == $view_arr[6] && $view_arr[6] != $page_quan)
			{
				$class = 'class="mid"';
				if ( ! in_array(($i - 1), $view_arr))
				{
					$delimiter_l = '&hellip;';
				}
				if ( ! in_array(($i + 1), $view_arr))
				{
					$delimiter_r = '&hellip;';
				}
			}

			$out .= $delimiter_l.'<a title="'.$title.'" '.$class.'href="'.Request::current( )->url( ).'?'.http_build_query(array_merge(Request::current( )->query( ), array(Pagination::PAGE_QUERY_VAR => $i))).'">'.$i.'</a>'.$delimiter_r;
		}
	}
	else
	{
		for ($i = 1; $i <= $page_quan; $i ++)
		{
			$class = '';
			$title = 'Перейти на страницу '.$i;

			if ($i == $page)
			{
				$class = 'class="curr" ';
			}

			$out .= '<a title="'.$title.'" '.$class.'href="'.Request::current( )->url( ).'?'.http_build_query(array_merge(Request::current( )->query( ), array(Pagination::PAGE_QUERY_VAR => $i))).'">'.$i.'</a> ';
		}
	}

}
echo "<div class='pgn'>$out</div>";