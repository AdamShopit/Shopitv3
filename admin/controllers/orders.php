<?php

class Orders extends CI_Controller {

	function Orders()
	{
		parent::__construct();

		$this->load->database();

		$this->load->model('settings_model');
		$this->settings_model->initConfig();
		
		$this->load->model('orders_model');
		$this->load->model('shipping_model');
		$this->load->model('inventory_model');
		$this->load->library('image_lib');
		$this->load->library('pagination');	
		$this->load->library('email');
		$this->load->library('parser');
		$this->load->helper('download');	

		/* Login check */
		$this->load->model('login_model');
		if(!$this->login_model->check_session()){
			redirect('/login');
		}
		/* End: Login check */
	}
	

	#------------------------------------------------------
	# Order summary (this week's orders)
	#------------------------------------------------------
	function index() {

		$this->permissions->access('can_access_order_listview');

		// Capture the current url so we can return to this 
		// page after an add/edit product page is accessed
		$data['redirect'] = redirect_create();
			
		//For dashboard alert
		$this->load->model('reports_model');
		$today = date('Y-m-d', strtotime('today'));
		$data['todaysOrderCount'] = $this->reports_model->theDay($today)->no_orders;
		//End
		
		//Get current week no, monday and sunday
		$week = get_mondayandsunday(date('Y-m-d'));
		$from = nice_date($week->monday, 'date');
		$to   = nice_date($week->sunday, 'date');
		
		$_POST['s_from_year'] = date('Y', strtotime($week->monday));
		$_POST['s_from_month'] = date('m', strtotime($week->monday));
		$_POST['s_from_day'] = date('d', strtotime($week->monday));
		
		$_POST['s_to_year'] = date('Y', strtotime($week->sunday));
		$_POST['s_to_month'] = date('m', strtotime($week->sunday));
		$_POST['s_to_day'] = date('d', strtotime($week->sunday));
		
		//For pagination (including filter)	
		if ($this->input->post('filter') == 'true') {
			$config['url_format'] = site_url('/orders/index/{offset}/'.http_build_query($_POST));
			$data['s_filter'] = http_build_query($_POST);
		} elseif ($this->uri->segment(4) != ''){
			$config['url_format'] = site_url('/orders/index/{offset}/'.$this->uri->segment(4));
			$data['s_filter'] = $this->uri->segment(4);
		} else {
			$config['base_url'] = base_url().'index.php/orders/index/';
		}

		//Start: Tell us which filter checkboxes/dropdowns are selected
		//so we can reselect them again
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
				$criteria_value = $parse_filter[1];
				
				$data[$criteria_name] = urldecode($criteria_value);
			}
		}
		//End
		
		//Tell us how many orders to show per page
		$per_page = ($data['s_perpage'] == "") ? 25 : $data['s_perpage'];

		$query = $this->orders_model->countOrders();
		
		$config['total_rows'] 	= $query;
		$config['uri_segment'] 	= 3;
		$config['per_page'] 	= $per_page;
			
		$this->pagination->initialize($config);
		
		$data['results_total'] = $config['total_rows'];
		//End of pagination
		
		$data['title'] 	   = "This Week's Activity ($from - $to)";
		$data['orders']    = $this->orders_model->listOrders($config['per_page'], $this->uri->segment(3), true);
		$data['statuses']  = $this->orders_model->listCustomStatuses();
		$data['templates'] = $this->orders_model->returnTemplates();
		$data['template_types'] = $this->orders_model->returnTemplateTypes();
		$data['content']   = 'orders/orders_list';
		$this->load->view('global/template', $data);

	}

	#------------------------------------------------------
	# View all orders, includes search filter
	#------------------------------------------------------
	function all() {	

		$this->permissions->access('can_access_order_listview');

		// Capture the current url so we can return to this 
		// page after an add/edit product page is accessed
		$data['redirect'] = redirect_create();

		//For pagination (including filter)	
		if ($this->input->post('filter') == 'true') {
			$config['url_format'] = site_url('/orders/all/{offset}/'.http_build_query($_POST));
			$data['s_filter'] = http_build_query($_POST);
			//We'll redirect at this point so the url shows the query string
			redirect('orders/all/0/'.$data['s_filter']);
		} elseif ($this->uri->segment(4) != ''){
			$config['url_format'] = site_url('/orders/all/{offset}/'.$this->uri->segment(4));
			$data['s_filter'] = $this->uri->segment(4);
		} else {
			$config['base_url'] = base_url().'index.php/orders/all/';
		}

		//Start: Tell us which filter checkboxes/dropdowns are selected
		//so we can reselect them again
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
				$criteria_value = $parse_filter[1];
				
				$data[$criteria_name] = urldecode($criteria_value);
			}
		}
		//End

		//Tell us how many orders to show per page
		$per_page = ($data['s_perpage'] == "") ? 25 : $data['s_perpage'];
		
		$query = $this->orders_model->countOrders();
		
		$config['total_rows'] 	= $query;
		$config['uri_segment'] 	= 3;
		$config['per_page'] 	= $per_page;
			
		$this->pagination->initialize($config);
		
		$data['results_total'] = $config['total_rows'];
		//End of pagination

		$data['title']	   = 'All Orders';
		$data['orders']    = $this->orders_model->listOrders($config['per_page'],$this->uri->segment(3));
		$data['statuses']  = $this->orders_model->listCustomStatuses();
		$data['templates'] = $this->orders_model->returnTemplates();
		$data['template_types'] = $this->orders_model->returnTemplateTypes();
		$data['content']   = 'orders/orders_list';

		$this->load->view('global/template',$data);

	}

	#------------------------------------------------------
	# View order
	#------------------------------------------------------
	function view() {

		$this->permissions->access('can_access_order_listview');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
		$data['redirect_link'] = $redirect->link;
		$data['redirect_query_string'] = $redirect->query_string;

		$data['order'] 			 = $this->orders_model->getOrderDetails($this->uri->segment(3));
		$data['order_inventory'] = $this->orders_model->getOrderInventory($this->uri->segment(3));
		$data['notes'] 			 = $this->orders_model->notes($this->uri->segment(3));
		$data['custom_field_templates'] = $this->settings_model->getCustomFields('orders');
		$data['statuses'] 		 = $this->orders_model->listCustomStatuses();
		$data['order_flow']		 = $this->orders_model->getFlowStatuses();
		$data['templates'] 		 = $this->orders_model->returnTemplates();
		$channel		 		 = $this->inventory_model->getLocationByShortname($data['order']->site);
		
		// Set the order source/channel
		$data['channel'] = ($channel->name == '') ? capfirst($data['order']->site) : $channel->name;
		
		//Get the coupon code for this order if it exists and if module is installed
		$data['order']->{'coupon_code'} = $this->modules_model->getCouponCode($data['order']->session_id);

		if (!empty($_POST)) {

			// Get status information
			$status = $this->orders_model->getStatusFromLabel($this->input->post('s_orderstatus'));
		
			// Update the order status
			$this->orders_model->updateOrderStatus($this->input->post('s_orderid'), $status->type);
			
			//Record order note
			if ($data['order']->order_status != $this->input->post('s_orderstatus')) {
				if ($data['order']->order_status == '') {
					$ordernote_status_from = 'Unprocessed';
				} else {
					$old_order_status = $this->orders_model->getStatusFromLabel($data['order']->order_status);
					$ordernote_status_from = $old_order_status->label;
				}

				$this->orders_model->recordnote($this->input->post('s_orderid'), 'Order status changed from ' . $ordernote_status_from . ' to ' . $status->label);

				//Create tags to pass to notifications
				$tags['order'] = $data['order'];
				$tags['items'] = $data['order_inventory'];
				
				//Send email to customer depending upon the status change
				$this->orders_model->sendNotifications($status->id, $data['order']->customer_email, $tags);

			}

			//Record order note (Custom)
			if ($this->input->post('s_ordernotes') != '') {
				$this->orders_model->recordnote($this->input->post('s_orderid'), $this->input->post('s_ordernotes'));
			}
						
			$this->session->set_flashdata('notice','Order status updated.');		
			redirect('orders/view/' . $this->input->post('s_orderid') . $redirect->query_string);
		}
	
		$data['title']	 = 'View Order';
		$data['content'] = 'orders/orders_view';

		$this->load->view('global/template',$data);
	
	}

	#------------------------------------------------------
	# Create order
	#------------------------------------------------------
	function build() {

		$this->permissions->access('can_access_order_builder');

		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('account_id', '', 'trim');
		$this->form_validation->set_rules('billing_title', 'Billing title', 'trim');
		$this->form_validation->set_rules('billing_firstname', 'Billing firstname', 'trim|required');
		$this->form_validation->set_rules('billing_surname', 'Billing surname', 'trim|required');
		$this->form_validation->set_rules('billing_company', 'Billing company', 'trim');
		$this->form_validation->set_rules('billing_address1', 'Billing address line 1', 'trim|required');
		$this->form_validation->set_rules('billing_address2', 'Billing address line 2', 'trim');
		$this->form_validation->set_rules('billing_city', 'Billing town/city', 'trim|required');
		$this->form_validation->set_rules('billing_postcode', 'Billing postcode', 'trim|required');
		$this->form_validation->set_rules('delivery_title', 'Delivery title', 'trim');
		$this->form_validation->set_rules('delivery_firstname', 'Delivery firstname', 'trim|required');
		$this->form_validation->set_rules('delivery_surname', 'Delivery surname', 'trim|required');
		$this->form_validation->set_rules('delivery_company', 'Delivery company', 'trim');
		$this->form_validation->set_rules('delivery_address1', 'Delivery address line 1', 'trim|required');
		$this->form_validation->set_rules('delivery_address2', 'Delivery address line 2', 'trim');
		$this->form_validation->set_rules('delivery_city', 'Delivery town/city', 'trim|required');
		$this->form_validation->set_rules('delivery_postcode', 'Delivery postcode', 'trim|required');
		$this->form_validation->set_rules('customer_email', 'Customer email', 'trim|required');
		$this->form_validation->set_rules('customer_phone', 'Customer phone', 'trim');
		$this->form_validation->set_rules('instructions', 'Instructions', 'trim');
		$this->form_validation->set_rules('transaction_type', 'Payment method', 'trim');
		$this->form_validation->set_rules('transaction_id', 'Transaction ID', 'trim');
		$this->form_validation->set_rules('product_no[]', 'Product no', 'trim|required');
		$this->form_validation->set_rules('product_name[]', 'Product name', 'trim|required');
		$this->form_validation->set_rules('product_qty[]', 'Product quantity', 'trim');
		$this->form_validation->set_rules('product_price[]', 'Product price', 'trim');
		$this->form_validation->set_rules('order_discount', 'Discount', 'trim');
		$this->form_validation->set_rules('order_shipping', 'Shipping cost', 'trim');
		$this->form_validation->set_rules('shipping_method', 'Shipping method', 'trim');
		$this->form_validation->set_rules('order_vat', 'VAT', 'trim');
		$this->form_validation->set_rules('s_orderstatus', 'Order status', 'trim');
		$this->form_validation->set_rules('s_ordernotes', 'Order notes', 'trim');
		$this->form_validation->set_rules('s_dispatch_day', '', 'trim');
		$this->form_validation->set_rules('s_dispatch_month', '', 'trim');
		$this->form_validation->set_rules('s_dispatch_year', '', 'trim');
		$this->form_validation->set_rules('custom_field_data[]', '', 'trim');
		$this->form_validation->set_rules('site', '', 'trim');

		if ($this->form_validation->run() == FALSE):
		
			$data['countries'] = $this->shipping_model->getISOCountries();
			$data['custom_field_templates'] = $this->settings_model->getCustomFields('orders');
			$data['statuses']  = $this->orders_model->listCustomStatuses();
			$data['locations'] = $this->inventory_model->getLocations();
			
			// Pass the new order number to the view
			$data['order'] = (object) array('order_ref' => "Auto");

			$data['title']	 = 'Build Order';
			$data['content'] = 'orders/orders_edit';
	
			$data['form_open'] = '<form action="'.site_url('orders/build').'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Build order';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = site_url('orders/all');
	
			$this->load->view('global/template',$data);

		else:

			//Check if this is a refund order (check the 3rd segment of the uri)
			$is_refund = ($this->uri->segment(3) != "") ? TRUE : FALSE;

			//Calculate the product line totals
			while( 
				   list($order_inventory_id_key, $order_inventory_id)=each($_POST['order_inventory_id']) and
				   list($product_id_key, $product_id)=each($_POST['product_id']) and 
				   list($product_no_key, $product_no)=each($_POST['product_no']) and
				   list($product_name_key, $product_name)=each($_POST['product_name']) and
				   list($product_qty_key, $product_qty)=each($_POST['product_qty']) and
				   list($product_price_key, $product_price)=each($_POST['product_price']) and
				   list($remove_key, $remove)=each($_POST['remove'])
				 )
			{

				$product_qty = ($product_qty == '') ? 1 : $product_qty;
				$product_price = (empty($product_price)) ? '0:00' : $product_price;

				//note: product_options set in product_name, therefore it will be null
				if (!empty($product_no) && !empty($product_name) && $remove != "yes") {

					$inv_item[] = array(
						'product_id' 	  => $product_id,
						'product_no'	  => $product_no,
						'product_name'    => $product_name,
						'product_qty'	  => $product_qty,
						'product_price'	  => $product_price,
						'product_options' => NULL,
					);
				
				}
								
				//Recalculate order total
				$order_total = $order_total + ($product_qty * $product_price);

			}
			
			//Recalculate order total and order VAT (if ticked)
			$order_discount = number_format($this->input->post('order_discount'), 2, '.', '');
			$order_total = number_format($order_total - $order_discount, 2, '.', '');
			if ($this->input->post('auto_vat') == 'true') {
				$order_vat = number_format(($order_total + $this->input->post('order_shipping')) * $this->config->item('vat_rate'), 2, '.', '');
			} else {
				$order_vat = $this->input->post('order_vat');
			}
			
			//Create order
			//Create order (If this is a refund, we need to keep the original order reference)
			$preserved_order_ref = ($is_refund) ? $this->input->post('order_ref') : NULL;
			$order_id = $this->orders_model->createOrder($order_total, $order_vat, $preserved_order_ref, $inv_item);

			//Save custom field data
			if ($_POST['custom_field_label'] != '') {
				while(  list($custom_field_id_key, $custom_field_id)=each($_POST['custom_field_id']) and
						list($custom_field_label_key, $custom_field_label)=each($_POST['custom_field_label']) and
						list($custom_field_data_key, $custom_field_data)=each($_POST['custom_field_data'])
					 )
				{
					if (empty($custom_field_id)) {
						$custom_field_update = false;
					} else {
						$custom_field_update = true;
					}
					
					if (!empty($custom_field_data)) {
					$this->settings_model->recordCustomFieldData($order_id, $custom_field_label, $custom_field_data, $custom_field_update);
					}
				}
			}
			
			//Record order note (use the order_id in segment 3 of uri if it exists which is the original order's id)
			$recordnote_id = ($is_refund) ? $this->uri->segment(3) : $order_id;
			$recordnote_message = ($is_refund) ? "<a href=\"".site_url("orders/editrefund/$order_id")."\">Refund #$order_id</a> attached to order" : "New order created";
			$this->orders_model->recordnote($recordnote_id, $recordnote_message);

			//Record order note (Custom)
			if ($this->input->post('s_ordernotes') != '') {
				$this->orders_model->recordnote($order_id, $this->input->post('s_ordernotes'));
			}

			// Get order details
			$order = $this->orders_model->getOrderDetails($order_id);
			$items = $this->orders_model->getOrderInventory($order_id);

			// Create the arrays to pass to the status notifications
			$tags['order'] = $order;
			$tags['items'] = $items;
			
			// Get status information
			$status = $this->orders_model->getStatusFromLabel($this->input->post('s_orderstatus'));

			//Record order note (only if not a refund)
			if ($this->input->post('s_orderstatus') != "" && !$is_refund) {
			$this->orders_model->recordnote($order_id, 'Order status changed from Unprocessed to ' . $status->label);
			}

			//Send email to customer depending upon the status change
			$this->orders_model->sendNotifications($status->id, $order->customer_email, $tags);

			$this->session->set_flashdata('notice','New order created');
			
			//Send the user to the correct page (order_id)
			$redirect_path = ($is_refund) ? "orders/all/0/filter=true&s_customer=$preserved_order_ref" : "orders/view/$order_id";
			redirect($redirect_path);

		endif;
	
	}

	#------------------------------------------------------
	# Update order
	#------------------------------------------------------
	function edit() {

		$this->permissions->access('can_access_order_builder');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
		$data['redirect_link'] = $redirect->link;
		$data['redirect_query_string'] = $redirect->query_string;
	
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('account_id', '', 'trim');
		$this->form_validation->set_rules('billing_title', 'Billing title', 'trim');
		$this->form_validation->set_rules('billing_firstname', 'Billing firstname', 'trim|required');
		$this->form_validation->set_rules('billing_surname', 'Billing surname', 'trim|required');
		$this->form_validation->set_rules('billing_company', 'Billing company', 'trim');
		$this->form_validation->set_rules('billing_address1', 'Billing address line 1', 'trim|required');
		$this->form_validation->set_rules('billing_address2', 'Billing address line 2', 'trim');
		$this->form_validation->set_rules('billing_city', 'Billing town/city', 'trim|required');
		$this->form_validation->set_rules('billing_postcode', 'Billing postcode', 'trim|required');
		$this->form_validation->set_rules('delivery_title', 'Delivery title', 'trim');
		$this->form_validation->set_rules('delivery_firstname', 'Delivery firstname', 'trim|required');
		$this->form_validation->set_rules('delivery_surname', 'Delivery surname', 'trim|required');
		$this->form_validation->set_rules('delivery_company', 'Delivery company', 'trim');
		$this->form_validation->set_rules('delivery_address1', 'Delivery address line 1', 'trim|required');
		$this->form_validation->set_rules('delivery_address2', 'Delivery address line 2', 'trim');
		$this->form_validation->set_rules('delivery_city', 'Delivery town/city', 'trim|required');
		$this->form_validation->set_rules('delivery_postcode', 'Delivery postcode', 'trim|required');
		$this->form_validation->set_rules('customer_email', 'Customer email', 'trim|required');
		$this->form_validation->set_rules('customer_phone', 'Customer phone', 'trim');
		$this->form_validation->set_rules('instructions', 'Instructions', 'trim');
		$this->form_validation->set_rules('transaction_type', 'Payment method', 'trim');
		$this->form_validation->set_rules('transaction_id', 'Transaction ID', 'trim');
		$this->form_validation->set_rules('product_no[]', 'Product no', 'trim|required');
		$this->form_validation->set_rules('product_name[]', 'Product name', 'trim|required');
		$this->form_validation->set_rules('product_qty[]', 'Product quantity', 'trim');
		$this->form_validation->set_rules('product_price[]', 'Product price', 'trim');
		$this->form_validation->set_rules('order_discount', 'Discount', 'trim');
		$this->form_validation->set_rules('order_shipping', 'Shipping cost', 'trim');
		$this->form_validation->set_rules('shipping_method', 'Shipping method', 'trim');
		$this->form_validation->set_rules('order_vat', 'VAT', 'trim');
		$this->form_validation->set_rules('s_orderstatus', 'Order status', 'trim');
		$this->form_validation->set_rules('s_ordernotes', 'Order notes', 'trim');
		$this->form_validation->set_rules('s_dispatch_day', '', 'trim');
		$this->form_validation->set_rules('s_dispatch_month', '', 'trim');
		$this->form_validation->set_rules('s_dispatch_year', '', 'trim');
		$this->form_validation->set_rules('custom_field_data[]', '', 'trim');

		if ($this->form_validation->run() == FALSE):

			$data['order'] 			 = $this->orders_model->getOrderDetails($this->uri->segment(3));
			$data['order_inventory'] = $this->orders_model->getOrderInventory($this->uri->segment(3));
			$data['statuses'] 		 = $this->orders_model->listCustomStatuses();
			$data['countries'] 		 = $this->shipping_model->getISOCountries();
			$data['custom_field_templates'] = $this->settings_model->getCustomFields('orders');

			$data['title']	 = 'Edit Order';
			$data['content'] = 'orders/orders_edit';
	
			$data['form_open'] = '<form action="'.site_url('orders/edit/'.$this->uri->segment(3) . $redirect->query_string).'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Edit order';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = site_url('orders/view/'.$this->uri->segment(3) . $redirect->query_string);
	
			$this->load->view('global/template',$data);
		
		else:

			//Stringify the inventory
			while( 
				   list($order_inventory_id_key, $order_inventory_id)=each($_POST['order_inventory_id']) and 
				   list($product_id_key, $product_id)=each($_POST['product_id']) and 
				   list($product_no_key, $product_no)=each($_POST['product_no']) and
				   list($product_name_key, $product_name)=each($_POST['product_name']) and
				   list($product_qty_key, $product_qty)=each($_POST['product_qty']) and
				   list($product_price_key, $product_price)=each($_POST['product_price']) and
				   list($remove_key, $remove)=each($_POST['remove'])
				 )
			{
				
				$product_qty = ($product_qty == '') ? 1 : $product_qty;
				$product_price = (empty($product_price)) ? '0:00' : $product_price;
				
				//note: product_options set in product_name, therefore it will be null
				if (!empty($product_no) && !empty($product_name)) {
						
					$inv_item = array(
						'order_id'		=> $this->uri->segment(3),
						'product_id' 	=> $product_id,
						'product_no'	=> $product_no,
						'product_name'  => $product_name,
						'product_qty'	=> $product_qty,
						'product_price'	=> $product_price,
						'product_options' => NULL,
					);
					
					// Add or update this inventory item
					$this->orders_model->updateOrderInventory($order_inventory_id, $inv_item, $remove);
					
					//Recalculate order total whilst we're in the loop
					$order_total = $order_total + ($product_qty * $product_price);
				}
			}
			
			//Recalculate order total and order VAT (if ticked)
			$order_discount = number_format($this->input->post('order_discount'), 2, '.', '');
			$order_total = number_format($order_total - $order_discount, 2, '.', '');
			if ($this->input->post('auto_vat') == 'true') {
				$order_vat = number_format(($order_total + $this->input->post('order_shipping')) * $this->config->item('vat_rate'), 2, '.', '');
			} else {
				$order_vat = $this->input->post('order_vat');
			}
			
			$order = $this->orders_model->getOrderDetails($this->uri->segment(3));
			$items = $this->orders_model->getOrderInventory($this->uri->segment(3));
			
			//Update order
			$this->orders_model->updateOrder($this->input->post('s_orderid'), $order_total, $order_vat);

			//Save custom field data
			if ($_POST['custom_field_label'] != '') {
				while(  list($custom_field_id_key, $custom_field_id)=each($_POST['custom_field_id']) and
						list($custom_field_label_key, $custom_field_label)=each($_POST['custom_field_label']) and
						list($custom_field_data_key, $custom_field_data)=each($_POST['custom_field_data'])
					 )
				{
					if (empty($custom_field_id)) {
						$custom_field_update = false;
					} else {
						$custom_field_update = true;
					}
					
					if (!empty($custom_field_data)) {
					$this->settings_model->recordCustomFieldData($this->input->post('s_orderid'), $custom_field_label, $custom_field_data, $custom_field_update);
					}
				}
			}
			
			//Record order note
			if ($this->input->post('order_changes') == 'true') {
			$this->orders_model->recordnote($this->input->post('s_orderid'), 'Order details updated');
			}

			// Get status information
			$status = $this->orders_model->getStatusFromLabel($this->input->post('s_orderstatus'));
	
			// Update the order status
			$this->orders_model->updateOrderStatus($this->input->post('s_orderid'), $status->type);

			//Record order note
			if ($order->order_status != $this->input->post('s_orderstatus')) {
				if ($order->order_status == '') {
					$ordernote_status_from = 'Unprocessed';
				} else {
					$old_order_status = $this->orders_model->getStatusFromLabel($order->order_status);
					$ordernote_status_from = $old_order_status->label;
				}
				
				$this->orders_model->recordnote($this->input->post('s_orderid'), 'Order status changed from ' . $ordernote_status_from . ' to ' . $status->label);

				//Create tags for email notifications
				$tags['order'] = $order;
				$tags['items'] = $items;
			
				//Send email to customer depending upon the status change
				$this->orders_model->sendNotifications($status->id, $order->customer_email, $tags);

			}

			//Record order note (Custom)
			if ($this->input->post('s_ordernotes') != '') {
				$this->orders_model->recordnote($this->input->post('s_orderid'), $this->input->post('s_ordernotes'));
			}

			$this->session->set_flashdata('notice','Order updated. <a href="'.$redirect->link.'">Back to orders</a>');		
			redirect('orders/view/' . $this->uri->segment(3) . $redirect->query_string);
		
		endif;
	
	}

	#------------------------------------------------------
	# Mark as dispatched
	#------------------------------------------------------
	function markasdispatched() {
	
		$order_id = $this->uri->segment(3);
		
		$order = $this->orders_model->getOrderDetails($order_id);
		$items = $this->orders_model->getOrderInventory($order_id);

		if (!empty($order_id)) {
			$status = $this->orders_model->markAsDispatched($order_id);
			
			//Record order note
			if ($order->order_status == '') {
				$ordernote_status_from = 'Unprocessed';
			} else {
				$old_order_status = $this->orders_model->getStatusFromLabel($order->order_status);
				$ordernote_status_from = $old_order_status->label;
			}
			$this->orders_model->recordnote($order_id, 'Order status changed from ' . $ordernote_status_from . ' to ' . $status->label);

			//Create tags for email notifications
			$tags['order'] = $order;
			$tags['items'] = $items;
			
			//Send email to customer if order status is "Dispatched"
			$this->orders_model->sendNotifications($status->id, $order->customer_email, $tags);			

			//Record order note
			$this->orders_model->recordnote($order_id, "$status->label email sent to customer");

		}
	
	}

	#------------------------------------------------------
	# View printable order
	#------------------------------------------------------
	function printnote() {

		$this->permissions->access('can_access_order_listview');

		$content = array();

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
		$redirects['redirect_link'] = $redirect->link;
		$redirects['redirect_query_string'] = $redirect->query_string;

		// Load snippets & config settings
		$snippets = $this->settings_model->loadSnippets();

		// Load our data
		$tags['order'] = $order = $this->orders_model->getOrderDetails($this->uri->segment(3));
		$tags['items'] = $this->orders_model->getOrderInventory($this->uri->segment(3));
		
		// Get the packing template
		$template = $this->orders_model->getTemplate($this->uri->segment(4));
		
		// If template exists then create the parser tags
		// else display message indicating template hasn't been created yet
		if ($template->id > 0) {
			$html['body'] = $template->content;

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

		} else {
			$html['body'] = sprintf('<p>A template has not been set up for this channel yet. <a href="javascript:void(0);" onclick="window.opener.location.href=\'%s\';self.close();">Set one up now?</a></p>', $order->site, site_url('orders/templates'));
			$content['order_ref'] = "Template not created";
		}

		// Merge the $html and $content arrays into one
		$data = array_merge($html, $content, $snippets, $redirects);

		$this->parser->parse('orders/orders_print', $data);
		
		// Mark order as printed
		$this->orders_model->markAsPrinted($this->uri->segment(3));
		
	}

	#------------------------------------------------------
	# Export CSV
	#------------------------------------------------------
	function export() {
		$this->permissions->access('can_access_order_exports');
		$data = $this->orders_model->exportOrders();
		$filename = 'orders_'.date('Y-m-d').'.csv';
		force_download($filename, $data);
	}

	#------------------------------------------------------
	# Ajax: Get total
	#------------------------------------------------------
	function gettotal() {
		
		$order_discount = number_format($this->input->post('order_discount'),2,'.','');
		$order_total 	= number_format($this->input->post('order_total'),2,'.','');
		$order_total 	= $order_total - $order_discount;
		$order_shipping = number_format($this->input->post('order_shipping'),2,'.','');
		$order_vat = $this->input->post('order_vat');
		
		if ($order_vat == 'auto') {
			$order_vat = number_format(($order_total + $order_shipping) * $this->config->item('vat_rate'),2,'.','');
		}
		
		$total = money($order_total + $order_shipping + $order_vat);
		
		print json_encode(
			array('total' => $total, 'order_total' => $order_total, 'order_shipping' => $order_shipping, 'order_vat' => $order_vat)
		);
	
	}

	#------------------------------------------------------
	# Custom Fields
	#------------------------------------------------------
	function custom() {
	
		$this->permissions->access('can_access_order_custom_fields');

		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		if ($this->input->post('custom_field_id') == '') {
		$this->form_validation->set_rules('custom_field_label', 'Custom field label', 'required');
		}
		$this->form_validation->set_rules('custom_field_title', 'Custom field title', 'required');

		if ($this->form_validation->run() == FALSE):

			$data['title']	 = 'Orders > Custom Fields';
	
			$data['custom_fields'] = $this->settings_model->getCustomFields('orders');
			
			//Get custom field data to edit
			if ($this->uri->segment(3) != '') {
			$data['edit'] = $this->settings_model->getCustomField($this->uri->segment(3));
			}
			
			$data['form_open'] = '<form action="'.current_url().'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Orders:';
			$data['form_close'] = '</form>';
			if ($this->uri->segment(3) != '') {
			$data['form_cancel_link'] = site_url('orders/custom');
			} else {
			$data['form_cancel_link'] = site_url('orders');
			}
			
			$data['content'] = 'options/options_customfields';
			$this->load->view('global/template',$data);		
		
		else:
		
			//Save and redirect
			if ($this->input->post('custom_field_id') != '') {
				$this->settings_model->updateCustomField($this->input->post('custom_field_id'));
			} else {
				$this->settings_model->createCustomField('orders');
			}
			$this->session->set_flashdata('notice','Custom field updated.');
			redirect('orders/custom');
		
		endif;
	}

	#------------------------------------------------------
	# View printable orders for today
	# - This feature has been deprecated as of 3.1.10
	#------------------------------------------------------
	#function printall() {
	#
	#	$this->permissions->access('can_access_order_listview');
	#	
	#	$today = date('Y-m-d', strtotime('today'));
	#	$data['orders'] = $this->orders_model->theDaysOrders($today);
	#	$this->load->view('orders/orders_print_all',$data);
	#	
	#}

	#------------------------------------------------------
	# Create Refund (Attaches to original order)
	#------------------------------------------------------
	function createrefund() {

		$this->permissions->access('can_access_order_builder');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
		$data['redirect_link'] = $redirect->link;
		$data['redirect_query_string'] = $redirect->query_string;

		$refunds = array();
		$refund_total = 0;
	
		$data['order'] = $this->orders_model->getOrderDetails($this->uri->segment(3));
		
		//Get the posted order inventory (the refunds), not the original order inventory
		if (!empty($_POST['refund'])) {
			
			//Loop through each post and append to string;
			foreach ($_POST['refund'] as $refund_key=>$refund_id) {

				// Get the item from the orders_inventory table
				$item = $this->orders_model->getOrderInventoryItem($refund_id);
				
				// Convert the price to a negative
				$item->product_price = $item->product_price * -1;
				
				// Push this product into a new global array
				array_push($refunds, $item);
				
				// Calculate the total refund whilst we're in the loop
				$refund_total = $refund_total + ($item->product_qty * $item->product_price);
			
			}
			
		}
		
		//Set the new inventory
		$data['order_inventory'] = $refunds;

		//Include the VAT on the selected items, but only if this
		//was greater than 0 on the initial order
		//Note: Use the vat_rate from the orders table - not the config 
		//setting - and also include the shipping
		//Returned value is already negative.
		if ($data['order']->order_vat > 0) {
			$refund_total = $refund_total + $data['order']->{'order_discount'};
			$order_vat = ( ($refund_total - $data['order']->{'order_shipping'} ) * $data['order']->vat_rate );
		} else {
			$order_vat = 0.00;
		}
		
		//Reset some of the data
		$data['order']->{'order_id'} 	 	= '';
		$data['order']->{'order_date'}		= NULL;
		$data['order']->{'order_status'} 	= $this->orders_model->getDefaultStatus('Refunded')->value;
		$data['order']->{'transaction_type'}= 'Sale or Return';
		$data['order']->{'transaction_id'}	= NULL;
		$data['order']->{'order_shipping'}	= $data['order']->{'order_shipping'} * -1; //Convert to negative
		$data['order']->{'order_vat'}		= $order_vat;
		$data['order']->{'order_total'}		= $refund_total;
		$data['order']->{'order_discount'}	= $data['order']->{'order_discount'} * -1; //Convert to negative

		$data['countries'] = $this->shipping_model->getISOCountries();
		$data['custom_field_templates'] = $this->settings_model->getCustomFields('orders');

		$data['title']	 = 'Create Refund';
		$data['content'] = 'orders/orders_refund';

		$data['form_open'] = '<form action="'.site_url('orders/build/'.$this->uri->segment(3).$redirect->query_string).'" method="post" enctype="multipart/form-data" >';
		$data['form_title'] = 'Create Refund';
		$data['form_close'] = '</form>';
		$data['form_cancel_link'] = site_url('orders/view/'.$this->uri->segment(3).$redirect->query_string);

		$this->load->view('global/template',$data);
	
	}

	#------------------------------------------------------
	# Update refund
	#------------------------------------------------------
	function editrefund() {

		$this->permissions->access('can_access_order_builder');

		// Get the redirect url for use on links, buttons and redirects
		$redirect = redirect_get();
		$data['redirect_link'] = $redirect->link;
		$data['redirect_query_string'] = $redirect->query_string;
	
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('billing_title', 'Billing title', 'trim');
		$this->form_validation->set_rules('billing_firstname', 'Billing firstname', 'trim|required');
		$this->form_validation->set_rules('billing_surname', 'Billing surname', 'trim|required');
		$this->form_validation->set_rules('billing_company', 'Billing company', 'trim');
		$this->form_validation->set_rules('billing_address1', 'Billing address line 1', 'trim|required');
		$this->form_validation->set_rules('billing_address2', 'Billing address line 2', 'trim');
		$this->form_validation->set_rules('billing_city', 'Billing town/city', 'trim|required');
		$this->form_validation->set_rules('billing_postcode', 'Billing postcode', 'trim|required');
		$this->form_validation->set_rules('delivery_title', 'Delivery title', 'trim');
		$this->form_validation->set_rules('delivery_firstname', 'Delivery firstname', 'trim|required');
		$this->form_validation->set_rules('delivery_surname', 'Delivery surname', 'trim|required');
		$this->form_validation->set_rules('delivery_company', 'Delivery company', 'trim');
		$this->form_validation->set_rules('delivery_address1', 'Delivery address line 1', 'trim|required');
		$this->form_validation->set_rules('delivery_address2', 'Delivery address line 2', 'trim');
		$this->form_validation->set_rules('delivery_city', 'Delivery town/city', 'trim|required');
		$this->form_validation->set_rules('delivery_postcode', 'Delivery postcode', 'trim|required');
		$this->form_validation->set_rules('customer_email', 'Customer email', 'trim');
		$this->form_validation->set_rules('customer_phone', 'Customer phone', 'trim');
		$this->form_validation->set_rules('instructions', 'Instructions', 'trim');
		$this->form_validation->set_rules('transaction_type', 'Payment method', 'trim');
		$this->form_validation->set_rules('transaction_id', 'Transaction ID', 'trim');
		$this->form_validation->set_rules('site', 'Site', 'trim');
		$this->form_validation->set_rules('product_no[]', 'Product no', 'trim|required');
		$this->form_validation->set_rules('product_name[]', 'Product name', 'trim|required');
		$this->form_validation->set_rules('product_qty[]', 'Product quantity', 'trim');
		$this->form_validation->set_rules('product_price[]', 'Product price', 'trim');
		$this->form_validation->set_rules('order_shipping', 'Shipping cost', 'trim');
		$this->form_validation->set_rules('shipping_method', 'Shipping method', 'trim');
		$this->form_validation->set_rules('order_vat', 'VAT', 'trim');
		$this->form_validation->set_rules('s_orderstatus', 'Order status', 'trim');
		$this->form_validation->set_rules('s_ordernotes', 'Order notes', 'trim');
		$this->form_validation->set_rules('s_dispatch_day', '', 'trim');
		$this->form_validation->set_rules('s_dispatch_month', '', 'trim');
		$this->form_validation->set_rules('s_dispatch_year', '', 'trim');
		$this->form_validation->set_rules('custom_field_data[]', '', 'trim');

		if ($this->form_validation->run() == FALSE):

			$data['order'] = $this->orders_model->getOrderDetails($this->uri->segment(3));
			$data['order_inventory'] = $this->orders_model->getOrderInventory($this->uri->segment(3));
			$data['notes'] = $this->orders_model->notes($this->uri->segment(3));
			$data['custom_field_templates'] = $this->settings_model->getCustomFields('orders');

			$data['title']	 = 'Edit Refund';
			$data['content'] = 'orders/orders_refund';
	
			$data['form_open'] = '<form action="'.site_url('orders/editrefund/'.$this->uri->segment(3)).'/'.$this->uri->segment(4).$redirect->query_string.'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] = 'Edit refund';
			$data['form_close'] = '</form>';
			$data['form_cancel_link'] = $redirect->link;
	
			$this->load->view('global/template', $data);
		
		else:

			//Stringify the inventory
			while( 
				   list($order_inventory_id_key, $order_inventory_id)=each($_POST['order_inventory_id']) and 
				   list($product_id_key, $product_id)=each($_POST['product_id']) and 
				   list($product_no_key, $product_no)=each($_POST['product_no']) and
				   list($product_name_key, $product_name)=each($_POST['product_name']) and
				   list($product_qty_key, $product_qty)=each($_POST['product_qty']) and
				   list($product_price_key, $product_price)=each($_POST['product_price']) and
				   list($remove_key, $remove)=each($_POST['remove'])
				 )
			{
				
				$product_qty = ($product_qty == '') ? 1 : $product_qty;
				$product_price = (empty($product_price)) ? '0:00' : $product_price;
								
				//note: product_options set in product_name, therefore it will be null
				if (!empty($product_no) && !empty($product_name)) {
						
					$inv_item = array(
						'order_id'		=> $this->uri->segment(3),
						'product_id' 	=> $product_id,
						'product_no'	=> $product_no,
						'product_name'  => $product_name,
						'product_qty'	=> $product_qty,
						'product_price'	=> $product_price,
						'product_options' => NULL,
					);
					
					// Add or update this inventory item
					$this->orders_model->updateOrderInventory($order_inventory_id, $inv_item, $remove);
					
					//Recalculate order total whilst we're in the loop
					$order_total = $order_total + ($product_qty * $product_price);

				}
			}
			
			//Recalculate order total and order VAT (if ticked)
			$order_total = number_format($order_total, 2, '.', '');
			if ($this->input->post('auto_vat') == 'true') {
				$order_vat = number_format(($order_total + $this->input->post('order_shipping')) * $this->config->item('vat_rate'), 2, '.', '');
			} else {
				$order_vat = $this->input->post('order_vat');
			}
			
			$data['order'] = $this->orders_model->getOrderDetails($this->uri->segment(3));
			
			//Update order
			$this->orders_model->updateOrder($this->input->post('s_orderid'), $order_total, $order_vat);

			//Save custom field data
			if ($_POST['custom_field_label'] != '') {
				while(  list($custom_field_id_key, $custom_field_id)=each($_POST['custom_field_id']) and
						list($custom_field_label_key, $custom_field_label)=each($_POST['custom_field_label']) and
						list($custom_field_data_key, $custom_field_data)=each($_POST['custom_field_data'])
					 )
				{
					if (empty($custom_field_id)) {
						$custom_field_update = false;
					} else {
						$custom_field_update = true;
					}
					
					if (!empty($custom_field_data)) {
					$this->settings_model->recordCustomFieldData($this->input->post('s_orderid'), $custom_field_label, $custom_field_data, $custom_field_update);
					}
				}
			}
			
			//Record order note
			if ($this->input->post('order_changes') == 'true') {
			$this->orders_model->recordnote($this->input->post('s_orderid'), 'Refund details updated');
			}

			//Record order note (Custom)
			if ($this->input->post('s_ordernotes') != '') {
				$this->orders_model->recordnote($this->input->post('s_orderid'), $this->input->post('s_ordernotes'));
			}

			$this->session->set_flashdata('notice','Refund updated.');		
			redirect("orders/all/0/filter=true&s_customer=".$this->input->post('order_ref').$redirect->query_string);
		
		endif;
	
	}

	#------------------------------------------------------
	# Delete refund - Ajax call
	#------------------------------------------------------
	function deleterefund() {
		
		$this->permissions->access('can_access_order_builder');
		$this->orders_model->deleteRefund( $this->uri->segment(3) );
		
	}

	#------------------------------------------------------
	# Bulk process orders
	#------------------------------------------------------
	function process() {

		$this->permissions->access('can_access_order_builder');
		
		if (!empty($_POST['order_id']) && $_POST['action'] != "") {
			
			// Store the checked items so we can re-tick them on our return
			$this->session->set_flashdata('checked_orderids', $_POST['order_id']);
		
			switch($this->input->post('action')) {
					
				// Change the order status
				case (preg_match('@status-(.?+)@', $this->input->post('action')) ? true : false) :
				
					foreach($_POST['order_id'] as $order_id) {

						// Get the current status of the order so we can record an order note
						$order = $this->orders_model->getOrderDetails($order_id);
						$items = $this->orders_model->getOrderInventory($order_id);
						
						//Record order note
						if ($order->order_status == '') {
							$ordernote_status_from = 'Unprocessed';
						} else {
							$old_order_status = $this->orders_model->getStatusFromLabel($order->order_status);
							$ordernote_status_from = $old_order_status->label;
						}
					
						// Remove the "status-" prefix from the status and 
						// set the POST var which needs to be passed to the model
						$order_status = str_replace('status-', '', $this->input->post('action'));
						$_POST['s_orderstatus'] = $order_status;

						// Get status information
						$status = $this->orders_model->getStatusFromLabel($order_status);
						
						// Set the POST var dates which also need passing to the model
						$_POST['s_dispatch_year']  = date('Y');
						$_POST['s_dispatch_month'] = date('m');
						$_POST['s_dispatch_day']   = date('d');
						
						// Only update the order/notes if the statuses are different
						if ($order->order_status != $status->value) {
							// Update the order status
							$this->orders_model->updateOrderStatus($order_id, $status->type);
	
							// Record order note about status change
							$this->orders_model->recordnote($order_id, "Order status changed from $ordernote_status_from to $status->label");

							// Get the data to pass as tags to status notifcations
							$tags['order'] = $order;
							$tags['items'] = $items;
										
							//Send email to customer depending upon the status change
							$this->orders_model->sendNotifications($status->id, $order->customer_email, $tags);

						}
					
					}

					//Redirect back to last page
					$this->session->set_flashdata('notice','Selected orders updated.');
					redirect($this->input->post('redirect'));
					break;

					
				// Print the packing notes for the selected orders
				case (preg_match('@printall-(.?+)@', $this->input->post('action')) ? true : false) :
				
					// Set back buttons link
					$data['redirect_link'] = $this->input->post('redirect');
					
					// Set the template type
					$data['template_type'] = str_replace('printall-', '', $this->input->post('action'));
					
					// Get the orders
					$data['orders'] = $this->orders_model->getOrders($_POST['order_id']);
					
					// Load snippets and configuration
					$data['snippets'] = $this->settings_model->loadSnippets();
					
					// Store the channel names so we don't need to do multiple database calls
					$channels = $this->inventory_model->getLocations();
					foreach($channels as $channel) {
						$data['channel'][$channel->shortname] = $channel->name;
					}
					
					// Output template
					$this->load->view('orders/orders_print_all', $data);
					
					// Mark order as printed
					$this->orders_model->markAsPrinted($_POST['order_id']);
					break;
					
				// Redirect to last page if no action is passed
				default:
					//Redirect back to last page
					redirect($this->input->post('redirect'));
					break;
				
			}
	
		} else {
			//Redirect back to last page
			redirect($this->input->post('redirect'));
		}
	
	}

	#------------------------------------------------------
	# Manage order statuses
	#------------------------------------------------------
	function statuses() {
	
		$this->permissions->access('can_access_order_statuses');
		
		$data['title']	  = 'Manage Order Statuses';
		$data['statuses'] = $this->orders_model->listCustomStatuses();

		$data['form_open'] = '<form action="' . site_url('orders/savestatuses') . '" method="post" enctype="multipart/form-data" >';
		$data['form_title'] = 'Manage Order Statuses';
		$data['form_close'] = '</form>';
		$data['form_cancel_link'] = site_url('orders');

		$data['content']  = 'orders/orders_statuses';
		$this->load->view('global/template', $data);
		
	}
	
	function savestatuses() {

		$this->permissions->access('can_access_order_statuses');

		// Counter
		$i = 1;
		
		// Loop through POST array and update statuses
		while(
			list($status_id, $id)=each($_POST['id']) and 
			list($status_label_id, $label)=each($_POST['label']) and 
			list($status_type_id, $type)=each($_POST['type']) and
			list($status_color_id, $color)=each($_POST['color'])
			)
		{
			
			// Label value - not really required, but we'll keep it in for info
			$value = ($id > 1) ? $label : NULL;

			//Set the flow (chart) flag
			$flow = (isset($_POST['flow'][$id])) ? 1 : 0;
			
			// Sort out the flags
			$flag_unprocessed = ( $this->input->post('flag_unprocessed') == $id ) ? 1 : 0;
			$flag_completed   = ( $this->input->post('flag_completed') == $id ) ? 1 : 0;
			$flag_refunded	  = ( $this->input->post('flag_refunded') == $id ) ? 1 : 0;
			$flag_cancelled	  = ( $this->input->post('flag_cancelled') == $id ) ? 1 : 0;
			$flag_dispatched  = ( $this->input->post('flag_dispatched') == $id ) ? 1 : 0;
			$flag_failed	  = ( $this->input->post('flag_failed') == $id ) ? 1 : 0;
			$flag_pending	  = ( $this->input->post('flag_pending') == $id ) ? 1 : 0;
			
			$status = array(
				'label'	 			=> $label,
				#'value'				=> $value, //This shouldn't be changed for preservation
				'type'   			=> $type,
				'color'	 			=> $color,
				'position' 			=> $i++,
				'flow'				=> $flow,
				'flag_unprocessed' 	=> $flag_unprocessed,
				'flag_completed' 	=> $flag_completed,
				'flag_refunded' 	=> $flag_refunded,
				'flag_cancelled' 	=> $flag_cancelled,
				'flag_failed'		=> $flag_failed,
				'flag_dispatched'	=> $flag_dispatched,
				'flag_pending'		=> $flag_pending,
			);
			
			#print_r($status); // For testing
			
			// Update status
			$this->orders_model->updateCustomStatus($id, $status);
			
		}
		
		// Now check if a new status is being added and
		// create it if so
		if ($this->input->post('new_status_label') != '') {
			
			$new_status_label = $this->input->post('new_status_label');
			
			// Strip out any brackets from the new_status_label input
			$strip_char = array(
				'(', ')', '[', ']'
			);
			foreach ($strip_char as $char) {
				$new_status_label = str_replace($char, '', $new_status_label);
			}
			
			$new_status = array(
				'label'	 => $this->input->post('new_status_label'),
				'value'	 => $new_status_label, // this field is used for preservation
				'type'   => $this->input->post('new_status_type'),
				'color'	 => $this->input->post('new_status_color'),
			);
			
			$this->orders_model->createCustomStatus($new_status);
			
		}
		
		// Check if any of the statuses are being deleted
		if (!empty($_POST['delete'])) {
			
			foreach($_POST['delete'] as $delete_id) {
				$this->orders_model->deleteCustomStatus($delete_id);
			}
			
		}

		// Redirect
		$this->session->set_flashdata('notice','Order statuses saved.');
		redirect('orders/statuses');
		
	}

	#------------------------------------------------------
	# Manage order status notifications (emails)
	#------------------------------------------------------
	function notifications() {

		$this->permissions->access('can_access_order_notifications');
	
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('status_id', 'Status', 'trim');
		$this->form_validation->set_rules('customer', 'Customer', 'trim');
		$this->form_validation->set_rules('admin', 'Admin', 'trim');
		$this->form_validation->set_rules('subject', 'Subject', 'trim|required');
		$this->form_validation->set_rules('body', 'Body', 'trim|required');

		if ($this->form_validation->run() == FALSE):

			$data['title'] = 'Status Notifications';
	
			// Get data
			$data['notifications'] = $this->orders_model->getStatusNotifications();
			$data['statuses'] 	   = $this->orders_model->listCustomStatuses();
			$data['users']		   = $this->login_model->listUsers();
			
			// Get custom field data to edit
			if ($this->uri->segment(3) != '') {
				$data['edit'] = $this->orders_model->getStatusNotification($this->uri->segment(3));
			}
			
			$data['form_open'] 		  = '<form action="'.current_url().'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] 	  = 'Manage Order Status Notifications';
			$data['form_close'] 	  = '</form>';
			$data['form_cancel_link'] = site_url('orders/notifications');
			
			$data['content'] = 'orders/orders_notifications';
			$this->load->view('global/template', $data);
		
		else:
		
			// Save and redirect
			if ($this->input->post('id') != '') {
				$this->orders_model->updateStatusNotification($this->input->post('id'));
			} else {
				$this->orders_model->createStatusNotification();
			}
			$this->session->set_flashdata('notice', 'Notifications updated.');
			redirect('orders/notifications');
		
		endif;
		
	}
	
	function deletenotification() {
		$this->permissions->access('can_access_order_notifications');
		$this->orders_model->deleteStatusNotification($this->uri->segment(3));
	}

	#------------------------------------------------------
	# Templates
	#------------------------------------------------------
	function templates() {

		$this->permissions->access('can_access_order_notifications');
		
		if ($this->db->table_exists('templates') === FALSE) {
			redirect('updates/r3110');
			exit();
		}
	
		$this->form_validation->set_message('required', 'required');
		$this->form_validation->set_message('valid_email', 'invalid email');
		$this->form_validation->set_message('max_length', ' ');
		$this->form_validation->set_message('exact_length', ' ');
		$this->form_validation->set_message('numeric', 'invalid');
		$this->form_validation->set_message('matches', 'not matching!');
		$this->form_validation->set_error_delimiters('<span class="error">', '</span>');

		$this->form_validation->set_rules('title', 'Title', 'trim|required');
		$this->form_validation->set_rules('content', 'Content', 'trim|required');

		if ($this->form_validation->run() == FALSE):

			$data['title'] = 'Order Note Templates';
	
			// Get data
			$data['templates'] = $this->orders_model->getTemplates();
			$data['channels'] = $this->inventory_model->getLocations();
			
			// Get custom field data to edit
			if ($this->uri->segment(3) != '') {
				$data['edit'] = $this->orders_model->getTemplate($this->uri->segment(3));
			}
			
			$data['form_open'] 		  = '<form action="'.current_url().'" method="post" enctype="multipart/form-data" >';
			$data['form_title'] 	  = 'Manage Packing Note Templates';
			$data['form_close'] 	  = '</form>';
			$data['form_cancel_link'] = site_url('orders/templates');
			
			$data['content'] = 'orders/orders_templates';
			$this->load->view('global/template', $data);
		
		else:
		
			// Save and redirect
			if ($this->input->post('id') != '') {
				$this->orders_model->updateTemplate($this->input->post('id'));
			} else {
				$this->orders_model->createTemplate();
			}
			$this->session->set_flashdata('notice', 'Template updated.');
			redirect('orders/templates');
		
		endif;
		
	}

	function deletetemplate() {
		$this->permissions->access('can_access_order_notifications');
		$this->orders_model->deleteTemplate(base64_decode($this->uri->segment(3)));
	}

	function previewtemplate() {

		$this->permissions->access('can_access_order_notifications');

		$content = array();

		// Load snippets & config settings
		$snippets = $this->settings_model->loadSnippets();

		// Load our data
		$tags['order'] = (object) array(
			'order_ref' 		=> '150101-105603-000001',
			'order_date' 		=> date('Y-m-d 10:56:03', strtotime('yesterday')),
			'billing_title' 	=> 'Mr',
			'billing_firstname' => 'Tony',
			'billing_surname' 	=> 'Stark',
			'billing_company' 	=> 'Stark Industries Inc.',
			'billing_address1'	=> '1 Ocean View',
			'billing_address2'	=> 'Mountain Top',
			'billing_city'		=> 'California',
			'billing_postcode'  => '902893',
			'billing_country'	=> 'USA',
			'delivery_title' 	=> 'Mr',
			'delivery_firstname' => 'Tony',
			'delivery_surname' 	=> 'Stark',
			'delivery_company' 	=> 'Stark Industries Inc.',
			'delivery_address1'	=> '1 Ocean View',
			'delivery_address2'	=> 'Mountain Top',
			'delivery_city'		=> 'California',
			'delivery_postcode'  => '902893',
			'delivery_country'	=> 'USA',
			'customer_email'	=> 'ironman@starkinc.com',
			'customer_phone'	=> '000103430',
			'order_total'		=> number_format(20000 * the_vat_rate(),2,'.',''),
			'order_vat'			=> number_format(20000*0.2, 2, '.', ''),
			'order_shipping'	=> '0.00',
			'total'				=> number_format(20000 * the_vat_rate(),2,'.',''),
			'order_discount'	=> '0.00',
			'shipping_method'	=> 'Express Delivery',
			'order_status'		=> 'Dispatched',
			'dispatch_date'		=> date('Y-m-d', strtotime('today')),
			'instructions'		=> "Press the buzzer on delivery.",
		);
		
		$tags['items'][] = (object) array(
			'product_no' => 'IRON1',
			'product_name' => 'Red/White Iron Man Suit',
			'product_options' => null,
			'product_qty' => 1,
			'product_price' => '10000.00',
			'linetotal' => '10000.00',
			''
		);
		$tags['items'][] = (object) array(
			'product_no' => 'IRON5',
			'product_name' => 'Black Iron Man Suit',
			'product_options' => '{Trim@Red}',
			'product_qty' => 1,
			'product_price' => '10000.00',
			'linetotal' => '10000.00',
			''
		);
		
		
		// Get the packing template
		$template = $this->orders_model->getTemplate($this->uri->segment(3));
		
		// If template exists then create the parser tags
		// else display message indicating template hasn't been created yet
		if ($template->id > 0) {
			$html['body'] = $template->content;

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
		}

		// Merge the $html and $content arrays into one
		$data = array_merge($html, $content, $snippets);
		$data['redirect_link'] = site_url('orders/templates');

		$this->parser->parse('orders/orders_print', $data);
		
	}

}