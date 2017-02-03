<?php

class Basket_model extends CI_Model {
	
	function Basket_model() {
		parent::__construct();

		// Get the price field names for this channel
		$this->channel_product_price = $this->config->item('channel_product_price');
		$this->channel_product_saleprice = $this->config->item('channel_product_saleprice');

	}

	#------------------------------------------------------
	# Add item to user's basket (table)
	# - includes product options and prices
	# - $product_opts is an array of option 'id'
	#------------------------------------------------------
	function addToBasket($product_id,$product_qty,$product_opts = '') {
		
		// Protect the product_qty from silly figures being entered 
		// like '2147483647'!
		$product_qty = ($product_qty >= 999) ? 1 : $product_qty;
		
		//get product options/prices
		if (is_array($product_opts)) {
		
			foreach ($product_opts as $option_id) :
			
				$this->db->where('id',$option_id);
				$query = $this->db->get('product_options');
				
				if ($query->num_rows() > 0)
				{
									
					foreach($query->result() as $option):
										
						//loops through the prices, totalling it up
						$option_price = $option_price + $option->option_price;
						$option_text  = $option_text . '{' . $option->option_label . '@' . $option->option_criteria . "}";
					
					endforeach;
				}
			
			endforeach;
		
		}

		//gets details of chosen product
		$sql = 'product_id,
				product_name,
				product_no,
				product_weight, 
				(select product_name from inventory i1 where product_id = inventory.parent_id) as parent_name,';
		$sql .= sprintf('%s as product_price,', $this->channel_product_price);
		$sql .= sprintf('%s as product_saleprice', $this->channel_product_saleprice);
		$this->db->select($sql, false);
		$this->db->where('product_id', $product_id);
		$query = $this->db->get('inventory');
		
		//if product exists in db, get data
		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $item){
				
				//check if product is already in the basket
				$this->db->where('session_id', $this->session->userdata('store_session'));
				$this->db->where('product_id', $item->product_id);
				$this->db->where('product_options', NULL);
				$this->db->where('site', $this->config->item('site'));
				$query2 = $this->db->get('basket');
				
				if ($query2->num_rows() > 0) { //already in the basket
					foreach ($query2->result() as $basket_item) {
						// The full quantity (including those already in the basket) are passed to 
						// the update basket function.
						$this->updateBasket($basket_item->basket_id, $basket_item->product_qty + $product_qty, true);
					}
				}
				
				//Else not in basket so add a new record
				else { 

					if ($item->product_saleprice == '0.00' || $item->product_saleprice == ''):
						$product_saleprice = '0.00';
					else:
						$product_saleprice = $item->product_saleprice + $option_price;
					endif;

					$product_price = base_rate($item->product_price) + $option_price;
					
					//Append the parent item name if there is one.
					$product_name = (empty($item->parent_name)) ? $item->product_name : $item->parent_name . ' - ' . unserialize_variant($item->product_name);

					$data = array (
								'session_id' 		=> $this->session->userdata('store_session'),
								'product_id' 		=> $item->product_id,
								'product_no' 		=> $item->product_no,
								'product_name' 		=> $product_name,
								'product_options'	=> $option_text,
								'product_price' 	=> $product_price,
								'product_saleprice' => $product_saleprice,
								'product_weight'	=> $item->product_weight,
								'product_qty'		=> $product_qty,
								'basket_date' 		=> date('Y-m-d H:i:s', time()),
								'site'				=> $this->config->item('site'),
							);
				
					$this->db->insert('basket', $data);
				}				

			}
		}		

	}
	

	#------------------------------------------------------
	# Update user's basket quantities
	#------------------------------------------------------
	function updateBasket($basket_id, $product_qty, $update_date=false) {
		
		$data = array (
			'product_qty' => ($product_qty),
		);
		
		if ($update_date) {
			$data['basket_date'] = date('Y-m-d H:i:s', time());
		}
	
		$this->db->where('basket_id', $basket_id);
		$this->db->update('basket', $data);
	
	}
	
	
	#------------------------------------------------------
	# Remove item from user's basket
	#------------------------------------------------------
	function removeItem($basket_id) {
	
		$this->db->where('basket_id',$basket_id);
		$this->db->delete('basket');
	
	}
	
	#------------------------------------------------------
	# Empty this user's basket
	#------------------------------------------------------
	function emptyBasket($session_id) {
		$this->db->where('session_id',$session_id);
		$this->db->where('site', $this->config->item('site'));
		$this->db->delete('basket');
	}	
	
	#------------------------------------------------------
	# Retrieve user's basket
	# - Also gets the stock level for each item
	#------------------------------------------------------
	function getBasket(){

		// Get this user's session id from the basket cookie
		$basket_cookie = get_cookie('basket');

		$sql = "select 
				basket.*, 
				(case when parent.product_id is NULL then basket.product_id else parent.product_id end) as parent_id, 
				(case when parent.product_name is NULL then basket.product_name else parent.product_name end) as parent_name,";

		$sql .= "this." . $this->config->item('stock_location') . " as stock_level
				from basket
				left join inventory parent on (select parent_id from inventory where product_id = basket.product_id) = parent.product_id
				join inventory this on this.product_id = basket.product_id
				where session_id = '".$basket_cookie."'
				and site = '". $this->config->item('site') ."'
				order by basket_date asc";
				
		$query = $this->db->query($sql);
	
		if ($query->num_rows() > 0)
		{
			return $query->result();
		}
	
	}

	#------------------------------------------------------
	# Get list of countries
	#------------------------------------------------------
	function getCountries() {
		
		$this->db->order_by('country_name','asc');
		$query = $this->db->get('countries');

		if ($query->num_rows > 0)
		{
			return $query->result();
		}
		
	}

	#------------------------------------------------------
	# Get list of ALL countries
	#------------------------------------------------------
	function getAllCountries() {
		
		$this->db->where('country_name != "Rest of World"');
		$this->db->order_by('country_name', 'asc');
		$query = $this->db->get('iso_countries');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		}
	
	}

	#------------------------------------------------------
	# Add order to database
	# - this occurs when user clicks checkout button in basket
	#------------------------------------------------------
	function createOrder($account_id = '') {

		$offer = (object) array();

		//A few things to reset first
		$offer->free_qty = 0;

		//Get the stored session_id from the existing cookie
		$basket_cookie = get_cookie('basket');
		
		//Check if this order has already been created (in the orders table). 
		//If it has update the existing data, otherwise insert as normal - we 
		//are trying to prevent duplicate data appearing in the admin or an 
		//existing confirmed order from being overwritten.
		$this->db->select('order_id');
		$this->db->where('session_id', $basket_cookie);
		//We need to check if the order_ref is NULL here because 
		//WorldPay and PayPal don't allow us to destroy local session cookies
		$this->db->where('order_ref IS NULL');
		$prequery = $this->db->get('orders');
		if ($prequery->num_rows() > 0) {
			$order_exists = $prequery->row()->order_id; //Returns the order_id (we can use this later)
		} else {
			$order_exists = FALSE;
		}
		
		// We'll not create an order reference at this stage
		// to prevent any confusion in the admin. The order ref
		// will be created on successful payment. 
		$order_ref = NULL;

		//Communication Preferences
		$pref_newsletter = (!empty($_POST['pref_newsletter'])) ? 1 : 0;
		
		//Gather data to insert into 'orders' table
		$data = array(
				'session_id'		=> $basket_cookie,
				'order_ref'			=> $order_ref,
				'order_date'		=> date('Y-m-d H:i:s', time()),
				'account_id'		=> $account_id,
				'billing_title' 	=> $this->security->xss_clean($_POST['BillingTitle']),
				'billing_firstname'	=> $this->security->xss_clean($_POST['BillingFirstname']),
				'billing_surname' 	=> $this->security->xss_clean($_POST['BillingSurname']),
				'billing_company' 	=> $this->security->xss_clean($_POST['BillingCompany']),
				'billing_address1' 	=> $this->security->xss_clean($_POST['BillingAddress1']),
				'billing_address2' 	=> $this->security->xss_clean($_POST['BillingAddress2']),
				'billing_city'	 	=> $this->security->xss_clean($_POST['BillingCity']),
				'billing_postcode' 	=> $this->security->xss_clean($_POST['BillingPostcode']),
				'billing_country' 	=> $this->security->xss_clean($_POST['BillingCountry']),
				'delivery_title' 	=> $this->security->xss_clean($_POST['DeliveryTitle']),
				'delivery_firstname'=> $this->security->xss_clean($_POST['DeliveryFirstname']),
				'delivery_surname' 	=> $this->security->xss_clean($_POST['DeliverySurname']),
				'delivery_company' 	=> $this->security->xss_clean($_POST['DeliveryCompany']),
				'delivery_address1'	=> $this->security->xss_clean($_POST['DeliveryAddress1']),
				'delivery_address2'	=> $this->security->xss_clean($_POST['DeliveryAddress2']),
				'delivery_city' 	=> $this->security->xss_clean($_POST['DeliveryCity']),
				'delivery_postcode'	=> $this->security->xss_clean($_POST['DeliveryPostcode']),
				'delivery_country' 	=> $this->security->xss_clean($_POST['DeliveryCountry']),
				'customer_email' 	=> $this->security->xss_clean($_POST['Email']),
				'customer_phone' 	=> $this->security->xss_clean($_POST['Phone']),
				'order_total'		=> base64_decode($_POST['amount']),
				'order_discount'	=> base64_decode($_POST['discount']),
				'order_vat'			=> base64_decode($_POST['vat']),
				'order_shipping'	=> base64_decode($_POST['shipping']),
				'shipping_method'	=> $this->security->xss_clean($_POST['shipping_method']),
				'transaction_type'	=> $this->security->xss_clean($_POST['gateway']),
				'instructions'		=> $this->security->xss_clean($_POST['Instructions']),
				'site'				=> $this->config->item('site'),
				'vat_rate'			=> $this->config->item('vat_rate'),
				'pref_newsletter'	=> $pref_newsletter, //This field is also in the account's table
				);
				
		//If no order exists, insert as normal
		//Else update the existing order details
		if ($order_exists == FALSE) {
			
			// Insert the customer information first (excluding the order inventory)
			$this->db->insert('orders', $data);
			
			// Get the new order_id of the record we just inserted
			$new_id = $this->db->insert_id();
			
			// Attach item to this order
			$this->_attachItemToOrder($basket_cookie, $new_id);
			
			
		} else {
			
			// Update the customer information first (excluding the order inventory)
			$this->db->where('order_id', $order_exists);
			$this->db->update('orders', $data);
			
			// Remove all order inventory items first for this order.(Why, did you say? Because we're 
			// assuming the customer has changed something in their basket).
			$this->db->where('order_id', $order_exists);
			$this->db->delete('orders_inventory');
			
			// Now re-attach the inventory items
			$this->_attachItemToOrder($basket_cookie, $order_exists);
			
		}

	}

	#------------------------------------------------------
	# Add item to orders_inventory table
	#------------------------------------------------------
	function _attachItemToOrder($basket_cookie, $order_id) {

		// Attach the inventory - Get basket contents for this user and prep for database insertion
		$this->db->select('
			basket.*, 
			(case when product_saleprice > 0 then product_saleprice else product_price end) as price'
		, false);
		$this->db->where('session_id', $basket_cookie);
		$query = $this->db->get('basket');
		
		if ($query->num_rows() > 0) {
			foreach ($query->result() as $item) {
			
				//BEGIN module: special offers
				if (library_exists('specialoffers')) {
					$offer = $this->specialoffers->returnQty($item->product_id, $item->product_qty);
				}
				//END module: special offers
				
				// Add item to orders_inventory table
				$inv_item = array(
					'order_id' 		  => $order_id,
					'product_id' 	  => $item->product_id,
					'product_no'	  => $item->product_no,
					'product_name'	  => $item->product_name,
					'product_qty'	  => $item->product_qty,
					'product_price'	  => $item->price,
					'product_options' => $item->product_options, // {label@value}{label@value}{...}
					'free_qty'		  => $offer->free_qty, // {module:special offers}
				);
				
				$this->db->insert('orders_inventory', $inv_item);
				
			}
		}
		
	}

	#------------------------------------------------------
	# Retrieve the Order_ID from above transaction
	#------------------------------------------------------
	function getOrderID() {

		//Get the stored session_id from the existing cookie
		$basket_cookie = get_cookie('basket');
		
		$this->db->select('order_id');
		$this->db->where('session_id', $basket_cookie);
		// We need to check if the order_ref is NULL so we know this is a completely 
		// new order (we don't want to overwrite an existing one by mistake!) 
		$this->db->where('order_ref IS NULL');
		$this->db->where('site', $this->config->item('site'));
		$query = $this->db->get('orders');
		
		if ($query->num_rows > 0)
		{
			$order = $query->row();
			return $order->order_id;
		}
		else {
			return false;
		}
	
	}

	#------------------------------------------------------
	# Update order on success
	#------------------------------------------------------
	function updateOrderStatus($order_id, $status, $order_status_id, $transaction_id, $transaction_total, $transaction_data) {

		// Get the default refund status
		$refund_status = $this->getDefaultStatus('Refunded');
		
		// Create the new incremental order reference
		$order_ref = $this->_createNewOrderRef();

		// Update the order data
		$data = array(
					'order_ref'		   => $order_ref,
					'order_status' 	   => $status,
					'order_status_id'  => $order_status_id,
					'transaction_id'   => $transaction_id,
					'transaction_total'=> str_replace(',', '', $transaction_total),
					'transaction_data' => $transaction_data,
				);
	
		// If this is not a refund, update the order date
		if ($status != $refund_status->value) {
			$data['order_date'] = date('Y-m-d H:i:s', time());
		}
		
		$this->db->where('order_id', $order_id);
		$this->db->update('orders', $data);
		
	}

	#------------------------------------------------------
	# Check if this product is already in the user's basket
	#------------------------------------------------------
	function isProductInMyBasket($product_id){
	
		$this->db->where('product_id',$product_id);
		$this->db->where('product_options',NULL);
		$this->db->where('session_id',$this->session->userdata('store_session'));
		$this->db->where('site', $this->config->item('site'));
		
		$query = $this->db->get('basket');

		if ($query->num_rows > 0)
		{
			return true;
		} else {
			return false;
		}
	
	}

	#------------------------------------------------------
	# Get inventory of order
	# - We'll use the order_id to ensure uniqueness
	#------------------------------------------------------
	function getInventory($order_id) {
	
		//We're just grabbing the 'inventory' from the orders_inventory 
		//table for now for this order...
		$this->db->select('*, (product_qty * product_price) as linetotal');
		$this->db->where('order_id', $order_id);
		$this->db->order_by('id', 'asc');
		$query = $this->db->get('orders_inventory');
		
		if ($query->num_rows > 0) {
			return $query->result();
		} else {
			return array();
		}
	
	}

	#------------------------------------------------------
	# Get order details
	# - We'll use the order_ref to ensure uniqueness
	# - which is used to get order details to send emails
	#------------------------------------------------------
	function getOrderDetails($order_id) {
		
		$this->db->select('*, (order_total + order_shipping + order_vat) as total');
		$this->db->where('order_id', $order_id);
		$this->db->limit(1);
		$query = $this->db->get('orders');
		
		if ($query->num_rows > 0)
		{
			return $query->row();
		}
	
	}


	#------------------------------------------------------
	# Update stock levels for this product_id
	# - We'll use the product_id to identify item
	# - and pass in the new stock level which is calculated
	# - by the controller...
	#------------------------------------------------------
	function updateStockLevels($product_id,$qty_purchased) {
		
		//First get the current stock level for this item
		$this->db->select($this->config->item('stock_location').' as product_qty');
		$this->db->where('product_id',$product_id);
		$query1 = $this->db->get('inventory');

		$current_stock_level = $query1->row()->product_qty;
		
		//Create the new_stock_level value
		$new_stock_level = $current_stock_level - $qty_purchased;
		
		//If "use_global_stock" is true, remove qty from the "location_1" field,
		//else use the appropriate column
		if ($this->config->item('channel_global_stock')) {
			$data = array (
						'location_1' => $new_stock_level,
					);
		} else {
			$data = array (
						$this->config->item('stock_location') => $new_stock_level,
					);
		}
		
		$this->db->where('product_id',$product_id);
		$this->db->update('inventory',$data);
	
	}
	

	#------------------------------------------------------
	# Get basket summary
	# - basket total, total items
	#------------------------------------------------------
	function basketSummary() {

		// Get this user's session id from the basket cookie
		$basket_cookie = $this->session->userdata('store_session');
		
		$this->db->cache_off();
		$vat = the_vat_rate();
		$this->db->select("sum(product_qty * (case when product_saleprice > 0 then product_saleprice else product_price end)) as total, sum(product_qty) as items, GROUP_CONCAT(product_id) as product_ids", FALSE);
		$this->db->where('session_id', $basket_cookie);
		$this->db->where('site', $this->config->item('site'));
		$query = $this->db->get('basket');
	
		return $query->row();
	
	}

	#------------------------------------------------------
	# Extend the cookie
	# - Each time the basket is viewed we'll reset the
	# - cookie to last for a further two days
	#------------------------------------------------------
	function extend_cookie() {
		
		//First get the stored session_id from the existing cookie
		$basket_cookie = get_cookie('basket');
		
		//Extend the cookie by 10 years (prevents orders from messing up during payment processing)
		$extend = ( (60 * 60 * 24) * 365 ) * 10; // 10 years
	
		//Set the cookie with its new time
		set_cookie('basket', $basket_cookie, $extend);

	}

	#------------------------------------------------------
	# Create the next order number
	# - based on the store's config setting
	# - should only update on payment success
	#------------------------------------------------------
	function _createNewOrderRef() {
		
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
		
		// Return the new order reference
		return $order_ref;
	
	}

	#------------------------------------------------------
	# //!Status notifications
	# - Following functions match those used in the 
	#   admin's orders_model.php file
	#------------------------------------------------------
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

	// Get list of enabled status notifications
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
	
	// Email status notification
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
			
			// Load snippets & config settings
			$snippets = $this->settings_model->snippets();

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
			$msg = $this->parser->parse('gateway/notification', $data, true);
			
			$this->email->message($msg);
			$this->email->send();
			$this->email->clear();
		
		}
		
	}

	// Send status notifications
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

	
}