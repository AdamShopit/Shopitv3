<?php
class Collections extends CI_Controller {

	function Collections() {
		parent::__construct();
		
		$this->load->database();

		$this->load->model('settings_model');
		$this->settings_model->initConfig();

		$this->load->model('collections_model');
		$this->load->model('inventory_model');

		/* Login check */
		$this->load->model('login_model');
		if(!$this->login_model->check_session()){
			redirect('/login');
		}
		/* End: Login check */

	}

	#------------------------------------------------------
	# Collections List
	#------------------------------------------------------
	function index() {

		$this->permissions->access('can_access_collections');
		
		$data['title']	 = 'Collections';

		$data['collection_groups'] = $this->collections_model->getCollectionGroups();
		
		if ($data['collection_groups'] > 0):
			$data['content'] = 'inventory/collections_list';
		else:
			$data['content'] = 'inventory/collections_empty';
		endif;
		$this->load->view('global/template',$data);
	
	}

	#------------------------------------------------------
	# Add collection to database
	#------------------------------------------------------
	function create() {

		$this->permissions->access('can_access_collections');

		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('collection_group');
		$this->form_validation->set_rules('collection_name', 'Collection name', 'required');
		$this->form_validation->set_rules('collection_desc');
		$this->form_validation->set_rules('collection_meta_title');
		$this->form_validation->set_rules('collection_custom_heading');
		$this->form_validation->set_rules('collection_meta_description');
		$this->form_validation->set_rules('collection_meta_keywords');
		$this->form_validation->set_rules('collection_meta_custom');

		if ($this->form_validation->run() == FALSE):
		
			$data['groups'] = $this->collections_model->getGroups();
		
			$data['title']	 = 'Collections > Create Collection';
			$data['form_open'] = '<form action="'.site_url('collections/create').'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Create new collection';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = site_url('collections');
			$data['content'] = 'inventory/collections_edit';
			$this->load->view('global/template',$data);
			
		else:

			//Clear db cache
			if ($this->config->item('caching') == 'true') {
			delete_cache();
			}

			// Collection image
			$config['upload_path'] = $_SERVER['DOCUMENT_ROOT'].'/docs/';
			$config['allowed_types'] = 'gif|jpg|png';
			$config['overwrite'] = TRUE;
			$this->load->library('upload', $config);
			$this->upload->do_upload('collection_image');
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
		
			$newid = $this->collections_model->addCollection($file_name);
			$this->session->set_flashdata('notice','Collection created. <a href="'.site_url('collections').'">Manage collections</a> ');
			if (!empty($error)) {
				$this->session->set_flashdata('alert', $error);
			}
			redirect('collections/edit/' . $newid);
		
		endif;
	}

	#------------------------------------------------------
	# Display contents of collections
	#------------------------------------------------------
	function manage() {

		$this->permissions->access('can_access_collections');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
		$data['redirect'] = ($redirect->link != "") ? $redirect->link : site_url('collections');
	
		$data['title']	 = 'Collections > Manage';
		$collection = $this->collections_model->getCollection($this->uri->segment(3));

		$data['collection_name'] = $collection->collection_name;
		$data['collection_id']	 = $collection->collection_id;

		$data['products'] = $this->collections_model->getItemsInCollection($this->uri->segment(3));

		$data['content'] = 'inventory/collections_manage';
		
		$this->load->view('global/template',$data);
		
	}

	#------------------------------------------------------
	# Remove item from collection
	# - uri segment(3) = collection_id
	# - uri segment(4) = product_id
	#------------------------------------------------------
	function removeitem() {

		$this->permissions->access('can_access_collections');

		//update the database
		$this->collections_model->removeItemFromCollection($this->uri->segment(3),$this->uri->segment(4));		

		//Clear db cache
		if ($this->config->item('caching') == 'true') {
		delete_cache();
		}
	
	}

	#------------------------------------------------------
	# Delete collection
	#------------------------------------------------------
	function delete() {

		$this->permissions->access('can_access_collections');
	
		$this->collections_model->deleteCollection($this->uri->segment(3));

		//Clear db cache
		if ($this->config->item('caching') == 'true') {
		delete_cache();
		}
	
	}	

