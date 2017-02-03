<?php
class Import extends CI_Controller {
	
	function Import() {
	
		parent::__construct();

		// Load the libraries and helpers
		$this->load->database();
		$this->load->library('importtool');

		$this->load->model('settings_model');
		$this->settings_model->initConfig();
		
		/* Login check */
		$this->load->model('login_model');
		if(!$this->login_model->check_session()){
			redirect('/login');
		}
		/* End: Login check */

		// Not accessible to non-admin users
		$this->permissions->access('can_access_admin_tools');

	}
	
	function index() {
	
	}

	#------------------------------------------------------
	# Import new categories
	#------------------------------------------------------
	function categories() {
		
		// Define the database columns to match
		$columns = array(
			'Cat ID' 			 => 'cat_id',
			'Parent Category ID' => 'cat_father_id',
			'Title'		 		 => 'cat_name',
			'Custom Heading' 	 => 'cat_custom_heading',
			'Meta Title'		 => 'cat_meta_title',
			'Meta Description'	 => 'cat_meta_description',
			'Meta Keywords'		 => 'cat_meta_keywords',
			'Slug/URL'			 => 'cat_slug',
			'Excerpt'			 => 'cat_excerpt',
			'Description'		 => 'cat_desc',
			'Hidden?'			 => 'cat_hide',
			'Sort Order'		 => 'cat_order',
			'Category Image'	 => 'cat_image',
		);

		// And put everything together
		$settings = array(
			'table' 	=> 'category',
			'columns' 	=> $columns,
			'update'	=> FALSE,
		);

		// Intialise our library vars
		$this->importtool->initialize($settings);

		// Some view settings
		$data['title'] = 'Import Categories';
		
		switch ($this->uri->segment(3)) {
			
			// Display the upload form
			default:
				
				// Load view
				$data['form_title'] = "Upload Categories";
				$data['form_url']	= site_url('import/categories/match');
				$data['content'] 	= "options/import_file";
				$this->load->view('global/template', $data);
				break;
			
			// Get uploaded file and match columns
			case "match":
				
				// We need to upload the file to the server as temp files are no good
				$data['file'] = $this->importtool->upload_file('file_upload');
				
				if ($data['file'] != FALSE) {
				
					// Get the columns, matched and all so we can send to the view
					$data['columns'] = $this->importtool->match_columns($data['file']);
	
					// Load view
					$data['form_title'] 		= "Match Category Columns";
					$data['form_open'] 			= '<form action="'.site_url('import/categories/preview').'" method="post" enctype="multipart/form-data">';
					$data['form_close'] 		= '</form>';
					$data['form_submit_label'] 	= 'Preview Import';
					$data['form_cancel_link'] 	= site_url('import/categories');
					$data['content'] 			= "options/import_match";
					$this->load->view('global/template', $data);
				
				} else {
					
					$this->session->set_flashdata('alert', 'Please select a valid CSV file!');
					redirect('import/categories');
					
				}
				break;
								
			// Preview uploaded data
			case "preview":

				// Return the file path as we need to delete it 
				// from the server (space-saving!)
				$data['file'] = $this->input->post('file_upload');

				// Get a preview of the data
				$data['preview'] = $this->importtool->preview($this->input->post('file_upload'));

				// Load view
				$data['form_title'] 		= "Data Preview";
				$data['form_open'] 			= '<form action="'.site_url('import/categories/do').'" method="post" enctype="multipart/form-data">';
				$data['form_close'] 		= '</form>';
				$data['form_submit_label'] 	= "Let's Import!";
				$data['form_cancel_link'] 	= site_url('import/categories');
				$data['content'] 			= "options/import_preview";
				$this->load->view('global/template', $data);
				break;
				
			case "do";
				
				// Do the data import and redirect
				$this->importtool->do_insert($_POST['row'], 'category');
				break;
		
		}
		
	}

