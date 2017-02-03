<?php
/************************************************
* Is dashboard
************************************************/
function is_dashboard() {
	$CI =& get_instance();
	if ($CI->uri->segment(1) == 'dashboard') {
		return true;
	}
}

/************************************************
* Get file time
************************************************/
function get_filetime($filename, $date_format="j F Y@H:i:s") {
	$path = $_SERVER['DOCUMENT_ROOT'] . '/';
	if (file_exists($path . $filename)) {
		return date($date_format, filemtime($path . $filename));
	} else {
		return '';
	}
}

/************************************************
* Table sorting
************************************************/
function table_sort($page,$sort,$default=false,$sort_default='asc',$page_uri_segment=3) {
	$CI =& get_instance();

	$segment = $CI->uri->segment($page_uri_segment + 1);

	//Create the current url for the hyperlink, making sure page = 0
	if ($CI->input->post('filter') == 'true') {
		$url = site_url($page . '/0/' . http_build_query($_POST));
	} elseif ($segment != ''){
		$clean_segment = preg_replace('@&sort=([a-z_]+)&sort_type=([a-z_]+)@','',$segment);
		$url = site_url($page . '/0'). '/' . $clean_segment;
	} else {
		$url = site_url($page . '/0') . '/';
	}

	//Check if there is a "sort=" in the current url
	preg_match('@(sort)=([a-z_]+)@', $segment, $sort_match);

	$sort_label = $sort_match[1];
	$sort_value = $sort_match[2];

	if (!empty($sort_match)) {
		if ($sort_value == $sort){
			$this_sort = true;
			$button = 'on';
		} else {
			$this_sort = false;
			$button = 'off';
		}
	} else {
		$this_sort = false;
		$button = 'off';
	}

	//Check if there is a "sort_type=(asc|desc)" in the current url
	preg_match('@(sort_type)=([a-z_]+)@', $segment, $sort_type_match);

	$sort_type_label = $sort_type_match[1];
	$sort_type_value = $sort_type_match[2];

	if (!empty($sort_type_match) && $sort_value == $sort) {
		switch($sort_type_value) {
			case 'asc':
				$sort_url = "&sort=$sort&sort_type=desc";
				break;
			case 'desc':
				$sort_url = "&sort=$sort&sort_type=asc";
				break;
			default:
				$sort_url = "&sort=$sort&sort_type=$sort_default";
				break;
		}
	} else {
		if ($default == true && empty($sort_label)) {
			$sort_default = 'desc';
			$button = 'on';
		} else {
			$button = 'off';
		}
		$sort_type_value = 'asc';
		$sort_url = "&sort=$sort&sort_type=$sort_default";
	}

	if ($this_sort == true) {
		return '<a href="' . $url . $sort_url . '" class="column-sort"><img src="' . template_directory() . 'assets/images/icon-sort-'.$sort_type_value.'-'.$button.'.png" alt="Sort" /></a>';
	} else {
		return '<a href="' . $url . $sort_url . '" class="column-sort"><img src="' . template_directory() . 'assets/images/icon-sort-'.$sort_type_value.'-'.$button.'.png" alt="Sort" /></a>';	
	}

}

/************************************************
* Identify today's date
* - used on orders screen
* - returns true/false
************************************************/
function is_today($order_date, $print=false) {
	$todays_date = date('Y-m-d');
	$today = strtotime($todays_date);
	
	$order_date_unchanged = strtotime($order_date);
	$order_date = explode(' ', $order_date);
	$order = strtotime($order_date[0]);
	
	if ($order == $today) {
		if ($print == false) {
			return true;
		} else {
			return 'Today, ' . date('H:i', $order_date_unchanged);
		}
	} else {
		return false;
	}
}

/************************************************
* Tooltips
************************************************/
function tooltip($string) {
	$CI =& get_instance();
	if ($CI->config->item('tooltips') == 'true') {
		return 'title="'.$string.'"';
	} else {
		return false;
	}
}

/************************************************
* Tooltips on/off preference
************************************************/
function tooltips_pref() {
	$CI =& get_instance();
	if ($CI->config->item('tooltips') == 'true') {
		return true;
	} else {
		return false;
	}
}

