<div id="content">

	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="5"><h2>API Keys</h2></td>
			</tr>
			<tr>
				<th width="20%">Created</th>
				<th width="25%">Note</th>
				<th width="40%">API Key</th>
				<th width="5%"><center>Status</center></th>
				<th width="10%">&nbsp;</th>
			</tr>
		</thead>
		
		<tbody>
			<?php
			$i = 0; //(A) used for background colour
	
			foreach($api_keys as $api) { 
	
				$i++; //(A)
				
				$post = ($i&1) ? 'even' : 'odd';
			?>
			<tr class="<?=$post;?>">
				<td><?=nice_date($api->created);?></td>
				<td><span id="<?=$api->id;?>" class="edit editapilabel" title="Click to edit..."><?=$api->label;?></span></td>
				<td>
					<?php if ($api->status == 1) { ?>
					<input style="width:95%;" name="key" value="<?=$api->key;?>" class="textbox" onclick="$(this).select();"/>
					<?php } else { ?>
					<strike><?=$api->key;?></strike>
					<?php } ?>
				</td>
				<td align="center"><?=status($api->status, 1);?></td>
				<td align="center">
					<?php if ($api->status == 1) { ?>
					<a href="<?=site_url("options/api/disable/$api->id");?>" class="button">Disable</a>
					<?php } else { ?>
					<a href="<?=site_url("options/api/enable/$api->id");?>" class="button">Enable</a>
					<?php } ?>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td colspan="5"><a href="<?=site_url('options/api/create');?>" class="button">Create a key</a></td>
			</tr>
		</tbody>
	</table>

</div>

<div id="sidebar">
	<h3>API Keys</h3>
	<p>API keys allow external services to connect to your store.</p>
</div>