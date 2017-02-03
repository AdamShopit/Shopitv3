<?php

class Shipping_model extends CI_Model {
	
	function Shipping_model() {
		parent::__construct();
	}

	#------------------------------------------------------
	# Create shipping rule
	# - insert into database
	#------------------------------------------------------
	function createRule() {
		
		$data = array(
				'rule_name' => $this->input->post('rule_name'),
				'country' 	=> $this->input->post('country'),
				'criteria' 	=> $this->input->post('criteria'),
				'operation' => $this->input->post('operation'),
				'value' 	=> $this->input->post('value'),
				'value2'	=> $this->input->post('value2'),
				'shipping' 	=> $this->input->post('shipping'),
				);
		
		$this->db->insert('shipping', $data);

	}

	#------------------------------------------------------
	# Update shipping rule
	#------------------------------------------------------
	function updateRule($rule_id) {
		$data = array(
				'rule_name' => $this->input->post('rule_name'),
				'country' 	=> $this->input->post('country'),
				'criteria' 	=> $this->input->post('criteria'),
				'operation' => $this->input->post('operation'),
				'value' 	=> $this->input->post('value'),
				'value2'	=> $this->input->post('value2'),
				'shipping' 	=> $this->input->post('shipping'),
				);
		
		$this->db->where('rule_id',$rule_id);
		$this->db->update('shipping',$data);
	
	}
	
	#------------------------------------------------------
	# Get shipping rules
	#------------------------------------------------------
	function getRules($country="United Kingdom") {
		
		if (empty($country)) {
			$country = "United Kingdom";
		}

		$this->db->where('country', $country);
		$this->db->order_by('country','desc');
		$this->db->order_by('shipping','asc');
		$query = $this->db->get('shipping');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	}

	#------------------------------------------------------
	# Get A shipping rule
	#------------------------------------------------------
	function getRule($rule_id) {

		$this->db->where('rule_id',$rule_id);
		$query = $this->db->get('shipping');
		
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
	}

	#------------------------------------------------------
	# Get shipping rules for this country
	#------------------------------------------------------
	function getCountryShipping($country) {

		//Preliminary check to see if selected country is 
		//in the database first. If not, then apply the 'RoW' country
		$this->db->select('rule_id');
		$this->db->where('country', $country);
		$query = $this->db->get('shipping');
		if ($query->num_rows() == 0) {
			$country = "Rest of World";
		}
		
		//Now, check again for the country
		$this->db->select('*, (case when criteria = "category" then 1 else 0 end) as shipping_pref');
		$this->db->where('country', $country);
		$this->db->order_by('shipping_pref', 'desc');
		$this->db->order_by('shipping', 'asc');
		
		$query = $this->db->get('shipping');

		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	
	}

	#------------------------------------------------------
	# Delete shipping rule
	#------------------------------------------------------
	function deleteRule($rule_id) {
		
		$this->db->where('rule_id',$rule_id);
		$this->db->delete('shipping');
		
	}

	#------------------------------------------------------
	# Get shipping countries
	#------------------------------------------------------
	function getCountries() {

		$this->db->order_by('is_home', 'desc');
		$this->db->order_by('country_name', 'asc');
		$query = $this->db->get('countries');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	}

	#------------------------------------------------------
	# Get ISO countries
	#------------------------------------------------------
	function getISOCountries($include_RoW=false) {

		#$this->db->select('iso_countries.country_name,iso');
		if ($include_RoW == false) {
		$this->db->where('country_name != "Rest of World"');
		}
		$this->db->order_by('country_name','asc');
		$query = $this->db->get('iso_countries');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	}


	#------------------------------------------------------
	# Add country to database
	# - via ajax
	#------------------------------------------------------
	function addCountry() {
		
		$data = array(
					'country_name' => $_POST['country_name'],
					'is_home'	   => '0',
				);
		
		$this->db->insert('countries',$data);
	}

	#------------------------------------------------------
	# Delete country from database
	#------------------------------------------------------
	function deleteCountry($thiscountry) {
		
		$this->db->where('country_id',$thiscountry);
		$this->db->delete('countries');
		
	}

	#------------------------------------------------------
	# Get this product's category id
	#------------------------------------------------------
	function getCatId($product_id){
		
		$this->db->select('
			(case when product_type = "variant"
			then
				(select cat_id from inventory i where product_id = inventory.parent_id)
			else
				cat_id
			end) as cat_id
		');
		$this->db->from('inventory');
		$this->db->where('product_id', $product_id);
		
		$query = $this->db->get();
		
		if ($query->num_rows() > 0) {
			return $query->row()->cat_id;
		} else {
			return 0;
		}
		
	}

	#------------------------------------------------------
	# Get country name based on iso code
	# @param $code (string or int) - code for the country
	# @param $type (string) - iso or iso3
	#------------------------------------------------------
	function getCountryName($code, $type='iso') {
		
		$this->db->where($type, $code);
		$query = $this->db->get('iso_countries');
		
		if ($query->num_rows() > 0) {
			return $query->row()->country_name;
		} else {
			return 'United Kingdom';
		}
		
	}
	
}