<?php 
#------------------------------------------------------
// Mobile Library
// @Author: Ketan Mistry
// @Web: project-octo.com
// @Created: 29 May 2014
// Configuration settings are in store/config/config.php
#------------------------------------------------------
if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Mobile {

	// Redirect the user to the mobile website if they
	// are using a mobile device (not tablet).
	function redirect() {
		
		$shopit =& get_instance();
	    
	    // Load the mobile detection library
	    $shopit->load->library('mobile_detect');
	    $shopit->load->library('user_agent');
		
		// Get the config setting for the mobile website's address
		$mobile_www = $shopit->config->item('mobile_www');
		
		// If it's 
		//	- a mobile 
		//  - AND NOT tablet 
		//	- AND config is set 
		// 	- AND the referer is NOT from the same domain 
		// then redirect
		if ($shopit->mobile_detect->isMobile() && !$shopit->mobile_detect->isTablet() && $mobile_www != NULL && !$this->same_domain_referer()) {
			
			// Get the current url we're on
			$current_url = current_url();
			
			// Create the redirect
			$redirect_url = str_replace($shopit->config->item('base_url'), $mobile_www, $current_url);
			
			// And do it...
			redirect($redirect_url);
		}
		
	}

	#------------------------------------------------------
	//! Display Popup for desktop/mobile option
	//  - We only want to show this once and only if
	//	  they are on a tablet. 
	//  @param $template = path to view file
	#------------------------------------------------------
	function popup($template=null){
		
		$shopit =& get_instance();

		// Get the config setting for the mobile website's address
		$mobile_www = $shopit->config->item('mobile_www');
		
		// Some vars
		$html = '';
		$data = array(
			'shopit_mobile_url' 	=> $shopit->config->item('mobile_www'),
			'shopit_mobile_jquery'	=> $this->javascript(),
		);
		
		// Load libraries
		$shopit->load->library('mobile_detect');
		
		// Display html template if it's set AND cookie is not set AND user is a tablet AND referer is not from the same domain
		if ($template != null && get_cookie('desktopView') != 1 && $shopit->mobile_detect->isTablet() && $mobile_www != NULL && !$this->same_domain_referer()) {

			$html = $shopit->parser->parse($template, $data, true);
			return $html;
		
		} else {
			return false;
		}
		
	}

	#------------------------------------------------------
	//! Javascript to handle cookies and stuff
	#------------------------------------------------------
	private function javascript() {

		$nl = "\n";
		
		$jscript = '';
		
		$jscript  = '<script type="text/javascript">'.$nl;
		$jscript .= '$(document).ready(function() {'.$nl;
		
			// Our jQuery functions go here
			
			// On click of a link within the popup, direct the user 
			// to the correct location
			$jscript .= '$("#mobile-popup a").click(function(event){'.$nl;
			$jscript .= '	var link = $(this).attr("href");'.$nl;
			$jscript .= '	if (link == "#") {'.$nl;
			$jscript .= '		event.preventDefault();'.$nl;
			$jscript .= '		$("#mobile-popup").fadeOut("fast").remove();'.$nl;
			$jscript .= '		setCookie("desktopView", 1, 1);'.$nl;
			$jscript .= '	}'.$nl;
			$jscript .= '});'.$nl;
	
			// Create cookie
			$jscript .= 'function setCookie(name, value, days) {'.$nl;
			$jscript .= '	if (days) {'.$nl;
			$jscript .= '		var date = new Date();'.$nl;
			$jscript .= '		date.setTime(date.getTime()+(days*24*60*60*1000));'.$nl;
			$jscript .= '		var expires = "; expires="+date.toGMTString();'.$nl;
			$jscript .= '	}'.$nl;
			$jscript .= '	else var expires = "";'.$nl;
			$jscript .= '	document.cookie = name+"="+value+expires+"; path=/";'.$nl;
			$jscript .= '}'.$nl;

		$jscript .= '});'.$nl;
		$jscript .= '</script>'.$nl;	
		
		return $jscript;
		
	}

	// Check the referrer url to identify whether we are 
	// coming from the same domain or not.
	// The mobile version should be on a 'm.' subdomain and
	// the desktop website should reside at 'www.'
	private function same_domain_referer() {

		$shopit =& get_instance();
		
		$same_domain = FALSE;
		
		// Get the referer
		$referer_url = $_SERVER['HTTP_REFERER'];
		
		// If the referer is not empty then do a few more checks
		if ($referer_url != "") {
			
			// Pick out the domain from the referer
			preg_match('@^(http|https):\/\/(www|m)\.(.*?)\.(com|co.uk|net|org|dev|labs)\/@', $referer_url, $matches);
			
			$base_url  = $matches[0];
			$protocol  = $matches[1];
			$subdomain = $matches[2];
			$domain	   = $matches[3];
			$extension = $matches[4];
			
			// Get the current page's site url
			$current_url = site_url();
			
			// Now check if the current url and the referring url's domain reside 
			// on the same domain by checking for its position.
			if (strpos($current_url, $domain) !== FALSE) {
				
				$same_domain = TRUE;
				
			}
		
		}
		
		return $same_domain;
		
	}

}