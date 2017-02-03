<?php

class Products_model extends CI_Model {
	
	function Products_model() {
		parent::__construct();
		
		// Get the price field names for this channel
		$this->channel_product_price = $this->config->item('channel_product_price');
		$this->channel_product_saleprice = $this->config->item('channel_product_saleprice');
		
	}

	#------------------------------------------------------
	# Get all products
	#------------------------------------------------------
	function getAllProducts() {
		
		$this->db->select('*');
		$this->db->from('inventory');
		$this->db->where('product_disabled','0');
		$this->db->where($this->config->item('channel'), 1);
		$this->db->join('category', 'category.cat_id = inventory.cat_id');
		$query = $this->db->get();
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	}

	#------------------------------------------------------
	# Get latest $no products
	#------------------------------------------------------
	function getLatestProducts($no) {
		
		$sql = 'inventory.cat_id, 
			inventory.product_id, 
			product_name,
			product_type,
			product_brand,
			product_brand_slug, 
			c1.cat_father_id,
			(select cat_slug from category where cat_id = c1.cat_father_id) as parent_cat_slug,
			(select cat_father_id from category where cat_id = c1.cat_father_id) as parent_cat_father_id,
			(select c2.cat_slug from category left join category c2 on category.cat_father_id = c2.cat_id where category.cat_id = c1.cat_father_id) as ancestor_cat_slug,
			(select c2.cat_father_id from category left join category c2 on category.cat_father_id = c2.cat_id where category.cat_id = c1.cat_father_id) as ancestor_cat_father_id,
			';

		// Custom field columns
		$sql .= '(case when product_type = "variation" 
			then 
				(select min('.$this->channel_product_price.') from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
			else 
				'.$this->channel_product_price.' 
		end) ' . the_base_rate() . ' as product_price,
		(case when product_type = "variation" 
			then 
				(select min('.$this->channel_product_saleprice.') from inventory i1 where parent_id = inventory.product_id and '.$this->channel_product_saleprice.' > 0 and product_disabled = 0) 
			else '.$this->channel_product_saleprice.' 
		end) as product_saleprice,
		(case when product_type = "variation" 
			then 
				(select min((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
			else
				(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)
		end) as min_price,
		(case when product_type = "variation" 
			then 
				(select max((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0)
			else
				(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)
		end) as max_price, ';
		
		$sql .=	'product_slug, 
			cat_slug, 
			product_image, 
			product_description, 
			product_excerpt, 
			'.$this->config->item('stock_location').' as product_qty, 
			product_views';
		$this->db->select($sql, false);
		$this->db->from('inventory');
		$this->db->join('category c1', 'c1.cat_id = inventory.cat_id');
		$this->db->where('product_disabled','0');
		$this->db->where($this->config->item('channel'), 1);
		$this->db->where('cat_hide', 0);
		// ORDER BY RAND() performance is bad, so this is our alternative!
		$this->db->where( sprintf('RAND() < (SELECT ((1/COUNT(*)) * %s) FROM inventory)', $no * 10) );
		$this->db->limit($no);
		$query = $this->db->get();
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	}

	#------------------------------------------------------
	# Get products from passed category via cat_slug
	# - $count defines what function returns
	#------------------------------------------------------
	function getProducts($count=false, $cat_id, $num=null, $offset=null) {

		//Set a few things here
		$filter_by_price = FALSE;

		//Database caching
		if ($this->config->item('caching') == 'true') {
		$this->db->cache_on();
		}
		
		if ($offset == null) {$offset = 0;}
		
		//START: Layered Navigation
		$filter_sql_query = "";
		if (!empty($_GET)) {
		
			// Loop through each $get parameter and create the necessary sql
			foreach($_GET as $param=>$value) {
				
				// Only apply those parameters that begin with "f_"
				if (preg_match('/f_/', $param)) {
					$filter_field = 'filter_'.$value;
					if ($this->db->field_exists($filter_field, 'inventory')) {
						$filter_sql[] = "$filter_field=1";
					}
				}
				
			}
			
			// Create the sql string to use further below
			if (!empty($filter_sql)) {
				$filter_sql_query = implode(' AND ', $filter_sql);
			}
			
		}
		//End: Layered Navigation
		
		//START: Sort Results - New code utilising $_GET query string
		if ($_GET['sort'] != '') {
			$sort_field = $this->input->get('sort');
		}
		//END: Sort Results
		
		//START: Price Range - Utilises $_GET query string
		if ($_GET['price_min'] != '' && $_GET['price_max'] != '') {
			$filter_by_price = TRUE;
			$price_min = (int) $this->input->get('price_min');
			$price_max = (int) $this->input->get('price_max');
		}
		//END: Price Range

		//Check if xcat items exist for this category so
		//we can skip the union in the heavy sql query
		$xcat_check = $this->db->query("select xcat_id from xcat where cat_id = $cat_id");
		$xcats = $xcat_check->num_rows();

		// If not count, process the normal products query
		if ($count === false) {

			$sql = 'select 
						inventory.product_id, 
						inventory.cat_id, 
						c1.cat_father_id,
						(select cat_slug from category where cat_id = c1.cat_father_id) as parent_cat_slug,
						(select cat_father_id from category where cat_id = c1.cat_father_id) as parent_cat_father_id,
						(select c2.cat_slug from category left join category c2 on category.cat_father_id = c2.cat_id where category.cat_id = c1.cat_father_id) as ancestor_cat_slug,
						(select c2.cat_father_id from category left join category c2 on category.cat_father_id = c2.cat_id where category.cat_id = c1.cat_father_id) as ancestor_cat_father_id,
						product_name,
						product_type,
						product_brand,
						product_brand_slug,';
			
			$sql .=		'(case when product_type = "variation" 
							then 
								(select min('.$this->channel_product_price.') from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
							else 
								'.$this->channel_product_price.' 
						end) ' . the_base_rate() . ' as product_price,
						(case when product_type = "variation" 
							then 
								(select min('.$this->channel_product_saleprice.') from inventory i1 where parent_id = inventory.product_id and '.$this->channel_product_saleprice.' > 0 and product_disabled = 0) 
							else '.$this->channel_product_saleprice.' 
						end) as product_saleprice, 
						(case when product_type = "variation" 
							then 
								(select min((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
							else
								(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)
						end) as min_price, 
						(case when product_type = "variation" 
							then 
								(select max((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0)
							else
								(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)
						end) as max_price,';
						
			$sql .=		'
						product_slug,
						cat_slug, 
						product_image, 
						product_description, 
						product_excerpt, 
						'.$this->config->item('stock_location').' as product_qty, 
						product_views, 
						date_added, 
						priority ';

		// Else just get the product_id for the count
		} else {
			$sql = 'select inventory.product_id';
		}

		$sql .=	'
				from inventory
				join category c1 on c1.cat_id = inventory.cat_id
				where inventory.cat_id = "' . $cat_id . '" 
				';

		// BEGIN: Layered Navigation
		if (!empty($filter_sql_query)) {
			$sql .= "and ($filter_sql_query) ";
		}
		// END: Layered Navigation

		// BEGIN: Price Ranges
		if ($filter_by_price) {
		
			$sql .= 'and (
						case when product_type = "variation"
						then
							(
								select
									(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' '.the_base_rate().' end) * '.the_vat_rate().' as price
								from inventory i2
								where parent_id = inventory.product_id
								and ( 
									(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' '.the_base_rate().' end) * '.the_vat_rate().' >= '.$price_min.'
									and (case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' '.the_base_rate().' end) * '.the_vat_rate().' <= '.$price_max.'
								)
								limit 1
							)
						else
							(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' '.the_base_rate().' end) * '.the_vat_rate().' >= '.$price_min.'
							and (case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' '.the_base_rate().' end) * '.the_vat_rate().' <= '.$price_max.'
						end
					)';
			
		}
		// END: Price Ranges
		
		$sql .=	'and product_disabled = 0
				and c1.cat_hide = 0 
				and ' . $this->config->item('channel') . ' = 1 ';

		// XCAT items note: product_slug in xcat table no longer used for the item url
		if ($xcats > 0) {
			$sql .=	'union all ';

			// If not count, then process the normal query
			if ($count === false) {

				$sql .=	'select 
							inventory.product_id, 
							inventory.cat_id, 
							c1.cat_father_id,
							(select cat_slug from category where cat_id = c1.cat_father_id) as parent_cat_slug,
							(select cat_father_id from category where cat_id = c1.cat_father_id) as parent_cat_father_id,
							(select c2.cat_slug from category left join category c2 on category.cat_father_id = c2.cat_id where category.cat_id = c1.cat_father_id) as ancestor_cat_slug,
							(select c2.cat_father_id from category left join category c2 on category.cat_father_id = c2.cat_id where category.cat_id = c1.cat_father_id) as ancestor_cat_father_id,
							product_name,
							product_type, 
							product_brand,
							product_brand_slug,';
				
				$sql .= 	'(case when product_type = "variation" 
								then 
									(select min('.$this->channel_product_price.') from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
								else 
									'.$this->channel_product_price.' 
							end) ' . the_base_rate() . ' as product_price,
							(case when product_type = "variation" 
								then 
									(select min('.$this->channel_product_saleprice.') from inventory i1 where parent_id = inventory.product_id and '.$this->channel_product_saleprice.' > 0 and product_disabled = 0) 
								else '.$this->channel_product_saleprice.' 
							end) as product_saleprice,
							(case when product_type = "variation" 
								then 
									(select min((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
								else
									(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)
							end) as min_price,
							(case when product_type = "variation" 
								then 
									(select max((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0)
								else
									(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)
							end) as max_price, ';
	
				$sql .= 	'inventory.product_slug, 
							c1.cat_slug as cat_slug, 
							product_image, 
							product_description, 
							product_excerpt, 
							'.$this->config->item('stock_location').' as product_qty,
							product_views, 
							date_added, 
							0 as priority ';

			// Else get only the product_id for the count
			} else {
				$sql .= 'select inventory.product_id ';
			}

			$sql .=	'
					from xcat
					join inventory on xcat.product_id = inventory.product_id
					join category c1 on inventory.cat_id = c1.cat_id
					where xcat.cat_id = "' . $cat_id . '"
					and xcat.product_id not in (select product_id from inventory where cat_id = '. $cat_id .') 
					';
			// BEGIN: Layered Navigation
			if (!empty($filter_sql_query)) {
				$sql .= "and ($filter_sql_query) ";
			}
			// END: Layered Navigation

			// BEGIN: Price Ranges
			if ($filter_by_price) {
				$sql .= 'and (
							case when product_type = "variation"
							then
								(
									select
										(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' '.the_base_rate().' end) * '.the_vat_rate().' as price
									from inventory i2
									where parent_id = inventory.product_id
									and ( 
										(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' '.the_base_rate().' end) * '.the_vat_rate().' >= '.$price_min.'
										and (case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' '.the_base_rate().' end) * '.the_vat_rate().' <= '.$price_max.'
									)
									limit 1
								)
							else
								(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' '.the_base_rate().' end) * '.the_vat_rate().' >= '.$price_min.'
								and (case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' '.the_base_rate().' end) * '.the_vat_rate().' <= '.$price_max.'
							end
						)';
			}
			// END: Price Ranges

			$sql .=	'and inventory.product_disabled = 0
					and c1.cat_hide = 0 
					and ' . $this->config->item('channel') . ' = 1 ';
	
		}

		// COLUMN SORT: Sort by priority by default (best-sellers) if there is no sort option selected
		// If count, don't run this
		if ($count === false) {
			if ($sort_field == null) {
				$sql .= 'order by min_price asc';
			} else {
				$sql .= 'order by ' . $sort_field;
			}
		}
		
		if ($count == false) {
			$sql .= ' LIMIT '. $offset . ',' . $num;
		}
		
		$query = $this->db->query($sql);
						
		if ($count == false) {
			if ($query->num_rows() > 0) {
				return $query->result();
			} else {
				return array();
			}
		} else {
			return $query->num_rows();
		}

	}

	#------------------------------------------------------
	# Detect if item has any product options assigned
	# - returns true if any
	#------------------------------------------------------
	function has_product_options($product_id) {
	
		$this->db->select('id');
		$this->db->where('product_id',$product_id);
		$query = $this->db->get('product_options');
		
		if ($query->num_rows() > 0) {
			return TRUE;
		}
	
	}

	#------------------------------------------------------
	# Get item
	#------------------------------------------------------
	function getItem($product_id){
		$sql = 'inventory.*,
				c1.cat_slug,
				c1.cat_father_id,
				(CASE WHEN (c1.cat_hide = 1 OR c2.cat_hide = 1 OR c3.cat_hide = 1) THEN
					1
				ELSE
					0
				END) AS cat_hide, 
				';
		
		// Custom field columns
		$sql .= $this->core->custom_fields_sql();
				
		$sql .=	'(case when product_type = "variation" 
					then 
						(select '.$this->channel_product_price.' from inventory i1 where parent_id = inventory.product_id AND product_order <= 1 ORDER BY '.$this->channel_product_price.' ASC LIMIT 1) 
					else 
						'.$this->channel_product_price.' 
				end) ' . the_base_rate() . ' as product_price,
				(case when product_type = "variation" 
					then 
						(select min('.$this->channel_product_saleprice.') from inventory i1 where parent_id = inventory.product_id and '.$this->channel_product_saleprice.' > 0 and product_disabled = 0) 
					else '.$this->channel_product_saleprice.' 
				end) as product_saleprice,
				(case when product_type = "variation" 
					then 
						(select min((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
					else
						(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)
				end) as min_price,
				(case when product_type = "variation" 
					then 
						(select max((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0)
					else
						(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)
				end) as max_price, ';
		
		$sql .=	$this->config->item('stock_location').' as product_qty';
		$this->db->select($sql, false);
		$this->db->from('inventory');
		$this->db->join('category c1','c1.cat_id = inventory.cat_id');
		$this->db->join('category c2','c2.cat_id = c1.cat_father_id', 'left');
		$this->db->join('category c3','c3.cat_id = c2.cat_father_id', 'left');
		$this->db->where('product_id', $product_id);
		$this->db->where($this->config->item('channel'), 1);
		$query = $this->db->get();
		
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
	}

	#------------------------------------------------------
	# Get Price of current product_id
	#------------------------------------------------------
	function getPrice($product_id){
		$this->db->select($this->channel_product_price.', '.$this->channel_product_saleprice);
		$this->db->where('product_id', $product_id);
		$query = $this->db->get('inventory');
		
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
	}


	#------------------------------------------------------
	# Get item attributes
	#------------------------------------------------------
	function getAttributes($product_id) {
	
		$this->db->where('product_id',$product_id);
		$this->db->order_by('attribute_order','asc');
		$query = $this->db->get('attributes');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	
	}

	#------------------------------------------------------
	# Get item product options
	#------------------------------------------------------
	function getProductOptionGroups($product_id) {
		
		$this->db->where('product_id',$product_id);
		$this->db->group_by(array('option_label'));
		$this->db->order_by('option_order','asc');
		$this->db->order_by('option_label','asc');
		$query = $this->db->get('product_options');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
		
	}

	function getProductOptions($product_id,$option_label) {
		
		$this->db->where('product_id',$product_id);
		$this->db->where('option_label',$option_label);
		$this->db->order_by('option_order','asc');
		$this->db->order_by('option_label','asc');
		$this->db->order_by('option_price','asc');
		$query = $this->db->get('product_options');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
		
	}

	function getProductOption($option_id) {
		
		$this->db->where('id',$option_id);
		$query = $this->db->get('product_options');
		
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}

	}

	#------------------------------------------------------
	# Increment product views
	#------------------------------------------------------
	function incrementView($product_id,$current_count) {
		
		$data = array(
					'product_views' => $current_count+1,
				);
		
		$this->db->where('product_id',$product_id);
		$this->db->update('inventory',$data);

	}

	#------------------------------------------------------
	# Search products
	# - retrieve the results
	# - $count returns the total number of results
	#------------------------------------------------------
	function getSearchResults($count=false, $keywords, $num=0, $offset=0) {

		// Custom fields - Return database column names/sql
		// (We're running this here to prevent active record db error)
		#$custom_fields_sql = $this->core->custom_fields_sql();

		//Set a few things here
		$filter_by_price = FALSE;
		
		//START: Layered Navigation
		$filter_sql_query = "";
		if (!empty($_GET)) {
		
			// Loop through each $get parameter and create the necessary sql
			foreach($_GET as $param=>$value) {
				
				// Only apply those parameters that begin with "f_"
				if (preg_match('/f_/', $param)) {
					$filter_field = 'filter_'.$value;
					if ($this->db->field_exists($filter_field, 'inventory')) {
						$filter_sql[] = "$filter_field=1";
					}
				}
				
			}
			
			// Create the sql string to use further below
			if (!empty($filter_sql)) {
				$filter_sql_query = implode(' AND ', $filter_sql);
			}
			
		}
		
		// Add the statement to the sql query
		if (!empty($filter_sql_query)) {
			$this->db->where("($filter_sql_query)");
		}
		//End: Layered Navigation

		//START: Filter by category
		if ($_GET['category'] != '') {
			$cat_id = preg_match( '/([0-9]+?)-/', $this->input->get('category'), $match );
			$this->db->where('inventory.cat_id', $match[1]);
		}
		//END: Filter by category
		
		//START: Sort Results - New code utilising $_GET query string
		if ($_GET['sort'] != '') {
			$sort_field = $this->input->get('sort');
		} else {
			// Sort by min_price if no sort option selected
			$sort_field = 'min_price asc';
		}
		//END: Sort Results

		//START: Price Range - Utilises $_GET query string
		if ($_GET['price_min'] != '' && $_GET['price_max'] != '') {
			$filter_by_price = TRUE;
			$price_min = (int) $this->input->get('price_min');
			$price_max = (int) $this->input->get('price_max');
		}
		//END: Price Range
		
		switch ($this->uri->segment(1)) {
		
			case 'tag':
				$this->db->like('product_tags', $keywords);		
				break;
			
			case 'brand':
				$this->db->where('product_brand_slug', $keywords);
				break;
			
			// via search box
			default:
				
				//Library for plural, singular functions
				$this->load->helper('inflector');
								
				$terms = explode(' ',$keywords);
								
				foreach ($terms as $term) {
					$singulize = singular($term);
					$sql[] = '(product_name LIKE "%'.$singulize.'%" 
							  OR product_description LIKE "%'.$singulize.'%" 
							  OR product_tags LIKE "%'.$singulize.'%"
							  OR 
							  	(CASE WHEN product_type = "variation" THEN
									(
									SELECT GROUP_CONCAT(DISTINCT product_no SEPARATOR ",")
									FROM inventory i3
									WHERE parent_id = inventory.product_id
									AND product_type = "variant"
									AND product_disabled = 0
									)
								ELSE
									product_no
								END) LIKE "%'.$singulize.'%"
							  )'; 
				}
				
				$sql_statement= implode(' AND ',$sql);
				$this->db->where('('.$sql_statement.')');
				break;
				
		}
		
		$sql = 'inventory.cat_id, 
			inventory.product_id, 
			product_name,
			product_type,
			product_brand,
			product_brand_slug,
			c1.cat_father_id,
			(select cat_slug from category where cat_id = c1.cat_father_id) as parent_cat_slug,
			(select cat_father_id from category where cat_id = c1.cat_father_id) as parent_cat_father_id,
			(select c2.cat_slug from category left join category c2 on category.cat_father_id = c2.cat_id where category.cat_id = c1.cat_father_id) as ancestor_cat_slug,
			(select c2.cat_father_id from category left join category c2 on category.cat_father_id = c2.cat_id where category.cat_id = c1.cat_father_id) as ancestor_cat_father_id,
			';

		$sql .= '(case when product_type = "variation" 
					then 
						(select min('.$this->channel_product_price.') from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
					else 
						'.$this->channel_product_price.' 
				end) ' . the_base_rate() . ' as product_price,
				(case when product_type = "variation" 
					then 
						(select min('.$this->channel_product_saleprice.') from inventory i1 where parent_id = inventory.product_id and '.$this->channel_product_saleprice.' > 0 and product_disabled = 0) 
					else '.$this->channel_product_saleprice.' 
				end) as product_saleprice,
				(case when product_type = "variation" 
					then 
						(select min((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
					else
						(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)
				end) as min_price,
				(case when product_type = "variation" 
					then 
						(select max((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0)
					else
						(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)
				end) as max_price,';
		
		$sql .=	'product_slug, 
			c1.cat_slug, 
			product_image, 
			product_description, 
			product_excerpt, 
			'.$this->config->item('stock_location').' as product_qty, 
			product_views,
			(CASE WHEN (c1.cat_hide = 1 OR c2.cat_hide = 1 OR c3.cat_hide = 1) THEN
				1
			ELSE
				0
			END) AS my_cat_hide
			';
		
		if ($count === false) {
			$this->db->select($sql, false);
		} else {
			$count_sql_select = '
				inventory.product_id,
				(CASE WHEN (c1.cat_hide = 1 OR c2.cat_hide = 1 OR c3.cat_hide = 1) THEN
					1
				ELSE
					0
				END) AS my_cat_hide
				';
			$this->db->select($count_sql_select, false);
		}
		$this->db->join('category c1', 'c1.cat_id = inventory.cat_id', 'left');
		$this->db->join('category c2', 'c2.cat_id = c1.cat_father_id', 'left');
		$this->db->join('category c3', 'c3.cat_id = c2.cat_father_id', 'left');
		$this->db->where('product_disabled','0');
		$this->db->where('product_type !=', 'variant');
		$this->db->where($this->config->item('channel'), 1);
		$this->db->having('my_cat_hide', 0);

		// BEGIN: Price Ranges
		if ($filter_by_price) {
			$price_sql = '(
						case when product_type = "variation"
						then
							(
								select
									(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' '.the_base_rate().' end) * '.the_vat_rate().' as price
								from inventory i2
								where parent_id = inventory.product_id
								and ( 
									(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' '.the_base_rate().' end) * '.the_vat_rate().' >= '.$price_min.'
									and (case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' '.the_base_rate().' end) * '.the_vat_rate().' <= '.$price_max.'
								)
								limit 1
							)
						else
							(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' '.the_base_rate().' end) * '.the_vat_rate().' >= '.$price_min.'
							and (case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' '.the_base_rate().' end) * '.the_vat_rate().' <= '.$price_max.'
						end
					)';
			$this->db->where($price_sql, null, false);
		}
		// END: Price Ranges

		if ($count == false) {
			$this->db->order_by($sort_field);
			$this->db->limit($num, $offset);
		}
		
		$query = $this->db->get('inventory');	
		
		if ($count == false) {
			if ($query->num_rows() > 0)
			{
				return $query->result();
			}
		} else {
			return $query->num_rows();
		}
	
	}
	
	#------------------------------------------------------
	# Get THIS collection
	# - $collection_slug is captured from url
	#------------------------------------------------------
	function getThisCollection($collection_slug) {
	
		$this->db->where('collection_slug', $collection_slug);
		$this->db->join('collection_groups', 'collection_groups.id = collections.collection_group', 'left');
		$query = $this->db->get('collections');
		
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
	
	}

	#------------------------------------------------------
	# Get collection items
	#------------------------------------------------------
	function countItemsInCollection($collection_id, $count=true) {
		
		$this->db->select('inventory.product_id, inventory.cat_id');
		$this->db->where('collection_id',$collection_id);
		$this->db->join('inventory','collection_items.product_id = inventory.product_id');
		$this->db->join('category','inventory.cat_id = category.cat_id');
		$this->db->where('product_disabled','0');
		$this->db->where('cat_hide','0');
		$this->db->where($this->config->item('channel'), 1);
		$query = $this->db->get('collection_items');

		if ($count) {
			return $query->num_rows();
		} else {
			return $query->result();
		}
	
	}

	#------------------------------------------------------
	# Get collection items
	#------------------------------------------------------
	function getItemsInCollection($collection_id, $num=null, $offset=0) {

		//START: Sort Results - New code utilising $_GET query string
		if ($_GET['sort'] != '') {
			$sort_field = $this->input->get('sort');
		} else {
			// Sort by itemorder (default) if no sort option selected
			$sort_field = 'itemorder asc';
		}
		//END: Sort Results
		
		$sql = 'collection_id, 
			collection_items.product_id, 
			inventory.cat_id, 
			(select cat_slug from category where cat_id = c1.cat_father_id) as parent_cat_slug,
			(select cat_father_id from category where cat_id = c1.cat_father_id) as parent_cat_father_id,
			(select c2.cat_slug from category left join category c2 on category.cat_father_id = c2.cat_id where category.cat_id = c1.cat_father_id) as ancestor_cat_slug,
			(select c2.cat_father_id from category left join category c2 on category.cat_father_id = c2.cat_id where category.cat_id = c1.cat_father_id) as ancestor_cat_father_id,
			product_name,
			product_type,
			product_brand,
			product_brand_slug,
			product_description, ';
		
		$sql .= '(case when product_type = "variation" 
				then 
					(select min('.$this->channel_product_price.') from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
				else 
					'.$this->channel_product_price.' 
			end) ' . the_base_rate() . ' as product_price,
			(case when product_type = "variation" 
				then 
					(select min('.$this->channel_product_saleprice.') from inventory i1 where parent_id = inventory.product_id and '.$this->channel_product_saleprice.' > 0 and product_disabled = 0) 
				else '.$this->channel_product_saleprice.' 
			end) as product_saleprice,
			(case when product_type = "variation" 
				then 
					(select min((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
				else
					(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)
			end) as min_price,
			(case when product_type = "variation" 
				then 
					(select max((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0)
				else
					(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)
			end) as max_price, ';
			
		$sql .= $this->config->item('stock_location').' as product_qty, 
			product_slug, 
			product_image, 
			product_disabled, 
			cat_father_id, 
			cat_slug, 
			cat_hide, 
			collection_items.order as itemorder';
		$this->db->select($sql, false);
		$this->db->where('collection_id',$collection_id);
		$this->db->join('inventory','collection_items.product_id = inventory.product_id');
		$this->db->join('category c1','inventory.cat_id = c1.cat_id');
		$this->db->where('product_disabled','0');
		$this->db->where('cat_hide','0');
		$this->db->where($this->config->item('channel'), 1);
		$this->db->order_by($sort_field);
		if ($num != null && $offset >= 0) {
			$this->db->limit($num, $offset);
		}
		$query = $this->db->get('collection_items');

		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	
	}

	#------------------------------------------------------
	# Record searches
	#------------------------------------------------------
	function recordSearch($term) {
	
		$data = array(
					'term' => $term,
					'timestamp' => date('Y-m-d H:i:s', time()), 	
				);
		
		$this->db->insert('searches',$data);
	
	}

	#------------------------------------------------------
	# Get alternative products
	#------------------------------------------------------
	function getAlternatives($keywords,$limit=12) {
	
		// Look at product_name and product_description
		// -check for word by adding a trailing space
		$terms = explode(' ',$keywords);

		foreach ($terms as $term) {
			$sql[] = '(product_name like "%'.$term.' %" or product_description like "%'.$term.' %")';
		}		

		$sql_statement= implode(' or ',$sql);

		$sql = 'inventory.cat_id, 
			inventory.product_id, 
			product_name,
			product_type,
			product_brand,
			product_brand_slug,
			c1.cat_father_id,
			(select cat_slug from category where cat_id = c1.cat_father_id) as parent_cat_slug,
			(select cat_father_id from category where cat_id = c1.cat_father_id) as parent_cat_father_id,
			(select c2.cat_slug from category left join category c2 on category.cat_father_id = c2.cat_id where category.cat_id = c1.cat_father_id) as ancestor_cat_slug,
			(select c2.cat_father_id from category left join category c2 on category.cat_father_id = c2.cat_id where category.cat_id = c1.cat_father_id) as ancestor_cat_father_id,
			';
		
		$sql .= '(case when product_type = "variation" 
				then 
					(select min('.$this->channel_product_price.') from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
				else 
					'.$this->channel_product_price.' 
			end) ' . the_base_rate() . ' as product_price,
			(case when product_type = "variation" 
				then 
					(select min('.$this->channel_product_saleprice.') from inventory i1 where parent_id = inventory.product_id and '.$this->channel_product_saleprice.' > 0 and product_disabled = 0) 
				else '.$this->channel_product_saleprice.' 
			end) as product_saleprice,
			(case when product_type = "variation" 
				then 
					(select min((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
				else
					(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)
			end) as min_price,
			(case when product_type = "variation" 
				then 
					(select max((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0)
				else
					(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)
			end) as max_price, ';
		
		$sql .= 'product_slug, 
			c1.cat_slug, 
			product_image, 
			product_description, 
			product_excerpt, 
			'.$this->config->item('stock_location').' as product_qty, 
			product_views,
			(CASE WHEN (c1.cat_hide = 1 OR c2.cat_hide = 1 OR c3.cat_hide = 1) THEN
				1
			ELSE
				0
			END) AS my_cat_hide
			';
		$this->db->select($sql, false);
		$this->db->join('category c1', 'c1.cat_id = inventory.cat_id', 'left');
		$this->db->join('category c2', 'c2.cat_id = c1.cat_father_id', 'left');
		$this->db->join('category c3', 'c3.cat_id = c2.cat_father_id', 'left');
		$this->db->where('('.$sql_statement.')');		
		$this->db->where('product_disabled','0');
		$this->db->where('product_type !=', 'variant');
		$this->db->having('my_cat_hide', 0);
		$this->db->where($this->config->item('channel'), 1);
		$this->db->limit($limit);
		$this->db->order_by('product_price asc');
		
		$query = $this->db->get('inventory');	
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	
	}
	
	#------------------------------------------------------
	# Get cross-sells
	# - Variants shouldn'tbe displayed for related items.
	# @param $product_id (int)		Product ID
	# @param $type (string)			Cross-sell type e.g. R, S and any custom type
	# @param $ignore_type (string)	Ignore product type 'variant' (default), 'variation' 
	#------------------------------------------------------
	function getCrossSells($product_id, $type, $ignore_type=NULL) {
	
		$sql = 'xitems.xitem_id,
				(CASE WHEN inventory.product_type = "variant" THEN
					p.product_id
				ELSE
					inventory.product_id
				END) AS product_id,
				inventory.parent_id,
				(CASE WHEN inventory.product_type = "variant" THEN
					(SELECT cat_father_id FROM category WHERE cat_id = p.cat_id)
				ELSE
					cat_father_id
				END) AS cat_father_id,
				(CASE WHEN inventory.product_type = "variant" THEN
					(SELECT cat_slug FROM category WHERE cat_id = p.cat_id)
				ELSE
					cat_slug
				END) AS cat_slug, 
				(CASE WHEN inventory.product_type = "variant" THEN
					(SELECT c2.cat_slug 
					FROM category 
					LEFT JOIN category c2 ON c2.cat_id = category.cat_father_id
					WHERE category.cat_id = p.cat_id
					)
				ELSE	
					(SELECT cat_slug FROM category WHERE cat_id = c1.cat_father_id)
				END) AS parent_cat_slug,
				(CASE WHEN inventory.product_type = "variant" THEN
					(SELECT c2.cat_father_id 
					FROM category 
					LEFT JOIN category c2 ON c2.cat_id = category.cat_father_id
					WHERE category.cat_id = p.cat_id
					)
				ELSE
					(SELECT cat_father_id FROM category WHERE cat_id = c1.cat_father_id)
				END) AS parent_cat_father_id, 
				(CASE WHEN inventory.product_type = "variant" THEN
					(SELECT c3.cat_slug 
					FROM category 
					LEFT JOIN category c2 ON category.cat_father_id = c2.cat_id 
					LEFT JOIN category c3 ON c3.cat_id = c2.cat_father_id
					WHERE category.cat_id = p.cat_id
					)
				ELSE
					(SELECT c2.cat_slug FROM category LEFT JOIN category c2 ON category.cat_father_id = c2.cat_id WHERE category.cat_id = c1.cat_father_id)
				END) AS ancestor_cat_slug, 
				(CASE WHEN inventory.product_type = "variant" THEN
					(SELECT c3.cat_father_id 
					FROM category 
					LEFT JOIN category c2 ON category.cat_father_id = c2.cat_id 
					LEFT JOIN category c3 ON c3.cat_id = c2.cat_father_id
					WHERE category.cat_id = p.cat_id
					)	
				ELSE
					(SELECT c2.cat_father_id FROM category LEFT JOIN category c2 ON category.cat_father_id = c2.cat_id WHERE category.cat_id = c1.cat_father_id)
				END) AS ancestor_cat_father_id, 
				inventory.product_name,
				p.product_name AS parent_name,
				(CASE WHEN inventory.product_type = "variant" THEN
					p.cat_id
				ELSE
					c1.cat_id
				END) AS cat_id,
				
				(CASE WHEN inventory.product_type = "variant" THEN
					(select cat_hide FROM category WHERE cat_id = p.cat_id)
				ELSE
					c1.cat_hide
				END) AS cat_hide,
				inventory.product_type,
				(CASE WHEN inventory.product_type = "variant" THEN
					p.product_brand
				ELSE
					inventory.product_brand
				END) AS product_brand, 
				(CASE WHEN inventory.product_type = "variant" THEN
					p.product_brand_slug
				ELSE
					inventory.product_brand_slug
				END) AS product_brand_slug, 
				(CASE WHEN inventory.product_type = "variant" THEN
					p.product_description
				ELSE
					inventory.product_description
				END) AS product_description, 
				(CASE WHEN inventory.product_type = "variant" THEN
					p.product_excerpt
				ELSE
					inventory.product_excerpt
				END) AS product_excerpt, 
				inventory.product_no, ';

		$sql .= '(case when inventory.product_type = "variation" 
					then 
						(select min('.$this->channel_product_price.') from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
					else 
						inventory.'.$this->channel_product_price.' 
				end) ' . the_base_rate() . ' as product_price,
				(case when inventory.product_type = "variation" 
					then 
						(select min('.$this->channel_product_saleprice.') from inventory i1 where parent_id = inventory.product_id and '.$this->channel_product_saleprice.' > 0 and product_disabled = 0) 
					else inventory.'.$this->channel_product_saleprice.' 
				end) as product_saleprice,
				(case when inventory.product_type = "variation" 
					then 
						(select min((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
					else
						(case when inventory.'.$this->channel_product_saleprice.' > 0 then inventory.'.$this->channel_product_saleprice.' else inventory.'.$this->channel_product_price.' ' . the_base_rate() . ' end)
				end) as min_price,
				(case when inventory.product_type = "variation" 
					then 
						(select max((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0)
					else
						(case when inventory.'.$this->channel_product_saleprice.' > 0 then inventory.'.$this->channel_product_saleprice.' else inventory.'.$this->channel_product_price.' ' . the_base_rate() . ' end)
				end) as max_price, ';
			
			$sql .=	'inventory.'.$this->config->item('stock_location').' as product_qty, 
				inventory.product_views,
				(CASE WHEN inventory.product_type = "variant" THEN
					p.product_slug
				ELSE
					inventory.product_slug
				END) AS product_slug,
				(CASE WHEN inventory.product_type = "variant" THEN
					p.product_image
				ELSE
					inventory.product_image
				END) AS product_image, 
				inventory.product_disabled';
		$this->db->select($sql, false);
		$this->db->from('xitems');
		$this->db->join('inventory','xitems.xitem_id = inventory.product_id');
		$this->db->join('inventory p', 'inventory.parent_id = p.product_id', 'left');
		$this->db->join('category c1','inventory.cat_id = c1.cat_id', 'left');
		$this->db->where('xitems.product_id',$product_id);
		$this->db->where('type', $type);
		$this->db->where('inventory.product_disabled', 0);
		$this->db->where('IFNULL(p.product_disabled,0)', 0);
		if (!empty($ignore_type)) {
			$this->db->where('inventory.product_type !=', $ignore_type);
		}
		$this->db->where('(inventory.' . $this->config->item('channel') . '= 1 OR p.' . $this->config->item('channel') . ' = 1)');
		$this->db->having('cat_hide', '0');
		$query = $this->db->get();
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
		
	}

	#------------------------------------------------------
	# Get cross-sell groups
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
	
	#------------------------------------------------------
	# List the cross-sell groups & products
	# @param $product_id (int) 		Product ID
	# @param $product_type (string)	'single', 'variation' or 'variant'
	# @returns Array of grouped cross-sells
	#------------------------------------------------------
	function listCrossSells($product_id, $product_type='single') {
	
		$x = 1;
		$g = 0;
		$data = array();
		
		// Get the list of cross-sell groups
		$groups = $this->getCrossSellGroups();
		
		// Uncomment the following line for testing
		#print_r($groups);
		
		// Loop through each group
		if (count($groups) > 0) {
			
			foreach($groups as $group) {

				$g++;
				
				// Add the .active class to the first group so it's open by 
				// default if this is a single item
				$active_group_class = ($product_type == 'single' && $g == 1) ? ' active' : '';
				
				// Get the products for this group if any
				$products = $this->getCrossSells($product_id, $group->type);

				//Get custom field templates
				$customfield_templates = $this->customFieldTemplates();
				
				// Uncomment the line below for testing
				#echo "<pre style='background:white;'>" .print_r($products, true) . "</pre>";
				
				$data[$group->label] = array();
				
				// Loop through the products
				if (count($products) > 0) {

					// Product counter
					$p = 0;
						
					foreach ($products as $item) {
					
						$p++;
						
						// Get product url
						$item_url = $this->core->_product_url($item->cat_father_id, $item);
						
						// Get product image
						$item_productimage = $this->core->_displayThumbnail($item, $image_size);
		
						// Define if this item is on sale or not
						$onsale = ($item->product_saleprice > 0) ? TRUE : FALSE;
		
						// Some useful css classes
						$onsale_css_class = ($onsale) ? "sale-item" : "";
						$css_classes = array("product-$p", "productid-$item->product_id", $item->product_brand_slug, $onsale_css_class, $item->product_type, $item->cat_slug);
						$css_classes = array_filter($css_classes);
						$css_class = implode(" ", $css_classes); //Turn the classes into a space-separated string
				
						// Price range template tag
						$product_price_range = ($item->product_type == 'variation' && $item->min_price != $item->max_price) ? sprintf('%s - %s', money($item->min_price), money($item->max_price)) : money($item->min_price);
						$product_price_range_exvat = ($item->product_type == 'variation' && $item->min_price != $item->max_price) ? sprintf('%s - %s', money($item->min_price, true, true, false), money($item->max_price, true, true, false)) : money($item->min_price, true, true, false);
						
						// Product name
						$product_name = ($item->product_type == 'variant') ? sprintf('%s - %s', $item->parent_name, unserialize_variant($item->product_name)) : $item->product_name;
						
						$data[$group->label]['items'][$p] = array(
							'item_count'			=> $p,
							'item_type' 			=> $item->product_type,
							'item_name' 			=> $product_name,
							'item_code'				=> $item->product_no,
							'item_brand'			=> $item->product_brand,
							'item_brand_slug'		=> $item->product_brand_slug,
							'item_image' 			=> $item_productimage,
							'item_price' 			=> money($item->min_price),
							'item_price_exvat'		=> money($item->min_price, true, true, false),
							'item_max_price'		=> money($item->max_price),
							'item_max_price_exvat' 	=> money($item->max_price, true, true, false),
							'item_price_range'		=> $product_price_range,
							'item_price_range_exvat'=> $product_price_range_exvat,
							'item_product_slug' 	=> $item->product_slug,
							'item_product_id' 		=> $item->xitem_id,
							'item_url'				=> $item_url,
							'item_description'		=> $item->product_description,
							'item_excerpt'			=> $item->product_excerpt,
							'item_summary'			=> get_first_paragraph($item->product_description),
							'css_classes'			=> $css_class,
						);

						// Get custom fields if any for this product and add to array so we
						// can use in the view as $variables or {template_tags}
						// - Each custom field is a database column
						if ($customfield_templates) {
							foreach($customfield_templates as $customfield_template) {
								$custom_field_col = $customfield_template->custom_field_label;
								$customfield_template_label = str_replace('custom_', 'custom:', $customfield_template->custom_field_label);
								$customfield_template_label = str_replace('-', '_', $customfield_template_label);
								if ($customfield_template->custom_field_type == 'editor') {
									$custom_field_data = autop($item->$custom_field_col);
								} else {
									$custom_field_data = trim($item->$custom_field_col);
								}
								$data[$group->label]['items'][$p][$customfield_template_label] = $custom_field_data;
							}
						}
						
					}
				
				}
				
			}
			
		}
	
		// Uncomment the following line for testing purposes
		#echo "<pre style='background:white;'>" .print_r($data, true) . "</pre>";
		
		// Output data
		return $data;
		
	}

	#------------------------------------------------------
	# Get custom field
	#------------------------------------------------------
	function getCustomFields($product_id, $type='inventory') {
	
		$this->db->select('custom_field_title, custom_field_data, custom_field_values.custom_field_label');
		$this->db->from('custom_field_values');
		$this->db->join('custom_field_templates', 'custom_field_values.custom_field_label = custom_field_templates.custom_field_label');
		$this->db->where('custom_field_for', $type);
		$this->db->where('custom_field_values.id', $product_id);
		$query = $this->db->get();
	
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}
		
	}

	// Get a list of all the custom field templates that are setup
	// @param $type (string)	'inventory' or 'order'
	// @param $variant (bool)	Set true to return variant custom fields only
	function customFieldTemplates($type='inventory', $variant=false) {
		
		$variant_int = ($variant === true) ? '1' : '0';
		
		$this->db->where('custom_field_for', $type);
		$this->db->where('template_tag', 1);
		$this->db->where('variants', $variant_int);
		$query = $this->db->get('custom_field_templates');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return false;
		}

		$query->free_result();

	}

	#------------------------------------------------------
	# Get SINGLE variant
	#------------------------------------------------------
	function getVariant($product_id){
	
		$sql = 'product_id,
				product_name, 
				product_no,
				product_ean,
				product_mpn,
				product_upc,
				product_weight,
				product_image,
				product_disabled,';
		$sql .= sprintf('%s AS product_qty,', $this->config->item('stock_location'));
		$sql .= sprintf('%s %s AS product_rrp,', $this->channel_product_price, the_base_rate());
		$sql .= sprintf('(CASE WHEN %s > 0 THEN %s ELSE %s %s END) AS product_price,', $this->channel_product_saleprice, $this->channel_product_saleprice, $this->channel_product_price, the_base_rate());
		$sql .= sprintf('%s,', $this->channel_product_saleprice);
		
		$this->db->select($sql, false);
		$this->db->from('inventory');
		$this->db->where('product_id', $product_id);
		$query = $this->db->get();
		
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
		
	}

	#------------------------------------------------------
	# Get ALL available variations
	#------------------------------------------------------
	function getVariations($parent_id) {

		$sql = 'product_id,
				product_name, 
				product_no,
				product_ean,
				product_mpn,
				product_upc,
				product_weight,
				product_image,
				product_disabled,';
		$sql .= sprintf('%s AS product_qty,', $this->config->item('stock_location'));
		
		// Custom field columns
		$sql .= $this->core->custom_fields_sql('inventory', true);
		
		$sql .= sprintf('%s %s AS product_rrp,', $this->channel_product_price, the_base_rate());
		$sql .= sprintf('(CASE WHEN %s > 0 THEN %s ELSE %s %s END) AS product_price,', $this->channel_product_saleprice, $this->channel_product_saleprice, $this->channel_product_price, the_base_rate());
		$sql .= sprintf('%s,', $this->channel_product_saleprice);
	
		$this->db->select($sql, false);
		$this->db->where('parent_id', $parent_id);
		$this->db->where('product_disabled', 0);
		$this->db->order_by('product_order', 'asc');
		$this->db->order_by('product_price', 'asc');
		$query = $this->db->get('inventory');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		}	
	
	}

}