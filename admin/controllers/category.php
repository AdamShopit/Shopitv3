<?php

class Category extends CI_Controller {

	function Category() {
		parent::__construct();
		
		$this->load->database();
		$this->load->model('category_model');
		$this->load->model('google_model','google');
		$this->load->helper('download');	

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
	# List of categories
	#------------------------------------------------------
	function index() {
		
		$this->permissions->access('can_access_category_list');

		// Capture the current url so we can return to this 
		// page after an add/edit product page is accessed
		$data['redirect'] = redirect_create();
	
		$data['title']	 = 'Categories';
		$data['categories'] = $this->category_model->getAllParents();
		$data['content'] = 'categories/category_list';
		$this->load->view('global/template',$data);
	}

	#------------------------------------------------------
	# Get list Sub Categories
	#------------------------------------------------------
	function sub() {

		$this->permissions->access('can_access_category_list');

		$data['categories'] = $this->category_model->getSubCategories($this->uri->segment(3));
		$parent = $this->category_model->getThisCategory($this->uri->segment(3));
		
		$data['parent_cat'] = $parent->cat_name;
		$data['parent_id'] = $parent->cat_id;
		
		$data['parent_father_id'] = $parent->cat_father_id;
		if ($parent->cat_father_id != 0) {
			$data['is_third_tier'] = true;
		}

		// Capture the current url so we can return to this 
		// page after an add/edit product page is accessed
		$data['redirect'] = redirect_create();
				
		$data['title']	 = 'Categories >' . ' ' . $parent->cat_name;
		
		if ($data['categories'] > 0):
			$data['content'] = 'categories/category_sublist';
		else:
			$data['content'] = 'categories/category_nodata';
		endif;
		
		$this->load->view('global/template',$data);
	}
	
	#------------------------------------------------------
	# Add category to database
	#------------------------------------------------------
	function add() {

		$this->permissions->access('can_access_category_addedit');
		
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('cat_hide');
		$this->form_validation->set_rules('cat_father_id');
		$this->form_validation->set_rules('cat_name', 'Category name', 'required');
		$this->form_validation->set_rules('cat_desc');
		$this->form_validation->set_rules('cat_excerpt');
		$this->form_validation->set_rules('cat_meta_title');
		$this->form_validation->set_rules('cat_custom_heading');
		$this->form_validation->set_rules('cat_meta_description');
		$this->form_validation->set_rules('cat_meta_keywords');
		$this->form_validation->set_rules('cat_meta_custom');

		if ($this->form_validation->run() == FALSE) :
		
			$data['title']	 = 'Categories > Add Category';

			$data['categories'] = $this->category_model->getAllCategories();

			$data['form_open'] = '<form action="'.site_url('category/add').'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Add new category';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = site_url('category');
			
			$data['content'] = 'categories/category_edit';
			$this->load->view('global/template',$data);
			
		else:
		
			/** Module: Category icons **/
			if (library_exists('categoryicons')):
			ini_set('memory_limit','24M');
			$config['upload_path'] = $_SERVER['DOCUMENT_ROOT'].'/docs/';
			$config['allowed_types'] = 'gif|jpg|png';
			$config['overwrite'] = TRUE;
			$this->load->library('upload', $config);
			$this->upload->do_upload('cat_image');
			$filedata = $this->upload->data();
			if ( $filedata['file_ext'] == '.jpg' ||  $filedata['file_ext'] == '.gif' ||  $filedata['file_ext'] == '.png' ) 
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
			endif;
			/** End: Category icons **/

			$newid = $this->category_model->addCategory($file_name);
			$this->session->set_flashdata('notice','Category created <a href="' . site_url('category') . '">View categories</a> <a href="' . site_url('category/add') . '">Add another category</a> ' . $error);		

			redirect('/category/edit/'.$newid);
		endif;
	}

	#------------------------------------------------------
	# Update category details
	#------------------------------------------------------
	function edit() {

		$this->permissions->access('can_access_category_addedit');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
		
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('cat_hide');
		$this->form_validation->set_rules('cat_father_id');
		$this->form_validation->set_rules('cat_name', 'Category name', 'required');
		$this->form_validation->set_rules('cat_slug', 'Category slug', 'required');
		$this->form_validation->set_rules('cat_desc');
		$this->form_validation->set_rules('cat_excerpt');
		$this->form_validation->set_rules('cat_meta_title');
		$this->form_validation->set_rules('cat_custom_heading');
		$this->form_validation->set_rules('cat_meta_description');
		$this->form_validation->set_rules('cat_meta_keywords');
		$this->form_validation->set_rules('cat_meta_custom');

		if ($this->form_validation->run() == FALSE) :

			$data['cat'] = $this->category_model->getThisCategory($this->uri->segment(3));
	
			$data['title'] = 'Categories > Edit Category';

			$data['categories'] = $this->category_model->getAllCategories();

			$data['form_open'] = '<form action="'.site_url('/category/edit/'.$this->uri->segment(3)). $redirect->query_string . '" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Edit category';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = $redirect->link;
			
			$data['content'] = 'categories/category_edit';
			$this->load->view('global/template',$data);	
			
		else:
			
			/** Module: Category icons **/
			if (library_exists('categoryicons')):
			$config['upload_path'] = $_SERVER['DOCUMENT_ROOT'].'/docs/';
			$config['allowed_types'] = 'gif|jpg|png';
			$config['overwrite'] = TRUE;
			$this->load->library('upload', $config);
			$this->upload->do_upload('cat_image');
			$filedata = $this->upload->data();
			if ( $filedata['file_ext'] == '.jpg' ||  $filedata['file_ext'] == '.gif' ||  $filedata['file_ext'] == '.png' ) 
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
			endif;
			/** End: Category icons **/
			
			$this->category_model->updateCategory($_POST['cat_id'],$file_name);
			$this->category_model->redirection($_POST['cat_id']);
		
			//Clear db cache. We don't want to delete everything,
			//just the folder that applies.
			if ($this->config->item('caching') == 'true') {
			$cache_cat = $this->category_model->getThisCategory($this->input->post('cat_id'));
			delete_cache($cache_cat->cat_url);
			}

			$result = $this->google->generate();
			$this->session->set_flashdata('alert',$result);		

			if (strpos($redirect->link, 'admin/index.php')) {
				$this->session->set_flashdata('notice','Category information updated. <a href="' . $redirect->link . '">View categories</a> ' . $error);		
				redirect('/category/edit/' . $_POST['cat_id']);
			} else {
				redirect($redirect->link . '?shopit-notice=Category updated.');
			}
		endif;
	}

	#------------------------------------------------------
	# Delete category
	#------------------------------------------------------
	function delete() {

		$this->permissions->access('can_access_category_addedit');
		
		$data['title']	 = 'Categories > Delete Category';
		$data['categories'] = $this->category_model->getAllParents();
		$parent = $this->category_model->getThisCategory($this->uri->segment(3));
		$data['subcat_count']  = $this->category_model->subcat_count($this->uri->segment(3));
		$data['total_product_count'] = $this->category_model->all_product_count($this->uri->segment(3));
		
		$data['parent_cat'] = $parent->cat_name;
		$data['parent_id'] = $parent->cat_id;

		$data['form_open'] = '<form action="'.site_url('category/confirm_delete').'" method="post" enctype="multipart/form-data" >';
		$data['form_title'] = 'Delete category "'.$parent->cat_name.'"?';
		$data['form_close'] = '</form>';
		$data['form_cancel_link'] = site_url('category');
		$data['form_submit_label'] = 'Confirm Delete';

		$data['content'] = 'categories/category_delete';
		$this->load->view('global/template',$data);
		
	}

	#------------------------------------------------------
	# Category deletion
	# - recategorise products
	# - delete this category (and sub categories!)
	#------------------------------------------------------
	function confirm_delete() {

		$this->permissions->access('can_access_category_addedit');

		//recategorise products in this category ($_POST['this_cat_id']). Returns an array of cat_id
		$categories = $this->category_model->recategorise_products($_POST['this_cat_id']);
		
		//delete categories
		foreach ($categories as $category) {
			$this->category_model->delete_category($category->cat_id);
		}

		$this->session->set_flashdata('notice','Categories deleted.');
		$result = $this->google->generate();
		$this->session->set_flashdata('alert',$result);
	
		redirect('category');
	}

	#------------------------------------------------------
	# Sortable
	#------------------------------------------------------
	function sortable() {

		$categories = explode(';',$_POST['cat_id']);
		$order = 1;
		
		foreach ($categories as $cat_id) {
			if (!empty($cat_id)):
				$this->category_model->updateCategoryOrder($cat_id,$order++);
			endif;
		}
	
	}

	#------------------------------------------------------
	# Export CSV
	#------------------------------------------------------
	function export() {
		$this->permissions->access('can_access_category_exports');
		$data = $this->category_model->exportCategories();
		$filename = 'categories_'.date('Y-m-d').'.csv';
		force_download($filename, $data);
		break;
	}

}