<div id="content">

		<table cellpadding="0" cellspacing="0" border="0">
			<thead>
				<tr>
					<td colspan="9">
						<h2><?=$form_title;?> <a href="<?=site_url('modules/coupons#create');?>">Create Coupon</a></h2>
					</td>
				</tr>
				<tr>
					<th width="24%">Label</th>
					<th width="15%">Coupon Code</th>
					<th width="10%"><center>Discount</center></th>
					<th width="10%">Min Spend</th>
					<th width="10%"><center>Expiry Date</center></th>
					<th width="10%"><center>Max Uses</center></th>
					<th width="10%"><center>Uses</center></th>
					<th width="10%"><center>Products</center></th>
					<th width="1%">&nbsp;</th>
				</tr>
			</thead>
			
			<tbody>
			<?php
			if ($coupons) {
			$i = 0; //(A) used for background colour 
			
			foreach($coupons as $item): 
		
				$i++; //(A)
				
				if ($i&1) { $post = 'odd'; } 
				else { $post = 'even'; } //(A);
				
				// Set the bulk apply/revoke segment uri value
				$bulk_apply  = base64_encode(sprintf('%s-1', $item->id));
				$bulk_revoke = base64_encode(sprintf('%s-0', $item->id));
			?>
				<tr class="<?=$post;?>">
					<td><?=$item->label;?></td>
					<td><?=$item->code;?></td>
					<td align="center">

					<?php
					// Check if value contains the percentage, else display as money value
					if (substr_count($item->discount,'%') > 0) {
						echo $item->discount;
					}
					else {
						echo money($item->discount);
					}
					?>
					</td>
					<td><?=money($item->max_spend);?></td>
					<td align="center" class="nowrap">
					<?php
					if ($item->expires > 0) {
						echo nice_date($item->expires, false);
					} else {
						echo '&infin;';
					}
					?>
					</td>
					<td align="center"><?=$item->max_uses;?></td>
					<td align="center"><?=$item->counter;?></td>
					<td align="center"><?=$this->modules_model->countCouponProducts($item->field_name);?></td>
					<td class="nowrap">
						<ul class="actions">
							<li><a href="#" class="btn-action"><img src="<?=template_directory('assets/images/btn-action-arrow-down.png');?>" alt=""/></a>
								<ul>
									<li><a href="<?=site_url("modules/coupons/edit/$item->id#edit");?>">Edit</a></li>
									<li class="nav-separator lineonly"></li>
									<li><a href="<?=site_url("modules/coupons/apply/$bulk_apply");?>">Apply to all products</a></li>
									<li><a href="<?=site_url("modules/coupons/apply/$bulk_revoke");?>">Revoke from all products</a></li>
									<li class="nav-separator lineonly"></li>
									<li><a href="<?=site_url('modules/coupons/delete/' . $item->id);?>" class="ajaxdelete">Delete</a></li>
								</ul>
							</li>
						</ul>
					</td>
				</tr>
			<?php 
			endforeach; 
			} else {
			?>
				<tr>
					<td colspan="9" align="center">There are no coupons setup.</td>
				</tr>				
			<?php } ?>
			</tbody>
			
		</table>

	<div class="table">
		
		<div class="table-row">
			<?php if (!empty($edit)) { ?>
			<h3 id="edit">Edit coupon</h3>
			<?php } else { ?>
			<h3 id="create">Create a new coupon</h3>
			<?php } ?>
		</div>

		<div class="table-row">
			<label>Label:</label>
			<input type="text" name="coupon_label" value="<?=set_value('coupon_label', $edit->label);?>" class="textbox" size="35" maxlength="128" <?=tooltip('Enter a description for this coupon e.g. 10% off. This is for office use only.');?> />
			<?=form_error('coupon_label');?>
		</div>
		
		<div class="table-row">
			<label>Coupon Code:</label>
			<input type="text" name="coupon_code" value="<?=set_value('coupon_code', $edit->code);?>" class="textbox" size="35" maxlength="10" <?=tooltip('Enter the code a customer would enter during checkout. Max 10 characters.');?> />
			<?=form_error('coupon_code');?>
		</div>

		<div class="table-row">
			<label>Discount:</label>
			<input type="text" name="coupon_discount" value="<?=set_value('coupon_discount', $edit->discount);?>" class="textbox" size="35" maxlength="10" <?=tooltip('Enter a price or a percentage e.g. 5.00 or 10%.');?> />
			<?=form_error('coupon_discount');?>
		</div>

		<div class="table-row">
			<label>Minimum Spend:</label>
			<input type="text" name="coupon_maxspend" value="<?=set_value('coupon_maxspend', $edit->max_spend);?>" class="textbox" size="35" maxlength="10" <?=tooltip("Enter the maximum spend this coupon will apply to or leave empty for no limit.");?> />
			<?=form_error('coupon_maxspend');?>
		</div>

		<div class="table-row">
			<label>Expires on:</label>
			<input type="text" name="coupon_expires" value="<?=set_value('coupon_expires', $edit->expires);?>" class="textbox jqueryui-date-picker" size="35" maxlength="10" <?=tooltip("Set an expiry date for this coupon or leave empty to keep it running continuously.");?> />
			<?=form_error('coupon_expires');?>
		</div>
		
		<div class="table-row">
			<label>Max Uses:</label>
			<input type="text" name="coupon_maxuses" value="<?=set_value('coupon_maxuses', $edit->max_uses);?>" class="textbox" size="35" maxlength="10" <?=tooltip("Enter the maximum amount of uses for this coupon.");?> />
			<?=form_error('coupon_maxuses');?>
		</div>

		<input type="hidden" name="coupon_id" value="<?=set_value('coupon_id', $edit->id);?>" />

	</div>

</div>

<div id="sidebar">
	<h3>Manage Coupons</h3>
	<p>Use this page to setup coupon codes which your customers can enter in their basket to obtain a discount.</p>
</div>