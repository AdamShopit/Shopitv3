<?php

class Pages_model extends CI_Model {
	
	function Pages_model() {
		parent::__construct();
		
		$this->load->model('redirection_model');
	}

	#------------------------------------------------------
	# Count posts
	# - used for pagination
	#------------------------------------------------------
	function countPages($page_type='post') {
		$this->db->select('page_id');
		$this->db->where('page_type', $page_type);
		$query = $this->db->get('pages');
				
		return $query->num_rows();
	}

	#------------------------------------------------------
	# Get list of page sites (channels)
	#------------------------------------------------------
	function getPageSites() {
		
		$sql = "select site, (case when name != '' then name else 'Unknown' end) as name, type, note
				from pages
				left join locations on site = shortname
				where page_type = 'page'
				group by site
				order by FIELD(site, 'website', site)";
		$query = $this->db->query($sql);
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return FALSE;
		}
		
	}

	#------------------------------------------------------
	# Get a list of pages
	#------------------------------------------------------
	function getPagesList($channel='website', $orderby='page_order', $sort='asc') {
		
		$this->db->where('page_type', 'page');
		$this->db->where('site', $channel);
		$this->db->order_by($orderby, $sort);
		$query = $this->db->get('pages');
		
		if ($query->num_rows > 0)
		{
			return $query->result();
		}
	
	}

	#------------------------------------------------------
	# Add/create page
	# We need to check for page_slug duplicates here,
	# get the max slug and increment it by 1
	#------------------------------------------------------
	function addPage($page_type="page") {
	
		$page_slug = $this->check_slug($_POST['page_title']);
		
		if ($this->input->post('page_order') == '') {
			$page_order = 0;
		} else {
			$page_order = $this->input->post('page_order');
		} 

		if ($this->input->post('page_visible') == '') {
			$page_visible = 1;
		} else {
			$page_visible = $this->input->post('page_visible');
		} 

		if ($this->input->post('page_sitemap') == '') {
			$page_sitemap = 1;
		} else {
			$page_sitemap = $this->input->post('page_sitemap');
		} 
		
		$data = array(
				'page_name' 			=> $this->input->post('page_title'),
				'page_content' 			=> autop($this->input->post('page_content')),
				'page_order'			=> $page_order,
				'page_meta_title'		=> $this->input->post('page_meta_title'),
				'page_custom_heading'	=> $this->input->post('page_custom_heading'),
				'page_meta_description' => format_meta($_POST['page_meta_description']),
				'page_meta_keywords' 	=> format_meta($_POST['page_meta_keywords'],'keywords'),
				'page_meta_custom'		=> $this->input->post('page_meta_custom'),
				'page_slug'				=> $page_slug,
				'page_visible'			=> $page_visible,
				'page_redirect'			=> $this->input->post('page_redirect'),
				'page_sitemap'			=> $page_sitemap,
				'page_template'			=> $this->input->post('page_template'),
				'page_type'				=> $page_type,
				'page_date'				=> date('Y-m-d H:i:s', time()),
				'page_author'			=> trim($this->session->userdata('firstname') . ' ' . $this->session->userdata('surname')),
				'site'					=> $this->input->post('page_site'),
			);
		
		$this->db->insert('pages',$data);
	
	}

	#------------------------------------------------------
	# Get page data for edit
	#------------------------------------------------------
	function getPage($page_id) {
	
		$this->db->where('page_id',$page_id);
		$query = $this->db->get('pages');

		if ($query->num_rows > 0)
		{
			return $query->row();
		}

	}

	#------------------------------------------------------
	# Add/create page
	#------------------------------------------------------
	function updatePage($page_id) {	
	
		$page_slug = $this->check_slug($_POST['page_slug'],$page_id);	

		if ($this->input->post('page_order') == '') {
			$page_order = 0;
		} else {
			$page_order = $this->input->post('page_order');
		} 

		if ($this->input->post('page_visible') == '') {
			$page_visible = 1;
		} else {
			$page_visible = $this->input->post('page_visible');
		} 

		if ($this->input->post('page_sitemap') == '') {
			$page_sitemap = 1;
		} else {
			$page_sitemap = $this->input->post('page_sitemap');
		} 
		
		$data = array(
				'page_name' 			=> $this->input->post('page_title'),
				'page_content' 			=> autop($this->input->post('page_content')),
				'page_order'			=> $page_order,
				'page_slug'				=> $page_slug,
				'page_meta_title'		=> $this->input->post('page_meta_title'),
				'page_custom_heading'	=> $this->input->post('page_custom_heading'),
				'page_meta_description' => format_meta($this->input->post('page_meta_description')),
				'page_meta_keywords' 	=> format_meta($this->input->post('page_meta_keywords'),'keywords'),
				'page_meta_custom'		=> $this->input->post('page_meta_custom'),
				'page_visible'			=> $page_visible,
				'page_redirect'			=> $this->input->post('page_redirect'),
				'page_sitemap'			=> $page_sitemap,
				'page_template'			=> $this->input->post('page_template'),
				'site'					=> $this->input->post('page_site'),
			);
		
		$this->db->where('page_id',$page_id);
		$this->db->update('pages',$data);
		
		//Return the new page slug
		return $page_slug;
	
	}

	#------------------------------------------------------
	# Redirection (301, 404, etc)
	# - this is run after the product is saved so the
	#   data will contain updated slugs
	#------------------------------------------------------
	function redirection($page_id, $page_slug) {
		
		//If existing url DOES NOT match the current url for this product then
		//add a redirection
		if ($page_slug != $this->input->post('existing_url')) {
		
			$current_url = $page_slug;
			
			$old_url = 'page/' . $this->input->post('existing_url');
			$new_url = 'page/' . $current_url;
			$this->redirection_model->create_redirection($old_url, $new_url);
			
		}
		
	}

	#------------------------------------------------------
	# Delete page
	#------------------------------------------------------
	function deletePage($page_id) {
	
		$this->db->where('page_id',$page_id);
		
		$this->db->delete('pages');
	
	}

	#------------------------------------------------------
	# Update page order
	#------------------------------------------------------
	function updatePageOrder($page_id,$order=0) {
	
		$data = array(
					'page_order' => $order,
				);
		
		$this->db->where('page_id',$page_id);
		$this->db->update('pages',$data);
	
	}

	#------------------------------------------------------
	# Checks slugs for duplicates and increments if
	# there is any
	#------------------------------------------------------
	function check_slug($slug,$page_id=null) {
		
		//Check if slug has changed for this category
		$this->db->select('page_slug');
		$this->db->where('page_id',$page_id);
		$query = $this->db->get('pages');
		
		$page = $query->row();
		
		//If slugs are the same, do nothing
		if ($slug == $page->page_slug) {
			return $page->page_slug;
		} else {
			//If not, then start checks
			// Start with checking the database for similar slugs		
			$this->db->select_max('page_slug');
			$this->db->where('page_slug regexp "'. slug($slug) .'(-[0-9])"');
			$this->db->or_where('page_slug',slug($slug));
			$this->db->order_by('page_slug','ASC');
			
			$query = $this->db->get('pages');
							
			foreach ($query->result() as $page) {
				
				$max_slug = $page->page_slug;
				
				// If the max slug is not empty...
				if (!empty($max_slug)) :
				
					// Grab the text ($match[1]) and the number $match[2]
					preg_match('@(.+-?)([-0-9])@',$max_slug,$match);
					$current_num = $match[2];
					
					// So, now we can increment by 1				
					$current_num = $current_num + 1;
					
					// Finally, create the new slug
					$page_slug = slug($slug) . '-' .  $current_num;
										
				else:
	
					$page_slug = slug($slug);
					
				endif;
				
				return $page_slug;
	
			}	
		}
	
	}

	#------------------------------------------------------
	# Manage snippets
	#------------------------------------------------------
	function listAllSnippetGroups() {
	
		$this->db->select('snippets.group_id, snippet_groups.label');
		$this->db->join('snippet_groups', 'snippet_groups.group_id = snippets.group_id', 'left');
		$this->db->group_by('snippets.group_id');
		$this->db->order_by('snippet_groups.label', 'asc');
		$query = $this->db->get('snippets');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		}
	}

	function getSnippetsList($group_id=0) {
		
		$this->db->where('group_id', $group_id);
		$this->db->order_by('title','asc');
		$query = $this->db->get('snippets');
		
		if ($query->num_rows > 0)
		{
			return $query->result();
		}
	
	}

	function getSnippets() {
		
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('snippets');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		}
		
	}

	function getSnippet($id) {
		
		$this->db->where('id', $id);
		$query = $this->db->get('snippets');
		
		return $query->row();
		
	}

	function createSnippet() {
		
		$data = array(
				'title' 	=> $this->input->post('snippet_title'),
				'label' 	=> str_replace('-', '_', slug($this->input->post('snippet_label'))),
				'content' 	=> $this->input->post('snippet_content'),
				'notes' 	=> $this->input->post('snippet_notes'),
				'early_parsing'	=> $this->input->post('snippet_early_parsing'),
				'group_id'	=> $this->input->post('group_id'),
				'widget'	=> $this->input->post('snippet_widget'),
				);
				
		$this->db->insert('snippets', $data);
		
	}
	
	function updateSnippet($id) {

		$data = array(
				'title' 	=> $this->input->post('snippet_title'),
				'label' 	=> str_replace('-', '_', slug($this->input->post('snippet_label'))),
				'content' 	=> $this->input->post('snippet_content'),
				'notes' 	=> $this->input->post('snippet_notes'),
				'early_parsing'	=> $this->input->post('snippet_early_parsing'),
				'group_id'	=> $this->input->post('group_id'),
				'widget'	=> $this->input->post('snippet_widget'),
				);
				
		$this->db->where('id', $id);
		$this->db->update('snippets', $data);
		
	}
	
	function deleteSnippet($id) {
				
		$this->db->where('id', $id);
		$this->db->delete('snippets');
		
	}
	
	// This function is used via the store front
	// editing options.
	function updateSnippetContent($id, $data) {
		$this->db->set('content', $data);
		$this->db->where('id', $id);
		$this->db->update('snippets');
	}

	#------------------------------------------------------
	# Manage snippet groups
	#------------------------------------------------------
	function getSnippetGroups() {
		
		$this->db->order_by('label', 'asc');
		$query = $this->db->get('snippet_groups');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		}
		
	}

	function getSnippetGroup($group_id) {
		
		$this->db->where('group_id', $group_id);
		$query = $this->db->get('snippet_groups');
		
		return $query->row();
		
	}

	function createSnippetGroup() {
		
		$data = array(
				'label' => $this->input->post('label'),
				);
				
		$this->db->insert('snippet_groups', $data);
		
	}
	
	function updateSnippetGroup($group_id) {

		$data = array(
				'label' => $this->input->post('label'),
				);
				
		$this->db->where('group_id', $group_id);
		$this->db->update('snippet_groups', $data);
		
	}
	
	function deleteSnippetGroup($group_id) {
				
		$this->db->where('group_id', $group_id);
		$this->db->delete('snippet_groups');
		
		//Set to ungrouped
		$data = array('group_id' => 0);
		$this->db->where('group_id', $group_id);
		$this->db->update('snippets', $data);
		
	}
	
}