	#------------------------------------------------------
	# Import new inventory items into the inventory table
	#------------------------------------------------------
	function inventory() {
		
		// Define the columns we need to match
		$columns = array(
			'Product ID'	=> 'product_id',
			'Parent ID'		=> 'parent_id',
			'Product Type'	=> 'product_type',
			'Category ID'	=> 'cat_id',
			'Product Order' => 'product_order',
			'Product No/SKU'=> 'product_no',
			'Product Name'	=> 'product_name',
			'Product Tags'	=> 'product_tags',
			'Excerpt'		=> 'product_excerpt',
			'Description'	=> 'product_description',
			'Image'			=> 'product_image', // Not always required
			'File'			=> 'product_file',
			'Slug/URL'		=> 'product_slug', 
			'Product Brand'	=> 'product_brand',
			'Brand Slug'	=> 'product_brand_slug',
			'Channel 1'		=> 'channel_1',
			'Cost Price'	=> 'product_costprice',
			'Price'			=> 'product_price',
			'Sale Price'	=> 'product_saleprice',
			'Weight'		=> 'product_weight',
			'Stock Level'	=> 'location_1',
			'EAN'			=> 'product_ean',
			'MPN'			=> 'product_mpn',
			'UPC'			=> 'product_upc',
			'Supplier Code'	=> 'supplier_code',
			'Meta Title' 	=> 'product_meta_title',
			'Custom Heading'=> 'product_custom_heading',
			'Meta Description' => 'product_meta_description',
			'Meta Keywords' => 'product_meta_keywords',
			'Disabled'		=> 'product_disabled',
			'Condition'		=> 'product_condition',
			'Archived'		=> 'archived',
		);	

		// And put everything together
		$settings = array(
			'table' 	=> 'inventory',
			'columns' 	=> $columns,
			'update'	=> FALSE,
		);

		// Intialise our library vars
		$this->importtool->initialize($settings);

		// Some view settings
		$data['title'] = 'Import Inventory';
		
		switch ($this->uri->segment(3)) {
			
			// Display the upload form
			default:
				
				// Load view
				$data['form_title'] = "Upload Inventory";
				$data['form_url']	= site_url('import/inventory/match');
				$data['content'] 	= "options/import_file";
				$this->load->view('global/template', $data);
				break;
			
			// Get uploaded file and match columns
			case "match":
				
				// We need to upload the file to the server as temp files are no good
				$data['file'] = $this->importtool->upload_file('file_upload');
				
				if ($data['file'] != FALSE) {
				
					// Get the columns, matched and all so we can send to the view
					$data['columns'] = $this->importtool->match_columns($data['file']);
	
					// Load view
					$data['form_title'] 		= "Match Inventory Columns";
					$data['form_open'] 			= '<form action="'.site_url('import/inventory/preview').'" method="post" enctype="multipart/form-data">';
					$data['form_close'] 		= '</form>';
					$data['form_submit_label'] 	= 'Preview Import';
					$data['form_cancel_link'] 	= site_url('import/inventory');
					$data['content'] 			= "options/import_match";
					$this->load->view('global/template', $data);
				
				} else {
					
					$this->session->set_flashdata('alert', 'Please select a valid CSV file!');
					redirect('import/inventory');
					
				}
				break;
								
			// Preview uploaded data
			case "preview":

				// Return the file path as we need to delete it 
				// from the server (space-saving!)
				$data['file'] = $this->input->post('file_upload');

				// Get a preview of the data
				$data['preview'] = $this->importtool->preview($this->input->post('file_upload'));

				// Load view
				$data['form_title'] 		= "Data Preview";
				$data['form_open'] 			= '<form action="'.site_url('import/inventory/do').'" method="post" enctype="multipart/form-data">';
				$data['form_close'] 		= '</form>';
				$data['form_submit_label'] 	= "Let's Import!";
				$data['form_cancel_link'] 	= site_url('import/inventory');
				$data['content'] 			= "options/import_preview";
				$this->load->view('global/template', $data);
				break;
				
			case "do";
				
				// Do the data import and redirect
				$this->importtool->do_insert($_POST['row'], 'inventory', 'inventory');
				break;
		
		}
		
	}

