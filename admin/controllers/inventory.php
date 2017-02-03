<?php

class Inventory extends CI_Controller {

	function Inventory()
	{
		parent::__construct();
		$this->load->database();
		
		$this->load->model('settings_model');
		$this->settings_model->initConfig();
		
		$this->load->model('inventory_model');
		$this->load->model('category_model');
		$this->load->model('collections_model');
		$this->load->model('filters_model');
		$this->load->model('google_model','google');
		$this->load->library('image_lib');
		$this->load->library('pagination');	
		$this->load->helper('download');	
		
		/* Login check */
		$this->load->model('login_model');
		if(!$this->login_model->check_session()){
			redirect('/login');
		}
		/* End: Login check */
	}
	
	#------------------------------------------------------
	# Inventory list
	#------------------------------------------------------
	function index()
	{			

		$this->permissions->access('can_access_inventory_list');

		//For pagination (including filter)	
		if ($this->input->post('filter') == 'true') {
			$config['url_format'] = site_url('/inventory/index/{offset}/'.urlencode(http_build_query($_POST)));
			$data['s_filter'] = http_build_query($_POST);
			$data['s_filter'] = str_replace('"', '', $data['s_filter']);
			//We'll redirect at this point so the url shows the query string
			redirect('inventory/index/0/'.urlencode($data['s_filter']));
		} elseif ($this->uri->segment(4) != ''){
			$config['url_format'] = site_url('/inventory/index/{offset}/'.urlencode($this->uri->segment(4)));
			$data['s_filter'] = urlencode($this->uri->segment(4));
		} else {
			$config['base_url'] = base_url().'index.php/inventory/index/';
		}

		$query = $this->inventory_model->countProducts();
		
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

		// Capture the current url so we can return to this 
		// page after an add/edit product page is accessed
		$data['redirect'] = redirect_create();

		$data['title']	 			= 'Inventory';
		$data['inventory'] 			= $this->inventory_model->listAllProducts($config['per_page'],$this->uri->segment(3));
		$data['collections'] 		= $this->collections_model->getFullCollectionsList();
		$data['collection_groups'] 	= $this->collections_model->collectionsNav();
		$data['categories'] 		= $this->category_model->getAvailableCategories();
		$data['locations'] 			= $this->inventory_model->getLocations();
		$data['coupons']			= $this->modules_model->getCoupons();
		
		$data['content'] = 'inventory/inventory_list';

		$this->load->view('global/template',$data);
	}
	
	#------------------------------------------------------
	# Add SINGLE product to database
	#------------------------------------------------------
	function add()
	{

		$this->permissions->access('can_access_inventory_addedit');

		// Get the filter groups for this products category
		$data['filter_groups'] = $this->filters_model->groups($data['item']->cat_id);
		
		//Define the type of listing we're creating here. We'll use this to display the
		//appropriate edit form with correct validation rules
		$listing_type = ($this->uri->segment(3) == 'variation') ? $this->uri->segment(3) : 'single';

		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('product_disabled');
		$this->form_validation->set_rules('cat_id');
		$this->form_validation->set_rules('product_name', 'Product name', 'required');
		$this->form_validation->set_rules('product_brand');
		$this->form_validation->set_rules('product_description');
		$this->form_validation->set_rules('product_excerpt');
		$this->form_validation->set_rules('product_condition');
		$this->form_validation->set_rules('priority', 'Priority', 'numeric');
		$this->form_validation->set_rules('product_tags');
		$this->form_validation->set_rules('product_meta_title');
		$this->form_validation->set_rules('product_custom_heading');
		$this->form_validation->set_rules('product_meta_description');
		$this->form_validation->set_rules('product_meta_keywords');
		$this->form_validation->set_rules('product_meta_custom');
		$this->form_validation->set_rules('custom_field_data[]', '', 'trim');
		
		//Apply additional validation rules for single item type
		if ($listing_type == 'single') {
		
			$this->form_validation->set_rules('product_costprice', 'Product cost price', 'numeric');
			$this->form_validation->set_rules('product_price', 'Product price', 'required|numeric');
			$this->form_validation->set_rules('product_saleprice', 'Product sale price', 'numeric');
			$this->form_validation->set_rules('product_weight', 'Product weight', 'numeric');
			$this->form_validation->set_rules('product_no', 'Product no', 'required');
			$this->form_validation->set_rules('product_ean');
			$this->form_validation->set_rules('product_mpn');
			$this->form_validation->set_rules('product_upc');
			$this->form_validation->set_rules('supplier_code');

		}

		//Stock locations & channels
		$locations = $this->inventory_model->getLocations();
		foreach ($locations as $location) {
			if ($listing_type == 'single' && $location->id > 1) {
				$this->form_validation->set_rules('channel_'.$location->id.'_product_price', "$location->name price", 'required|numeric|trim');
				$this->form_validation->set_rules('channel_'.$location->id.'_product_saleprice', "$location->name sale price", 'numeric|trim');
			}
			if ($listing_type == 'single') {
				$this->form_validation->set_rules("location_$location->id", "$location->name quantity", 'required|numeric|trim');
			}
			$this->form_validation->set_rules("channel_$location->id", "", 'numeric|trim');
		}

		$data['coupons'] = $this->modules_model->getCoupons();
		foreach($data['coupons'] as $coupon) {
			$this->form_validation->set_rules($coupon->field_name);
		}
		
		//Set the correct form url
		$form_url = ($listing_type == 'single') ? 'inventory/add' : 'inventory/add/variation';

		if ($this->form_validation->run() == FALSE):
		
			$data['title']	 = 'Inventory > Add item';

			$data['categories'] = $this->category_model->getAvailableCategories();
			$data['custom_field_templates'] = $this->settings_model->getCustomFields('inventory', 'parents');
			$data['attribute_sets'] = $this->inventory_model->getAttributeSets();
			$data['productoption_sets'] = $this->inventory_model->getProductOptionSets();
			$data['locations']	= $locations;

			$data['form_open'] = '<form id="formAddEditItem" action="'.site_url($form_url).'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Add new item';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = site_url('inventory');
	
			//Set the correct view file
			$view_file = ($listing_type == 'single') ? 'inventory_edit' : 'inventory_editvariation';
			
			$data['content'] = "inventory/$view_file";

			$this->load->view('global/template',$data);
			
		else:

			//Upload: Product file/attachment
			$config['upload_path'] 	 = $_SERVER['DOCUMENT_ROOT'].'/docs/';
			$config['allowed_types'] = 'pdf|doc|docx|xls|xlsx';
			$config['overwrite'] 	 = TRUE;
			$this->load->library('upload', $config);
			$this->upload->do_upload('product_file');
			$filedata = $this->upload->data();
			if ( $filedata['file_ext'] == '.pdf' ||  $filedata['file_ext'] == '.doc' || $filedata['file_ext'] == '.docx' ||  $filedata['file_ext'] == '.xls' || $filedata['file_ext'] == '.xlsx' ) 
			{
				$file_name = $filedata['file_name'];
				$error = '';
			}
			elseif($filedata['file_ext'] == ''){
				$file_name = '';
				$error = '';
			} else {
				$file_name = '';
				$error = $this->upload->display_errors('<strong class="red">','</strong>');
			}

			// Gets the new id of the product inserted right above.
			$data = array(
				'parent_id' 	=> 0,
				'product_type'	=> $listing_type,
			);
			$newid = $this->inventory_model->addProduct($file_name, $data);

			// Insert product attributes
			$attribute_order = 0;
			if (!empty($_POST['attribute_name'])) {
			while( list($attribute_name_id,$attribute_name)=each($_POST['attribute_name']) and 
				   list($attribute_value_id,$attribute_value)=each($_POST['attribute_value']) )
			{
				if (!empty($attribute_name) || !empty($attribute_value)):
				if ($attribute_delete != 'true') {
					$attribute_order++;
				}
				$this->inventory_model->addAttribute($newid,$attribute_name,$attribute_value,null,'false',$attribute_order);
				endif;
			}
			}

			// Insert product options
			$option_order = 0;
			if (!empty($_POST['option_label'])) {
			while( list($option_label_id,$option_label)=each($_POST['option_label']) and 
				   list($option_criteria_id,$option_criteria)=each($_POST['option_criteria']) and
				   list($option_price_id,$option_price)=each($_POST['option_price'])
				 )
			{
				if (!empty($option_label) || !empty($option_criteria)):
				if ($option_delete != 'true') {
					$option_order++;
				}
				$this->inventory_model->addProductOption($newid,$option_label,$option_criteria,$option_price,null,'false',$option_order);
				endif;
			}
			}

			//Custom fields
			if ($_POST['custom_field_label'] != '') {
				while(  list($custom_field_id_key, $custom_field_id)=each($_POST['custom_field_id']) and
						list($custom_field_label_key, $custom_field_label)=each($_POST['custom_field_label']) and
						list($custom_field_data_key, $custom_field_data)=each($_POST['custom_field_data'])
					 )
				{
					if (empty($custom_field_id)) {
						$custom_field_update = false;
					} else {
						$custom_field_update = true;
					}
					
					$this->settings_model->recordCustomFieldData($newid, $custom_field_label, $custom_field_data, $custom_field_update);

				}
			}

			// Extra categories
			$product_slug = get_product_slug($newid);

			if (!empty($_POST['x_categories'])):
				while( list($xcat_key,$xcat_id) = each($_POST['x_categories']) ) 
				{
					//Add the xcategories
					$this->inventory_model->addExtraCategory($xcat_id,$newid,$product_slug);
				}
			endif;

			//Clear db cache. We don't want to delete everything,
			//just the folder that applies.
			if ($this->config->item('caching') == 'true') {
			$cache_cat = $this->category_model->getThisCategory($this->input->post('cat_id'));
			delete_cache($cache_cat->cat_url);
			}

			//Post-save options
			$post_save_opts = '<a href="'.site_url("inventory/edit/$newid".redirect_create_manual('inventory') . "#section-gallery").'">Attach images</a> ';
			if ($listing_type == 'variation') {
			$post_save_opts .= '<a href="'.site_url("inventory/addvariation/$newid".redirect_create_manual('inventory')).'">Add variation</a> ';
			}
			$post_save_opts .= '<a href="'.site_url('inventory').'">View inventory</a> ';

			$result = $this->google->generate();
			$this->session->set_flashdata('alert',$result);		
			$this->session->set_flashdata('notice',"Product created. What would you like to do now? $post_save_opts");		
			redirect('/inventory/edit/' . $newid);

		endif;

	}

