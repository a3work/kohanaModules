<tr>
<td><?=$username?><small><?=$label?></small></td>
<td class='cms-ctrl'>
<?=View::factory('user.item.ctrl', array(
	'flags' => $flags,
	'list' => $list,
	'id' => $id,
))->render( )?>
</td>
</tr>