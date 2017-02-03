<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
#------------------------------------------------------
# Shopit core functions
#------------------------------------------------------
class Shopit {

	#------------------------------------------------------
	//! Shopit version number
	#------------------------------------------------------
	function version() {
		
		$shopit =& get_instance();
		
		$version = $shopit->config->item('version');
		$major 	 = $shopit->config->item('major');
		$minor 	 = $shopit->config->item('minor');

		if ($minor > 0) {
			return "$version.$major.$minor";
		} else {
			return "$version.$major";
		}
		
	}

	#------------------------------------------------------
	# Get the number of days since the admin config file
	# was last modified. Used within version_check() and
	# new_version_popup() functions below
	# @returns No. of days (int)
	#------------------------------------------------------
	private function pre_version_check() {
		
		$shopit =& get_instance();
	
		// Get the dates
		#$today = date('Y-m-d', strtotime('1 weeks')); // for testing
		$today 	  = date('Y-m-d');
		$filetime = get_filetime('/admin/config/config.php', 'Y-m-d');
		
		// Work out the difference in days
		$datetime1 = new DateTime($filetime);
		$datetime2 = new DateTime($today);
		$interval  = $datetime1->diff($datetime2);
		$days      = $interval->format('%R%a');
		
		return $days;
		
	} 

	#------------------------------------------------------
	# New version check
	#   - Displays a new update installed notification
	#     together with a link to release notes
	#------------------------------------------------------
	function version_check($max_days=5) {
		
		$shopit =& get_instance();

		$days = $this->pre_version_check();
		
		// Display a message for X days
		if ($days >= 0 and $days <= $max_days) {
			$message = sprintf('<a class="version-notice" href="%s" target="_blank" title="A new update has been installed - Click here to see what\'s changed.">A new update #%s has been installed!</a>', site_root('user-guide/release-notes.html'), $this->version());
		} else {
			$message = '';
		}
		
		return $message;
		
	}
	
	#------------------------------------------------------
	# New Features Notification
	#   - Displays an info modal containing a brief guide
	#     to new features within the current Shopit version
	#   - Templates are located in 'views/misc' folder
	#     with each file named after the version number
	#     e.g. 3111.php (3.1.11)
	#   - Any images should be stored in 'assets/images/misc'
	#------------------------------------------------------
	function new_version_popup($max_days=5) {
		
		$shopit =& get_instance();
		$html = '';
		
		$days = $this->pre_version_check();
		$version = str_replace('.', '', $this->version());
		
		// Get the notification cookie
		$notification_cookie = get_cookie('shopit_new_feature_notification_read');
		
		// Check a feature template exists - not all updates will have new features
		$template = (file_exists(APPPATH."views/misc/$version.php")) ? TRUE : FALSE;
		
		// Modal is available for $max_days AND only if cookie is not set AND a template exists
		if ($days >= 0 and $days <= $max_days and !$notification_cookie and $template === true) {
			
			// Begin jquery script
			$html .= "<script type=\"text/javascript\">\n";
			$html .= "$(document).ready(function() {\n";
			
			// Fancybox modal
			$html .= '$(".shopit-new-features-popup").fancybox({
						"autoSize": false, 
						"width": 960, 
						"height": 400, 
						"margin":0, 
						"padding": 0, 
						"closeBtn": true, 
						"closeClick": false,
						"loop": false,
						"modal": false,
						"index": 0,
						"helpers": { 
							overlay: {
								closeClick: false, 
								css: {
									"background": "rgba(255,255,255,0.5)"
									}
							} 
						},
						"keys": { close: false, next: false, prev: false },
						"live": true,
						// Drop cookie that lasts for $max_days + 2 days after modal is closed
						"afterClose": function(){
							var expiry_date = "'.date('D, j M Y H:i:s T', time() + (($max_days + 2) * 24 * 60 * 60)).'";
							document.cookie="shopit_new_feature_notification_read=true;expires="+expiry_date+";path=/";
						}
					  });' . "\n";
			
			// Trigger fancybox on click
			$html .= "$('.shopit-new-features-popup:first').trigger('click');\n";
			
