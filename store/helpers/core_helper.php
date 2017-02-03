<?php
/************************************************
* Display shipping based on rules
* - used in basket/checkout
************************************************/
function shipping_values($shippingcost=null) {
	global $data,$shipping_encrypted,$shipping_rulename;
	
	$CI =& get_instance();
	
	if ($shippingcost != null){
		$shipping_encrypted[] = base64_encode($shippingcost->shipping);
		$data['shipping'] .= '<option value="' . base64_encode($shippingcost->shipping) . '">' . $shippingcost->rule_name . ' (' . $CI->config->item('currency') . $shippingcost->shipping . ')</option>';
		$data['shipping_value'][] = $shippingcost->shipping;
		$shipping_rulename[] = $shippingcost->rule_name;
	} else {
		//show default shipping settings when no rules exist
		$shipping_encrypted[] = base64_encode($CI->config->item('default_shipping_cost'));
		$data['shipping'] .= '<option value="' . base64_encode($CI->config->item('default_shipping_cost')) . '">' . $CI->config->item('default_shipping_name') . ' (' . $CI->config->item('currency') . $CI->config->item('default_shipping_cost') . ')</option>';
		$data['shipping_value'][] = $CI->config->item('default_shipping_cost');
		$shipping_rulename[] = $CI->config->item('default_shipping_name');
	}
}

/************************************************
* Base rate
************************************************/
function base_rate($price, $rounddown=true) {

	$CI =& get_instance();
	
	$base_rate = ($CI->config->item('base_rate') != "") ? $CI->config->item('base_rate') : 0;
	
	if (substr_count($base_rate,'%') > 0) {				// Check if value contains the percentage
		$base_rate = str_replace('%','',$base_rate);	// If yes (>0) then remove the sign
		$base_rate = 1 + ($base_rate / 100);			// convert it to a decimal value
		$base_rate = $price * $base_rate;				// produces the decimal value
	}
	else {
		$base_rate = $price + ($base_rate);
	}

	if ($rounddown) {
		//Convert the value to an array so we can count how 
		//many digits are after the decimal point
		$split = explode('.', $base_rate);
		$decimals = strlen($split[1]);
		
		//If the digits are more than 2, do some rounding down
		if ($decimals > 2) {
			// If decimals begin with 98n then FORCE the price to display as .99
			if ($split[1] >= 980 && $CI->config->item('force_99p') == 'true') {
				$base_rate = preg_replace('@\.(9(8|9)[0-9])@', '.99', $base_rate);
			} else {
				// Don't do any rounding...
			}
		}
	}
	return number_format($base_rate,2,'.','');
}

/************************************************
* Get base rate
* - returns the rate on its own in decimal format
* - can be used to add/multiply to price (set true)
************************************************/
function the_base_rate($show_operator=true) {

	$CI =& get_instance();
	
	$base_rate = ($CI->config->item('base_rate') != "") ? $CI->config->item('base_rate') : 0;
	
	if (substr_count($base_rate,'%') > 0) {				// Check if value contains the percentage
		$base_rate = str_replace('%','',$base_rate);	// If yes (>0) then remove the sign
		$base_rate = 1 + ($base_rate / 100);			// convert it to a decimal value
		$operator  = '* ';
	}
	else {
		$base_rate = $base_rate;
		$operator  = '+ ';
	}

	if ($show_operator == true) {
		return $operator . number_format($base_rate,2,'.','');
	} else {
		return number_format($base_rate,2,'.','');
	}
}


/************************************************
* Get the VAT rate
* - returns vat rate on its own as decimal
* - this is used to multiply to a price
************************************************/
function the_vat_rate() {
	$CI =& get_instance();
	return 1 + $CI->config->item('vat_rate');
}

/************************************************
* Switches urls to SSL (https)
* - DEPRECATED AS OF 3.1.11. 
* - Replaced with site_url() function below.
************************************************/
function basket_url($path=null,$show_indexphp=true) {
	return site_url($path);
}

