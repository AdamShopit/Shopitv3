<?php

class Options extends CI_Controller {
	
	function Options() {

		parent::__construct();

		$this->load->database();
		
		$this->load->helper('file');
		$this->load->helper('download');
		$this->load->helper('number');
		$this->load->model('settings_model');
		$this->load->model('google_model','google');
		$this->load->model('redirection_model');
		$this->load->model('inventory_model');
		$this->load->library('pagination');	

		$this->settings_model->initConfig();

		/* Login check */
		$this->load->model('login_model');
		if(!$this->login_model->check_session()){
			redirect('/login');
		}
		/* End: Login check */

	}

	#------------------------------------------------------
	# List options available
	#------------------------------------------------------
	function index() {

		$this->permissions->access('can_access_options_summary');

		define('XMLPATH', $_SERVER['DOCUMENT_ROOT']."/base/");

		// Get the date we need to display info
		$data['locations'] = $this->inventory_model->getLocations();

		$data['title']	 = 'Options';
		$data['content'] = 'options/options';
		
		$data['app_path'] = $_SERVER['DOCUMENT_ROOT'] . '/application/admin/controllers/';
		
		$this->load->view('global/template',$data);
	}
	
	#------------------------------------------------------
	# Get configuration (for admin account)
	#------------------------------------------------------
	function configuration() {

		$this->permissions->access('can_access_admin_config');
		$this->permissions->access('can_access_admin_tools');
	
		$data['title']	 = 'Settings';
		$data['content'] = 'options/settings';
		
		$settings = $this->settings_model->getSettings();
		
		foreach ($settings as $setting) {
		
			$data[$setting->setting] = $setting->value;
		
		}
				
		$data['form_open'] = '<form action="'.site_url('options/saveconfig').'" method="post" enctype="multipart/form-data" >';
		$data['form_title'] = 'Store configuration';
		$data['form_close'] = '</form>';
		$data['form_cancel_link'] = site_url('dashboard');

		$this->load->view('global/template',$data);
	
	}

	#------------------------------------------------------
	# Save configuration
	#------------------------------------------------------
	function saveconfig() {

		$this->permissions->access('can_access_admin_config');
		$this->permissions->access('can_access_admin_tools');

		//Empty the settings table first
		$this->db->empty_table('settings');
		
		//Insert the data in it's table
		foreach ($_POST as $setting => $value) {
			
			if ($setting != 'submit'):
			
			switch ($value) {
			
				case 'pound':
					$value = '&'.$value.';';
					break;
				case 'euro':
					$value = '&'.$value.';';
					break;	
			
			}
			
			$this->settings_model->saveSetting($setting,$value);
			endif;
			
		}
		
		//Get a fresh copy of the data now...
		$settings = $this->settings_model->getSettings();
		
		//Cache notice
		$cache_notice = ($this->config->item('caching') == 'true') ? ' If you have changed any price related settings, you will need to clear site cache. <a href="'.site_url('options/clearcache').'">Clear cache</a>' : '';
		
		//Re-direct the page with the new settings
		$this->session->set_flashdata('notice',"Store configuration settings saved. $cache_notice");		
		redirect('/options/configuration');

	}

	#------------------------------------------------------
	# Get preferences (for client account)
	#------------------------------------------------------
	function preferences() {

		$this->permissions->access('can_access_admin_config', true);

		$data['title']	 = 'Store Options';
		$data['content'] = 'options/client_options';
		
		$settings = $this->settings_model->getSettings();
		
		foreach ($settings as $setting) {
		
			$data[$setting->setting] = $setting->value;
		
		}

		$data['form_open'] = '<form action="'.site_url('options/saveprefs').'" method="post" enctype="multipart/form-data" >';
		$data['form_title'] = 'Store Preferences';
		$data['form_close'] = '</form>';
		$data['form_cancel_link'] = site_url('dashboard');
				
		$this->load->view('global/template',$data);

	}

