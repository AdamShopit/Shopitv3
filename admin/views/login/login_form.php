<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Shopit - <?=$title;?></title>

<link href="<?=template_directory('assets/styles/login.css');?>" rel="stylesheet" type="text/css" media="screen" title="default" />
<meta name="HandheldFriendly" content="true" />
<meta name="viewport" content="initial-scale=1.0, width=device-width" />

<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery.min.js?v=v1.10.2');?>"></script>
<script type="text/javascript" src="<?=template_directory('assets/scripts/login.js');?>"></script>

</head>

<body class="<?=body_class();?>">

<?php if($this->session->flashdata('notice') != ''): ?>
<div id="notice"><?=$this->session->flashdata('notice');?></div>
<?php endif;?>

<div id="frm-login">

	<img src="<?=template_directory('assets/images/login-shopitlogo.png');?>" alt="" id="login-logo" />

	<form action="<?=site_url('login');?>" method="post">
	
		<div class="table-row">
			<input type="text" name="username" value="" class="textbox" placeholder="Username or email address" autocomplete="off" />
		</div>
		<div class="table-row">
			<input type="password" name="password" value="" class="textbox" placeholder="Password" autocomplete="off" />
		</div>
		<div class="table-row">
			<input type="submit" name="submit" value="login" class="button" />
			<input type="hidden" name="referer" value="<?=$_SERVER['HTTP_REFERER'];?>" autocomplete="off" />
		</div>
		
	</form>

</div>

<p id="shopit">
	<strong>Shopit Version <?=$this->shopit->version();?></strong>. Designed &amp; developed by <a href="http://project-octo.com">Shopit Commerce Ltd</a><br/>
	Copyright &copy; <?=date('Y');?>, Shopit Commerce Ltd. <a href="<?=base_url();?>license.txt" target="_blank">Shopit License</a>
</p>
</body>
</html>