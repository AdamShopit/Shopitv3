<?php
class Pages extends CI_Controller {

	function Pages() {
		parent::__construct();
		
		$this->load->database();

		$this->load->model('settings_model');
		$this->settings_model->initConfig();
		
		$this->load->model('pages_model');
		$this->load->model('inventory_model');
		$this->load->model('google_model','google');

		/* Login check */
		$this->load->model('login_model');
		if(!$this->login_model->check_session()){
			redirect('/login');
		}
		/* End: Login check */
	}

	function index() {
	
		$this->permissions->access('can_access_pages');
		
		$data['title']	 = 'Pages';
		$data['form_title'] = 'Pages';
		$data['content'] = 'pages/pages_list';
		
		$data['channels'] = $this->pages_model->getPageSites();
				
		$this->load->view('global/template',$data);
	
	}

	#------------------------------------------------------
	# Add page to database
	#------------------------------------------------------
	function create() {

		$this->permissions->access('can_access_pages');
		
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('page_visible');
		$this->form_validation->set_rules('page_sitemap');
		$this->form_validation->set_rules('page_site');
		$this->form_validation->set_rules('page_title', 'Page title', 'required');
		$this->form_validation->set_rules('page_redirect');
		$this->form_validation->set_rules('page_content');
		$this->form_validation->set_rules('page_order');
		$this->form_validation->set_rules('page_template');
		$this->form_validation->set_rules('page_meta_title');
		$this->form_validation->set_rules('page_custom_heading');
		$this->form_validation->set_rules('page_meta_description');
		$this->form_validation->set_rules('page_meta_keywords');
		$this->form_validation->set_rules('page_meta_custom');

		if ($this->form_validation->run() == FALSE):

			//Channels
			$data['locations'] = $this->inventory_model->getLocations();

			$data['title']	 	= 'Pages > Create New Page';
			$data['form_open'] 	= '<form action="'.site_url('pages/create').'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Create new page';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = site_url('pages');
			$data['content'] = 'pages/pages_edit';
			$this->load->view('global/template',$data);
		
		else:

			$this->pages_model->addPage();
			$result = $this->google->generate();
			$this->session->set_flashdata('alert',$result);
			$this->session->set_flashdata('notice','Page created. <a href="' . site_url('pages') . '">Manage pages</a>');		
			redirect('/pages/edit/' . $this->db->insert_id());
			
		endif;
	}


	#------------------------------------------------------
	# Delete page
	#------------------------------------------------------
	function delete() {

		$this->permissions->access('can_access_pages');
	
		$this->pages_model->deletePage($this->uri->segment(3));
		$result = $this->google->generate();
		$this->session->set_flashdata('alert',$result);
	
	}	

	#------------------------------------------------------
	# Update page
	#------------------------------------------------------
	function edit() {

		$this->permissions->access('can_access_pages');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
	
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('page_visible');
		$this->form_validation->set_rules('page_sitemap');
		$this->form_validation->set_rules('page_site');
		$this->form_validation->set_rules('page_title', 'Page title', 'required');
		if ($this->input->post('page_redirect') == '') {
			$this->form_validation->set_rules('page_slug', 'Page slug', 'required');
		} else {
			$this->form_validation->set_rules('page_slug');
		}
		$this->form_validation->set_rules('page_redirect');
		$this->form_validation->set_rules('page_content');
		$this->form_validation->set_rules('page_order');
		$this->form_validation->set_rules('page_template');
		$this->form_validation->set_rules('page_meta_title');
		$this->form_validation->set_rules('page_custom_heading');
		$this->form_validation->set_rules('page_meta_description');
		$this->form_validation->set_rules('page_meta_keywords');
		$this->form_validation->set_rules('page_meta_custom');

		if ($this->form_validation->run() == FALSE):

			//Channels
			$data['locations'] = $this->inventory_model->getLocations();

			$data['title']	 	= 'Pages > Edit Page';
			$data['form_open'] 	= '<form action="'.site_url('pages/edit/'.$this->uri->segment(3)).$redirect->query_string.'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Edit page';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = ($redirect->link != "") ? $redirect->link : site_url('pages');

			$data['content'] = 'pages/pages_edit';
			$data['document'] = $this->pages_model->getPage($this->uri->segment(3));
	
			$this->load->view('global/template',$data);

		else:
		
			$new_page_slug = $this->pages_model->updatePage($_POST['page_id']);
			$this->pages_model->redirection($this->input->post('page_id'), $new_page_slug);
			
			$result = $this->google->generate();
			$this->session->set_flashdata('alert',$result);
			
			if ($redirect->link != "") {
				redirect($redirect->link . '?shopit-notice=Page updated.');
			} else {
				$this->session->set_flashdata('notice','Page updated. <a href="' . site_url('pages') . '">Manage pages</a>');
				redirect('pages/edit/' . $_POST['page_id']);
			}
		
		endif;
	
	}