/************************************************
* Codebox on/off preference
************************************************/
function codebox_pref() {
	$CI =& get_instance();
	if ($CI->config->item('codebox') == 'true') {
		return true;
	} else {
		return false;
	}
}

function codebox() {
	$CI =& get_instance();
	if ($CI->config->item('codebox') == 'true') {
		return "codebox";
	} else {
		return "";
	}
}

/************************************************
* Template directory
************************************************/
function template_directory($path=null) {
	return base_url() . $path;
}

/************************************************
* Admin URL
************************************************/
function admin_url() {
	return base_url().'index.php';
}

function site_root($path=null) {
	$CI =& get_instance();
	return $CI->config->item('site_root') . $path;
}

/************************************************
* Converts string to slugs
************************************************/
function slug($str)
{
	$str = strtolower(trim($str));	
	$str = preg_replace('/[^a-z0-9-]/', '-', $str);
	$str = preg_replace('/-+/', "-", $str);
	$str = rtrim($str ,"-");
	return $str;
}

/************************************************
* dropdown: is selected
************************************************/
function is_selected($set_value,$to_compare) 
{
	if ($set_value == $to_compare):
		return ' selected="selected"';
	endif;
}

/************************************************
* checkbox: is selected
* - second function checks to see if value is
* - within an array
************************************************/
function is_checked($set_value,$to_compare) 
{
	if ($set_value == $to_compare):
		return ' checked="checked"';
	endif;
}

function is_checked_array($set_value,$array)
{
	if (is_array($array)):
		if (in_array($set_value,$array)):
			return ' checked="checked"';
		else:
			return '';
		endif;
	endif;
}

/************************************************
* Display item status
************************************************/
function status($field,$yes=0) {
	if ($field == $yes):
		return '<span class="status-indicator light light-green valign" title="Active"></span>';
	else:
		return '<span class="status-indicator light light-red valign" title="Inactive"></span>';
	endif;
}

/************************************************
* Display order status badge
************************************************/
function order_status($field) {

	$shopit =& get_instance();

	$shopit->db->select('label, color');
	$shopit->db->where('value', $field);
	$shopit->db->limit(1);
	$query = $shopit->db->get('order_statuses');
	
	if ($query->num_rows() > 0) {
		$status = $query->row();
		$color = $status->color;
		$label = $status->label;
	} else {
		$color = '#d7d7d7';
		$label = $field;
	}
		
	if ($field != '') {
		return '<span class="badge" style="background-color:'.$color.';">'.$label.'</span>';
	} else {
		return '';
	}

}

/************************************************
* checkbox: is disabled
************************************************/
function disabled($set_value,$to_compare) 
{
	if ($set_value == $to_compare):
		return ' disabled="disabled"';
	endif;
}

#------------------------------------------------------
# Format meta description by removing line breaks, html
# and chopping to 25 words
#------------------------------------------------------
function format_meta($content,$type="description") {
	
	$content = strip_tags($content);
	$content = htmlspecialchars($content);
	$content = str_replace("\n",' ',$content);
	
	return $content;

}

#------------------------------------------------------
# Dashboard notification
#------------------------------------------------------
function system_notification() {
	
	$_notification = file_get_contents('http://www.dubbedcreative.com/system-alert');
	$_notification = strip_tags($_notification,'<div><a><ul><li><ol><p><em><strong><h1><h2><h3>');
	
	return $_notification;

}

#------------------------------------------------------
# Displays date in a nice format
# @param $date (string) - As saved in database
# @param $format (string) - 'datetime', 'time' or null
#------------------------------------------------------
function nice_date($date, $format='datetime') {

	$new_date = new DateTime($date);
		
	if ($format == 'datetime') {
		$date = $new_date->format('j M Y, H:i');
	} elseif($format == 'time') {
		$date = $new_date->format('H:i A');
	} else {
		$date = $new_date->format('j M Y');
	}
	
	return $date;

}

#------------------------------------------------------
# Twitter/Faceboog style "X mins ago" time
#------------------------------------------------------
function realtime_ago($time) {

   $periods = array("sec", "min", "hour", "day", "week", "month", "year", "decade");
   $lengths = array("60","60","24","7","4.35","12","10");

   $now = time();

   $difference = $now - $time;
   $tense = "ago";

   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
       $difference /= $lengths[$j];
   }

   $difference = round($difference);

   if($difference != 1) {
       $periods[$j].= "s";
   }

   return "$difference $periods[$j] $tense";

}

