<!DOCTYPE HTML>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<link rel="icon" href="<?=Route::url('static_files', array('filetype' => 'img', 'file' => 'favicon.ico'))?>" type="image/x-icon">
	<title><?=$settings['title']?></title>
	<meta name="keywords" content="<?=$settings['keywords']?>"/>
	<meta name="description" content="<?=$settings['description']?>"/>
	<?//=InclStream::instance( )->add('style.css', FALSE, 1)?>
	<?//=InclStream::instance( )->add('main.js', FALSE, -1)?>
	<?=InclStream::instance( )->render( )?>
</head>
<body>
<?=$admin_menu?>
<?=$breadcrumbs?>
<h1>It works!</h1>
<p>Система установлена корректно.
<br>
Установите шаблон, разделив его на общие для всех страниц header и footer, а так же дефолтный body.
<br>
<br>
<i>&copy;Bicycle_CMS team, 2013&mdash;<?=date('Y')?></i></p>
<?=$menu['main']?>