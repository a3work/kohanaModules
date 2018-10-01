<ul>
<li><a href='<?=Route::url('user_list', array('list' => 'accounts'))?>'><?=__u('accounts')?></a> <small><?="({$count['users']}) "?></small></li>
<li><a href='<?=Route::url('user_list', array('list' => 'groups'))?>'><?=__u('user groups')?></a> <small><?="({$count['groups']}) "?></small></li>
</ul>