#------------------------------------------------------
# Money format
#------------------------------------------------------
function money($value, $decimal=2) {

	$CI =& get_instance();
	$value = number_format($value, $decimal, '.', ',');
	return $CI->config->item('currency') . $value;

}

#------------------------------------------------------
# Check library exists
#------------------------------------------------------
function library_exists($filename, $type="store") {

	$library_item = $_SERVER['DOCUMENT_ROOT'] . "/$type/libraries/" . ucfirst($filename) . '.php';
	
	if (file_exists($library_item)) {
		return true;
	}
	else {
		return false;
	}

}

#------------------------------------------------------
# Get single item information
#------------------------------------------------------
function get_item_name($product_id) {

	$CI =& get_instance();
	
	$CI->db->select('product_name');
	$CI->db->where('product_id',$product_id);
	$query = $CI->db->get('inventory');
	
	if ($query->num_rows() > 0) {
	
		foreach ($query->result() as $item) {
		
			return($item->product_name);
		
		}
	
	}

}

#------------------------------------------------------
# Get today's order count
#------------------------------------------------------
function todays_orders() {

	$CI =& get_instance();

	$CI->db->select('order_id');
	$CI->db->where('date(order_date)', 'CURDATE()', FALSE);
	$CI->db->where('order_status_id', 2);
	$CI->db->from('orders');
	
	$count = $CI->db->count_all_results();
	
	if ($count > 0):
		return '<span class="shopitmenu-bubble" title="You have ' . $count . ' new orders today">' . $count . '</span>';
	endif;
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
# Get the product slug
#------------------------------------------------------
function get_product_slug($product_id) {
	
	$CI =& get_instance();
	
	//First get the product category	
	$CI->db->select('cat_id, product_slug');
	$CI->db->where('product_id',$product_id);
	$products = $CI->db->get('inventory');
	
	if ($products->num_rows() > 0) {
	
		foreach ($products->result() as $product):
		
			//Get this item's category details
			$CI->db->select('cat_slug,cat_father_id');
			$CI->db->where('cat_id',$product->cat_id);
			$cats = $CI->db->get('category');
			
			foreach ($cats->result() as $cat):
				
				//If is a parent...
				if ($cat->cat_father_id == 0):
					$category_slug = $cat->cat_slug . '/-/-';
				else:
					//else get the parent category
					$CI->db->select('cat_slug,cat_father_id');
					$CI->db->where('cat_id',$cat->cat_father_id);
					$parents = $CI->db->get('category');
					
					foreach ($parents->result() as $parent):
						
						if ($parent->cat_father_id == 0):
							$category_slug = $parent->cat_slug . '/' . $cat->cat_slug . '/-';
						else:
							//else get the ancestor
							$CI->db->select('cat_slug,cat_father_id');
							$CI->db->where('cat_id',$parent->cat_father_id);
							$ancestor = $CI->db->get('category');
							
							foreach ($ancestor->result() as $ancestor):
								$category_slug = $ancestor->cat_slug . '/' . $parent->cat_slug . '/' . $cat->cat_slug;
							endforeach;
							
						endif;
						
					endforeach;
					
				endif;
			
			endforeach;
			
			return $category_slug . '/' . $product->product_slug;
		
		endforeach;
	
	}
	
}

#------------------------------------------------------
# String manipulation
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

function truncate($string, $length=255) {
	$string = trim($string);
	$string = substr($string, 0, $length);
	return $string;
}

#------------------------------------------------------
# Clean text for CSV file
# - removes newline characters
# - strips all tags
# - decode html
#------------------------------------------------------
function csv_cleanse($field) {
	$string = strip_tags($field);
	$string = str_replace("\n", ' ', $string);
	$string = html_entity_decode($string);
	$string = str_replace('"', '&quot;', $string);
	$string = preg_replace('@&#([0-9]){5};@', '', $string);
	return $string;	
}

#------------------------------------------------------
# Return default shipping country
#------------------------------------------------------
function default_country() {
	$CI =& get_instance();
	$CI->db->select('country_name');
	$CI->db->where('is_home',1);
	$query = $CI->db->get('countries');
	
	if ($query->num_rows() > 0) {
		return $query->row()->country_name;
	} else {
		return false;
	}
}

#------------------------------------------------------
# Detect browser
#------------------------------------------------------
function body_class() {
	
	$agent = $_SERVER['HTTP_USER_AGENT'];
	
	//Check platform Mac/PC
	if (strpos($agent,"Macintosh") || strpos($agent,"Mac")):
		$platform = "mac";
	elseif (strpos($agent,"Windows")):
		$platform = "windows";
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
# Base rate
# - Add base rate to price (is not applied to
#   sale prices)
#------------------------------------------------------
function base_rate($price, $rounding=true) {

	$CI =& get_instance();
	
	$base_rate = $CI->config->item('base_rate');
	
	if (substr_count($base_rate,'%') > 0) {				// Check if value contains the percentage
		$base_rate = str_replace('%','',$base_rate);	// If yes (>0) then remove the sign
		$base_rate = 1 + ($base_rate / 100);			// convert it to a decimal value
		$base_rate = $price * $base_rate;				// produces the decimal value
	}
	else {
		$base_rate = $price + ($base_rate);
	}

	if ($rounding) {
		//Convert the value to an array so we can count how 
		//many digits are after the decimal point
		$split = explode('.', $base_rate);
		$decimals = strlen($split[1]);
		
		//If the digits are more than 2, do some rounding
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
# Delete cache
# - this is used to delete db cache files used for
#   the category pages of the store.
#------------------------------------------------------
function delete_cache($uri=null, $delete_contents=TRUE) {
	
	$cache_path = $_SERVER['DOCUMENT_ROOT'].'/cache'; //WITHOUT trailing slash
	
	//Check if uri is only segment 1 and if so add "/index" to it
	$uri_check = (substr_count($uri, '/'));
	if ($uri != null && $uri_check == 0) {
		$uri = $uri . '/index';
	}
	$uri_path = str_replace('/', '+', $uri);
	$uri_path = $uri_path . '/'; //Add the trailing slash
	
	delete_files("$cache_path/$uri_path", $delete_contents);
	
}

#------------------------------------------------------
# Get Monday to Sunday dates
# - returns monday's date and sunday's date based
# 	on given date
#------------------------------------------------------
function get_mondayandsunday($date) {
	
	$shopit =& get_instance();
	$shopit->db->select("DATE(DATE_ADD('$date -7', interval 0-weekday('$date') day)) as monday, DATE(DATE_ADD('$date -7', interval 6-weekday('$date') day)) as sunday", false);
	$query = $shopit->db->get();
	return $query->row();

}

#------------------------------------------------------
# Get the last week's Sunday
#------------------------------------------------------
function get_last_sunday($date=null) {
	
	// If $date is null, then use today
	$date = ($date == null) ? strtotime(date('Y-m-d')) : strtotime($date);
	
	// If today is Sunday, get the Sunday from a week a go 
	// else get the last Sunday that passed.
	if (date('l', $date) == 'Sunday') {
		$day = strtotime('sunday 1 week ago', $date);
	} else {
		$day = strtotime('sunday last week', $date);
	}
	
	// Return the Sundays date
	return date('Y-m-d', $day);
	
}

/************************************************
* Get the VAT rate
* - returns vat rate on its own as decimal
* - this is used to multiply to a price
************************************************/
function the_vat_rate() {
	$shopit =& get_instance();
	return 1 + $shopit->config->item('vat_rate');
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
# Redirect URL preservation
# - Capture the current url so we can return to this 
# 	page after an add/edit product page is accessed
#------------------------------------------------------
function redirect_create() {
	$current_url = base64_encode(current_url());
	return "?R=$current_url";
}

function redirect_get() {
	$shopit =& get_instance();
	$redirect = array();
	
	// Preserve the original url query string - shouldn't need to call this var anywhere
	$redirect_url_encoded = $shopit->input->get('R');
	
	// Recreate the full query string to preserve the url where required
	$redirect_url_query_string = "?R=$redirect_url_encoded";
	
	// Decode the query string so we can use it as a link back
	$redirect_url = base64_decode($redirect_url_encoded);
	
	// Create the variables to use in the controller
	$redirect = (object) array(
		'query_string' => $redirect_url_query_string,
		'link' => $redirect_url
	);
	
	return $redirect;
}

function redirect_create_manual($segments) {
	$current_url = base64_encode(site_url($segments));
	return "?R=$current_url";
}

#------------------------------------------------------
# Convert value to percent
#------------------------------------------------------
function percentify($value1=1, $value2=1, $symbol=true) {

	$symbol = ($symbol) ? '%' : '';
	
	if ($value2 > 0) {
		$value = ($value1 / $value2) * 100;
		return ceil($value) . $symbol;
	} else {
		return "0$symbol";
	}
	
}

#------------------------------------------------------
# Percentage difference between two numbers
# Ref: http://www.calculatorsoup.com/calculators/algebra/percent-change-calculator.php
# @param $value1 is present value
# @param $value2 is a past value
#------------------------------------------------------
function percentify_diff($value1=1, $value2=1, $symbol=true) {

	$prepend_symbol = '';
	$symbol = ($symbol) ? '%' : '';
	
	if ($value2 > 0) {
		
		// Do the math
		$value = (($value1 - $value2) / $value2) * 100;
		
		// Prepend the plus sign if it's a positive value
		if ($value > 0) {
			$prepend_symbol = '+';
		}
		
		return $prepend_symbol . ceil($value) . $symbol;
	} else {
		return "-$symbol";
	}

}

#------------------------------------------------------
# Site icon
# @param $label (string) - Name of the site as in db
#------------------------------------------------------
function site_icon($label) {
	$icon = '';
	
	if ($label != 'website') {
		$path_to_icon = sprintf('%s/admin/assets/images/site-%s.png', $_SERVER['DOCUMENT_ROOT'], strtolower($label));
		if (file_exists($path_to_icon)) {
			$icon =sprintf('<img src="%s/assets/images/site-%s.png" alt="%s" title="%s" />', base_url(), strtolower($label), $label, $label);
		}
	}
	return $icon; 
}

#------------------------------------------------------
# Convert XML to PHP array
# @param $xml (string) - Raw unformatted XML data
# @return array
#------------------------------------------------------
function xmltoarray($xml) {
    
    // Load XML string
    $de_xml = simplexml_load_string($xml);
    
    // Convert to json array
    $json = json_encode($de_xml);
    
    // And decode it back to a simple array
    $array = json_decode($json, true);
    
    // Return the array
    return $array;
    
}

#------------------------------------------------------
# Split customer name into first and last name
# @param $customer_name (string) - Customer's full name
# @return Array of firstname and lastname
#------------------------------------------------------
function split_name($customer_name) {
	
	$name 	   = explode(" ", $customer_name);
	$lastname  = array_pop($name);
	$firstname = implode(" ", $name);
	
	$data = (object) array(
		'firstname' => $firstname,
		'lastname'  => $lastname
	);
	
	return $data;
	
}

#------------------------------------------------------
# Unserialize variant name
# @param $data (string)  The variant name un/serialised
# @returns full formatted variant name (string)
#------------------------------------------------------
function unserialize_variant($data) {
	
	// Unserialize the passed variable 
	$variant_product_name = unserialize($data);
	
	// If it's an array, do the necessary to make it a readable string,
	// else just return whatever was passed through the function - unchanged
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

#------------------------------------------------------
# Create inline editable link
# @param $value (string)
# @param $db_where (array)	 Our where clauses
# @param $db_table (string)  Database table name
# @param $db_column (string) Database column name
# @param $edit_type (string) 'text' or 'select'
# @param $data (array)		 Array of select options (with keys)
# @returns HTML
#------------------------------------------------------
function inline_edit($value, $db_where, $db_table, $db_column, $edit_type='text', $data=array()) {
	
	// Serialize and base64 encode the where comparisons
	$db_where = serialize($db_where);
	$db_where = base64_encode($db_where);
	
	switch($edit_type){
		case 'text':
		default:
			$data_options = '';
			$class = 'edit-inline';
			break;
		case 'select':
			$data_options = sprintf('data-values="%s"', htmlspecialchars(json_encode($data), ENT_QUOTES, 'UTF-8'));
			$class = 'edit-inline-select';
			break;
	}
	
	$html =  sprintf('<span id="%s" class="%s" data-table="%s" data-where="%s" %s>%s</span>', $db_column, $class, $db_table, $db_where, $data_options, $value);
	
	return $html;
	
}