	#------------------------------------------------------
	# Update SINGLE product information
	#------------------------------------------------------
	function edit() 
	{

		$this->permissions->access('can_access_inventory_addedit');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();

		// Get product data here so we can grab a few things before the template loads
		$data['item'] = $this->inventory_model->getProduct($this->uri->segment(3));
	
		// Get the filter groups for this products category
		$data['filter_groups'] = $this->filters_model->groups($data['item']->cat_id);
		
		//Define the type of listing we're creating here. We'll use this to display the
		//appropriate edit form with correct validation rules
		$listing_type = ($data['item']->product_type == 'variation') ? $this->uri->segment(3) : 'single';
			
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('product_disabled');
		$this->form_validation->set_rules('cat_id');
		$this->form_validation->set_rules('product_name', 'Product name', 'required');
		$this->form_validation->set_rules('product_slug', 'Product slug', 'required');
		$this->form_validation->set_rules('product_brand');
		$this->form_validation->set_rules('product_description');
		$this->form_validation->set_rules('product_excerpt');
		$this->form_validation->set_rules('product_condition');
		$this->form_validation->set_rules('priority', 'Priority', 'numeric');
		$this->form_validation->set_rules('product_tags');
		$this->form_validation->set_rules('product_meta_title');
		$this->form_validation->set_rules('product_custom_heading');
		$this->form_validation->set_rules('product_meta_description');
		$this->form_validation->set_rules('product_meta_keywords');
		$this->form_validation->set_rules('product_meta_custom');
		$this->form_validation->set_rules('custom_field_data[]', '', 'trim');
		$this->form_validation->set_rules('gallery_product_image[]');

		//Apply additional validation rules for single item type
		if ($listing_type == 'single') {

			$this->form_validation->set_rules('product_costprice', 'Product cost price', 'numeric');
			$this->form_validation->set_rules('product_price', 'Product price', 'required|numeric');
			$this->form_validation->set_rules('product_saleprice', 'Product sale price', 'numeric');
			$this->form_validation->set_rules('product_weight', 'Product weight', 'numeric');
			$this->form_validation->set_rules('product_no', 'Product no', 'required');
			$this->form_validation->set_rules('product_ean');
			$this->form_validation->set_rules('product_mpn');
			$this->form_validation->set_rules('product_upc');
			$this->form_validation->set_rules('supplier_code');

		}
		
		//Stock locations & channels
		$locations = $this->inventory_model->getLocations();
		foreach ($locations as $location) {
			if ($listing_type == 'single' && $location->id > 1) {
				$this->form_validation->set_rules('channel_'.$location->id.'_product_price', "$location->name price", 'required|numeric|trim');
				$this->form_validation->set_rules('channel_'.$location->id.'_product_saleprice', "$location->name sale price", 'numeric|trim');
			}
			if ($listing_type == 'single') {
				$this->form_validation->set_rules("location_$location->id", "$location->name quantity", 'required|numeric|trim');
			}
			$this->form_validation->set_rules("channel_$location->id", "", 'numeric|trim');
		}
		
		$data['coupons'] = $this->modules_model->getCoupons();
		foreach($data['coupons'] as $coupon) {
			$this->form_validation->set_rules($coupon->field_name);
		}

		if ($this->form_validation->run() == FALSE):

			$data['title'] = 'Inventory > Edit item';

			$data['categories'] = $this->category_model->getAvailableCategories($this->uri->segment(3));
			$data['extracats'] 	= $this->inventory_model->listExtraCategories($this->uri->segment(3));
			$data['attributes'] = $this->inventory_model->getAttributes($this->uri->segment(3));
			$data['productoptions'] = $this->inventory_model->getProductOptions($this->uri->segment(3));
			$data['custom_field_templates'] = $this->settings_model->getCustomFields('inventory', 'parents');
			$data['attribute_sets'] = $this->inventory_model->getAttributeSets();
			$data['productoption_sets'] = $this->inventory_model->getProductOptionSets();
			$data['locations']	= $locations;

			$data['form_open'] = '<form id="formAddEditItem" action="'.site_url('inventory/edit/' . $this->uri->segment(3)) . $redirect->query_string . '" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = sprintf('Edit item - %s', $data['item']->product_name);
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = $redirect->link;

			//Set the correct view file
			$view_file = ($listing_type == 'single') ? 'inventory_edit' : 'inventory_editvariation';
			
			$data['content'] = "inventory/$view_file";
			
			$this->load->view('global/template',$data);
		
		else:

			//Upload: Product file/attachment
			$config['upload_path'] 	 = $_SERVER['DOCUMENT_ROOT'].'/docs/';
			$config['allowed_types'] = 'pdf|doc|docx|xls|xlsx';
			$config['overwrite'] 	 = TRUE;
			$this->load->library('upload', $config);
			$this->upload->do_upload('product_file');
			$filedata = $this->upload->data();
			if ( $filedata['file_ext'] == '.pdf' ||  $filedata['file_ext'] == '.doc' || $filedata['file_ext'] == '.docx' ||  $filedata['file_ext'] == '.xls' || $filedata['file_ext'] == '.xlsx' ) 
			{
				$file_name = $filedata['file_name'];
				$error = '';
			}
			elseif($filedata['file_ext'] == ''){
				$file_name = '';
				$error = '';
			} else {
				$file_name = '';
				$error = $this->upload->display_errors('<strong class="red">','</strong>');
			}
		
			// Update item
			$this->inventory_model->updateProduct($file_name);
			$this->inventory_model->redirection($this->input->post('product_id'));
			
			// Update product attributes
			$attribute_order = 0;
			if (!empty($_POST['attribute_name'])) {
			while( list($attribute_name_id,$attribute_name)=each($_POST['attribute_name']) and 
				   list($attribute_value_id,$attribute_value)=each($_POST['attribute_value']) and
				   list($attribute_id_key,$attribute_id)=each($_POST['attribute_id']) and
				   list($attribute_delete_key,$attribute_delete)=each($_POST['attribute_delete'])
				 )
			{
				if (!empty($attribute_name) || !empty($attribute_value)):
					if ($attribute_delete != 'true') {
						$attribute_order++;
					}
					$this->inventory_model->addAttribute($_POST['product_id'],$attribute_name,$attribute_value,$attribute_id,$attribute_delete,$attribute_order);
				endif;
			}
			}

			// Update product options
			$option_order = 0;
			if (!empty($_POST['option_label'])) {
			while( list($option_label_id,$option_label)=each($_POST['option_label']) and 
				   list($option_criteria_id,$option_criteria)=each($_POST['option_criteria']) and
				   list($option_price_id,$option_price)=each($_POST['option_price']) and
				   list($option_id_key,$option_id)=each($_POST['option_id']) and
				   list($option_delete_key,$option_delete)=each($_POST['option_delete'])
				 )
			{
				if (!empty($option_label) || !empty($option_criteria)):
					if ($option_delete != 'true') {
						$option_order++;
					}
					$this->inventory_model->addProductOption($_POST['product_id'],$option_label,$option_criteria,$option_price,$option_id,$option_delete,$option_order);
				endif;
			}
			}

			//Custom fields
			if ($_POST['custom_field_label'] != '') {
				while(  list($custom_field_id_key, $custom_field_id)=each($_POST['custom_field_id']) and
						list($custom_field_label_key, $custom_field_label)=each($_POST['custom_field_label']) and
						list($custom_field_data_key, $custom_field_data)=each($_POST['custom_field_data'])
					 )
				{
					if ($custom_field_id <= 0) {
						$custom_field_update = false;
					} else {
						$custom_field_update = true;
					}
					
					$this->settings_model->recordCustomFieldData($this->input->post('product_id'), $custom_field_label, $custom_field_data, $custom_field_update);

				}
			}

			// Get the product slug for this item 
			$product_slug = get_product_slug($_POST['product_id']);

			// Extra categories
			if ($_POST['xcategory_change'] == 'true') { //We know the xcategories have been changed
				//Remove all the xcats for this product
				$this->inventory_model->removeExtraCategories($_POST['product_id']);
				
				if (!empty($_POST['x_categories'])):
					while( list($xcat_key,$xcat_id) = each($_POST['x_categories']) ) 
					{
						//Add the xcategories
						$this->inventory_model->addExtraCategory($xcat_id,$_POST['product_id'],$product_slug);
					}
				endif;
			}

			//Clear db cache. We don't want to delete everything,
			//just the folder that applies.
			if ($this->config->item('caching') == 'true') {
			$cache_cat = $this->category_model->getThisCategory($this->input->post('cat_id'));
			delete_cache($cache_cat->cat_url);
			}

			$result = $this->google->generate();
			$this->session->set_flashdata('alert',$result);		

			if (strpos($redirect->link, 'admin/index.php')) {
				$this->session->set_flashdata('notice','Product information updated. <a href="' . $redirect->link . '">View inventory</a>. ' . $error);
				redirect('/inventory/edit/' . $_POST['product_id'] . $redirect->query_string);
			} else {
				redirect($redirect->link . '?shopit-notice=Product updated.');
			}
		
		endif;
	}

