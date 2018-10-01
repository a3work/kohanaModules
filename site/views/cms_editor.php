<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" SYSTEM "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>

<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=8">
	<?=InclStream::instance( )->add('jquery-1.8.3.min.js', FALSE, 10)?>
    <?=InclStream::instance( )->add('cms.css')?>
    <?=InclStream::instance( )->add('cms.incl.css')?>
    <?=InclStream::instance( )->add('cms_iframe.init.js')?>
	<?=InclStream::instance( )->render( )?>
</head>
<body class='data cms-editor'>
	<div class='cms-editor-wrapper'>
		<?=$content?>
	</div>
</body>
</html>