	#------------------------------------------------------
	# Update inventory items in the inventory table
	#------------------------------------------------------
	function updateinventory() {
		
		// Define the columns we need to match
		$columns = array(
			'Product ID'	=> 'product_id',
			'Parent ID'		=> 'parent_id',
			'Product Type'	=> 'product_type',
			'Category ID'	=> 'cat_id',
			'Product Order' => 'product_order',
			'Product No/SKU'=> 'product_no',
			'Product Name'	=> 'product_name',
			'Product Tags'	=> 'product_tags',
			'Excerpt'		=> 'product_excerpt',
			'Description'	=> 'product_description',
			'Image'			=> 'product_image',
			'File'			=> 'product_file',
			'Slug/URL'		=> 'product_slug',
			'Product Brand'	=> 'product_brand',
			'Brand Slug'	=> 'product_brand_slug',
			'Channel 1'		=> 'channel_1',
			'Cost Price'	=> 'product_costprice',
			'Price'			=> 'product_price',
			'Sale Price'	=> 'product_saleprice',
			'Weight'		=> 'product_weight',
			'Stock Level'	=> 'location_1',
			'EAN'			=> 'product_ean',
			'MPN'			=> 'product_mpn',
			'UPC'			=> 'product_upc',
			'Supplier Code'	=> 'supplier_code',
			'Meta Title' 	=> 'product_meta_title',
			'Custom Heading'=> 'product_custom_heading',
			'Meta Description' => 'product_meta_description',
			'Meta Keywords' => 'product_meta_keywords',
			'Disabled'		=> 'product_disabled',
			'Condition'		=> 'product_condition',
			'Date Added'	=> 'date_added',
			'Archived'		=> 'archived',
		);	

		// And put everything together
		$settings = array(
			'table' 	=> 'inventory',
			'columns' 	=> $columns,
			'update'	=> TRUE, // Used to add the extra unique id selection in the match columns step
		);

		// Intialise our library vars
		$this->importtool->initialize($settings);

		// Some view settings
		$data['title'] = 'Import Inventory';
		
		switch ($this->uri->segment(3)) {
			
			// Display the upload form
			default:
				
				// Load view
				$data['form_title'] = "Update Inventory";
				$data['form_url']	= site_url('import/updateinventory/match');
				$data['content'] 	= "options/import_file";
				$this->load->view('global/template', $data);
				break;
			
			// Get uploaded file and match columns
			case "match":
				
				// We need to upload the file to the server as temp files are no good
				$data['file'] = $this->importtool->upload_file('file_upload');
				
				if ($data['file'] != FALSE) {
				
					// Get the columns, matched and all so we can send to the view
					$data['columns'] = $this->importtool->match_columns($data['file']);
	
					// Load view
					$data['form_title'] 		= "Match Inventory Columns";
					$data['form_open'] 			= '<form action="'.site_url('import/updateinventory/preview').'" method="post" enctype="multipart/form-data">';
					$data['form_close'] 		= '</form>';
					$data['form_submit_label'] 	= 'Preview Updates';
					$data['form_cancel_link'] 	= site_url('import/updateinventory');
					$data['content'] 			= "options/import_match";
					$this->load->view('global/template', $data);
				
				} else {
					
					$this->session->set_flashdata('alert', 'Please select a valid CSV file!');
					redirect('import/updateinventory');
					
				}
				break;
								
			// Preview uploaded data
			case "preview":

				// Return the file path as we need to delete it 
				// from the server (space-saving!)
				$data['file'] = $this->input->post('file_upload');

				// Get a preview of the data
				$data['preview'] = $this->importtool->preview($this->input->post('file_upload'));

				// Load view
				$data['form_title'] 		= "Data Preview";
				$data['form_open'] 			= '<form action="'.site_url('import/updateinventory/do').'" method="post" enctype="multipart/form-data">';
				$data['form_close'] 		= '</form>';
				$data['form_submit_label'] 	= "Let's Update!";
				$data['form_cancel_link'] 	= site_url('import/updateinventory');
				$data['content'] 			= "options/import_preview";
				$this->load->view('global/template', $data);
				break;
				
			case "do";
				
				// Do the data import and redirect
				$this->importtool->do_update($_POST['row'], 'inventory', 'inventory');
				break;
		
		}
		
	}

}