	#------------------------------------------------------
	# Duplicate product and variations (if any)
	#------------------------------------------------------
	function duplicate() {

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();

		// Set a nice var for the selected parent's product_id
		$parent_id = $this->uri->segment(3);

		// Get this product's data to help us create a duplicate
		$item = $this->inventory_model->getProduct($parent_id);
		
		// If single product type, copy the product number
		$product_no = ($item->product_type == 'single') ? "Copy of $item->product_no" : NULL;
		
		// Create a copy of this product, but prepend "Copy of " to the product 
		// name. Let's start by preparing our post array
		$_POST = array(
			// Product settings
			'product_disabled' 		   => 1,
			'product_order'			   => 0,
			// Product information
			'cat_id'				   => $item->cat_id,
			'product_name'			   => "Copy of $item->product_name",
			'product_description' 	   => $item->product_description,
			'product_excerpt'		   => $item->product_excerpt,
			'product_no'			   => $product_no,
			'product_image'			   => $item->product_image,
			'product_file'			   => $item->product_file,
			// Barcodes & supplier info
			'product_ean'			   => $item->product_ean,
			'product_mpn'			   => $item->product_mpn,
			'product_upc'			   => $item->product_upc,
			'supplier_code'			   => $item->supplier_code,
			// Price, weight and condition
			'product_costprice' 	   => $item->product_costprice,
			'product_price'			   => $item->product_price,
			'product_saleprice' 	   => $item->product_saleprice,
			'product_weight'		   => $item->product_weight,
			'product_condition' 	   => $item->product_condition,
			//Brand & slugs
			'product_slug'			   => $item->product_slug,
			'product_brand'			   => $item->product_brand,
			'product_brand_slug' 	   => $item->product_brand_slug,
			// Product meta
			'product_tags'			   => $item->product_tags,
			'product_meta_title'	   => $item->product_meta_title,
			'product_custom_heading'   => $item->product_custom_heading,
			'product_meta_description' => $item->product_meta_description,
			'product_meta_keywords'    => $item->product_meta_keywords,
			'product_meta_custom'	   => $item->product_meta_custom,
			// Other data
			'priority'				   => $item->priority,
			'date_added'			   => date('Y-m-d H:i:s', time()),
		);

		// And the other array data required for the model
		$data = array(
			'parent_id' 	=> 0,
			'product_type' 	=> $item->product_type,
		);

		// Stock locations & channels
		$locations = $this->inventory_model->getLocations();
		foreach($locations as $location) {
			// Stock
			$location_field 		= "location_$location->id";
			$_POST[$location_field] = $item->$location_field;
			// Sales channels
			$channel_field 		   = "channel_$location->id";
			$_POST[$channel_field] = $item->$channel_field;
			// Sales channel pricing
			$channel_field_price 		 	 = sprintf('channel_%s_product_price', $location->id);
			$_POST[$channel_field_price] 	 = $item->$channel_field_price;
			$channel_field_saleprice 		 = sprintf('channel_%s_product_saleprice', $location->id);
			$_POST[$channel_field_saleprice] = $item->$channel_field_saleprice;
		}

		// Get the filter groups for this products category
		$filter_groups = $this->filters_model->groups($item->cat_id);
		foreach($filter_groups as $group) {
		
			$filter_options = $this->filters_model->options($group->group_id);
			
			foreach($filter_options as $option) { 
				
				// Set the field name
				$filter_field_name = "filter_$option->filter_id";
				
				// Set the POST
				$_POST[$filter_field_name] = $item->$filter_field_name;
				
			}
		}

		// Coupons
		$coupons = $this->modules_model->getCoupons();
		foreach($coupons as $coupon) {
			$coupon_field_name = $coupon->field_name;
			$_POST[$coupon_field_name] = $item->$coupon_field_name;
		}

		// At this point, we need to insert and get the new product ID
		$newid = $this->inventory_model->addProduct(null, $data);
		
		// Product Attributes
		$attributes = $this->inventory_model->getAttributes($parent_id);
		if ($attributes > 0) {
			foreach($attributes as $attribute) {
				$this->inventory_model->addAttribute($newid, $attribute->attribute_name, $attribute->attribute_value, NULL, 'false', $attribute->attribute_order);
			}
		}
		
		// Product Options
		$productoptions = $this->inventory_model->getProductOptions($parent_id);
		if ($productoptions > 0) {
			foreach($productoptions as $p) {
				$this->inventory_model->addProductOption($newid, $p->option_label, $p->option_criteria, $p->option_price, NULL, 'false', $p->option_order);
			}
		}
		
		// Custom Fields
		$custom_field_templates = $this->settings_model->getCustomFields('inventory');
		foreach($custom_field_templates as $custom_field) {
			$custom_field_data = $this->settings_model->getCustomFieldData($parent_id, $custom_field->custom_field_label);
			$this->settings_model->recordCustomFieldData($newid, $custom_field->custom_field_label, $custom_field_data->custom_field_data, FALSE);
		}

		// Additional categories
		$xcats = $this->inventory_model->listExtraCategories($parent_id);
		if ($xcats > 0) {
			foreach($xcats as $xcat) {
				$this->inventory_model->addExtraCategory($xcat->cat_id, $newid, 'N/A');
			}
		}
		
		// Related items
		$xitems = $this->inventory_model->getCrossSells($parent_id);
		if ($xitems > 0) {
			foreach($xitems as $xitem) {
				$this->inventory_model->saveRelated($newid, $xitem->xitem_id, $xitem->type);
			}
		}
		
		// Uncomment the line below for testing
		#echo "<pre>" . print_r($_POST, true) . "</pre>";
	
		// If this is variation, we need to duplicate 
		// all the variants and attach them too
		if ($item->product_type == 'variation') {
		
			// Get all the variations for the item we're duplicating
			$variations = $this->inventory_model->getVariations($parent_id);
			foreach($variations as $variant) {
			
				// Trash any existing POST var
				unset($_POST);

				// Create the POST vars for this variant
				$_POST = array(
					// Product settings
					'product_disabled' 		   => $variant->product_disabled,
					'product_order'			   => $variant->product_order,
					// Product information
					'cat_id'				   => 0,
					'product_name'			   => $variant->product_name,
					'product_description' 	   => NULL,
					'product_excerpt'		   => NULL,
					'product_no'			   => $variant->product_no,
					'product_image'			   => $variant->product_image,
					'product_file'			   => NULL,
					// Barcodes & supplier info
					'product_ean'			   => $variant->product_ean,
					'product_mpn'			   => $variant->product_mpn,
					'product_upc'			   => $variant->product_upc,
					'supplier_code'			   => $variant->supplier_code,
					// Price, weight and condition
					'product_costprice' 	   => $variant->product_costprice,
					'product_price'			   => $variant->product_price,
					'product_saleprice' 	   => $variant->product_saleprice,
					'product_weight'		   => $variant->product_weight,
					'product_condition' 	   => NULL,
					//Brand & slugs
					'product_slug'			   => NULL,
					'product_brand'			   => NULL,
					'product_brand_slug' 	   => NULL,
					// Product meta
					'product_tags'			   => NULL,
					'product_meta_title'	   => NULL,
					'product_custom_heading'   => NULL,
					'product_meta_description' => NULL,
					'product_meta_keywords'    => NULL,
					'product_meta_custom'	   => NULL,
					// Other data
					'priority'				   => 0,
					'date_added'			   => date('Y-m-d H:i:s', time()),
				);
				
				$data = array(
					'parent_id' 	=> $newid,
					'product_type' 	=> 'variant',
				);

				// Stock levels
				foreach($locations as $location) {
					// Stock
					$location_field 		= "location_$location->id";
					$_POST[$location_field] = $variant->$location_field;
					// Sales channel pricing
					$channel_field_price 		 	 = sprintf('channel_%s_product_price', $location->id);
					$_POST[$channel_field_price] 	 = $variant->$channel_field_price;
					$channel_field_saleprice 		 = sprintf('channel_%s_product_saleprice', $location->id);
					$_POST[$channel_field_saleprice] = $variant->$channel_field_saleprice;
				}

				// Coupons
				foreach($coupons as $coupon) {
					$coupon_field_name = $coupon->field_name;
					$_POST[$coupon_field_name] = $variant->$coupon_field_name;
				}
		
				// At this point, we need to insert and get the new variant's product ID
				$variant_newid = $this->inventory_model->addProduct(NULL, $data);

				// Product Options
				$productoptions = $this->inventory_model->getProductOptions($variant->product_id);
				if ($productoptions > 0) {
					foreach($productoptions as $p) {
						$this->inventory_model->addProductOption($variant_newid, $p->option_label, $p->option_criteria, $p->option_price, NULL, 'false', $p->option_order);
					}
				}

				// Uncomment the line below for testing
				#echo "<pre>" . print_r($_POST, true) . "</pre>";
				
			}
		
		}
		
		// Redirect to inventory and highlight this new product
		$result = $this->google->generate();
		$this->session->set_flashdata('alert', $result);
		$this->session->set_flashdata('notice', 'Product duplicated. <a href="' . $redirect->link . '">View inventory</a>');
		redirect("inventory/index/0/filter=true&s_productno=id:$newid");

	}

