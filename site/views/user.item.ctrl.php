<?
if ($flags['edit'])
{
?>

	<a href='<?=Route::url('user_manage', array('list'=>$list, 'id'=>$id))?>' class='cms-edit' title='<?=($list == 'accounts' ? __('edit account') : __('edit group'))?>'></a>
<?
}
if ($flags['attr'])
{
?>
	<a href='<?=Route::url('user_attr', array('list'=>$list, 'id'=>$id))?>' class='cms-attr' title='<?=__('attributes of')?>'></a>
<?
}
if ($flags['acc'])
{
?>
	<a href='<?=Route::url('user_access', array('list'=>$list, 'id'=>$id))?>' class='cms-acc' title='<?=__('permissions of')?>'></a>
<?
}
if ($flags['del'])
{
?>
	<a href='<?=Route::url('user_delete', array('list'=>$list, 'id'=>$id))?>' class='cms-del' title='<?=__('delete')?>'></a>
<?
}
?>