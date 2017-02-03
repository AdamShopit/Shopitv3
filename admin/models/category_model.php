<?php

class Category_model extends CI_Model {
	
	function Category_model() {
		parent::__construct();
		
		$this->load->model('redirection_model');
	}

	#------------------------------------------------------
	# Get sub-category count
	#------------------------------------------------------
	function subcat_count($cat_id) {
		
		$sql = 'select c1.cat_id, c1.cat_name
				from category c1
				where c1.cat_id = '.$cat_id.'
				union all
				select c2.cat_id, c2.cat_name
				from category c1
				inner join category c2 on c1.cat_id = c2.cat_father_id
				where c1.cat_id = '.$cat_id.'
				union all
				select c3.cat_id, c3.cat_name
				from category c1
				inner join category c2 on c1.cat_id = c2.cat_father_id
				inner join category c3 on c2.cat_id = c3.cat_father_id
				where c1.cat_id = '.$cat_id;		

		$query = $this->db->query($sql);
		return $query->num_rows();
	
	}

	#------------------------------------------------------
	# Count no of products in category (direct)
	#------------------------------------------------------
	function product_count($cat_id) {
		
		$this->db->select('count(product_id) as product_count');
		$this->db->where('cat_id',$cat_id);
		$query = $this->db->get('inventory');
		
		$result = $query->result();
		return $result[0]->product_count;
	
	}

	#------------------------------------------------------
	# Count ALL products
	#------------------------------------------------------
	function all_product_count($cat_id) {

		$sql = 'select c1.cat_id, product_id, product_name
				from category c1
				inner join inventory on inventory.cat_id = c1.cat_id
				where c1.cat_id = '.$cat_id.'
				and archived = 0
				union all
				select c2.cat_id, product_id, product_name
				from category c1
				inner join category c2 on c1.cat_id = c2.cat_father_id
				inner join inventory on inventory.cat_id = c2.cat_id
				where c1.cat_id = '.$cat_id.'
				and archived = 0
				union all
				select c3.cat_id, product_id, product_name
				from category c1
				inner join category c2 on c1.cat_id = c2.cat_father_id
				inner join category c3 on c2.cat_id = c3.cat_father_id
				inner join inventory on inventory.cat_id = c3.cat_id
				where c1.cat_id = '.$cat_id.'
				and archived = 0';
				
		$query = $this->db->query($sql);
		return $query->num_rows();

	}

	#------------------------------------------------------
	# Count ALL LIVE products
	#------------------------------------------------------
	function live_product_count($cat_id,$status=0) {

		$sql = "select c1.cat_id, product_id, product_name, product_disabled
				from category c1
				inner join inventory on inventory.cat_id = c1.cat_id
				where c1.cat_id = $cat_id
				and (product_disabled = $status or c1.cat_hide = $status)
				and archived = 0
				union all
				select c2.cat_id, product_id, product_name, product_disabled
				from category c1
				inner join category c2 on c1.cat_id = c2.cat_father_id
				inner join inventory on inventory.cat_id = c2.cat_id
				where c1.cat_id = $cat_id
				and (product_disabled = $status or c2.cat_hide = $status)
				and archived = 0
				union all
				select c3.cat_id, product_id, product_name, product_disabled
				from category c1
				inner join category c2 on c1.cat_id = c2.cat_father_id
				inner join category c3 on c2.cat_id = c3.cat_father_id
				inner join inventory on inventory.cat_id = c3.cat_id
				where c1.cat_id = $cat_id
				and (product_disabled = $status or c3.cat_hide = $status)
				and archived = 0";
				
		$query = $this->db->query($sql);
		return $query->num_rows();

	}

	#------------------------------------------------------
	# Move products from this category (recategorise)
	#------------------------------------------------------
	function recategorise_products($cat_id) {

		//Get all products contained in this category ($cat_id)
		$sql = 'select c1.cat_id
				from category c1
				where c1.cat_id = '.$cat_id.'
				union all
				select c2.cat_id
				from category c1
				inner join category c2 on c1.cat_id = c2.cat_father_id
				where c1.cat_id = '.$cat_id.'
				union all
				select c3.cat_id
				from category c1
				inner join category c2 on c1.cat_id = c2.cat_father_id
				inner join category c3 on c2.cat_id = c3.cat_father_id
				where c1.cat_id = '.$cat_id;
				
		$query = $this->db->query($sql);

		if (isset($_POST['new_cat_id'])) {
			foreach ($query->result() as $product) {
			
				$data = array(
							'cat_id' => $_POST['new_cat_id'],
						);
				
				$this->db->where('cat_id',$product->cat_id);
				$this->db->update('inventory',$data);
				
			}
		}
	
		return $query->result();
	
	}

