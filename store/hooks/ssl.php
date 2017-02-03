<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
|--------------------------------------------------------------------------
| SSL
|--------------------------------------------------------------------------
|
| Automatically switches product pages from HTTPS:// to HTTP://
| and vice-versa.
|
*/
class Ssl {
	
	#------------------------------------------------------
	# Redirect to HTTP:// (if ssl config setting is true)
	#------------------------------------------------------
	public function redirect() {
		
		$shopit =& get_instance();
		
		// Set the page types for which SSL apply
		$page_types = array('basket', 'payment', 'checkout', 'store');

		// Get the current url
		$current_url = current_url();
		
		// Ignore urls concerning images/feeds/plugins/search as they'll be required to 
		// work through both http and https protocols
		if ($shopit->uri->segment(1) != 'image' and $shopit->uri->segment(1) != 'feeds' and $shopit->uri->segment(1) != 'plugins' and $shopit->uri->segment(1) != 'search') {
		
			// If SSL is on, and NOT checkout type page and current protocal is https
			if ($shopit->config->item('ssl_on') and !in_array($shopit->uri->segment(1), $page_types) and $shopit->config->item('base_url_protocol') == 'https://') {
				
				// Redirect to http
				$non_ssl_url = str_replace('https://', 'http://', $current_url);
				redirect($non_ssl_url);
				
			// Else if SSL is on, and IS checkout type page and current protocal is http
			} elseif ($shopit->config->item('ssl_on') and in_array($shopit->uri->segment(1), $page_types) and $shopit->config->item('base_url_protocol') == 'http://') {
	
				// Redirect to https
				$ssl_url = str_replace('http://', 'https://', $current_url);
				redirect($ssl_url);
			
			// Else if SSL is off and current protocol is https
			} elseif ($shopit->config->item('ssl_on') === false and $shopit->config->item('base_url_protocol') == 'https://') {
				
				// Redirect to http
				$non_ssl_url = str_replace('https://', 'http://', $current_url);
				redirect($non_ssl_url);
			
			}
		
		}
		
	}
	
}