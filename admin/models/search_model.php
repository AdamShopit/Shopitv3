<?php
class Search_model extends CI_Model {
	
	function Search_model() {
		parent::__construct();
	}
	
	#------------------------------------------------------
	# Search orders
	#------------------------------------------------------
	function orders($keyword, $limit=30) {
		
		$keyword = urldecode($keyword);
		
		// Convert the address details into a sing string which we can search on
		$concat_array = array('billing_firstname', 'billing_surname', 'billing_company', 'billing_address1', 'billing_address2', 'billing_city', 'billing_postcode', 'billing_country', 'delivery_firstname', 'delivery_surname', 'delivery_address1', 'delivery_address2', 'delivery_city', 'delivery_postcode');
		$concat = implode(",", $concat_array);
		
		// Do the query
		$this->db->select('*, (order_total + order_shipping + order_vat) as total, (select count(t1.order_id) from orders t1 where (t1.order_status_id = "2") and t1.billing_address1 = t2.billing_address1 and t1.billing_city = t2.billing_city) as orders');
		$this->db->from('orders t2');
		$this->db->where('(order_ref LIKE "%'.$keyword.'%" OR CONCAT_WS(" ",'.$concat.') LIKE "%'.$keyword.'%" OR customer_email LIKE "%'.$keyword.'%")');
		$this->db->where('refund', 0);
		$this->db->order_by('order_date', 'desc');
		$this->db->limit($limit);
		$query = $this->db->get();
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
		
	}

	#------------------------------------------------------
	# Search inventory
	#------------------------------------------------------
	function inventory($keyword, $limit=30) {

		$keyword = urldecode($keyword);
	
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
		$this->db->having('(product_name LIKE "%'.$keyword.'%" OR product_ean LIKE "%'.$keyword.'%" OR product_no LIKE "%'.$keyword.'%" OR product_description LIKE "%'.$keyword.'%" OR product_codes LIKE "%'.$keyword.'%")');
		$this->db->order_by('product_name','asc');
		$this->db->limit($limit);
		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}		
	
	}

	#------------------------------------------------------
	# Search customers
	#------------------------------------------------------
	function customers($keyword, $limit=30) {
		
		$keyword = urldecode($keyword);

		// Convert the address details into a sing string which we can search on
		$concat_array = array('billing_firstname', 'billing_surname', 'billing_city', 'billing_company', 'billing_address1', 'billing_address2', 'billing_postcode', 'billing_country', 'delivery_firstname', 'delivery_surname', 'delivery_city', 'delivery_address1', 'delivery_address2', 'delivery_postcode');
		$concat = implode(",", $concat_array);

		// Do query...
		$this->db->select('order_id, account_id, billing_title, billing_firstname, billing_surname, billing_address1, billing_city, customer_email');
		$this->db->from('orders t2');
		$this->db->where('(CONCAT_WS(" ",'.$concat.') LIKE "%'.$keyword.'%" OR customer_email LIKE "%'.$keyword.'%")');
		$this->db->group_by('account_id, billing_address1, billing_postcode, customer_email');
		$this->db->order_by('account_id');

		$query = $this->db->get();

		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}		

	}
	
}