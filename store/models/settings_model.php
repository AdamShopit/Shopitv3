<?php

class Settings_model extends CI_Model {
	
	function Settings_model() {
		parent::__construct();
	}

	#------------------------------------------------------
	# Get store config settings from database
	#------------------------------------------------------
	function initConfig() {
		
		//Create config settings from the 'settings' table
		$query = $this->db->get('settings');
		
		if ($query->num_rows() > 0) {

			foreach ($query->result() as $setting) {
			
				$this->config->set_item($setting->setting,$setting->value);
			
			}
			
		}
		
		//Create the 'sales' channel settings
		$this->db->select('id, name, type, use_global_stock');
		$this->db->where('shortname', $this->config->item('site'));
		
		$query = $this->db->get('locations');
		
		if ($query->num_rows() > 0) {
		
			$channel = $query->row();
			
			$this->config->set_item('channel', 'channel_'.$channel->id);
			$this->config->set_item('channel_type', $channel->type);
			$this->config->set_item('channel_name', $channel->name);
			$this->config->set_item('channel_id', $channel->id);
			
			//Sets the global stock flag - if this is the default channel, this should be TRUE as well so
			//it takes stock from the "location_1" field
			if ($channel->use_global_stock == 1 || $channel->type == 'default') {
				$use_global_stock = TRUE;
				$this->config->set_item('stock_location', 'location_1'); //Stock qty
			} else {
				$use_global_stock = FALSE;
				$this->config->set_item('stock_location', 'location_'.$channel->id); //Stock qty
			}
			
			$this->config->set_item('channel_global_stock', $use_global_stock);
			
			// Set the channel price fields
			if ($channel->id == 1) {
				$this->config->set_item('channel_product_price', 'product_price');
				$this->config->set_item('channel_product_saleprice', 'product_saleprice');
			} else {
				$this->config->set_item('channel_product_price', 'channel_'.$channel->id.'_product_price');
				$this->config->set_item('channel_product_saleprice', 'channel_'.$channel->id.'_product_saleprice');
			}

		} else {
		
			// If no channel found, then default to 'website'
			$this->config->set_item('channel', 'channel_1');
			$this->config->set_item('channel_type', 'default');
			$this->config->set_item('channel_name', 'Website');
			$this->config->set_item('channel_id', 1);
			// Setting the following as TRUE will ensure stock is taken from the "location_1" field
			$this->config->set_item('channel_global_stock', TRUE);
			$this->config->set_item('stock_location', 'location_1'); //Stock qty
			// Set the default price fields
			$this->config->set_item('channel_product_price', 'product_price');
			$this->config->set_item('channel_product_saleprice', 'product_saleprice');
			
		}

	}

	#------------------------------------------------------
	# Load snippets
	#------------------------------------------------------
	function snippets() {
	
		global $data;
	
		// Load the snippets as setup in the admin
		$this->db->cache_off();
		$this->db->order_by('early_parsing', 'desc');
		$query = $this->db->get('snippets');
	
		if ($query->num_rows() > 0) {	
			$snippets = $query->result();
			foreach($snippets as $snippet) {
				if (is_admin() !== FALSE) {

					// Check cookies to see if admin user has turned the snippet highlights off
					$highlight_snippets = get_cookie('shopit_mgnt_snippets');
					$highlight_snippets_class = (!empty($highlight_snippets)) ? 'true' : 'false';
					
					// We want widgets to be treated differently by the admin console, 
					// so we need make the 'edit' link provide alternative data
					if ($snippet->widget == 1) {
						// The widget should not output the serialised data,
						// but rather provide an array that we can work with
						// via php in the templates. To work with this array,
						// a function called 'snippet_widget' must be used which
						// also handles the editable boundary.
						$snippet_content = array(
							'data' 	 => unserialize($snippet->content),
							'id' 	 => $snippet->id,
							'title'  => $snippet->title,
							'widget' => $snippet->widget,
						);
					} else {
						$snippet_content  = sprintf('<div class="shopit-console-snippet" data-href="%sadmin/index.php/pages/snippets/%s%s#edit" data-label="edit %s: %s" data-hide="%s" data-id="shopit-console-snippet-%d">', site_url(), $snippet->id, redirect_create(), 'snippet', strtolower($snippet->title), $highlight_snippets_class, $snippet->id);
						$snippet_content .= $snippet->content;
						$snippet_content .= '</div>';
					}
					
				} else {
					
					// Display the content as normal
					if ($snippet->widget == 1) {
						$snippet_content = array(
							'data' 	 => unserialize($snippet->content),
							'id' 	 => $snippet->id,
							'title'  => $snippet->title,
							'widget' => $snippet->widget,
						);
					} else {
						$snippet_content = $snippet->content;
					}
					
				}
				$data['sn_'.$snippet->label] = $snippet_content;
			}
		}

		// Load some store information
		$this->_store();
		$this->_page_type();

		// Load links to my account area if it is installed
		if (library_exists('myaccount')) {
			$data['shopit:myaccount:links'] 	= $this->myaccount->links();
			$data['shopit:myaccount:logged_in'] = $this->myaccount->user_logged_in();
		} else {
			$data['shopit:myaccount:links'] 	= "";
			$data['shopit:myaccount:logged_in'] = FALSE;
		}
		
		return $data;
		
	}
	