	#------------------------------------------------------
	# Delete product from database
	# - ajax
	#------------------------------------------------------
	function delete() 
	{

		$this->permissions->access('can_access_inventory_addedit');

		if (!empty($_POST['id'])): //Ajax
			$product_id = $_POST['id'];
		elseif ($this->uri->segment(3) != ''): //Normal GET url
			$product_id = $this->uri->segment(3);
		endif;		

		//Get this product's details
		$item = $this->inventory_model->getProduct($product_id);
		
		//Delete the product from the database and everything
		//that is attached to it
		$this->inventory_model->deleteProduct($product_id);

		//Clear db cache. We don't want to delete everything,
		//just the folder that applies.
		if ($this->config->item('caching') == 'true') {
		$cache_cat = $this->category_model->getThisCategory($item->cat_id);
		delete_cache($cache_cat->cat_url);
		}
		
		$result = $this->google->generate();

	}

	#------------------------------------------------------
	# Archive product
	# - to preserve this item as a "hidden" page on the
	#	website ~ primarily for seo purposes
	#------------------------------------------------------
	function archive() {

		$this->permissions->access('can_access_inventory_addedit');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
		
		//Get this product's details
		$item = $this->inventory_model->getProduct($product_id);
		
		//Archive the product
		$this->inventory_model->archiveProduct($this->uri->segment(3));

		//Clear db cache. We don't want to delete everything,
		//just the folder that applies.
		if ($this->config->item('caching') == 'true') {
		$cache_cat = $this->category_model->getThisCategory($item->cat_id);
		delete_cache($cache_cat->cat_url);
		}
		
		//Display message to user
		$result = $this->google->generate();
		$this->session->set_flashdata('alert',$result);		
		$this->session->set_flashdata('notice', "Product archived.");
		
		//Redirect back to the page we were on
		redirect($redirect->link);
	
	}

	#------------------------------------------------------
	# Unarchive a product
	# - this will just update the flags and redirect
	#	the user to the edit screen so they can adjust
	#	product information
	#------------------------------------------------------
	function unarchive() {

		$this->permissions->access('can_access_inventory_addedit');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
	
		//Unarchive the product
		$this->inventory_model->unarchiveProduct($this->uri->segment(3));

		//Send a notice to the user
		$this->session->set_flashdata('notice','Product has been unarchived but still marked as disabled.');
		
		//Redirect to edit product page
		redirect('inventory/edit/'.$this->uri->segment(3) . $redirect->query_string);
	
	}

	#------------------------------------------------------
	# Update item status
	# i.e. product_disabled = 0/1
	#------------------------------------------------------
	function itemstatus() {

		$this->permissions->access('can_access_inventory_addedit');
	
		//Get the current status
		$current_status = $this->inventory_model->getItemStatus($this->uri->segment(3));

		//Update with new status, returns 0 or 1
		$new_status = $this->inventory_model->updateItemStatus($this->uri->segment(3), $current_status);

		//Print to json, so we can use via jQuery
		print json_encode(
			array('status' => $new_status, 'rowid' => $this->uri->segment(3), 'menuid' => $this->uri->segment(3))
		);

	}

	#------------------------------------------------------
	# Export CSV
	#------------------------------------------------------
	function export() {
		$this->permissions->access('can_access_inventory_exports');
		$data = $this->inventory_model->exportInventory();
		$filename = 'inventory_'.date('Y-m-d').'.csv';
		force_download($filename, $data);
	}

	#------------------------------------------------------
	# Gallery view
	#------------------------------------------------------
	function gallery() 
	{

		$this->permissions->access('can_access_inventory_addedit');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
		
		$data['redirect_link'] = $redirect->link;
		$data['redirect_query_string'] = $redirect->query_string;

		if ($this->uri->segment(3) != ''):
		
			//Check is this is a variant we're adding images to...
			$is_variant = ($this->uri->segment(4) == '') ? FALSE : TRUE;
		
			$data['title']	 = 'Inventory > Manage Gallery';
			$item = $this->inventory_model->getProduct($this->uri->segment(3), $is_variant);
			
			$data['product_id'] 	= $item->product_id;
			$data['parent_id']		= $item->parent_id;
			$data['product_name']	= unserialize_variant($item->product_name);
			$data['images']			= $item->product_image;
			
			$data['content'] = 'inventory/inventory_gallery';
			$this->load->view('global/template',$data);
		
		else:
			redirect('/inventory');
		endif;
	}

	#------------------------------------------------------
	# Related items form
	#------------------------------------------------------
	function related() {

		$this->permissions->access('can_access_inventory_addedit');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();

		$data['title']	 = 'Inventory > Manage related/similar items';

		$data['item'] 		= $this->inventory_model->getProduct($this->uri->segment(3));
		$data['relateditems'] = $this->inventory_model->getCrossSells($this->uri->segment(3));
		
		$data['form_open'] = '<form action="'. site_url('/inventory/update_related/'.$this->uri->segment(3). $redirect->query_string) . '" method="post" enctype="multipart/form-data" >';
		$data['form_close'] = '</form>';
		$data['form_cancel_link'] = $redirect->link;
		
		$data['content'] = 'inventory/inventory_relateditems_form';
		$this->load->view('global/template',$data);
	}

	#------------------------------------------------------
	# Update related items
	#------------------------------------------------------
	function update_related() {

		$this->permissions->access('can_access_inventory_addedit');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
		
		$this->inventory_model->updateRelated();

		if (strpos($redirect->link, 'admin/index.php')) {
			$this->session->set_flashdata('notice','Product information updated. <a href="'.$redirect->link.'">View inventory</a>');
			redirect('/inventory/related/' . $_POST['product_id'] . $redirect->query_string);
		} else {
			redirect($redirect->link.'?shopit-notice=Cross sells updated.');
		}
	
	}

	#------------------------------------------------------
	# Add image to product gallery
	#------------------------------------------------------
	function addimage()
	{

		$this->permissions->access('can_access_inventory_addedit');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();

		$upload_dir = $_SERVER["DOCUMENT_ROOT"].$this->config->item('path_to_uploads');
		$max_file_size = 1024000 * 3; //3Mb

		//Check if this is a variant
		$is_variant = ($this->input->post('parent_id') > 0) ? "/variation" : null;

		//NEW CODE (using codeigniter - supports jpg, png and gif files):
		if (preg_match('@(.+?).(jpg|JPG|gif|png)@', $_FILES['product_image']['name']) && $_FILES['product_image']['size'] <= $max_file_size) :

			//Upload the image to server
			ini_set('memory_limit','96M');
	
			$unique_no = rand(1111,9999);
			$custom_filename = slug($_POST['product_name']).'-'.$unique_no;
			preg_match('@(.+?).(jpg|JPG|gif|png)@', $_FILES['product_image']['name'], $ext);
			$db_filename = $custom_filename.".".$ext[2];
	
			$dimension = $this->config->item('max_image_width');
			
			$config['image_library'] = 'gd2';
			$config['source_image']	= $_FILES['product_image']['tmp_name'];
			$config['maintain_ratio'] = TRUE;
			$config['width']	 = $dimension;
			$config['height']	 = $dimension;
			$config['new_image'] = $upload_dir.$db_filename;
			
			$this->image_lib->initialize($config); 
			
			if ($this->image_lib->resize()) {
				//Update the database
				$this->inventory_model->appendImage($_POST['product_id'], $db_filename);
			}

			redirect('/inventory/gallery/'.$_POST['product_id'] . $is_variant . $redirect->query_string);

		else:
		
			$this->session->set_flashdata('notice','Please select a <strong>jpg, png or gif</strong> file to upload. No other file type is supported. Max file size: 3Mb.');
			redirect('/inventory/gallery/'.$_POST['product_id'] . $is_variant . $redirect->query_string);	
		
		endif;

	}

	#------------------------------------------------------
	# Remove image from product gallery
	#------------------------------------------------------
	function removeimage()
	{

		$this->permissions->access('can_access_inventory_addedit');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
	
		$upload_dir = $_SERVER["DOCUMENT_ROOT"].$this->config->item('path_to_uploads');
		
		$thisimage = $this->uri->segment(4);

		//Get list of images from db
		$item = $this->inventory_model->getImages($this->uri->segment(3));
					
		//Remove file string from item for the database
		$newimagelist = str_replace($thisimage.';','',$item->product_image);
				
		//Update database
		$this->inventory_model->updateImageList($this->uri->segment(3),$newimagelist);

		//Check if this is a child item so we can append to the url
		$is_variant = ($this->uri->segment(5) == "variation") ? "/variation" : null;
				
		redirect('inventory/gallery/'.$this->uri->segment(3) . $is_variant . $redirect->query_string);
	
	}

