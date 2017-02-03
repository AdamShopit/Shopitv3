<div id="content">

		<table cellpadding="0" cellspacing="0" border="0">
			<thead>
				<tr>
					<td colspan="3"><h2><?=$form_title;?></h2></td>
				</tr>
				<tr>
					<th width="80%">Offer</th>
					<th width="20%">&nbsp;</th>
				</tr>
			</thead>
			
			<tbody>
			<?php
			if ($special_offers) {
			$i = 0; //(A) used for background colour 
			
			foreach($special_offers as $item): 
		
				$i++; //(A)
				
				if ($i&1) { $post = 'odd'; } 
				else { $post = 'even'; } //(A);
			?>
				<tr class="<?=$post;?>">
					<td><?=$item->label;?></td>
					<td align="center">
						<a href="<?=site_url('modules/specialoffers/edit/' . $item->id);?>" class="button">Edit</a>
						<a href="<?=site_url('modules/specialoffers/delete/' . $item->id);?>" class="button ajaxdelete">Delete</a>
					</td>
				</tr>
			<?php 
			endforeach; 
			} else {
			?>
				<tr>
					<td colspan="3" align="center">There are no special offers setup.</td>
				</tr>				
			<?php } ?>
			</tbody>
			
		</table>

	<div class="table">
		
		<div class="table-row">
			<?php if (!empty($edit)) { ?>
			<h3>Edit special offer</h3>
			<?php } else { ?>
			<h3>Create a new special offer</h3>
			<?php } ?>
		</div>
		
		<div class="table-row">
			<label>Buy:</label>
			<input type="text" name="specialoffer_buy" value="<?=set_value('specialoffer_buy', $edit->buy);?>" class="textbox" size="35" maxlength="4" <?=tooltip('Enter the quantity to buy');?> />
			<?=form_error('specialoffer_buy');?>
		</div>

		<div class="table-row">
			<label>Get Free:</label>
			<input type="text" name="specialoffer_get" value="<?=set_value('specialoffer_get', $edit->get);?>" class="textbox" size="35" maxlength="4" <?=tooltip('Enter the quantity received free.');?> />
			<?=form_error('specialoffer_get');?>
		</div>

		<div class="table-row">
			<label>Description:</label>
			<input type="text" name="specialoffer_label" value="<?=set_value('specialoffer_label', $edit->label);?>" class="textbox" size="75" maxlength="128" <?=tooltip('Enter a description for this offer e.g. Buy 2 get 1 free. This description will be displayed to the customer.');?> />
			<?=form_error('specialoffer_label');?>
		</div>

		<input type="hidden" name="specialoffer_id" value="<?=set_value('specialoffer_id', $edit->id);?>" />

	</div>

</div>

<div id="sidebar">
	<h3>Manage special offers</h3>
	<p>Add text here...</p>
</div>