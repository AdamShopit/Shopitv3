<?php

class Category_model extends CI_Model {
	
	function Category_model() {
		parent::__construct();
	}

	#------------------------------------------------------
	# Get Category
	# - retrieves this category's cat_id and cat_father_id
	#------------------------------------------------------
	function getCategory() {
	
		// We're viewing a parent category
		if ( ($this->uri->segment(2) == '' || is_numeric($this->uri->segment(2))) && ($this->uri->segment(3) == '' || !is_numeric($this->uri->segment(3))) ):
		
			$this->db->select('*, 
				(select cat_name from category c1 where c1.cat_id = category.cat_father_id) as parent_cat_name,
				(select cat_slug from category c1 where c1.cat_id = category.cat_father_id) as parent_cat_slug,
				(select c2.cat_id from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_father_id) as ancestor_cat_id,
				(select c2.cat_father_id from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_id) as ancestor_cat_father_id,
				(select c2.cat_name from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_father_id) as ancestor_cat_name,
				(select c2.cat_slug from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_father_id) as ancestor_cat_slug
				'
			, false);
			$this->db->where('cat_slug',$this->uri->segment(1));
			$this->db->where('cat_father_id',0);
			
			$query = $this->db->get('category');
		
		// We're viewing a sub-category
		elseif ($this->uri->segment(2) != '' && ( $this->uri->segment(3) == '' || is_numeric($this->uri->segment(3)) )):

			$sql = 'select 
						*,
						(select cat_name from category c1 where c1.cat_id = category.cat_father_id) as parent_cat_name,
						(select cat_slug from category c1 where c1.cat_id = category.cat_father_id) as parent_cat_slug,
						(select c2.cat_id from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_father_id) as ancestor_cat_id,
						(select c2.cat_father_id from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_id) as ancestor_cat_father_id,
						(select c2.cat_name from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_father_id) as ancestor_cat_name,
						(select c2.cat_slug from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_father_id) as ancestor_cat_slug
					from category
					where cat_slug = "' . $this->uri->segment(2) .'"
					and cat_father_id in (
						select cat_id 
						from category 
						where cat_slug = "'. $this->uri->segment(1) . '"
						and cat_father_id = 0
					)';
			
			$query = $this->db->query($sql);
		
		// We're viewing a third-tier category
		elseif ( $this->uri->segment(2) != '' && ($this->uri->segment(3) != '' || !is_numeric($this->uri->segment(3))) && ($this->uri->segment(4) == '' || is_numeric($this->uri->segment(4))) ):

			$sql = 'select 
						*,
						(select cat_name from category c1 where c1.cat_id = category.cat_father_id) as parent_cat_name,
						(select cat_slug from category c1 where c1.cat_id = category.cat_father_id) as parent_cat_slug,
						(select c2.cat_id from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_father_id) as ancestor_cat_id,
						(select c2.cat_father_id from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_id) as ancestor_cat_father_id,
						(select c2.cat_name from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_father_id) as ancestor_cat_name,
						(select c2.cat_slug from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_father_id) as ancestor_cat_slug
					from category
					where cat_slug = "' . $this->uri->segment(3) . '"
					and cat_father_id in (
						select cat_id 
						from category 
						where cat_slug = "' . $this->uri->segment(2) . '"
 						and cat_father_id in (
							select cat_id
							from category
							where cat_slug = "' . $this->uri->segment(1) . '"
							and cat_father_id = 0
						) 
					)';
			
			$query = $this->db->query($sql);
	
		// We're viewing a product in a first-tier category
		elseif ( $this->uri->segment(2) == '-' && $this->uri->segment(3) == '-' && is_numeric($this->uri->segment(5)) ):

			$sql = 'select 
						*,
						(select cat_name from category c1 where c1.cat_id = category.cat_father_id) as parent_cat_name,
						(select cat_slug from category c1 where c1.cat_id = category.cat_father_id) as parent_cat_slug,
						(select c2.cat_id from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_father_id) as ancestor_cat_id,
						(select c2.cat_father_id from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_id) as ancestor_cat_father_id,
						(select c2.cat_name from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_father_id) as ancestor_cat_name,
						(select c2.cat_slug from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_father_id) as ancestor_cat_slug
					from inventory,category
					where product_id = "' . $this->uri->segment(5) . '"
					and inventory.cat_id = category.cat_id
					and inventory.cat_id in (
						select cat_id
						from category 
						where cat_slug = "' . $this->uri->segment(1) . '"
					)';

			$query = $this->db->query($sql);
					
		// We're viewing a product that is in a second-tier category
		elseif ( ($this->uri->segment(3) == '-'  && is_numeric($this->uri->segment(5))) ):
			
			$sql = 'select 
						*,
						(select cat_name from category c1 where c1.cat_id = category.cat_father_id) as parent_cat_name,
						(select cat_slug from category c1 where c1.cat_id = category.cat_father_id) as parent_cat_slug,
						(select c2.cat_id from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_father_id) as ancestor_cat_id,
						(select c2.cat_father_id from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_id) as ancestor_cat_father_id,
						(select c2.cat_name from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_father_id) as ancestor_cat_name,
						(select c2.cat_slug from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_father_id) as ancestor_cat_slug
					from inventory,category
					where product_id = "' . $this->uri->segment(5) . '"
					and inventory.cat_id = category.cat_id
					and inventory.cat_id in (
						select cat_id
						from category 
						where cat_slug = "' . $this->uri->segment(2) . '"
 						and cat_father_id in (
						select cat_id
						from category
						where cat_slug = "' . $this->uri->segment(1) . '"
						) 
					)';

			$query = $this->db->query($sql);

		// We're viewing a product that is in a third-tier category
		elseif ( ($this->uri->segment(2) != '-' && $this->uri->segment(3) != '-' && is_numeric($this->uri->segment(5))) ):

			$sql = 'select 
						*,
						(select cat_name from category c1 where c1.cat_id = category.cat_father_id) as parent_cat_name,
						(select cat_slug from category c1 where c1.cat_id = category.cat_father_id) as parent_cat_slug,
						(select c2.cat_id from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_father_id) as ancestor_cat_id,
						(select c2.cat_father_id from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_id) as ancestor_cat_father_id,
						(select c2.cat_name from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_father_id) as ancestor_cat_name,
						(select c2.cat_slug from category c1 left join category c2 on c1.cat_father_id = c2.cat_id where c1.cat_id = category.cat_father_id) as ancestor_cat_slug
					from inventory,category
					where product_id = "' . $this->uri->segment(5) . '"
					and inventory.cat_id = category.cat_id
					and inventory.cat_id in (
						select cat_id
						from category 
						where cat_slug = "' . $this->uri->segment(3) . '"
 						and cat_father_id in (
						select cat_id
						from category
						where cat_slug = "' . $this->uri->segment(2) . '"
						) 
					)';

			$query = $this->db->query($sql);

		endif;

		if (!empty($query)):
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
		endif;
		
	}

