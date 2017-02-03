<?php 
#------------------------------------------------------
# Module: Category Icons
#------------------------------------------------------
if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Categoryicons {

	#------------------------------------------------------
	# Get category image
	# - creates icon sized by dimension
	#------------------------------------------------------
	function get_image($filename,$alt='') {
		
		if (!empty($filename)) {
			return '<img src="'. base_url().'docs/'.$filename . '" alt="'.$alt.'" title="'.$alt.'" />';
		} else {
			return "";
		}
		
	}

}
?>