	#------------------------------------------------------
	# Save preferences
	#------------------------------------------------------
	function saveprefs() {

		$this->permissions->access('can_access_admin_config', true);
	
		//Update the data
		foreach ($_POST as $setting => $value) {
			
			if ($setting != 'submit'):
			
			switch ($value) {
			
				case 'pound':
					$value = '&'.$value.';';
					break;
				case 'euro':
					$value = '&'.$value.';';
					break;	
			
			}
			
			$this->settings_model->updateSetting($setting,$value);
			endif;
			
		}

		//Get a fresh copy of the data now...
		$settings = $this->settings_model->getSettings();
	
		//Re-direct the page with the new settings
		$this->session->set_flashdata('notice','Store options saved.');		
		redirect('/options/preferences');

	}

	#------------------------------------------------------
	# Turn tooltips on/off
	#------------------------------------------------------
	function tooltips(){
	
		if ($this->config->item('tooltips') == 'true') {
			//turn tooltips off
			$this->settings_model->tooltips('false');
			$tooltips = 'off';
			
		} else {
			//turn tooltips on
			$this->settings_model->tooltips('true');
			$tooltips = 'on';
		}

		print json_encode(
			array('status' => $tooltips)
		);
		
	}

	#------------------------------------------------------
	# Turn codebox on/off (applies to editor)
	#------------------------------------------------------
	function codebox(){
	
		if ($this->config->item('codebox') == 'true') {
			//turn codebox off
			$this->settings_model->codebox('false');
			$codebox = 'off';
			
		} else {
			//turn codebox on
			$this->settings_model->codebox('true');
			$codebox = 'on';
		}

		print json_encode(
			array('status' => $codebox)
		);
		
	}

	#------------------------------------------------------
	# Update Google Base/Sitemap XML files
	#------------------------------------------------------
	function googlise() {
		$this->permissions->access('can_access_options_services');
		$result = $this->google->generate("Google feeds have been updated successfully.",false);
		$this->session->set_flashdata('notice',$result);
		redirect('options');
	}

	function xmldownload() {
		$this->permissions->access('can_access_options_services');
		$get = $this->uri->segment(3);
		$file = file_get_contents($_SERVER['DOCUMENT_ROOT']."/base/$get");
		force_download($get, $file);		
	}

	function xmlpreview() {
		$this->permissions->access('can_access_options_services');
		header('Content-type: text/xml');
		$get = $this->uri->segment(3);
		$file = file_get_contents($_SERVER['DOCUMENT_ROOT']."/base/$get");
		print $file;		
	}
	
	//Use this function for testing ~ outputs directly to screen
	//and pulls the data direct from the database
	function livepreview() {
		$this->permissions->access('can_access_options_services');
		header('Content-type: text/xml');
		$xml = $this->google->base(false);
		print $xml;
	}

	#------------------------------------------------------
	# Redirection
	#------------------------------------------------------
	function redirection() {

		$this->permissions->access('can_access_options_redirection');
		
		switch ($this->uri->segment(3)) {
		
			case "delete":
				$this->redirection_model->deleteRedirection($this->uri->segment(4));
				break;
				
			default:
				//Add redirect
				if (!empty($_POST)) {
					if ($this->input->post('filter') == 'true' && $this->input->post('s_oldurl') != '' && $this->input->post('s_newurl')) {
						$this->redirection_model->addRedirection();
						$this->session->set_flashdata('notice', 'The ' . $this->input->post('s_statuscode') . ' redirect for ' . $this->input->post('s_oldurl') . ' has been setup.');
						redirect($this->input->post('redirect_url'));
					}			
				}
		
				//For pagination	
				$config['base_url'] = site_url('options/redirection');
		
				$query = $this->redirection_model->countRedirections();
				
				$config['total_rows'] 	= $query;
				$config['uri_segment'] 	= 3;
				$config['per_page'] = 25;
				$this->pagination->initialize($config);
				
				$data['results_total'] = $config['total_rows'];
				//End of pagination
		
				$data['title']	 = 'Redirections';
				$data['redirections'] = $this->redirection_model->listRedirections($config['per_page'],$this->uri->segment(3));
				
				$data['content'] = 'options/redirection_list';
		
				$this->load->view('global/template',$data);
				
				break;
		
		}
	
	}

