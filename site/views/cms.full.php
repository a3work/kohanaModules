<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<?//<meta http-equiv="X-UA-Compatible" content="IE=8">?>
<?//=InclStream::instance( )->add('reset.css', FALSE, 3)?>
<?=InclStream::instance( )->add('cms.css', FALSE, -1)?>
<?=InclStream::instance( )->add('cms.ext.css', FALSE, -2)?>
<?=InclStream::instance( )->jquery( )?>
<?=InclStream::instance( )->render( )?>
</head>
<body>
<div class='cms-content-stub'>
	<div class='cms-content'>

	</div>
</div>
<?=$menu?>
<div class='cms-wrapper'>
	<div class='cms-left'><?=isset($left) ? $left : ''?></div>
	<div class='cms-right'><?=isset($right) ? $right : ''?></div>
	<div class='cms-content'>
		<h2><?=isset($parent) ? "<a href='$parent_href'>$parent</a>" : ''?><?=$header?></h2>
<?=$body?>
	</div>
</div>
<div class='cms-footer'><?=isset($footer) ? $footer : ''?></div>
</body>
</html>
