<div id="content">

	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="5">
					<h2>Manage Shipping Rules
					<form id="formDisplayCountryRules" method="post" action="<?=site_url('shipping');?>" style="display:inline;">
					<select name="display_country" id="display_country">
						<?php foreach($shippingcountries as $display_country):?>
						<option value="<?=$display_country->country_name;?>" <?=is_selected($display_country->country_name,set_value('display_country',$this->input->post('display_country')));?> ><?=$display_country->country_name;?></option>
						<?php endforeach; ?>
					</select>
					<noscript>
					<input type="submit" name="submit" value="go" />
					</noscript>
					</form>
					<a href="<?=site_url('shipping/create');?>">Create new rule</a></h2>
				</td>
			</tr>
			<tr>
				<th width="20%">Country</th>
				<th width="20%">Shipping label</th>
				<th width="45%">Criteria</th>
				<th width="15%">Shipping cost</th>
				<th width="1">&nbsp;</th>
			</tr>
		</thead>
	
		<tbody>
	
			<?php 
			$i = 0; //(A) used for background colour
			if ($shippingrules > 0) {
			foreach($shippingrules as $rule): 
		
				if ($rule->criteria == 'total'):
					$metric = $this->config->item('currency');
					$value_1 = $metric . number_format($rule->value,2);
					$value_2 = $metric . number_format($rule->value2,2);
				elseif ($rule->criteria == 'category'):
					$value_1 = '';
					$value_2 = '';
					$rule->operation = '<strong>' . $this->category_model->getThisCategory($rule->operation)->cat_name . '</strong>';
				else:
					$metric = "kg";
					$value_1 = $rule->value . $metric;
					$value_2 = $rule->value2 . $metric;
				endif;
				
				$i++; //(A)
				
				if ($i&1) { $post = 'odd'; } 
				else { $post = 'even'; } //(A);
	
				if ($rule->operation == 'between'):
					$value2 = ' and ' . $value_2;
				else:
					$value2 = ''; 
				endif;
	
			?>
			<tr class="<?=$post;?>" id="<?=$rule->rule_id;?>">
				<td><?=$rule->country;?></td>
				<td><?=$rule->rule_name;?></td>
				<td>
					<?=ucfirst($rule->criteria);?> is <?=$rule->operation;?>
					<strong>
						<?=$value_1;?><?=$value2;?>
					</strong>
				</td>
				<td><?=$this->config->item('currency');?><?=$rule->shipping;?></td>
				<td>
					<ul class="actions">
						<li><a href="#" class="btn-action"><img src="<?=template_directory();?>/assets/images/btn-action-arrow-down.png" alt=""/></a>
							<ul>
								<li><a href="<?=site_url('shipping/edit/'.$rule->rule_id);?>">Edit rule</a></li>
								<li><a href="<?=site_url('shipping/delete/'.$rule->rule_id);?>" class="ajaxdelete">Delete rule</a></li>
							</ul>
						</li>
					</ul>
				</td>
			</tr>
	
			<?php endforeach;
			} else { ?>
			<tr>
				<td colspan="5">You have no rules setup yet. <a href="<?=site_url('shipping/create');?>" class="button">Create a shipping rule</a></td>
			</tr>
			<?php } ?>
		</tbody>
	</table>
	
	<table cellpadding="0" cellspacing="0" border="0">
		<thead>
			<tr>
				<td colspan="2"><h2>Add/Delete Shipping Locations</h2></td>
			</tr>
			<tr>
				<th width="20%">Country</th>
				<th width="80%">Actions</th>
			</tr>
		</thead>
	
		<tbody id="listcountries">	
			<?php 
			$i = 0; //(A) used for background colour
			foreach($shippingcountries as $country): 
				
				$i++; //(A)
				
				if ($i&1) { 
					$post = 'odd'; 
				} 
				else { 
					$post = 'even'; 
				} //(A);
				
				if ($i==6){$i = 0;}	
			?>
			<tr class="table-row <?=$post;?>">
				<td><?=$country->country_name;?></td>
				<td>
					<?php if ($country->is_home == '0'):?>
					<a href="<?=site_url('shipping/deletecountry/'.$country->country_id);?>" class="ajaxdelete button">Remove</a>
					<?php endif; ?>
				</td>
			</tr>				
			<?php endforeach; ?>			
		</tbody>

		<tfoot>
			<tr class="table-row">
				<td colspan="2" class="table-row">
					<h3>Add another country</h3>
					<form id="formAddCountry" action="<?=site_url('shipping/addcountry');?>" method="post">
						<select name="country_name" id="country_name" class="dropdown">
						<?php foreach($iso_countries as $iso_country):?>
							<option value="<?=$iso_country->country_name;?>"><?=$iso_country->country_name;?></option>
						<?php endforeach;?>
						</select>
						<input type="submit" class="button" value="Add country" />
						<span id="feedback"></span>
					</form>
				</td>
			</tr>
		</tfoot>
	</table>

</div>

<div id="sidebar">
	<h3>About shipping rules</h3>
	<p>Total is basket total of items only i.e. excluding the shipping. Ensure you cover <strong>ALL</strong> the countries you have set in the 'Manage Countries' section below.</p>
</div>