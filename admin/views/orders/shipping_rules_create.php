<div id="content">
	
	<div class="table">

		<h2><?=$form_title;?></h2>
	
		<?php if (validation_errors()) { ?>
		<p class="error_notice">Sorry, we found some errors with your shipping rule. Please check below.</p>
		<?php } ?>
	
		<div class="table-row">
			<h3>Give it a name</h3>
		</div>

		<div class="table-row">
			<label>Enter title: <span class="red">*</span></label>
			<input type="text" name="rule_name" value="<?=set_value('rule_name',$shipping->rule_name);?>" size="45" class="textbox required" <?=tooltip("This name will appear in the store's checkout/basket, so we recommend using a name that will make sense to your customers e.g. Free shipping, Standard delivery, Next day delivery, etc.");?>/>
			<?=form_error('rule_name');?>
		</div>
	
		<div class="table-row">
			<h3>Set rule criteria</h3>
		</div>

		<div class="table-row">
			<label>Select country:</label>
			<select name="country" id="country" class="dropdown">
				<?php foreach($countries as $country):?>
				<option value="<?=$country->country_name;?>" <?=is_selected($country->country_name,set_value('country',$shipping->country));?> ><?=$country->country_name;?></option>
				<?php endforeach; ?>
			</select>
		</div>
		
		<div class="table-row">
			<label>If:</label>
			<select name="criteria" id="criteria" class="dropdown">
				<option value="total" <?=is_selected('total',set_value('criteria',$shipping->criteria));?>>Total (<?=$this->config->item('currency');?>)</option>
				<option value="weight" <?=is_selected('weight',set_value('criteria',$shipping->criteria));?>>Weight (Kg)</option>
				<option value="category" <?=is_selected('category',set_value('criteria',$shipping->criteria));?>>Category</option>
			</select>
		</div>
				
		<div class="table-row">
			<label>Is:</label>
			<span id="shipping_criteria">
			
				<?php 
				// Load the correct shipping rule template
				if ($shipping->criteria == "category") { 
				?>
				<select name="operation" id="shipping_operation" class="dropdown">
					<?=$this->load->view('orders/shipping_rules_category');?>
				</select>
				<input type="hidden" name="value" value="0.00" />
				<?php } else {
				$this->load->view('orders/shipping_rules_totals');
				}
				?>
				
				<?php
				if ($shipping->value == ''):
					$value1 = '25.00';
				else:
					if ($shipping->criteria == 'total'):
					$value1 = number_format($shipping->value,2);
					else:
					$value1 = $shipping->value;
					endif;
				endif;	
				?>
				
				<input type="text" name="value" value="<?=set_value('value',$value1);?>" size="5" class="textbox" <?php if ($shipping->criteria == "category") {?>style="display:none;"<?php } ?> />
				<?=form_error('value');?>
				
				<?php
				if ($shipping->value2 == ''):
					$value2 = '';
				else:
					if ($shipping->criteria == 'total'):
					$value2 = number_format($shipping->value2,2);
					else:
					$value2 = $shipping->value2;
					endif;
				endif;	
				
				if ($shipping->operation == 'between' || $this->input->post('operation') == 'between'):
					$css_display = '';
				else: 
					$css_display = 'style="display:none;"';
				endif;
				?>
				
				<span id="shipping_value2" <?=$css_display;?>>
					and <input type="text" name="value2" value="<?=set_value('value2',$value2);?>" size="5" class="textbox is_required"/>
					<?=form_error('value2');?>
				</span>

			</span>
		</div>
	
		<div class="table-row">
			<label>Set shipping to:</label>
			<?php
			if ($shipping->shipping == ''):
				$shipping_amt = '4.95';
			else:
				$shipping_amt = $shipping->shipping;
			endif;	
			?>
			<?=$this->config->item('currency');?> <input type="text" name="shipping" value="<?=set_value('shipping',$shipping_amt);?>" size="5" class="textbox"/>		
			<?=form_error('shipping');?>
		</div>
	
		<input type="hidden" name="rule_id" value="<?=$shipping->rule_id;?>" />
	
	</div>

</div>

<div id="sidebar">
	<h3>Creating rules</h3>
	<p>Please ensure that you do not create shipping rules that conflict with each other.</p>
</div>