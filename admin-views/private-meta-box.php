<?php
/**
* Events post main metabox
*/

// Don't load directly
if ( !defined('ABSPATH') ) { die('-1'); }

?>
<script type="text/javascript">
console.log('loaded');
</script>

<style type="text/css">
</style>

<table class="form-table">
	<tbody>
		<tr class="form-field">
			<th scope="row" valign="top"></th>
			<td><p><input type="checkbox" id="id_private" name="private" value="1" <?php echo $checked;?> style="width: auto;"> <label for="id_private">Private Category</label></p></td>
		</tr>
	</tbody>
</table>
