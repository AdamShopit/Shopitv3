<?php
class Dashboard_model extends CI_Model {
	
	function Dashboard_model() {
		parent::__construct();
	}

	#------------------------------------------------------
	# Check for widgets for this user
	# - returns true/false
	#------------------------------------------------------
	function hasWidgets() {
		
		$this->db->select('id');
		$this->db->where('uid', $this->session->userdata('uid'));
		$this->db->where('active', 1);
		$query = $this->db->get('dashboard');
		
		if ($query->num_rows() > 0){
			return true;
		} else {
			return false;
		}
		
	}

	#------------------------------------------------------
	# Get my widgets
	#------------------------------------------------------
	function myWidgets() {
		
		$this->db->where('uid', $this->session->userdata('uid'));
		$this->db->where('active', 1);
		$this->db->order_by('dashboard.order');
		$query = $this->db->get('dashboard');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
		
	}
	
	#------------------------------------------------------
	# Save widgets
	#------------------------------------------------------
	function saveWidgets() {
		
		//Check if this user has existing widgets already
		$has_existing_widgets = $this->hasWidgets();
	
		if (!empty($_POST['widget'])) {
		
			//Get my widgets before save
			$mywidgets_before = $this->myWidgets();
			
			if ($has_existing_widgets == true):
				foreach ($mywidgets_before as $mywidget_before) {
					//creates a new array containing only the titles of existing widgets
					$mywidget_before_array[] = $mywidget_before->widget; 
				}
			else:
				$mywidget_before_array[] = array(); 
			endif;
						
			//Loop through widget post
			while (list($order, $widget) = each($_POST['widget']) and list($key, $id) = each($_POST['id'])) {
						
				$data = array(
					'uid' 	 => $this->session->userdata('uid'),
					'widget' => $widget,
					'order'  => $order,
					'active' => 1,
				);
				
				//Insert or update widgets
				if ($id == '0') { 
					//is a new widget
					$this->db->insert('dashboard', $data);
				} else { 
					//is existing widget
					$this->db->where('id', $id);
					$this->db->update('dashboard', $data);
				}			
						
				//creates a new array containing only the titles of the new list of widgets
				$mywidget_after_array[] = $widget;
			
			}
			
			//Check the differences in the before and after arrays if
			//there are existing widgets available
			if ($has_existing_widgets == true):
			
				$compare_arrays = array_diff($mywidget_before_array, $mywidget_after_array);
						
				if (!empty($compare_arrays)) {
					foreach ($compare_arrays as $delete_widget){
						$this->db->where('uid',$this->session->userdata('uid'));
						$this->db->where('widget', $delete_widget);
						$this->db->delete('dashboard');
					}
				}
			
			endif;
	
		}
	
	}
	
}