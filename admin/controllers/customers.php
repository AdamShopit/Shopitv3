<?php

class Customers extends CI_Controller {
	
	function Customers() {
		parent::__construct();

		$this->load->database();

		$this->load->model('settings_model');
		$this->settings_model->initConfig();
		
		$this->load->model('customers_model');
		$this->load->model('orders_model');
		$this->load->model('shipping_model');
		$this->load->library('pagination');	
		$this->load->library('email');
		$this->load->helper('security');	
		$this->load->helper('download');	

		/* Login check */
		$this->load->model('login_model');
		if(!$this->login_model->check_session()){
			redirect('/login');
		}
		/* End: Login check */

	}
	
	#------------------------------------------------------
	# Display list of customers
	#------------------------------------------------------
	function index() {

		$this->permissions->access('can_access_customer_list');

		// Capture the current url so we can return to this 
		// page after an add/edit product page is accessed
		$data['redirect'] = redirect_create();

		//For pagination (including filter)	
		if ($this->input->post('filter') == 'true') {
			$config['url_format'] = site_url('/customers/index/{offset}/'.http_build_query($_POST));
		} elseif ($this->uri->segment(4) != ''){
			$config['url_format'] = site_url('/customers/index/{offset}/'.$this->uri->segment(4));
		} else {
			$config['base_url'] = site_url('customers/index/');
		}
		
		$query = $this->customers_model->countCustomers();
		
		$config['total_rows'] 	= $query;
		$config['uri_segment'] 	= 3;
		$config['per_page'] = 25;
				
		$this->pagination->initialize($config);
		
		$data['results_total'] = $config['total_rows'];
		//End of pagination

		//Start: Tell us which filter checkboxes/dropdowns are selected
		//so we can reselect them again
		if(!empty($_POST)) {
			$segment = http_build_query($_POST);
		} else {
			$segment = $this->uri->segment(4);
		}

		if ($segment != '') {
			$query_string = explode('&',$segment);
			
			foreach ($query_string as $filter) {
				$parse_filter = explode('=',$filter);
				$criteria_name = $parse_filter[0];
				$criteria_value = $parse_filter[1];
				
				$data[$criteria_name] = urldecode($criteria_value);
			}
		}
		//End

		$data['title']	 = 'Customers';
		$data['customers'] = $this->customers_model->listAllCustomers($config['per_page'],$this->uri->segment(3));
		
		$data['content'] = 'customers/customers_list';

		$this->load->view('global/template',$data);
	}

	#------------------------------------------------------
	# View subscription details
	#------------------------------------------------------
	function view() {

		$this->permissions->access('can_access_customer_list');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
		$data['redirect_link'] = $redirect->link;
		$data['redirect_query_string'] = $redirect->query_string;

		// Create a redirect to return back to this customer
		$data['redirect'] = redirect_create();
	
		$data['customer']  = $this->customers_model->getCustomerDetails($this->uri->segment(3));
		$data['orders']    = $this->customers_model->getOrders($data['customer']->billing_address1,$data['customer']->billing_city);
		$data['templates'] = $this->orders_model->returnTemplates();
		$data['title']	   = 'View Customer Details';
		$data['content']   = 'customers/customers_view';

		$this->load->view('global/template',$data);

	}

	#------------------------------------------------------
	# Customer search/filter
	#------------------------------------------------------
	function filter() {

		$this->permissions->access('can_access_customer_list');

		if ($_POST['s'] != ''):
		
			$keywords = $_POST['s'];
		
		else:
			
			$keywords = $this->uri->segment(3);
		endif;

		//For pagination
		$config['base_url'] = base_url().'index.php/customers/filter/'.$keywords;		
		
		$query = $this->customers_model->countCustomerSearch($keywords);
		
		$config['total_rows'] 	= $query;
		$config['uri_segment'] 	= 4;
		$config['per_page'] 	= 10;
		$config['full_tag_open']= '<p id="pagination"><strong>Page:</strong> ';
		$config['full_tag_close']= '</p>';
		$config['first_link'] 	= '&laquo;';
		$config['first_tag_open'] = '<span class="first_link">';
		$config['first_tag_close'] = '</span>';
		$config['last_link'] 	= '&raquo;';
		$config['last_tag_open'] = '<span class="last_link">';
		$config['last_tag_close'] = '</span>';
		$config['next_link'] 	= '&rsaquo;';
		$config['next_tag_open'] = '<span class="next_link">';
		$config['next_tag_close'] = '</span>';
		$config['prev_link'] 	= '&lsaquo;';
		$config['prev_tag_open'] = '<span class="prev_link">';
		$config['prev_tag_close'] = '</span>';
		$config['cur_tag_open'] = '<span class="current_link"><strong>';
		$config['cur_tag_close'] = '</strong></span>';
		$config['num_tag_open'] = '<span class="digit_link">';
		$config['num_tag_close'] = '</span>';
		$config['num_links'] = 4;
		
		$this->pagination->initialize($config);
		
		$data['results_total'] = $config['total_rows'];
		//End of pagination

		$data['title']	 = 'Customers';
		$data['customers'] = $this->customers_model->customerSearch($keywords,$config['per_page'],$this->uri->segment(4));
		
		if ($data['customers'] > 0):
		$data['content'] = 'customers/customers_list';
		else:
		$data['content'] = 'customers/customers_empty';
		endif;

		$this->load->view('global/template',$data);
	
	}

