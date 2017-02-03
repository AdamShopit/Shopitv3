<?php
class Webservices_model extends CI_Model {
	
	function Webservices_model() {
		parent::__construct();
		
		// Get the price field names for this channel
		$this->channel_product_price 	 = $this->config->item('channel_product_price');
		$this->channel_product_saleprice = $this->config->item('channel_product_saleprice');
		
	}

	#------------------------------------------------------
	# Get products based on lookup type
	# @param $type (string) 	'brand', 'keyword', 'tag', 'category'
	# @param $keywords (string)	String to search for
	# @param $limit (int)		Number of products to return
	#------------------------------------------------------
	function products($type='keyword', $keywords, $limit=10) {

		// Set our array of lookup types here
		$lookup_types = array('brand', 'keyword', 'tag', 'category');
		
		// If the lookup type does not exist default it to 'keyword'
		$type = (in_array($type, $lookup_types)) ? $type : 'keyword';
		
		// Set SQL criteria for each lookup type
		switch ($type) {
		
			// Lookup by tags
			case 'tag':
				$this->db->like('product_tags', $keywords);		
				break;
			
			// Lookup by brand
			case 'brand':
				$this->db->where('product_brand_slug', $keywords);
				break;
				
			// Lookup by category
			case 'category':
				$this->db->where('c1.cat_name', $keywords);
				break;
			
			// Lookup by keywords
			default:
				
				//Library for plural, singular functions
				$this->load->helper('inflector');
								
				$terms = explode(' ',$keywords);
								
				foreach ($terms as $term) {
					$singulize = singular($term);
					$sql[] = '(product_name LIKE "%'.$singulize.'%" OR product_description LIKE "%'.$singulize.'%")';
				}
				
				$sql_statement= implode(' OR ',$sql);
				$this->db->where("($sql_statement)");
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
		
		$this->db->select($sql, false);
		$this->db->join('category c1', 'c1.cat_id = inventory.cat_id', 'left');
		$this->db->join('category c2', 'c2.cat_id = c1.cat_father_id', 'left');
		$this->db->join('category c3', 'c3.cat_id = c2.cat_father_id', 'left');
		$this->db->where('product_disabled', 0);
		$this->db->where('product_type !=', 'variant');
		$this->db->where($this->config->item('channel'), 1);
		$this->db->having('my_cat_hide', 0);
		$this->db->order_by('min_price');
		$this->db->limit($limit);
		$query = $this->db->get('inventory');	
		
		if ($query->num_rows() > 0){
			$results = $query->result_array();
		} else {
			$results = array();
		}

		// Uncomment the line below for testing
		#echo sprintf('<pre>%s</pre>', print_r($results, true));
		
		return $results;
	
	}

}