<div class="gatewaytable">

	<h2>Sorry, your order could not be processed</h2>

	<p><WPDISPLAY ITEM=banner></p>
	
	<p><a href="<?=base_url();?>" class="btnShop">Return to homepage</a></p>

	<?php if ($this->config->item('WorldPaySetup') == 'TEST'):?>
	<div>
		<?php print_r($_POST); ?>
	</div>
	<?php endif;?>

</div>	
