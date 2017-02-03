<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Shopit - <?=$title;?></title>
<link href="http://fonts.googleapis.com/css?family=Droid+Sans+Mono" rel="stylesheet" type="text/css" />
<link href="<?=template_directory('assets/styles/shopit.css?'.date('Ymd'));?>" rel="stylesheet" type="text/css" media="screen" />
<link href="<?=template_directory('assets/styles/shopit_styles.css?'.date('YmdH'));?>" rel="stylesheet" type="text/css" media="screen" />
<link href="<?=template_directory('assets/styles/jquery.autocomplete.css');?>" rel="stylesheet" type="text/css" media="screen" />
<link href="<?=template_directory('assets/styles/ui-lightness/jquery-ui-1.10.3.custom.css');?>" rel="stylesheet" type="text/css" media="screen" />
<link href="<?=template_directory('assets/scripts/fancybox2/jquery.fancybox.css?v=2.1.5');?>" rel="stylesheet" type="text/css" media="screen" />
<link href="<?=template_directory('assets/styles/jquery.qtip.css');?>" rel="stylesheet" type="text/css" media="screen" />
<link href="<?=template_directory('assets/scripts/markitup/skins/simple/style.css');?>" rel="stylesheet" type="text/css" media="screen" />
<link href="<?=template_directory('assets/scripts/markitup/sets/html/style.css');?>"  rel="stylesheet" type="text/css" media="screen" />

<meta name="viewport" content="initial-scale=1.0, width=device-width" />
</head>

<body class="<?=body_class();?>">

<div id="header">
	<div id="shopitlogo"><a href="<?=site_url();?>"><img src="<?=template_directory('assets/images/shopitlogo.png');?>" alt="Shopit" title="Shopit" /></a></div>

	<ul id="shopitmenu">
		<?php
		// Display the permissions based menu
		echo $this->shopit->nav();
		?>
	</ul>

</div>

<?=$form_open;?>

<?php if($this->session->flashdata('notice') != ''): ?>
<div id="notice"><?=$this->session->flashdata('notice');?></div>
<?php endif;?>

<?php if($this->session->flashdata('alert') != ''): ?>
<div id="alert"><?=$this->session->flashdata('alert');?></div>
<?php endif;?>

<?php 
if (is_dashboard()): 
	if ($todaysOrderCount > 0) {
?>
<div id="notice">You have received <?=$todaysOrderCount;?> order(s) today. <a href="<?=site_url('orders');?>" class="button">View today's orders</a></div>
<?php 
	}
endif; ?>

<div id="shopit_content">
	
	
