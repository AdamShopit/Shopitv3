<!doctype html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Shopit! - Error</title>

<link href="<?=template_directory('assets/styles/login.css');?>" rel="stylesheet" type="text/css" media="screen" title="default" />
<meta name="HandheldFriendly" content="true" />
<meta name="viewport" content="initial-scale=1.0, width=device-width" />
</head>

<body class="<?=body_class();?>">

	<div id="notice">
		<!-- <h3><?=$heading;?></h3> -->
		<p><img src="<?=template_directory('assets/images/icon-errorpage.png');?>" alt="" /></p>
		<?=$message;?>
	</div>

</body>
</html>