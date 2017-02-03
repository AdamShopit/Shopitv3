<?php
$address_type = ($this->uri->segment(3) == '' || $this->uri->segment(3) == 'billing') ? 'billing' : 'delivery';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>Shopit - <?=$title;?></title>

<link href="<?=template_directory('assets/styles/shopit.css');?>" rel="stylesheet" type="text/css" media="screen" title="default"/>
<link href="<?=template_directory('assets/styles/shopit_styles.css');?>" rel="stylesheet" type="text/css" media="screen" title="default"/>

<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery.min.js?v=v1.10.2');?>"></script>
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery-ui.min.js?v=1.10.3');?>"></script>
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery.qtip.min.js');?>"></script>
<script type="text/javascript">
$(document).ready(function() {

	//Add border to last column header
	$('table').each(function(){
		$(this).find('th:last').addClass('last-column');
	});
	
	//Copy item to parent screen
	$('.copyitem').click(function(){
    	
    	var firstname 	= $(this).closest('tr').find('input[name="firstname"]').val();
    	var surname 	= $(this).closest('tr').find('input[name="surname"]').val();
    	var company 	= $(this).closest('tr').find('input[name="company"]').val();
    	var address1 	= $(this).closest('tr').find('input[name="address1"]').val();
    	var address2 	= $(this).closest('tr').find('input[name="address2"]').val();
    	var city 		= $(this).closest('tr').find('input[name="city"]').val();
    	var postcode 	= $(this).closest('tr').find('input[name="postcode"]').val();
    	var country 	= $(this).closest('tr').find('input[name="country"]').val();
		<?php if ($address_type == 'billing') { ?>
    		var account_id 	= $(this).closest('tr').find('input[name="account_id"]').val();
    		var email = $(this).closest('tr').find('input[name="email"]').val();
			var phone = $(this).closest('tr').find('input[name="phone"]').val();
    	<?php } ?>

    	$('input[name="<?=$address_type;?>_firstname"]', window.parent.document).val(firstname);
     	$('input[name="<?=$address_type;?>_surname"]', window.parent.document).val(surname);
    	$('input[name="<?=$address_type;?>_company"]', window.parent.document).val(company);
    	$('input[name="<?=$address_type;?>_address1"]', window.parent.document).val(address1);
    	$('input[name="<?=$address_type;?>_address2"]', window.parent.document).val(address2);
    	$('input[name="<?=$address_type;?>_city"]', window.parent.document).val(city);
    	$('input[name="<?=$address_type;?>_postcode"]', window.parent.document).val(postcode);
    	$('input[name="<?=$address_type;?>_country"]', window.parent.document).val(country);
		<?php if ($address_type == 'billing') { ?>
    		$('input[name="account_id"]', window.parent.document).val(account_id);
    		$('input[name="customer_email"]', window.parent.document).val(email);
			$('input[name="customer_phone"]', window.parent.document).val(phone);
    	<?php } ?>

		parent.$.fancybox.close();	
	});

});
</script>
<style>
body {
	margin: 50px auto 0px auto;
}

#filter {
	width: 100%;
	height: 26px;
	padding: 7px 10px;
	margin: 0 auto;
	background-color: #2e2b28;
	color: white;
	line-height: 130%;
	position: fixed;
	top: 0;
	left: 0;
	font-weight: 500;
	font-size: 13px;
	box-shadow: 0 0 10px rgba(0,0,0,0.8);
	z-index: 99;
	clear: both;
	text-align: center;
}
</style>
</head>

<body class="<?=body_class();?>">

	<form method="post" action="<?=site_url("customers/lookup/$address_type");?>">
	<div id="filter">
		<label>Enter any part of customer name or company name</label>
		<input id="fullname" name="fullname" class="textbox" value="" />
		<input type="submit" name="submit" value="Lookup" />
	</div>
	</form>

	<table width="100%" cellpadding="0" cellspacing="0" border="0">
	
		<thead>
			<tr>
				<th width="20%">Name</th>
				<th width="15%">Company</th>
				<th width="20%">Street</th>
				<th width="20%">City</th>
				<th width="15%">Postcode</th>
				<th width="5%"><center>Has Account?</center></th>
				<th width="5%">&nbsp;</th>
			</tr>
		</thead>
		<tbody>

	<?php if ($customers > 0): 
		$i = 0; //(A) used for background colour
		$id = 0;
		
		foreach($customers as $item): 
	
			$i++; //(A)
			$id++;
			
			if ($i&1) { $post = 'odd'; } 
			else { $post = 'even'; } //(A);
	?>
		
		<tr class="<?=$post;?>" id="c<?=$id;?>">
			<td><?=capitalise($item->fullname);?></td>
			<td><?=capitalise($item->company);?></td>
			<td><?=$item->address1;?></td>
			<td><?=$item->city;?></td>
			<td><?=$item->postcode;?></td>
			<td align="center">
				<?php
				if ($item->account_id > 0) {
					echo sprintf('<span class="badge badge-green">%s</span>', 'Yes');
				} else {
					echo "";
				}
				?>
			</td>
			<td align="center">
				<input type="hidden" name="account_id" value="<?=$item->account_id;?>" />
				<input type="hidden" name="title" value="<?=$item->billing_title;?>" />
				<input type="hidden" name="firstname" value="<?=capitalise($item->firstname);?>" />
				<input type="hidden" name="surname" value="<?=capitalise($item->surname);?>" />
				<input type="hidden" name="company" value="<?=capitalise($item->company);?>" />
				<input type="hidden" name="address1" value="<?=capitalise($item->address1);?>" />
				<input type="hidden" name="address2" value="<?=capitalise($item->address2);?>" />
				<input type="hidden" name="city" value="<?=capitalise($item->city);?>" />
				<input type="hidden" name="postcode" value="<?=uppercase($item->postcode);?>" />
				<input type="hidden" name="country" value="<?=$item->country;?>" />
				<input type="hidden" name="email" value="<?=$item->customer_email;?>" />
				<input type="button" class="button copyitem" value="Copy" rel="c<?=$id;?>" />
			</td>
		</tr>
	
	<?php 
		endforeach; 
	else: 
	?> 
		<tr>
			<td colspan="6" align="center">No customers found.</td>
		</tr>
	<?php endif; ?>
	</tbody>
	</table>

</body>
</html>