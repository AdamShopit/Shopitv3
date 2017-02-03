<?php

class Shipping extends CI_Controller {

	function Shipping() {
		parent::__construct();
		
		$this->load->database();

		$this->load->model('settings_model');
		$this->settings_model->initConfig();
		
		$this->load->model('shipping_model');
		$this->load->model('category_model');

		/* Login check */
		$this->load->model('login_model');
		if(!$this->login_model->check_session()){
			redirect('/login');
		}
		/* End: Login check */
		
	}
	
	#------------------------------------------------------
	# Display list of shipping rules
	#------------------------------------------------------
	function index() {	

		$this->permissions->access('can_access_shipping');

		$data['title']	 = 'Shipping Rules';
		$data['shippingrules'] = $this->shipping_model->getRules($this->input->post('display_country'));
		$data['shippingcountries'] = $this->shipping_model->getCountries();
		$data['iso_countries'] = $this->shipping_model->getISOCountries(true);
		
		$data['content'] = 'orders/shipping_rules';
		$this->load->view('global/template',$data);
	}
	

	#------------------------------------------------------
	# Display shipping rules form
	#------------------------------------------------------
	function create() {

		$this->permissions->access('can_access_shipping');

		$data['title']	 = 'Create Shipping Rule';
		$data['countries'] = $this->shipping_model->getCountries();
		$data['categories'] = $this->category_model->getAvailableCategories();

		$data['form_open'] = '<form action="'.site_url('shipping/createrule').'" method="post" enctype="multipart/form-data" >';
		$data['form_title'] = 'Create new shipping rule';
		$data['form_close'] = '</form>';
		$data['form_cancel_link'] = site_url('shipping');

		$data['content'] = 'orders/shipping_rules_create';
		$this->load->view('global/template',$data);
	}

	#------------------------------------------------------
	# Creat shipping rule i.e. add to database
	#------------------------------------------------------
	function createrule() {

		$this->permissions->access('can_access_shipping');

		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('rule_name', 'Rule name', 'required');
		$this->form_validation->set_rules('country');
		$this->form_validation->set_rules('criteria');
		$this->form_validation->set_rules('operation');
		$this->form_validation->set_rules('value', 'Value', 'required|numeric');
		if ($this->input->post('operation') == 'between') {
		$this->form_validation->set_rules('value2', 'Value2', 'required|numeric');
		} else {
		$this->form_validation->set_rules('value2', 'Value2', 'numeric');
		}
		$this->form_validation->set_rules('shipping','Shipping cost','required|numeric');

		if ($this->form_validation->run() == FALSE) :
		
			$data['title']	 = 'Create Shipping Rule';
			$data['countries'] = $this->shipping_model->getCountries();
			$data['categories'] = $this->category_model->getAvailableCategories();
			$data['form_open'] = '<form action="'.site_url('shipping/createrule').'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Create new shipping rule';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = site_url('shipping');
			$data['content'] = 'orders/shipping_rules_create';
			$this->load->view('global/template',$data);
			
		else:

			if ($this->input->post('value') != '') {
				$this->session->set_flashdata('notice','Shipping rule has been created.');
				$this->shipping_model->createRule();
			}
			redirect('/shipping');

		endif;
	}

	#------------------------------------------------------
	# Edit shipping rule
	#------------------------------------------------------
	function edit() {

		$this->permissions->access('can_access_shipping');

		$data['title']	 = 'Edit Shipping Rule';
		$data['countries'] = $this->shipping_model->getCountries();
		$data['categories'] = $this->category_model->getAvailableCategories();
		$data['shipping'] = $this->shipping_model->getRule($this->uri->segment(3));

		$data['form_open'] = '<form action="'.site_url('shipping/update').'" method="post" enctype="multipart/form-data" >';
		$data['form_title'] = 'Edit shipping rule';
		$data['form_close'] = '</form>';
		$data['form_cancel_link'] = site_url('shipping');

		$data['content'] = 'orders/shipping_rules_create';
		$this->load->view('global/template',$data);
	}


