<?php
$show_right = isset($show_right) && $show_right === TRUE;
$show_left = isset($show_left) && $show_left === TRUE;
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=8">
<?//=InclStream::instance( )->add('reset.css', FALSE, 3)?>
<?=InclStream::instance( )->add('cms.css', FALSE, -1)?>
<?=InclStream::instance( )->jquery( )?>
<?=InclStream::instance( )->render( )?>
</head>
<body class='cms'>
<?=$menu?>
<div class='cms-wrapper'>
<? if ($show_left): ?>
	<div class='cms-left'><?=$left?></div>
<? endif; ?>
<? if ($show_right): ?>
	<div class='cms-right'><?=$right?></div>
<? endif; ?>
	<div class='cms-content-simple<?=$show_left ? ' cms-content-simple-l' : ''?><?=$show_right ? ' cms-content-simple-r' : ''?>'>
		<h2><?=isset($parent) ? "<a href='$parent_href'>$parent</a>" : ''?><?=$header?></h2>
<?=$body?>
	</div>
</div>
</body>
</html>