	#------------------------------------------------------
	# Ajax upload multiple product images
	#------------------------------------------------------
	function uploadimages() {

		$upload_dir = $_SERVER["DOCUMENT_ROOT"].$this->config->item('path_to_uploads');
		$max_file_size = 1024000 * 3; // 3Mb
		$html = "";

		// NEW CODE (using codeigniter - supports jpg, png and gif files):
		if (preg_match('@(.+?).(jpg|JPG|gif|png)@', $_FILES['product_image']['name']) && $_FILES['product_image']['size'] <= $max_file_size) {

			// Upload the image to server
			ini_set('memory_limit', '96M');
	
			// Make the file name web friendly and append a unique number
			$unique_no = rand(1111,9999);
			$basefile = pathinfo($_FILES['product_image']['name']);
			$custom_filename = slug($basefile['filename']).'-'.$unique_no;
			preg_match('@(.+?).(jpg|JPG|gif|png)@', $_FILES['product_image']['name'], $ext);
			$db_filename = $custom_filename.".".$ext[2];
	
			$dimension = $this->config->item('max_image_width');
			
			$config['image_library']  = 'gd2';
			$config['source_image']	  = $_FILES['product_image']['tmp_name'];
			$config['maintain_ratio'] = TRUE;
			$config['width']	 	  = $dimension;
			$config['height']	 	  = $dimension;
			$config['new_image'] 	  = $upload_dir.$db_filename;
			
			$this->image_lib->initialize($config); 
			
			if ($this->image_lib->resize()) {
			
				// Create a random number to attach to the html ID
				$rand = rand(1111, 9999);
	
				// Output the updated display
				$html  = '<li class="sortable-image">';
				$html .= '<div class="gallerythumb" style="background-image:url(\''.site_url("image/resize/$db_filename/100/100").'\');" data-src="'.site_url("image/resize/$db_filename/100/100").'">';
				$html .= '<img style="display:none;" src="'.site_root("uploads/$db_filename").'" />';
				$html .= '<input type="hidden" name="gallery_product_image[]" value="' . $db_filename . '" />';
				$html .= '</div>';
				$html .= '<div class="sortable-image-controls">';
				$html .= '</div>';
				$html .= '</li>';
			
			}
			
			echo $html;

		}
		
	}
	
	#------------------------------------------------------
	# Ajax edit product qty
	#------------------------------------------------------
	function editproductqty() {

		$this->permissions->access('can_access_inventory_addedit');
		
		if (!empty($_POST['id'])):
			
			$data = array(
						'location_1' 	=> $_POST['value'],
					);
			
			$this->db->where('product_id',$_POST['id']);
			$this->db->update('inventory',$data);
			
			print $_POST['value'];
		endif;
	}

	#------------------------------------------------------
	# Get products like $q
	# - uses ajax
	#------------------------------------------------------
	function showrelatedresults() {

		//Get list of cross-sell groups
		$data['groups'] = $this->inventory_model->getCrossSellGroups();
	
		if ($this->uri->segment(4) != ''):
			$data['inventory'] = $this->inventory_model->listProductsLike($this->uri->segment(3),$this->uri->segment(4));
		endif;		

		$this->load->view('inventory/inventory_relateditems',$data);
	}

	#------------------------------------------------------
	# Product lookup
	#------------------------------------------------------
	function lookup() {
			
		// Parameters to nice vars
		$channel_shortname = ($this->uri->segment(4) != '') ? $this->uri->segment(4) : 'website';
		$search_term 	   = $this->uri->segment(3);
		
		// Get channel details
		$data['channel'] = $this->inventory_model->getLocationByShortname($channel_shortname);
		
		// If no channel is detected with this shortname, default the channel id to 1 (website)
		if (count($data['channel']) == 0) {
			$data['channel'] = $this->inventory_model->getLocationByShortname('website');
		}
		
		// Set the channel fields for product_price, product_saleprice, etc.

		if ($this->uri->segment(3) != ''):
			$data['inventory'] = $this->inventory_model->listAllProductsLike(0, $search_term, 25, $data['channel']->id);
		endif;		
	
		$this->load->view('orders/orders_product_lookup', $data);
		
	}

	#------------------------------------------------------
	# Ajax: Sortable gallery images
	#------------------------------------------------------
	function gallerysort() {
	
		$this->inventory_model->updateImageOrder($_POST['product_id'],$_POST['product_images']);

	}

	#------------------------------------------------------
	# Custom Fields
	#------------------------------------------------------
	function custom() {

		$this->permissions->access('can_access_inventory_custom_fields');

		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		if ($this->input->post('custom_field_id') == '') {
		$this->form_validation->set_rules('custom_field_label', 'Custom field label', 'required');
		}
		$this->form_validation->set_rules('custom_field_title', 'Custom field title', 'required');

		if ($this->form_validation->run() == FALSE):

			$data['title']	 = 'Inventory > Custom Fields';
	
			$data['custom_fields'] = $this->settings_model->getCustomFields('inventory');
			
			//Get custom field data to edit
			if ($this->uri->segment(3) != '') {
			$data['edit'] = $this->settings_model->getCustomField($this->uri->segment(3));
			}
			
			$data['form_open'] = '<form action="'.current_url().'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Inventory:';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = site_url('inventory');
			
			$data['content'] = 'options/options_customfields';
			$this->load->view('global/template',$data);		
		
		else:
		
			//Save and redirect
			if ($this->input->post('custom_field_id') != '') {
				$this->settings_model->updateCustomField($this->input->post('custom_field_id'));
			} else {
				$this->settings_model->createCustomField('inventory');
			}
			$this->session->set_flashdata('notice','Custom field updated.');
			redirect('inventory/custom');
		
		endif;
	}

	#------------------------------------------------------
	# Attribute Sets
	#------------------------------------------------------
	function attributesets() {

		$this->permissions->access('can_access_attribute_sets');
		
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		
		$this->form_validation->set_rules('attribute_set_label', 'Attribute set label', 'trim|required');
		$this->form_validation->set_rules('attribute_set_desc', 'Attribute set description', 'trim');

		if ($this->form_validation->run() == FALSE):

			$data['title']	 = 'Inventory > Attribute Sets';

			$data['attribute_sets'] = $this->inventory_model->getAttributeSets();

			//Get custom field data to edit
			if ($this->uri->segment(3) != '') {
			$data['edit'] = $this->inventory_model->getAttributeSet($this->uri->segment(3));
			}

			$data['form_open'] = '<form action="'.current_url().'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Attribute sets';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = site_url('inventory/attributesets');
			
			$data['content'] = 'inventory/inventory_attributesets';
			$this->load->view('global/template',$data);		
		
		else:
			
			//Save and redirect
			if ($this->input->post('attribute_set_id') != '') {
				$this->inventory_model->updateAttributeSet($this->input->post('attribute_set_id'));
			} else {
				$this->inventory_model->createAttributeSet('inventory');
			}
			$this->session->set_flashdata('notice','Attribute set updated.');
			redirect('inventory/attributesets');
		
		endif;
	
	}
	
	#------------------------------------------------------
	# Attributes
	#------------------------------------------------------
	function attributes() {

		$this->permissions->access('can_access_attribute_sets');
		
		if ($this->uri->segment(3) == '') {
			redirect('inventory/attributesets');
		}

		// Update product attributes
		$attribute_order = 0;
		if (!empty($_POST['attribute_name'])) {
		while( list($attribute_name_id,$attribute_name)=each($_POST['attribute_name']) and 
			   list($attribute_value_id,$attribute_value)=each($_POST['attribute_value']) and
			   list($attribute_id_key,$attribute_id)=each($_POST['attribute_id']) and
			   list($attribute_delete_key,$attribute_delete)=each($_POST['attribute_delete'])
			 )
		{
			if (!empty($attribute_name) || !empty($attribute_value)):
				if ($attribute_delete != 'true') {
					$attribute_order++;
				}
				$this->inventory_model->addAttributeToSet($this->uri->segment(3), $attribute_name, $attribute_value, $attribute_id, $attribute_delete, $attribute_order);
			endif;
		}
		$this->session->set_flashdata('notice','Attribute set updated.');
		redirect('inventory/attributes/' . $this->uri->segment(3));
		}

		$data['title']	 = 'Inventory > Manage Attributes';
		
		$data['attribute_set'] = $this->inventory_model->getAttributeSet($this->uri->segment(3));
		$data['attributes'] = $this->inventory_model->getAttributesForSet($this->uri->segment(3));

		$data['form_open']  = '<form action="'.current_url().'" method="post" enctype="multipart/form-data" >';
		$data['form_title'] = 'Attribute sets';
		$data['form_close'] = '</form>';
		$data['form_cancel_link'] = site_url('inventory/attributesets');

		$data['content'] = 'inventory/inventory_attributes';
		$this->load->view('global/template',$data);

	}

	#------------------------------------------------------
	# Ajax: Load attribute set
	#------------------------------------------------------
	function loadattr() {
	
		$attributes = $this->inventory_model->getAttributesForSet($this->uri->segment(3));

		if ($attributes > 0):
		foreach ($attributes as $attribute):
?>
		<li class="table-row product-attribute">
			<label><img src="<?=template_directory('assets/images/icon-draggable2.png');?>" alt="" class="valign draggable" /></label>
			<input name="attribute_name[]" type="text" value="<?=$attribute->attribute_name;?>" class="textbox" size="30" /> =
			<input name="attribute_value[]" type="text" value="<?=$attribute->attribute_value;?>" class="textbox" size="30" />
			<input name="attribute_id[]" type="hidden" value="" />
			<input name="attribute_delete[]" type="hidden" value="false" />
		</li>
<?php 
		endforeach; 
		endif;
	
	}

	#------------------------------------------------------
	# Product option Sets
	#------------------------------------------------------
	function productoptionsets() {

		$this->permissions->access('can_access_product_option_sets');
		
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		
		$this->form_validation->set_rules('option_set_label', 'Option set label', 'trim|required');
		$this->form_validation->set_rules('option_set_desc', 'Option set description', 'trim');

		if ($this->form_validation->run() == FALSE):

			$data['title']	 = 'Inventory > Product Option Sets';

			$data['productoption_sets'] = $this->inventory_model->getProductOptionSets();

			//Get custom field data to edit
			if ($this->uri->segment(3) != '') {
			$data['edit'] = $this->inventory_model->getProductOptionSet($this->uri->segment(3));
			}

			$data['form_open'] = '<form action="'.current_url().'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Product option sets';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = site_url('inventory/productoptionsets');
			
			$data['content'] = 'inventory/inventory_productoptionsets';
			$this->load->view('global/template',$data);		
		
		else:
			
			//Save and redirect
			if ($this->input->post('option_set_id') != '') {
				$this->inventory_model->updateProductOptionSet($this->input->post('option_set_id'));
			} else {
				$this->inventory_model->createProductOptionSet('inventory');
			}
			$this->session->set_flashdata('notice','Product option set updated.');
			redirect('inventory/productoptionsets');
		
		endif;
	
	}
	
