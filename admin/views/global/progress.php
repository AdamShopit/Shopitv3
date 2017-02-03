<!DOCTYPE HTML>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Please wait...</title>
<link href="<?=template_directory('assets/styles/shopit.css?'.date('Ymd'));?>" rel="stylesheet" type="text/css" media="screen" title="default"/>
<link href="<?=template_directory('assets/styles/shopit_styles.css?'.date('Ymd'));?>" rel="stylesheet" type="text/css" media="screen" title="default"/>
<style type="text/css" media="screen,all">
body {
	background-color: #2e2b28;
}
</style>
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery.min.js?v=v1.10.2');?>"></script>
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery.ajaxQueue.min.js');?>"></script>

<script type="text/javascript">
$(document).ready(function() {
	var percentage = 0;
    <?=$script;?>
});
</script>
</head>

<body>
	<div id="progressbar">
		
		<?=$message;?>
	
		<div id="progressbar-container">
			<div id="progressbar-thebar" style="width:1%;">
				<label></label>
			</div>
		</div>
	</div>
</body>
</html>

<?php
/*------------------------------------------------------
# This is example loop which creates the javascript 
# within a controller.
#------------------------------------------------------	
$current = 0;

foreach ($things as $thing) {
	
	$current++;
	$url = 'path-to-processing-script';

	// Add the note to the jquery ajax queue
	$script .= "
	jQuery.ajaxQueue({
	    url: '$url',
	    dataType: 'json',
		beforeSend: function(){
		},
		complete: function(){
			percentage = ($current/$total)*100;
			percentage = Math.ceil(percentage) + '%';
			$('#progressbar-thebar').css('width',percentage);
			$('#progressbar-thebar label').html(percentage);
			if (percentage == '100%') {
				setTimeout(function(){location.href='$redirect'} , 2000);
			}\n
		}
	});";
	
}

// Load our progress bar
$data['script']  = $script;
$data['message'] = "Please wait whilst we do something...";
$this->load->view('global/progress', $data);
*/
?>
