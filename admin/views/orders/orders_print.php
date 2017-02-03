<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Your Order: {order_ref}</title>
<link href="<?=template_directory();?>assets/styles/printorder.css" rel="stylesheet" type="text/css" media="screen,print" title="default"/>
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery.min.js?v=v1.10.2');?>"></script>
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery-ui.min.js?v=1.10.3');?>"></script>
</head>

<body>
	
	<div id="window">
		<div id="window_options">&nbsp;</div>
		<a href="<?=$redirect_link;?>" class="button">Back</a>
		<a href="#" onclick="window.print();return false;" class="button">Print</a>
	</div>
	
	<div id="printnote">
		{body}
	</div>

</body>
</html>