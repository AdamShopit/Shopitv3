<?php
class Filters extends CI_Controller {

	function Filters() {
		parent::__construct();
		
		$this->load->database();
		$this->load->model('category_model');
		$this->load->model('filters_model');
		$this->load->model('inventory_model');

		$this->load->model('settings_model');
		$this->settings_model->initConfig();

		/* Login check */
		$this->load->model('login_model');
		if(!$this->login_model->check_session()){
			redirect('/login');
		}
		/* End: Login check */

	}
	
	#------------------------------------------------------
	# Index - doesn't do anything
	#------------------------------------------------------
	function index() {
		redirect('dashboard');
	}

	#------------------------------------------------------
	# Show purchase message
	#------------------------------------------------------
	function purchase() {
		
		$data['title']	 	= 'Categories > Manage Filters';
		$data['page_title'] = 'Get Filters Module';
		
		$data['content'] 	= 'purchase/filters';
		$this->load->view('global/template', $data);
	}

	#------------------------------------------------------
	# Manage Filters
	#------------------------------------------------------
	function manage() {

		// Check module is installed
		if (!library_exists('filters')) {
			redirect('filters/purchase');
		} else {
			$this->permissions->access('can_module_filters');
		}

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
	
		$data['filter_groups'] = $this->filters_model->groups($this->uri->segment(3));
		$cat = $this->category_model->getThisCategory($this->uri->segment(3));
		
		$data['title']	 = 'Categories > Manage Filters';
		$data['form_open'] = '<form action="'.site_url('filters/save/'.$this->uri->segment(3)).$redirect->query_string.'" method="post" enctype="multipart/form-data">';
		$data['form_title'] = "Manage Filters for &quot;$cat->cat_name&quot;";
		$data['form_close'] = '</form>';
		$data['form_cancel_link'] = ($redirect->link != '') ? $redirect->link : site_url('category');

		$data['content'] = 'modules/filters/manage';
		$this->load->view('global/template', $data);
		
	}

	#------------------------------------------------------
	# Ajax HTML Templates
	#------------------------------------------------------
	// Create a new empty group
	function creategroup() {
		$this->permissions->access('can_module_filters');
		$this->load->view('modules/filters/create_group');
	}

	// Add a new option to an existing group
	function createoption() {
		$this->permissions->access('can_module_filters');
		$this->load->view('modules/filters/create_option');
	}
	
	// Re-load the filters on product page when 
	// category dropdown is changed
	function load() {
		$this->permissions->access('can_module_filters');

		// Get the filter groups for this products category
		$data['filter_groups'] = $this->filters_model->groups($this->input->post('cat_id'));

		// Get product data here so we can grab a few things before the template loads
		$data['item'] = $this->inventory_model->getProduct($this->input->post('product_id'));
		// Load the view
		$this->load->view('modules/filters/inventory_section', $data);
	}

	#------------------------------------------------------
	# Ajax Save's & Updates
	#------------------------------------------------------
	// Add a new filter option on add/edit inventory page
	function addoption() {

		$this->permissions->access('can_module_filters');
		
		// Set the filter data we need to send
		$filter_data = array(
			'group_id'  	=> $this->input->post('group_id'),
			'label'			=> $this->input->post('label'),
			'colour'		=> null,
			'filter_order' 	=> $this->input->post('filter_order'),
		);
		
		// Insert the new option and return the id
		$filter_id = $this->filters_model->createOption($filter_data);
		
		// Set the field name
		$filter_field_name = "filter_$filter_id";

		// Return the html to append to the form
		$html = '<label for="'.$filter_field_name.'"><input type="checkbox" name="'.$filter_field_name.'" id="'.$filter_field_name.'" value="1" /> '.$this->input->post('label').'</label>';

		echo $html;

	}
	
	// Delete filter option
	function deleteoption() {
		$this->permissions->access('can_module_filters');
		$this->filters_model->deleteOption($this->input->post('filter_id'));
	}
	
	// Delete filter group and all its options
	function deletegroup() {
		$this->permissions->access('can_module_filters');
		$this->filters_model->deleteGroup($this->input->post('group_id'));
	}

	#------------------------------------------------------
	# Save Filters
	#------------------------------------------------------
	function save() {

		// Check module is installed
		if (!library_exists('filters')) {
			redirect('filters/purchase');
		} else {
			$this->permissions->access('can_module_filters');
		}

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
		
		// Some settings/vars
		$g = 0;
		
		// Loop through the array
		foreach($_POST['groups'] as $group) {
			
			$g++;
			$f = 0;

			// Set the group data we need to send
			$group_data = array(
				'cat_id' 	  => $this->uri->segment(3),
				'label'	 	  => $group['group_label'],
				'type'	 	  => $group['group_type'],
				'group_order' => $g,
			);
			
			// If group_id is null then we know this is a new group
			// that needs creating, else update the existing group
			if (empty($group['group_id'])) {
				// Create the new group and return the id
				$group_id = $this->filters_model->createGroup($group_data);
			} else {
				// Update the existing group and at the same time, we'll create
				// a more useful $group_id variable
				$group_id = $group['group_id'];
				$this->filters_model->updateGroup($group_id, $group_data);
			}
			
			// Now we'll look at the filter options
			if (!empty($group['filters']) && $group_data['label'] != "") {
				foreach($group['filters'] as $filter) {
					
					$f++;
					
					// Set the filter data we need to send
					$filter_data = array(
						'group_id'  	=> $group_id,
						'label'			=> $filter['label'],
						'colour'		=> $filter['colour'],
						'filter_order' 	=> $f,
					);
					
					// If the filter_id is null then we know this is a new option
					// we need to insert, otherwise we should update the one
					if (empty($filter['id'])) {
						// Insert the new option and return the id
						$filter_id = $this->filters_model->createOption($filter_data);
					} else {
						// Update the existing option and at the same time create
						// a more useful $filter_if variable
						$filter_id = $filter['id'];
						$this->filters_model->updateOption($filter_id, $filter_data);
					}
					
				}
			}
			
		}

		if ($redirect->link != "") {
			redirect($redirect->link . '?shopit-notice=Category filters saved.');
		} else {
			// Redirect back to page and tell user things have saved
			$this->session->set_flashdata('notice','Filters saved.');	
			redirect('filters/manage/'.$this->uri->segment(3));
		}
		
	}

}