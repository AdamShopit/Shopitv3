<?php

class Install extends CI_Controller {

	function Install()
	{
		parent::__construct();
		$this->load->helper('security');
		$this->load->helper('file');
		$this->load->dbutil();
		$this->load->library('session');
	}
	
	/************************************************
	* Step 1: Change a few files						
	************************************************/
	function step1() {

		// If database config already exists proceed to step 2
		if($this->db->username && $this->db->password && $this->db->hostname && $this->db->database) {

			redirect('step2');

		}

		//Set existing values if database connection exists
		$data['db'] = array(
			'host'		=>	$this->db->hostname,
			'user'		=>	$this->db->username,
			'pass'		=>	$this->db->password,
			'database'	=>	$this->db->database,
			);

		foreach($data['db'] as $db_field){
			if(!empty($db_field)) {
				$errors[] = 1;
			}
		}

		// Set template variables and load view
		$data['active1'] = 'active';
		$data['active2'] = '';
		$data['active3'] = '';
		$data['content']	= 	'step1';
		$data['step_name'] 	= 	'Database Settings';
		$this->load->view('template',$data);
	
	}
	
	
	/************************************************
	* Step 2: Setup admin table						
	************************************************/
	function step2()
	{	

		$data['step_name'] = 'Admin Settings';

		// Define inputs
		$db_host 	= 	$_POST['db_hostname'];
		$db_name 	= 	$_POST['db_name'];
		$db_user 	= 	$_POST['db_username'];
		$db_pass 	= 	$_POST['db_password'];

		foreach($_POST as $db_field){
			if(empty($db_field)) {
				$errors[]	=	1;
			}
		}

		// Check for validation errors (all fields required)
		if(!empty($errors)){

			$this->session->set_flashdata('message', 'Please enter all the required database information');
			redirect('step1');

		} else {
	 	
	 		$db_test = array(
	 			'hostname'		=> 		$db_host,	 			
	 			'database'		=> 		$db_name,	 			
	 			'username'		=> 		$db_user,	 			
	 			'password'		=> 		$db_pass,
	 			'dbdriver'		=>		'mysqli',
	 			'dbprefix'		=>		'',
	 			'pconnect'		=>		FALSE,
	 			'db_debug'		=>		FALSE,
	 			'cache_on'		=>		FALSE,
	 			'cachedir'		=>		'',
	 			'char_set'		=>		'utf8',
	 			'dbcollat'		=>		'utf8_general_ci'
	 		);

	 		// Test database connection with new settings
		    $connect = $this->load->database($db_test, TRUE);
		   

			// If no connection ID returned, throw error and redirect to step 1
			if(empty($connect->conn_id)) {

				$this->session->set_flashdata('message', 'Cannot connect to database. Please check your database settings.');
				redirect('step1');

			} else {

				// If database config is empty save data passed from step 1
				if($this->db->username == '' && $this->db->password == '' && $this->db->hostname == '' &&  $this->db->database == '') {
				    
					$config_file  = '<?php'."\n";    	
					$config_file .= '$' . "db['default']['hostname'] = '" . $db_host . "';" . "\n";
					$config_file .= '$' . "db['default']['database'] = '" . $db_name . "';" . "\n";
					$config_file .= '$' . "db['default']['username'] = '" . $db_user . "';" . "\n";
					$config_file .= '$' . "db['default']['password'] = '" . $db_pass . "';" . "\n";
					$config_file .= '?>';    	
						

					// Write database settings to file
					write_file($_SERVER['DOCUMENT_ROOT'] . '/database.inc.php', $config_file);
				}

				    
			}


			// Set template variables and load view
			$data['active1'] = 'active';
			$data['active2'] = 'active';
			$data['active3'] = '';
			$data['content'] = 'step2';
			$this->load->view('template',$data);

		}
		
	}