	#------------------------------------------------------
	# Backup the database
	#------------------------------------------------------	
	function backup() {

		$this->permissions->access('can_access_admin_backup');

		// Load the DB utility class
		$this->load->dbutil();
		
		// Backup your entire database and assign it to a variable
		$backup =& $this->dbutil->backup(); 
		
		// Load the download helper and send the file to your desktop
		$this->load->helper('download');
		force_download('mybackup-'.date('Y-m-d').'.sql.zip', $backup);	
	}

	#------------------------------------------------------
	# Clear ALL cache
	#------------------------------------------------------
	function clearcache() {

		$this->permissions->access('can_access_options_tools');
	
		delete_cache();
		$result = "All cache has been cleared.";
		$this->session->set_flashdata('notice',$result);
		redirect('options');
	
	}
	
	#------------------------------------------------------
	# API Keys
	# - used to access/protect certain external services
	#------------------------------------------------------
	function api() {

		$this->permissions->access('can_access_options_services');
		
		switch($this->uri->segment(3)) {
			
			case "create":
				$this->settings_model->createAPI();
				$result = "Woohoo! You've just created a super cool API key.";
				$this->session->set_flashdata('notice', $result);
				redirect('options/api');
				break;
				
			case "enable":
				$this->settings_model->changeAPIStatus($this->uri->segment(4), 1);
				redirect('options/api');
				break;

			case "disable":
				$this->settings_model->changeAPIStatus($this->uri->segment(4), 0);
				redirect('options/api');
				break;
				
			case "label":
				if (!empty($_POST['id'])) {
				$this->settings_model->updateAPILabel($_POST['id'], $_POST['value']);
				print $_POST['value'];
				}
				break;
			
			default:
				$data['title']	 = 'API Keys';
				$data['content'] = 'options/apis';
				
				$data['api_keys'] = $this->settings_model->getAPIs();
				$this->load->view('global/template', $data);
				break;
		}
		
	}

	#------------------------------------------------------
	# PHP Info (for Admin's)
	#------------------------------------------------------
	function server() {

		$this->permissions->access('can_access_admin_tools');

		// Get php info and strip out the head and body
		ob_start();
		phpinfo(INFO_MODULES);
		$pinfo = ob_get_contents();
		ob_end_clean();
 
		$pinfo = preg_replace( '%^.*<body>(.*)</body>.*$%ms','$1',$pinfo);
		
		// Output
		$data['phpinfo'] = $pinfo;
		$data['title']	 = 'Server Configuration';
		$data['content'] = 'options/tools_server';
		$this->load->view('global/template', $data);
		
	}
	
	#------------------------------------------------------
	# Developer: Inventory CSV Export
	#------------------------------------------------------
	function devexport() {
		$this->permissions->access('can_access_admin_tools');
		$data = $this->inventory_model->developerExport();
		$filename = 'full_inventory_'.date('Y-m-d').'.csv';
		force_download($filename, $data);
		break;
	}

	#------------------------------------------------------
	# Ajax: Custom Shortcuts
	# - Enables users to add their own shortcuts to set
	#   menus in the admin.
	#------------------------------------------------------
	// Create shortcut
	function shortcut() {
		
		$id    = time();
		$type  = 'shortcut';
		$page  = ($this->uri->segment(3) == 'orders') ? 'orders' : 'inventory';
		$label = $this->input->get('label');
		$value = base64_decode($this->uri->segment(4));
		
		// Save to database
		$this->settings_model->save_user_settings($id, $type, $label, $value, $page);
		
		echo json_encode(array(
			'id'	=> $id,
			'type'  => $type,
			'page'  => $page,
			'label' => $label,
			'value' => $value,
		));
		
	}
	
	// Remove shortcut
	function deshortcut() {
		$id = $this->uri->segment(3);
		$this->settings_model->remove_user_setting($this->session->userdata('uid'), $id);
	}

}