/************************************************
* Site URL
* Create a local URL based on your basepath. Segments can be passed via the
* first parameter either as a string or an array.
* - Checks SSL settings/status
* - Overwrites the native function
************************************************/
function site_url($uri = '') {

	$shopit =& get_instance();
	
	// Set the page types for which SSL apply
	$page_types = array('basket', 'payment', 'checkout', 'store');

	// Get the first segment of the passed uri
	$segments = explode('/', $uri);
	$segment1 = $segments[0];

	// Ignore urls concerning images/feeds/plugins/search as they'll be required to 
	// work through both http and https protocols
	if ($segment1 != 'image' and $segment1 != 'feeds' and $segment1 != 'plugins' and $segment1 != 'search') {

		// Check if SSL is turned on for this site
		if ($shopit->config->item('ssl_on') and in_array($segment1, $page_types)) {
			
			// If $uri contains 'basket', 'checkout' or 'payment
			// convert it top https.
			$site_url = str_replace('http://', 'https://', $shopit->config->site_url($uri));
		
		} else {
			
			// Else revert it to http
			$site_url = str_replace('https://', 'http://', $shopit->config->site_url($uri));
	
		}
	
	} else {
		$site_url = $shopit->config->site_url($uri);
	}

	return $site_url;

}

#------------------------------------------------------
# Is page type
# - where array items are compared to url segment 1
# - returns true or false
# @param $array (array) - e.g. array('tag','brand','search')
# @param $segment (int) - Default 1
#------------------------------------------------------
function is_page_type($array=array(), $segment=1) {
	$shopit =& get_instance();

	if (in_array($shopit->uri->segment($segment))) {
		return TRUE;
	} else {
		return FALSE;
	}
}

/************************************************
* URL suffix
************************************************/
function url_suffix() {
	$CI =& get_instance();
	if ($CI->config->item('url_suffix') == ''){
		return '';
	} else {
		return $CI->config->item('url_suffix');
	}
}

/************************************************
* Core files to be included in the head
* - jquery core file
* - jquery plugins
* - cart/basket css files
************************************************/
function core_files() 
{
	
	global $data;
	
	$shopit =& get_instance();

	if ($ssl == false) {
		$path = base_url();
	} else {
		$path = site_url('');
	}

?>
<link href="/site/styles/cart.css?<?=date('Ymd');?>" rel="stylesheet" type="text/css" media="screen" title="default"/>
<link href="<?=$path;?>core/styles/cookie-monster.css" rel="stylesheet" type="text/css" media="screen" title="default"/>
<link href="<?=$path;?>core/scripts/fancybox2/jquery.fancybox.css?v=2.1.5" rel="stylesheet" type="text/css" media="screen" title="default"/>
<script type="text/javascript" src="<?=$path;?>core/scripts/jquery.min.js"></script>
<script type="text/javascript" src="<?=$path;?>core/scripts/fancybox2/jquery.fancybox.pack.js?v=2.1.5"></script>
<script type="text/javascript" src="<?=$path;?>core/scripts/jquery.validate.js"></script>
<script type="text/javascript" src="<?=$path;?>core/scripts/store.js"></script>
<?php
// Google GA code
if ($shopit->config->item('google_ua_code') != "") { 
	echo $shopit->config->item('google_ua_code');
}

// Shopit Admin Bar
shopit_admin_bar($path);

}