	#------------------------------------------------------
	# Export CSV
	#------------------------------------------------------
	function export() {
		$this->permissions->access('can_access_customer_exports');
		$data = $this->customers_model->exportCustomers();
		$filename = 'customers_'.date('Y-m-d').'.csv';
		force_download($filename, $data);
		break;
	}

	#------------------------------------------------------
	# Customers lookup
	#------------------------------------------------------
	function lookup() {

		if ($this->input->post('fullname') != ''):
			$data['customers'] = $this->customers_model->listCustomersLike($this->input->post('fullname'), 25);
		endif;		
	
		$data['title'] = "Customer Lookup";
	
		$this->load->view('orders/orders_customer_lookup', $data);
	}

	#------------------------------------------------------
	# Display list of customer accounts
	# - Alphabetical order
	#------------------------------------------------------
	function accounts() {

		$this->permissions->access('can_module_myaccount');

		// Capture the current url so we can return to this 
		// page after an add/edit product page is accessed
		$data['redirect'] = redirect_create();

		//For pagination (including filter)	
		if ($this->input->post('filter') == 'true') {
			$config['url_format'] = site_url('/customers/accounts/{offset}/'.http_build_query($_POST));
		} elseif ($this->uri->segment(4) != ''){
			$config['url_format'] = site_url('/customers/accounts/{offset}/'.$this->uri->segment(4));
		} else {
			$config['base_url'] = site_url('customers/accounts/');
		}
		
		$query = $this->customers_model->countAccounts();
		
		$config['total_rows'] 	= $query;
		$config['uri_segment'] 	= 3;
		$config['per_page'] 	= 25;
				
		$this->pagination->initialize($config);
		
		$data['results_total'] = $config['total_rows'];
		//End of pagination

		//Start: Tell us which filter checkboxes/dropdowns are selected
		//so we can reselect them again
		if(!empty($_POST)) {
			$segment = http_build_query($_POST);
		} else {
			$segment = $this->uri->segment(4);
		}

		if ($segment != '') {
			$query_string = explode('&',$segment);
			
			foreach ($query_string as $filter) {
				$parse_filter = explode('=',$filter);
				$criteria_name = $parse_filter[0];
				$criteria_value = $parse_filter[1];
				
				$data[$criteria_name] = urldecode($criteria_value);
			}
		}
		//End

		$data['title']	 = 'Customers';
		$data['customers'] = $this->customers_model->listAllAccounts($config['per_page'],$this->uri->segment(3));
		
		$data['content'] = 'customers/accounts';

		$this->load->view('global/template', $data);
	}

	#------------------------------------------------------
	# Export CSV
	#------------------------------------------------------
	function exportaccounts() {
		$this->permissions->access('can_module_myaccount');
		$data = $this->customers_model->exportAccounts();
		$filename = 'customers_'.date('Y-m-d').'.csv';
		force_download($filename, $data);
		break;
	}

