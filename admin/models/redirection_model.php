<?php

class Redirection_model extends CI_Model {
	
	function Redirection_model() {
		parent::__construct();
	}

	#------------------------------------------------------
	# Create redirection
	#------------------------------------------------------
	function create_redirection($old_url, $new_url, $status_code=301) {
	
		$data = array(
				'old_url' => $old_url,
				'new_url' => $new_url,
				'status_code' => $status_code,
				);
		$this->db->insert('redirection', $data);
		
		//Perform a cleanse to remove ALL redirects
		//where old_url = new_url
		$this->db->where('old_url = new_url');
		$this->db->delete('redirection');

		//Remove any redirects that are duplicates
		$this->db->query('DELETE t1 FROM redirection t1, redirection t2 WHERE t1.old_url = t2.old_url AND t1.new_url = t2.new_url AND t1.id < t2.id');
		
		//Remove any redirect loops
		$this->db->query('DELETE t1 FROM redirection t1, redirection t2 WHERE t1.old_url = t2.new_url AND t1.new_url = t2.old_url');
	
	}

	#------------------------------------------------------
	# Count redirections
	# - returns total number of redirections
	#------------------------------------------------------
	function countRedirections() {
		
		$this->db->select('id');
		$query = $this->db->get('redirection');
		
		return $query->num_rows();
	}

	#------------------------------------------------------
	# Get redirection
	#------------------------------------------------------
	function listRedirections($num, $offset) {
	
		$this->db->order_by('old_url','asc');
		$this->db->limit($num,$offset);
		$query = $this->db->get('redirection');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	
	}

	#------------------------------------------------------
	# Add redirection
	#------------------------------------------------------
	function addRedirection() {
		
		$data = array(
				'old_url' => str_replace(site_root(), '', $this->input->post('s_oldurl')),
				'new_url' => str_replace(site_root(), '', $this->input->post('s_newurl')),
				'status_code' => $this->input->post('s_statuscode'),
				);
		$this->db->insert('redirection',$data);
		
	}

	#------------------------------------------------------
	# Delete Redirection
	#------------------------------------------------------
	function deleteRedirection($id) {
	
		if (!empty($id)) {
		$this->db->where('id', $id);
		$this->db->delete('redirection');
		}
	
	}
	
}