	#------------------------------------------------------
	# Product options
	#------------------------------------------------------
	function productoptions() {

		$this->permissions->access('can_access_product_option_sets');

		if ($this->uri->segment(3) == '') {
			redirect('inventory/productoptionsets');
		}
		
		// Update product attributes
		$option_order = 0;
		if (!empty($_POST['option_label'])) {
		while( list($option_label_id,$option_label)=each($_POST['option_label']) and 
			   list($option_criteria_id,$option_criteria)=each($_POST['option_criteria']) and
			   list($option_price_id,$option_price)=each($_POST['option_price']) and
			   list($option_id_key,$option_id)=each($_POST['option_id']) and
			   list($option_delete_key,$option_delete)=each($_POST['option_delete'])
			 )
		{
			if (!empty($option_label) || !empty($option_criteria)):
				if ($option_delete != 'true') {
					$option_order++;
				}
				$this->inventory_model->addProductOptionToSet($this->uri->segment(3),$option_label,$option_criteria,$option_price,$option_id,$option_delete,$option_order);
			endif;
		}
		$this->session->set_flashdata('notice','Product option set updated.');
		redirect('inventory/productoptions/' . $this->uri->segment(3));
		}

		$data['title']	 = 'Inventory > Manage Attributes';
		
		$data['productoption_set'] = $this->inventory_model->getProductOptionSet($this->uri->segment(3));
		$data['productoptions'] = $this->inventory_model->getProductOptionsForSet($this->uri->segment(3));

		$data['form_open']  = '<form action="'.current_url().'" method="post" enctype="multipart/form-data" >';
		$data['form_title'] = 'Attribute sets';
		$data['form_close'] = '</form>';
		$data['form_cancel_link'] = site_url('inventory/productoptionsets');

		$data['content'] = 'inventory/inventory_productoptions';
		$this->load->view('global/template',$data);

	}

	#------------------------------------------------------
	# Ajax: Load attribute set
	#------------------------------------------------------
	function loadproductopts() {
	
		$productoptions = $this->inventory_model->getProductOptionsForSet($this->uri->segment(3));

		if ($productoptions > 0):
		foreach ($productoptions as $option):
?>
		<li class="table-row product-option">
			<label><img src="<?=template_directory('assets/images/icon-draggable2.png');?>" alt="" class="valign draggable" /></label>
			<input name="option_label[]" type="text" value="<?=$option->option_label;?>" class="textbox" size="20" />
			<input name="option_criteria[]" type="text" value="<?=$option->option_criteria;?>" class="textbox" size="20" />
			<input name="option_price[]" type="text" value="<?=$option->option_price;?>" class="textbox number" size="20" />
			<input name="option_id[]" value="" type="hidden" />
			<input name="option_delete[]" type="hidden" value="false" />
		</li>
<?php 
		endforeach; 
		endif;
	
	}

	#------------------------------------------------------
	# Lookups
	#------------------------------------------------------
	function brands() {
	
		if ($this->uri->segment(3) != ''):
			$brands = $this->inventory_model->lookup_brands($this->uri->segment(3));
			
			if ($brands > 0) {
				foreach ($brands as $brand) {
					print '<div class="lookup-result" rel="'.$brand->product_brand.'">' . $brand->product_brand . "</div>";
				}
			}
		endif;
	
	}

	function suppliers() {
	
		if ($this->uri->segment(3) != ''):
			$suppliers = $this->inventory_model->lookup_suppliers($this->uri->segment(3));
			
			if ($suppliers > 0) {
				foreach ($suppliers as $supplier) {
					print '<div class="lookup-result" rel="'.$supplier->supplier_code.'">' . $supplier->supplier_code . "</div>";
				}
			}
		endif;
	
	}

	#------------------------------------------------------
	# Google Preview
	#------------------------------------------------------
	function googlepreview() {
		
		//Decide what we need to show..
		$page_title = ($this->input->post('meta_title') == '') ? $this->input->post('product_title') : $this->input->post('meta_title');
		$page_desc  = ($this->input->post('meta_desc') == '') ?  $this->input->post('product_desc') : $this->input->post('meta_desc');
		
		$slug = get_product_slug($this->input->post('product_id'));
		
		$cat = $this->category_model->getThisCategory($this->input->post('cat_id'));
		
		//Do some formatting...
		$page_title = $page_title . " - " . $cat->cat_name . " | " . config_item('store_name'); 
		$page_title = character_limiter($page_title, 65);
		$page_desc = strip_tags($page_desc);
		$page_desc = word_limiter($page_desc, 25);
		$page_url = config_item('site_root');
		$page_url = str_replace('http://', '', $page_url);
		$page_url = $page_url . $slug;
		$page_url = (strlen($page_url) >  70) ? substr($page_url, 0, 70) . "..." : $page_url;
		
		echo "<h4>$page_title</h4>\n";
		echo "<cite>$page_url</cite>";
		echo "<p>$page_desc</p>";
		
	}

	#------------------------------------------------------
	# Stock Locations
	#------------------------------------------------------
	// Display purchase module message
	function purchaselocations() {
		
		$data['title']	 	= 'Inventory > Manage Stock Locations';
		$data['page_title'] = 'Get Locations/Sales Channel Module';
		
		$data['content'] 	= 'purchase/stocklocations';
		$this->load->view('global/template', $data);

	}
	
	function locations() {

		// Check module is installed
		if (!library_exists('stocklocations')) {
			redirect('inventory/purchaselocations');
		} else {
			$this->permissions->access('can_module_stocklocations');
		}

		// Manage locations
		if (!empty($_POST)) {
		
			//Update locations
			while( list($location_name_id, $location_name)=each($_POST['location_name']) and 
				   list($location_note_id, $location_note)=each($_POST['location_note']) and
				   list($location_type_id, $location_type)=each($_POST['location_type']) and
				   list($location_id_key, $location_id)=each($_POST['location_id'])
				 )
			{
				if (!empty($location_name)) {
				
					//Protect the location type if this is the default location (this website)
					$type = ($location_id == 1) ? 'default' : $location_type;
					
					//Set the global stock usage flag
					$location_useglobal = (isset($_POST['location_useglobal'][$location_id_key])) ? 1 : 0;
				
					$update_data = array(
						'name' => $location_name,
						'note' => $location_note,
						'type' => $location_type,
						'use_global_stock' => $location_useglobal,
					);
				
					$this->inventory_model->updateLocation($location_id, $update_data);
				
				}
			}
			
			//Add new location
			if ($this->input->post('location_name_new') != '') {
				$insert_data = array(
					'name' => $this->input->post('location_name_new'),
					'note' => $this->input->post('location_note_new'),
					'type' => $this->input->post('location_type_new'),
					'use_global_stock' => $this->input->post('location_useglobal_new'),
				);
				
				$this->inventory_model->addLocation($insert_data);
			}
			
			//Redirect and display message
			$this->session->set_flashdata('notice','Locations updated.');
			redirect('inventory/locations');
		
		}

		$data['title'] = 'Inventory > Manage Stock Locations';
		
		$data['locations'] = $this->inventory_model->getLocations();

		$data['form_open']  = '<form action="'.current_url().'" method="post" enctype="multipart/form-data" >';
		$data['form_title'] = 'Manage Locations';
		$data['form_close'] = '</form>';
		$data['form_cancel_link'] = site_url('inventory');

		$data['content'] = 'modules/locations';
		$this->load->view('global/template', $data);
		
	}

	// Delete Location
	function deletelocation() {
		$this->permissions->access('can_module_stocklocations');
		$this->inventory_model->deleteLocation($this->uri->segment(3));
	}

	#------------------------------------------------------
	# Ajax: Detach variation
	#------------------------------------------------------
	function deletevariation() {
		$this->permissions->access('can_access_inventory_addedit');
		$this->inventory_model->deleteVariation($this->uri->segment(3));
	}

