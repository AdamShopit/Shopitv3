<form method="post" action="<?=site_url('orders/all');?>">
<input type="hidden" name="filter" value="true" />
<h3>Filter orders</h3>

<p><strong>Number of orders per page:</strong></p>

<p style="margin-bottom:10px;">
	<label><input type="radio" name="s_perpage" id="s_perpage" value=""<?=is_checked('',$s_perpage);?>/>25</label> &nbsp;
	<label><input type="radio" name="s_perpage" id="s_perpage" value="50"<?=is_checked('50',$s_perpage);?>/>50</label> &nbsp;
	<label><input type="radio" name="s_perpage" id="s_perpage" value="100"<?=is_checked('100',$s_perpage);?>/>100</label> &nbsp;
	<label><input type="radio" name="s_perpage" id="s_perpage" value="200"<?=is_checked('200',$s_perpage);?>/>200</label> &nbsp;
</p>

<p>Show me only those orders that match the following criteria:</p>

<p><strong>Order status is:</strong></p>
<ul>
	<?php
	$s = (object) array();
	$s->counter = 0;
	foreach($statuses as $status) {
	
		$s->counter++;

		// Set the unprocessed status
		if ($status->value == null) {
			$status->value = "Unprocessed";
		}

		// Create a dynamic variable in the form $s_orderstatus_1 based on the array item
		$dynamic_field_var = ${'s_orderstatus_'.$s->counter}; 

		// If orders is not filtered, then tick all the order status checkboxes by 
		// default (we are showing all orders here) except unprocessed on 'this 
		// weeks activity' page.
		// Set them all checked by default, unless filter has been used
		$auto_check_orderstatus_field = ($filter != 'true') ? 'checked="checked"' : is_checked($status->value, $dynamic_field_var);
		
		// Now make adjustments to the default checked statuses, depending on the page viewed
		if ($filter != 'true') {
			// Check all for 'View all orders' page
			if ($this->uri->segment(2) == "all") {
				$auto_check_orderstatus_field = 'checked="checked"';
			// Check all except "unprocessed" for weeks activity page
			} elseif ($status->value == "Unprocessed" and $this->uri->segment(2) == "") {
				$auto_check_orderstatus_field = '';
			}
		} else {
			is_checked($status->value, $dynamic_field_var);
		}
		
	?>
	<li>
		<label>
			<input type="checkbox" class="orderstatus" name="s_orderstatus_<?=$s->counter;?>" value="<?=rawurlencode($status->value);?>" <?=$auto_check_orderstatus_field;?> /> <span class="badge" style="background-color:<?=$status->color;?>"><?=$status->label;?></span>
		</label>
	</li>
	<?php } ?>
</ul>

<p><strong>Miscellaneous:</strong></p>
<ul>
	<li><label for="s_note"><input type="checkbox" name="s_note" id="s_note" value="true"<?=is_checked('true',$s_note);?>/>Has "Additional instructions" note</label></li>
</ul>

<p><strong>Search:</strong></p>

<div class="textbox-with-dropdown">
	<select name="s_search_type" id="s_search_type">
		<option value="customer" <?=is_selected('customer', $s_search_type);?>>Customer</option>
		<option value="orderno" <?=is_selected('orderno', $s_search_type);?>>Order No</option>
		<option value="coupon" <?=is_selected('coupon', $s_search_type);?>>Coupon</option>
	</select>
	<input type="text" name="s_search" id="s_search" value="<?=$s_search;?>" class="textbox" maxlength="75" placeholder="Enter keywords" />
</div>

