<?php

class Login extends CI_Controller {

	function Login()
	{
		parent::__construct();
		
		$this->load->database();
		
		$this->load->model('settings_model');
		$this->settings_model->initConfig();

		$this->load->model('login_model');
		$this->load->helper('security');
		$this->load->helper('cookie');
		$this->load->library('user_agent');
	}
	
	function index()
	{	
		
		if ($this->session->userdata('uid') AND $this->session->userdata('logged_in')==TRUE) {
		
			redirect('/dashboard');
		
		} else {
	
			//Check for post data
			if (isset($_POST['username']) AND isset($_POST['password'])) {
			
				//Check user exists in database
				$user = $this->login_model->check_user($this->input->post('username', true), $this->input->post('password', true));
			
				//If found in database, write to session
				if (!empty($user)) {
					
					//Set session
					$login_data = array(
						'username' 	=> $user->username,
						'uid'		=> $user->uid,
						'logged_in' => TRUE,
						'firstname' => $user->firstname,
						'lastname'	=> $user->surname,
						'email'		=> $user->email,
					); 
					
					$this->session->set_userdata($login_data);
					
					//Write to user access log table
					$this->login_model->log($user, $this->input->post('referer'));

					//Drop cookie to use on storefront
					//this will stop the incremental view count
					//and is also used for the floating storefront admin bar
					
					$cookie_value = array(
						'firstname' => $user->firstname,
						'lastname'	=> $user->surname,
						'email'		=> $user->email,
						'uid'		=> $user->uid,
					);
					$cookie = array(
						'name' 	 => 'shopit_mgnt',
						'value'  => $this->encrypt->encode(serialize($cookie_value)),
						'expire' => '0',  
					);
					
					set_cookie($cookie);
					//Redirect to dashboard
					redirect('/dashboard');
					
				} else {
					
					//Redirect back to login form
					$this->session->set_flashdata('notice',"Oops, something wasn't right. Please try again.");		
					redirect('/');
				
				}
			
			} else {
				//Display login form when no post data
				$data['title'] = 'Login';
				$this->load->view('login/login_form',$data);
			}
		
		}
	}


	function logout() {
	
		$this->session->unset_userdata('username');
		$this->session->unset_userdata('uid');
		$this->session->unset_userdata('logged_in');
		$this->session->unset_userdata('auth_type');
		$this->session->unset_userdata('firstname');
		$this->session->unset_userdata('surname');
		$this->session->unset_userdata('email');
		delete_cookie('shopit_mgnt');

		$this->session->set_flashdata('notice','You have logged out successfully. See you soon!');		
		redirect('/');
	
	}
		
}