/************************************************
* Display available payment methods in basket
************************************************/
function displayPaymentOptions() 
{

	// Set the CI instance
	$shopit =& get_instance();
	
	// Set some defaults
	$payment_option = "";
	$i = 0;

	// Set the list of available payment gateways
	// and whether they are turned on or not
	$payment_options = array(
		// Primary gateways first
		'payment_sagepay'  	  => $shopit->config->item('payment_sagepay'),
		'payment_worldpay' 	  => $shopit->config->item('payment_worldpay'),
		'payment_cardsave' 	  => $shopit->config->item('payment_cardsave'),
		'payment_barclaycard' => $shopit->config->item('payment_barclaycard'),
		// We'll put PayPal last to indicate 
		// this as a second payment option
		'payment_paypal'      => $shopit->config->item('payment_paypal'),
	);

	// Loop through the gateways
	foreach ($payment_options as $gateway=>$setting) {
		
		// If gateway is on (true)
		if ($setting == 'true') {
		
			// Increment the counter
			$i++;
			
			// Check the first option by default
			$payment_checked = ($i == 1) ? 'checked="checked"' : '';
		
			// Set the option value that we send to the checkout script
			switch($gateway) {
				
				case 'payment_sagepay':
					$payment_value = 'SagePay';
					break;

				case 'payment_cardsave':
					$payment_value = 'CardSave';
					break;

				case 'payment_worldpay':
					$payment_value = 'WorldPay';
					break;

				case 'payment_barclaycard':
					$payment_value = 'Barclaycard';
					break;

				case 'payment_paypal':
					$payment_value = 'PayPal';
					break;
				
			}

			// Set the label: Credit/debit card or PayPal
			$payment_label = ($gateway != 'payment_paypal') ? 'Credit/Debit Card' : 'PayPal';
			
			// Create a list of useful css classes
			$css_classes = array(
				'payment-option', 
				strtolower("payment-option-$payment_value"),
				'payby-'.slug($payment_label),  //payby-{credit-debit-card|paypal}
			);
			
			$css_class = implode(" ", $css_classes); //Turn the classes into a space-separated string
			
			// Create the html
			$payment_option .= '<label class="'.$css_class.'"><input type="radio" name="gateway" value="'.$payment_value.'" autocomplete="off" '.$payment_checked.' /><span> '.$payment_label."</span></label>\n";
			
		}
		
	}

	return $payment_option;
}

/************************************************
* Converts string to slugs
************************************************/
function slug($str)
{
  $str = strtolower(trim($str));	
  $str = preg_replace('/[^a-z0-9-]/', '-', $str);
  $str = preg_replace('/-+/', "-", $str);
  return $str;
}

/************************************************
* Format content [for TINYMCE's incapabilities]
* - Replaces [br] with <br />
************************************************/
function format_content($content)
{
	$content = str_replace('[br]','<br />' . "\n", $content);
	return $content;
}

#------------------------------------------------------
# Format meta description by removing line breaks, html
# and chopping to 25 words
#------------------------------------------------------
function format_meta($content, $type="description") {
	
	// Remove any html and line breaks
	$content = strip_tags($content);
	$content = htmlspecialchars($content);
	$content = str_replace("\n",' ',$content);
	
	// Remove any snippets within the content
	$content = preg_replace('@\{(.+?)\}@', '', $content);
	
	// Remove any spaces at the beginning/end of content
	$content = trim($content);
	
	// Limit the description to 25 words
	if ($type == "description" ):
		$content = word_limiter($content,25,'');
	endif;
	
	return $content;

}

#------------------------------------------------------
# Resize image
# - $image is the filename, without the path
#------------------------------------------------------
function resize_image($image,$width=400,$height=400) {

	if (!empty($image)) {
	return site_url("image/resize/$image/$width/$width");
	}

}

#------------------------------------------------------
# Get category slug
#------------------------------------------------------
function get_category_slug($cat_id) {
	$CI =& get_instance();
	
	$CI->db->select('concat_ws("/",c3.cat_slug,c2.cat_slug,c1.cat_slug) as cat_url', false);
	$CI->db->from('category c1');
	$CI->db->join('category c2', 'c1.cat_father_id = c2.cat_id', 'left');
	$CI->db->join('category c3', 'c2.cat_father_id = c3.cat_id', 'left');
	$CI->db->where('c1.cat_id', $cat_id);
	
	$query = $CI->db->get();
	
	if ($query->num_rows() > 0) {
		$cat = $query->row();
		return $cat->cat_url;
	}
}

