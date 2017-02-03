<?php

class Shipping_model extends CI_Model {
	
	function Shipping_model() {
		parent::__construct();
	}

	#------------------------------------------------------
	# Get shipping rules
	#------------------------------------------------------
	function getShippingRules($country) {

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
		// The "* -1" in the  below order by statement forces the results to be descending
		$this->db->order_by('shipping_pref DESC, (CASE WHEN criteria = "category" THEN shipping * -1 ELSE shipping END)');
		
		$query = $this->db->get('shipping');

		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	
	}

	#------------------------------------------------------
	# Get ISO country
	#------------------------------------------------------
	function getISOCountry($country_name) {

		$this->db->select('iso');
		$this->db->where('country_name',$country_name);
		$query = $this->db->get('iso_countries');
		
		if ($query->num_rows() > 0) {
			return $query->row()->iso;
		} else {
			return false;
		}
	}

	#------------------------------------------------------
	# Get country code (number code i.e UK = 826)
	#------------------------------------------------------
	function getCountryCode($country_name) {

		$this->db->select('numcode');
		$this->db->where('country_name',$country_name);
		$query = $this->db->get('iso_countries');
		
		if ($query->num_rows() > 0)
		{
			return $query->row()->numcode;
		} else {
			return false;
		}
	}

	#------------------------------------------------------
	# Get country VAT status
	#------------------------------------------------------
	function getCountryVATStatus($country_name) {
	
		$this->db->select('vat_exempt');
		$this->db->where('country_name', $country_name);	
		$query = $this->db->get('iso_countries');
		
		if ($query->num_rows() > 0) {
			return $query->row()->vat_exempt;
		} else {
			return false;
		}
	
	}
}