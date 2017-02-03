<div id="content">
	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="4"><h2>Redirections</h2></td>
			</tr>
			<tr>
				<th width="42%">Old URL</th>
				<th width="42%">Redirects to</th>
				<th width="8%"><center>Status</center></th>
				<th width="8"><center>Actions</center></th>
			</tr>
		</thead>
	
		<tbody>
	<?php
		$i = 0; //(A) used for background colour 
		
		if ($redirections > 0) {
		
		foreach($redirections as $redirect): 
	
			$i++; //(A)
			
			if ($i&1) { $post = 'odd'; } 
			else { $post = 'even'; } //(A);
	?>
		
		<tr class="<?=$post;?>" id="<?=$redirect->product_id;?>">
			<td><a href="<?=site_root($this->config->item('site_root_index_page').$redirect->old_url);?>" target="_blank"><?=$redirect->old_url;?></a></td>
			<td><a href="<?=site_root($this->config->item('site_root_index_page').$redirect->new_url);?>" target="_blank"><?=$redirect->new_url;?></a></td>
			<td align="center"><?=$redirect->status_code;?></td>
			<td align="center"><a href="<?=site_url('options/redirection/delete/' . $redirect->id);?>" class="ajaxdelete button">Delete</a></td>
		</tr>
	
		<?php 
		endforeach; 
		} else { 
		?>
		<tr>
			<td colspan="4" align="center">No redirects could be found.</td>
		</tr>
		<?php } ?>
		
		</tbody>
	
		<tfoot>
			<tr>
				<td colspan="4"><?=$this->pagination->create_links();?></td>
			</tr>
		</tfoot>
	
	</table>
</div>

<div id="sidebar">
	<form method="post" action="<?=site_url('options/redirection');?>">
	<h3>Redirection</h3>
	<p style="margin-bottom:10px;">This page shows all the redirects that have been setup.</p>
	<p><strong>Add a new redirect</strong></p>
	<p>Select the redirect status code:</p>
	<p style="margin-bottom:10px;">
		<select name="s_statuscode" class="dropdown">
			<option value="301">Moved permanently (301)</option>
			<option value="302">Moved temporarily (302)</option>
		</select>
	</p>
	
	<p>Enter the URL that needs redirecting without <?=site_root();?></p>
	<p><textarea name="s_oldurl" class="textbox"></textarea></p>

	<p>And now the URL to redirect to without <?=site_root();?></p>
	<p><textarea name="s_newurl" class="textbox"></textarea></p>

	<p align="right">
		<input type="hidden" name="filter" value="true" />
		<input type="hidden" name="redirect_url" value="<?=current_url();?>" />
		<input type="submit" name="submit" value="Add Redirect" class="button" />
	</p>
	</form>
</div>