	#------------------------------------------------------
	# Edit variation information
	#------------------------------------------------------
	function editvariation() {

		$this->permissions->access('can_access_inventory_addedit');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
			
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('product_disabled');
		$this->form_validation->set_rules('product_name', 'Product name', 'trim|required');
		$this->form_validation->set_rules('product_no', 'Product No', 'trim|required');
		$this->form_validation->set_rules('product_price', 'Product price', 'trim|required|numeric');
		$this->form_validation->set_rules('product_saleprice', 'Product sale price', 'trim|numeric');
		$this->form_validation->set_rules('product_weight', 'Product weight', 'trim|numeric');
		$this->form_validation->set_rules('product_ean');
		$this->form_validation->set_rules('product_mpn');
		$this->form_validation->set_rules('product_upc');
		$this->form_validation->set_rules('supplier_code');

		//Stock locations & channels
		$locations = $this->inventory_model->getLocations();
		foreach ($locations as $location) {
			if ($location->id > 1) {
				$this->form_validation->set_rules('channel_'.$location->id.'_product_price', "$location->name price", 'required|numeric|trim');
				$this->form_validation->set_rules('channel_'.$location->id.'_product_saleprice', "$location->name sale price", 'numeric|trim');
			}
			$this->form_validation->set_rules("location_$location->id", "$location->name quantity", 'required|numeric|trim');
			$this->form_validation->set_rules("channel_$location->id", "", 'numeric|trim');
		}

		// Coupons module
		$data['coupons'] = $this->modules_model->getCoupons();
		foreach($data['coupons'] as $coupon) {
			$this->form_validation->set_rules($coupon->field_name);
		}
		// End

		if ($this->form_validation->run() == FALSE):

			$data['locations'] = $locations;
			$data['title'] = 'Inventory > Edit Variation';

			$data['item'] = $this->inventory_model->getProduct($this->uri->segment(3), TRUE);
			$data['productoptions'] = $this->inventory_model->getProductOptions($this->uri->segment(3));
			$data['productoption_sets'] = $this->inventory_model->getProductOptionSets();
			$data['custom_field_templates'] = $this->settings_model->getCustomFields('inventory', 'variants');
			
			$data['form_open'] = '<form id="formAddEditItem" action="'.site_url('inventory/editvariation/'.$this->uri->segment(3) . $redirect->query_string).'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = sprintf('Edit Variation for %s', $data['item']->parent_name);
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = $redirect->link;
			
			$data['content'] = 'inventory/inventory_editvariant';
			$this->load->view('global/template',$data);
		
		else:

			// Check if variant name attributes have been applied (i.e. been posted)
			if (isset($_POST['variant_attr'])) {

				while( list($variant_attr_key, $variant_attr) = each($_POST['variant_attr']) ) {
					// Create array item (if the name has been entered)
					if (!empty($variant_attr['name'])) {
						$v_attr[$variant_attr['name']] = array(
							'value' => $variant_attr['value'],
							'image' => $variant_attr['image']
						);
					}
				}
				
				// Now serialize the attributes array
				$serialized_v_attr = serialize($v_attr);
				
				// And replace the product_name post with the serialized string
				$_POST['product_name'] = $serialized_v_attr;
				
			}
		
			// Update item
			$this->inventory_model->updateProduct($file_name, $data);

			//Custom fields
			if ($_POST['custom_field_label'] != '') {
				while(  list($custom_field_id_key, $custom_field_id)=each($_POST['custom_field_id']) and
						list($custom_field_label_key, $custom_field_label)=each($_POST['custom_field_label']) and
						list($custom_field_data_key, $custom_field_data)=each($_POST['custom_field_data'])
					 )
				{
					if (empty($custom_field_id)) {
						$custom_field_update = false;
					} else {
						$custom_field_update = true;
					}
					
					$this->settings_model->recordCustomFieldData($this->input->post('product_id'), $custom_field_label, $custom_field_data, $custom_field_update);

				}
			}

			// Update product options
			$option_order = 0;
			if (!empty($_POST['option_label'])) {
				while( list($option_label_id,$option_label)=each($_POST['option_label']) and 
					   list($option_criteria_id,$option_criteria)=each($_POST['option_criteria']) and
					   list($option_price_id,$option_price)=each($_POST['option_price']) and
					   list($option_id_key,$option_id)=each($_POST['option_id']) and
					   list($option_delete_key,$option_delete)=each($_POST['option_delete'])
					 )
				{
					if (!empty($option_label) || !empty($option_criteria)):
						if ($option_delete != 'true') {
							$option_order++;
						}
						$this->inventory_model->addProductOption($this->input->post('product_id'),$option_label,$option_criteria,$option_price,$option_id,$option_delete,$option_order);
					endif;
				}
			}

			$result = $this->google->generate();
			$this->session->set_flashdata('alert',$result);		
			
			if (strpos($redirect->link, 'admin/index.php')) {
				$this->session->set_flashdata('notice','Variation updated. <a href="' . $redirect->link . '">View inventory</a>. ' . $error);
				redirect('/inventory/editvariation/' . $this->input->post('product_id') . $redirect->query_string);
			} else {
				redirect($redirect->link . '?shopit-notice=Variation updated.');
			}
		
		endif;
	}

	#------------------------------------------------------
	# Add variation information
	# - This function is also used for "Duplicate variation"
	#------------------------------------------------------
	function addvariation() {

		$this->permissions->access('can_access_inventory_addedit');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
			
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('product_disabled');
		$this->form_validation->set_rules('product_name', 'Product name', 'trim|required');
		$this->form_validation->set_rules('product_no', 'Product No', 'trim|required');
		$this->form_validation->set_rules('product_price', 'Product price', 'trim|required|numeric');
		$this->form_validation->set_rules('product_saleprice', 'Product sale price', 'trim|numeric');
		$this->form_validation->set_rules('product_weight', 'Product weight', 'trim|numeric');
		$this->form_validation->set_rules('product_ean');
		$this->form_validation->set_rules('product_mpn');
		$this->form_validation->set_rules('product_upc');
		$this->form_validation->set_rules('supplier_code');

		//Stock locations & channels
		$locations = $this->inventory_model->getLocations();
		foreach ($locations as $location) {
			if ($location->id > 1) {
				$this->form_validation->set_rules('channel_'.$location->id.'_product_price', "$location->name price", 'required|numeric|trim');
				$this->form_validation->set_rules('channel_'.$location->id.'_product_saleprice', "$location->name sale price", 'numeric|trim');
			}
			$this->form_validation->set_rules("location_$location->id", "$location->name quantity", 'required|numeric|trim');
			$this->form_validation->set_rules("channel_$location->id", "", 'numeric|trim');
		}

		// Coupons module
		$data['coupons'] = $this->modules_model->getCoupons();
		foreach($data['coupons'] as $coupon) {
			$this->form_validation->set_rules($coupon->field_name);
		}
		// End

		if ($this->form_validation->run() == FALSE):

			// Auto-detect if this variant will use utlilise attributes
			$data['has_variant_attributes'] = false;
			$has_variant_attributes = $this->inventory_model->getVariations($this->uri->segment(3), 'product_name');
			foreach($has_variant_attributes as $variant_attr) {
				// If we find an array, set the flag as true
				// and keep it that way whilst we complete the loop
				if (is_array(unserialize($variant_attr->product_name))) {
					$data['has_variant_attributes'] = true;
				}
			}

			// If this is a "duplicate variation" link that has been clicked,
			// get the child's product_id (segment 4) we're duplicating.
			if ($this->uri->segment(4) != "") {
				$child_product_id = $this->uri->segment(4);
				$data['item'] = $this->inventory_model->getProduct($child_product_id, TRUE);
				// Let's prepend "Copy of" to the product title and product number
				$data['item']->{'product_name'} = $data['item']->product_name;
				$data['item']->{'product_no'} = 'Copy of ' . $data['item']->product_no;
			}

			$data['locations'] = $locations;
			$data['title'] = 'Inventory > Add variation';

			$data['productoptions'] = $this->inventory_model->getProductOptions($child_product_id);
			$data['productoption_sets'] = $this->inventory_model->getProductOptionSets();
			$data['custom_field_templates'] = $this->settings_model->getCustomFields('inventory', 'variants');

			$data['form_open'] = '<form id="formAddEditItem" action="'.site_url('inventory/addvariation/'.$this->uri->segment(3) . $redirect->query_string).'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Add variation';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = $redirect->link;
			
			$data['content'] = 'inventory/inventory_editvariant';
			$this->load->view('global/template', $data);
		
		else:

			// Check if variant name attributes have been applied (i.e. been posted)
			if (isset($_POST['variant_attr'])) {
				
				while( list($variant_attr_key, $variant_attr) = each($_POST['variant_attr']) ) {
					// Create array item (if the name has been entered)
					if (!empty($variant_attr['name'])) {
						$v_attr[$variant_attr['name']] = array(
							'value' => $variant_attr['value'],
							'image' => $variant_attr['image']
						);
					}
				}
				
				// Now serialize the attributes array
				$serialized_v_attr = serialize($v_attr);
				
				// And replace the product_name post with the serialized string
				$_POST['product_name'] = $serialized_v_attr;
				
			}
		
			// Gets the new id of the product inserted right above.
			$data = array(
				'parent_id' 	=> $this->uri->segment(3),
				'product_type'	=> 'variant',
			);
			$newid = $this->inventory_model->addProduct($file_name, $data);

			//Custom fields
			if ($_POST['custom_field_label'] != '') {
				while(  list($custom_field_id_key, $custom_field_id)=each($_POST['custom_field_id']) and
						list($custom_field_label_key, $custom_field_label)=each($_POST['custom_field_label']) and
						list($custom_field_data_key, $custom_field_data)=each($_POST['custom_field_data'])
					 )
				{
					if (empty($custom_field_id)) {
						$custom_field_update = false;
					} else {
						$custom_field_update = true;
					}
					
					$this->settings_model->recordCustomFieldData($newid, $custom_field_label, $custom_field_data, $custom_field_update);

				}
			}

			// Insert product options
			$option_order = 0;
			if (!empty($_POST['option_label'])) {
				while( list($option_label_id,$option_label)=each($_POST['option_label']) and 
					   list($option_criteria_id,$option_criteria)=each($_POST['option_criteria']) and
					   list($option_price_id,$option_price)=each($_POST['option_price'])
					 )
				{
					if (!empty($option_label) || !empty($option_criteria)):
					if ($option_delete != 'true') {
						$option_order++;
					}
					$this->inventory_model->addProductOption($newid,$option_label,$option_criteria,$option_price,null,'false',$option_order);
					endif;
				}
			}

			$result = $this->google->generate();
			$this->session->set_flashdata('alert',$result);		
			$this->session->set_flashdata('notice','Variation added. What would you like to do now? <a href="' . $redirect->link . '">View inventory</a> <a href="'.site_url('inventory/addvariation/'.$this->uri->segment(3)).'">Add another variation</a>' . $error);
			redirect("/inventory/editvariation/$newid$redirect->query_string");
		
		endif;
	}

