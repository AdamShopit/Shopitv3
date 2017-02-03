<?php

class Orders_model extends CI_Model {
	
	function Orders_model() {
		parent::__construct();
	}


	#------------------------------------------------------
	# Count orders
	# - retrieves total number of orders (i.e. num_rows)
	#------------------------------------------------------
	function countOrders($hide_unprocessed=false) {
		
		$this->db->from('orders t2');
		$this->db->where('refund', 0);

		//Start: Results filter
		if(!empty($_POST)) {
			$segment = http_build_query($_POST);
		} else {
			$segment = $this->uri->segment(4);
		}

		if ($segment != '') {
			$query_string = explode('&',$segment);
			
			foreach ($query_string as $filter) {
				$parse_filter = explode('=',$filter);
				$criteria_name = $parse_filter[0];
				$criteria_value = urldecode($parse_filter[1]);
				
				switch ($criteria_name) {
				
					case (preg_match('@s_orderstatus_(.?+)@', $criteria_name) ? true : false) :
						if (!empty($criteria_name)) {
							if ($criteria_value == 'Dispatched') {
								$order_status_sql[] = "( order_status = '$criteria_value' OR dispatch_date is not null )";
							} elseif($criteria_value == "Refunded") {
								$order_status_sql[] = "( ( select order_ref from orders where order_ref = t2.order_ref and refund = 1 LIMIT 1 ) OR order_status = '$criteria_value' )";
							} elseif($criteria_value == "Unprocessed") {
								$order_status_sql[] = "( order_status_id = 0 )";
							} elseif(is_numeric($criteria_value)) {
								$order_status_sql[] = "( order_status_id = $criteria_value )";
							} else {
								$order_status_sql[] = "( order_status = '$criteria_value' )";
							}
							
							// Process the $order_status_sql array after the 
							// $filter foreach loop has completed
						}
						break;		

					case 's_note':
						$this->db->where('instructions != ""');
						break;

					case 's_search_type':
						$s_search_type = $criteria_value;
						break;
					
					case 's_search':
						if ($s_search_type == 'customer') {
							$this->db->where('(CONCAT(billing_firstname," ",billing_surname) LIKE "%'.$criteria_value.'%")');
						} elseif($s_search_type == 'orderno') {
							$this->db->where('order_ref', $criteria_value);
						} elseif($s_search_type == 'coupon') {
							$this->db->join('coupons', 'coupons.session_id = t2.session_id');
							$this->db->where('code', $criteria_value);
						}
						break;
					
					case 's_from_day':
						$s_from_day = $criteria_value;
						break;

					case 's_from_month':
						$s_from_month = $criteria_value;
						break;
					
					case 's_from_year':
						$s_from_year = $criteria_value;
						break;

					case 's_to_day':
						$s_to_day = $criteria_value;
						break;

					case 's_to_month':
						$s_to_month = $criteria_value;
						break;

					case 's_to_year':
						$s_to_year = $criteria_value;
						break;

					case 'sort':
							$sort_field = $criteria_value;
							break;
							
					case 'sort_type':
							$sort_type = $criteria_value;
							break;
					
					default:
					break;
				}
				
			}

			if ($s_from_year != null) {
			$date_from = $s_from_year . '-' . $s_from_month . '-' . $s_from_day . ' 00:00:01';
			$date_to = $s_to_year . '-' . $s_to_month . '-' . $s_to_day . ' 23:59:59';

			$this->db->where('order_date >=',$date_from);
			$this->db->where('order_date <=',$date_to);
			}

		}
		//End: Results filter

		// Hide unprocessed orders by default but only in this week's activity page
		if ($hide_unprocessed and $this->uri->segment(2) == "") {
			$this->db->where('order_status_id > 0');
		}

		//Process the $order_status_sql array (if it exists)
		if ( !empty($order_status_sql) ) {
			$order_status_query = implode(' OR ', $order_status_sql);
			$this->db->where("( $order_status_query )");
		}

		$this->db->order_by('order_date','asc');
		$query = $this->db->get();
		
		if ($query->num_rows() > 0)
		{
			return $query->num_rows();
		}
	}