#------------------------------------------------------
# Get product slug
#------------------------------------------------------
function get_product_slug($product_id) {
	$CI =& get_instance();
	
	//Get cat_id, product_slug for this product
	$CI->db->select('cat_id, product_slug');
	$CI->db->where('product_id', $product_id);
	$item = $CI->db->get('inventory');		
	$cat_id = $item->row()->cat_id;
	$product_slug = $item->row()->product_slug;
	
	//Get the slug
	$CI->db->select('concat_ws("/",c3.cat_slug,c2.cat_slug,c1.cat_slug) as cat_url', false);
	$CI->db->from('category c1');
	$CI->db->join('category c2', 'c1.cat_father_id = c2.cat_id', 'left');
	$CI->db->join('category c3', 'c2.cat_father_id = c3.cat_id', 'left');
	$CI->db->where('c1.cat_id', $cat_id);
	
	$query = $CI->db->get();
	
	if ($query->num_rows() > 0) {
		$cat_url = $query->row()->cat_url;
		
		//Count occurences of '/' in the cat_url
		$url_check = substr_count($cat_url, '/');
		
		if ($url_check == 0) {
			//is in grandparent category only
			return $cat_url . '/-/-/' . $product_slug . '/' . $product_id;
		} elseif ($url_check == 1) {
			//is in parent
			return $cat_url . '/-/' . $product_slug . '/' . $product_id;
		} else {
			//is in child
			return $cat_url . '/' . $product_slug . '/' . $product_id;
		}
	}
}

#------------------------------------------------------
# Check library exists
#------------------------------------------------------
function library_exists($filename) {

	$library_item = $_SERVER['DOCUMENT_ROOT'] . '/store/libraries/' . ucfirst($filename) . '.php';
	
	if (file_exists($library_item)) {
		return true;
	}
	else {
		return false;
	}

}

#------------------------------------------------------
# Check if dropdown/radio option is selected
#------------------------------------------------------
function is_selected($set_value,$compare_value) {

	if ($set_value == $compare_value):
		return ' selected="selected"';
	else:
		return false;
	endif;

}

function is_checked($set_value,$to_compare) 
{
	if ($set_value == $to_compare):
		return ' checked="checked"';
	else:
		return false;
	endif;
}

#------------------------------------------------------
# Detect browser
#------------------------------------------------------
function body_class() {
	
	$agent = $_SERVER['HTTP_USER_AGENT'];
	
	//Check platform Mac/PC/mobile
	if (strpos($agent, "Macintosh")):
		$platform = "mac";
	elseif (strpos($agent, "Windows")):
		$platform = "windows";
	elseif (strpos($agent, "iPhone")):
		$platform = "ios iphone";
	elseif (strpos($agent, "iPad")):
		$platform = "ios ipad";
	elseif (strpos($agent, "Android")):
		$platform = "android";
	else:
		$platform = "";
	endif;

	//Check browser
	if (strpos($agent,"Safari")):
		$browser = "safari";
	elseif (strpos($agent,"MSIE")):
		if (strpos($agent,"MSIE 6")):
			$version = " ie6";
		elseif (strpos($agent,"MSIE 7")):
			$version = " ie7";
		elseif (strpos($agent,"MSIE 8")):
			$version = " ie8";
		elseif (strpos($agent,"MSIE 9")):
			$version = " ie9";
		else:
			$version = "";
		endif;
		$browser = "explorer" . $version;
	elseif (strpos($agent,"Firefox")):
		$browser = "firefox";
	elseif (strpos($agent,"Opera")):
		$browser = "opera";
	endif;
	
	if ($browser != null){
		return $browser . " " . $platform;
	}
	else {
		return $platform;
	}
	
}

#------------------------------------------------------
# Check if page is basket, checkout or payment
#------------------------------------------------------
function is_not_basket() {
	
	$CI =& get_instance();
	
	switch ($CI->uri->segment(1)) {
	
		case 'basket';
		case 'checkout';
		case 'payment':
			return false;
			break;
		
		default:
			return true;
			break;
	}
		
}	

