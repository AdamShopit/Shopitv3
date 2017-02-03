<?php
class Users extends CI_Controller {

	function Users() {
		parent::__construct();
		
		$this->load->database();

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
	# List users
	#------------------------------------------------------
	function index() {
	
		$this->permissions->access('can_manage_users');
	
		$data['title']	 = 'Users';
		$data['content'] = 'users/users_list';
		
		$data['users'] = $this->login_model->listUsers();
				
		$this->load->view('global/template',$data);
	
	}

	#------------------------------------------------------
	# Access log - get the last 30 days worth
	#------------------------------------------------------
	function log() {
		
		$this->permissions->access('can_manage_users');
		
		$data['title'] = 'User Access Log';
		$data['content'] = 'users/access_log';
		
		$data['logs'] = $this->login_model->getLogs();

		$this->load->view('global/template', $data);
		
	}
	
	#------------------------------------------------------
	# Add user to database
	#------------------------------------------------------
	function create() {

		$this->permissions->access('can_manage_users');

		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('firstname', 'First name', 'trim|required');
		$this->form_validation->set_rules('surname', 'Surname', 'trim');
		$this->form_validation->set_rules('email', 'email', 'trim|valid_email|required');
		$this->form_validation->set_rules('username', 'Username', 'trim|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|required');
		$this->form_validation->set_rules('cpassword', 'Confirm password', 'trim|required|matches[password]');
		$this->form_validation->set_rules('auth_type');

		if ($this->form_validation->run() == FALSE):

			// Get list of all groups
			$data['user_groups'] = $this->login_model->listGroups(FALSE);
		
			$data['title']	 	= 'Users > Create User';
			$data['form_open'] 	= '<form action="'.site_url('users/create').'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Create new user';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = site_url('users');
			
			$data['content'] = 'users/users_form';
			$this->load->view('global/template',$data);
		
		else:
		
			$this->login_model->createUser();
			$this->session->set_flashdata('notice','User created.');		
			redirect('/users');
		
		endif;
	
	}

	#------------------------------------------------------
	# Delete user
	#------------------------------------------------------
	function delete() {
			
		$this->permissions->access('can_manage_users');
		$this->login_model->deleteUser($this->uri->segment(3));
	
	}

	#------------------------------------------------------
	# Change user login details/password
	#------------------------------------------------------
	function update() {

		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		if ($this->permissions->access('can_manage_users', false)) {
			$this->form_validation->set_rules('firstname', 'First name', 'trim|required');
			$this->form_validation->set_rules('surname', 'Surname', 'trim');
			$this->form_validation->set_rules('email', 'email', 'trim|valid_email|required');
			$this->form_validation->set_rules('username', 'Username', 'trim|required');
			$this->form_validation->set_rules('password', 'Password', 'trim');
			$this->form_validation->set_rules('cpassword', 'Confirm password', 'trim|matches[password]');
			$this->form_validation->set_rules('auth_type');
		} else {
			$this->form_validation->set_rules('password', 'Password', 'trim|required');
			$this->form_validation->set_rules('cpassword', 'Confirm password', 'trim|required|matches[password]');
		}

		// Decode the url segment that contains our user id
		$encoded_segment = $this->uri->segment(3);
		$user_id = base64_decode($this->uri->segment(3));
		$user_id = $this->encrypt->decode($user_id);

		if ($this->form_validation->run() == FALSE):
			
			// Get this user
			$data['user'] = $this->login_model->getUser($user_id); 
			
			// Get list of all groups
			$data['user_groups'] = $this->login_model->listGroups(FALSE);
			
			if ($this->permissions->access('can_manage_users', false)) {
				$data['title']	 	= 'Users > Update User';
				$data['form_title'] = 'Update user';
			} else {
				$data['title']	 	= 'Users > Change Password';
				$data['form_title'] = 'Change password';
			}
			
			$data['form_open'] 	= '<form action="'.site_url('users/update/'.$encoded_segment).'" method="post" enctype="multipart/form-data" >';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = site_url('users');

			$data['content'] = 'users/users_form';
			$this->load->view('global/template', $data);
		
		else:
		
			$this->login_model->updateUser($user_id);
			$this->session->set_flashdata('notice','User updated.');
			if ($this->permissions->access('can_manage_users', false)){
				redirect('users');
			} else {
				redirect('logout');
			}
		
		endif;
	
	}

	#------------------------------------------------------
	# Manage Groups
	#------------------------------------------------------
	function groups() {

		$this->permissions->access('can_manage_users');
		
		switch($this->uri->segment(3)) {
			
			// Edit an existing group
			case 'edit':
				
				if ($this->uri->segment(4) == '') {
					redirect('users/groups');
					break;
				}
				
				$group_id = base64_decode($this->uri->segment(4));
				$group_id = $this->encrypt->decode($group_id);
				
				// Get this groups data
				$data['group'] = $this->login_model->getGroup($group_id);

				// Configure form validation
				$this->form_validation->set_message('required', 'required');
				$this->form_validation->set_message('valid_email', 'invalid email');
				$this->form_validation->set_message('max_length', ' ');
				$this->form_validation->set_message('exact_length', ' ');
				$this->form_validation->set_message('numeric', 'invalid');
				$this->form_validation->set_message('matches', 'not matching!');
				$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		
				$this->form_validation->set_rules('group_title', 'Title', 'required');
				$this->form_validation->set_rules('group_title', 'Title', 'required');
				$columns = $this->login_model->listGroupColumns();
				foreach($columns as $field) {
				$this->form_validation->set_rules($field);
				}

				if ($this->form_validation->run() == FALSE) {
					// Load the correct templates
					$data['form_open'] 			= '<form action="'.site_url("users/groups/edit/$group_id").'" method="post" enctype="multipart/form-data" >';
					$data['form_title'] 		= 'Manage User Group';
					$data['form_close'] 		= '</form>';
					$data['form_cancel_link'] 	= site_url('users/groups');
					$data['title']	 			= 'User Groups > Edit';
					$data['content'] 			= 'users/groups_edit';
				} else {
					$this->login_model->updateGroup();
					$this->session->set_flashdata('notice', 'User group updated.');
					redirect('users/groups');
				}
				break;
				
			case 'create':
				// Configure form validation
				$this->form_validation->set_message('required', 'required');
				$this->form_validation->set_message('valid_email', 'invalid email');
				$this->form_validation->set_message('max_length', ' ');
				$this->form_validation->set_message('exact_length', ' ');
				$this->form_validation->set_message('numeric', 'invalid');
				$this->form_validation->set_message('matches', 'not matching!');
				$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
		
				$this->form_validation->set_rules('group_title', 'Title', 'required');
				$columns = $this->login_model->listGroupColumns();
				foreach($columns as $field) {
					$this->form_validation->set_rules($field);
					
					// Tick the checkboxes by default when creating a new group
					$data['group'] = (object) array($field => "0");
				}
				
				if ($this->form_validation->run() == FALSE) {
					// Load the correct templates
					$data['form_open'] 			= '<form action="'.site_url('users/groups/create').'" method="post" enctype="multipart/form-data" >';
					$data['form_title'] 		= 'Create User Group';
					$data['form_close'] 		= '</form>';
					$data['form_cancel_link'] 	= site_url('users/groups');
					$data['title']	 			= 'User Groups > Create';
					$data['content'] 			= 'users/groups_edit';
				} else {
					$do_insert = true;
					$this->login_model->updateGroup($do_insert);
					$this->session->set_flashdata('notice', 'User group created.');
					redirect('users/groups');
				}
				break;
			
			// Show the list of user groups
			default:
				// Get list of all groups
				$data['user_groups'] = $this->login_model->listGroups();
				
				// Load the correct templates
				$data['title']	 = 'User Groups';
				$data['content'] = 'users/groups_list';
				break;
		}
		
		$this->load->view('global/template', $data);
		
	}

}