<?php

class Inventory_model extends CI_Model {
	
	function Inventory_model() {
		parent::__construct();

		$this->load->model('redirection_model');
	}

	#------------------------------------------------------
	# Count products
	# - retrieves total number of products (i.e. num_rows)
	#------------------------------------------------------
	function countProducts() {
		
		$this->db->from('inventory');
		$this->db->where('parent_id', 0);

		//Start: Results filter
		if(!empty($_POST)) {
			$segment = http_build_query($_POST);
		} else {
			$segment = $this->uri->segment(4);
		}

		if ($segment != '') {
			$query_string = explode('&', urldecode($segment));
			
			foreach ($query_string as $filter) {
				$parse_filter = explode('=',$filter);
				$criteria_name = $parse_filter[0];
				$criteria_value = urldecode($parse_filter[1]);
				
				switch ($criteria_name) {
				
					case 's_sale':
						$this->db->where('product_saleprice > 0');
						break;
					
					case 's_disabled':
						$this->db->where('product_disabled',1);
						break;
					
					case 's_archived':
						$include_archived = true;
						break;
					
					case 's_nophotos':
						$this->db->where('(product_image is null OR product_image = "")');
						break;
						
					case 's_stocklevel':
						$this->db->where('location_1 <= 10');
						break;
					
					case 's_category':
						$cat_col_id = explode('-',$criteria_value);
						switch ($cat_col_id[0]) {
							case "category":
							$this->db->where('cat_id',$cat_col_id[1]);
							break;
							
							case "collection":
							$this->db->join('collection_items','inventory.product_id = collection_items.product_id');
							$this->db->where('collection_id',$cat_col_id[1]);
							break;
							
							default:
							break;
						}
					break;

					case 's_productno':
						if (!empty($criteria_value)) {
							$criteria_value = str_replace('"', '', $criteria_value);
							if (preg_match('@id:([0-9]+)@', $criteria_value, $match)) {
								$this->db->where('product_id', trim($match[1]));
							} else {
								$multiple_products = explode(',',$criteria_value);
								foreach ($multiple_products as $m){
									$m_sql[] =  'product_name LIKE "%' . trim($m) . '%" 
												OR product_ean LIKE "%' . trim($m) . '%"
												OR (CASE WHEN product_type = "variation" THEN ( SELECT GROUP_CONCAT(product_no, " ", product_name SEPARATOR " ") FROM inventory i1 WHERE parent_id = inventory.product_id ) ELSE product_no END) LIKE "%'.trim($m).'%"';
								}
								$m_sql_query = implode(' OR ', $m_sql);
								$this->db->where('('.$m_sql_query.')');
							}
						}
						break;

					//Adjust the channels query string into a single array
					case (preg_match('@s_channel_(.?+)@', $criteria_name) ? true : false) :
						if (!empty($criteria_name)) {
							$channel_value = str_replace('s_', '', $criteria_name);
							$channel_value = "$channel_value = 1";
							$channel_field[] = $channel_value;
						}
						break;

					default:
					break;
				}
				
			}

			//Filter the channels now
			if (count($channel_field) > 0) {
				$channel_sql_query = implode(' OR ', $channel_field);
				$this->db->where("( $channel_sql_query )");
			}

		}
		//End: Results filter

		//Include archived products?
		if ($include_archived != true) {
			$this->db->where('archived', 0);
		}

		return $this->db->count_all_results();
	}

	#------------------------------------------------------
	# Get all products
	# - retrieves products with pagination
	#------------------------------------------------------
	function listAllProducts($num,$offset) {
		
		$this->db->select('
			*, 
			(case when product_type = "variation"
				then 
					( select group_concat(product_no, " ", product_name separator " ") from inventory i1 where parent_id = inventory.product_id )
				else
					product_no
				end		
			) as product_codes, 
			# Category breadcrumb
			CONCAT_WS(
				" &raquo; ",
				(select cat_name from category where cat_id = (select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = inventory.cat_id))),
				(select cat_name from category where cat_id = (select cat_father_id from category where cat_id = inventory.cat_id)),
				(select cat_name from category where cat_id = inventory.cat_id)
			) AS category
		', false);
		$this->db->from('inventory');
		$this->db->where('parent_id', 0);
		
		//Start: Results filter
		if(!empty($_POST)) {
			$segment = http_build_query($_POST);
		} else {
			$segment = $this->uri->segment(4);
		}

		if ($segment != '') {
			$query_string = explode('&', urldecode($segment));
			
			foreach ($query_string as $filter) {
				$parse_filter = explode('=',$filter);
				$criteria_name = $parse_filter[0];
				$criteria_value = urldecode($parse_filter[1]);
				
				switch ($criteria_name) {
				
					case 's_sale':
						$this->db->where('product_saleprice > 0');
						break;
					
					case 's_disabled':
						$this->db->where('product_disabled',1);
						break;

					case 's_archived':
						$include_archived = true;
						break;
					
					case 's_nophotos':
						$this->db->where('(product_image is null OR product_image = "")');
						break;
						
					case 's_stocklevel':
						$this->db->where('location_1 <= 10');
						break;
						
					case 's_category':
						$cat_col_id = explode('-',$criteria_value);
						switch ($cat_col_id[0]) {
							case "category":
							$this->db->where('cat_id',$cat_col_id[1]);
							break;
							
							case "collection":
							$this->db->join('collection_items','inventory.product_id = collection_items.product_id');
							$this->db->where('collection_id',$cat_col_id[1]);
							break;
							
							default:
							break;
						}
					break;
					
					case 's_productno':
						if (!empty($criteria_value)) {
							$criteria_value = str_replace('"', '', $criteria_value);
							if (preg_match('@id:([0-9]+)@', $criteria_value, $match)) {
								$this->db->where('product_id', trim($match[1]));
							} else {
								$multiple_products = explode(',',$criteria_value);
								foreach ($multiple_products as $m){
									$m_sql[] =  'product_name LIKE "%' . trim($m) . '%" 
												OR product_ean LIKE "%' . trim($m) . '%"
												OR product_codes LIKE "%'.trim($m).'%"';
								}
								$m_sql_query = implode(' OR ', $m_sql);
								$this->db->having('('.$m_sql_query.')');
							}
						}
						break;
					
					case 'sort':
						$sort_field = $criteria_value;
						break;
							
					case 'sort_type':
						$sort_type = $criteria_value;
						break;

					//Adjust the channels query string into a single array
					case (preg_match('@s_channel_(.?+)@', $criteria_name) ? true : false) :
						if (!empty($criteria_name)) {
							$channel_value = str_replace('s_', '', $criteria_name);
							$channel_value = "$channel_value = 1";
							$channel_field[] = $channel_value;
						}
						break;
					
					default:
					break;
				}
				
			}

			//Filter the channels now
			if (count($channel_field) > 0) {
				$channel_sql_query = implode(' OR ', $channel_field);
				$this->db->where("( $channel_sql_query )");
			}

		}
		//End: Results filter

		//Include archived products?
		if ($include_archived != true) {
			$this->db->where('archived', 0);
		}

		//Start: Column sort
		if ($sort_field != null && $sort_type != null) {
			$this->db->order_by($sort_field,$sort_type);
		} else {
			$this->db->order_by('category','asc');
			$this->db->order_by('product_name','asc');
		}
		//End: Column sort
		
		$this->db->limit($num,$offset);
		$query = $this->db->get();
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	}

