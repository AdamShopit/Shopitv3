<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<title>Processing Payment...</title>
<link href="/site/styles/cart.css" rel="stylesheet" type="text/css" media="screen" title="default"/>
<style type="text/css" media="all">
* {
	margin: 0;
	padding: 0;
}

body {
	background-color: #f7f7f7;
	font-size: 14px;
	font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
	color: darkslategrey;
}

#page {
	background-color: white;
	border: 1px solid #d7d7d7;
	border-radius: 2px;
	-moz-border-radius: 2px;
	-webkit-border-radius: 2px;
	width: 600px;
	margin: 30px auto;
	text-align: center;
}

#header {
	padding: 15px;
}

#content {
	padding: 0 15px 15px 15px;
}

h1 {
	color: dimgray;
	font-weight: 300;
	font-size: 18px;
	line-height: 160%;
}

h1 strong {
	color: orange;
	font-weight: 400;
}

input[type="submit"] {
	margin-top: 15px;
}
</style>
</head>

<body onLoad="document.frmProcess.submit.click()">
	<div id="page">
		<div id="header">
			<h1>Please wait, your order details are being processed and you will now be redirected to the <strong>{gateway}</strong> website to complete payment.</h1>
		</div>
		<div id="content">
			<p><img src="/core/images/ajax-loader.gif" alt="" /></p>
			<?php $this->load->view($content);?>
		</div>
	</div>
</body>
</html>