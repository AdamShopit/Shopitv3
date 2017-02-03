<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>{page_title} || {store:name}</title>
{meta_description}
{meta_keywords}
{meta_custom}
<link href="/site/styles/layout.css?<?=date('Ymd');?>" rel="stylesheet" type="text/css" media="screen" title="default"/>
<?=core_files($ssl);?>
</head>

<body class="<?=body_class();?>">

<h1><?=$this->config->item('store_name');?></h1>

<p><a href="<?=base_url();?>admin">Admin</a> | <a href="<?=site_url();?>">Store Home</a></p>

<nav>
	<ul>
		{shopit:myaccount:links}
	</ul>
</nav>

<?php if($this->session->flashdata('basket_notice') != ''): ?>
<div class="message"><?=$this->session->flashdata('basket_notice');?></div>
<?php endif;?>
