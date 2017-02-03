<?php
class Filters_model extends CI_Model {
	
	function Filters_model() {
		parent::__construct();
		
	}

	#------------------------------------------------------
	# List filter groups
	#------------------------------------------------------
	function groups($cat_id) {
		
		$this->db->where('cat_id', $cat_id);
		$this->db->order_by('group_order');
		$query = $this->db->get('filter_groups');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
		
	}

	#------------------------------------------------------
	# List filter options
	#------------------------------------------------------
	function options($group_id) {
		
		$this->db->select('filter_definitions.*');
		$this->db->from('filter_definitions');
		$this->db->join('filter_groups', 'filter_groups.group_id = filter_definitions.group_id');
		$this->db->where('filter_definitions.group_id', $group_id);
		$this->db->order_by('filter_order');
		$query = $this->db->get();
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
		
	}

	#------------------------------------------------------
	# Create filter group
	# - Returns inserted group_id
	#------------------------------------------------------
	function createGroup($data=array()) {
		
		if (!empty($data) && $data['label'] != "") {
			$this->db->insert('filter_groups', $data);
			$group_id = $this->db->insert_id();
		} else {
			$group_id = null;
		}
		
		return $group_id;
		
	}

	#------------------------------------------------------
	# Update filter group
	#------------------------------------------------------
	function updateGroup($group_id, $data=array()) {
		
		if ($group_id != '' && $data['label'] != "") {
			$this->db->where('group_id', $group_id);
			$this->db->update('filter_groups', $data);
		}
		
	}

	#------------------------------------------------------
	# Delete filter group
	# - Also deletes related filter options
	#------------------------------------------------------
	function deleteGroup($group_id) {

		//Load database library
		$this->load->dbforge();
		
		// Only delete the group if passed group_id is greater than 0
		if ($group_id > 0) {
			
			// First get all this groups filter options
			$filter_groups = $this->options($group_id);
			
			// Loop through all options and do the necessary deletes
			foreach ($filter_groups as $filter) {
				$this->deleteOption($filter->filter_id);
			}
			
			// Finally we can delete the group itself
			$this->db->where('group_id', $group_id);
			$this->db->delete('filter_groups');
			
		}
		
	}

	#------------------------------------------------------
	# Create filter option
	# - Should also create the column in 
	#   the filter_data table
	#------------------------------------------------------
	function createOption($data=array()) {
		
		// If $data is not empty and label exists the 
		// insert the option, else do nothing
		if (!empty($data) && $data['label'] != "") {
			
			$this->db->insert('filter_definitions', $data);
			$filter_id = $this->db->insert_id();

			//Add the new column field to the filter_data table in the form "f_{$id}"
			$fields = array(
				//Create the filter field
				'filter_'.$filter_id => array(
					'type' 		=> 'tinyint',
					'constraint' => 1,
					'null' 		=> true,
					'default'	=> 0,
				)
			);
			
			// Load the database library
			$this->load->dbforge();
			
			// Add the column to the table
			$this->dbforge->add_column('inventory', $fields);
			
			// Unset the $fields array
			unset($fields);

		} else {
			$filter_id = null;
		}
		
		return $filter_id;
		
	}

	#------------------------------------------------------
	# Update filter option
	#------------------------------------------------------
	function updateOption($filter_id, $data=array()) {

		if ($filter_id != '' && $data['label'] != "") {
			
			$this->db->where('filter_id', $filter_id);
			$this->db->update('filter_definitions', $data);
			
		}
		
	}

	#------------------------------------------------------
	# Delete filter option
	#------------------------------------------------------
	function deleteOption($filter_id) {

		//Load database library
		$this->load->dbforge();
		
		// Only do the deletes if the passed filter_id is 1 or greater
		if ($filter_id > 0) {
		
			// Delete option from filters table
			$this->db->where('filter_id', $filter_id);
			$this->db->delete('filter_definitions');

			//Remove this filter from all the products as well
			//by removing the column from the inventory table
			if ($this->db->field_exists('filter_'.$filter_id, 'inventory')) {
				$this->dbforge->drop_column('inventory', 'filter_'.$filter_id);
			}

		}
		
	}

	#------------------------------------------------------
	# Update filter fields for this product_id
	#------------------------------------------------------
	function updateFilters($cat_id) {
		
		// Get the fields for this category
		$this->db->select('filter_definitions.*');
		$this->db->from('filter_definitions');
		$this->db->join('filter_groups', 'filter_groups.group_id = filter_definitions.group_id');
		$this->db->where('cat_id', $cat_id);
		$this->db->order_by('filter_order');
		$query = $this->db->get();
		
		if ($query->num_rows() > 0) {
			
			// Foreach filter option create the data array 
			// containing the fields we need to update 
			foreach ($query->result() as $filter) {
				
				// Create the field name
				$field_name = "filter_$filter->filter_id";
				$data[$field_name] = $this->input->post($field_name); 
 				
			}
			
			// Return the data array which would be appended in 
			// the appropriate inventory_model functions
			return $data;
			
		} else {
			return array();
		}
		
	}

}