	#------------------------------------------------------
	# Retrieve all parent categories
	#------------------------------------------------------
	function getAllParents() {
		
		$this->db->where('cat_father_id',0);
		$this->db->where('cat_hide',0);
		$this->db->order_by('cat_order','asc');
		$this->db->order_by('cat_name','asc');
		$query = $this->db->get('category');

		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	}

	#------------------------------------------------------
	# Retrieve all categories
	#------------------------------------------------------
	function getAllCategories() {
		
		$this->db->where('cat_hide',0);
		$this->db->order_by('cat_order','asc');
		$this->db->order_by('cat_name', 'asc');
		$query = $this->db->get('category');

		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	}

	#------------------------------------------------------
	# Retrieve sub-categories
	#------------------------------------------------------
	function getSubCategories($cat_id) {
		
		$select_sql = 	'c1.*,
						(CASE WHEN (c1.cat_hide = 1 OR c2.cat_hide = 1 OR c3.cat_hide) THEN
							1
						ELSE
							0
						END) AS my_cat_hide';
		$this->db->select($select_sql);
		$this->db->from('category c1');
		$this->db->join('category c2', 'c2.cat_id = c1.cat_father_id', 'left');
		$this->db->join('category c3', 'c3.cat_id = c3.cat_father_id', 'left');
		$this->db->where('c1.cat_father_id', $cat_id);
		$this->db->having('my_cat_hide', 0);
		$this->db->order_by('c1.cat_order','asc');
		$this->db->order_by('c1.cat_name', 'asc');
		$query = $this->db->get();

		if ($query->num_rows() > 0)
		{
			return $query->result();
		} else {
			return array();
		}
		
	}