	#------------------------------------------------------
	# Export CSV file
	#------------------------------------------------------
	function exportInventory() {

		$delimiter = '","';
		$newline = "\r\n";

		//Stock locations
		$locations = $this->getLocations();
		foreach($locations as $location) {
			$locations_array[] = "location_$location->id";
			$locations_csv_column_titles[] = "$location->name Qty";
		}
		$location_sql_select = implode(', ', $locations_array);

		$this->db->select('
			inventory.product_id, 
			product_type, 
			inventory.cat_id,
			
			# Category breadcrumb
			CONCAT_WS(
				" > ",
				(select cat_name from category where cat_id = (select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = inventory.cat_id))),
				(select cat_name from category where cat_id = (select cat_father_id from category where cat_id = inventory.cat_id)),
				(select cat_name from category where cat_id = inventory.cat_id)
			) AS category,

			# Category slug
			CONCAT_WS(
				"/",
				(select cat_slug from category where cat_id = (select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = inventory.cat_id))),
				(select cat_slug from category where cat_id = (select cat_father_id from category where cat_id = inventory.cat_id)),
				(select cat_slug from category where cat_id = inventory.cat_id)
			) AS cat_url,

			product_name, 
			product_slug, 
			product_brand, 
			product_description, 
			product_excerpt,
			product_no, 
			product_ean, 
			product_mpn, 
			product_upc, 
			product_costprice, 
			product_price, 
			product_saleprice, 
			product_weight, 
			product_image, 
			product_tags, 
			product_views, 
			product_meta_title as meta_title, 
			product_meta_description as meta_description, 
			product_meta_keywords as meta_keywords, 
			product_disabled, 
			supplier_code,
			(case when product_type = "variation"
				then 
					( select group_concat(product_no, " ", product_name separator " ") from inventory i1 where parent_id = inventory.product_id )
				else
					product_no
				end	
			) as product_codes,
		' . $location_sql_select, false);
		$this->db->from('inventory');
		$this->db->where('parent_id', 0);
		
		//Start: Results filter
		if(!empty($_POST)) {
			$segment = $this->input->post('export_filter');
		} else {
			$segment = $this->uri->segment(4);
		}

		if ($segment != '') {
			$query_string = explode('&', urldecode($segment));
			
			foreach ($query_string as $filter) {
				$parse_filter = explode('=',$filter);
				$criteria_name = $parse_filter[0];
				$criteria_value = urldecode($parse_filter[1]);
				
				switch ($criteria_name) {
				
					case 's_sale':
						$this->db->where('product_saleprice > 0');
						break;
					
					case 's_disabled':
						$this->db->where('product_disabled',1);
						break;

					case 's_archived':
						$include_archived = true;
						break;
					
					case 's_nophotos':
						$this->db->where('(product_image is null OR product_image = "")');
						break;
						
					case 's_stocklevel':
						$this->db->where('location_1 <= 10');
						break;
						
					case 's_category':
						$cat_col_id = explode('-',$criteria_value);
						switch ($cat_col_id[0]) {
							case "category":
							$this->db->where('cat_id',$cat_col_id[1]);
							break;
							
							case "collection":
							$this->db->join('collection_items','inventory.product_id = collection_items.product_id');
							$this->db->where('collection_id',$cat_col_id[1]);
							break;
							
							default:
							break;
						}
					break;
					
					case 's_productno':
						if (!empty($criteria_value)) {
							if (preg_match('@id:([0-9]+)@', $criteria_value, $match)) {
								$this->db->where('product_id', trim($match[1]));
							} else {
								$multiple_products = explode(',',$criteria_value);
								foreach ($multiple_products as $m){
									$m_sql[] =  'product_name LIKE "%' . trim($m) . '%" 
												OR product_ean LIKE "%' . trim($m) . '%"
												OR product_codes LIKE "%'.trim($m).'%"';
								}
								$m_sql_query = implode(' OR ', $m_sql);
								$this->db->having('('.$m_sql_query.')');
							}
						}
						break;
					
					case 'sort':
						$sort_field = $criteria_value;
						break;
							
					case 'sort_type':
						$sort_type = $criteria_value;
						break;

					//Adjust the channels query string into a single array
					case (preg_match('@s_channel_(.?+)@', $criteria_name) ? true : false) :
						if (!empty($criteria_name)) {
							$channel_value = str_replace('s_', '', $criteria_name);
							$channel_value = "$channel_value = 1";
							$channel_field[] = $channel_value;
						}
						break;
					
					default:
					break;
				}
				
			}

			//Filter the channels now
			if (count($channel_field) > 0) {
				$channel_sql_query = implode(' OR ', $channel_field);
				$this->db->where("( $channel_sql_query )");
			}

		}
		//End: Results filter

		//Include archived products?
		if ($include_archived != true) {
			$this->db->where('archived', 0);
		}

		//Start: Column sort
		if ($sort_field != null && $sort_type != null) {
			$this->db->order_by($sort_field,$sort_type);
		} else {
			$this->db->order_by('category','asc');
			$this->db->order_by('product_name','asc');
		}
		//End: Column sort
		
		$query = $this->db->get();

		$csv_column_titles = array(
			"Type", 
			"Product Id", 
			"Variant Id",
			"Category Id", 
			"Category", 
			"Name", 
			"Brand", 
			"Description", 
			"Excerpt",
			"Product No", 
			"EAN", 
			"MPN", 
			"UPC", 
			"Supplier Code", 
			"Cost Price",  
			"Price", 
			"Sale Price", 
			"Weight", 
			"Image", 
			"Tags", 
			"Views", 
			"Meta Title", 
			"Meta Description", 
			"Meta Keywords", 
			"Disabled", 
			"Product URL",
		);
		
		$csv_column_titles = array_merge($csv_column_titles, $locations_csv_column_titles);
		
		$csv_data_row = '"' . strtoupper( implode($delimiter,$csv_column_titles) ) . '"' . $newline;
		
		foreach ($query->result() as $inv) {
		
			if ($inv->product_image != ''){
				$product_images = explode(';', $inv->product_image);
				$product_image = site_root('image/resize/'.$product_images[0]);
			} else {
				$product_image = '';
			}
			
			//Count occurences of '/' in the cat_url
			$cat_url = str_replace(' > ', '/', $inv->cat_url);
			$cat_url_check = substr_count($cat_url, '/');
			
			if ($cat_url_check == 0) {
				//is in grandparent category only
				$product_url = site_root($cat_url . '/-/-/' . $inv->product_slug . '/' . $inv->product_id);
			} elseif ($cat_url_check == 1) {
				//is in parent
				$product_url = site_root($cat_url . '/-/' . $inv->product_slug . '/' . $inv->product_id);
			} else {
				//is in child
				$product_url = site_root($cat_url . '/' . $inv->product_slug . '/' . $inv->product_id);
			}

			/*********************************************************************
			* PRODUCT VARIATION 
			* If this is a variation (parent) then we need to get all the variants
			*********************************************************************/
			if ($inv->product_type == "variation") {
			
				//Get the variants
				$variant_fields = "product_type, product_id, product_name, product_no, product_ean, product_mpn, product_upc, supplier_code, product_costprice, product_price, product_saleprice, product_weight, product_disabled";

				$variations = $this->getVariations($inv->product_id, $variant_fields);
				
				foreach($variations as $variant) {
				
					$variant_product_name = "$inv->product_name - $variant->product_name";
				
					//Add this variant to csv
					$data = array(
						$variant->product_type,
						$inv->product_id,
						$variant->product_id,
						$inv->cat_id,
						$inv->category,
						csv_cleanse($variant_product_name),
						$inv->product_brand,
						csv_cleanse($inv->product_description),
						csv_cleanse($inv->product_excerpt),
						$variant->product_no,
						$variant->product_ean,
						$variant->product_mpn,
						$variant->product_upc,
						$variant->supplier_code,
						$variant->product_costprice,
						$variant->product_price,
						$variant->product_saleprice,
						$variant->product_weight,
						$product_image,
						$inv->product_tags,
						$inv->product_views,
						$inv->meta_title, 
						csv_cleanse($inv->meta_description), 
						csv_cleanse($inv->meta_keywords), 
						$variant->product_disabled,
						$product_url,
					); 

					foreach($locations as $location) {
						$location_field = "location_$location->id";
						$data[] = $variant->$location_field;
					}
				
					$csv_data_row .= '"' . implode($delimiter,$data) . '"' . $newline;
	
				}
				
			} else {
			//Is a single item
			
				$data = array(
						$inv->product_type,
						$inv->product_id, 
						'',
						$inv->cat_id, 
						$inv->category, 
						csv_cleanse($inv->product_name), 
						$inv->product_brand, 
						csv_cleanse($inv->product_description),
						csv_cleanse($inv->product_excerpt), 
						$inv->product_no, 
						$inv->product_ean,
						$inv->product_mpn,
						$inv->product_upc,
						$inv->supplier_code,
						$inv->product_costprice, 
						$inv->product_price, 
						$inv->product_saleprice, 
						$inv->product_weight, 
						$product_image, 
						$inv->product_tags, 
						$inv->product_views, 
						$inv->meta_title, 
						csv_cleanse($inv->meta_description), 
						csv_cleanse($inv->meta_keywords), 
						$inv->product_disabled, 
						$product_url,
				);
				
				foreach($locations as $location) {
					$location_field = "location_$location->id";
					$data[] = $inv->$location_field;
				}
			
				//Add the single item to the CSV
				$csv_data_row .= '"' . implode($delimiter,$data) . '"' . $newline;
				
			}
		
		}
		
