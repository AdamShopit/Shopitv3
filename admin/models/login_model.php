<?php

class Login_model extends CI_Model {
	
	function Login_model() {
		parent::__construct();
		$this->load->helper('security');
	}

	#------------------------------------------------------
	# Check session exists
	#------------------------------------------------------
	function check_session() {
	
		if ($this->session->userdata('uid') AND $this->session->userdata('logged_in')==TRUE):
			return TRUE;
		else:
			return FALSE;
		endif;
		
	}

	#------------------------------------------------------
	# Check user exists in database
	# @param $username (string) - Submitted username
	# @param $password (string) - Submitted password
	#------------------------------------------------------
	function check_user($username, $password=null) {
	
		// Check if username exists and if so return their encrypted password here.
		$this->db->where('username', $username);
		$this->db->or_where('email', $username);
		$user_check = $this->db->get('users');
		
		if ($user_check->num_rows() > 0) {
			$user = $user_check->row();
		
			// Verify the passed $password is the same as the one in the database and
			// if it is return user's details, else return false
			if (!empty($password) and password_verify($password, $user->password)) {
				return $user;
			} else {
				return false;
			}
		} else {
			return false;
		}
	
	}

	#------------------------------------------------------
	# Access log - record log entry on every login
	#------------------------------------------------------
	function log($user=array(), $referer=null) {
		
		$data = array(
			'uid' 		 => $user->uid,
			'username' 	 => $user->username,
			'referer'	 => $referer,
			'user_agent' => $this->input->user_agent(),
			'ip_address' => $this->input->ip_address(),
			'timestamp'  => date('Y-m-d H:i:s'),
		);
		
		$this->db->insert('user_logs', $data);
		
	}

	#------------------------------------------------------
	# Get access logs
	#------------------------------------------------------
	function getLogs($interval=30) {
		
		$this->db->where("timestamp >= (DATE(NOW()) - INTERVAL $interval DAY)");
		$this->db->order_by('timestamp', 'desc');
		
		$query = $this->db->get('user_logs');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
		
	}

	#------------------------------------------------------
	# List users
	#------------------------------------------------------
	function listUsers() {
		
		$this->db->join('user_groups', 'users.group_id = user_groups.group_id', 'left');
		$this->db->order_by('uid','asc');
		$query = $this->db->get('users');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	}


	#------------------------------------------------------
	# Add user to database
	#------------------------------------------------------
	function createUser() {
	
		$data = array (
			'firstname'	=> $this->input->post('firstname'),
			'surname'	=> $this->input->post('surname'),
			'email'		=> $this->input->post('email'),
			'username'	=> $this->input->post('username'),
			'password'	=> password_hash($this->input->post('password', true), PASSWORD_DEFAULT),
			'group_id'	=> $this->input->post('group_id'),
		);
		$this->db->insert('users',$data);
	
	}

	#------------------------------------------------------
	# Delete user from database
	#------------------------------------------------------
	function deleteUser($uid) {
	
		$uid = base64_decode($uid);
		$uid = $this->encrypt->decode($uid);
	
		// Do not delete the account with ID 1
		if ($uid > 1) {
			$this->db->where('uid', $uid);
			$this->db->delete('users');
		}
	
	}

	#------------------------------------------------------
	# Get this user's details
	#------------------------------------------------------
	function getUser($uid) {
	
		$this->db->where('uid',$uid);
		$query = $this->db->get('users');
		return $query->row();
	
	}

	#------------------------------------------------------
	# Update user login details
	#------------------------------------------------------
	function updateUser($uid) {
	
		if ($this->permissions->access('can_manage_users', false)) {
			$data = array(
				'firstname'	=> $this->input->post('firstname'),
				'surname'	=> $this->input->post('surname'),
				'username'	=> $this->input->post('username'),
				'email'		=> $this->input->post('email'),
				'group_id'	=> $this->input->post('group_id'),
			);
		}
		
		//Only update the password if not blank
		if ($_POST['password'] != '' && $_POST['cpassword'] != ''){
			$data = array(
				'password' => password_hash($this->input->post('password', true), PASSWORD_DEFAULT),
			);
		}
		
		$this->db->where('uid', $uid);
		$this->db->update('users', $data);
	
	}

	#------------------------------------------------------
	# List user groups
	#------------------------------------------------------
	function listGroups($include_1=true) {
	
		$this->db->select('*, (select count(uid) from users where group_id = user_groups.group_id ) as members');
		if (!$include_1) {
			$this->db->where('group_id > ', 1);
		}
		$this->db->order_by('group_id','asc');
		$query = $this->db->get('user_groups');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
	}

	#------------------------------------------------------
	# List user group section columns
	# - These are the columns beginning with "can_".
	#------------------------------------------------------
	function listGroupColumns() {
	
		// Get all the columns of this table
		$columns = $this->db->list_fields('user_groups');
		
		// Loop through each column...
		foreach($columns as $field) {
			
			// And look for any fields beginning with 'can_'
			preg_match('@^can_(.*)@', $field, $matches);
			
			// If a match is found, then we can save this field's post data
			if ($matches[0] != "") {
				$data[] = $field;
			}
		
		}
		
		return $data;
		
	}

	#------------------------------------------------------
	# Get user group
	#------------------------------------------------------
	function getGroup($group_id) {
		
		$this->db->where('group_id', $group_id);
		$query = $this->db->get('user_groups');
		return $query->row();
		
	}

	#------------------------------------------------------
	# Create/update user group
	#------------------------------------------------------
	function updateGroup($insert=false) {
		
		$data['group_title'] = $this->input->post('group_title');
		$data['group_description'] = $this->input->post('group_description');

		// Get all the columns of this table
		$columns = $this->db->list_fields('user_groups');
		
		// Loop through each column...
		foreach($columns as $field) {
			
			// And look for any fields beginning with 'can_'
			preg_match('@^can_(.*)@', $field, $matches);
			
			// If a match is found, then we can save this field's post data
			if ($matches[0] != "") {
				$data[$field] = $this->input->post($field);
			}
		
		}
		
		// Uncomment the line below for testing
		#echo "<pre>" . print_r($data, true) . "</pre>";

		// Do the update or insert
		if ($insert) {
			$this->db->insert('user_groups', $data);
		} else {
			$group_id = base64_decode($this->input->post('group_id'));
			$group_id = $this->encrypt->decode($group_id);
			$this->db->where('group_id', $group_id);
			$this->db->update('user_groups', $data);
		}
		
	}
	
}