#------------------------------------------------------
# Redirection 301, 404, etc.
#------------------------------------------------------
function manage_redirection() {
	$CI =& get_instance();
	$base_url = ($CI->config->item('index_page') == 'index.php') ? site_url('/') : site_url(); 
	$current_url = str_replace($base_url, '', current_url());
	
	$CI->db->where('old_url', $current_url);
	$CI->db->or_where('old_url', "$current_url/");
	$CI->db->limit(1);
	$query = $CI->db->get('redirection');
	
	//Match found
	if ($query->num_rows() > 0) {
		$redirection = $query->row();
		redirect($redirection->new_url, 'location', $redirection->status_code);
	}

}

#------------------------------------------------------
# If Admin/Client user is logged in to admin
# - uses cookie called 'shopit_mgnt'
#------------------------------------------------------
function is_admin() {
	$CI =& get_instance();

	$cookie = get_cookie('shopit_mgnt');
	
	if ($cookie != FALSE) {	
		$cookie = $CI->encrypt->decode($cookie);
		$crumbs = unserialize($cookie);
		return (object)$crumbs;
	} else {
		return FALSE; //Cookie doesn't exist
	}
}

#------------------------------------------------------
# Displays date in a nice format
#------------------------------------------------------
function nice_date($date, $format='datetime') {

	$new_date = new DateTime($date);
		
	if ($format == 'datetime'):	
		$date = $new_date->format('j F Y, H:i');
	else:
		$date = $new_date->format('j F Y');
	endif;
	
	return $date;

}

#------------------------------------------------------
# Money format v2
# - The $value passed into this should have the 
#   base_rate applied to it if applicable
# - Rounds prices to .99p where applicable
#------------------------------------------------------
function money($value, $decimals=true, $rounding=true, $output_price_with_vat=true, $include_currency=true, $force_rounddown=false) {

	$shopit =& get_instance();

	//Apply VAT if required
	if ($output_price_with_vat) {
		$value = $value * the_vat_rate();
	}
	
	if ($rounding) {
		//Convert the value to an array so we can count how 
		//many digits are after the decimal point
		$split = explode('.', $value);
		$decimal_count = strlen($split[1]);
		
		//If the digits are more than 2, do some rounding
		if ($decimal_count > 2) {
		
			// If decimals begin with 98n then FORCE the price to display as .99
			if ($split[1] >= 980 && $shopit->config->item('force_99p') == 'true' && $force_rounddown == false) {
				$value = preg_replace('@\.(9(8|9)[0-9])@', '.99', $value);
			} else {
				// Force down decimal places by removing the last digit (if $force_roundown = true)
				if ($force_rounddown && $shopit->config->item('force_99p') == 'true') {
					$value = preg_replace('@\.([0-9])([0-9])([0-9]+)$@', '.$1$2', $value);
				}
			}
			
		}
	}
	
	$value = ($decimals == true) ? number_format($value,2,'.','') : number_format($value,0);
	
	//Output the price
	if ($include_currency) {
		$currency = $shopit->config->item('currency');
	} else {
		$currency = "";
	}
	return $currency . $value;

}

#------------------------------------------------------
# Clean address fields
# - Capitalises all words
#------------------------------------------------------
function capitalise($string) {
	$string = trim($string);
	$string = strtolower($string);
	$string = ucwords($string);
	return $string;	
}

function uppercase($string) {
	$string = trim($string);
	$string = strtoupper($string);
	return $string;
}

function capfirst($string) {
	$string = trim($string);
	$string = ucfirst($string);
	return $string;
}

