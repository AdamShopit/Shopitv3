<?php
class Ajax extends CI_Controller {
	
	function Ajax() {
		parent::__construct();		
	}
	
	function index() {
		redirect();
	}
	
	#------------------------------------------------------
	//! Ajax for the Variant Attribute Selector (Dropdown)
	# - Listen to the incoming posts and do the necessary processing
	#------------------------------------------------------
	function variant_attr(){
		
		header('Content-Type: text/html; charset=utf-8');

		$html = "";	
		
		// Let's start by decoding the posted value
		$values = rawurldecode(($_POST['values']));
		
		// And json decode the result to give an array we can work with
		$variant_attr = json_decode($values, true);
		
		// Loop through the array and create the dropdown html
		if ($variant_attr) {
			foreach ($variant_attr as $label1 => $value1) {
				
				if (is_array($value1)) {
					
					$html .= sprintf('<li data-title="%s"><span>Select %s</span><i class="icon"></i><ul>', $label1, strtolower($label1));
					
					foreach ($value1 as $label2 => $value2) {

						if (is_array($value2['data'])) {
							$option_value = rawurlencode((json_encode($value2['data'])));
							$data_value = sprintf('data-variant-image="%s"', $value2['image']);
							$image_src = (isset($value2['image'])) ? sprintf('<img src="%s" alt="" /> ', $value2['image']) : '';
						} else {
							$option_value = $value2['data'];
							$option_image = (isset($value2['variant_product_image'])) ? sprintf('data-variant-image="%s"', $value2['image']) : '';
							$image_src = (isset($value2['image'])) ? sprintf('<img src="%s" alt="" /> ', $value2['image']) : '';
							$option_image_default = (isset($value2['variant_product_image_default'])) ? sprintf('data-variant-image-default="%s"', $value2['variant_product_image_default']) : '';
							$option_image_fullsize = (isset($value2['variant_product_image_fullsize'])) ? sprintf('data-variant-image-fullsize="%s"', $value2['variant_product_image_fullsize']) : '';
							$option_image_gallery_reference = (isset($value2['variant_product_image'])) ? sprintf('data-gallery="shopit-gallery-thumb-variant-%s"', $option_value) : '';
							$data_value = sprintf('data-variant="shopit-variant-%s" %s %s %s %s', slug($option_value), $option_image_gallery_reference, $option_image, $option_image_default, $option_image_fullsize);
						}
						
						$option_label = ($label2 == '') ? 'Not Applicable' : $label2;
						
						$html .= sprintf('<li><label><input type="radio" class="shopit-variant-attr" name="variant-attr-%s" value="%s" %s />%s%s</label></li>', slug($label1), $option_value, $data_value, $image_src, $option_label);
			
					}
					
					$html .= '</ul></li>';
					
				}
				
			}
		}
		
		// Output the html
		echo $html;
		
	}

}