	#------------------------------------------------------
	# Delete categorys
	#------------------------------------------------------
	function delete_category($cat_id) {
		
		$this->db->where('cat_id',$cat_id);
		$this->db->delete('category');
		
	}

	#------------------------------------------------------
	# Retrieve all parent categories
	#------------------------------------------------------
	function getAllParents($sortbyorder=true) {
		
		$this->db->where('cat_father_id',0);
		if ($sortbyorder == true) {
		$this->db->order_by('cat_order','asc');
		}
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
		
		$this->db->order_by('cat_name', 'asc');
		$query = $this->db->get('category');

		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	}

	#------------------------------------------------------
	# Get AVAILABLE categories in nice format
	# - Parent > Child > Grandchild
	#------------------------------------------------------
	function getAvailableCategories($product_id=null, $exclude_disabled=false) {
		
		$sql = 'SELECT 
					c1.cat_id, 
					CONCAT_WS(" &raquo; ",c3.cat_name,c2.cat_name,c1.cat_name) AS cat_name,
					(CASE WHEN (c1.cat_hide = 1 OR c2.cat_hide = 1 OR c3.cat_hide = 1) THEN
						1
					ELSE
						0
					END) AS cat_hide 
				FROM category c1
				LEFT JOIN category c2 ON c1.cat_father_id = c2.cat_id
				LEFT JOIN category c3 ON c2.cat_father_id = c3.cat_id 
				WHERE (c1.cat_father_id IN (SELECT cat_id from category) OR c1.cat_father_id = 0)';
		if ($product_id != null) {
		$sql .= 'AND c1.cat_id NOT IN (SELECT cat_id FROM xcat WHERE product_id = '.$product_id.') ';
		}
		if ($exclude_disabled == true) {
			$sql .= ' HAVING cat_hide = 0 ';
		}
		$sql .= 'order by cat_name asc';
		
		$query = $this->db->query($sql);
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	
	}

	#------------------------------------------------------
	# Retrieve sub-categories
	#------------------------------------------------------
	function getSubCategories($cat_father_id,$sortbyorder=true) {
		
		$this->db->where('cat_father_id',$cat_father_id);
		if ($sortbyorder == true) {
		$this->db->order_by('cat_order', 'asc');
		}
		$this->db->order_by('cat_name', 'asc');
		$query = $this->db->get('category');

		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	}

	#------------------------------------------------------
	# Retrieve this parent category
	#------------------------------------------------------
	function getThisCategory($cat_id) {
		
		$this->db->select('c1.cat_id, c1.cat_name, c1.cat_desc, c1.cat_excerpt, c1.cat_father_id, c1.cat_slug, c1.cat_meta_title, c1.cat_custom_heading, c1.cat_meta_description, c1.cat_meta_keywords, c1.cat_meta_custom, c1.cat_hide, c1.cat_image, c1.cat_order, concat_ws("/",c3.cat_slug,c2.cat_slug,c1.cat_slug) as cat_url', false);
		$this->db->from('category c1');
		$this->db->join('category c2', 'c1.cat_father_id = c2.cat_id', 'left');
		$this->db->join('category c3', 'c2.cat_father_id = c3.cat_id', 'left');
		$this->db->where('c1.cat_id',$cat_id);
		$query = $this->db->get('category');

		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
	
	}

	#------------------------------------------------------
	# Add category
	#------------------------------------------------------
	function addCategory($file='') {
		$data = array(
					'cat_name' 			=> $this->input->post('cat_name'),
					'cat_desc' 			=> autop($this->input->post('cat_desc')),
					'cat_excerpt' 		=> autop($this->input->post('cat_excerpt')),
					'cat_father_id' 	=> $this->input->post('cat_father_id'),
					'cat_slug'			=> $this->category_model->check_slug(slug($this->input->post('cat_name'))),
					'cat_meta_title'	=> $this->input->post('cat_meta_title'),
					'cat_custom_heading' => $this->input->post('cat_custom_heading'),
					'cat_meta_description' => format_meta($this->input->post('cat_meta_description')),
					'cat_meta_keywords' => format_meta($this->input->post('cat_meta_keywords'),'keywords'),
					'cat_meta_custom'	=> $this->input->post('cat_meta_custom'),
					'cat_hide'			=> $this->input->post('cat_hide'),
				);

		if (library_exists('categoryicons')): 
			if ($file != ''):
				$data['cat_image'] = $file;
			else:
				$data['cat_image'] = null;
			endif;
		endif;

		$this->db->insert('category',$data);
		
		return $this->db->insert_id();
	}