#------------------------------------------------------
# Create search filter action link
# - used to filter results by price ranges
# - link url returned is in the format: http://shopit.dev/uncategorized/15/s_sort=price%20asc&s_pricemin=15&s_pricemax=50&s_perpage=15&filter=true
# Parameters:
#	- label is the link text
#	- min_price is the min price of the range (int)
#	- max_price is the max price of the range (int)
#	- s_sort is the sort format, $s_sort is passed into this 
#	- s_perpage is the items per page, $s_perpage is passed into this 
#	- filter_action is the link page, $filter_action is passed into this
#	- s_pricemin is the min price as passed through the page url 
#	- s_pricemax is the max price as passed through the page url 
#------------------------------------------------------
function filter_action($label, $min_price, $max_price, $s_sort, $s_perpage, $filter_action, $s_pricemin='', $s_pricemax='') {	
	//if this page is the selected price range, apply class	
	$class = ($s_pricemin == $min_price) ? ' class="this_filter_price" ' : '';
	
	//return the generated link
	return '<a href="'. "$filter_action/0/s_sort=$s_sort&s_pricemin=$min_price&s_pricemax=$max_price&s_perpage=$s_perpage&filter=true" . '"'. "$class>" . $label . '</a>';
}

#------------------------------------------------------
# EU Cookie consent - May 2012 (http://www.ico.gov.uk)
# - This is just plain daft so we'll just use implied
#   consent as it's easier:
# 	- Implied consent is a valid form of consent and can be used 
# 	  in the context of compliance with the revised rules on cookies.
# 	- If you are relying on implied consent you need to be satisfied
# 	  that your users understand that their actions will result in 
# 	  cookies being set. Without this understanding you do not 
# 	  have their informed consent.
# 	- You should not rely on the fact that users might have read 
# 	  a privacy policy that is perhaps hard to find or difficult 
# 	  to understand.
# 	- In some circumstances, for example where you are collecting 
# 	  sensitive personal data such as health information, you might
# 	  feel that explicit consent is more appropriate.
#------------------------------------------------------
function cookie_monster($message=null, $link_to_policy=null, $classes=null, $styles=null, $hide_message_on_visit=2) {

	$CI =& get_instance();

	//Load libraries
	$CI->load->library('user_agent');

	//Check if this agent is a browser. If true
	//then continue processing...
	if ($CI->agent->browser()) {

		//Check if consent cookie already exists
		$cookie_monster = get_cookie('cookiemonster');
		
		if ($cookie_monster != FALSE) {
			//Cookie exists so we need to proceed any further 
			//and not display the cookie message
			$display_message = false;
		} else {
		//Else no cookie exists, then create one that lasts for 10 years 
		//only if they choose to continue browsing
			$display_message = true;
	
			//We need to check how many pages they have browsed
			$visit = $CI->session->userdata('cookiemonster');
			
			if ($visit == false) {
				$visit_counter = $CI->session->set_userdata('cookiemonster', 1);
			} else {
				$visit_counter = $CI->session->set_userdata('cookiemonster', $visit+1);
			}
			
			//Get the updated count
			$visit = $CI->session->userdata('cookiemonster');
	
			//If this is their X visit, then drop 
			//a cookie and hide the message, and unset
			//the session as it's no longer needed
			if ($visit == $hide_message_on_visit) {
				$display_message = false;
				$expires = ((24 * 365) * 10) * 3600;
				set_cookie('cookiemonster', '1', $expires);
				$CI->session->unset_userdata('cookiemonster');
			}
		}
	
		//Specify default policy link if none provided
		$link_to_policy = ($link_to_policy ==  null) ? site_url('page/privacy-policy') : site_url($link_to_policy);
		
		//Specify default classes/styles
		$classes = ($classes == null) ? "" : " class=\"$classes\"";
		$styles  = ($styles == null) ? "" : " style=\"$styles\"";
		
		//Display default message if none specified
		if ($message == null) {
			$message = "<p>Cookies ensure we give you the best experience of our website. By using our website, you agree to our use of cookies. <a href=\"$link_to_policy\">Learn more</a></p>";
		}
	
		if ($display_message != false) {
?>
<div id="cookie-monster"<?=$classes;?><?=$styles;?>>
	<div id="cookie-monster-info">
		<?=$message;?>
	</div>
</div>
<?php
		}
	
	}

}