	#------------------------------------------------------
	# Update collection
	#------------------------------------------------------
	function edit() {

		$this->permissions->access('can_access_collections');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
	
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('collection_group');
		$this->form_validation->set_rules('collection_name', 'Collection name', 'required');
		$this->form_validation->set_rules('collection_slug', 'Collection slug', 'required');
		$this->form_validation->set_rules('collection_desc');
		$this->form_validation->set_rules('collection_meta_title');
		$this->form_validation->set_rules('collection_custom_heading');
		$this->form_validation->set_rules('collection_meta_description');
		$this->form_validation->set_rules('collection_meta_keywords');
		$this->form_validation->set_rules('collection_meta_custom');

		if ($this->form_validation->run() == FALSE):

			$data['groups'] = $this->collections_model->getGroups();
		
			$data['title']	 = 'Collections > Edit Collection';
			$data['form_open'] = '<form action="'.site_url('collections/edit/'.$this->uri->segment(3)).$redirect->query_string.'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Edit collection';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = ($redirect->link != "") ? $redirect->link : site_url('collections');
			$data['content'] = 'inventory/collections_edit';
			$data['collection'] = $this->collections_model->getCollection($this->uri->segment(3));
	
			$this->load->view('global/template',$data);
	
		else:

			// Collection images
			$config['upload_path'] = $_SERVER['DOCUMENT_ROOT'].'/docs/';
			$config['allowed_types'] = 'gif|jpg|png';
			$config['overwrite'] = TRUE;
			$this->load->library('upload', $config);
			$this->upload->do_upload('collection_image');
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
		
			$this->collections_model->updateCollection($_POST['collection_id'], $file_name);
			$this->session->set_flashdata('notice','Collection updated. <a href="'.site_url('collections').'">Manage collections</a>');
			if (!empty($error)) {
				$this->session->set_flashdata('alert', $error);
			}

			//Clear db cache
			if ($this->config->item('caching') == 'true') {
			delete_cache();
			}

			if ($redirect->link != "") {
				redirect($redirect->link . '?shopit-notice=Collection updated.');
			} else {
				redirect('collections/edit/'.$_POST['collection_id']);
			}
		
		endif;
	
	}

	#------------------------------------------------------
	# Ajax: Edit colections_slug
	#------------------------------------------------------
	function editslug() {
			
		if (!empty($_POST['id'])):

			$page_slug = $this->collections_model->check_slug($_POST['value']);
			
			$data = array(
						'collection_slug' => $page_slug,
					);
			
			$this->db->where('collection_id',$_POST['id']);
			$this->db->update('collections',$data);
		
			print $page_slug;
		
		endif;
	}

	#------------------------------------------------------
	# Ajax: Sortable collection items
	#------------------------------------------------------
	function sortable() {
	
		//If manual post via submit button
		if (isset($_POST['submit']) || isset($_POST['submit_x'])) {
		
			while(list($product_id_key,$product_id)=each($_POST['product_id'])) {
				$this->collections_model->updateCollectionOrder($_POST['collection_id'],$product_id,$product_id_key);			
			}

			redirect('collections/manage/' . $_POST['collection_id']);
		
		}
		//else ajax submission
		else {
		
			$collection_items = explode(';',$_POST['collection_items']);
			$order = 1;
			
			foreach ($collection_items as $product_id) {
				if (!empty($product_id)):
					$this->collections_model->updateCollectionOrder($_POST['collection_id'],$product_id,$order++);
				endif;
			}
		
		}

	}

	#------------------------------------------------------
	# Ajax: Sort collections (list)
	#------------------------------------------------------
	function sortlist() {

		$list = explode(';', $_POST['collection_id']);
		$order = 1;
		
		foreach ($list as $collection_id) {
			if (!empty($collection_id)):
				$this->collections_model->sortCollectionList($collection_id, $order++);
			endif;
		}
	
	}

	#------------------------------------------------------
	# Collection groups
	#------------------------------------------------------
	function groups() {

		$this->permissions->access('can_access_collections');
	
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('group_label', 'Label', 'trim|required');

		if ($this->form_validation->run() == FALSE):

			$data['title'] = 'Collection Groups';
	
			$data['groups'] = $this->collections_model->getGroups();
			
			//Get custom field data to edit
			if ($this->uri->segment(3) != '') {
				$data['edit'] = $this->collections_model->getGroup($this->uri->segment(3));
			}
			
			$data['form_open'] = '<form action="'.current_url().'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Manage Groups';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = site_url('pages');
			
			$data['content'] = 'inventory/collections_group';
			$this->load->view('global/template',$data);		
		
		else:
		
			//Save and redirect
			if ($this->input->post('group_id') != '') {
				$this->collections_model->updateGroup($this->input->post('group_id'));
			} else {
				$this->collections_model->createGroup();
			}
			$this->session->set_flashdata('notice','Group updated.');
			redirect('collections/groups');
		
		endif;

	}

	function deletegroup() {
		$this->permissions->access('can_access_collections');
		$this->collections_model->deleteGroup($this->uri->segment(3));
		$this->session->set_flashdata('notice','Group deleted.');
		redirect('collections/groups');
	}

}