	#------------------------------------------------------
	# Ajax: Edit page_slug
	#------------------------------------------------------
	function editslug() {
			
		if (!empty($_POST['id'])):

			$page_slug = $this->pages_model->check_slug($_POST['value']);
			
			$data = array(
						'page_slug' => $page_slug,
					);
			
			$this->db->where('page_id',$_POST['id']);
			$this->db->update('pages',$data);
		
			print $page_slug;
		
		endif;
	}

	#------------------------------------------------------
	# Sortable
	#------------------------------------------------------
	function sortable() {

		$pages = explode(';',$_POST['page_id']);
		$order = 1;
		
		foreach ($pages as $page_id) {
			if (!empty($page_id)):
				$this->pages_model->updatePageOrder($page_id,$order++);
			endif;
		}
	
	}

	#------------------------------------------------------
	# Snippets
	#------------------------------------------------------
	function snippets() {

		$this->permissions->access('can_access_snippets');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();

		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('snippet_label', 'Label', 'trim|required');
		$this->form_validation->set_rules('snippet_title', 'Title', 'trim|required');
		$this->form_validation->set_rules('snippet_content', 'Content', 'trim');
		$this->form_validation->set_rules('snippet_notes', 'Notes', 'trim');
		$this->form_validation->set_rules('snippet_early_parsing', 'Early parsing?', 'trim');
		$this->form_validation->set_rules('group_id', 'Snippet Group', 'trim');

		if ($this->form_validation->run() == FALSE):

			$data['title'] = 'Snippets';
	
			$data['snippet_groups'] = $this->pages_model->listAllSnippetGroups();
			$data['groups'] = $this->pages_model->getSnippetGroups();
			
			//Get custom field data to edit
			if ($this->uri->segment(3) != '') {
				$data['edit'] = $this->pages_model->getSnippet($this->uri->segment(3));
			}
			
			$data['form_open'] = '<form action="'.current_url().$redirect->query_string.'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Manage Snippets';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = ($redirect->link != "") ? $redirect->link : site_url('pages');
			
			$data['content'] = 'pages/snippets';
			$this->load->view('global/template',$data);		
		
		else:
		
			//Save and redirect
			if ($this->input->post('snippet_id') != '') {
				$this->pages_model->updateSnippet($this->input->post('snippet_id'));
			} else {
				$this->pages_model->createSnippet();
			}
			
			if ($redirect->link != "") {
				redirect($redirect->link . '?shopit-notice=Snippet updated.');
			} else {
				$this->session->set_flashdata('notice','Snippet updated.');
				redirect('pages/snippets');
			}
		
		endif;
	}

	function deletesnippet() {
		$this->permissions->access('can_access_snippets');
		$this->pages_model->deleteSnippet($this->uri->segment(3));
		$this->session->set_flashdata('notice','Snippet deleted.');
		redirect('pages/snippets');
	}

