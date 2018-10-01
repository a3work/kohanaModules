<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link href="http://fonts.googleapis.com/css?family=PT+Sans&amp;subset=latin,cyrillic" rel="stylesheet" type="text/css">
	<?=InclStream::instance( )->add('reset.css')?>
	<?=InclStream::instance( )->add('browser.css')?>
	<?=InclStream::instance( )->add('jquery-1.8.3.min.js', NULL, 10)?>
	<?=InclStream::instance( )->add('browser.js')?>
	<?=InclStream::instance( )->render( )?>
</head>
<body>
<div class='main'>
	<div class='main-folder'><?=$content?></div>
</div>
</body>
</html>