<p><strong>Processed between following dates:</strong></p>
<p>
	<select name="s_from_day">
		<?php 
		if($s_from_day == null):
			$s_from_day = date('d'); 
		endif;
		for($i = 1; $i <= 31; $i++) { 
		$day = ($i<10) ? "0$i" : $i; //add the leading 0 if missing
		?>
		<option value="<?=$day;?>"<?=is_selected($day,$s_from_day);?>><?=$day;?></option>
		<?php } ?>
	</select>
	<?php
		if($s_from_month == null):
			$s_from_month = date('m'); 
		endif;
	?>
	<select name="s_from_month">
		<option value="01"<?=is_selected('01',$s_from_month);?>>January</option>
		<option value="02"<?=is_selected('02',$s_from_month);?>>February</option>
		<option value="03"<?=is_selected('03',$s_from_month);?>>March</option>
		<option value="04"<?=is_selected('04',$s_from_month);?>>April</option>
		<option value="05"<?=is_selected('05',$s_from_month);?>>May</option>
		<option value="06"<?=is_selected('06',$s_from_month);?>>June</option>
		<option value="07"<?=is_selected('07',$s_from_month);?>>July</option>
		<option value="08"<?=is_selected('08',$s_from_month);?>>August</option>
		<option value="09"<?=is_selected('09',$s_from_month);?>>September</option>
		<option value="10"<?=is_selected('10',$s_from_month);?>>October</option>
		<option value="11"<?=is_selected('11',$s_from_month);?>>November</option>
		<option value="12"<?=is_selected('12',$s_from_month);?>>December</option>
	</select>
	<select name="s_from_year">
		<?php 
		if ($s_from_year == null):
			$s_from_year = date('Y') - 1;
		endif;
		for($i = (date('Y')-5); $i <= date('Y'); $i++) { 
		?>
		<option value="<?=$i;?>"<?=is_selected($i,$s_from_year);?>><?=$i;?></option>
		<?php } ?>
	</select> and
</p>
<p>
	<select name="s_to_day">
		<?php 
		if($s_to_day == null):
			$s_to_day = date('d'); 
		endif;
		for($i = 1; $i <= 31; $i++) { 
		$day = ($i<10) ? "0$i" : $i; //add the leading 0 if missing
		?>
		<option value="<?=$day;?>"<?=is_selected($day,$s_to_day);?>><?=$day;?></option>
		<?php } ?>
	</select>
	<?php
		if($s_to_month == null):
			$s_to_month = date('m'); 
		endif;
	?>
	<select name="s_to_month">
		<option value="01"<?=is_selected('01',$s_to_month);?>>January</option>
		<option value="02"<?=is_selected('02',$s_to_month);?>>February</option>
		<option value="03"<?=is_selected('03',$s_to_month);?>>March</option>
		<option value="04"<?=is_selected('04',$s_to_month);?>>April</option>
		<option value="05"<?=is_selected('05',$s_to_month);?>>May</option>
		<option value="06"<?=is_selected('06',$s_to_month);?>>June</option>
		<option value="07"<?=is_selected('07',$s_to_month);?>>July</option>
		<option value="08"<?=is_selected('08',$s_to_month);?>>August</option>
		<option value="09"<?=is_selected('09',$s_to_month);?>>September</option>
		<option value="10"<?=is_selected('10',$s_to_month);?>>October</option>
		<option value="11"<?=is_selected('11',$s_to_month);?>>November</option>
		<option value="12"<?=is_selected('12',$s_to_month);?>>December</option>
	</select>
	<select name="s_to_year">
		<?php 
		if ($s_to_year == null):
			$s_to_year = date('Y');
		endif;
		for($i = (date('Y')-5); $i <= date('Y'); $i++) { 
		?>
		<option value="<?=$i;?>"<?=is_selected($i,$s_to_year);?>><?=$i;?></option>
		<?php } ?>
	</select>
</p>

<p align="right">
	<input type="submit" name="submit" value="Filter Orders" class="button" />
</p>
</form>

<form method="post" action="<?=site_url('orders/export');?>">
	<h3>Export CSV file</h3>
	<p>If you would like to export the filtered order results you see on the left, click the "Export CSV" button below.</p>
	
	<p align="right">
		<input type="hidden" name="export_filter" value="<?=$s_filter;?>" />
		<input type="submit" name="export" value="Export CSV" class="button" />
	</p>	
</form>
