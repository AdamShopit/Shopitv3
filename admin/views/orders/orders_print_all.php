<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Order Note</title>
<link href="<?=template_directory();?>assets/styles/printorder.css" rel="stylesheet" type="text/css" media="screen,print" title="default"/>
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery.min.js?v=v1.10.2');?>"></script>
<script type="text/javascript" src="<?=template_directory('assets/scripts/jquery-ui.min.js?v=1.10.3');?>"></script>
</head>

<body>
<div id="window">
	<a href="<?=$redirect_link;?>" class="button">Back</a>
	<a href="#" onclick="window.print();return false;" class="button">Print</a>
</div>

<?php
if ($orders > 0) {
foreach($orders as $order){
	
	// If this is a mobile order, set the channel to 'website'
	$site = ($order->site == 'mobile') ? 'website' : $order->site;
	
	// Do a lookup on the order's channel for display purposes. If location does
	// not exist, use the site as recorded against the order.
	$order_channel = (array_key_exists($site, $channel)) ? $channel[$site] : $order->site;
	
	// Get the available templates of $template_type for this channel/site
	$templates = $this->orders_model->getTemplatesByType($template_type, $site);
	
	// Get the order data
	$tags['order'] = $order;
	$tags['items'] = $this->orders_model->getOrderInventory($order->order_id);

	// Now loop through each template of this $template_type
	// and display it
	if (count($templates) > 0) {
		foreach ($templates as $template) {
			
			// Create the parser tags
			$html['body'] = $template->content;

			// Loop through the array of tags and create the parser tags
			if (!empty($tags)) {
				foreach ($tags as $tag=>$value) {
					if (is_array($value)) {
						foreach($value as $key=>$pair) {
							$content[$tag][$key] = get_object_vars($pair);
						}
					} else {
						$content = get_object_vars($value);
					}
				}
			}
		
			// Merge the $html and $content arrays into one
			$data = array_merge($html, $content, $snippets);
		?>
	
		<div class="printnote">
			<?php echo $this->parser->parse('orders/orders_print_template', $data, true);?>
		</div>
	
		<?php 
		}
	} else {
		echo sprintf('<div class="printnote"><p>A template has not been set up for this channel (%s) yet. <a href="javascript:void(0);" onclick="window.opener.location.href=\'%s\';self.close();">Set one up now?</a></p></div>', $order_channel, site_url('orders/templates'));
	}
}
}
?>

</body>
</html>