	#------------------------------------------------------
	# Retrieve category title via id
	#------------------------------------------------------
	function getCategoryById($id) {
		
		$this->db->where('cat_id',$id);
		$query = $this->db->get('category');
		
		if($query->row() > 0)
		{
			return $query->row();
		}
	}

	#------------------------------------------------------
	# Retrieve category slug
	#------------------------------------------------------
	function getCategorySlug($id) {
	
		$this->db->select('cat_slug,cat_father_id');
		$this->db->where('cat_id',$id);
		$query = $this->db->get('category');
		
		if($query->row() > 0)
		{
			return $query->row();
		}
	
	}

	#------------------------------------------------------
	# Get brands
	#------------------------------------------------------
	function getBrands() {
		
		$this->db->select('product_brand');
		$this->db->order_by('product_brand','asc');
		$this->db->group_by('product_brand');
		$this->db->where($this->config->item('channel'), 1);
		
		$query = $this->db->get('inventory');
	
		if($query->row() > 0) {
			return $query->result_array();
		} else {
			return array();
		}

	}

	#------------------------------------------------------
	# Get brand title where product_brand_slug = keyword
	#------------------------------------------------------
	function getBrandTitle($str) {
		
		$this->db->select('product_brand');
		$this->db->where('product_brand_slug',$str);
		$this->db->where($this->config->item('channel'), 1);
		$this->db->group_by('product_brand_slug');
		$this->db->limit(1);
		
		$query = $this->db->get('inventory');
	
		if($query->row() > 0)
		{
			return $query->row();
		}

	}


	#------------------------------------------------------
	# Get Collections
	# - which contain products
	#------------------------------------------------------
	function getCollections() {
	
		$this->db->where('collection_lock','0');
		$this->db->where('(select count(id) from collection_items where collections.collection_id = collection_items.collection_id) > 0');
		$this->db->order_by('collection_name','asc');
		$query = $this->db->get('collections');
		
		if($query->row() > 0)
		{
			return $query->result();
		}
		
	}

	#------------------------------------------------------
	# Get a Single collection
	# @param $collection_id (int) 	ID of the collection to retrieve
	#------------------------------------------------------
	function getCollection($collection_id=1) {
		
		$this->db->where('collection_id', $collection_id);
		$this->db->where('collection_lock','1');
		$query = $this->db->get('collections');

		if($query->row() > 0)
		{
			return $query->row();
		}

	}
	
