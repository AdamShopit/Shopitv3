<?php
class Modules_model extends CI_Model {
	
	function Modules_model() {
		parent::__construct();

	}
	
	#------------------------------------------------------
	# Coupons
	#------------------------------------------------------
	function getCoupons() {
		$this->db->select('*, concat("coupon_", id) as field_name', false);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('coupon_codes');
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
	}

	function getCoupon($id) {
		$this->db->where('id', $id);
		$query = $this->db->get('coupon_codes');
		if ($query->num_rows() > 0) {
			return $query->row();
		} else {
			return false;
		}
	}

	function createCoupon() {
		$data = array(
			'code' 		=> uppercase($this->input->post('coupon_code')),
			'label'		=> $this->input->post('coupon_label'),
			'discount'	=> $this->input->post('coupon_discount'),
			'expires'	=> $this->input->post('coupon_expires'),
			'max_spend'	=> $this->input->post('coupon_maxspend'),
			'max_uses'	=> $this->input->post('coupon_maxuses'),
		);
		$this->db->insert('coupon_codes', $data);

		$id = $this->db->insert_id();

		// Add the new column to the inventory table 
		// in the form "coupon_[$id]"
		$fields = array(
				'coupon_'.$id 	=> array(
				'type' 			=> 'tinyint',
				'constraint' 	=> 1,
				'null' 			=> true,
				'default'		=> 0,
			),
		);
		
		$this->dbforge->add_column('inventory', $fields);
		unset($fields);
		
		return $id;
	}

	function updateCoupon($id) {
		$data = array(
			'code' 		=> uppercase($this->input->post('coupon_code')),
			'label'		=> $this->input->post('coupon_label'),
			'discount'	=> $this->input->post('coupon_discount'),
			'expires'	=> $this->input->post('coupon_expires'),
			'max_spend'	=> $this->input->post('coupon_maxspend'),
			'max_uses'	=> $this->input->post('coupon_maxuses'),
		);
		$this->db->where('id', $id);
		$this->db->update('coupon_codes', $data);
	}
	
	function deleteCoupon($id) {
		$this->db->where('id', $id);
		$this->db->delete('coupon_codes');

		// Remove this coupon from all the products as well
		// by removing the column from the inventory table
		if ($this->db->field_exists('coupon_'.$id, 'inventory')) {
			$this->dbforge->drop_column('inventory', 'coupon_'.$id);
		}
	}
	
	function getCouponCode($session_id) {
		$this->db->select('code');
		$this->db->where('session_id', $session_id);
		$query = $this->db->get('coupons');
		if ($query->num_rows() > 0) {
			return $query->row()->code;
		} else {
			return FALSE;
		}
	}
	
	function applyCouponToProduct($coupon_colname, $product_id, $setting) {
		$this->db->set($coupon_colname, $setting);
		$this->db->where("(case when product_type = 'variant' then parent_id else product_id end) = $product_id");
		$this->db->where('product_type != "variation"');
		$this->db->update('inventory');
	}
	
	function applyCouponToAllProducts($coupon_colname, $value=1) {
		$value = ($value > 0) ? 1 : 0;
		$this->db->set($coupon_colname, $value);
		$this->db->where('product_type != "variation"');
		$this->db->update('inventory');
	}
	
	function countCouponProducts($coupon_colname) {
		$this->db->where('product_type != "variation"');
		$this->db->where($coupon_colname, 1);
		return $this->db->count_all_results('inventory');
	}

}