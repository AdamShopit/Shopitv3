<div class="gatewaytable">

	<h2>Thank you for your order</h2>

	<WPDISPLAY ITEM=banner>
	
	<p><a href="<?=site_url();?>" class="btnShop">Return to homepage</a></p>
	
	<?php if ($this->config->item('WorldPaySetup') == 'TEST'):?>
	<div>
		<?php print_r($_POST); ?>
	</div>
	<?php endif;?>

</div>	
