<?php

class Settings_model extends CI_Model {
	
	function Settings_model() {
		parent::__construct();
	}

	#------------------------------------------------------
	# Get store settings
	#------------------------------------------------------
	function getSettings() {
		$query = $this->db->get('settings');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	}

	#------------------------------------------------------
	# Save store setting
	#------------------------------------------------------
	function saveSetting($setting_name,$setting_value) {
		$data = array(
					'setting' => $setting_name,
					'value'	  => $setting_value,
				);
		
		$this->db->insert('settings',$data);
	}

	#------------------------------------------------------
	# Update setting
	#------------------------------------------------------
	function updateSetting($setting_name,$setting_value) {
		$data = array(
					'value' => $setting_value,
				);
		
		$this->db->where('setting',$setting_name);
		$this->db->update('settings',$data);
	}

	#------------------------------------------------------
	# Initiate store config settings
	# - also gets this user's preferences e.g. tooltips
	#------------------------------------------------------
	function initConfig() {
		$query = $this->db->get('settings');
		
		if ($query->num_rows() > 0) {

			foreach ($query->result() as $setting) {
			
				$this->config->set_item($setting->setting,$setting->value);
			
			}
			
		}
		
		$query->free_result();
		
		// Get user's preferences
		$this->db->select('tooltips, codebox');
		$this->db->where('uid',$this->session->userdata('uid'));
		$query = $this->db->get('users');
		
		$pref = $query->row();
		
		$this->config->set_item('tooltips', $pref->tooltips);
		$this->config->set_item('codebox', $pref->codebox);
		
		// Load this user's permissions (if logged in)
		if ($this->session->userdata('uid') != '') {
			foreach ($this->permissions->init_config() as $permission=>$access) {
				$this->config->set_item($permission, $access);
			}
		}

		// Enable profiler
		if ($this->config->item('enable_profiler') == 'true') {
			$this->output->enable_profiler(true);
		}
		
	}

	#------------------------------------------------------
	# Turn tooltips on/off
	#------------------------------------------------------
	function tooltips($state) {
		
		$data = array(
					'tooltips' => $state,
				);
		
		$this->db->where('uid',$this->session->userdata('uid'));
		$this->db->update('users',$data);
		
		$this->initConfig();
		
	}

	#------------------------------------------------------
	# Turn codebox on/off
	#------------------------------------------------------
	function codebox($state) {
		
		$data = array(
					'codebox' => $state,
				);
		
		$this->db->where('uid',$this->session->userdata('uid'));
		$this->db->update('users',$data);
		
		$this->initConfig();
		
	}

	#------------------------------------------------------
	# Create custom field template
	#------------------------------------------------------
	function createCustomField($custom_field_for='inventory') {

		$custom_field_default = ($this->input->post('custom_field_default') != false) ? $this->input->post('custom_field_default') : NULL;
		
		$data = array(
			'custom_field_for'	 => $custom_field_for,
			'custom_field_label' => 'custom_' . slug($this->input->post('custom_field_label')),
			'custom_field_title' => $this->input->post('custom_field_title'),
			'custom_field_type'  => $this->input->post('custom_field_type'),
			'custom_field_default'  => $custom_field_default,
			'template_tag'		 => $this->input->post('template_tag'),
			'variants'			 => $this->input->post('variants'),
		);
		
		$this->db->insert('custom_field_templates', $data);
				
	}

	#------------------------------------------------------
	# Update custom field template
	#------------------------------------------------------
	function updateCustomField($custom_field_id) {
	
		$custom_field_default = ($this->input->post('custom_field_default') != false) ? $this->input->post('custom_field_default') : NULL;

		$data = array(
			'custom_field_title' => $this->input->post('custom_field_title'),
			'custom_field_type'  => $this->input->post('custom_field_type'),
			'custom_field_default'  => $custom_field_default,
			'template_tag'		 => $this->input->post('template_tag'),
			'variants'			 => $this->input->post('variants'),
		);
		
		$this->db->where('custom_field_id', $custom_field_id);
		$this->db->update('custom_field_templates', $data);
	
	}

