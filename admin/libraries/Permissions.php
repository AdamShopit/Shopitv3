<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 
#------------------------------------------------------
# Library to manage user permissions
#------------------------------------------------------
class Permissions {
	
	#------------------------------------------------------
	# Initialise our variables
	#------------------------------------------------------
#	function initialize($params = array()) {
#
#		if (count($params) == 0) return;
#		
#		foreach ($params as $key => $val) {
#			$this->$key = $val;			
#		}
#		
#	}

	#------------------------------------------------------
	# Set Config Items
	# Returns an array of permission fields and settings
	# for this user - we use this function in the settings
	# model to set config items
	#------------------------------------------------------
	function init_config() {

		$shopit =& get_instance();
		
		$data = array();
		
		$sql = "SELECT user_groups.*
				FROM users
				JOIN user_groups ON users.group_id = user_groups.group_id
				WHERE uid = " . $shopit->session->userdata('uid');

		$query = $shopit->db->query($sql);

		foreach ($query->list_fields() as $field) {
			
			// Only get the fields beginning with "can_" and add to an array
			if (strpos($field, 'can_') !== FALSE) {
				$data[$field] = (bool) $query->row()->$field;
				
			}
			
		}
		
		return $data;
		
	}

	#------------------------------------------------------
	# Access Permissions
	# @$name = name of the field in user_groups table
	# @$show_error = set whether to redirect to error page
	#------------------------------------------------------
	function access($name, $show_error=true, $group_id_only=null) {
	
		$shopit =& get_instance();
		
		// Get the current user's permissions
		$permissions = $this->get( $shopit->session->userdata('uid') );
		
		// If the current user matches the database field, then 
		// view the page else redirect them to a 'unauthorised'
		// message. If $name contains the string "_module_", then
		// we can also check if the library is installed. We'll do
		// this part first.
		
		// Check if this is a module and if so get it's name
		preg_match('@can_module_(.*)@', $name, $matches);
		$module_installed = ($matches[0] != "") ? library_exists($matches[1]) : TRUE;
		
		// Is this permission for a particular group_id only?
		if ($group_id_only != null) {
			if ($permissions->group_id == $group_id_only) {
				$uid_access_only = true;
			} else {
				$uid_access_only = false;
			}
		} else {
			$uid_access_only = true;
		}
		
		if ($permissions->$name == 1 && $module_installed && $uid_access_only) {
		
			return TRUE;
			
		} else {
		
			if ($show_error) {
				show_error('You do not have the necessary permissions to view this page.');
			} else {
				return FALSE;
			}
			
			break;
			
		}
		
	}

	#------------------------------------------------------
	# Get Permissions (of the current user)
	# @$uid = user id as set in the users table for this user
	#------------------------------------------------------
	function get($uid) {
	
		$shopit =& get_instance();
		
		$shopit->db->select('user_groups.*');
		$shopit->db->from('users');
		$shopit->db->join('user_groups', 'users.group_id = user_groups.group_id');
		$shopit->db->where('uid', $uid);
		$query = $shopit->db->get();
	
		return $query->row();
		
	}

	#------------------------------------------------------
	# Widget Permission
	# @$name = name of the field in user_groups table. This
	# is already prefixed with "can_access_dashwidgets_".
	#------------------------------------------------------
	function widget($name) {
	
		$shopit =& get_instance();
		
		// Get the current user's permissions
		$permissions = $this->get( $shopit->session->userdata('uid') );
		
		// Add the prefix
		$name = "can_access_dashwidgets_$name";
		
		if ($permissions->$name == 1) {
			return TRUE;
		} else {
			return FALSE;
		}
	
	}

}