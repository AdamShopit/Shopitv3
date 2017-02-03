<?php

class Pages_model extends CI_Model {
	
	function Pages_model() {
		parent::__construct();
	}
	

	#------------------------------------------------------
	# Get homepage content
	#------------------------------------------------------
	function getHomepage() {
		
		$this->db->where('page_id','1');
		$this->db->limit(1);
		$query = $this->db->get('pages');
		
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}

	}


	#------------------------------------------------------
	# Get page content
	#------------------------------------------------------
	function getPage($page_slug) {
		
		$this->db->where('page_slug',$page_slug);
		$this->db->limit(1);
		$query = $this->db->get('pages');
		
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}

	}

	#------------------------------------------------------
	# Get list of pages
	# - 1 = homepage content
	#------------------------------------------------------
	function getPagesList($orderby='page_order', $sort='asc') {
		
		$this->db->cache_off();
		
		$this->db->where('page_id > ', 1);
		$this->db->where('page_visible', 1);
		$this->db->where('page_type', 'page');
		$this->db->where('site', $this->config->item('site'));
		$this->db->order_by($orderby, $sort);
		$query = $this->db->get('pages');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return FALSE;
		}
		
	}

	#------------------------------------------------------
	# Create Documents List
	#------------------------------------------------------
	function createList() {
		
		$list = "";

		$this->db->where('page_visible', 1);
		$this->db->where('page_type', 'page');
		$this->db->where('site', $this->config->item('site'));
		$this->db->order_by('page_order', 'asc');
		$query = $this->db->get('pages');

		if ($query->num_rows() > 0) {
			foreach ($query->result() as $page) {
				
				$class = ( $this->uri->segment(2) == $page->page_slug || ($page->page_slug == "home" && $this->uri->segment(1) == "") ) ? 'class="page page-'.$page->page_id.' current-page"' : 'class="page page-'.$page->page_id.'"';
			
				if (strlen($page->page_redirect) > 1) {
					$list .= '<li '.$class.'><a href="'.$page->page_redirect.'">'.$page->page_name.'</a></li>'."\n";
				} else {
					if ($page->page_id == 1) {
						$url = site_url();
					} else {
						$url = site_url("page/$page->page_slug");
					}
					$list .= '<li '.$class.'><a href="'.$url.'">'.$page->page_name.'</a></li>'."\n";
				}
			}
		}
		
		return $list;
		
	}

}