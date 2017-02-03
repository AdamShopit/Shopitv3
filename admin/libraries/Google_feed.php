<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
#------------------------------------------------------
# Google Feeds Library
#------------------------------------------------------
class Google_feed {
	
	#------------------------------------------------------
	# Initialise our variables
	#------------------------------------------------------
#	function initialize($params = array()) {
#
#		if (count($params) == 0) return;
#		
#		foreach ($params as $key => $val) {
#			$shopit->$key = $val;			
#		}
#				
#	}

	#------------------------------------------------------
	# Formats string into XML safe characters
	#------------------------------------------------------
	function safe_xml($string) {

		$shopit =& get_instance();

		$shopit->load->helper('xml');
		
		$string = htmlspecialchars_decode($string);
		$string = strip_tags($string);
		$string = str_replace(array("\r\n", "\r", "\n", "\t"),' ', $string);
		$string = trim($string);
		$string = str_replace('&nbsp;',' ', $string);
		$string = str_replace('&hellip;',':', $string);
		$string = str_replace(' & ', ' and ', $string);
		$string = str_replace('&#39;',"'", $string);
		$string = str_replace('&#45;',"-", $string);
		$string = str_replace('&#42;',"*", $string);
		$string = str_replace('&#33;',"!", $string);
		$string = str_replace('&#37;',"%", $string);
		$string = str_replace('&#43;',"+", $string);
		$string = str_replace('&#58;',":", $string);
		$string = str_replace('&#59;',";", $string);
		$string = str_replace('&middot;', ',', $string);
		$string = str_replace('&rsquo;', "'", $string);
		$string = str_replace('&rdquo;', '"', $string);
		$string = str_replace('&ldquo;', '"', $string);
		$string = str_replace('&quot;', '"', $string);
		$string = str_replace('&plusmn;', '+/-', $string);
		$string = str_replace('&trade;', '', $string);
		$string = str_replace('&ndash;', '-', $string);
		$string = str_replace('&mdash;', '-', $string);
		$string = str_replace('&lsquo;', "'", $string);
		$string = xml_convert($string);
		
		return $string;
	
	}
	
	#------------------------------------------------------
	# Get delivery cost for this item
	#------------------------------------------------------
	function getdeliverycost($product_price, $product_weight, $country="United Kingdom") {

		$shopit =& get_instance();
		
		$shippingrule = $shopit->shipping_model->getCountryShipping($country);

		if ($shippingrule > 0):
		
		foreach ($shippingrule as $shippingcost) {

			//Check criteria for total value
			if ($shippingcost->criteria == 'total'):
			
				switch($shippingcost->operation) {
				
					case 'less than':
						if ($product_price <= $shippingcost->value):
							$shipping[] = $shippingcost->shipping;
						endif;
						break;

					case 'more than':
						if ($product_price >= $shippingcost->value):						
							$shipping[] = $shippingcost->shipping;
						endif;
						break;

					case 'equal to':
						if ($product_price == $shippingcost->value):
							$shipping[] = $shippingcost->shipping;
						endif;
						break;

					case 'between':
						if ($product_price >= $shippingcost->value && $product_price <= $shippingcost->value2):
							$shipping[] = $shippingcost->shipping;
						endif;
						break;

				}

			//Check criteria for total weight value
			elseif ($shippingcost->criteria == 'weight'):
			
				switch($shippingcost->operation) {
				
					case 'less than':
						if ($product_weight <= $shippingcost->value):
							$shipping[] = $shippingcost->shipping;
						endif;
						break;
						
					case 'more than':
						if ($product_weight >= $shippingcost->value):
							$shipping[] = $shippingcost->shipping;
						endif;
						break;
						
					case 'equal to':
						if ($product_weight == $shippingcost->value):
							$shipping[] = $shippingcost->shipping;
						endif;
						break;

					case 'between':
						if ($product_weight >= $shippingcost->value && $product_weight <= $shippingcost->value2):
							$shipping[] = $shippingcost->shipping;
						endif;
						break;

				}
			
			endif;

		}
		
		else:
			$shipping[] = $shopit->config->item('default_shipping_cost');
		endif;
	
		//Add the currency and money format to the price using
		//the first key in the array
		if ($shipping[0] > 0) {
			$delivery_cost_with_vat = $shipping[0] * (1 + $shopit->config->item('vat_rate'));
			$delivery_cost = number_format($delivery_cost_with_vat,2);
		} else {
			$delivery_cost = '0.00';
		}
				
		return 'GBP'.$delivery_cost;

	}

	#------------------------------------------------------
	# Update Shop Plugin Google Shopping feed
	# - This is used to update base.xml files on
	#   different servers (which use the shop plugin)
	# - Call function in generate() above if required
	#------------------------------------------------------
	function update_plugin_feed($url) {
		$curl = curl_init(); 
		curl_setopt ($curl, CURLOPT_URL, $url); 
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
		$result = curl_exec ($curl); 
		curl_close ($curl); 
	} 
	
}