	#------------------------------------------------------
	# Collection Group
	# @param $collection_group_id (int)	Collection group ID
	# @param $select (string)			Columns to return
	#------------------------------------------------------
	function getCollectionGroup($collection_group_id=0, $select='collection_name, collection_id, collection_slug') {
		
		$this->db->select($select);
		$this->db->where('collection_group', $collection_group_id);
		$this->db->order_by('collection_order', 'asc');
		$this->db->order_by('collection_name', 'asc');
		$query = $this->db->get('collections');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return (object)array();
		}
		
	}

	#------------------------------------------------------
	# Category Navigation
	#------------------------------------------------------
	function createNav() {

		$nav = array();
		$html = '';
		
		$this->db->select('
			cat_id as this_cat_id,
			cat_father_id as parent_cat_id,
			(
				select cat_father_id
				from category c1 
				where c1.cat_id = category.cat_father_id
			) as ancestor_cat_id, 
			cat_name,
			cat_slug,
			cat_order
		', false);
		$this->db->where('cat_hide', 0);
		$this->db->order_by('ancestor_cat_id', 'asc');
		$this->db->order_by('parent_cat_id', 'asc');
		$this->db->order_by('cat_order', 'asc');
		$this->db->order_by('cat_name', 'asc');
		$query = $this->db->get('category');
		
		if ($query->num_rows() > 0) {
			
			// Loop through each category and create our menu array
			foreach ($query->result() as $cat) {
			
				// Set the current parent category
				$parent_class = ($this->uri->segment(1) == $cat->cat_slug) ? "current-parent" : "";

				if ( $cat->parent_cat_id == 0 && $cat->ancestor_cat_id < 1 ) {
					// First-tier category
					$nav['nav'][$cat->this_cat_id][$cat->parent_cat_id] = array(
						'id'   => $cat->this_cat_id,
						'name' => $cat->cat_name,
						'slug' => $cat->cat_slug,
						'css'  => implode(' ', array_filter(array("cat-$cat->this_cat_id", $parent_class, 'cat-level-1'))),
						'parent_cat_id' => $cat->parent_cat_id,
						'ancestor_cat_id' => $cat->ancestor_cat_id,
					);
				// The isset() in the elseif statements below check to ensure this
				// category belongs within another - this is to help remove orphan categories 
				} elseif ( $cat->parent_cat_id > 0 && $cat->ancestor_cat_id < 1 && isset($nav['nav'][$cat->parent_cat_id][0]) ) {
					// Second-tier category
					$nav['nav'][$cat->parent_cat_id][$cat->ancestor_cat_id]['categories'][$cat->this_cat_id] = array(
						'id'   => $cat->this_cat_id,
						'name' => $cat->cat_name,
						'slug' => $cat->cat_slug,
						'css'  => implode(' ', array_filter(array("cat-$cat->this_cat_id", 'cat-level-2'))),
						'parent_cat_id' => $cat->parent_cat_id,
						'ancestor_cat_id' => $cat->ancestor_cat_id,
					);
				} elseif ( $cat->parent_cat_id > 0 && $cat->ancestor_cat_id >= 0 && isset($nav['nav'][$cat->ancestor_cat_id][0]['categories'] )) {
					// Third-tier category
					$nav['nav'][$cat->ancestor_cat_id][0]['categories'][$cat->parent_cat_id]['categories'][$cat->this_cat_id] = array(
						'id'   => $cat->this_cat_id,
						'name' => $cat->cat_name,
						'slug' => $cat->cat_slug,
						'css'  => implode(' ', array_filter(array("cat-$cat->this_cat_id", 'cat-level-3'))),
						'parent_cat_id' => $cat->parent_cat_id,
						'ancestor_cat_id' => $cat->ancestor_cat_id,
					);
				}
				
			}

			// Now we can use our menu array to build our nav as a list (excluding the <ul> tag)
			if (!empty($nav)) {
			
				// The first loop will be ancestor categories
				foreach($nav['nav'] as $ancestor) {
				
					$ancestor = $ancestor[0];
					
					$has_children_class = (isset($ancestor['categories'])) ? ' has-children' : '';
					
					$html .= sprintf('<li class="%s"><a href="%s">%s</a>', $ancestor['css'].$has_children_class, site_url($ancestor['slug']), $ancestor['name']);
					
						// Get any second-tier categories that appear within this parent and create the their links
						if (isset($ancestor['categories'])) {
							
							$html .= "<ul class=\"cat-level-2\">\n";
							
							foreach($ancestor['categories'] as $parent) {

								$has_children_class = (isset($parent['categories'])) ? ' has-children' : '';
								
								$url   = sprintf('%s/%s', $ancestor['slug'], $parent['slug']);
								$html .= sprintf('<li class="%s"><a href="%s">%s</a>', $parent['css'].$has_children_class, site_url($url), $parent['name']);
								
								// Now do the final third-tier categories
								if (isset($parent['categories'])) {
									
									$html .= "<ul class=\"cat-level-3\">\n";
									
									foreach($parent['categories'] as $child) {
										$url   = sprintf('%s/%s/%s', $ancestor['slug'], $parent['slug'], $child['slug']);
										$html .= sprintf('<li class="%s"><a href="%s">%s</a>', $child['css'], site_url($url), $child['name']);
									}
									
									$html .= "</ul>\n";
									
								}
								
							}
							
							$html .= "</ul>\n";
							
						}
					
					$html .= "</li>\n";
					
				}
			}

		}
		
		return $html;
		
	}

}