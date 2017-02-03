<?php 
#------------------------------------------------------
# Module: Coupons
# - Apply discount code to customer basket
#------------------------------------------------------
if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Coupons {

	#------------------------------------------------------
	# Display coupon code formfield in basket
	#------------------------------------------------------
	function display_form($label = 'Got a discount code? Enter it here:') {
		
		$CI =& get_instance();
		
		$html = '<form id="frmCouponCode" action="'.site_url('store/coupons/apply').'" method="post">'.
			$CI->session->flashdata('coupon_notice').'	
			<label>'.$label.'</label>
			<input type="text" name="CouponCode" id="CouponCode" class="cart-textbox" maxlength="10" size="10" value="" autocomplete="off" />
			<input type="submit" name="submit" class="btn-applycoupon" value="Apply" />
			</form>';
			
		return $html;
	
	}

	#------------------------------------------------------
	# Check coupon code exists and APPLY
	# - and has not expired
	# - and exceeds max spend requirement
	# - and there are products in the basket that apply
	# @param $code = the coupon code
	# @param $totalprice = the line item total of the basket
	# @param $product_ids = a comma separated list of product_ids
	#------------------------------------------------------
	function check($code, $totalprice) {
	
		$CI =& get_instance();
		
		$data = (object) array();
		
		// First check a coupon with $code exists
		$CI->db->select('code, discount, expires, max_spend, max_uses, counter, concat("coupon_", id) as field_name', FALSE);
		$CI->db->where('code', $code);
		$CI->db->where('counter < max_uses');
		$query = $CI->db->get('coupon_codes');
		
		// If it does, do some further checks
		if ($query->num_rows() > 0) {
		
			$coupon = $query->row();

			// Now check the basket contains items for which this coupon 
			// can be applied and return the total of those items
			$applicable_products_total = $this->check_products($coupon->field_name);
			$applicable_products = ( $applicable_products_total > 0 ) ? TRUE : FALSE;
		
			// Check if coupon has not expired, is within max spend, has applicable items and attach to basket
			if ((strtotime($coupon->expires) >= strtotime(date('Y-m-d')) || $coupon->expires == "0000-00-00") && $totalprice >= $coupon->max_spend && $applicable_products) {
		
				// Before we do anything, delete any existing codes for this customer
				$CI->db->where('session_id', $CI->session->userdata('store_session'));
				$CI->db->delete('coupons');
				
				// Coupon exists, so attach new coupon code to this basket
				$insert = array(
							'session_id' => $CI->session->userdata('store_session'),
							'code'		 => $coupon->code,
						);
				$CI->db->insert('coupons', $insert);
				
				// Return the discount and appropriate messages if any
				$data->{'message'}  = '';
				$data->{'discount'} = $coupon->discount;
			
			} elseif ($totalprice < $coupon->max_spend && $applicable_products) {
			
				$spend = money($coupon->max_spend - $totalprice, true, true, false);
				$data->{'message'}  = "You need to spend $spend more to use your discount code.";
				$data->{'discount'} = 0;

			} elseif (!$applicable_products) {
			
				$data->{'message'}  = 'Sorry, the code you entered is not valid.';
				$data->{'discount'} = 0;
				
			} else {
								
				$data->{'message'}  = 'Sorry, the code you entered has expired.';
				$data->{'discount'} = 0;
				
			}
		
		// If it doesn't, display a message saying so	
		} else {

			$data->{'message'}  = 'Sorry, the discount code you entered could not be applied.';
			$data->{'discount'} = 0;

		}		 
		
		return $data;
	
	}

	#------------------------------------------------------
	# Apply the discount
	# - Check if discount is a percentage or decimal and
	#   return the correct value
	# @param $baskettotal is the total of line items
	#------------------------------------------------------
	function get_discount($baskettotal) {
	
		$CI =& get_instance();
		
		// Check if customer has applied coupon to basket and if it has not expired
		// and is within the max spend
		$CI->db->select('discount, expires, max_spend, max_uses, counter, concat("coupon_", coupon_codes.id) as field_name', FALSE);
		$CI->db->join('coupon_codes', 'coupons.code = coupon_codes.code');
		$CI->db->where('session_id', $CI->session->userdata('store_session'));
		$CI->db->where('(expires >= CURDATE() OR expires = 0000-00-00)');
		$CI->db->where('max_spend <=', $baskettotal);
		$query = $CI->db->get('coupons');
		
		// We've found one, so do the maths...
		if ($query->num_rows() > 0) {
		
			$coupon = $query->row();
			
			// First get the total of all applicable products in the basket
			$applicable_products_total = $this->check_products($coupon->field_name);
		
			// Now we can do the maths and get the correct discount value
			if (substr_count($coupon->discount,'%') > 0) {			 // Check if value contains the percentage
				$discount = str_replace('%', '', $coupon->discount); // If yes (>0) then remove the sign
				$discount = ($discount / 100);						 // convert it to a decimal value
				$discount = $applicable_products_total * $discount;	 // produces the discount as decimal value
			} else {
				$discount = $coupon->discount;	// It's a fixed discount amount e.g. £10.00
			}
		
			// Convert the value to an array so we can count how 
			// many digits are after the decimal point
			$split = explode('.', $discount);
			$decimals = strlen($split[1]);
			
			// If the digits are more than 2, do some rounding down
			if ($decimals > 2) {
				$discount = intval(($discount*100))/100; // We'll round down the figure, so 6.997 will be 6.99
			}
	
			// Return the discount
			return number_format($discount, 2, '.', '');
	
		} else {
			return 0;
		}
	
	}

	#------------------------------------------------------
	# Remove a discount code from basket
	#------------------------------------------------------
	function remove() {
		
		$CI =& get_instance();
		$CI->db->where('session_id', $CI->session->userdata('store_session'));
		$CI->db->delete('coupons');
		
	}

	#------------------------------------------------------
	# Check product to see if they are applicable
	# - return the total of the items that are applicable
	#------------------------------------------------------
	function check_products($coupon_colname) {
		
		$shopit =& get_instance();
		
		$shopit->db->select("sum(basket.product_qty * (case when basket.product_saleprice > 0 then basket.product_saleprice else basket.product_price end)) as total", FALSE);
		$shopit->db->join('inventory', 'inventory.product_id = basket.product_id');
		$shopit->db->where('session_id', $shopit->session->userdata('store_session'));
		$shopit->db->where($coupon_colname, 1);
		$query = $shopit->db->get('basket');
		
		if ($query->num_rows() > 0) {
			return $query->row()->total;
		} else {
			return 0;
		}
		
	}

	#------------------------------------------------------
	# Update the coupon counter on completed order
	#------------------------------------------------------
	function counter($session_id) {
		
		$shopit =& get_instance();
		
		// First get the code for this order from the coupons
		// table, using the session_id as the identifier
		$shopit->db->select('code');
		$shopit->db->where('session_id', $session_id);
		$query = $shopit->db->get('coupons');
		
		if ($query->num_rows() > 0) {
			
			// If a code has been found, decrement the counter by 1
			$shopit->db->set('counter', 'counter+1', FALSE);
			$shopit->db->where('code', $query->row()->code);
			$shopit->db->update('coupon_codes');
			
		}
		
	}

}