	#------------------------------------------------------
	# Edit customer account
	#------------------------------------------------------
	function edit() {

		$this->permissions->access('can_module_myaccount');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
		$data['redirect_link'] = $redirect->link;
		$data['redirect_query_string'] = $redirect->query_string;
	
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('account_title', 'Title', 'trim');
		$this->form_validation->set_rules('account_firstname', 'First Name', 'trim|required');
		$this->form_validation->set_rules('account_surname', 'Surname', 'trim|required');
		$this->form_validation->set_rules('account_company', 'Company', 'trim');
		$this->form_validation->set_rules('account_address1', 'Address Line 1', 'trim|required');
		$this->form_validation->set_rules('account_address2', 'Address Line 2', 'trim');
		$this->form_validation->set_rules('account_city', 'City', 'trim|required');
		$this->form_validation->set_rules('account_postcode', 'Postcode', 'trim|required');
		$this->form_validation->set_rules('account_country', 'Country', 'trim|required');
		$this->form_validation->set_rules('account_phone', 'Title', 'trim');
		$this->form_validation->set_rules('account_user', 'Username/Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('pref_newsletter', 'Marketing', 'trim');

		if ($this->form_validation->run() == FALSE) :

			$data['customer']  = $this->customers_model->getAccount($this->uri->segment(3));
			$data['countries'] = $this->shipping_model->getISOCountries();
				
			$data['title']= 'Customers > Edit Account';

			$data['form_open'] = '<form action="'.site_url('customers/edit/'.$this->uri->segment(3)).$redirect->query_string.'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Edit Customer Account';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = site_url('customers/accounts'.$redirect->query_string);

			$data['content'] = 'customers/account_edit';
			$this->load->view('global/template', $data);
			
		else:
			$this->customers_model->updateAccount($this->input->post('account_id'));
			$this->session->set_flashdata('notice','Customer account updated. <a href="'.$redirect->link.'">Back to previous page</a>');		
			redirect('customers/edit/'.$this->uri->segment(3).$redirect->query_string);
		endif;
		
	}

	#------------------------------------------------------
	# Create customer account
	#------------------------------------------------------
	function create() {

		$this->permissions->access('can_module_myaccount');

		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('account_title', 'Title', 'trim');
		$this->form_validation->set_rules('account_firstname', 'First Name', 'trim|required');
		$this->form_validation->set_rules('account_surname', 'Surname', 'trim|required');
		$this->form_validation->set_rules('account_company', 'Company', 'trim');
		$this->form_validation->set_rules('account_address1', 'Address Line 1', 'trim|required');
		$this->form_validation->set_rules('account_address2', 'Address Line 2', 'trim');
		$this->form_validation->set_rules('account_city', 'City', 'trim|required');
		$this->form_validation->set_rules('account_postcode', 'Postcode', 'trim|required');
		$this->form_validation->set_rules('account_country', 'Country', 'trim|required');
		$this->form_validation->set_rules('account_phone', 'Title', 'trim');
		$this->form_validation->set_rules('account_user', 'Username/Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('account_pass', 'password', 'trim|required');
		$this->form_validation->set_rules('pref_newsletter', 'Marketing', 'trim');
		$this->form_validation->set_rules('notifyuser');
		$this->form_validation->set_rules('sync_orders');

		if ($this->form_validation->run() == FALSE) :

			$data['countries'] = $this->shipping_model->getISOCountries();
				
			$data['title']= 'Customers > Create Account';

			$data['form_open'] = '<form action="'.site_url('customers/create').'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Create Customer Account';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = site_url('customers/accounts');

			$data['content'] = 'customers/account_edit';
			$this->load->view('global/template', $data);
			
		else:
			
			// Create the new account
			$this->customers_model->createAccount($this->input->post('account_id'));
			
			// Email the customer
			if ($this->input->post('notifyuser') == "yes") {
			
				$email = array(
					'email' 	=> $this->input->post('account_user'),
					'firstname' => $this->input->post('account_firstname'),
					'password'	=> $this->input->post('account_pass'),
				);
				
				$config['mailtype'] = 'html';
				$this->email->initialize($config);
				
				$this->email->from($this->config->item('store_email'), $this->config->item('store_name'));
				$this->email->to($this->input->post('account_user'));
				
				$this->email->subject('Your new account details');
				$msg = $this->load->view('customers/account_email', $email, true);
				
				$this->email->message($msg);
				$this->email->send();
				
			}
			
			// Send update notice to screen
			$this->session->set_flashdata('notice', 'Customer account created.');
			redirect('customers/accounts');
		endif;
		
	}

	#------------------------------------------------------
	# Check if user email exists
	# - returns true or false
	#------------------------------------------------------
	function checkuser() {
	
		$email = $this->input->post('Email');

		$this->db->where('account_user', $email);
		$query = $this->db->get('accounts');
		
		if ($query->num_rows() > 0) {
			$message = "<label>&nbsp;</label><span class='error'>Oops! Looks like this user has already registered an account.</span>";
			$failed = true;
		} else {
			$message = "";
			$failed = false;
		}

		print json_encode( 
			array (
				'message' => $message,
				'failed'  => $failed,
			) 
		);
	
	}
	
}