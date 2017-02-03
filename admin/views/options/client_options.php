<div id="content">

	<div class="table">

		<h2><?=$form_title;?></h2>

		<div class="table-row">
			<h3>Store Details</h3>
		</div>
	
		<div class="table-row">
			<label>Store name: </label> 
			<input name="store_name" value="<?=set_value('store_name',$store_name);?>" class="textbox required" size="75" maxlength="50" <?=tooltip("Enter the name of the store. This is displayed in the browser's title bar.");?> />
		</div>
		
		<div class="table-row">
			<label>Store email: </label> 
			<input name="store_email" value="<?=set_value('store_email',$store_email);?>" class="textbox required email" size="75" maxlength="50" <?=tooltip("This email is used to receive new order alerts.");?> />
		</div>	
		
		<!-- Company Details -->
		<div class="table-row">
			<h3>Company Details</h3>
		</div>
	
		<div class="table-row">
			<label>Company name: </label> 
			<input name="company_name" value="<?=set_value('company_name',$company_name);?>" class="textbox required" size="75" />
		</div>	
	
		<div class="table-row">
			<label>Company address: </label> 
			<textarea name="company_address" class="textbox required" rows="5"><?=set_value('company_address',$company_address);?></textarea>
		</div>	
	
		<div class="table-row">
			<label>Company telephone: </label> 
			<input name="company_tel" value="<?=set_value('company_tel',$company_tel);?>" class="textbox required" size="75" maxlength="35" />
		</div>	
	
		<div class="table-row">
			<label>Company fax: </label> 
			<input name="company_fax" value="<?=set_value('company_fax',$company_fax);?>" class="textbox" size="75" maxlength="35" />
		</div>	
		
		<div class="table-row">
			<label>Company email: </label> 
			<input name="company_email" value="<?=set_value('company_email',$company_email);?>" class="textbox required" size="75" maxlength="50" />
		</div>	
	
		<div class="table-row">
			<label>Company registration: </label> 
			<textarea name="company_reg" class="textbox" rows="5"><?=set_value('company_reg',$company_reg);?></textarea>
		</div>	
	
		<!-- Taxes/Stock -->
		<div class="table-row">
			<h3>Taxes &amp; Stock</h3>
		</div>
	
		<div class="table-row">
			<label>VAT rate: </label> 
			<input name="vat_rate" value="<?=set_value('vat_rate',$vat_rate);?>" class="textbox required number" size="75" maxlength="5" <?=tooltip("This must be in <strong>decimal</strong> format i.e 0.175 = 17.5%.");?> />
		</div>	
	
		<div class="table-row">
			<label>Base rate: </label> 
			<input name="base_rate" value="<?=set_value('base_rate',$base_rate);?>" class="textbox required" size="75" maxlength="5" <?=tooltip("This rate is added to all product prices (not sale prices). It can be a percentage (e.g. 20%) or a price (e.g. 2.99).");?> />
		</div>	

	</div>

</div>

<div id="sidebar">
	<h3>Edit store preferences</h3>
	<p class="redtext"><strong>All fields must be complete.</strong></p>
</div>