		return $csv_data_row;

	}

	#------------------------------------------------------
	# Developer Export
	# - Returns sorted results for every inventory item
	#------------------------------------------------------
	function developerExport() {
		$this->load->dbutil();
		
		$this->db->from('inventory');
		$this->db->order_by('product_id', 'asc');
		$this->db->order_by('parent_id', 'asc');
		$this->db->order_by('product_order', 'asc');
		$query = $this->db->get();

		return $this->dbutil->csv_from_result($query, ",");
	}

	#------------------------------------------------------
	# Get product
	# @param $product_id(int)		Product ID
	# @param $is_variant(bool)		Is this a variant
	#------------------------------------------------------
	function getProduct($product_id, $is_variant=FALSE) {
		
		if ($is_variant) {
			$select = 'inventory.*, (select product_name from inventory i1 where i1.product_id = inventory.parent_id ) AS parent_name';
		} else {
			$select = '*';
		}
		
		$this->db->select($select);
		$this->db->from('inventory');
		$this->db->where('product_id', $product_id);
		if (!$is_variant) {
		$this->db->join('category', 'category.cat_id = inventory.cat_id');
		}
		$query = $this->db->get();
		
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
	}

	#------------------------------------------------------
	# Add product information to database
	#------------------------------------------------------
	function addProduct($file=null, $array=null) {
		
		//Clean the product tags from any empty tags
		$product_tags = NULL;
		$tags = $this->input->post('product_tags');
		if ($tags != "") {
			$tags = explode(',', $tags);
			$tags = array_filter($tags, 'trim');
			$product_tags = implode(',', $tags);
		}

		$data = array(
			'product_disabled'		=> $this->input->post('product_disabled'),
			'parent_id'				=> $array['parent_id'],
			'product_type'			=> $array['product_type'],
			'cat_id' 				=> $this->input->post('cat_id'),
			'product_name' 			=> $this->input->post('product_name'),
			'product_description' 	=> autop($this->input->post('product_description')),
			'product_excerpt' 		=> autop($this->input->post('product_excerpt')),
			'product_no'		 	=> $this->input->post('product_no'),
			// The set of fields below have been switched to use the $_POST var instead of 
			// CI as they get set as 0 (instead of NULL) when empty using CI's input library
			'product_ean'		 	=> $_POST['product_ean'],
			'product_mpn'		 	=> $_POST['product_mpn'],
			'product_upc'		 	=> $_POST['product_upc'],
			'supplier_code'		 	=> $_POST['supplier_code'],
			// End
			'product_costprice' 	=> $this->input->post('product_costprice'),
			'product_price' 		=> $this->input->post('product_price'),
			'product_saleprice' 	=> $this->input->post('product_saleprice'),
			'product_weight'		=> $this->input->post('product_weight'),
			'product_condition'		=> $this->input->post('product_condition'),
			'product_tags'			=> $product_tags,
			'product_brand'			=> $this->input->post('product_brand'),
			'product_brand_slug'	=> slug($this->input->post('product_brand')),
			'product_meta_title'	=> $this->input->post('product_meta_title'),
			'product_custom_heading'=> $this->input->post('product_custom_heading'),
			'product_meta_description' => $this->input->post('product_meta_description'),
			'product_meta_keywords'	=> $this->input->post('product_meta_keywords'),
			'product_meta_custom'	=> $this->input->post('product_meta_custom'),
			'date_added'			=> date('Y-m-d H:i:s', time()),
		);

		// Create the product slug automatically if no "product_slug" POST var is sent
		if ($this->input->post('product_slug') === FALSE) {
			$data['product_slug'] = slug($this->input->post('product_name'));
		} else {
			$data['product_slug'] = $this->input->post('product_slug');
		}
		
		// If product image has been POST'd, the save to db
		if ($this->input->post('product_image') !== FALSE) {
			$data['product_image'] = $this->input->post('product_image');
		}
		
		// Set the product order if it's set (this is used when 
		// creating duplicates and is solely for variations)
		if ($this->input->post('product_order') !== FALSE) {
			$data['product_order'] = $this->input->post('product_order');
		}

		// Stock locations & channels
		$locations = $this->getLocations();
		foreach($locations as $location) {
			// Stock
			$location_field = "location_$location->id";
			$data[$location_field] = $this->input->post($location_field);
			// Sales channels
			$channel_field = "channel_$location->id";
			$data[$channel_field] = $this->input->post($channel_field);
			// Additional Channel Prices
			if ($location->id > 1) {
				$product_price_field = "channel_$location->id".'_product_price';
				$product_saleprice_field = "channel_$location->id".'_product_saleprice';
				$data[$product_price_field] = $this->input->post($product_price_field);
				$data[$product_saleprice_field] = $this->input->post($product_saleprice_field);
			}
		}

		//Add product file
		if ($file != ''):
			$data['product_file'] = $file;
		else:
			$data['product_file'] = null;
		endif;
		
		//Save search filters
		$filters = $this->filters_model->updateFilters($this->input->post('cat_id'));
		foreach($filters as $filter_field => $filter_val) {
			$data[$filter_field] = $filter_val; // $filter_val is the posted value
		}

		// Get all the coupons we've set up
		$coupons = $this->modules_model->getCoupons();
		// Loop through them all and create the array item to save
		foreach($coupons as $coupon) {
			$coupon_colname = $coupon->field_name;
			$coupon_post_val = ($this->input->post($coupon_colname) > 0) ? 1 : 0;
			$data[$coupon_colname] = $coupon_post_val;
		}
				
		// Insert the date and get the new id
		$this->db->insert('inventory',$data);
		$product_id = $this->db->insert_id();

		return $product_id;
	
	}

	#------------------------------------------------------
	# Update product information
	#------------------------------------------------------
	function updateProduct($file=null, $array=null) 
	{
		
		//Nullify a few things for database cleanliness
		$product_no  	= ($this->input->post('product_no') == '')    ? NULL : $this->input->post('product_no');
		$product_ean 	= ($this->input->post('product_ean') == '')   ? NULL : $this->input->post('product_ean');
		$product_mpn 	= ($this->input->post('product_mpn') == '')   ? NULL : $this->input->post('product_mpn');
		$product_upc 	= ($this->input->post('product_upc') == '')   ? NULL : $this->input->post('product_upc');
		$supplier_code 	= ($this->input->post('supplier_code') == '') ? NULL : $this->input->post('supplier_code');
		$product_tags	= NULL;
		
		//Clean the product tags from any empty tags
		$tags = $this->input->post('product_tags');
		if ($tags != "") {
			$tags = explode(',', $tags);
			$tags = array_filter($tags, 'trim');
			$product_tags = implode(',', $tags);
		}

		$data = array(
			'product_disabled'		=> $this->input->post('product_disabled'),
			'cat_id' 				=> $this->input->post('cat_id'),
			'product_name' 			=> $this->input->post('product_name'),
			'product_description' 	=> autop($this->input->post('product_description')),
			'product_excerpt' 		=> autop($this->input->post('product_excerpt')),
			'product_no'		 	=> $product_no,
			'product_ean'		 	=> $product_ean,
			'product_mpn'		 	=> $product_mpn,
			'product_upc'		 	=> $product_upc,
			'supplier_code'		 	=> $supplier_code,
			'product_costprice' 	=> $this->input->post('product_costprice'),
			'product_price' 		=> $this->input->post('product_price'),
			'product_saleprice' 	=> $this->input->post('product_saleprice'),
			'product_weight'		=> $this->input->post('product_weight'),
			'product_condition'		=> $this->input->post('product_condition'),
			'product_slug'			=> slug($this->input->post('product_slug')),
			'product_tags'			=> $product_tags,
			'product_brand'			=> $this->input->post('product_brand'),
			'product_brand_slug'	=> slug($this->input->post('product_brand')),
			'product_meta_title'	=> $this->input->post('product_meta_title'),
			'product_custom_heading'=> $this->input->post('product_custom_heading'),
			'product_meta_description' => format_meta($this->input->post('product_meta_description')),
			'product_meta_keywords'	=> format_meta($this->input->post('product_meta_keywords'),'keywords'),
			'product_meta_custom'	=> $this->input->post('product_meta_custom'),
		);

		// Save the product_image references
		if (!empty($_POST['gallery_product_image'])) {
			$product_image = implode(';', $_POST['gallery_product_image']);
			$data['product_image'] = "$product_image;"; // Add the trailing semi-colon
		}

		//Stock locations & channels
		$locations = $this->getLocations();
		foreach($locations as $location) {
			// Stock
			$location_field = "location_$location->id";
			$data[$location_field] = $this->input->post($location_field);
			// Sales channels
			$channel_field = "channel_$location->id";
			$data[$channel_field] = $this->input->post($channel_field);
			// Additional Channel Prices
			if ($location->id > 1) {
				$product_price_field = "channel_$location->id".'_product_price';
				$product_saleprice_field = "channel_$location->id".'_product_saleprice';
				$data[$product_price_field] = $this->input->post($product_price_field);
				$data[$product_saleprice_field] = $this->input->post($product_saleprice_field);
			}
		}

		//file not empty and delete_product_file is not set, insert filename
		if ( ($_POST['delete_product_file'] != 'true' && $file != '') || ($_POST['delete_product_file'] == 'true' && $file != '') ):
			$data['product_file'] = $file;
		//delete_product_file is set and file is empty, insert null
		elseif ( ($_POST['delete_product_file'] == 'true' && $file == '') ):
			$data['product_file'] = null;
		endif;
		
		//Save search filters
		$filters = $this->filters_model->updateFilters($this->input->post('cat_id'));
		foreach($filters as $filter_field => $filter_val) {
			$data[$filter_field] = $filter_val; // $filter_val is the posted value
		}

		// Get all the coupons we've set up
		$coupons = $this->modules_model->getCoupons();
		// Loop through them all and create the array item to save
		foreach($coupons as $coupon) {
			$coupon_colname = $coupon->field_name;
			$coupon_post_val = ($this->input->post($coupon_colname) > 0) ? 1 : 0;
			$data[$coupon_colname] = $coupon_post_val;
		}

		$this->db->where('product_id', $_POST['product_id']);
		$this->db->update('inventory', $data);

	}

	#------------------------------------------------------
	# Redirection (301, 404, etc)
	# - this is run after the product is saved so the
	#   data will contain updated slugs
	#------------------------------------------------------
	function redirection($product_id) {
		
		$current_url = get_product_slug($product_id);
		
		//If existing url DOES NOT match the current url for this product then
		//add a redirection
		if ($this->input->post('existing_url') != $current_url) {
			$old_url = $this->input->post('existing_url') . '/' . $product_id;
			$new_url = $current_url . '/' . $product_id;
			$this->redirection_model->create_redirection($old_url, $new_url);
		}
		
	}

	#------------------------------------------------------
	# Update related/similar products
	#------------------------------------------------------
	function updateRelated() {

		//Format the related_items array that is passed via $_POST
		if(is_array($_POST['related_items_id'])):
			while ( list($key,$value) = each($_POST['related_items_id']) and
					list($delete_key,$delete_value) = each($_POST['related_items_delete']) and
					list($type_key,$type_value) = each($_POST['related_items_type']) )
			{
				$this->db->select('xitem_id');
				$this->db->where('product_id',$_POST['product_id']);
				$this->db->where('xitem_id',$value);
				$this->db->where('type',$type_value);
				$query = $this->db->get('xitems');
				
				if ($query->num_rows() == 0) {
					//Not in there, so add it
					$data = array(
						'product_id' => $_POST['product_id'],
						'xitem_id' => $value,
						'type' => $type_value,
					);
					
					$this->db->insert('xitems',$data);
				} else {
					//It's already there, but if delete=true
					//then we need to delete it
					if ($delete_value == 'true') {
						$this->db->where('xitem_id',$value);
						$this->db->where('product_id',$_POST['product_id']);
						$this->db->delete('xitems');
					}
				}
			}
		endif;

	}

	#------------------------------------------------------
	# Save related item (used in product duplication)
	#------------------------------------------------------
	function saveRelated($product_id, $cross_sell_id, $type='R') {
		
		$data = array(
			'product_id' => $product_id,
			'xitem_id'	 => $cross_sell_id,
			'type'		 => $type,
		);
		
		$this->db->insert('xitems', $data);
		
		$newid = $this->db->insert_id();
		return $newid;
		
	}

	#------------------------------------------------------
	# Get images for this product_id
	#------------------------------------------------------
	function getImages($product_id) 
	{
		
		$this->db->select('product_image');
		$this->db->where('product_id',$product_id);
		$query = $this->db->get('inventory');

		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
		
	}
	
	#------------------------------------------------------
	# Append image to this product_id
	#------------------------------------------------------
	function appendImage($product_id,$dbfilename) 
	{
	
		//Get images from db first	
		$this->db->select('product_image');
		$this->db->where('product_id',$product_id);
		$query = $this->db->get('inventory');
		
		foreach ($query->result() as $item) {
			//if ($item->product_image != ''):
			$product_image = $item->product_image;
			//endif;

			//Append above data with the new one passed and update db
			$data = array(
						'product_image' => $product_image . $dbfilename . ';'
					);
			
			$this->db->where('product_id',$product_id);
			$this->db->update('inventory',$data);

		}

	}
	
	#------------------------------------------------------
	# Update image list in db
	# - used for image deletion
	#------------------------------------------------------
	function updateImageList($product_id,$value) {
	
		$data = array(
					'product_image' => $value
				);
		
		$this->db->where('product_id',$product_id);
		$this->db->update('inventory',$data);
	
	}

	#------------------------------------------------------
	# Get products via ajax autocomplete
	# - This retrieves single, variants and variations
	#------------------------------------------------------
	function listProductsLike($product_id,$text,$limit=25) {

		$keywords = explode(' ', $text);
		foreach($keywords as $keyword) {
			$keyword_array[] = " (CASE WHEN inventory.product_type = 'variant' THEN CONCAT_WS(' ', p.product_name, inventory.product_name) ELSE inventory.product_name END) LIKE '%$keyword%' " ;
		}
		$keyword_string = implode(' AND ', $keyword_array);
		
		$this->db->select('
			inventory.product_id, 
			(CASE WHEN inventory.product_type = "variant" THEN
				p.product_name
			ELSE
				inventory.product_name
			END
			) AS parent_name,
			inventory.product_name,  
			inventory.product_description, 
			inventory.product_image, 
			inventory.location_1 as product_qty, 
			inventory.product_disabled,
			inventory.product_type,
			(case when inventory.product_type = "variation"
				then
					( select group_concat(product_no separator ", ") from inventory i1 where parent_id = inventory.product_id ) 
				else
					inventory.product_no
			end) as product_no,
			(case when inventory.product_type = "variation" 
				then 
					(select min((case when product_saleprice > 0 then product_saleprice else product_price end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
				else
					(case when inventory.product_saleprice > 0 then inventory.product_saleprice else inventory.product_price end)
			end) as product_price
		', false);
		$this->db->from('inventory');
		$this->db->join('inventory p', 'inventory.parent_id = p.product_id', 'left');
		$this->db->where('inventory.product_id != '.$product_id);
		$this->db->where('(CASE WHEN inventory.product_type = "variant" THEN p.archived ELSE inventory.archived END) = 0');
		$this->db->where("( ( $keyword_string ) OR inventory.product_no LIKE '%$text%' )");
		$this->db->limit($limit);
		
		$query = $this->db->get();
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	}

	#------------------------------------------------------
	# Get ALL products via ajax autocomplete
	# - Retrieves all SINGLE and VARIANT products
	#------------------------------------------------------
	function listAllProductsLike($product_id, $text, $limit=50, $channel_id) {
	
		$this->the_base_rate = the_base_rate();

		// This big query gets everything we need - all the variants first, with 
		// parent items details and some category data attached
		$sql = '
				select 
					v.product_type,
					(case when p.product_disabled > 0 then p.product_disabled else v.product_disabled end) as product_disabled,	v.product_id as product_id,
					p.product_id as parent_id,
					concat_ws(" - ", p.product_name, v.product_name) as product_name,
					p.product_name as parent_name,
					v.product_name as variant_name,
					v.product_no,';
		
		$sql .=	"v.product_price $this->the_base_rate,
				v.product_saleprice,
				(case when v.product_saleprice > 0 then v.product_saleprice else v.product_price $this->the_base_rate end) as price,";
		
		$sql .=	'v.product_weight,
					v.location_1 as product_qty, 
					p.product_description,
					p.product_image,
					p.cat_id
				from inventory v
				left join inventory p on v.parent_id = p.product_id 
				where v.product_type = "variant"
				and (p.product_name like "%'.$text.'%" OR v.product_no like "%'.$text.'%") 
				and p.channel_'.$channel_id.' = 1
				and v.archived = 0
		';

		// Union the second query
		$sql .= " UNION ALL ";

		// The second query for single items
		$sql .= '
				select 
					v.product_type,
					v.product_disabled as parent_disabled,
					v.product_id as product_id,
					v.product_id as parent_id,
					v.product_name, 
					v.product_name as parent_name,
					"" as variant_name,
					v.product_no,';

		$sql .=	"v.product_price $this->the_base_rate,
				v.product_saleprice,
				(case when v.product_saleprice > 0 then v.product_saleprice else v.product_price $this->the_base_rate end) as price,";
		
		$sql .=	'v.product_weight,
					v.location_1 as product_qty, 
					v.product_description,
					v.product_image,
					v.cat_id
				from inventory v
				where v.product_type = "single"
				and (product_name like "%'.$text.'%" OR product_no like "%'.$text.'%") 
				and v.channel_'.$channel_id.' = 1
				and v.archived = 0
		';

		// Order the results
		$sql .= " order by product_name asc ";
		$sql .= " limit $limit ";
		
		$query = $this->db->query($sql);

		if ($query->num_rows() > 0) {
			return $query->result();
		}
		
	}

	#------------------------------------------------------
	# Insert product attribute
	#------------------------------------------------------
	function addAttribute($product_id,$attribute_name,$attribute_value,$attribute_id=null,$attribute_delete='false',$attribute_order) {
	
		$data = array(
			'product_id' 	  => $product_id,
			'attribute_name'  => $attribute_name,
			'attribute_value' => $attribute_value,
			'attribute_order' => $attribute_order,
		);
				
		// If is existing record, update it
		if ($attribute_id != null && $attribute_delete=='false'):
			$this->db->where('id',$attribute_id);
			$this->db->update('attributes',$data);
		elseif ($attribute_delete=='true'):
			$this->db->where('id',$attribute_id);
			$this->db->delete('attributes');
		else:
			$this->db->insert('attributes',$data);
		endif;
	
	}

	#------------------------------------------------------
	# Get product attributes
	#------------------------------------------------------
	function getAttributes($product_id) {
	
		$this->db->where('product_id',$product_id);
		$this->db->order_by('attribute_order','asc');
		$query = $this->db->get('attributes');
		
		if ($query->num_rows > 0)
		{
			return $query->result();
		}
	
	}

	#------------------------------------------------------
	# Insert product option
	#------------------------------------------------------
	function addProductOption($product_id,$option_label,$option_criteria,$option_price,$option_id=null,$option_delete='false',$option_order) {
	
		$data = array(
					'product_id' 	=> $product_id,
					'option_label'  => $option_label,
					'option_criteria' => $option_criteria,
					'option_price'	=> $option_price,
					'option_order'	=> $option_order,
				);
				
		// If is existing record, update it
		if ($option_id != null && $option_delete=='false'):
			$this->db->where('id',$option_id);
			$this->db->update('product_options',$data);
		elseif ($option_delete=='true'):
			$this->db->where('id',$option_id);
			$this->db->delete('product_options');
		else:
		// else insert it as a new one
			$this->db->insert('product_options',$data);
		endif;
	
	}

	#------------------------------------------------------
	# Get product options
	#------------------------------------------------------
	function getProductOptionGroups($product_id) {
		
		$this->db->select('option_label');
		$this->db->where('product_id',$product_id);
		$this->db->group_by('option_label');
		$this->db->order_by('option_label','asc');
		$query = $this->db->get('product_options');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
		
	}

	function getProductOptions($product_id, $option_label=null) {
	
		$this->db->where('product_id',$product_id);
		if (!empty($option_label)) {
		$this->db->where('option_label',$option_label);
		}
		$this->db->group_by(array('option_label','option_criteria'));
		$this->db->order_by('option_order','asc');
		$this->db->order_by('option_label','asc');
		$this->db->order_by('option_price','asc');
		$query = $this->db->get('product_options');
		
		if ($query->num_rows > 0) {
		
			return $query->result();
		}
	
	}

	#------------------------------------------------------
	# Update product image order
	#------------------------------------------------------
	function updateImageOrder($product_id,$product_images) {
	
		$data = array(
					'product_image' => $product_images,
				);
		
		$this->db->where('product_id',$product_id);
		$this->db->update('inventory',$data);
	
	}

	#------------------------------------------------------
	# Checks slugs for duplicates and increments if
	# there is any
	#------------------------------------------------------
	function check_slug($slug) {
		
		// Start with checking the database for similar slugs		
		$this->db->select_max('product_slug');
		#$this->db->where('product_slug regexp "'. slug($slug) .'(-[0-9])"'); //Not required for item page
		$this->db->or_where('product_slug',slug($slug));
		$this->db->order_by('product_slug','ASC');
		
		$query = $this->db->get('inventory');
						
		foreach ($query->result() as $page) {
			
			$max_slug = $page->product_slug;
			
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

	#------------------------------------------------------
	# List extra categories as an array
	#------------------------------------------------------
	function listExtraCategories($product_id) {
	
		$sql = 'select xcat.cat_id, concat_ws(" &raquo; ",c3.cat_name,c2.cat_name,c1.cat_name) as cat_name
				from xcat
				left join category c1 on xcat.cat_id = c1.cat_id
				left join category c2 on c1.cat_father_id = c2.cat_id
				left join category c3 on c2.cat_father_id = c3.cat_id 
				where product_id = ' . $product_id . ' 
				order by cat_name asc';

		$query = $this->db->query($sql);
		
		if ($query->num_rows() > 0) {

			return $query->result();

		}
	
	}

	#------------------------------------------------------
	# Check if extra category already exists
	#------------------------------------------------------
	function checkExtraCategoryExists($cat_id,$product_id) {
		$this->db->where('product_id',$product_id);
		$this->db->where('cat_id',$cat_id);
		$query = $this->db->get('xcat');
		
		if ($query->num_rows() > 0) {
			return $query->row()->xcat_id;
		} else {
			return false;
		}
	}

	#------------------------------------------------------
	# Delete extra categories before re-adding using the
	# loopable function below
	#------------------------------------------------------
	function removeExtraCategories($product_id) {
		
		$this->db->where('product_id',$product_id);
		$this->db->delete('xcat');
		
	}

	#------------------------------------------------------
	# Add extra categories
	#------------------------------------------------------
	function addExtraCategory($cat_id,$product_id,$product_slug) {
		
		$data = array(
			'cat_id' => $cat_id,
			'product_id' => $product_id,
			'product_slug' => $product_slug,
		);
		
		$this->db->insert('xcat',$data);
		
	}
	
	#------------------------------------------------------
	# Update category
	#------------------------------------------------------
	function setCategory($product_id, $cat_id) {
		
		$this->db->set('cat_id', $cat_id);
		$this->db->where('product_id', $product_id);
		$this->db->update('inventory');
		
	}

	#------------------------------------------------------
	# Delete product
	#------------------------------------------------------
	function deleteProduct($product_id) {

		//Delete product
		$this->db->where('product_id',$product_id);
		$this->db->delete('inventory');
		
		//Delete any related variations
		$this->db->where('parent_id', $product_id);
		$this->db->delete('inventory');

		//Delete product options
		$this->db->where('product_id',$product_id);
		$this->db->delete('product_options');

		//Delete product attributes
		$this->db->where('product_id',$product_id);
		$this->db->delete('attributes');

		//Delete product in additional categories
		$this->db->where('product_id',$product_id);
		$this->db->delete('xcat');

		//Delete product from collections
		$this->db->where('product_id',$product_id);
		$this->db->delete('collection_items');
		
		//Delete product from xitems
		$this->db->where('product_id',$product_id);
		$this->db->delete('xitems');
		
		//Delete product from any related products
		$this->db->where('xitem_id',$product_id);
		$this->db->delete('xitems');

		//Delete the product from any baskets
		$this->db->where('product_id', $product_id);
		$this->db->delete('basket');
		
	}

	#------------------------------------------------------
	# Archive product
	#------------------------------------------------------
	function archiveProduct($product_id) {
		
		//Update the product flags
		$data = array(
			'product_disabled'	=> 1,
			'archived'			=> 1,
			'location_1'		=> 0,
		);
		
		$this->db->where('product_id', $product_id);
		$this->db->update('inventory', $data);

		//Delete product in additional categories
		$this->db->where('product_id',$product_id);
		$this->db->delete('xcat');

		//Delete product from collections
		$this->db->where('product_id',$product_id);
		$this->db->delete('collection_items');
		
		//Delete product from xitems
		$this->db->where('product_id',$product_id);
		$this->db->delete('xitems');
		
		//Delete product from any related products
		$this->db->where('xitem_id',$product_id);
		$this->db->delete('xitems');

		//Delete the product from any baskets
		$this->db->where('product_id', $product_id);
		$this->db->delete('basket');
		
	}
	
	#------------------------------------------------------
	# Unarchive product
	#------------------------------------------------------
	function unarchiveProduct($product_id) {
		
		//Update the product flags
		$data = array(
			'product_disabled'	=> 1,
			'archived'			=> 0,
		);
		
		$this->db->where('product_id', $product_id);
		$this->db->update('inventory', $data);
		
	}
	
	#------------------------------------------------------
	# Get cross-selling items
	#------------------------------------------------------
	function getCrossSells($product_id,$type=null) {
	
		$this->db->select("
			xitems.*,
			inventory.product_type,
			(CASE WHEN inventory.product_type = 'variant' THEN
				p.product_name
			ELSE
				inventory.product_name
			END
			) AS parent_name,
			inventory.product_name,
			(case when inventory.product_type = 'variation'
				then
					( select group_concat(i1.product_no separator ', ') from inventory i1 where parent_id = xitems.xitem_id )
				else
					inventory.product_no 
			end) as product_no,
			(CASE WHEN inventory.product_disabled = '1' OR p.product_disabled = '1' THEN
				1
			ELSE
				0
			END) AS product_disabled,
			xitem_groups.label
		", false);
		$this->db->from('xitems');
		$this->db->join('inventory','xitems.xitem_id = inventory.product_id');
		$this->db->join('inventory p', 'inventory.parent_id = p.product_id', 'left');
		$this->db->join('xitem_groups', 'xitem_groups.type = xitems.type', 'left');
		$this->db->where('xitems.product_id', $product_id);
		if ($type != null) {
		$this->db->where('type',$type);
		}
		$this->db->group_by('xitems.id');
		$this->db->order_by('type asc, product_name asc');
		$query = $this->db->get();
		
		if ($query->num_rows > 0)
		{
			return $query->result();
		}
	
	}	

	#------------------------------------------------------
	# Manage cross-sell groups
	#------------------------------------------------------
	function getCrossSellGroups() {
		
		$this->db->order_by('group_order', 'asc');
		$this->db->order_by('label', 'asc');
		$query = $this->db->get('xitem_groups');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
		
	}

	function getCrossSellGroup($group_id) {
		
		$this->db->where('id', $group_id);
		$query = $this->db->get('xitem_groups');
		
		return $query->row();
		
	}

	function createCrossSellGroup() {
		
		$label = $this->input->post('label');
		$letters = "";
		
		// Create the abbr. type from the label. 
		// We need to make this unique because some of the labels 
		// may begin with the same words. So, we'll check the 
		// number of words first and abbreviate.
		if (substr_count($label, ' ') > 0) {
			
			// Create an array of words
			$words = explode(' ', $label);
			
			// Get the first letter of each word
			foreach($words as $word) {
				$letters .= substr($word, 0, 1);
			}
			
			$type = strtoupper($letters);
			
		} else {
			$type = strtoupper( substr($this->input->post('label'), 0, 4) );
		}
		
		$data = array(
				'label' => $this->input->post('label'),
				'type'	=> $type,
				);
				
		$this->db->insert('xitem_groups', $data);
		
	}
	
	function updateCrossSellGroup($group_id) {

		$data = array(
				'label' => $this->input->post('label'),
				//Ignore updating the 'type' field as we need 
				//to keep the reference intact for existing items
				);
				
		$this->db->where('id', $group_id);
		$this->db->update('xitem_groups', $data);
		
	}

	// Re-order the groups (ajax sort)
	function orderCrossSellGroups($id, $order=0) {
	
		$data = array(
					'group_order' => $order,
				);
		
		$this->db->where('id', $id);
		$this->db->update('xitem_groups', $data);
	
	}

	#------------------------------------------------------
	# Status management
	# - used to updated product_disabled status
	#------------------------------------------------------
	function getItemStatus($product_id) {
	
		$this->db->select('product_disabled');
		$this->db->where('product_id', $product_id);
		$query = $this->db->get('inventory');
		
		$item_status = $query->row()->product_disabled;
		
		return $item_status;
		
	}
	
	// Toggle item status
	function updateItemStatus($product_id, $current_status) {
	
		if ($current_status == 0) {
			//Disable the item
			$data['product_disabled'] = 1;
		} else {
			//Enable the item
			$data['product_disabled'] = 0;
		}
		
		$this->db->where('product_id', $product_id);
		$this->db->update('inventory', $data);
		
		return $data['product_disabled'];
	
	}

	// Set item status
	function setItemStatus($product_id, $int_status=1) {
	
		$this->db->set('product_disabled', $int_status);
		$this->db->where('product_id', $product_id);
		$this->db->update('inventory', $data);
		
	}

	#------------------------------------------------------
	# Get product weight
	#------------------------------------------------------	
	function getWeight($product_id) {
		
		$this->db->select('product_weight');
		$this->db->where('product_id', $product_id);
		$query = $this->db->get('inventory');
		
		if ($query->num_rows() > 0) {
			return $query->row()->product_weight;
		} else {
			return '0.000';
		}
		
	}

	#------------------------------------------------------
	# ATTRIBUTE SETS: Manage attribute sets
	#------------------------------------------------------
	function getAttributeSets() {
	
		$this->db->order_by('attribute_set_label', 'asc');
		$query = $this->db->get('attribute_sets');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		}
	
	}

	function getAttributeSet($attribute_set_id) {
	
		$this->db->where('attribute_set_id', $attribute_set_id);
		$query = $this->db->get('attribute_sets');
		
		if ($query->num_rows() > 0) {
			return $query->row();
		} else {
			return false;
		}
	
	}

	function createAttributeSet() {
	
		$data = array(
			'attribute_set_label' => $this->input->post('attribute_set_label'),
			'attribute_set_desc' => $this->input->post('attribute_set_desc'),
		);
		
		$this->db->insert('attribute_sets', $data);
	
	}

	function updateAttributeSet($attribute_set_id) {
	
		$data = array(
			'attribute_set_label' => $this->input->post('attribute_set_label'),
			'attribute_set_desc' => $this->input->post('attribute_set_desc'),
		);
		
		$this->db->where('attribute_set_id', $attribute_set_id);
		$this->db->update('attribute_sets', $data);
	}

	function addAttributeToSet($attribute_set_id,$attribute_name,$attribute_value,$attribute_id=null,$attribute_delete='false',$attribute_order) {

		$data = array(
			'attribute_set_id' => $attribute_set_id,
			'attribute_name'  => $attribute_name,
			'attribute_value' => $attribute_value,
			'attribute_order' => $attribute_order,
		);
				
		// If is existing record, update it
		if ($attribute_id != null && $attribute_delete=='false'):
			$this->db->where('id',$attribute_id);
			$this->db->update('attribute_set_templates', $data);
		elseif ($attribute_delete=='true'):
			$this->db->where('id',$attribute_id);
			$this->db->delete('attribute_set_templates');
		else:
			$this->db->insert('attribute_set_templates', $data);
		endif;
	
	}

	function getAttributesForSet($attribute_set_id) {
	
		$this->db->where('attribute_set_id', $attribute_set_id);
		$this->db->order_by('attribute_order', 'asc');
		$query = $this->db->get('attribute_set_templates');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		}
	}


	#------------------------------------------------------
	# PRODUCT OPTIONS: Manage product option sets
	#------------------------------------------------------
	function getProductOptionSets() {
	
		$this->db->order_by('option_set_label', 'asc');
		$query = $this->db->get('productoption_sets');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		}
	
	}

	function getProductOptionSet($option_set_id) {
	
		$this->db->where('option_set_id', $option_set_id);
		$query = $this->db->get('productoption_sets');
		
		if ($query->num_rows() > 0) {
			return $query->row();
		} else {
			return false;
		}
	
	}

	function createProductOptionSet() {
	
		$data = array(
			'option_set_label' => $this->input->post('option_set_label'),
			'option_set_desc' => $this->input->post('option_set_desc'),
		);
		
		$this->db->insert('productoption_sets', $data);
	
	}

	function updateProductOptionSet($option_set_id) {
	
		$data = array(
			'option_set_label' => $this->input->post('option_set_label'),
			'option_set_desc' => $this->input->post('option_set_desc'),
		);
		
		$this->db->where('option_set_id', $option_set_id);
		$this->db->update('productoption_sets', $data);
	}

	function addProductOptionToSet($option_set_id,$option_label,$option_criteria,$option_price,$option_id,$option_delete,$option_order) {

		$data = array(
			'option_set_id'   => $option_set_id,
			'option_label'    => $option_label,
			'option_criteria' => $option_criteria,
			'option_price'    => $option_price,
			'option_order'    => $option_order,
		);
				
		// If is existing record, update it
		if ($option_id != null && $option_delete=='false'):
			$this->db->where('id', $option_id);
			$this->db->update('productoption_set_templates', $data);
		elseif ($option_delete=='true'):
			$this->db->where('id', $option_id);
			$this->db->delete('productoption_set_templates');
		else:
			$this->db->insert('productoption_set_templates', $data);
		endif;
	
	}

	function getProductOptionsForSet($option_set_id) {
	
		$this->db->where('option_set_id', $option_set_id);
		$this->db->order_by('option_order', 'asc');
		$query = $this->db->get('productoption_set_templates');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		}
	}

	#------------------------------------------------------
	# Lookup brands
	#------------------------------------------------------
	function lookup_brands($str) {
	
		$this->db->select('product_brand');
		$this->db->like('product_brand', $str, 'after');
		$this->db->group_by('product_brand');
		$this->db->order_by('product_brand', 'asc');
		$this->db->limit(6);
	
		$query = $this->db->get('inventory');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		}	
	
	}

	#------------------------------------------------------
	# Lookup supplier
	#------------------------------------------------------
	function lookup_suppliers($str) {
	
		$this->db->select('supplier_code');
		$this->db->like('supplier_code', $str, 'after');
		$this->db->group_by('supplier_code');
		$this->db->order_by('supplier_code', 'asc');
		$this->db->limit(6);
	
		$query = $this->db->get('inventory');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		}	
	
	}

	#------------------------------------------------------
	# Stock locations
	#------------------------------------------------------
	function getLocations($order_by='locked desc, name asc') {
		
		#$this->db->order_by($order_by);
		$this->db->query("select * from locations order by FIELD(id, '1', '2', '3'), name");
		#$this->db->order_by("FIELD(id, '1', '2', '3'), name");
		$query = $this->db->get('locations');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
		
	}

	function getLocation($channel_id) {
		$this->db->where('id', $channel_id);
		$query = $this->db->get('locations');
		if ($query->num_rows() > 0) {
			return $query->row();
		} else {
			return array();
		}
	}
	
	function getLocationByShortname($shortname) {
		$this->db->where('shortname', $shortname);
		$query = $this->db->get('locations');
		if ($query->num_rows() > 0) {
			return $query->row();
		} else {
			return array();
		}
	}
	
	function updateLocation($id, $data) {

		$this->load->dbforge();
		
		$this->db->where('id', $id);
		$this->db->update('locations', $data);
		
		// Check if the price fields are setup for
		// this location and add them if not
		if ($id > 1) {
			
			if ($this->db->field_exists("channel_$id".'_product_price', 'inventory') === FALSE) {
				$fields = array(
					// Create the channels product price field
					"channel_$id".'_product_price' => array(
						'type' 		=> 'decimal',
						'constraint' => array(10,2),
						'null' 		=> true,
						'default'	=> 0,
					)
				);
				$this->dbforge->add_column('inventory', $fields);
				unset($fields);
			}
			
			if (!$this->db->field_exists("channel_$id".'_product_saleprice', 'inventory')) {
				$fields = array(
					// Create the channels product saleprice field
					"channel_$id".'_product_saleprice' => array(
						'type' 		=> 'decimal',
						'constraint' => array(10,2),
						'null' 		=> true,
						'default'	=> 0,
					)
				);
				$this->dbforge->add_column('inventory', $fields);
				unset($fields);
			}
			
		}
		
	}
	
	function addLocation($data) {
	
		// Insert the data into the locations table
		$this->db->insert('locations', $data);

		// Get the new id of the entry we just added
		$id = $this->db->insert_id();
		
		// Add the new column field to the inventory table in the form "location_{$id}"
		$fields = array(
				// Create the location qty field
				'location_'.$id => array(
					'type' 		=> 'int',
					'constraint' => 10,
					'null' 		=> true,
					'default'	=> 0,
				),
				// Channel field
				'channel_'.$id => array(
					'type' 		=> 'int',
					'constraint' => 1,
					'null' 		=> true,
					'default'	=> 1, //Turn on by default
				),
				// Create the channels product price field
				"channel_$id".'_product_price' => array(
					'type' 		=> 'decimal',
					'constraint' => array(10,2),
					'null' 		=> true,
					'default'	=> 0,
				),
				// Create the channels product saleprice field
				"channel_$id".'_product_saleprice' => array(
					'type' 		=> 'decimal',
					'constraint' => array(10,2),
					'null' 		=> true,
					'default'	=> 0,
				),
		);
		
		$this->load->dbforge();
		$this->dbforge->add_column('inventory', $fields);
		unset($fields);
		
		// Add the shortname to the locations table. This is the name appended with the insert_id
		$this->db->set('shortname', slug($data['name'].$id));
		$this->db->where('id', $id);
		$this->db->update('locations');
		
		// Return the id
		return $id;

	}

	function deleteLocation($id) {

		// Load database library
		$this->load->dbforge();
		
		if ($id > 1) {
			$this->db->where('id', $id);
			$this->db->delete('locations');

			// Remove this location from all the products as well
			// by removing the column from the inventory table
			if ($this->db->field_exists('location_'.$id, 'inventory')) {
				$this->dbforge->drop_column('inventory', 'location_'.$id);
			}

			// Remove this channel from all the products as well
			// by removing the column from the inventory table
			if ($this->db->field_exists('channel_'.$id, 'inventory')) {
				$this->dbforge->drop_column('inventory', 'channel_'.$id);
			}

			// Remove this channels prices from all the products as well
			// by removing the columns from the inventory table
			if ($this->db->field_exists('channel_'.$id.'_product_price', 'inventory')) {
				$this->dbforge->drop_column('inventory', 'channel_'.$id.'_product_price');
			}
			if ($this->db->field_exists('channel_'.$id.'_product_saleprice', 'inventory')) {
				$this->dbforge->drop_column('inventory', 'channel_'.$id.'_product_saleprice');
			}
			
		}
		
	}
	
	// Add item to channel
	function addItemToChannel($channel_field, $product_id, $add=true) {
		
		$value = ($add == true) ? 1 : 0;
		
		$this->db->set($channel_field, $value);
		$this->db->where('product_id', $product_id);
		$this->db->update('inventory');
		
	}

	#------------------------------------------------------
	# PRODUCT VARIATIONS: Manage child/parent relations
	#------------------------------------------------------
	function getVariations($parent_id, $select_all=true) {
		
		//Just get the fields we need, else get all (default)
		if (!$select_all) {
			$this->db->select("$select_all");
		}
		
		$this->db->where('parent_id', $parent_id);
		$this->db->order_by('product_order', 'asc');
		$this->db->order_by('product_price', 'asc');
		$query = $this->db->get('inventory');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}

	}
		
	function deleteVariation($product_id) {
		
		$this->db->where('product_id', $product_id);
		$this->db->delete('inventory');
		
		//Delete from basket
		$this->db->where('product_id', $product_id);
		$this->db->delete('basket');
		
	}

	function reorderVariations($child_id, $parent_id, $order) {
		
		$data = array(
			'parent_id' => $parent_id,
			'product_order' => $order,
		);
		
		$this->db->where('product_id', $child_id);
		$this->db->update('inventory', $data);

	}

	#------------------------------------------------------
	# Product conversions
	# - Converts products to 'single' or 'variation'
	# @param $type (string)		'single' or 'variation'
	# @param $product_id (int)	Product ID to convert
	#------------------------------------------------------
	function convertProduct($type=null, $product_id) {
		
		switch($type) {
			
			// Convert variant to single item
			case 'single':
				
				// We need to get this item's information first
				$variant = $this->getProduct($product_id, TRUE);
				
				// Then get the parent's info
				$parent = $this->getProduct($variant->parent_id);
			
				$data = array(
					'parent_id' 			=> 0,
					'product_type' 			=> 'single',
					'cat_id'				=> $parent->cat_id,
					'product_order' 		=> 0,
					'product_name'			=> sprintf('%s - %s', $parent->product_name, unserialize_variant($variant->product_name)),
					'product_brand' 		=> $parent->product_brand,
					'product_brand_slug' 	=> slug($parent->product_brand),
					'product_description' 	=> $parent->product_description,
					'product_slug'			=> slug(sprintf('%s-%s', $parent->product_name, unserialize_variant($variant->product_name))),
					'product_tags'			=> $parent->product_tags,
					'product_disabled' 		=> $parent->product_disabled,
					'product_condition' 	=> $parent->product_condition,
					'product_meta_title' 	=> null,
					'product_meta_custom' 	=> null,
					'product_meta_description' => null,
					'product_meta_keywords' => null,
					'product_custom_heading' => null,
				);
				
				// Get the filters
				foreach ($parent as $key=>$value) {
					if (preg_match('@filter_@', $key)) {
						$data[$key] = $value;
					}
				}
				
				// Copy the channels too
				foreach ($parent as $key=>$value) {
					if (preg_match('@channel_@', $key)) {
						$data[$key] = $value;
					}
				}
				
				// Update the table
				$this->db->where('product_id', $product_id);
				$this->db->update('inventory', $data);
				break;
			
			// Convert single item to variation parent
			case 'variation':

				// We need to get this item's information first
				$item = $this->getProduct($product_id);
				
				$data = array(
					'product_no'		=> null,
					'product_type' 		=> 'variation',
					'product_costprice' => 0,
					'product_price'		=> 0,
					'product_saleprice' => 0,
				);

				// Reset channel product_prices
				foreach ($item as $key=>$value) {
					if (preg_match('@channel_(.+?)_product_(.+?)@', $key)) {
						$data[$key] = 0;
					}
				}

				// Reset stock levels
				foreach ($item as $key=>$value) {
					if (preg_match('@location_@', $key)) {
						$data[$key] = 0;
					}
				}

				// Reset coupon columns
				foreach ($item as $key=>$value) {
					if (preg_match('@coupon_@', $key)) {
						$data[$key] = 0;
					}
				}
				
				// Update the table
				$this->db->where('product_id', $product_id);
				$this->db->update('inventory', $data);
				
				// Delete this item from any baskets as 
				// it's no longer compatible
				$this->db->where('product_id', $product_id);
				$this->db->delete('basket');
				break;
		}
		
	}

}