<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />
<title>Install Shopit Developer</title>

<link href="/install/views/install.css" rel="stylesheet" type="text/css" media="screen" title="default"/>
</head>

<body>
<wrapper>
	<div>
		<div id="logo">
			<img src="/admin/assets/images/shopitlogo.png">
		</div>
		<div id="steps">
			<span id="step" class="<?=$active1?>">
			1
			</span>
			<span id="name">Database Settings</span>

			<span id="step" class="<?=$active2?>">
			2
			</span>
			<span id="name">Admin Settings</span>

			<span id="step" class="<?=$active3?>">
			3
			</span>
			<span id="name">Site Settings</span>
		</div>
		<div class="table">
			<h1><?php echo $step_name ?></h1>
			<?php $this->load->view($content);?>
		</div>
	</div>
</wrapper>
</body>
</head>