	function step3()
	{

		// Redirect to step 1 if no connection found
		if($this->db->username == '' && $this->db->password == '' && $this->db->hostname == '' &&  $this->db->database == '') {

			$this->session->set_flashdata('message', 'Cannot connect to database. Please check your database settings.');
			redirect('step1');

		} else {



		// Loop through input fields to check for missing values
		foreach($_POST as $db_field){
			if(empty($db_field)) {
				$errors[]	=	1;
			}
		}


		// Redirect to step 2 if validation errors occur
		if(!empty($errors)){
			$this->session->set_flashdata('message', 'Please enter all the required user information.');
			redirect('step2');
		}





		$this->load->dbforge();


		//!!! Create 'settings' table
		$fields = array( 
			'setting' 	   => array(
	        						'type' 		 => 'VARCHAR',
	                                'constraint' => '250',
	                                'null' 		 => TRUE
	                              ),
	        'value' => array(
	                                'type' 		 => 'TEXT',
	                                'null' 		 => TRUE,
	                              ),
	        );

		$this->dbforge->add_field($fields); 
		// gives CREATE TABLE IF NOT EXISTS table_name	
		$this->dbforge->create_table('settings', TRUE);

		//!!! Insert default settings				
		$this->db->insert('settings',array('setting' => 'store_name', 'value' => 'Shop Name'));
		$this->db->insert('settings',array('setting' => 'company_name', 'value' => 'Company Name'));
		$this->db->insert('settings',array('setting' => 'company_address', 'value' => 'Company Address'));
		$this->db->insert('settings',array('setting' => 'company_tel', 'value' => '+44 (0) 1234 567890'));
		$this->db->insert('settings',array('setting' => 'company_fax', 'value' => '+44 (0) 1234 567890'));
		$this->db->insert('settings',array('setting' => 'company_email', 'value' => 'store@yourdomain.co.uk'));
		$this->db->insert('settings',array('setting' => 'company_reg', 'value' => 'Reg No: 0123456789. Registered in England and Wales.'));
		$this->db->insert('settings',array('setting' => 'product_type', 'value' => 'all'));
		$this->db->insert('settings',array('setting' => 'variation_attributes', 'value' => 'false'));
		$this->db->insert('settings',array('setting' => 'latest_products', 'value' => '12'));
		$this->db->insert('settings',array('setting' => 'products_per_page', 'value' => '12'));
		$this->db->insert('settings',array('setting' => 'path_to_uploads', 'value' => '/uploads/'));
		$this->db->insert('settings',array('setting' => 'thumbnail_width', 'value' => '149'));
		$this->db->insert('settings',array('setting' => 'thumbnail_height', 'value' => '149'));
		$this->db->insert('settings',array('setting' => 'image_width', 'value' => '349'));
		$this->db->insert('settings',array('setting' => 'image_height', 'value' => '349'));
		$this->db->insert('settings',array('setting' => 'gallery_thumb', 'value' => '50'));
		$this->db->insert('settings',array('setting' => 'image_quality', 'value' => '95'));
		$this->db->insert('settings',array('setting' => 'max_image_width', 'value' => '1000'));
		$this->db->insert('settings',array('setting' => 'image_zoom', 'value' => '600'));
		$this->db->insert('settings',array('setting' => 'currency', 'value' => '&pound;'));
		$this->db->insert('settings',array('setting' => 'caching', 'value' => 'false'));
		$this->db->insert('settings',array('setting' => 'store_email', 'value' => ''));
		$this->db->insert('settings',array('setting' => 'order_no', 'value' => '000001'));
		$this->db->insert('settings',array('setting' => 'vat_rate', 'value' => '0.2'));
		$this->db->insert('settings',array('setting' => 'base_rate', 'value' => '0'));
		$this->db->insert('settings',array('setting' => 'force_99p', 'value' => 'true'));
		$this->db->insert('settings',array('setting' => 'outofstock_purchases', 'value' => 'false'));
		$this->db->insert('settings',array('setting' => 'stock_purchaselimit', 'value' => '20'));
		$this->db->insert('settings',array('setting' => 'show_stockamount', 'value' => 'true'));
		$this->db->insert('settings',array('setting' => 'default_shipping_cost', 'value' => '4.95'));
		$this->db->insert('settings',array('setting' => 'default_shipping_name', 'value' => 'Standard shipping'));
		$this->db->insert('settings',array('setting' => 'payment_sagepay', 'value' => 'false'));
		$this->db->insert('settings',array('setting' => 'payment_cardsave', 'value' => 'false'));
		$this->db->insert('settings',array('setting' => 'payment_worldpay', 'value' => 'false'));
		$this->db->insert('settings',array('setting' => 'payment_paypal', 'value' => 'true'));
		$this->db->insert('settings',array('setting' => 'payment_barclaycard', 'value' => 'false'));
		$this->db->insert('settings',array('setting' => 'campaignmonitor_clientid', 'value' => null));
		$this->db->insert('settings',array('setting' => 'campaignmonitor_listid', 'value' => null));
		$this->db->insert('settings',array('setting' => 'mailchimp_apikey', 'value' => null));
		$this->db->insert('settings',array('setting' => 'mailchimp_listid', 'value' => null));
		$this->db->insert('settings',array('setting' => 'google_ua', 'value' => null));
		$this->db->insert('settings',array('setting' => 'google_ua_code', 'value' => null));
		$this->db->insert('settings',array('setting' => 'arrivals_url', 'value' => null));
		$this->db->insert('settings',array('setting' => 'enable_profiler', 'value' => 'false'));

		unset($fields);
		unset($data_field);


		$data['step_name'] = 'Site Settings';

		//!!! Setup 'users' table
		$fields = array( 
			'uid' 	   => array(
        						'type' => 'INT',
                                'constraint' => '11',
                                'unsigned' => TRUE,
                                'auto_increment' => TRUE
                              ),
			'group_id' => array(
        						'type' => 'SMALLINT',
                                'constraint' => '11',
                                'default' => 0
                              ),
            'username' => array(
                                'type' => 'VARCHAR',
                                'constraint' => '25',
                                'null' => FALSE,
                              ),
            'password' => array(
                                'type' => 'VARCHAR',
                                'constraint' => '255',
                                'null' => FALSE,
                              ),
            'firstname' => array(
                              	'type' =>'VARCHAR',
                                'constraint' => '25',
                                'null' => FALSE,
                              ),
            'surname' => array(
                              	'type' =>'VARCHAR',
                                'constraint' => '25',
                                'null' => TRUE,
                              ),
            'email' => array(
                                'type' => 'VARCHAR',
                                'constraint' => '255',
                                'null' => TRUE,
                              ),
            'tooltips' => array(
                              	'type' =>'VARCHAR',
                                'constraint' => '10',
                                'null' => FALSE,
                                'default' => 'true',
                              ),
            'codebox' => array(
                              	'type' =>'VARCHAR',
                                'constraint' => '10',
                                'null' => TRUE,
                                'default' => 'false',
                              ),
            'settings' => array(
                              	'type' =>'MEDIUMTEXT',
                                'null' => TRUE,
                                'default' => NULL,
                              ),
          );

		$this->dbforge->add_field($fields); 
		$this->dbforge->add_key('uid', TRUE);
		// gives CREATE TABLE IF NOT EXISTS table_name	
		$this->dbforge->create_table('users', TRUE);


		//!!! Create admin user
		$data_fields = array(
			'group_id'	=> 1,
			'username' 	=> 'admin',
			'password'	=> password_hash($this->input->post('admin_password', true), PASSWORD_DEFAULT),
			'firstname'	=> $this->input->post('admin_firstname', true),
			'surname'	=> $this->input->post('admin_lastname', true),
			'email'		=> $this->input->post('admin_email', true),
			'codebox'	=> 'true'
		);


		// Check if username exists.
		$this->db->where('username', 'admin');
		$user_exists = $this->db->get('users');

		// Insert if username does not exist
		if($user_exists->num_rows == 0 ){
			$this->db->insert('users',$data_fields);
		} else {
			// Check password field if username exists (update if password is not empty)
			if(!empty($_POST['admin_password'])){
				$this->db->where('username', 'admin');
				$this->db->update('users');
			}
		}


		unset($fields);
		unset($data_field);

		//!!! Create 'user_groups' table
		if (!$this->db->table_exists('user_groups')) {
		
			$fields = array( 
				'group_id' => array(
					'type' 	 	 => 'smallint',
					'constraint' => 11,
					'null' 		 => FALSE,
					'unsigned'   => TRUE,
	                'auto_increment' => TRUE,
				),
	            'group_title' => array(
					'type' 		 => 'varchar',
					'constraint' => 100,
					'null' 		 => TRUE,
					'default'	 => NULL,
				),
				'group_description' => array(
					'type'		=> 'text',
					'null'		=> TRUE,
					'default'	=> NULL,
				),
				'is_locked' => array(
					'type' 	 	 => 'tinyint',
					'constraint' => 1,
					'null' 		 => TRUE,
					'default'	 => '0',
				)
			);
	
			$permission_fields = array(
				'can_access_dashwidgets_sales', 'can_access_dashwidgets_orders', 'can_access_dashwidgets_inventory', 'can_access_dashwidgets_stats', 'can_access_dashwidgets_realtime',
				'can_access_order_listview', 'can_access_order_builder', 'can_access_shipping', 'can_access_order_custom_fields', 'can_access_order_statuses', 'can_access_order_notifications', 'can_access_order_exports',
				'can_access_inventory_list', 'can_access_inventory_addedit', 'can_access_collections', 'can_access_attribute_sets', 'can_access_product_option_sets', 'can_access_inventory_custom_fields', 'can_access_inventory_exports', 'can_access_inventory_reports',
				'can_access_category_list', 'can_access_category_addedit', 'can_access_category_exports', 
				'can_access_customer_list', 'can_access_customer_exports',
				'can_access_reports', 'can_access_pages', 'can_access_snippets',
				'can_access_options_summary', 'can_access_options_services', 'can_access_options_tools', 'can_access_options_redirection',
				'can_module_myaccount', 'can_module_prioritisation', 'can_module_filters', 'can_module_coupons', 'can_module_stocklocations',
				'can_supersearch', 'can_manage_users',
				'can_access_admin_config', 'can_access_admin_backup', 'can_access_admin_tools'
			);
			
			foreach($permission_fields as $permission) {
				
				$fields[$permission] = 	array(
					'type' 	 	 => 'tinyint',
					'constraint' => 1,
					'null' 		 => TRUE,
					'default'	 => '0',
				);
				
			}
	
			$this->dbforge->add_field($fields); 
			$this->dbforge->add_key('group_id', TRUE);
			$this->dbforge->create_table('user_groups', TRUE); // gives CREATE TABLE IF NOT EXISTS table_name
			unset($fields);
			
			//!- Set up default groups: 'admin', 'client', 'staff'
			// Admin
			$insert_admin = array(
				'group_id' 			=> 1,
				'group_title' 		=> 'Admin',
				'group_description' => 'Project Octo',
				'is_locked'			=> 1,
			);
			
			foreach ($permission_fields as $permission) {
				$insert_admin[$permission] = 1;
			}
			
			$this->db->insert('user_groups', $insert_admin);
			
			// Client
			$client_permissions = array(
				'can_access_dashwidgets_sales', 'can_access_dashwidgets_orders', 'can_access_dashwidgets_inventory', 'can_access_dashwidgets_stats', 'can_access_dashwidgets_realtime',
				'can_access_order_listview', 'can_access_order_builder', 'can_access_shipping', 'can_access_order_custom_fields', 'can_access_order_statuses', 'can_access_order_notifications', 'can_access_order_exports',
				'can_access_inventory_list', 'can_access_inventory_addedit', 'can_access_collections', 'can_access_attribute_sets', 'can_access_product_option_sets', 'can_access_inventory_custom_fields', 'can_access_inventory_exports', 'can_access_inventory_reports',
				'can_access_category_list', 'can_access_category_addedit', 'can_access_category_exports', 
				'can_access_customer_list', 'can_access_customer_exports',
				'can_access_reports', 'can_access_pages', 'can_access_snippets',
				'can_access_options_summary', 'can_access_options_services', 'can_access_options_tools', 'can_access_options_redirection',
				'can_module_myaccount', 'can_module_prioritisation', 'can_module_filters', 'can_module_coupons', 'can_module_stocklocations',
				'can_supersearch', 'can_manage_users',
				'can_access_admin_config', 'can_access_admin_backup'
			);

			$insert_client = array(
				'group_id' 			=> 2,
				'group_title' 		=> 'Client',
				'group_description' => 'Shop Owner',
				'is_locked'			=> 1,
			);

			foreach ($client_permissions as $permission) {
				$insert_client[$permission] = 1;
			}
			
			$this->db->insert('user_groups', $insert_client);
			
			// Staff
			$insert_staff = array(
				'group_id' 			=> 3,
				'group_title' 		=> 'Staff',
				'group_description' => 'Employees group',
			);
			$this->db->insert('user_groups', $insert_staff);

		}
		
		//!!! Create 'user_logs' table
		$fields = array( 
			'id' => array(
				'type' 	 	 => 'int',
				'constraint' => 11,
				'null' 		 => FALSE,
				'unsigned'   => TRUE,
                'auto_increment' => TRUE,
			),
            'timestamp' => array(
				'type' 		 => 'timestamp',
				'null' 		 => TRUE,
			),
			'uid' => array(
				'type' 	 	 => 'int',
				'constraint' => 11,
				'null' 		 => TRUE,
			),
			'username' => array(
				'type'		 => 'varchar',
				'constraint' => 128,
				'null'		 => TRUE,
				'default'	 => NULL,
			),
			'referer' => array(
				'type' 	 	 => 'text',
				'null' 		 => TRUE,
				'default'	 => NULL,
			),
			'user_agent' => array(
				'type' 	 	 => 'text',
				'null' 		 => TRUE,
				'default'	 => NULL,
			),
			'access_url' => array(
				'type' 	 	 => 'text',
				'null' 		 => TRUE,
				'default'	 => NULL,
			),
			'ip_address' => array(
				'type'		 => 'varchar',
				'constraint' => 128,
				'null'		 => TRUE,
				'default'	 => NULL,
			),
		);
		
		$this->dbforge->add_field($fields); 
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('user_logs', TRUE); // gives CREATE TABLE IF NOT EXISTS table_name
		unset($fields);


			// Import SQL Dump
			$import = $this->import_schema($_SERVER['DOCUMENT_ROOT'] . '/install/views/default.sql');
			//var_dump($import); //for debugging
			
		}


		// Set template variables and load view
		$data['active1'] = 'active';
		$data['active2'] = 'active';
		$data['active3'] = 'active';
		$data['content'] = 'step3';
		$this->load->view('template',$data);
	}




	/*
	|--------------------------------------------------------------------------
	| Import Schema
	|--------------------------------------------------------------------------
	|
	| Imports an SQL dump file into the selected database.
	|
	*/
	private function import_schema($path_to_file) {
		
		if (!empty($path_to_file)) {
		
			$sql = file_get_contents($path_to_file);
			
			$final = '';
			
			if (!empty($sql)) {
			
				foreach(explode("\n", $sql) as $line) {
					if ( isset($line[0]) && $line[0] != '#' ) {
						$final .= $line . "\n";
					}
				}
				
				foreach (explode(";\n", $final) as $sql) {
					if ($sql) {
						$this->db->query($sql);
					}
				}
				
				return true;
			
			} else {
				return false;
			}
			
		} else {
			return false;
		}
		
	}
}