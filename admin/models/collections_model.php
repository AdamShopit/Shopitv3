<?php

class Collections_model extends CI_Model {
	
	function Collections_model() {
		parent::__construct();
	}

	#------------------------------------------------------
	# Get collection groups
	#------------------------------------------------------
	function getCollectionGroups() {
	
		$this->db->select('collection_group, group_label');
		$this->db->join('collection_groups', 'collection_groups.id = collections.collection_group', 'left');
		$this->db->group_by('collection_group');
		$this->db->order_by('collection_group', 'asc');
		$query = $this->db->get('collections');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return (object) array();
		}
	}

	#------------------------------------------------------
	# Get a list of collections
	#------------------------------------------------------
	function getCollectionsList($collection_group=null) {
		
		$this->db->where('collection_group', $collection_group);
		$this->db->order_by('collection_order', 'asc');
		$this->db->order_by('collection_name', 'asc');
		$query = $this->db->get('collections');
		
		if ($query->num_rows > 0)
		{
			return $query->result();
		} else {
			return (object) array();
		}
	
	}

	function getFullCollectionsList() {
	
		$this->db->order_by('collection_group', 'asc');
		$this->db->order_by('collection_lock','desc');
		$this->db->order_by('collection_name','asc');
		$query = $this->db->get('collections');
		
		if ($query->num_rows > 0)
		{
			return $query->result();
		}

	}

	#------------------------------------------------------
	# Create collections nav
	# @returns multi-dimensional array of groups 
	# 		   and collections
	#------------------------------------------------------
	function collectionsNav() {
		
		$array = array();
		
		// Get the groups and loop through them
		$groups = $this->getCollectionGroups();
		
		foreach($groups as $group) {
			
			$group_title = ($group->collection_group == 0) ? "Ungrouped" : $group->group_label;
			
			$array[$group_title] = array();
			
			// Get collections within this group and loop through them
			$collections = $this->getCollectionsList($group->collection_group);
			
			foreach($collections as $collection) {
				
				$array[$group_title][$collection->collection_id] = $collection->collection_name;
				
			}
			
		}
		
		return (object) $array;
		
	}

	#------------------------------------------------------
	# Enter info here
	#------------------------------------------------------
	function product_count($collection_id) {
	
		$this->db->select('product_id');
		$this->db->where('collection_id',$collection_id);
		$this->db->order_by('order','asc');
		$query = $this->db->get('collection_items');

		return $query->num_rows();

	}


	#------------------------------------------------------
	# Get a specific collection
	#------------------------------------------------------
	function getCollection($collection_id) {
		$this->db->where('collection_id',$collection_id);
		$query = $this->db->get('collections');
		
		if ($query->num_rows > 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	#------------------------------------------------------
	# Add/create collection
	#------------------------------------------------------
	function addCollection($file='') {

		$page_slug = $this->check_slug($_POST['collection_name']);
		
		$data = array(
					'collection_name' 		=> $_POST['collection_name'],
					'collection_desc' 		=> autop($_POST['collection_desc']),
					'collection_slug'		=> $page_slug,
					'collection_meta_title'	=> $_POST['collection_meta_title'],
					'collection_custom_heading'	=> $_POST['collection_custom_heading'],
					'collection_meta_description' => format_meta($_POST['collection_meta_description']),
					'collection_meta_keywords' 	=> format_meta($_POST['collection_meta_keywords'],'keywords'),
					'collection_meta_custom' => $this->input->post('collection_meta_custom'),
					'collection_group'		=> $this->input->post('collection_group'),
				);

		if ($file != ''):
			$data['collection_image'] = $file;
		else:
			$data['collection_image'] = null;
		endif;
		
		$this->db->insert('collections',$data);
		
		return $this->db->insert_id();
	
	}

	#------------------------------------------------------
	# Add to collection
	#------------------------------------------------------
	function addToCollection($collection_id,$product_id){

		//Check if this item is already in this collection
		$this->db->select('id');
		$this->db->where('collection_id',$collection_id);
		$this->db->where('product_id',$product_id);
		$query = $this->db->get('collection_items');
		
		if ($query->num_rows() > 0) {
			return '<span class="red">Item is already in collection.</span>';
		} else {

			$data = array(
					'collection_id' => $collection_id,
					'product_id' 	=> $product_id,
					);
			
			$this->db->insert('collection_items',$data);

			return 'Item added to collection.';
		}
		
	}

	#------------------------------------------------------
	# Get ALL items in collection
	# - the 'collection_items' is a string in the db table
	# - this returns ALL fields
	#------------------------------------------------------
	function getItemsInCollection($collection_id) {
	
		$this->db->where('collection_id',$collection_id);
		$this->db->join('inventory','inventory.product_id = collection_items.product_id');
		$this->db->order_by('order','asc');
		$query = $this->db->get('collection_items');

		if ($query->num_rows > 0)
		{
			return $query->result();
		}

	}

	#------------------------------------------------------
	# Is item in collection
	# - returns true or false
	#------------------------------------------------------
	function isItemInCollection($collection_id,$product_id) {
		
		$this->db->where('collection_id',$collection_id);
		$this->db->where('product_id',$product_id);
		$query = $this->db->get('collection_items');
		
		if ($query->num_rows > 0) {
			return TRUE;
		} else {
			return FALSE;
		}
	
	}
	
	#------------------------------------------------------
	# Get collections for THIS item
	# @param $product_id (int)	Product ID
	# @return Array of collection_ids
	#------------------------------------------------------
	function getCollectionsForItem($product_id) {
		
		$this->db->select('collection_id');
		$this->db->where('product_id', $product_id);
		$query = $this->db->get('collection_items');
		
		if ($query->num_rows() > 0) {
			foreach($query->result() as $collection){
				$array[] = $collection->collection_id;
			}
			return $array;
			
		} else {
			return array();
		}
		
	}

	#------------------------------------------------------
	# Removes item from collection
	# - works by removing the product_id from the string
	#------------------------------------------------------
	function removeItemFromCollection($collection_id,$product_id) {
				
		$this->db->where('collection_id',$collection_id);
		$this->db->where('product_id',$product_id);
		$this->db->delete('collection_items');
	
	}

	#------------------------------------------------------
	# Delete collection
	#------------------------------------------------------
	function deleteCollection($collection_id) {
	
		$this->db->where('collection_id',$collection_id);
		$this->db->where('collection_lock','0');
		$query = $this->db->delete('collections');
		
		$this->db->where('collection_id',$collection_id);
		$query2 = $this->db->delete('collection_items');
	
	}

	#------------------------------------------------------
	# Update collection
	#------------------------------------------------------
	function updateCollection($collection_id, $file='') {
	
		$data = array(
					'collection_name' => $_POST['collection_name'],
					'collection_desc' => autop($_POST['collection_desc']),
					'collection_slug' => $this->check_slug($_POST['collection_slug'],$collection_id),
					'collection_meta_title' => $_POST['collection_meta_title'],
					'collection_custom_heading'	=> $_POST['collection_custom_heading'],
					'collection_meta_description' => format_meta($_POST['collection_meta_description']),
					'collection_meta_keywords' => format_meta($_POST['collection_meta_keywords'],'keywords'),
					'collection_meta_custom' => $this->input->post('collection_meta_custom'),
					'collection_group' => $this->input->post('collection_group'),
				);

		//file not empty and delete_cat_image is not set, insert filename
		if ( ($_POST['delete_collection_image'] != 'true' && $file != '') || ($_POST['delete_collection_image'] == 'true' && $file != '') ):
			$data['collection_image'] = $file;
		//delete_cat_image is set and file is empty, insert null
		elseif ( ($_POST['delete_collection_image'] == 'true' && $file == '') ):
			$data['collection_image'] = null;
		endif;
				
		$this->db->where('collection_id', $collection_id);
		$this->db->update('collections', $data);
	
	}

	#------------------------------------------------------
	# Checks slugs for duplicates and increments if
	# there is any
	#------------------------------------------------------
	function check_slug($slug,$collection_id=null) {
		
		//Check if slug has changed for this category
		$this->db->select('collection_slug');
		$this->db->where('collection_id',$collection_id);
		$query = $this->db->get('collections');
		
		$collection = $query->row();
		
		//If slugs are the same, do nothing
		if ($slug == $collection->collection_slug) {
			return $collection->collection_slug;
		} else {
			//If not, then start checks
			// Start with checking the database for similar slugs		
			$this->db->select_max('collection_slug');
			$this->db->where('collection_slug regexp "'. slug($slug) .'(-[0-9])"');
			$this->db->or_where('collection_slug',slug($slug));
			$this->db->order_by('collection_slug','ASC');
			
			$query = $this->db->get('collections');
							
			foreach ($query->result() as $page) {
				
				$max_slug = $page->collection_slug;
				
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
	# Update collection_item order
	#------------------------------------------------------
	function updateCollectionOrder($collection_id,$product_id,$order=0) {
	
		$data = array(
					'order' => $order,
				);
		
		$this->db->where('collection_id',$collection_id);
		$this->db->where('product_id',$product_id);
		$this->db->update('collection_items',$data);
	
	}
	
	#------------------------------------------------------
	# Sort collection list
	#------------------------------------------------------
	function sortCollectionList($collection_id, $order=0) {
	
		$data = array(
			'collection_order' => $order,
		);

		$this->db->where('collection_id', $collection_id);
		$this->db->update('collections', $data);
	
	}

	#------------------------------------------------------
	# Manage Collection Groups
	#------------------------------------------------------
	function getGroups() {
		
		$this->db->order_by('group_label', 'asc');
		$query = $this->db->get('collection_groups');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		}
		
	}

	function getGroup($id) {
		
		$this->db->where('id', $id);
		$query = $this->db->get('collection_groups');
		
		return $query->row();
		
	}

	function createGroup() {
		
		$data = array(
				'group_label' => $this->input->post('group_label'),
				);
				
		$this->db->insert('collection_groups', $data);
		
	}
	
	function updateGroup($id) {

		$data = array(
				'group_label' => $this->input->post('group_label'),
				);
				
		$this->db->where('id', $id);
		$this->db->update('collection_groups', $data);
		
	}
	
	function deleteGroup($id) {
				
		$this->db->where('id', $id);
		$this->db->delete('collection_groups');
		
		//Set to ungrouped
		$data = array('collection_group' => 0);
		$this->db->where('collection_group', $id);
		$this->db->update('collections', $data);
		
	}
	
}