	#------------------------------------------------------
	# Get custom field templates
	# @param $custom_field_for (string) 	'inventory' or 'order'
	# @param $retrieve (string)				'all', 'variants' or 'parents'
	#------------------------------------------------------
	function getCustomFields($custom_field_for='inventory', $retrieve='all') {
	
		$this->db->where('custom_field_for', $custom_field_for);
		switch($retrieve) {
			case 'variants':
				$this->db->where('variants', '1');
				break;
			case 'parents':
				$this->db->where('variants', '0');
				break;
			default:
				break;
		}
		$this->db->order_by('custom_field_id', 'asc');
		$query = $this->db->get('custom_field_templates');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		} else {
			return array();
		}
		
	}

	#------------------------------------------------------
	# Get a custom field template
	#------------------------------------------------------
	function getCustomField($custom_field_id) {
		
		$this->db->where('custom_field_id', $custom_field_id);
		$query = $this->db->get('custom_field_templates');
		
		if ($query->num_rows() > 0) {
			return $query->row();
		} else {
			return false;
		}
		
	}

	#------------------------------------------------------
	# Record custom field data
	#------------------------------------------------------
	function recordCustomFieldData($id, $custom_field_label, $custom_field_data, $update=true) {
	
		$data = array(
			'custom_field_data'  => $custom_field_data,
		);
		
		if ($update == true) {
			$this->db->where('id', $id);
			$this->db->where('custom_field_label', $custom_field_label);
			$this->db->update('custom_field_values', $data);
		} else {
			$data['id'] = $id;
			$data['custom_field_label'] = $custom_field_label;
			$this->db->insert('custom_field_values', $data);
		}
	
	}

	#------------------------------------------------------
	# Get custom field data
	#------------------------------------------------------
	function getCustomFieldData($id, $custom_field_label) {
	
		$this->db->select('custom_field_id, custom_field_data');
		$this->db->where('id', $id);
		$this->db->where('custom_field_label', $custom_field_label);
		
		$query = $this->db->get('custom_field_values');
		
		if ($query->num_rows() > 0){
			return $query->row();
		} else {
			return false;
		}
	
	}

	#------------------------------------------------------
	# API Keys
	#------------------------------------------------------
	// Get all api keys
	function getAPIs() {
		
		$this->db->order_by('id');
		$query = $this->db->get('api_keys');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
		
	}
	
	// Retrieve a single API key - get the first one
	function getAPIKey() {
	
		$this->db->select('key');
		$this->db->where('status', 1);
		$this->db->order_by('id', 'asc');
		$this->db->limit(1);
		$query = $this->db->get('api_keys');
		
		if ($query->num_rows() > 0) {
			return $query->row()->key;
		} else {
			return false;
		}
		
	}
	
	// Create a new API Key
	function createAPI() {
		
		// Create some strings
		$domain = $this->config->item('site_root');
		$domain = str_replace('http://', '', $domain); // Remove http:// from the domain
		$domain = substr(trim($domain), 0, -1); //Remove the trailing slash from the domain
		
		$timestamp = date('YmdHis', time());
		
		$api_key = sha1("$domain-$timestamp");
		
		// Save key
		$data = array(
			'created' => date('Y-m-d H:i:s', time()),
			'label'	  => NULL,
			'key'	  => $api_key,
			'status'  => 1
		);
		
		$this->db->insert('api_keys', $data);
		
	}
	
	// Change the status of an API Key
	function changeAPIStatus($id, $status=1) {
		
		$this->db->set('status', $status);
		$this->db->where('id', $id);
		$this->db->update('api_keys');
		
	}
	
	// Update the API label
	function updateAPILabel($id, $label) {
		
		$this->db->set('label', $label);
		$this->db->where('id', $id);
		$this->db->update('api_keys');

	}
	
	// Check for a valid API key
	function apiExists($api_key) {
		
		$this->db->where('key', $api_key);
		$this->db->where('status', 1);
		$query = $this->db->get('api_keys');
		
		if ($query->num_rows() > 0) {
			return true;
		} else {
			return false;
		}
		
	}

	#------------------------------------------------------
	# Load snippets contents
	#------------------------------------------------------
	function loadSnippets() {
	
		//Load the snippets as setup in the admin
		$this->db->order_by('early_parsing', 'desc');
		$query = $this->db->get('snippets');
	
		if ($query->num_rows() > 0) {	
			$snippets = $query->result();
			foreach($snippets as $snippet) {
				$data['sn_'.$snippet->label] = $snippet->content;
			}
		}

		// Company information
		$data['store:name'] 	= $data['store_name'] 	 = $this->config->item('store_name');
		$data['store:company'] 	= $data['company_name']  = $this->config->item('company_name');
		$data['store:tel']		= $data['company_tel'] 	 = $this->config->item('company_tel');
		$data['store:fax']		= $data['company_fax'] 	 = $this->config->item('company_fax');
		$data['store:email']	= $data['company_email'] = $this->config->item('company_email');
		$data['store:reg']		= $data['company_reg'] 	 = $this->config->item('company_reg');
		
		// Handy configuration settings
		$data['store:currency'] = $data['currency'] = $this->config->item('currency');
		$data['store:vat_rate'] = $this->config->item('vat_rate');
		$data['site_url']		= site_url();
		$data['current_url']	= current_url();
		
		// Some others that might be used in status notifications
		$data['store_email'] 	= $this->config->item('store_email');
		$data['store:address'] 	= $data['company_address'] = $this->config->item('company_address');
		
		return $data;
	
	}

	#------------------------------------------------------
	# User-Specific Settings
	# - These are settings specific to the 
	#   logged-in admin user
	# @param $id (int)		 - Usually a timestamp
	# @param $type (string)	 - 'shortcut' or other setting type
	# @param $label (string) - Label for the link
	# @param $value (string) - Settings value
	# @param $page (string)  - Page the setting applies to ('inventory', 'order', null, etc)
	#------------------------------------------------------
	// Save user setting
	function save_user_settings($id, $type, $label, $value, $page=null) {
		
		// First get the user settings
		$this->db->select('settings');
		$this->db->where('uid', $this->session->userdata('uid'));
		$query = $this->db->get('users');
		
		if ($query->num_rows() > 0) {
			
			$my_settings = $query->row()->settings;
			
			$settings = unserialize($my_settings);
			
			// If there are NO settings already, create an empty array
			// that we can add stuff to
			if (empty($settings)) {
				$settings = array();
			}
			
			// Add this setting to existing settings
			$settings[$id] = array(
				'type' => $type, 
				'label' => $label, 
				'value' => str_replace(site_url(), '', $value), 
				'page' => $page
			);
			
			// Serialise the data again
			$settings = serialize($settings);
			
			// And save it to the database
			$this->db->set('settings', $settings);
			$this->db->where('uid', $this->session->userdata('uid'));
			$this->db->update('users');
			
		}
		
	}
	
	// Retrieve user setting
	function my_user_settings($uid){
		
		$this->db->select('settings');
		$this->db->where('uid', $uid);
		$query = $this->db->get('users');
		
		$data = unserialize($query->row()->settings);
		
		if (empty($data)) {
			return array();
		} else {
			return $data;
		}
		
	}

	// Remove user setting
	function remove_user_setting($uid, $id) {
		
		// First get all the settings
		$settings = $this->my_user_settings($uid);
		
		// Remove the setting
		unset($settings[$id]);
		
		// Re-serialize the array
		$settings = serialize($settings);
		
		// Re-save the amended array back to the database
		$this->db->set('settings', $settings);
		$this->db->where('uid', $uid);
		$this->db->update('users');
		
	}
	
}