	#------------------------------------------------------
	# Snippet groups
	#------------------------------------------------------
	function snippetgroups() {

		$this->permissions->access('can_access_snippets');
	
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('label', 'Label', 'trim|required');

		if ($this->form_validation->run() == FALSE):

			$data['title'] = 'Snippet Groups';
	
			$data['groups'] = $this->pages_model->getSnippetGroups();
			
			//Get custom field data to edit
			if ($this->uri->segment(3) != '') {
				$data['edit'] = $this->pages_model->getSnippetGroup($this->uri->segment(3));
			}
			
			$data['form_open'] = '<form action="'.current_url().'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Manage Snippet Groups';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = site_url('pages');
			
			$data['content'] = 'pages/snippets_group';
			$this->load->view('global/template',$data);		
		
		else:
		
			//Save and redirect
			if ($this->input->post('group_id') != '') {
				$this->pages_model->updateSnippetGroup($this->input->post('group_id'));
			} else {
				$this->pages_model->createSnippetGroup();
			}
			$this->session->set_flashdata('notice','Group updated.');
			redirect('pages/snippetgroups');
		
		endif;

	}

	function deletesnippetgroup() {
		$this->permissions->access('can_access_snippets');
		$this->pages_model->deleteSnippetGroup($this->uri->segment(3));
		$this->session->set_flashdata('notice','Snippet group deleted.');
		redirect('pages/snippetgroups');
	}
	
	#------------------------------------------------------
	# Snippet Widgets Interface/Ajax
	# - These functions/methods are used in conjunction
	#   with the new admin bar/editing options on the 
	#   store-front.
	#------------------------------------------------------
	// Load widget options form. This would be displayed within
	// a popup/modal within the store front.
	function widgetform(){

		$this->permissions->access('can_access_snippets');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();

		$data['categories'] = $this->category_model->getAvailableCategories(NULL, TRUE);
		$data['ajax_post_url'] = site_url('pages/ajax_widgetform_inventory');
		$data['form_action'] = site_url('pages/widget_save'.$redirect->query_string);
		
		// Display our template
		$this->load->view('pages/snippets_widgetform', $data);
		
	}
	
	// Ajax load inventory items based on the passed category Id
	function ajax_widgetform_inventory() {
		
		$cat_id = $this->input->post('cat_id');
		$channel_id = ($this->input->get('channel_id') != '') ? $this->input->get('channel_id') : '1';
		
		$this->db->select('product_id, product_name');
		$this->db->from('inventory');
		$this->db->where('cat_id', $cat_id);
		$this->db->where('product_type !=', 'variant');
		$this->db->where('product_disabled', 0);
		$this->db->where("channel_$channel_id", 1);
		$this->db->order_by('product_name');
		
		$query = $this->db->get();
		
		if ($query->num_rows() > 0) {
			
			echo '<select name="shopit_console_snippet_data">' . "\n";
			echo '<option value="">Choose product...</option>' . "\n";
			
			foreach ($query->result() as $item) {
				echo sprintf('<option value="%d">%s</option>' . "\n", $item->product_id, $item->product_name);
			}
			
			echo "</select>\n";
			
		} else {
			echo "";
		}
		
	}
	
	// Save snippet widget data
	function widget_save() {

		$this->permissions->access('can_access_snippets');
		
		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
		
		// There's two important POST vars sent: shopit_console_snippet_data 
		// and shopit_console_widget_type. Both these variables to be included
		// in the serailised data string we'll be saving to the database.
		$data = array(
			'type' => $this->input->post('shopit_console_widget_type'),
			'content' => $this->input->post('shopit_console_snippet_data'),
		);
		
		$data = serialize($data);
		
		// Save to database
		$this->pages_model->updateSnippetContent($this->input->post('snippet_id'), $data);
		
		// Redirect back to the page we were on
		redirect($redirect->link . '?shopit-notice=Snippet updated.');
		
	}
		
}