	#------------------------------------------------------
	# Re-order variations
	#------------------------------------------------------
	function sortvariations() {

		$children  = explode(';',$_POST['child_id']);
		$parent_id = $this->input->post('parent_id');
		$order 	   = 1;
		
		foreach ($children as $child_id) {
			if (!empty($child_id) && $parent_id > 0):
				$this->inventory_model->reorderVariations($child_id, $parent_id, $order++);
			endif;
		}
	
	}
	
	#------------------------------------------------------
	# Ajax Form: Split variant name
	# - This is to enable attributes to be applied to
	#   variants through the product name
	#------------------------------------------------------
	function splitvariantname() {
		$this->load->view('inventory/inventory_editvariant_splitvariantname');
	}

	#------------------------------------------------------
	# Convert item to single or variation
	#------------------------------------------------------
	function convert() {

		$this->permissions->access('can_access_inventory_addedit');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();

		$conversion_type = $this->uri->segment(3);
		$product_id = $this->uri->segment(4);
		
		$this->inventory_model->convertProduct($conversion_type, $product_id);

		$result = $this->google->generate();
		
		$this->session->set_flashdata('alert',$result);		
		$this->session->set_flashdata('notice','Product converted successfully <a href="' . $redirect->link . '">View inventory</a>');
		
		// Redirect to the filtered inventory list
		$redirect_url = sprintf('inventory/index/0/filter=true&s_productno=%s', urlencode("id:$product_id"));
		redirect($redirect_url);

	}

	#------------------------------------------------------
	# Bulk process orders
	#------------------------------------------------------
	function process() {

		$this->permissions->access('can_access_inventory_addedit');
		
		if (!empty($_POST['product_id']) && $_POST['action'] != "") {
		
			switch($this->input->post('action')) {

				// Delete products
				case "delete":
				
					foreach($_POST['product_id'] as $product_id) {

						// Clear db cache. We don't want to delete everything,
						// just the folder that applies.
						if ($this->config->item('caching') == 'true') {
							// Get the item details
							$item = $this->inventory_model->getProduct($product_id);
							$cache_cat = $this->category_model->getThisCategory($item->cat_id);
							delete_cache($cache_cat->cat_url);
						}
						
						// Delete the product from the database and everything
						// that is attached to it
						$this->inventory_model->deleteProduct($product_id);

					}

					// Regenerate the google feed
					$result = $this->google->generate();

					// Redirect back to last page
					$this->session->set_flashdata('notice', 'Selected items deleted.');
					redirect($this->input->post('redirect'));
					break;
				
				// Enable products
				case "enable":
	
					foreach($_POST['product_id'] as $product_id) {
						$this->inventory_model->setItemStatus($product_id, 0);
					}

					//Redirect back to last page
					$this->session->set_flashdata('notice','Selected items updated.');
					redirect($this->input->post('redirect'));
					break;

				// Disable products
				case "disable":
	
					foreach($_POST['product_id'] as $product_id) {
						$this->inventory_model->setItemStatus($product_id, 1);
					}

					//Redirect back to last page
					$this->session->set_flashdata('notice','Selected items updated.');
					redirect($this->input->post('redirect'));
					break;

				// Archive products
				case "archive":
	
					foreach($_POST['product_id'] as $product_id) {
						$this->inventory_model->archiveProduct($product_id);
					}

					//Redirect back to last page
					$this->session->set_flashdata('notice','Selected items updated.');
					redirect($this->input->post('redirect'));
					break;

				// Add item to location
				case (strpos($this->input->post('action'), 'add_channel_')) :
				
					// Remove the "add_-" prefix from the channel id and 
					// set the POST var which needs to be passed to the model
					$channel_field = str_replace('add_', '', $this->input->post('action'));
					
					foreach($_POST['product_id'] as $product_id) {
						$this->inventory_model->addItemToChannel($channel_field, $product_id, true);
					}
					
					//Redirect back to last page
					$this->session->set_flashdata('notice','Selected items updated.');
					redirect($this->input->post('redirect'));
					break;

				// Remove item from location
				case (strpos($this->input->post('action'), 'remove_channel_')) :
				
					// Remove the "remove_-" prefix from the channel id and 
					// set the POST var which needs to be passed to the model
					$channel_field = str_replace('remove_', '', $this->input->post('action'));
					
					foreach($_POST['product_id'] as $product_id) {
						$this->inventory_model->addItemToChannel($channel_field, $product_id, false);
					}
					
					//Redirect back to last page
					$this->session->set_flashdata('notice','Selected items updated.');
					redirect($this->input->post('redirect'));
					break;

				// Move item to collection
				case (strpos($this->input->post('action'), 'collection-')) :
				
					// Remove the "collection-" prefix from the category id and 
					// set the POST var which needs to be passed to the model
					$collection_id = str_replace('collection-', '', $this->input->post('action'));
					
					foreach($_POST['product_id'] as $product_id) {
						$this->collections_model->addToCollection($collection_id, $product_id);
					}
					
					//Redirect back to last page
					$this->session->set_flashdata('notice','Selected items updated.');
					redirect($this->input->post('redirect'));
					break;

				// Apply or Remove a coupon from selected items
				case (preg_match('@(add|remove)_coupon_(.+?)$@', $this->input->post('action')) ? true : false) :
					
					// Match the bits we need
					preg_match('@(add|remove)_coupon_(.+?)$@', $this->input->post('action'), $matches);

					$coupon_setting = ($matches[1] == 'add') ? 1 : 0;
					$coupon_colname = 'coupon_'.$matches[2];

					// Loop through each product and apply the coupon setting
					foreach($_POST['product_id'] as $product_id) {
						$this->modules_model->applyCouponToProduct($coupon_colname, $product_id, $coupon_setting);
					}

					//Redirect back to last page
					$this->session->set_flashdata('notice','Selected items updated.');
					redirect($this->input->post('redirect'));
					break;
				
				// Move item to another category
				case (strpos($this->input->post('action'), 'category-')) :
				
					// Remove the "category-" prefix from the category id and 
					// set the POST var which needs to be passed to the model
					$cat_id = str_replace('category-', '', $this->input->post('action'));
					
					foreach($_POST['product_id'] as $product_id) {
						$this->inventory_model->setCategory($product_id, $cat_id);
					}
					
					//Redirect back to last page
					$this->session->set_flashdata('notice','Selected items updated.');
					redirect($this->input->post('redirect'));
					break;
					
				// Redirect to last page if no action is passed
				default:
					//Redirect back to last page
					redirect($this->input->post('redirect'));
					break;
				
			}
				
		} else {
			//Redirect back to last page
			redirect($this->input->post('redirect'));
		}
	
	}

	#------------------------------------------------------
	# Cross-Sell Groups
	#------------------------------------------------------
	function crosssellgroups() {

		$this->permissions->access('can_access_inventory_addedit');
			
		// Check the new cross-sell groups table named 'xitem_groups' exists
		// and if not, create it
		if ($this->db->table_exists('xitem_groups') === FALSE) {
			
			// Set the fields
			$fields = array( 
				'id' => array(
					'type' 	 	 => 'int',
					'constraint' => 11,
					'null' 		 => FALSE,
					'unsigned'   => TRUE,
	                'auto_increment' => TRUE,
				),
				'label' => array(
					'type'		=> 'varchar',
					'constraint' => 250,
					'null'		=> TRUE,
					'default'	=> NULL,
				),
				'type' => array(
					'type'		=> 'varchar',
					'constraint' => 30,
					'null'		=> TRUE,
					'default'	=> NULL,
				),
				'group_order' => array(
					'type'		=> 'int',
					'constraint' => 11,
					'null'		=> TRUE,
					'default'	=> '0',
				),
			);
			
			$this->load->dbforge();
			$this->dbforge->add_field($fields); 
			$this->dbforge->add_key('id', TRUE);
			$this->dbforge->create_table('xitem_groups', TRUE); // gives CREATE TABLE IF NOT EXISTS table_name
			unset($fields);
			
		}
		
		// Continue script
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('label', 'Label', 'trim|required');

		if ($this->form_validation->run() == FALSE):

			$data['title'] = 'Cross-Sell Groups';
	
			$data['groups'] = $this->inventory_model->getCrossSellGroups();
			
			//Get custom field data to edit
			if ($this->uri->segment(3) != '') {
				$data['edit'] = $this->inventory_model->getCrossSellGroup($this->uri->segment(3));
			}
			
			$data['form_open'] = '<form action="'.current_url().'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Manage Cross-Sell Groups';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = site_url('pages');
			
			$data['content'] = 'inventory/cross_sell_groups';
			$this->load->view('global/template',$data);		
		
		else:
		
			//Save and redirect
			if ($this->input->post('group_id') != '') {
				$this->inventory_model->updateCrossSellGroup($this->input->post('group_id'));
			} else {
				$this->inventory_model->createCrossSellGroup();
			}
			$this->session->set_flashdata('notice','Group updated.');
			redirect('inventory/crosssellgroups');
		
		endif;

	}

	// Ajax sort
	function sortcrosssellgroups() {

		$groups = explode(';', $_POST['id']);
		$order = 1;
		
		foreach ($groups as $id) {
			if (!empty($id)):
				$this->inventory_model->orderCrossSellGroups($id, $order++);
			endif;
		}
	
	}

}