#------------------------------------------------------
# MAILCHIMP & CAMPAIGN MONITOR
# - Add email to list
# - remove email from list
# - Set $account_id as 0 to bypass account checks
#------------------------------------------------------	
function cmSubscribe($account_id=0, $pref_newsletter=0, $firstname, $lastname, $email) {

	$shopit =& get_instance();

	//Only add the subscriber if they haven't got a customer account
	//and they have ticked the sign-up checkbox in the basket during checkout
	if ($account_id < 1 && $pref_newsletter >= 1) {

		//Campaign Monitor
		if ($shopit->config->item('campaignmonitor_listid') != null) {
			$shopit->load->library('CampaignMonitor');
	
			$shopit->campaignmonitor->CampaignMonitor(
					$shopit->config->item('campaignmonitor_apikey'), 
					$shopit->config->item('campaignmonitor_clientid'), 
					null, 
					$shopit->config->item('campaignmonitor_listid')
			);
	
	        $result = $shopit->campaignmonitor->subscriberAdd($email, $firstname . ' ' . $lastname); // 2nd paramter could be used for name.
        }  
		
		//Mailchimp
		if ($shopit->config->item('mailchimp_apikey') != "" && $shopit->config->item('mailchimp_listid') != "") {
			$config = array(
					 	'apikey' => $shopit->config->item('mailchimp_apikey'),
					  );
			$shopit->load->library('Mailchimp', $config);
			
			$result = $shopit->mailchimp->listSubscribe($shopit->config->item('mailchimp_listid'), $email, array('FNAME'=>$firstname, 'LNAME'=>$lastname), 'html', false);
		}
		
	}

}

function cmUnsubscribe($email) {

	$shopit =& get_instance();
	
	if ($shopit->config->item('mailchimp_apikey') != "" && $shopit->config->item('mailchimp_listid') != "") {
		$config = array(
				 	'apikey' => $shopit->config->item('mailchimp_apikey'),
				  );
		$shopit->load->library('Mailchimp', $config);
		
		$result = $shopit->mailchimp->listUnsubscribe( $shopit->config->item('mailchimp_listid'), $email );
		
		return $result;
	}
}

#------------------------------------------------------
# Get the first paragraph
# - used for product description preview
#------------------------------------------------------
function get_first_paragraph($string) {
	
	//Remove all html tags
	$string = strip_tags($string);
	
	//Get the first paragraph (upto the first newline)
	$string = strtok($string, "\n");
	
	//Return the formatted string
	return "$string";
	
}

#------------------------------------------------------
# Auto paragraph content
# - pass content to $pee, $br = 1 means line break 
#   functionality is enabled
#------------------------------------------------------
//Define the chars we need to look out for in this array,
//so we can use them in the two functions below
global $html_chars;
$html_chars = array(
	'£' => '&pound;',
);

