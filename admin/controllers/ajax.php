<?php
class Ajax extends CI_Controller {

	function Ajax()
	{
		parent::__construct();
		$this->load->database();
		
		$this->load->model('settings_model');
		$this->settings_model->initConfig();
		
		$this->load->model('reports_model');
		$this->load->model('inventory_model');
		$this->load->model('category_model');
		$this->load->model('collections_model');
		$this->load->model('filters_model');
		
		/* Login check */
		$this->load->model('login_model');
		if(!$this->login_model->check_session()){
			redirect('/login');
		}
		/* End: Login check */
	}

	function index() {
		redirect('dashboard');
	}

	#------------------------------------------------------
	# Add/Remove item to collection
	#------------------------------------------------------
	function addtocollection() {

		// Disable the profiler for this function
		$this->output->enable_profiler(FALSE);

		if ($this->uri->segment(3) > 0 && $this->uri->segment(4) > 0):

			//Check if item is already in collection, returns TRUE if it is
			$in_collection = $this->collections_model->isItemInCollection($this->uri->segment(4),$this->uri->segment(3));

			if ($in_collection == TRUE) {
				//remove it
				$result = $this->collections_model->removeItemFromCollection($this->uri->segment(4),$this->uri->segment(3));
				$status = 'off';
			} else {
				//Add it
				$result = $this->collections_model->addToCollection($this->uri->segment(4),$this->uri->segment(3));
				$status = 'on';
			}

			//Clear db cache
			if ($this->config->item('caching') == 'true') {
			delete_cache();
			}

			print json_encode(
				array('status' => $status, 'rowid' => $this->uri->segment(3), 'menuid' => $this->uri->segment(4))
			);
			
		endif;
	}

	#------------------------------------------------------
	# Inline editing
	# - Used on orders view template to enter serial number
	#   and order line item status
	#------------------------------------------------------
	function edit() {
		
		$db_where 	= base64_decode($this->input->post('where')); 	// Database ID of row we're updating - needs decoding & unserializing
		$db_where 	= unserialize($db_where);
		$table  	= $this->input->post('table');					// Name of the table
		$value  	= $this->input->post('value');					// The value we're saving
		$column 	= $this->input->post('id'); 					// ID is the html elements ID which is the column name
		
		if (!empty($db_where) and !empty($table) and !empty($column)) {
			
			// Do we need to update or insert this item? 
			// We need to check first...
			foreach($db_where as $where_key => $where_value) {
				$this->db->where($where_key, $where_value);
			}
			$this->db->from($table);
			$count = $this->db->count_all_results();
			
			// Now we can either update or insert the data
			$this->db->set($column, $value);
			if ($count > 0) {
				foreach($db_where as $where_key => $where_value) {
					$this->db->where($where_key, $where_value);
				}
				$this->db->update($table);
			} else {
				foreach($db_where as $where_key => $where_value) {
					$this->db->set($where_key, $where_value);
				}
				$this->db->insert($table, $data);
			}
		}
		
		echo $value;
		
	}
	
}