	#------------------------------------------------------
	# Update category
	#------------------------------------------------------
	function updateCategory($cat_id,$file='') {

		$cat_slug = $this->category_model->check_slug($_POST['cat_slug'],$cat_id);

		$data = array(
					'cat_name' 			=> $this->input->post('cat_name'),
					'cat_desc' 			=> autop($this->input->post('cat_desc')),
					'cat_excerpt' 		=> autop($this->input->post('cat_excerpt')),
					'cat_father_id' 	=> $this->input->post('cat_father_id'),
					'cat_slug'			=> $cat_slug,
					'cat_meta_title'	=> $this->input->post('cat_meta_title'),
					'cat_custom_heading' => $this->input->post('cat_custom_heading'),
					'cat_meta_description' => format_meta($this->input->post('cat_meta_description')),
					'cat_meta_keywords' => format_meta($this->input->post('cat_meta_keywords'),'keywords'),
					'cat_meta_custom'	=> $this->input->post('cat_meta_custom'),
					'cat_hide'			=> $this->input->post('cat_hide'),
				);

		if (library_exists('categoryicons')): 
			//file not empty and delete_cat_image is not set, insert filename
			if ( ($_POST['delete_cat_image'] != 'true' && $file != '') || ($_POST['delete_cat_image'] == 'true' && $file != '') ):
				$data['cat_image'] = $file;
			//delete_cat_image is set and file is empty, insert null
			elseif ( ($_POST['delete_cat_image'] == 'true' && $file == '') ):
				$data['cat_image'] = null;
			endif;
		endif;
		
		$this->db->where('cat_id',$cat_id);
		$this->db->update('category',$data);
		
		//Hide nested categories
		$sql = 'select c1.cat_id, c1.cat_name, c1.cat_hide
				from category c1
				where c1.cat_id = '.$cat_id.'
				union all
				select c2.cat_id, c2.cat_name, c2.cat_hide
				from category c1
				inner join category c2 on c1.cat_id = c2.cat_father_id
				where c1.cat_id = '.$cat_id.'
				union all
				select c3.cat_id, c3.cat_name, c3.cat_hide
				from category c1
				inner join category c2 on c1.cat_id = c2.cat_father_id
				inner join category c3 on c2.cat_id = c3.cat_father_id
				where c1.cat_id = '.$cat_id;
		
		$query = $this->db->query($sql);
		
		foreach ($query->result() as $category) {
			$data = array(
				'cat_hide' => $_POST['cat_hide'],
			);
			
			$this->db->where('cat_id',$category->cat_id);
			$this->db->update('category',$data);
		}
	
		//Return the new cat_slug
		return $cat_slug;
	}

	#------------------------------------------------------
	# Update category order
	#------------------------------------------------------
	function updateCategoryOrder($cat_id,$order=0) {
	
		$data = array(
					'cat_order' => $order,
				);
		
		$this->db->where('cat_id',$cat_id);
		$this->db->update('category',$data);
	
	}