	#------------------------------------------------------
	# Update shipping rule
	#------------------------------------------------------
	function update() {

		$this->permissions->access('can_access_shipping');
	
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('rule_name', 'Rule name', 'required');
		$this->form_validation->set_rules('country');
		$this->form_validation->set_rules('criteria');
		$this->form_validation->set_rules('operation');
		$this->form_validation->set_rules('value', 'Value', 'required|numeric');
		if ($this->input->post('operation') == 'between') {
		$this->form_validation->set_rules('value2', 'Value2', 'required|numeric');
		} else {
		$this->form_validation->set_rules('value2', 'Value2', 'numeric');
		}
		$this->form_validation->set_rules('shipping','Shipping cost','required|numeric');

		if ($this->form_validation->run() == FALSE) :
		
			$data['title']	 = 'Edit Shipping Rule';
			$data['countries'] = $this->shipping_model->getCountries();
			$data['categories'] = $this->category_model->getAvailableCategories();
			$data['shipping'] = $this->shipping_model->getRule($this->uri->segment(3));
	
			$data['form_open'] = '<form action="'.site_url('shipping/update/'.$this->uri->segment(3)).'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Edit shipping rule';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = site_url('shipping');
			$data['content'] = 'orders/shipping_rules_create';
			$this->load->view('global/template',$data);
			
		else:
			$this->session->set_flashdata('notice','Shipping rule has been updated.');		
			$this->shipping_model->updateRule($this->input->post('rule_id'));
			redirect('shipping');
		endif;
	
	}

	#------------------------------------------------------
	# Delete shipping rule (ajax)
	#------------------------------------------------------
	function delete() {

		$this->permissions->access('can_access_shipping');
		
		if ($this->uri->segment(3) > 0):
			$this->shipping_model->deleteRule($this->uri->segment(3));
		endif;
		
	}

	#------------------------------------------------------
	# Add country to shipping list
	#------------------------------------------------------
	function addcountry() {

		$this->permissions->access('can_access_shipping');
		
		$this->shipping_model->addCountry();
				
		print '<tr class="table-row highlight">';
		print '<td>'.$this->input->post('country_name').'</td>';
		print '<td>&nbsp;</td>';
		print '</tr>';

	}

	#------------------------------------------------------
	# Delete country from shipping list
	#------------------------------------------------------
	function deletecountry() {
		$this->permissions->access('can_access_shipping');
		$this->shipping_model->deleteCountry($this->uri->segment(3));
	}

	#------------------------------------------------------
	# Shipping rules lookup
	#------------------------------------------------------
	function lookup() {
		
		$data['total_price']  = $this->uri->segment(4);
		$data['total_weight'] = $this->uri->segment(5);
		
		//Convert the cat_ids into a useful array to pass to the view
		$cat_ids = $this->uri->segment(6);
		if ($cat_ids != "") {
			$data['cat_ids'] = explode(',', $cat_ids);
		} else {
			$data['cat_ids'] = array();
		}
		
		$shipping_country = urldecode($this->uri->segment(3));
		$data['shipping'] = $this->shipping_model->getCountryShipping($shipping_country);
	

		$this->load->view('orders/orders_shipping_options', $data);
	}

	#------------------------------------------------------
	# Criteria dropdown loads (Ajax)
	#------------------------------------------------------
	function load_categories() {
		$data['categories'] = $this->category_model->getAvailableCategories();
		$this->load->view('orders/shipping_rules_category', $data);
	}
	
	function load_criteria() {
		$this->load->view('orders/shipping_rules_criteria');
	}
	
	#------------------------------------------------------
	# Manage VAT per country
	#------------------------------------------------------
	function vat() {

		$this->permissions->access('can_access_shipping');
	
		$data['title']	 = 'Manage VAT Per Country';
		$data['countries'] = $this->shipping_model->getISOCountries();
		
		$data['content'] = 'orders/shipping_vat';
		$this->load->view('global/template',$data);
	
	}
	
	
	//Update the vat setting via ajax
	function updatevat() {

		$this->permissions->access('can_access_shipping');
			
		$data = array(
					'vat_exempt' => $this->input->post('vat_exempt'),
				);
		
		$this->db->where('id', $this->input->post('id'));
		$this->db->update('iso_countries', $data);
		
	}

}