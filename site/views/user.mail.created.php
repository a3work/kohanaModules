<?php $href = Route::url('login', array(), 'http'); ?>
<h2><?=__u('registration completed')?></h2>
<p>
<?=__um('user', 'hello')?>.<br><?=__('Registration on :url complete successfuly.', array(':url' => IDN::decodeIDN/*idn_to_utf8*/(URL::domain( ))))?><br>
<?=__('Use this data for authorization:')?>
<ul>
<li><?=__u('username')?>: <?=$username?></li>
<li><?=__u('password')?>: <?=$password?></li>
</ul>
<br>
<?=__u('enter username and password on page :href ', array(':href' => HTML::factory('anchor')->text($href)->href($href)))?>.<br><br>
<?=__u('please don\'t reply to this mail')?>.
</p>
<p style='text-align:right;font-style:italic'>
<?=__('Best regards from :url', array(':url' => IDN::decodeIDN/*idn_to_utf8*/(URL::domain( ))))?>.
</p>