	#------------------------------------------------------
	# Load store information as tags
	# - e.g. store name, address, phone, etc
	# - We don't want to output every config item!
	# - We'll load these through the snippets() function
	#   above
	#------------------------------------------------------
	function _store() {
		
		global $data;
		
		//Company information
		$data['store:name'] 	= $data['store_name'] 	 = $this->config->item('store_name');
		$data['store:company'] 	= $data['company_name']  = $this->config->item('company_name');
		$data['store:tel']		= $data['company_tel'] 	 = $this->config->item('company_tel');
		$data['store:fax']		= $data['company_fax'] 	 = $this->config->item('company_fax');
		$data['store:email']	= $data['company_email'] = $this->config->item('company_email');
		$data['store:reg']		= $data['company_reg'] 	 = $this->config->item('company_reg');
		
		//Handy configuration settings
		$data['store:currency'] = $data['currency'] = $this->config->item('currency');
		$data['store:vat_rate'] = $this->config->item('vat_rate');
		$data['site_url']		= site_url();
		$data['current_url']	= current_url();
		$data['base_url']		= base_url();

		// Create the canonical link - typically we'll replace the "m." in 
		// the url with "www." and vice-versa (won't work for development sites).
		$domain = ($_SERVER['HTTP_HOST']);
		$split_domain = explode('.', $domain);
		$subdomain = $split_domain[0];
		$canonical_subdomain = ($split_domain[0] == 'www') ? 'http://m.' : 'http://www.';
		$data['canonical_url_mobile']  = str_replace("http://$subdomain.", $canonical_subdomain, current_url());
		$data['canonical_url'] = current_url(); // Strips out query string from the current url
		
		//Some others that might be used in status notifications
		$data['store_email'] 	= $this->config->item('store_email');
		$data['store:address'] 	= $data['company_address'] = $this->config->item('company_address');
		
		return $data;
		
	}

	#------------------------------------------------------
	# Identify the type of page we're on?
	#------------------------------------------------------
	function _page_type() {
		
		global $data;
		
		switch($this->uri->segment(1)) {
			
			// Homepage
			case "":
				$page_type = 'home';
				break;
			
			// A collection
			case "collections":
				$page_type = 'collection';
				break;

			// A tag page
			case "tag":
				$page_type = 'tag';
				break;
			
			// Search results	
			case "search":
				$page_type = "search";
				break;
			
			// Brand page
			case "brand":
				$page_type = 'brand';
				break;
			
			// A basket related page	
			case "basket";
				$page_type = 'basket';
				break;
				
			case "payment";
			case "checkout";
			case "store":
				$page_type = 'checkout';
				break;
			
			// Categories list
			case "categories":
				$page_type = 'categories';
				break;
				
			case "page":
				$page_type = 'page';
				break;
			
			default:
				if ($this->uri->segment(5) == "") {
					$page_type = 'category';
				} else {
					$page_type = 'product';
				}
				break;
			
		}
		
		$data['page_type'] = $page_type;
		
		return $data;
		
	}	

}