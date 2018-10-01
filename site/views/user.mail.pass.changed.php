<h2><?=__um('user', 'hello')?>!</h2>
<p>
<?=__('Use this data for authorization:')?>
<ul>
<li><?=__u('username')?>: <?=$username?></li>
<li><?=__u('password')?>: <?=$password?></li>
</ul>
<br>
<?=__u('please don\'t reply to this mail')?>.
</p>
<p style='text-align:right;font-style:italic'>
<?=__('Best regards from :url', array(':url' => IDN::decodeIDN/*idn_to_utf8*/(URL::domain( ))))?>.
</p>