function autop($pee, $br = 1) {
	
	global $html_chars;

	if ( trim($pee) === '' )
		return '';
	$pee = $pee . "\n"; // just to make things a little easier, pad the end
	$pee = preg_replace('|<br />\s*<br />|', "\n\n", $pee);
	// Space things out a little
	$allblocks = '(?:table|thead|tfoot|caption|col|colgroup|tbody|tr|td|th|div|dl|dd|dt|ul|ol|li|pre|select|option|form|map|area|blockquote|address|math|style|input|p|h[1-6]|hr|fieldset|legend|section|article|aside|hgroup|header|footer|nav|figure|figcaption|details|menu|summary)';
	$pee = preg_replace('!(<' . $allblocks . '[^>]*>)!', "\n$1", $pee);
	$pee = preg_replace('!(</' . $allblocks . '>)!', "$1\n\n", $pee);
	$pee = str_replace(array("\r\n", "\r"), "\n", $pee); // cross-platform newlines
	if ( strpos($pee, '<object') !== false ) {
		$pee = preg_replace('|\s*<param([^>]*)>\s*|', "<param$1>", $pee); // no pee inside object/embed
		$pee = preg_replace('|\s*</embed>\s*|', '</embed>', $pee);
	}
	$pee = preg_replace("/\n\n+/", "\n\n", $pee); // take care of duplicates
	// make paragraphs, including one at the end
	$pees = preg_split('/\n\s*\n/', $pee, -1, PREG_SPLIT_NO_EMPTY);
	$pee = '';
	foreach ( $pees as $tinkle )
		$pee .= '<p>' . trim($tinkle, "\n") . "</p>\n";
	$pee = preg_replace('|<p>\s*</p>|', '', $pee); // under certain strange conditions it could create a P of entirely whitespace
	$pee = preg_replace('!<p>([^<]+)</(div|address|form)>!', "<p>$1</p></$2>", $pee);
	$pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee); // don't pee all over a tag
	$pee = preg_replace("|<p>(<li.+?)</p>|", "$1", $pee); // problem with nested lists
	$pee = preg_replace('|<p><blockquote([^>]*)>|i', "<blockquote$1><p>", $pee);
	$pee = str_replace('</blockquote></p>', '</p></blockquote>', $pee);
	$pee = preg_replace('!<p>\s*(</?' . $allblocks . '[^>]*>)!', "$1", $pee);
	$pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*</p>!', "$1", $pee);
	if ($br) {
		$pee = preg_replace_callback('/<(script|style).*?<\/\\1>/s', '_autop_newline_preservation_helper', $pee);
		$pee = preg_replace('|(?<!<br />)\s*\n|', "<br />\n", $pee); // optionally make line breaks
		$pee = str_replace('<WPPreserveNewline />', "\n", $pee);
	}
	$pee = preg_replace('!(</?' . $allblocks . '[^>]*>)\s*<br />!', "$1", $pee);
	$pee = preg_replace('!<br />(\s*</?(?:p|li|div|dl|dd|dt|th|pre|td|ul|ol)[^>]*>)!', '$1', $pee);
	if (strpos($pee, '<pre') !== false)
		$pee = preg_replace_callback('!(<pre[^>]*>)(.*?)</pre>!is', 'clean_pre', $pee );
	$pee = preg_replace( "|\n</p>$|", '</p>', $pee );

	// replace those characters that cause problems with the database
	foreach ($html_chars as $problem_char => $html_entity) {
		$pee = str_replace($problem_char, $html_entity, $pee);
	}

	return html_entity_decode($pee);
}

function _autop_newline_preservation_helper( $matches ) {
	return str_replace("\n", "<WPPreserveNewline />", $matches[0]);
}

function hidep($content) {

	global $html_chars;
	
	$content = str_replace('<p>','',$content);
	$content = str_replace('</p>', "\n", $content);

	// add these extra characters to the array, just for display/cleansing
	$html_chars[' '] = '&nbsp;';
	$html_chars['-'] = '&ndash';
	$html_chars["'"][0] = '&rsquo;';
	$html_chars["'"][1] = '&lsquo;';
	
	// convert those characters that cause problems with the database
	// back into nice characters to display inside the editor
	foreach ($html_chars as $problem_char => $html_entity) {
		$content = str_replace($html_entity, $problem_char, $content);
	}
	
	return $content;

}

#------------------------------------------------------
# Unserialize variant name
# @param $data (string)  Serialized/unserialised string
# @returns full formatted variant name (string)
#------------------------------------------------------
function unserialize_variant($data) {
	
	// Unserialize the passed variable 
	$variant_product_name = unserialize($data);
	
	// If it's an array, do the necessary to make it a readable string,
	// else just return whatever was passed, unchanged, through the $data
	if (is_array($variant_product_name)) {
		$var_attr = array();
		foreach ($variant_product_name as $var_name => $var_value) {
			$var_attr[] = trim($var_value['value']);
		}
		
		// Separate each attribute with a space
		$variant_name = implode(' ', $var_attr);	
	} else {
		$variant_name = $data;
	}
	
	return $variant_name;
	
}