			// Close jquery script
			$html .= "\n});\n";
			$html .= "</script>\n";
			
			// Our hidden content for the modal
			$html .= $shopit->load->view("misc/$version", null, true) . "\n\n";
		}
		
		return $html;
		
	}

	#------------------------------------------------------
	# Log version update
	# - This adds a record to 'version_log' table to
	#   indicate update has been performed
	#------------------------------------------------------
	function log_version_update($message='') {
		
		$shopit =& get_instance();
		
		$shopit->db->set('version', $this->version());
		$shopit->db->set('message', $message);
		$shopit->db->set('timestamp', date('Y-m-d H:i:s', time()));
		$shopit->db->insert('version_log');
		
	}

	#------------------------------------------------------
	//! Browser/Platform Detection
	#------------------------------------------------------
	function user_browser() {
	
		$user = (object) array();
		
		$agent = $_SERVER['HTTP_USER_AGENT'];
		
		// Check platform Mac/PC
		if (strpos($agent, "Macintosh") || strpos($agent, "Mac")):
			$user->{'platform'} = "mac";
		elseif (strpos($agent, "Windows")):
			$user->{'platform'} = "win";
		elseif (strpos($agent, "iPhone") || strpos($agent, "iPad")):
			$platform = "ios";
		elseif (strpos($agent, "Android")):
			$platform = "android";
		else:
			$user->{'platform'} = "";
		endif;
	
		// Check browser
		if (strpos($agent, "Safari")) {
		
			$user->{'browser'} = "safari";
			$user->{'version'} = 99;
			
		} elseif (strpos($agent, "MSIE")) {
		
			$user->{'browser'} = "explorer";
			
			if (strpos($agent, "MSIE 6")):
				$user->{'version'} = 6;
			elseif (strpos($agent, "MSIE 7")):
				$user->{'version'} = 7;
			elseif (strpos($agent, "MSIE 8")):
				$user->{'version'} = 8;
			elseif (strpos($agent, "MSIE 9")):
				$user->{'version'} = 9;
			elseif (strpos($agent, "MSIE 10")):
				$user->{'version'} = 10;
				$user->{'browser'} = "iexplorer";
			else:
				$user->{'version'} = 99;
				$user->{'browser'} = "iexplorer";
			endif;
			
		} elseif (strpos($agent, "Firefox")) {
			
			$user->{'browser'} = "firefox";
			$user->{'version'} = 99;
			
		} elseif (strpos($agent, "Opera")) {
		
			$user->{'browser'} = "opera";
			$user->{'version'} = 99;
		
		}
		
		// Return array
		return $user;
		
	}

	#------------------------------------------------------
	//! Dynamic Menu
	# 	- Auto creates the main admin navigation based on
	#     logged in user's permissions
	# Array elements:
	# - url				Controller/method
	# - permission 		true/false
	# - activemenu		Sets the controller name that defines
	# 					whether this menu is active or not.
	# 					Multiple controllers must be separated
	# 					with a | (pipeline)
	# - notification	Displays the menu bubble
	# - menu			Array of submenu items
	#   - separator		Sets the current item as a separator
	#   - label			Sets a label above the current item if
	# 					'separator' is set to true
	# 	- module		Checks if this item should be displayed
	# 					only if a module/library is installed
	# 					(or can use a config setting that
	# 					returns a boolean)
	#------------------------------------------------------
	function nav() {
	
		$shopit =& get_instance();
		
		// Set a few things
		$html = '';
		$nl   = "\n";
		
		// Get custom shortcuts
		$my_shortcuts = $shopit->settings_model->my_user_settings($shopit->session->userdata('uid'));
		
		// Uncomment the line below for debugging
		#echo sprintf("<pre>%s</pre>", print_r($my_shortcuts, true));
		
		// Set the options for each section as an array 
		$opt_dashboard = array(
			'Dashboard' => array(
				'url' 		 => 'dashboard',
				'permission' => true,
				'activemenu' => 'dashboard',
			)
		);

		$opt_orders = array(
			
			'Orders' => array(
				
				'activemenu' => 'orders|shipping',
				'notification' => todays_orders(),
				'menu' => array(
			
					'This Week\'s Activity' => array(
						'url' 			=> 'orders',
						'permission'	=> $shopit->config->item('can_access_order_listview'),
					),
					'View All Orders' => array(
						'url' 			=> 'orders/all',
						'permission' 	=> $shopit->config->item('can_access_order_listview'),
					),	
					'Create New Order' => array(
						'url' 			=> 'orders/build',
						'permission' 	=> $shopit->config->item('can_access_order_builder'),
					),
					'Manage Rules' => array(
						'url' 			=> 'shipping',
						'permission' 	=> $shopit->config->item('can_access_shipping'),
						'separator' 	=> true,
						'label' 		=> 'Shipping Rules',
					),	
					'Create New Rule' => array(
						'url' 			=> 'shipping/create',
						'permission' 	=> $shopit->config->item('can_access_shipping'),
					),	
					'Manage VAT' => array(
						'url'           => 'shipping/vat',
						'permission'   => $shopit->config->item('can_access_shipping'),
					),
					'Custom Fields' => array(
						'url' 			=> 'orders/custom',
						'permission' 	=> $shopit->config->item('can_access_order_custom_fields'),
						'separator'     => true,
						'label' 		=> null,
					),
					'Order Statuses' => array(
						'url' 			=> 'orders/statuses',
						'permission'	=> $shopit->config->item('can_access_order_statuses'),
						'separator' 	=> true,
						'label' 		=> null,
					),
					'Status Notifications' => array(
						'url' 			=> 'orders/notifications',
						'permission'	=> $shopit->config->item('can_access_order_notifications'),
					),
					'Packing Note Templates' => array(
						'url'			=> 'orders/templates',
						'permission'	=> $shopit->config->item('can_access_order_notifications'),
						'separator'		=> true,
						'label'			=> null,
					),
		
				)
			
			)
		
		);
		
		// Loop through each custom shortcut and add to this menu
		foreach($my_shortcuts as $shortcut_id => $shortcut) {
			
			if ($shortcut['type'] == 'shortcut' and $shortcut['page'] == 'orders') {
				
				$os++;
				$separator = ($os == 1) ? true : false;
				
				$shortcut_label = sprintf('%s <span data-link="%s">&#10005;</span>', $shortcut['label'], site_url("options/deshortcut/$shortcut_id"));
				
				// Add our shortcut to the menu
				$opt_orders['Orders']['menu'][$shortcut_label] = array(
					'url' 		 => $shortcut['value'],
					'permission' => true,
					'separator'  => $separator,
					'label' 	 => null,
					'class'		 => 'shopit-shortcut',
				);
				
				// Set a flag to use in the 'plus' array item
				$has_order_shortcuts = true;
				
			}
			
		}

		// End the orders menu with the 'plus' button
		$opt_orders['Orders']['menu']['&#43;'] = array(
				'url'			=> sprintf('options/shortcut/orders/%s', base64_encode(current_url())),
				'permission'	=> true,
				'separator'		=> (!$has_order_shortcuts) ? true: false,
				'label'			=> null,
				'class'			=> 'shopit-shortcut-create'
		);

		// Inventory
		$option_new_item = (($shopit->config->item('product_type') == 'single' || $shopit->config->item('product_type') == 'all') && $shopit->config->item('can_access_inventory_addedit')) ? true : false;
		$option_new_item_variations = (($shopit->config->item('product_type') == 'variations' || $shopit->config->item('product_type') == 'all') && $shopit->config->item('can_access_inventory_addedit')) ? true : false;

		$cat_jumpmenu = '<form id="quickgo_category" method="post" action="'.site_url('inventory/index').'">';
		$cat_jumpmenu .= '<input type="hidden" name="filter" value="true" />';
		$cat_jumpmenu .= '<select name="s_category" class="dropdown" onchange="this.form.submit();">';
		$categories = $shopit->category_model->getAvailableCategories();
		
		if ($categories > 0) { 
			$cat_jumpmenu .= '<option value="">Select category...</option>';
			foreach($categories as $category) { 
				$cat_jumpmenu .= '<option value="category-'.$category->cat_id.'">'.str_replace(' & ', ' &amp; ',$category->cat_name).'</option>';
			}
		}
		
		$cat_jumpmenu .= '</select>';
		$cat_jumpmenu .= '</form>';
		
		$opt_inventory = array(
			
			'Inventory' => array(

				'activemenu' => 'inventory|collections',
				'menu' => array(
				
					'View List' => array(
						'url' 			=> 'inventory',
						'permission' 	=> $shopit->config->item('can_access_inventory_list'),
					),
					'New Item' => array(
						'url' 			=> 'inventory/add',
						'permission' 	=> $option_new_item,
					),
					'New Item With Variations' => array(
						'url' 			=> 'inventory/add/variation',
						'permission' 	=> $option_new_item_variations,
					),
					'Manage Collections' => array(
						'url' 			=> 'collections',
						'permission' 	=> $shopit->config->item('can_access_collections'),
						'separator' 	=> true,
						'label' 		=> null,
					),
					'Add New Collection' => array(
						'url' 			=> 'collections/create',
						'permission' 	=> $shopit->config->item('can_access_collections'),
					),
					'Collection Groups' => array(
						'url' 			=> 'collections/groups',
						'permission' 	=> $shopit->config->item('can_access_collections'),
					),
					'Manage Locations' => array(
						'url' 			=> 'inventory/locations',
						'permission' 	=> $shopit->config->item('can_module_stocklocations'),
						'separator' 	=> true,
						'label' 		=> 'Stock Locations',
					),
					'Attribute Sets' => array(
						'url' 			=> 'inventory/attributesets',
						'permission' 	=> $shopit->config->item('can_access_attribute_sets'),
						'separator' 	=> true,
						'label' 		=> null
					),
					'Product Option Sets' => array(
						'url' 			=> 'inventory/productoptionsets',
						'permission' 	=> $shopit->config->item('can_access_product_option_sets'),
					),
					'Custom Fields' => array(
						'url' 			=> 'inventory/custom',
						'permission' 	=> $shopit->config->item('can_access_inventory_custom_fields'),
					),
					'Coupons' => array(
						'url' 			=> 'modules/coupons',
						'permission' 	=> $shopit->config->item('can_module_coupons'),
					),
					'Cross-Sell Groups' => array(
						'url' 			=> 'inventory/crosssellgroups',
						'permission' 	=> $shopit->config->item('can_access_inventory_addedit'),
					),
					$cat_jumpmenu => array(
						'url' 			=> null,
						'permission' 	=> $shopit->config->item('can_access_inventory_list'),
						'separator'		=> true,
						'label'			=> 'Go to...',
					),
				
				)
			
			)
		
		);

		// Loop through each custom shortcut and add to this menu
		foreach($my_shortcuts as $shortcut_id => $shortcut) {
			
			if ($shortcut['type'] == 'shortcut' and $shortcut['page'] == 'inventory') {
				
				$is++;
				$separator = ($is == 1) ? true : false;

				$shortcut_label = sprintf('%s <span data-link="%s">&#10005;</span>', $shortcut['label'], site_url("options/deshortcut/$shortcut_id"));
				
				// Add our shortcut to the menu
				$opt_inventory['Inventory']['menu'][$shortcut_label] = array(
					'url' 		 => $shortcut['value'],
					'permission' => true,
					'separator'  => $separator,
					'label' 	 => null,
					'class'		 => 'shopit-shortcut',
				);
				
				// Set a flag to use in the 'plus' array item
				$has_inventory_shortcuts = true;
				
			}
			
		}

		// End the orders menu with the 'plus' button
		$opt_inventory['Inventory']['menu']['&#43;'] = array(
				'url'			=> sprintf('options/shortcut/inventory/%s', base64_encode(current_url())),
				'permission'	=> true,
				'separator'		=> (!$has_inventory_shortcuts) ? true: false,
				'label'			=> null,
				'class'			=> 'shopit-shortcut-create'
		);

		// Categories
		$opt_categories = array(
				
			'Categories' => array(

				'activemenu' => 'category|prioritisation|filters',
				'menu' => array(
				
					'View Categories' => array(
						'url' 			=> 'category',
						'permission' 	=> $shopit->config->item('can_access_category_list'),
					),
					'Add New Category' => array(
						'url' 			=> 'category/add',
						'permission' 	=> $shopit->config->item('can_access_category_addedit'),
					),
				
				)
			
			)
		
		);
		
		$opt_customers = array(
			
			'Customers' => array(

				'activemenu' => 'customers',
				'menu' => array(
				
					'Customers/Orders' => array(
						'url'			=> 'customers',
						'permission'	=> $shopit->config->item('can_access_customer_list'),
					), 
					'Manage Accounts' => array(
						'url'			=> 'customers/accounts',
						'permission'	=> $shopit->config->item('can_module_myaccount'),
						'separator'		=> true,
						'label'			=> 'Customer Accounts',
						'module'		=> library_exists('myaccount'),
					), 
					'Create New Account' => array(
						'url'			=> 'customers/create',
						'permission'	=> $shopit->config->item('can_module_myaccount'),
						'module'		=> library_exists('myaccount'),
					), 
				
				)
			
			)
			
		);
		
		// Reports
		$opt_reports = array(
		
			'Reports' => array(
				'url' 		 => 'reports',
				'permission' => $shopit->config->item('can_access_reports'),
				'activemenu' => 'reports',
			)
		
		);
		
		// Pages
		$opt_pages = array(

			'Pages' => array(

				'activemenu' => 'pages',
				'menu' => array(
	
					'Manage Pages' => array(
						'url'			=> 'pages',
						'permission'	=> $shopit->config->item('can_access_pages'),
					), 
					'Create New Page' => array(
						'url'			=> 'pages/create',
						'permission'	=> $shopit->config->item('can_access_pages'),
					), 
					'Manage Snippets' => array(
						'url'			=> 'pages/snippets',
						'permission'	=> $shopit->config->item('can_access_snippets'),
						'separator'		=> true,
						'label'			=> 'Snippets'
					), 
					'Snippet Groups' => array(
						'url'			=> 'pages/snippetgroups',
						'permission'	=> $shopit->config->item('can_access_snippets'),
					), 
				
				)
			
			)
		
		);

		// Options
		$tooltip_class = (tooltips_pref()) ? '' : 'hide-tick';
		$codebox_class = (codebox_pref()) ? '' : 'hide-tick';
		
		$admin_site_config = ($shopit->config->item('can_access_admin_config') && $shopit->config->item('can_access_admin_tools')) ? true : false;
		$client_site_config = ($shopit->config->item('can_access_admin_config') && !$shopit->config->item('can_access_admin_tools')) ? true : false;
		
		$opt_options = array(
					
			'Options' => array(

				'activemenu' => 'options|statusboard',
				'menu' => array(
				
					'Services &amp; Modules' => array(
						'url'			=> 'options',
						'permission'	=> $shopit->config->item('can_access_options_summary'),
					), 
					'Site Configuration' => array(
						'url'			=> 'options/configuration',
						'permission'	=> $admin_site_config,
					), 
					'Site Preferences' => array(
						'url'			=> 'options/preferences',
						'permission'	=> $client_site_config,
					), 
					'Update Google XML Feeds' => array(
						'url'			=> 'options/googlise/manual',
						'permission'	=> $shopit->config->item('can_access_options_services'),
						'separator'		=> true,
						'label'			=> 'Services',
					), 
					'Status Board' => array(
						'url'			=> 'statusboard',
						'permission'	=> $shopit->config->item('can_access_options_services'),
					), 
					'API Keys' => array(
						'url'			=> 'options/api',
						'permission'	=> $shopit->config->item('can_access_options_services'),
					), 
					'Redirection' => array(
						'url'			=> 'options/redirection',
						'permission'	=> $shopit->config->item('can_access_options_redirection'),
					), 
					'PHP Info' => array(
						'url'			=> 'options/server',
						'permission'	=> $shopit->config->item('can_access_admin_tools'),
						'separator'		=> true,
						'label'			=> 'Developer Tools',
					), 
					'Shopit3 Developer Guide' => array(
						'url'			=> '/user-guide',
						'permission'	=> $shopit->config->item('can_access_admin_tools'),
						'external_link' => '_blank',
					), 
					'Shopit3 Release Notes' => array(
						'url'			=> '/user-guide/release-notes.html',
						'permission'	=> $shopit->config->item('can_access_admin_tools'),
						'external_link' => '_blank',
					), 
					'Import Inventory' => array(
						'url'			=> 'import/inventory',
						'permission'	=> $shopit->config->item('can_access_admin_tools'),
						'separator'		=> true,
						'label'			=> 'Import/Export Tools',
					), 
					'Update Inventory' => array(
						'url'			=> 'import/updateinventory',
						'permission'	=> $shopit->config->item('can_access_admin_tools'),
					), 
					'Import Categories' => array(
						'url'			=> 'import/categories',
						'permission'	=> $shopit->config->item('can_access_admin_tools'),
					), 
					'Developer Inventory Export' => array(
						'url'			=> 'options/devexport',
						'permission'	=> $shopit->config->item('can_access_admin_tools'),
					), 
					'<img src="'.template_directory('assets/images/tick-white.png').'" class="'.$tooltip_class.'" /> Show Tooltips' => array(
						'url'			=> 'options/tooltips',
						'permission'	=> $shopit->config->item('can_access_options_tools'),
						'separator'		=> true,
						'label'			=> 'Tools',
						'id'			=> 'pref_tooltips',
					), 
					'<img src="'.template_directory('assets/images/tick-white.png').'" class="'.$codebox_class.'" /> Enable Codebox' => array(
						'url'			=> 'options/codebox',
						'permission'	=> $shopit->config->item('can_access_options_tools'),
						'id'			=> 'pref_codebox',
					), 
					'Backup Database' => array(
						'url'			=> 'options/backup',
						'permission'	=> $shopit->config->item('can_access_admin_backup'),
					), 
				
				)
				
			)
		
		);

		// User
		$secure_password_link = base64_encode( $shopit->encrypt->encode($shopit->session->userdata('uid')) );

		// Define Name
		if(!empty($shopit->session->userdata('firstname'))){
			$name = $shopit->session->userdata('firstname');
		} else {
			$name = $shopit->session->userdata('username');
		}
		
		$opt_user = array(
			$name => array(

				'activemenu' => '',
				'menu' => array(
				
					'Browse Store' => array(
						'url'			=> site_root(),
						'permission'	=> true,
						'external_link' => '_self',
					), 
					'Manage Users' => array(
						'url'			=> 'users',
						'permission'	=> $shopit->config->item('can_manage_users'),
					), 
					'Change My Password' => array(
						'url'			=> "users/update/$secure_password_link",
						'permission'	=> true,
					), 
					'Change My Avatar' => array(
						'url'			=> 'https://en.gravatar.com',
						'permission'	=> true,
						'external_link' => '_parent',
					), 
					'Logout' => array(
						'url'			=> 'logout',
						'permission'	=> true,
						'separator'		=> true,
						'label'			=> null,
					), 
				
				)
				
			)
		
		);

		// Merge all the arrays
		$nav_array = array_merge(
			$opt_dashboard,
			$opt_orders, 
			$opt_inventory, 
			$opt_categories, 
			$opt_customers, 
			$opt_reports, 
			$opt_pages,
			$opt_options,
			$opt_user
		);
	
		foreach($nav_array as $item=>$options) {
			
			// Convert the $options to object class
			$options = (object) $options;

			// Display the notification (bubble) if set
			$notification = (isset($options->notification)) ? $options->notification : '';

			// IS this a single tab or a dropdown? If it's a single tab, 
			// it will have a url and permission of it's own.
			if (isset($options->url)) {
		
				// Does this user have permission? If true, show it...
				if ($options->permission && ($options->module != false || !isset($options->module))) {
					$html .= '<li class="'.$this->activemenu($options->activemenu).'"><a href="'.site_url($options->url).'">'.$item.'</a> '.$notification.'</li>';
				}
			
			} else {
				
				// Check how many options are available in this section. If there is 1 or more,
				// then show the options otherwise hide this entire section from view.
				$n = 0;
				foreach($options->menu as $key=>$val) {
					$val = (object) $val;
					if ($val->permission) {
						$n++;
					}
				}
			
				if ($n > 0) {
			
					// Set the nested css class
					$nested_class = (count($options->menu) > 0) ? 'nested' : '';
					
					// Insert the parent tab into the html
					$html .= '<li class="'.$this->activemenu($options->activemenu).$nested_class.'"><a class="nolink" href="#">'.$item.'</a> ' . $notification;
					
					// If there's some menu options, put them 
					// within a nested <ul>
					if (count($options->menu) > 0) {
						$html .= "<ul>$nl";
					}
					
					// Loop through all the child menu options
					foreach($options->menu as $label=>$option) {
						
						// Convert the options to a standard class object to keep the code a bit cleaner
						$option = (object) $option;
		
						// Does this user have permission? If true, show it...
						if ($option->permission && ($option->module == true || !isset($option->module))) {
						
							// Check if there's a separator (line with optional label) above this option
							// and insert it into the html if true
							if ($option->separator) {
								if ($option->label != null) {
									$html .= '<li class="nav-separator">'.$option->label.'</li>'.$nl;
								} else {
									$html .= '<li class="nav-separator lineonly">&nbsp;</li>'.$nl;
								}
							}
							
							$a_id = ($option->id != '') ? 'id="'.$option->id.'"' : '';
							$a_class = ($option->class != '') ? ' class="'.$option->class.'"' : '';
							
							if (isset($option->url)) {
							
								// Set the type of link this is
								if (isset($option->external_link)) {
									$link = $option->url;
									$target = ' target="'.$option->external_link.'"';
								} else {
									$link = site_url($option->url);
									$target = '';
								}
							
								// Continue to add the menu item to the html
								$html .= '<li><a '.$a_id.$a_class.' href="'.$link.'"'.$target.'>'.$label.'</a></li>'.$nl;
							
							} else {

								// Continue to add the menu item to the html
								$html .= '<li>'.$label.'</li>'.$nl;
								
							}
							
						}
						
					}
		
					// Close of the nested <ul> if it exists
					if (count($options->menu) > 0) {
						$html .= "</ul>$nl";
					}
					
					// Close off the parent tab li
					$html .= '</li>'.$nl;
					
				}
			
			}
			
		}

		// Return the complete nav html
		return $html;
		
	}

	#------------------------------------------------------
	# Return a css class to highlight the active menu item
	#------------------------------------------------------
	private function activemenu($criteria) {
	
		$shopit =& get_instance();
		
		$menuitems = explode('|', $criteria);
		
		foreach($menuitems as $menuitem) {
			if (trim($menuitem) == $shopit->uri->segment(1)) {
				return 'active ';
			}
		}	
	
	}

	#------------------------------------------------------
	# Gravatar
	# @param $email - The email address of user
	# @param $size = Default size of gravatar in pixels
	# @ return String containing the complete image tag
	#------------------------------------------------------
	function gravatar($email=null, $size=22, $attr=array()) {

		$html = "";
		$dropbox_img = 'https://dl.dropboxusercontent.com/s/jhwutdy4hkj33ox/gravatar.png';
		
		if (isset($email)) {
		
			// Set the default image when no gravatar is available
			// https://www.dropbox.com/s/jhwutdy4hkj33ox/gravatar.png
			$default = urlencode("$dropbox_img?v=".time());
			
			// Gravatar URL without the trailing slash
			$grav_url = 'http://www.gravatar.com/avatar';
			
			// Do the necessary to the email address
			$email = md5( strtolower(trim($email)) );
			
			// Create the full url
			$src = "$grav_url/$email?d=$default&s=$size";
			
		
		} else {
			$src = $dropbox_img;
		}

		// Create the img tag
		$html = '<img src="'.$src.'" alt="" width="'.$size.'" height="'.$size.'" border="0" class="gravatar valign" />';
		
		return $html;
		
	}

}