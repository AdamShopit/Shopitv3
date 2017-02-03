<?php

class Customers_model extends CI_Model {
	
	function Customers_model() {
		parent::__construct();
	}

	#------------------------------------------------------
	# Count customers
	# - retrieves total number of customers (i.e. num_rows)
	#------------------------------------------------------
	function countCustomers() {
		
		$this->db->select('order_id');

		//Start: Results filter
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
				$criteria_value = urldecode($parse_filter[1]);
				
				switch ($criteria_name) {
				
					case 's_customername':
						$this->db->like('CONCAT(billing_firstname," ",billing_surname)',$criteria_value);
						$this->db->or_like('customer_email', $criteria_value);
						break;
						
				}
			}
		}		
		//End: Results filter

		$this->db->group_by('billing_address1,billing_city');
		$query = $this->db->get('orders');
				
		if ($query->num_rows() > 0)
		{
			return $query->num_rows();
		}
	}

	#------------------------------------------------------
	# Get all customers
	# - retrieves customers with pagination
	#------------------------------------------------------
	function listAllCustomers($num,$offset) {
				
		$this->db->select('order_id,billing_title,billing_firstname,billing_surname,billing_city,customer_email,max(order_date) as last_order,(select count(t1.order_id) from orders t1 where (t1.order_status_id = "2") and t1.billing_address1 = t2.billing_address1 and t1.billing_city = t2.billing_city) as orders');
		$this->db->from('orders t2');

		//Start: Results filter
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
				$criteria_value = urldecode($parse_filter[1]);
				
				switch ($criteria_name) {
				
					case 's_customername':
						$this->db->like('CONCAT(billing_firstname," ",billing_surname)',$criteria_value);
						$this->db->or_like('customer_email', $criteria_value);
						break;
						
				}
			}
		}		
		//End: Results filter

		$this->db->limit($num,$offset);
		$this->db->group_by('billing_address1,billing_city');
		$this->db->order_by('orders desc');

		$query = $this->db->get();
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	}

	#------------------------------------------------------
	# Retrieve customer's details 
	#------------------------------------------------------
	function getCustomerDetails($id) {
	
		$this->db->where('order_id',$id);
		$query = $this->db->get('orders');
		
		if ($query->num_rows() > 0)
		{
			return $query->row();
		} 
	}

	#------------------------------------------------------
	# Get customer's orders
	#------------------------------------------------------
	function getOrders($billing_address1, $billing_city) {

		$this->db->select('*,(order_total + order_shipping + order_vat) as total');
		$this->db->where('billing_address1', $billing_address1);
		$this->db->where('billing_city', $billing_city);
		$this->db->where('refund', 0);
		$this->db->order_by('order_date','desc');
		
		$query = $this->db->get('orders');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	
	}

	#------------------------------------------------------
	# Export categories CSV file
	#------------------------------------------------------
	function exportCustomers() {

		$delimiter = '","';
		$newline = "\r\n";

		$this->db->select('concat(billing_title," ",billing_firstname," ",billing_surname) as billing_name, billing_address1, billing_address2, billing_city, billing_postcode, billing_country, customer_email, customer_phone, max(order_date) as last_ordered, (select count(t1.order_id) from orders t1 where (t1.order_status = "Completed" or t1.order_status="Dispatched") and t1.billing_address1 = t2.billing_address1 and t1.billing_city = t2.billing_city) as total_orders', false);
		$this->db->from('orders t2');
		$this->db->group_by('billing_address1,billing_city');
		$this->db->order_by('total_orders desc');
		$query = $this->db->get();

		$csv_column_titles = array("Customer Name", "Address 1", "Address 2", "City", "Postcode", "Country", "Email", "Phone", "Last Order", "Total Orders" );
		
		$csv_data_row = '"' . strtoupper( implode($delimiter,$csv_column_titles) ) . '"' . $newline;

		foreach ($query->result() as $customer) {

			$data = array(
					$customer->billing_name, $customer->billing_address1, $customer->billing_address2, $customer->billing_city, $customer->billing_postcode, $customer->billing_country, $customer->customer_email, $customer->customer_phone, $customer->last_ordered, $customer->total_orders
					); 
		
			$csv_data_row .= '"' . implode($delimiter,$data) . '"' . $newline;
		
		}
		
		return $csv_data_row;

	}

	#------------------------------------------------------
	# Get customers via ajax autocomplete
	#------------------------------------------------------
	function listCustomersLike($text, $limit=10) {
		
		$address = ($this->uri->segment(3) == '' || $this->uri->segment(3) == 'billing') ? 'billing' : 'delivery';
		
		$sql = "
				SELECT 
					account_id,
					".$address."_title as title, 
					".$address."_firstname as firstname, 
					".$address."_surname as surname, 
					".$address."_company as company, 
					".$address."_address1 as address1, 
					".$address."_address2 as address2, 
					".$address."_city as city, 
					".$address."_postcode as postcode, 
					".$address."_country as country, 
					CONCAT(".$address."_firstname, ' ', ".$address."_surname) as fullname,
					customer_email,
					customer_phone
				FROM orders
				WHERE (CONCAT(".$address."_firstname, ' ', ".$address."_surname)  LIKE '%$text%' OR ".$address."_company LIKE '%$text%' OR customer_email LIKE '%$text%')
				GROUP BY ".$address."_address1
				
				UNION 
				
				SELECT 
					account_id,
					account_title as title, 
					account_firstname as firstname, 
					account_surname as surname, 
					account_company as company, 
					account_address1 as address1, 
					account_address2 as address2, 
					account_city as city, 
					account_postcode as postcode, 
					account_country as country, 
					CONCAT(account_firstname, ' ', account_surname) as fullname,
					account_user as customer_email,
					account_phone as customer_phone
				FROM accounts
				WHERE (CONCAT(account_firstname, ' ', account_surname)  LIKE '%$text%' OR account_company LIKE '%$text%' OR account_user LIKE '%$text%')
				ORDER BY account_id DESC
				LIMIT $limit ";

		$query = $this->db->query($sql);
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	}

	#------------------------------------------------------
	# Count customers
	# - retrieves total number of customers (i.e. num_rows)
	#------------------------------------------------------
	function countAccounts() {
		
		$this->db->select('account_id');

		//Start: Results filter
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
				$criteria_value = urldecode($parse_filter[1]);
				
				switch ($criteria_name) {
				
					case 's_customername':
						$this->db->like('CONCAT(account_firstname, " ", account_surname)', $criteria_value);
						$this->db->or_like('account_user', $criteria_value);
						break;
						
				}
			}
		}		
		//End: Results filter

		$query = $this->db->get('accounts');
		return $query->num_rows();

	}

	#------------------------------------------------------
	# Get all customer accounts
	# - retrieves customers accounts with pagination
	#------------------------------------------------------
	function listAllAccounts($num, $offset) {
				
		$this->db->from('accounts');

		//Start: Results filter
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
				$criteria_value = urldecode($parse_filter[1]);
				
				switch ($criteria_name) {
				
					case 's_customername':
						$this->db->like('CONCAT(account_firstname, " ", account_surname)', $criteria_value);
						$this->db->or_like('account_user', $criteria_value);
						break;
						
				}
			}
		}		
		//End: Results filter

		$this->db->limit($num, $offset);
		$this->db->order_by('account_surname asc');

		$query = $this->db->get();
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	}

	#------------------------------------------------------
	# Export accounts CSV file
	#------------------------------------------------------
	function exportAccounts() {

		$delimiter = '","';
		$newline = "\r\n";

		$this->db->from('accounts');
		$this->db->order_by('account_id asc');
		$query = $this->db->get();

		$csv_column_titles = array("Account ID", "Email/Username", "Title", "First Name", "Last Name", "Company", "Address 1", "Address 2", "City", "Postcode", "Country", "Phone", "Marketing", "Last Login" );
		
		$csv_data_row = '"' . strtoupper( implode($delimiter,$csv_column_titles) ) . '"' . $newline;

		foreach ($query->result() as $customer) {

			$data = array(
					$customer->account_id, $customer->account_user, $customer->account_title, $customer->account_firstname, $customer->account_surname, $customer->account_company, $customer->account_address1, $customer->account_address2, $customer->account_city, $customer->account_postcode, $customer->account_country, $customer->account_phone, $customer->pref_newsletter, $customer->last_login
					); 
		
			$csv_data_row .= '"' . implode($delimiter,$data) . '"' . $newline;
		
		}
		
		return $csv_data_row;

	}

	#------------------------------------------------------
	# Get Account
	#------------------------------------------------------
	function getAccount($account_id) {
		
		$this->db->where('account_id', $account_id);
		$query = $this->db->get('accounts');
		
		if ($query->num_rows() > 0 ) {
			return $query->row();
		} else {
			return FALSE;
		}
		
	}

	#------------------------------------------------------
	# Update Account
	#------------------------------------------------------
	function updateAccount($account_id) {
		
		if ($account_id > 0) {
		
			$pref_newsletter = ($this->input->post('pref_newsletter') == 1) ? 1 : 0;
			
			$data = array(
				'account_user'		=> $this->input->post('account_user'),
				'account_title'		=> $this->input->post('account_title'),
				'account_firstname'	=> $this->input->post('account_firstname'),
				'account_surname'	=> $this->input->post('account_surname'),
				'account_company'	=> $this->input->post('account_company'),
				'account_address1'	=> $this->input->post('account_address1'),
				'account_address2'	=> $this->input->post('account_address2'),
				'account_city'		=> $this->input->post('account_city'),
				'account_postcode'	=> $this->input->post('account_postcode'),
				'account_country'	=> $this->input->post('account_country'),
				'account_phone'		=> $this->input->post('account_phone'),
				'pref_newsletter'	=> $pref_newsletter,
			);

			//Check if a new password has been added
			if (trim( $this->input->post('account_pass') ) != "") {
				$data['account_pass'] = do_hash(trim($this->input->post('account_pass')), 'md5');
			}
			
			// Manage the mailchimp subscription depending on the posted $pref_newsletter field
			if ($pref_newsletter == 0) {
				cmUnsubscribe($data['account_user']);
			} else {
				cmSubscribe(0, 1, $data['account_firstname'], $data['account_surname'], $data['account_user']);
			}
			
			$this->db->where('account_id', $account_id);
			$this->db->update('accounts', $data);

			// If chosen, sync any existing orders
			if ($this->input->post('sync_orders') >= 1) {
				$this->db->set('account_id', $account_id);
				$this->db->where( 'customer_email', $this->input->post('account_user') );
				$this->db->update('orders');
			}
			
		}
		
	}

	#------------------------------------------------------
	# Create Account
	#------------------------------------------------------
	function createAccount() {
		
		$pref_newsletter = ($this->input->post('pref_newsletter') == 1) ? 1 : 0;
		
		$data = array(
			'account_user'		=> $this->input->post('account_user'),
			'account_title'		=> $this->input->post('account_title'),
			'account_firstname'	=> $this->input->post('account_firstname'),
			'account_surname'	=> $this->input->post('account_surname'),
			'account_company'	=> $this->input->post('account_company'),
			'account_address1'	=> $this->input->post('account_address1'),
			'account_address2'	=> $this->input->post('account_address2'),
			'account_city'		=> $this->input->post('account_city'),
			'account_postcode'	=> $this->input->post('account_postcode'),
			'account_country'	=> $this->input->post('account_country'),
			'account_phone'		=> $this->input->post('account_phone'),
			'pref_newsletter'	=> $pref_newsletter,
		);

		//Check if a new password has been added
		if (trim( $this->input->post('account_pass') ) != "") {
			$data['account_pass'] = do_hash(trim($this->input->post('account_pass')), 'md5');
		}
		
		// Manage the mailchimp subscription depending on the posted $pref_newsletter field
		if ($pref_newsletter == 1) {
			cmSubscribe(0, 1, $data['account_firstname'], $data['account_surname'], $data['account_user']);
		}
		
		// Insert into database & get the new insert id
		$this->db->insert('accounts', $data);
		$account_id = $this->db->insert_id();
		
		// If chosen, sync any existing orders
		if ($this->input->post('sync_orders') >= 1) {
			$this->db->set('account_id', $account_id);
			$this->db->where( 'customer_email', $this->input->post('account_user') );
			$this->db->update('orders');
		}
		
	}
	
}