	#------------------------------------------------------
	# Redirection (301, 404, etc)
	# - this is run after the category is saved so the
	#   data will contain updated slugs
	#------------------------------------------------------
	function redirection($cat_id) {
	
		//First get the cat_url/cat_slug for this cat_id
		$this->db->select('c1.cat_id, concat_ws("/",c3.cat_slug,c2.cat_slug,c1.cat_slug) as cat_url', false);
		$this->db->from('category c1');
		$this->db->join('category c2', 'c1.cat_father_id = c2.cat_id', 'left');
		$this->db->join('category c3', 'c2.cat_father_id = c3.cat_id', 'left');
		$this->db->where('c1.cat_id',$cat_id);
		$this->db->or_where('c1.cat_father_id',$cat_id);
		$this->db->or_where('c2.cat_father_id',$cat_id);
		$this->db->or_where('c3.cat_father_id',$cat_id);
		$this->db->order_by('cat_url', 'asc');

		$query = $this->db->get();

		if ($query->num_rows() > 0)
		{
			//Get the url for the current category, this is the new one IF it has changed
			$cats = $query->result();
			
			//The first category pulled should be this category ($cat_id)
			$cat_url = $cats[0]->cat_url;
			
			//If this url does not equal the existing url passed via $_POST then create redirects
			if ($this->input->post('existing_url') != $cat_url) {
			
				//For the CURRENT category
				$this->redirection_model->create_redirection($this->input->post('existing_url'), $cat_url);
			
				//For all NESTED categories
				foreach ($cats as $cat):
					if ($cat->cat_id != $cat_id) {
						//We need to str_replace using the existing url
						$old_url = str_replace($cat_url, $this->input->post('existing_url'), $cat->cat_url);
						$this->redirection_model->create_redirection($old_url, $cat->cat_url);				
					}
				
					//For all products WITHIN CURRENT category
					//First get all the products in this category $cat_id
					$this->db->select('c1.cat_id, c3.cat_slug as cat_slug3, c2.cat_slug as cat_slug2, c1.cat_slug as cat_slug1, product_slug, product_id');
					$this->db->from('category c1');
					$this->db->join('inventory inv','c1.cat_id = inv.cat_id', 'inner');
					$this->db->join('category c2', 'c1.cat_father_id = c2.cat_id', 'left');
					$this->db->join('category c3', 'c2.cat_father_id = c3.cat_id', 'left');
					$this->db->where('c1.cat_id',$cat->cat_id);
					
					$query = $this->db->get();
					
					if ($query->num_rows() > 0)
					{
						//Get the product url segments, this is the new one IF it has changed
						$products = $query->result();
						
						foreach ($products as $item) {
							//Create new product url to redirect to
							if ($item->cat_slug2 == '') { //Will be a parent url structure
								$new_product_url = $item->cat_slug1 . '/-/-/' . $item->product_slug . '/' . $item->product_id;
							} elseif ($item->cat_slug2 != '' && $item->cat_slug3 == '') { //Is a child url structure
								$new_product_url = $item->cat_slug2 . '/' . $item->cat_slug1 . '/-/' . $item->product_slug . '/' . $item->product_id;
							} elseif ($item->cat_slug2 != '' && $item->cat_slug3 != '') { //Is grandchild url structure
								$new_product_url = $item->cat_slug3 . '/' . $item->cat_slug2 . '/' . $item->cat_slug1 . '/' . $item->product_slug . '/' . $item->product_id;
							}
							
							//Work out the old product url using the passed hidden field
							//Check what the category structure is, i.e. parent, child or grandchild by
							//counting the number of forward slashes
							$url_type = substr_count($this->input->post('existing_url'), '/');
							switch ($url_type) {
								
								case 0: //Parent category being changed
									$old_product_url = preg_replace('@(.+?)/(.+?)/(.+?)/(.+?)/([0-9]+)@', $this->input->post('existing_url') . '/$2/$3/$4/$5', $new_product_url);
									break;
								
								case 1: //Child category being changed
									$old_product_url = preg_replace('@(.+?)/(.+?)/(.+?)/(.+?)/([0-9]+)@', $this->input->post('existing_url') . '/$3/$4/$5', $new_product_url);
									break;
								
								case 2: //Grandchild category being changed
									$old_product_url = preg_replace('@(.+?)/(.+?)/(.+?)/(.+?)/([0-9]+)@', $this->input->post('existing_url') . '/$4/$5', $new_product_url);
									break;
								
							}
							
							//Create the redirection for this product
							$this->redirection_model->create_redirection($old_product_url, $new_product_url);
						
						}

					}

				endforeach;

			}
			
		}

	}

	#------------------------------------------------------
	# Checks slugs for duplicates and increments if
	# there is any
	#------------------------------------------------------
	function check_slug($slug,$cat_id=null) {
	
		//Check if slug has changed for this category
		$this->db->select('cat_slug');
		$this->db->where('cat_id',$cat_id);
		$query = $this->db->get('category');
		
		$category = $query->row();
		
		//If slugs are the same, do nothing
		if ($slug == $category->cat_slug) {
			return $category->cat_slug;
		} else {
			//If not, then start checks
			// Start with checking the database for similar slugs		
			$this->db->select_max('cat_slug');
			$this->db->or_where('cat_slug',slug($slug));
			$this->db->order_by('cat_slug','ASC');
			
			$query = $this->db->get('category');
							
			foreach ($query->result() as $page) {
				
				$max_slug = $page->cat_slug;
				
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
	# Export categories CSV file
	#------------------------------------------------------
	function exportCategories() {

		$delimiter = '","';
		$newline = "\r\n";

		$this->db->select('c1.cat_id, c1.cat_name, c1.cat_desc, c1.cat_meta_title, c1.cat_meta_description, c1.cat_meta_keywords, c1.cat_hide, concat_ws(" > ",c3.cat_name,c2.cat_name,c1.cat_name) as cat_path', false);
		$this->db->from('category c1');
		$this->db->join('category c2', 'c1.cat_father_id = c2.cat_id','left');
		$this->db->join('category c3', 'c2.cat_father_id = c3.cat_id','left');
		$this->db->order_by('cat_path','asc');
		$query = $this->db->get();

		$csv_column_titles = array("Category Id", "Name", "Description", "Category Path", "Meta Title", "Meta Description", "Meta Keywords", "Disabled" );
		
		$csv_data_row = '"' . strtoupper( implode($delimiter,$csv_column_titles) ) . '"' . $newline;

		foreach ($query->result() as $cat) {

			$data = array(
					$cat->cat_id, csv_cleanse($cat->cat_name), csv_cleanse($cat->cat_desc), $cat->cat_path, $cat->cat_meta_title, csv_cleanse($cat->cat_meta_description), csv_cleanse($cat->cat_meta_keywords), $cat->cat_hide
					); 
		
			$csv_data_row .= '"' . implode($delimiter,$data) . '"' . $newline;
		
		}
		
		return $csv_data_row;

	}
}
?>