	#------------------------------------------------------
	# List all orders
	# - retrieves orders with pagination
	#------------------------------------------------------
	function listOrders($num, $offset, $hide_unprocessed=false) {
		
		$this->db->select('*, (order_total + order_shipping + order_vat) as total, (select count(t1.order_id) from orders t1 where (t1.order_status_id = "2") and t1.billing_address1 = t2.billing_address1 and t1.billing_city = t2.billing_city) as orders');
		$this->db->from('orders t2');
		$this->db->where('refund', 0);

		//Start: Results filter
		if(!empty($_POST)) {
			$segment = http_build_query($_POST);
		} else {
			$segment = $this->uri->segment(4);
		}

		if ($segment != '') {
			$query_string = explode('&',$segment);
			
			foreach ($query_string as $filter) {
				$parse_filter = explode('=',$filter);
				$criteria_name = $parse_filter[0];
				$criteria_value = urldecode($parse_filter[1]);
				
				switch ($criteria_name) {

					case (preg_match('@s_orderstatus_(.?+)@', $criteria_name) ? true : false) :
						if (!empty($criteria_name)) {
							if ($criteria_value == 'Dispatched') {
								$order_status_sql[] = "( order_status = '$criteria_value' OR dispatch_date is not null )";
							} elseif($criteria_value == "Refunded") {
								$order_status_sql[] = "( ( select order_ref from orders where order_ref = t2.order_ref and refund = 1 LIMIT 1 ) OR order_status = '$criteria_value' )";
							} elseif($criteria_value == "Unprocessed") {
								$order_status_sql[] = "( order_status_id = 0 )";
							} elseif(is_numeric($criteria_value)) {
								$order_status_sql[] = "( order_status_id = $criteria_value )";
							} else {
								$order_status_sql[] = "( order_status = '$criteria_value' )";
							}
							
							// Process the $order_status_sql array after the 
							// $filter foreach loop has completed
						}
						break;		
						
					case 's_note':
						$this->db->where('instructions != ""');
						break;
						
					case 's_search_type':
						$s_search_type = $criteria_value;
						break;
					
					case 's_search':
						if ($s_search_type == 'customer') {
							$this->db->where('(CONCAT(billing_firstname," ",billing_surname) LIKE "%'.$criteria_value.'%")');
						} elseif($s_search_type == 'orderno') {
							$this->db->where('order_ref', $criteria_value);
						} elseif($s_search_type == 'coupon') {
							$this->db->join('coupons', 'coupons.session_id = t2.session_id');
							$this->db->where('code', $criteria_value);
						}
						break;					
					
					case 's_from_day':
						$s_from_day = $criteria_value;
						break;

					case 's_from_month':
						$s_from_month = $criteria_value;
						break;
					
					case 's_from_year':
						$s_from_year = $criteria_value;
						break;

					case 's_to_day':
						$s_to_day = $criteria_value;
						break;

					case 's_to_month':
						$s_to_month = $criteria_value;
						break;

					case 's_to_year':
						$s_to_year = $criteria_value;
						break;
					
					case 'sort':
						$sort_field = $criteria_value;
						break;
							
					case 'sort_type':
						$sort_type = $criteria_value;
						break;
					
					default:
					break;
				}
				
			}

			if ($s_from_year != null) {
			$date_from = $s_from_year . '-' . $s_from_month . '-' . $s_from_day . ' 00:00:01';
			$date_to = $s_to_year . '-' . $s_to_month . '-' . $s_to_day . ' 23:59:59';

			$this->db->where('order_date >=',$date_from);
			$this->db->where('order_date <=',$date_to);
			}

		}
		//End: Results filter

		// Hide unprocessed orders by default but only in this week's activity page
		if ($hide_unprocessed and $this->uri->segment(2) == "") {
			$this->db->where('order_status_id > 0');
		}
		
		//Process the $order_status_sql array (if it exists)
		if ( !empty($order_status_sql) ) {
			$order_status_query = implode(' OR ', $order_status_sql);
			$this->db->where("( $order_status_query )");
		}

		//Start: Column sort
		if ($sort_field != null && $sort_type != null) {
			$this->db->order_by($sort_field,$sort_type);
		} else {
			$this->db->order_by('order_date','desc');
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
	function exportOrders() {

		$delimiter = '","';
		$newline = "\r\n";
		
		$this->db->select('
			order_ref, 
			t2.session_id, 
			order_date, 
			concat_ws(" ", billing_title, billing_firstname, billing_surname) as billing_name, 
			billing_address1, 
			billing_address2, 
			billing_city, 
			billing_postcode, 
			billing_country,
			concat_ws(" ", delivery_title, delivery_firstname, delivery_surname) as delivery_name, 
			delivery_address1, 
			delivery_address2, 
			delivery_city, 
			delivery_postcode, 
			delivery_country, 
			customer_email, 
			customer_phone,
			(select group_concat( concat_ws( ":", product_id, product_no, product_name, product_qty, product_price, product_options ) separator "|") from orders_inventory where order_id = t2.order_id) as inventory,
			order_total, 
			order_vat, 
			order_shipping, 
			(order_total + order_shipping + order_vat) as total, 
			shipping_method, 
			order_status, 
			dispatch_date, 
			dispatch_email, 
			transaction_id, 
			transaction_type, 
			transaction_total
		', false);

		$this->db->from('orders t2');

		//Start: Results filter
		if(!empty($_POST)) {
			$segment = $this->input->post('export_filter');
		} else {
			$segment = $this->uri->segment(4);
		}

		if ($segment != '') {
			$query_string = explode('&',$segment);
			
			foreach ($query_string as $filter) {
				$parse_filter = explode('=',$filter);
				$criteria_name = $parse_filter[0];
				$criteria_value = urldecode($parse_filter[1]);
				
				switch ($criteria_name) {
				
					case (preg_match('@s_orderstatus_(.?+)@', $criteria_name) ? true : false) :
						if (!empty($criteria_name)) {
							if ($criteria_value == 'Dispatched') {
								$order_status_sql[] = "( order_status = '$criteria_value' OR dispatch_date is not null )";
							} elseif($criteria_value == "Refunded") {
								$order_status_sql[] = "( ( select order_ref from orders where order_ref = t2.order_ref and refund = 1 LIMIT 1 ) OR order_status = '$criteria_value' )";
							} elseif($criteria_value == "Unprocessed") {
								$order_status_sql[] = "( order_status_id = 0 )";
							} elseif(is_numeric($criteria_value)) {
								$order_status_sql[] = "( order_status_id = $criteria_value )";
							} else {
								$order_status_sql[] = "( order_status = '$criteria_value' )";
							}
							
							// Process the $order_status_sql array after the 
							// $filter foreach loop has completed
						}
						break;		
						
					case 's_note':
						$this->db->where('instructions != ""');
						break;
					
					case 's_search_type':
						$s_search_type = $criteria_value;
						break;
					
					case 's_search':
						if ($s_search_type == 'customer') {
							$this->db->where('(CONCAT(billing_firstname," ",billing_surname) LIKE "%'.$criteria_value.'%")');
						} elseif($s_search_type == 'orderno') {
							$this->db->where('order_ref', $criteria_value);
						} elseif($s_search_type == 'coupon') {
							$this->db->join('coupons', 'coupons.session_id = t2.session_id');
							$this->db->where('code', $criteria_value);
						}
						break;
					
					case 's_from_day':
						$s_from_day = $criteria_value;
						break;

					case 's_from_month':
						$s_from_month = $criteria_value;
						break;
					
					case 's_from_year':
						$s_from_year = $criteria_value;
						break;

					case 's_to_day':
						$s_to_day = $criteria_value;
						break;

					case 's_to_month':
						$s_to_month = $criteria_value;
						break;

					case 's_to_year':
						$s_to_year = $criteria_value;
						break;
					
					case 'sort':
						$sort_field = $criteria_value;
						break;
							
					case 'sort_type':
						$sort_type = $criteria_value;
						break;
					
					default:
					break;
				}
				
			}

			if ($s_from_year != null) {
			$date_from = $s_from_year . '-' . $s_from_month . '-' . $s_from_day . ' 00:00:01';
			$date_to = $s_to_year . '-' . $s_to_month . '-' . $s_to_day . ' 23:59:59';

			$this->db->where('order_date >=',$date_from);
			$this->db->where('order_date <=',$date_to);
			}

		}
		//End: Results filter		

		//Process the $order_status_sql array (if it exists)
		if ( !empty($order_status_sql) ) {
			$order_status_query = implode(' OR ', $order_status_sql);
			$this->db->where("( $order_status_query )");
		}

		//Start: Column sort
		if ($sort_field != null && $sort_type != null) {
			$this->db->order_by($sort_field,$sort_type);
		} else {
			$this->db->order_by('order_date','desc');
		}
		//End: Column sort

		$query = $this->db->get();

		$csv_column_titles = array("Order Ref", "Order Date", "Billing Name", "Billing Address 1", "Billing Address 2", "Billing City", "Billing Postcode", "Billing Country", "Delivery Name", "Delivery Address 1", "Delivery Address 2", "Delivery City", "Delivery Postcode", "Delivery Country", "Email", "Phone", "Inventory", "Item Total", "VAT", "Shipping", "Order Total", "Shipping Method", "Status", "Dispatch Date", "Dispatch Email", "Transaction Id",  "Transaction Type", "Transaction Total", "Coupon Code" );

		$csv_data_row = '"' . strtoupper( implode($delimiter,$csv_column_titles) ) . '"' . $newline;

		foreach ($query->result() as $order) {

			$inventory = "";
			
			//inventory details
			$pieces = explode('|',$order->inventory);
			
			foreach ($pieces as $item):
				
				list($product_id, $product_no, $product_name, $product_qty, $product_price, $product_options) = explode(":", $item);
			
				if (!empty($product_name)):
	
					$product_options = str_replace('@',': ',$product_options);
					$product_options = str_replace('{', '',$product_options);
					$product_options = str_replace('}', ' | ',$product_options);
					$product_options = preg_replace('@ \|\ $@','',$product_options); //Remove the last pipeline symbol
					$product_options = trim($product_options);
				
					$inventory .= $product_qty . ' x ' . str_replace('"','',$product_name) . ' (' . $product_no .'), ';

				endif;
			
			endforeach;

			$order->{'coupon_code'} = $this->modules_model->getCouponCode($order->session_id);
			
			//Get the order status label
			$status = $this->getStatusFromLabel($order->order_status);
			
			if ($status->id > 0) {
				$order_status = $status->label;
			} else {
				$order_status = $order->order_status;
			}

			$data = array(
					$order->order_ref, $order->order_date, $order->billing_name, $order->billing_address1, $order->billing_address2, $order->billing_city, $order->billing_postcode, $order->billing_country, $order->delivery_name,  $order->delivery_address1, $order->delivery_address2, $order->delivery_city, $order->delivery_postcode, $order->delivery_country, $order->customer_email, $order->customer_phone, $inventory, $order->order_total, $order->order_vat, $order->order_shipping, $order->total, $order->shipping_method, $order_status, $order->dispatch_date, $order->dispatch_email, $order->transaction_id, $order->transaction_type, $order->transaction_total, $order->coupon_code
					); 
		
			$csv_data_row .= '"' . implode($delimiter,$data) . '"' . $newline;
		
		}

		return $csv_data_row;

	}

	#------------------------------------------------------
	# List all THIS WEEK'S orders
	#------------------------------------------------------
	function ordersWeekly($from=null, $to=null) {
		
		$this->db->select('*, (order_total + order_shipping + order_vat) as total, (select count(t1.order_id) from orders t1 where (t1.order_status_id = "2") and t1.billing_address1 = t2.billing_address1 and t1.billing_city = t2.billing_city) as orders');
		$this->db->from('orders t2');
		$this->db->where('refund', 0);
		if (!empty($from) && !empty($to)) {
		$this->db->where('order_date >=', $from);
		$this->db->where('order_date <=', $to);
		} else {
		$today = date('Y-m-d');
		$this->db->where("order_date >= DATE(DATE_ADD('$today -7', interval 0-weekday('$today') day))");
		$this->db->where("order_date <= DATE(DATE_ADD('$today -7', interval 6-weekday('$today') day))");
		}
		$this->db->order_by('order_date','desc');	

		$query = $this->db->get();
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	}

	#------------------------------------------------------
	# Retrieve order details 
	#------------------------------------------------------
	function getOrderDetails($order_id) {
	
		$this->db->select('*, (order_total + order_vat + order_shipping) as total');
		$this->db->where('order_id',$order_id);
		$query = $this->db->get('orders');
		
		if ($query->num_rows() > 0)
		{
			return $query->row();
		}
	}

	#------------------------------------------------------
	# Retrieve order inventory
	#------------------------------------------------------
	function getOrderInventory($order_id) {
		
		$this->db->select('*, (product_qty * product_price) as linetotal');
		$this->db->where('order_id', $order_id);
		$this->db->order_by('order_id');
		$query = $this->db->get('orders_inventory');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
		
	}
	
	#------------------------------------------------------
	# Retrieve single order inventory item
	#------------------------------------------------------
	function getOrderInventoryItem($id) {
		
		$this->db->where('id', $id);
		$query = $this->db->get('orders_inventory');
		
		if ($query->num_rows() > 0){
			return $query->row();
		} else {
			return array();
		}
		
	}

	#------------------------------------------------------
	# Save order inventory
	#------------------------------------------------------
	function updateOrderInventory($order_inventory_id=0, $array, $remove='no') {
		
		// If this id exists, it means we need to update the record.
		// Else we must add a new one...
		if ($order_inventory_id > 0) {
			
			if ($remove == 'yes') {
				$this->db->where('id', $order_inventory_id);
				$this->db->delete('orders_inventory');
			} else {
				$this->db->where('id', $order_inventory_id);
				$this->db->update('orders_inventory', $array);
			}
			
		} else {
			$this->db->insert('orders_inventory', $array);
		}
		
	}
	
	#------------------------------------------------------
	# Update order status
	#------------------------------------------------------
	function updateOrderStatus($order_id, $order_status_id=0) {

		$data = array(
			'order_status' 	  => $this->input->post('s_orderstatus'),
			'order_status_id' => $order_status_id,
		);

		// Only run this code if order_status_id is not "unprocessed"
		if ($order_status_id > 0) {
			
			//Check if this order has an order_ref, if not we need to create a new one
			$this->db->select('order_ref');
			$this->db->where('order_id', $order_id);
			$check = $this->db->get('orders');
			$existing_order_ref = $check->row()->order_ref;
			
			if (empty($existing_order_ref)) {
				// Get the next order number as defined in the config settings
				// to use as the current one
				$current_order_no = $this->config->item('order_no');
				$current_order_no = str_pad($current_order_no, 6, '0', STR_PAD_LEFT);
				
				// Append it to the order ref
				$order_ref = date('ymd-His', time()) . '-' . $current_order_no;
				
				// Increment the config setting in preparation for the next order
				$next_order_no = $current_order_no + 1;
				$next_order_no = str_pad($next_order_no, 6, '0', STR_PAD_LEFT);
				
				// And save to settings
				$this->db->set('value', $next_order_no);
				$this->db->where('setting', 'order_no');
				$this->db->update('settings');
				
				// Add to data array
				$data['order_ref'] = $order_ref;
			}
		
		}

		// If this is the dispatched status, record the dispatch date
		if ($_POST['s_orderstatus'] == $this->getDefaultStatus('Dispatched')->value) {
			$dispatch_date = $this->input->post('s_dispatch_year') . '-' . $this->input->post('s_dispatch_month') . '-' . $this->input->post('s_dispatch_day');
			$data['dispatch_date'] = $dispatch_date;
			$data['dispatch_email'] = date('Y-m-d');
		}
		
		$this->db->where('order_id', $order_id);
		$this->db->update('orders', $data);
	
	}

	#------------------------------------------------------
	# Mark as dispatched
	# - returns the status label
	#------------------------------------------------------
	function markAsDispatched($order_id) {

		// Record the date of dispatch
		$dispatch_date = date('Y-m-d');

		// Get default dispatched status
		$status = $this->getDefaultStatus('dispatched');
		
		// Update the entry
		$data = array(
			'order_status' 	  => $status->value,
			'order_status_id' => $status->type, // 0, 1 or 2
			'dispatch_date'	  => $dispatch_date,
			'dispatch_email'  => $dispatch_date
		);
		
		$this->db->where('order_id', $order_id);
		$this->db->update('orders', $data);
		
		// Return the status
		return $status;
	
	}

	#------------------------------------------------------
	# Update Order
	#------------------------------------------------------
	function updateOrder($order_id, $order_total, $order_vat) {

		//Set the order_status_id. This makes in easier to
		//identify completed OR failed orders rather than using the
		//order_status field
		$status = $this->getStatusFromLabel($this->input->post('s_orderstatus'));
	
		$data = array(
				'account_id'		=> $this->input->post('account_id'),
				'billing_title' 	=> $this->input->post('billing_title'),
				'billing_firstname' => $this->input->post('billing_firstname'),
				'billing_surname' 	=> $this->input->post('billing_surname'),
				'billing_company' 	=> $this->input->post('billing_company'),
				'billing_address1' 	=> $this->input->post('billing_address1'),
				'billing_address2' 	=> $this->input->post('billing_address2'),
				'billing_city' 		=> $this->input->post('billing_city'),
				'billing_postcode' 	=> $this->input->post('billing_postcode'),
				'billing_country' 	=> $this->input->post('billing_country'),
				'delivery_title' 	=> $this->input->post('delivery_title'),
				'delivery_firstname'=> $this->input->post('delivery_firstname'),
				'delivery_company' 	=> $this->input->post('delivery_company'),
				'delivery_surname' 	=> $this->input->post('delivery_surname'),
				'delivery_address1' => $this->input->post('delivery_address1'),
				'delivery_address2' => $this->input->post('delivery_address2'),
				'delivery_city' 	=> $this->input->post('delivery_city'),
				'delivery_postcode' => $this->input->post('delivery_postcode'),
				'delivery_country' 	=> $this->input->post('delivery_country'),
				'customer_email' 	=> $this->input->post('customer_email'),
				'customer_phone' 	=> $this->input->post('customer_phone'),
				'order_status' 		=> $this->input->post('s_orderstatus'),
				'order_status_id'	=> $status->type,
				'order_total'		=> $order_total,
				'order_shipping'	=> $this->input->post('order_shipping'),
				'shipping_method'	=> $this->input->post('shipping_method'),
				'order_vat'			=> $order_vat,
				'order_discount'	=> $this->input->post('order_discount'),
				'instructions' 		=> $this->input->post('instructions'),
				'transaction_type' 	=> $this->input->post('transaction_type'),
				'transaction_id' 	=> $this->input->post('transaction_id'),
				'refund'			=> $this->input->post('refund'),
				);

		// If this is the dispatched status, record the dispatch date
		if ($_POST['s_orderstatus'] == $this->getDefaultStatus('Dispatched')->value) {
			$dispatch_date = $this->input->post('s_dispatch_year') . '-' . $this->input->post('s_dispatch_month') . '-' . $this->input->post('s_dispatch_day');
			$data['dispatch_date'] = $dispatch_date;
			$data['dispatch_email'] = date('Y-m-d');
		}
		
		if ($this->input->post('s_unmarkdispatched') == 'true') {
			$data['dispatch_date'] = null;
			$data['dispatch_email'] = null;
		}
			
		$this->db->where('order_id', $order_id);
		$this->db->update('orders', $data);
	
	}

	#------------------------------------------------------
	# Create New Order
	#------------------------------------------------------
	function createOrder($order_total, $order_vat, $preserve_order_ref=null, $inventory) {
	
		//Preserve the order_ref or create a new one?
		if ($preserve_order_ref == null) {
			// Get the next order number as defined in the config settings
			// to use as the current one
			$current_order_no = $this->config->item('order_no');
			$current_order_no = str_pad($current_order_no, 6, '0', STR_PAD_LEFT);
			
			// Append it to the order ref
			$order_ref = date('ymd-His', time()) . '-' . $current_order_no;
			
			// Increment the config setting in preparation for the next order
			$next_order_no = $current_order_no + 1;
			$next_order_no = str_pad($next_order_no, 6, '0', STR_PAD_LEFT);
			
			// And save to settings
			$this->db->set('value', $next_order_no);
			$this->db->where('setting', 'order_no');
			$this->db->update('settings');
		} else {
			$order_ref = $preserve_order_ref;
		}

		
		//Set the date/time of the order (check if a datetime is posted first)
		if ($this->input->post('order_date') != '') {
			$order_date = $this->input->post('order_date') . date(' H:i:s', time());
		} else {
			$order_date = date('Y-m-d H:i:s', time());
		}

		//Set the order_status_id. This makes in easier to
		//identify completed OR failed orders rather than using the
		//order_status field
		$post_status = ($this->input->post('s_orderstatus') == "") ? NULL : $this->input->post('s_orderstatus');
		$status = $this->getStatusFromLabel($post_status);
		
		// Set the VAT if it's passed
		$vat_rate = (isset($_POST['vat_rate'])) ? $this->input->post('vat_rate') : $this->config->item('vat_rate');

		$data = array(
				'order_ref'			=> $order_ref,
				'session_id'		=> 'orderbuilder',
				'order_date'		=> $order_date,
				'account_id'		=> $this->input->post('account_id'),
				'billing_title' 	=> $this->input->post('billing_title'),
				'billing_firstname' => $this->input->post('billing_firstname'),
				'billing_surname' 	=> $this->input->post('billing_surname'),
				'billing_company' 	=> $this->input->post('billing_company'),
				'billing_address1' 	=> $this->input->post('billing_address1'),
				'billing_address2' 	=> $this->input->post('billing_address2'),
				'billing_city' 		=> $this->input->post('billing_city'),
				'billing_postcode' 	=> $this->input->post('billing_postcode'),
				'billing_country' 	=> $this->input->post('billing_country'),
				'delivery_title' 	=> $this->input->post('delivery_title'),
				'delivery_firstname'=> $this->input->post('delivery_firstname'),
				'delivery_surname' 	=> $this->input->post('delivery_surname'),
				'delivery_company' 	=> $this->input->post('delivery_company'),
				'delivery_address1' => $this->input->post('delivery_address1'),
				'delivery_address2' => $this->input->post('delivery_address2'),
				'delivery_city' 	=> $this->input->post('delivery_city'),
				'delivery_postcode' => $this->input->post('delivery_postcode'),
				'delivery_country' 	=> $this->input->post('delivery_country'),
				'customer_email' 	=> $this->input->post('customer_email'),
				'customer_phone' 	=> $this->input->post('customer_phone'),
				'order_status' 		=> $this->input->post('s_orderstatus'),
				'order_status_id'	=> $status->type,
				'order_total'		=> $order_total,
				'order_shipping'	=> $this->input->post('order_shipping'),
				'shipping_method'	=> $this->input->post('shipping_method'),
				'order_vat'			=> $order_vat,
				'order_discount'	=> $this->input->post('order_discount'),
				'instructions' 		=> $this->input->post('instructions'),
				'transaction_type' 	=> $this->input->post('transaction_type'),
				'transaction_id' 	=> $this->input->post('transaction_id'),
				'vat_rate'			=> $vat_rate,
				'refund'			=> $this->input->post('refund'),
				'site'				=> $this->input->post('site'), // Channel's shortname
				);

		// If this is the dispatched status, record the dispatch date
		if ($_POST['s_orderstatus'] == $this->getDefaultStatus('Dispatched')->value) {
			$dispatch_date = $this->input->post('s_dispatch_year') . '-' . $this->input->post('s_dispatch_month') . '-' . $this->input->post('s_dispatch_day');
			$data['dispatch_date'] = $dispatch_date;
			$data['dispatch_email'] = date('Y-m-d');
		}
				
		$this->db->insert('orders', $data);
	
		//Get the new order_id
		$new_order_id = $this->db->insert_id();
		
		// Loop through the inventory items and attach to order
		foreach($inventory as $inv_item) {
			$inv_item['order_id'] = $new_order_id;
			$this->updateOrderInventory(0, $inv_item);
		}
		
		//Return the new order_id
		return $new_order_id;
		
	}

	#------------------------------------------------------
	# Order notes
	#------------------------------------------------------
	function recordnote($order_id, $note) {
	
		$data = array(
					'order_id' 	=> $order_id,
					'author'	=> trim($this->session->userdata('firstname') . ' ' . $this->session->userdata('surname')),
					'email'		=> $this->session->userdata('email'),
					'date'		=> date('Y-m-d H:i:s'),
					'note'		=> $note,
				);
		
		$this->db->insert('order_notes', $data);
	
	}

	#------------------------------------------------------
	# Get notes
	#------------------------------------------------------
	function notes($order_id) {
		$this->db->where('order_id', $order_id);
		$this->db->order_by('date','desc');
		$query = $this->db->get('order_notes');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		}
	}

	#------------------------------------------------------
	# The Day's Sales
	# - can be used for any date
	#------------------------------------------------------
	function theDaysOrders($date) {
		
		$day_start = $date . ' 00:00:00';
		$day_end   = $date . ' 23:59:59';
		
		$this->db->select('*, (order_total + order_shipping + order_vat) as total', false);
		$this->db->where('order_status_id','2');
		$this->db->where('order_date >=', $day_start);
		$this->db->where('order_date <=', $day_end);
		$this->db->order_by('order_date','asc');
		$query = $this->db->get('orders');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	
	}

	#------------------------------------------------------
	# Get list of attached refund orders
	#------------------------------------------------------
	function getRefunds($order_ref) {
		
		$this->db->select('
			order_id as refund_id, 
			order_date as refund_date,
			order_status, 
			(select group_concat(product_name separator ";") from orders_inventory where order_id = orders.order_id) as inventory, 
			(order_total + order_shipping + order_vat) as total
		', false);
		$this->db->where('order_ref', $order_ref);
		$this->db->where('refund', 1);
		$this->db->order_by('order_date', 'desc');
		$query = $this->db->get('orders');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
		
	}
	
	#------------------------------------------------------
	# Delete refund
	#------------------------------------------------------
	function deleteRefund($order_id) {
		
		if (!empty($order_id)) {
			
			//Delete order from orders table
			$this->db->where('order_id', $order_id);
			$this->db->delete('orders');
			
			//Delete any related notes
			$this->db->where('order_id', $order_id);
			$this->db->delete('order_notes');
			
			//Delete items from the orders_inventory table
			$this->db->where('order_id', $order_id);
			$this->db->delete('orders_inventory');
			
		}
		
	}

	#------------------------------------------------------
	# Selected orders
	# - retrieves only the orders passed through the array
	#   and in that order
	#------------------------------------------------------
	function getOrders($array_of_order_ids) {
		
		foreach($array_of_order_ids as $order_id) {
			$this->db->or_where('order_id', $order_id);
		}
		
		$this->db->select('*, (order_total + order_shipping + order_vat) as total', false);
		$query = $this->db->get('orders');
		
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	}

	#------------------------------------------------------
	# Mark as printed
	# - Sets the 'printed' column to 1
	# @param $order_id (single order ID or an array of order IDs)
	#------------------------------------------------------
	function markAsPrinted($order_id) {

		$this->db->set('printed', '1');
		
		if (is_array($order_id)) {
			foreach($order_id as $id) {
				$this->db->or_where('order_id', $id);
			}
		} else {
			$this->db->where('order_id', $order_id);
		}
		$this->db->update('orders');
		
	}

	#------------------------------------------------------
	# Manage order statuses
	#------------------------------------------------------
	// Retrieve a list of statuses
	function listCustomStatuses() {
		
		$this->db->order_by('position', 'asc');
		$query = $this->db->get('order_statuses');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
		
	}
	
	// Update existing status
	function updateCustomStatus($id, $data) {
		
		$this->db->where('id', $id);
		$this->db->update('order_statuses', $data);
		
	}
	
	// Add new status
	function createCustomStatus($data) {
		
		if (!empty($data['label'])) {
			$this->db->insert('order_statuses', $data);
		}
	
	}
	
	// Delete status
	function deleteCustomStatus($id) {
		
		if (!empty($id)) {
			$this->db->where('id', $id);
			$this->db->delete('order_statuses');
		}
		
	}
	
	// Get flag_ status (Default status)
	function getDefaultStatus($for='completed') {
		
		// Ensure var is in lowercase
		$for = strtolower($for);
		
		$this->db->where("flag_$for", 1);
		$this->db->limit(1);
		$query = $this->db->get('order_statuses');
		
		if ($query->num_rows() > 0) {
			return $query->row();
		} else {
			return NULL;
		}
		
	}
	
	// Get status based on label value
	function getStatusFromLabel($value=null) {
		
		$this->db->where('value', $value);
		$query = $this->db->get('order_statuses');

		return $query->row();
		
	}
	
	// Get the flow statuses (used for the flow chart on orders view)
	function getFlowStatuses() {
		
		$this->db->where('flow', 1);
		$this->db->order_by('position', 'asc');
		$query = $this->db->get('order_statuses');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
	}

	#------------------------------------------------------
	# Order notifications
	#------------------------------------------------------
	function getStatusNotifications() {
		
		$this->db->select('order_notifications.*, label, value, color, users.firstname, users.email');
		$this->db->join('order_statuses', 'order_statuses.id = status_id');
		$this->db->join('users', 'users.uid = order_notifications.admin', 'left');
		$this->db->order_by('order_notifications.id', 'asc');
		$query = $this->db->get('order_notifications');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
	}
	
	function getStatusNotification($id) {
		
		$this->db->where('id', $id);
		$query = $this->db->get('order_notifications');
		
		if ($query->num_rows() > 0) {
			return $query->row();
		} else {
			return FALSE;
		}
		
	}
	
	function updateStatusNotification($id) {

		// If no customer or admin recipients set, then disable the notification
		$enabled = ($this->input->post('customer') == 0 && $this->input->post('admin') == 0) ? 0 : $this->input->post('enabled');
		
		$data = array(
			'status_id' => $this->input->post('status_id'),
			'subject'   => $this->input->post('subject'),
			'body' 		=> $this->input->post('body'),
			'enabled'	=> $enabled,
			'note'		=> $this->input->post('note'),
			'customer'	=> $this->input->post('customer'),
			'admin'		=> $this->input->post('admin'),
		);
		
		$this->db->where('id', $id);
		$this->db->update('order_notifications', $data);
		
	}

	function createStatusNotification() {
	
		// If no customer or admin recipients set, then disable the notification
		$enabled = ($this->input->post('customer') == 0 && $this->input->post('admin') == 0) ? 0 : $this->input->post('enabled');
		
		$data = array(
			'status_id' => $this->input->post('status_id'),
			'subject'   => $this->input->post('subject'),
			'body' 		=> $this->input->post('body'),
			'enabled'	=> $enabled,
			'note'		=> $this->input->post('note'),
			'customer'	=> $this->input->post('customer'),
			'admin'		=> $this->input->post('admin'),
		);
		
		$this->db->insert('order_notifications', $data);
		
	}
	
	function deleteStatusNotification($id=0) {
		
		if ($id > 0) {
			$this->db->where('id', $id);
			$this->db->delete('order_notifications');
		}
		
	}
	
	function getNotificationEmails($status_id) {
		
		$this->db->select('order_notifications.*, users.email as admin_email', false);
		$this->db->join('users', 'users.uid = order_notifications.admin', 'left');
		$this->db->where('status_id', $status_id);
		$this->db->where('enabled', 1);
		$query = $this->db->get('order_notifications');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return FALSE;
		}
		
	}
	
	function emailNotification($to=NULL, $bcc=NULL, $subject="", $body="", $tags=array()) {

		if (!empty($to)) {

			// Output as plain text
			#header('Content-type: text/plain; charset=utf-8');	// for testing

			// Start email helper
			$config['mailtype'] = 'html';
			$this->email->initialize($config);
			
			// Template body tag - contains the content of the email
			$html['body'] = $body;

			// Loop through the array of tags and create the parser tags
			if (!empty($tags)) {
				foreach ($tags as $tag=>$value) {
					if (is_array($value)) {
						foreach($value as $key=>$pair) {
							$content[$tag][$key] = get_object_vars($pair);
						}
					} else {
						$content = get_object_vars($value);
					}
				}
			}
			
			// Get the shopit settings and create the parser tags
			$settings = $this->settings_model->getSettings();
			foreach ($settings as $tag) {
				$content[$tag->setting] = $tag->value; 
			}
			
			// Load snippets
			$snippets = $this->settings_model->loadSnippets();

			// Merge the $html and $content arrays into one
			$data = array_merge($html, $content, $snippets);

			#print_r($data); // for testing
			#$this->parser->parse('orders/orders_email', $data); // for testing

			// Send email to customer....
			$this->email->from($this->config->item('store_email'), $this->config->item('store_name'));
			$this->email->to($to);
			if (!empty($bcc)) {
				$this->email->bcc($bcc);
			}
			
			$this->email->subject($subject);
			$msg = $this->parser->parse('orders/orders_email', $data, true);
			
			$this->email->message($msg);
			$this->email->send();
			$this->email->clear();
		
		}
		
	}
	
	function sendNotifications($status_id, $customer_email, $tags=array()) {
		
		//Send email to customer depending upon the status change
		$notifications = $this->getNotificationEmails($status_id);

		if ($notifications != false) {
			
			// Send email for each notification
			foreach($notifications as $email) {
			
				// Prep the recipients
				if ($email->customer == 1 && $email->admin == 0) {
					$to  = $customer_email;
					$bcc = NULL;
				} elseif($email->customer == 1 && $email->admin > 0) {
					$to  = $customer_email;
					$bcc = $email->admin_email;
				} elseif($email->customer == 0 && $email->admin > 0) {
					$to  = $email->admin_email;
					$bcc = NULL;
				} else {
					$to  = NULL;
					$bcc = NULL;
				}
				
				$this->emailNotification($to, $bcc, $email->subject, $email->body, $tags);
				
			}
			
		}
		
	}

	#------------------------------------------------------
	# Order Templates (Packing Notes)
	#------------------------------------------------------
	// Get Templates
	// @param $site (string or null) - If string, should be site/channel
	function getTemplates($site=null) {
		
		if (!empty($site)) {
			$this->db->where('site', $site);
		}
		
		$this->db->order_by('id');
		$query = $this->db->get('templates');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
		
	}
	
	// Get Template
	// @param $id (int or string) - If string, should be site/channel
	function getTemplate($id) {
		
		if (!empty($id)) {
			
			if (is_numeric($id)) {
				$this->db->where('id', $id);
			} else {
				$this->db->where('site', $id);
			}
			$query = $this->db->get('templates');
			
			return $query->row();
		
		} else {
			return array();
		}
		
	}
	
	// Get Templates by Type
	// @param $type (string) - 'packing', 'dispatch' or 'other'
	// @param $site (string) - The site/channel
	function getTemplatesByType($type='packing', $site='website') {
		
		$this->db->where('type', $type);
		$this->db->where('site', $site);
		$this->db->order_by('id');
		$query = $this->db->get('templates');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
		
	}

	function updateTemplate($id) {
		
		$data = array(
			'title' 	=> $this->input->post('title'),
			'content'   => $this->input->post('content'),
			'site'   	=> $this->input->post('site'),
			'type'		=> $this->input->post('type'),
		);
		
		$this->db->where('id', $id);
		$this->db->update('templates', $data);
		
	}

	function createTemplate() {
	
		$data = array(
			'title' 	=> $this->input->post('title'),
			'content'   => $this->input->post('content'),
			'site'   	=> $this->input->post('site'),
			'type'		=> $this->input->post('type'),
		);
		
		$this->db->insert('templates', $data);
		
	}
	
	function deleteTemplate($id=0) {
		
		if ($id > 0) {
			$this->db->where('id', $id);
			$this->db->delete('templates');
		}
		
	}
	
	// Create an array of available templates. This is to prevent 
	// multiple database calls to the same query on the orders
	// list view.
	function returnTemplates() {
		
		$array = array();
		
		// Get all templates
		$templates = $this->getTemplates();
		
		// Create our new array in the format ['channel_name'][n] = array()
		foreach($templates as $template) {
			
			$array[$template->site][] = array(
				'id' 	=> $template->id,
				'title' => $template->title,
			);
			
		}
		
		// Retuen the array
		return $array;
		
	}
	
	// Return list of available template types. This is used
	// for the bulk processing options on the orders list view.
	function returnTemplateTypes() {
		
		$this->db->select('type');
		$this->db->group_by('type');
		$this->db->order_by('type');
		$query = $this->db->get('templates');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
		
	}

}