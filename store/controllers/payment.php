<?php
class Payment extends CI_Controller {

	function Payment() {
		parent::__construct();
		
		$this->load->model('settings_model');
		$this->settings_model->initConfig();
		
		$this->load->helper('form');
		$this->load->helper('text');
		$this->load->helper('cookie');
		
		$this->load->library('email');
		$this->load->library('encrypt');
		
		$this->load->model('basket_model');
		$this->load->model('pages_model');
		$this->load->model('category_model');

		//Load modules		
		foreach ($this->config->item('modules') as $module) {
			if (library_exists($module)):
			$this->load->library($module);
			endif;
		}

	}

	function index() {
		redirect('');
	}


	#------------------------------------------------------
	# //!SagePay SUCCESS page
	# - updates database
	# - deletes cookie
	# - sends email to vendor
	#------------------------------------------------------	
	function spsuccess() {
	
		global $data;

		$this->load->config('gateways/sagepay');
		$this->load->helper('sagepay');

		// Now check we have a Crypt field passed to this page 
		$strCrypt=$_REQUEST["crypt"];
		if (strlen($strCrypt)==0) {
			ob_end_flush();
			redirect('');
			exit;
		}

		// Now decode the Crypt field and extract the results
		$strDecoded=simpleXor(Base64Decode($strCrypt),$this->config->item('strEncryptionPassword'));
		$values = getToken($strDecoded);

		// Split out the useful information into variables we can use
		$data['strStatus']		 = $values['Status'];
		$data['strStatusDetail'] = $values['StatusDetail'];
		$data['strVendorTxCode'] = $values["VendorTxCode"]; //this is the 'order_id'
		$data['strVPSTxId']		 = $values["VPSTxId"];
		$data['strTxAuthNo']	 = $values["TxAuthNo"];
		$data['strAmount']		 = number_format($values["Amount"],2);
		$data['strAVSCV2']		 = $values["AVSCV2"];
		$data['strAddressResult']= $values["AddressResult"];
		$data['strPostCodeResult']= $values["PostCodeResult"];
		$data['strCV2Result'] 	 = $values["CV2Result"];
		$data['strGiftAid']		 = $values["GiftAid"];
		$data['str3DSecureStatus']= $values["3DSecureStatus"];
		$data['strCAVV']		 = $values["CAVV"];
		$data['strCardType']	 = $values["CardType"];
		$data['strLast4Digits']	 = $values["Last4Digits"];
		$data['strAddressStatus']= $values["AddressStatus"]; // PayPal transactions only
		$data['strPayerStatus']  =$values["PayerStatus"];     // PayPal transactions only

		//Strip the random number from the VendorTxCode, to leave the order_id
		$order_id = strstr($values["VendorTxCode"], '-', TRUE);

		//First get the inventory data for this order
		$inventory_items = $this->basket_model->getInventory($order_id);
		
		// Get the default status for 'completed'
		$status = $this->basket_model->getDefaultStatus('Completed');

		//Update the stock levels
		//Check session has been cleared so this function is not rerun if the page is refreshed.
		if ($this->session->userdata('store_session') != ''): //if not empty

			//Update order status/details in the database
			$transaction_data = $values['Status'] . ';' . $values['StatusDetail'] . ';' . $data['strAmount'] . ';';
			$this->basket_model->updateOrderStatus($order_id, $status->value, $status->type, $data['strVPSTxId'], $data['strAmount'], $transaction_data);		
			
			foreach ($inventory_items as $item) {
			
				//if a product name exists, then update the stock level accordingly.
				if (!empty($item->product_name)) {
					$this->basket_model->updateStockLevels($item->product_id, $item->product_qty);
				}
			
			}

			// Get order data (we do this here because of the 
			// new order_ref that was generated further above)
			$order = $vendor['order'] = $this->basket_model->getOrderDetails($order_id);
			
			//Set the parser tags data for notifications
			$tags['order'] = $order;
			$tags['items'] = $inventory_items;
			
			//Send email to customer depending upon the status change
			$this->basket_model->sendNotifications($status->id, $order->customer_email, $tags);							

			//Add customer to campaign monitor subscription
			cmSubscribe($order->account_id, $order->pref_newsletter, $order->billing_firstname, $order->billing_surname, $order->customer_email);
			//End.
			
			// Update the coupon code counter
			$this->coupons->counter($order->session_id);
			
		endif;
		
		#------------------------------------------------------
		# For Google Analytics Ecommerce code
		#------------------------------------------------------
		//Set transaction data
		$data['google_trans'] = array(
								'orderid'	=> $order->order_ref,
								'store'		=> $this->config->item('store_name'),
								'total'		=> number_format($order->total,2,'.',''),
								'tax'		=> number_format($order->order_vat,2,'.',''),
								'shipping'	=> $order->order_shipping,
								'city'		=> $order->billing_city,
								'state'		=> '',
								'country'	=> $order->billing_country,
								);
		
		foreach ($inventory_items as $item) {
		
			//if a product name exists, then update the stock level accordingly.
			if (!empty($item->product_name)) {

				//Format product options for display
				$product_options = str_replace('@',': ', $item->product_options);
				$product_options = str_replace('{', '',$product_options);
				$product_options = str_replace('}', ' | ',$product_options);
				$product_options = preg_replace('@ \|\ $@','',$product_options); //Remove the last pipeline symbol
				$product_options = trim($product_options);

				$data['google_inventory'][] = array(
										'orderid' => $order->order_ref,
										'sku'	  => $item->product_no,
										'name' 	  => $item->product_name,
										'variant' => $product_options,
										'price'	  => $item->product_price,
										'qty'	  => $item->product_qty,
									   ); 
			
			}
		
		}
		#------------------------------------------------------
		# End: Google Ecommerce code
		#------------------------------------------------------

		$data['order_number'] 	= $order->order_ref;
		$data['pages'] 			= $this->pages_model->getPagesList(); //Display site documents
		$data['categories'] 	= $this->category_model->getAllParents();	//Get parent categories
				
		$data['page_title'] 		= 'Thank you for your order';
		$data['meta_description'] 	= '';
		$data['meta_keywords'] 		= '';
		$data['meta_custom'] 		= '';
		$data['content'] 			= 'gateway/sagepay/success';

		//Load snippets
		$this->settings_model->snippets();
		
		$mybasket = $this->core->mybasket_summary();

		//Load the core templates
		$data['itemtotal'] 			= $mybasket->itemtotal;
		$data['baskettotal'] 		= $mybasket->baskettotal;
		$data['itemtotal_int'] 		= $mybasket->items;
		$data['baskettotal_exvat'] 	= $mybasket->baskettotal_exvat;
		$data['shopit:header']  	= $this->parser->parse('global/header', $data, true);
		$data['shopit:footer']  	= $this->parser->parse('global/footer', $data, true);
		$data['shopit:sidebar'] 	= $this->parser->parse('global/sidebar', $data, true);
		$data['shopit:content'] 	= $this->parser->parse('content/homepage', $data, true);
		$data['shopit:search']  	= $this->parser->parse('boxes/search-box', $data, true);
		$data['shopit:mybasket']  	= $this->parser->parse('boxes/basket', $data, true);
		$data['shopit:breadcrumb']  = "";
		$data['shopit:categories'] 	= $this->category_model->createNav();
		$data['shopit:pages']  		= $this->pages_model->createList();

		$this->parser->parse('global/cart',$data);
		
		//Remove user's session to clear their basket
		$this->session->sess_destroy();
		delete_cookie('basket');
	}
	
	
	#------------------------------------------------------
	# //!SagePay failure page
	#------------------------------------------------------	
	function spfailure() {
	
		global $data;

		$this->load->config('gateways/sagepay');
		$this->load->helper('sagepay');

		//Remove user's session to clear their basket
		$this->session->unset_userdata('store_session');

		// Now check we have a Crypt field passed to this page 
		$strCrypt=$_REQUEST["crypt"];
		if (strlen($strCrypt)==0) {
			ob_end_flush();
			redirect('');
			exit;
		}

		// Now decode the Crypt field and extract the results
		$strDecoded=simpleXor(Base64Decode($strCrypt),$this->config->item('strEncryptionPassword'));
		$values = getToken($strDecoded);

		// Split out the useful information into variables we can use
		$data['strStatus']		 = $values['Status'];
		$data['strStatusDetail'] = $values['StatusDetail'];
		$data['strVendorTxCode'] = $values["VendorTxCode"]; //This is the order_id
		$data['strVPSTxId']		 = $values["VPSTxId"];
		$data['strTxAuthNo']	 = $values["TxAuthNo"];
		$data['strAmount']		 = number_format($values["Amount"],2);
		$data['strAVSCV2']		 = $values["AVSCV2"];
		$data['strAddressResult']= $values["AddressResult"];
		$data['strPostCodeResult']= $values["PostCodeResult"];
		$data['strCV2Result'] 	 = $values["CV2Result"];
		$data['strGiftAid']		 = $values["GiftAid"];
		$data['str3DSecureStatus']= $values["3DSecureStatus"];
		$data['strCAVV']		 = $values["CAVV"];
		$data['strCardType']	 = $values["CardType"];
		$data['strLast4Digits']	 = $values["Last4Digits"];
		$data['strAddressStatus']= $values["AddressStatus"]; // PayPal transactions only
		$data['strPayerStatus']  =$values["PayerStatus"];     // PayPal transactions only

		// Determine the reason this transaction was unsuccessful
		if ($data['strStatus']=="NOTAUTHED")
			$data['strReason'] ="You payment was declined by the bank.  This could be due to insufficient funds, or incorrect card details.";
		else if ($data['strStatus']=="ABORT")
			$data['strReason']="You chose to Cancel your order on the payment pages.";
		else if ($data['strStatus']=="REJECTED") 
			$data['strReason']="Your order did not meet our minimum fraud screening requirements.";
		else if ($data['strStatus']=="INVALID" or strStatus=="MALFORMED")
			$data['strReason']="We could not process your order because we have been unable to register your transaction with our Payment Gateway.";
		else if ($data['strStatus']=="ERROR")
			$data['strReason']="We could not process your order because our Payment Gateway service was experiencing difficulties.";
		else
			$data['strReason']="The transaction process failed. Please contact us with the date and time of your order and we will investigate.";

		//Strip the random number from the VendorTxCode, to leave the order_id
		$order_id = strstr($values["VendorTxCode"], '-', TRUE);

		// Get the default status for 'completed'
		$status = $this->basket_model->getDefaultStatus('Failed');

		//Update database
		$transaction_data = $values['Status'] . ';' . $values['StatusDetail'] . ';' . $data['strAmount'] . ';';
		$this->basket_model->updateOrderStatus($order_id, $status->value, $status->type, $data['strVPSTxId'], $data['strAmount'], $transaction_data);		

		//Get order data - do this now to get the new order ref created above
		$order = $this->basket_model->getOrderDetails($order_id);
		$inventory_items = $this->basket_model->getInventory($order_id);

		//Set the parser tags data for notifications
		$tags['order'] = $order;
		$tags['items'] = $inventory_items;
		
		//Send email to customer depending upon the status change
		$this->basket_model->sendNotifications($status->id, $order->customer_email, $tags);							
				
		$data['order_number'] = $order->order_ref;
		$data['pages'] 	= $this->pages_model->getPagesList(); //Display site documents
		$data['categories'] = $this->category_model->getAllParents();	//Get parent categories

		$data['page_title'] = 'Sorry, there was an error processing your payment';
		$data['meta_description'] = '';
		$data['meta_keywords'] = '';
		$data['meta_custom'] = '';
		$data['content'] = 'gateway/sagepay/failure';

		//Load snippets
		$this->settings_model->snippets();

		$mybasket = $this->core->mybasket_summary();

		//Load the core templates
		$data['itemtotal'] 			= $mybasket->itemtotal;
		$data['baskettotal'] 		= $mybasket->baskettotal;
		$data['itemtotal_int'] 		= $mybasket->items;
		$data['baskettotal_exvat'] 	= $mybasket->baskettotal_exvat;
		$data['shopit:header']  	= $this->parser->parse('global/header', $data, true);
		$data['shopit:footer']  	= $this->parser->parse('global/footer', $data, true);
		$data['shopit:sidebar'] 	= $this->parser->parse('global/sidebar', $data, true);
		$data['shopit:content'] 	= $this->parser->parse('content/homepage', $data, true);
		$data['shopit:search']  	= $this->parser->parse('boxes/search-box', $data, true);
		$data['shopit:mybasket']  	= $this->parser->parse('boxes/basket', $data, true);
		$data['shopit:breadcrumb']  = "";
		$data['shopit:categories'] 	= $this->category_model->createNav();
		$data['shopit:pages']  		= $this->pages_model->createList();

		$this->parser->parse('global/cart',$data);

	}


	#------------------------------------------------------
	# //!PAYPAL IPN
	# - runs behind the scenes on paypal's server
	# - updates database
	# - $_POST['item_name'] is the order_id
	#------------------------------------------------------	
	function ppipn() {
		
		global $postdata,$info;

		$this->load->config('gateways/paypal');
		
		//posts transaction data using fsockopen. 
		function fsockPost($url,$data) { 
		
			global $postdata,$info;
			
			//Parse url 
			$web=parse_url($url); 
			
			$postdata = "cmd=_notify-validate";

			//build post string 
			foreach($data as $i=>$v) { 
			$postdata .= '&' . $i . "=" . urlencode($v); 
			}
			
			
			//Set the port number
			if($web[scheme] == "https") { $web[port]="443";  $ssl="ssl://"; } else { $web[port]="80"; }  
			
			//Create paypal connection
			$fp=@fsockopen($ssl . $web[host],$web[port],$errnum,$errstr,30); 
			
			//Error checking
			if(!$fp) { echo "$errnum: $errstr"; } 
			 
			//Post Data
			else { 
			 
			  fputs($fp, "POST $web[path] HTTP/1.1\r\n"); 
			  fputs($fp, "Host: $web[host]\r\n"); 
			  fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n"); 
			  fputs($fp, "Content-length: ".strlen($postdata)."\r\n"); 
			  fputs($fp, "Connection: close\r\n\r\n"); 
			  fputs($fp, $postdata . "\r\n\r\n"); 
			
			//loop through the response from the server 
			while(!feof($fp)) { $info[]=@fgets($fp, 1024); } 
			
			//close fp - we are done with it 
			fclose($fp); 
			
			//break up results into a string
			$info=implode(",",$info); 
			
			}
			
			return $info; 
		} 

		$result=fsockPost('https://www.paypal.com/cgi-bin/webscr',$_POST);

		//Check if order number has been passed and
		//if so, continue the processing.
		if (!empty($_POST['invoice'])) {

			// Get the order details
			$order = $vendor['order'] = $this->basket_model->getOrderDetails($_POST["invoice"]);

			// Get inventory data for this order
			$inventory_items = $this->basket_model->getInventory($_POST['invoice']);

			switch ($_POST['payment_status']) {
			
				case 'Completed';
				case 'Processed':

					// Get the default status for 'completed'
					$status = $this->basket_model->getDefaultStatus('Completed');
	
					//Update the stock levels
					//First get the inventory for this order
					//!!! item_name is the order_ref					
					foreach ($inventory_items as $item) {
										
						//if a product name exists, then update the stock level accordingly by passing it the basket qty's.
						if (!empty($item->product_name)):
							$this->basket_model->updateStockLevels($item->product_id, $item->product_qty);
						endif;
					
					}	

					// Update the coupon code counter
					$this->coupons->counter($order->session_id);
					break;
					
				case 'Failed':
					// Get the default status for 'failed'
					$status = $this->basket_model->getDefaultStatus('Failed');
					break;
	
				case 'Pending':
					// Get the default status for 'unprocessed'
					$status = $this->basket_model->getDefaultStatus('Unprocessed');
					break;
	
				case 'Refunded':
					// Get the default status for 'refunded'
					$status = $this->basket_model->getDefaultStatus('Refunded');
					break;
					
				default:
					// Get the default status for 'unprocessed'
					$status = $this->basket_model->getDefaultStatus('Unprocessed');
					break;
					
			}
			
			$transaction_data = $_POST['payment_type'] . ';' . $_POST['payment_status'] . ';' . $_POST['mc_gross'] . ';';
			$this->basket_model->updateOrderStatus($_POST['invoice'], $status->value, $status->type, $_POST['txn_id'], $_POST['mc_gross'], $transaction_data);

			//Set the parser tags data for notifications
			$tags['order'] = $order;
			$tags['items'] = $inventory_items;
			
			//Send email to customer depending upon the status change
			$this->basket_model->sendNotifications($status->id, $order->customer_email, $tags);							

			if ($_POST['payment_status'] == 'Completed' || $_POST['payment_status'] == 'Processed') {	
				//Add customer to campaign monitor subscription
				cmSubscribe($order->account_id, $order->pref_newsletter, $order->billing_firstname, $order->billing_surname, $order->customer_email);
				//End.
			}

		}	
			
	}
	

	#------------------------------------------------------
	# //!PayPal SUCCESS page
	#------------------------------------------------------	
	function ppsuccess() {

		global $data;

		$this->load->config('gateways/paypal');

		/**************************************************************
		* PAYPAL PDT - for Google Ecommerce Code					  *
		**************************************************************/
		$tx = $data['tx'] = $this->input->get('tx');

		// Init cURL
		$request = curl_init();
		
		// Set request options
		curl_setopt_array($request, array
		(
		  CURLOPT_URL 	=> $this->config->item('paypalURL'),
		  CURLOPT_POST 	=> TRUE,
		  CURLOPT_POSTFIELDS => http_build_query(array
		    (
		      'cmd' => '_notify-synch',
		      'tx' => $tx,
		      'at' => $this->config->item('paypalPDTToken'),
		    )),
		  CURLOPT_RETURNTRANSFER => 1,
		  CURLOPT_HEADER => FALSE,
		  CURLOPT_SSL_VERIFYHOST => 0,
		  CURLOPT_SSL_VERIFYPEER => 0,
		  //CURLOPT_CAINFO => 'cacert.pem',
		));
		
		// Execute request and get response and status code
		$response = curl_exec($request);
		$status   = curl_getinfo($request, CURLINFO_HTTP_CODE);
		
		// Close connection
		curl_close($request);

		if ($status == 200 AND strpos($response, 'SUCCESS') === 0) {
			
			//Clean up the response

			// Remove SUCCESS part (7 characters long)
			$response = substr($response, 7);
			
			// URL decode
			$response = urldecode($response);
			
			// Turn into associative array
			preg_match_all('/^([^=\s]++)=(.*+)/m', $response, $m, PREG_PATTERN_ORDER);
			$response = array_combine($m[1], $m[2]);
			
			// Fix character encoding if different from UTF-8 (in my case)
			if(isset($response['charset']) AND strtoupper($response['charset']) !== 'UTF-8')
			{
			  foreach($response as $key => &$value)
			  {
			    $value = mb_convert_encoding($value, 'UTF-8', $response['charset']);
			  }
			  $response['charset_original'] = $response['charset'];
			  $response['charset'] = 'UTF-8';
			}

			// Sort on keys for readability (handy when debugging)
			ksort($response);

			$order = $this->basket_model->getOrderDetails($response['invoice']);
			
		}

		#------------------------------------------------------
		# For Google Analytics Ecommerce code
		#------------------------------------------------------
		//Set transaction data
		$data['google_trans'] = array(
								'orderid'	=> $order->order_ref,
								'store'		=> $this->config->item('store_name'),
								'total'		=> number_format($order->total,2,'.',''),
								'tax'		=> number_format($order->order_vat,2,'.',''),
								'shipping'	=> $order->order_shipping,
								'city'		=> $order->billing_city,
								'state'		=> '',
								'country'	=> $order->billing_country,
								);

		//Get inventory
		$inventory_items = $this->basket_model->getInventory($order->order_id);
		
		foreach ($inventory_items as $item) {
		
			//if a product name exists, then update the stock level accordingly.
			if (!empty($item->product_name)) {

				//Format product options for display
				$product_options = str_replace('@',': ', $item->product_options);
				$product_options = str_replace('{', '', $product_options);
				$product_options = str_replace('}', ' | ', $product_options);
				$product_options = preg_replace('@ \|\ $@','', $product_options); //Remove the last pipeline symbol
				$product_options = trim($product_options);

				$data['google_inventory'][] = array(
										'orderid' => $order->order_ref,
										'sku'	  => $item->product_no,
										'name' 	  => $item->product_name,
										'variant' => $product_options,
										'price'	  => $item->product_price,
										'qty'	  => $item->product_qty,
									   ); 
			}
		
		}
		#------------------------------------------------------
		# End: Google Ecommerce code
		#------------------------------------------------------
		
		$data['pages'] 	= $this->pages_model->getPagesList(); //Display site documents
		$data['categories'] = $this->category_model->getAllParents();	//Get parent categories

		$data['page_title'] = 'Your order';
		$data['meta_description'] = '';
		$data['meta_keywords'] = '';
		$data['meta_custom'] = '';
		$data['content'] = 'gateway/paypal/success';

		//Load snippets
		$this->settings_model->snippets();

		$mybasket = $this->core->mybasket_summary();

		//Load the core templates
		$data['itemtotal'] 			= $mybasket->itemtotal;
		$data['baskettotal'] 		= $mybasket->baskettotal;
		$data['itemtotal_int'] 		= $mybasket->items;
		$data['baskettotal_exvat'] 	= $mybasket->baskettotal_exvat;
		$data['shopit:header']  	= $this->parser->parse('global/header', $data, true);
		$data['shopit:footer']  	= $this->parser->parse('global/footer', $data, true);
		$data['shopit:sidebar'] 	= $this->parser->parse('global/sidebar', $data, true);
		$data['shopit:content'] 	= $this->parser->parse('content/homepage', $data, true);
		$data['shopit:search']  	= $this->parser->parse('boxes/search-box', $data, true);
		$data['shopit:mybasket']  	= $this->parser->parse('boxes/basket', $data, true);
		$data['shopit:breadcrumb']  = "";
		$data['shopit:categories'] 	= $this->category_model->createNav();
		$data['shopit:pages']  		= $this->pages_model->createList();

		$this->parser->parse('global/cart',$data);
		
		//Remove user's session to clear their basket
		$this->session->sess_destroy();
		delete_cookie('basket');

	}


	#------------------------------------------------------
	# //!WorldPay RESPONSE page
	# - updates database
	# - deletes cookie
	# - sends email to vendor
	#------------------------------------------------------	
	function wpresponse() {
	
		global $data;

		$this->load->config('gateways/worldpay');

		$values = array(
				  	'Status'		=> $_POST['transStatus'], 		// transaction status: Y = authorised / C = cancelled
				  	'StatusDetail'	=> $_POST['rawAuthMessage'], 	// transaction message
				  	'Amount'		=> $_POST['amount'],			// total amount
				  	'VendorTxCode' 	=> $_POST['cartId'], 			// order_id
				  	'VPSTxId'		=> $_POST['transId'], 			// transaction_id
				  );

		if ($values['Status'] == 'Y'):
			// Get the default status for 'completed'
			$status = $this->basket_model->getDefaultStatus('Completed');
		else:
			// Get the default status for 'cancelled'
			$status = $this->basket_model->getDefaultStatus('Cancelled');
		endif;

		//Update order status/details in the database
		$transaction_data = $values['Status'] . ';' . $values['StatusDetail'] . ';' . $values['Amount'] . ';';
		$this->basket_model->updateOrderStatus($values['VendorTxCode'], $status->value, $status->type, $values['VPSTxId'], $values['Amount'], $transaction_data);		

		//First get the data for this order
		$inventory_items = $this->basket_model->getInventory($values["VendorTxCode"]);
		$order = $vendor['order'] = $this->basket_model->getOrderDetails($values["VendorTxCode"]);

		//Update the stock levels
		if ($values['Status'] == 'Y') {
							
			foreach ($inventory_items as $item) {
						
				//if a product name exists, then update the stock level accordingly.
				if (!empty($item->product_name)) {
					$this->basket_model->updateStockLevels($item->product_id, $item->product_qty);
				}
			
			}

			//Add customer to campaign monitor subscription
			cmSubscribe($order->account_id, $order->pref_newsletter, $order->billing_firstname, $order->billing_surname, $order->customer_email);
			//End.

			// Update the coupon code counter
			$this->coupons->counter($order->session_id);
			
		}
		
		//Set the parser tags data for notifications
		$tags['order'] = $order;
		$tags['items'] = $inventory_items;
		
		//Send email to customer depending upon the status change
		$this->basket_model->sendNotifications($status->id, $order->customer_email, $tags);							
				
		$data['pages'] 	= $this->pages_model->getPagesList(); //Display site documents
		$data['categories'] = $this->category_model->getAllParents();	//Get parent categories
		
		$data['meta_description'] = '';
		$data['meta_keywords'] = '';
		$data['meta_custom'] = '';
		
		if ($values['Status'] == 'Y'):
			$data['page_title'] = 'Thank you for your order';
			$content 			= 'gateway/worldpay/success';
		else:
			$data['page_title'] = 'Sorry, there was an error processing your payment';
			$content			= 'gateway/worldpay/failure';
		endif;

		//Load snippets
		$this->settings_model->snippets();

		$mybasket = $this->core->mybasket_summary();

		//Load the core templates
		$data['itemtotal'] 			= $mybasket->itemtotal;
		$data['baskettotal'] 		= $mybasket->baskettotal;
		$data['itemtotal_int'] 		= $mybasket->items;
		$data['baskettotal_exvat'] 	= $mybasket->baskettotal_exvat;
		$data['shopit:header']  	= $this->parser->parse('global/header', $data, true);
		$data['shopit:footer']  	= $this->parser->parse('global/footer', $data, true);
		$data['shopit:sidebar'] 	= $this->parser->parse('global/sidebar', $data, true);
		$data['shopit:content'] 	= $this->parser->parse('content/homepage', $data, true);
		$data['shopit:search']  	= $this->parser->parse('boxes/search-box', $data, true);
		$data['shopit:mybasket']  	= $this->parser->parse('boxes/basket', $data, true);
		$data['shopit:breadcrumb']  = "";
		$data['shopit:categories'] 	= $this->category_model->createNav();
		$data['shopit:pages']  		= $this->pages_model->createList();

		//Some additional short tags
		$data['WorldPayInstId'] = $this->config->item('WorldPayInstId');
		$data['WorldPayAssetsPath'] = '/i/' . $this->config->item('WorldPayInstId') . '/'; 
		
		$this->parser->parse($content, $data);
		
		//Clear user's basket
		$this->basket_model->emptyBasket($_POST['M_usersession']);
	}


	#------------------------------------------------------
	# //! Barclaycard epdq template
	# - Used to display the payment stuff
	#------------------------------------------------------
	function epdqtemplate() {
		$this->load->config('gateways/barclaycard');
		$data = array(
			'store_name' => $this->config->item('store_name'),
		);
		$this->parser->parse('gateway/barclaycard/epdq', $data);
	}

	
	#------------------------------------------------------
	# //!Barclaycard RESPONSE page - Runs in the background
	# - Utilises $_GET
	# - updates database
	# - deletes cookie
	# - sends email to vendor
	# - Statuses:
	# 		*5 - Authorised (ACCEPT)
	# 		*9 - Payment requested (ACCEPT)
	# 		*0 - Invalid or incomplete (DECLINE)
	# 		*2 - Authorised refused (DECLINE)
	# 		*1 - Cancelled by client (CANCEL) 
	# 		51 - Authorised waiting (offline payment - N/A)
	# 		91 - Payment processing (offline payment - N/A)
	# 		*92/52 - Authorisation not known (EXCEPTION)
	# 		*93 - Payment refused (DECLINE)
	# - A simple line of text needs to be output for this
	#   script to be run successfully.
	#------------------------------------------------------	
	function epdqresponse() {

		$this->load->config('gateways/barclaycard');

		// Create the SHA-OUT signature to ensure no values have been tampered with. 
		// Parameter names should be in uppercase and arranged alphabetically. Empty
		// values should not be included
		
		// Make all keys uppercase
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$parameters = array_change_key_case($_POST, CASE_UPPER);
		} else {
			$parameters = array_change_key_case($_GET, CASE_UPPER);
		}
		
		// Sort alphbetically
		ksort($parameters); 
		
		#echo "<pre>" . print_r($parameters, true) . "</pre>"; // For debugging

		foreach ($parameters as $param=>$value) {			
			
			// Ignore these parameters
			$ignore = array('SHASIGN', 'SESSIONID', 'OID');
			
			if (!in_array($param, $ignore) && $value != "") {
				// Append this param to the hash string
				$hash_string = $hash_string . "$param=".urldecode($value).$this->config->item('ePDQ_SHA1_Passphrase');
				// Use this unformatted copy for debugging
				$hash_string_unformatted = $hash_string_unformatted . "$param=$value".$this->config->item('ePDQ_SHA1_Passphrase')."\r\n";
			}
		
		}

		// Create the hash and uppercase it to match exactly
		$shasign = strtoupper(hash('SHA1', $hash_string));
		
		// If the signature's don't match stop here by showing the exception template.
		#if ($this->input->get('SHASIGN') === $shasign) {
			
			// Check the status and do the necessary
			switch ($parameters['STATUS']) {
				
				// ACCEPTED
				case 5;
				case 9:
					//First get the inventory data for this order
					$inventory_items = $this->basket_model->getInventory($parameters['OID']);
					
					// Get the default status for 'completed'
					$status = $this->basket_model->getDefaultStatus('Completed');

					// Update order status/details in the database - this will generate our new order_ref too
					$transaction_data = implode(';', $parameters);
					$this->basket_model->updateOrderStatus($parameters['OID'], $status->value, $status->type, $parameters['PAYID'], $parameters['AMOUNT'], $transaction_data);
					
					// Go through each inventory item and update the stock level
					foreach ($inventory_items as $item) {
						if (!empty($item->product_name)) {
							$this->basket_model->updateStockLevels($item->product_id, $item->product_qty);
						}
					}

					// Now Get order data (we do this here because of the 
					// new order_ref that was generated further above)
					$order = $this->basket_model->getOrderDetails($parameters['OID']);

					// Set the parser tags data for notifications
					$tags['order'] = $order;
					$tags['items'] = $inventory_items;
					
					// Send email to customer depending upon the status change
					$this->basket_model->sendNotifications($status->id, $order->customer_email, $tags);							
		
					// Add customer to campaign monitor subscription
					cmSubscribe($order->account_id, $order->pref_newsletter, $order->billing_firstname, $order->billing_surname, $order->customer_email);
					// End.

					// Update the coupon code counter
					$this->coupons->counter($order->session_id);
					
					// Clear user's basket
					$this->basket_model->emptyBasket($parameters['SESSIONID']);
					
					// Display a default message to the script
					echo "Order processed successfully.";
					break;
					
				// DECLINED - Mark as FAILED
				case 0;
				case 2;
				case 93:
					
					// Get the default status for 'completed'
					$status = $this->basket_model->getDefaultStatus('Failed');

					// Update order status/details in the database - this will generate our new order_ref too
					$transaction_data = implode(';', $parameters);
					$this->basket_model->updateOrderStatus($parameters['OID'], $status->value, $status->type, $parameters['PAYID'], $parameters['AMOUNT'], $transaction_data);

					// Now Get order data (we do this here because of the 
					// new order_ref that was generated further above)
					$order = $this->basket_model->getOrderDetails($parameters['OID']);

					// Set the parser tags data for notifications
					$tags['order'] = $order;
					$tags['items'] = $inventory_items;
					
					// Send email to customer depending upon the status change
					$this->basket_model->sendNotifications($status->id, $order->customer_email, $tags);							
		
					// Add customer to campaign monitor subscription
					cmSubscribe($order->account_id, $order->pref_newsletter, $order->billing_firstname, $order->billing_surname, $order->customer_email);
					// End.
					
					// Display a default message to the script
					echo "Payment was declined.";
					break;
					
				// CANCELLED
				case 1:
					// We don't need to do anything here except
					// display a default message to the script
					echo "Order cancelled by customer.";
					break;
					
				// EXCEPTION (Technical issues resulting unpredictable result) - Mark as PENDING
				case 52;
				case 92;
					//First get the inventory data for this order
					$inventory_items = $this->basket_model->getInventory($parameters['OID']);
					
					// Get the default status for 'pending'
					$status = $this->basket_model->getDefaultStatus('Pending');

					// Update order status/details in the database - this will generate our new order_ref too
					$transaction_data = implode(';', $parameters);
					$this->basket_model->updateOrderStatus($parameters['OID'], $status->value, $status->type, $parameters['PAYID'], $parameters['AMOUNT'], $transaction_data);
					
					// Go through each inventory item and update the stock level
					foreach ($inventory_items as $item) {
						if (!empty($item->product_name)) {
							$this->basket_model->updateStockLevels($item->product_id, $item->product_qty);
						}
					}

					// Now Get order data (we do this here because of the 
					// new order_ref that was generated further above)
					$order = $this->basket_model->getOrderDetails($parameters['OID']);

					// Set the parser tags data for notifications
					$tags['order'] = $order;
					$tags['items'] = $inventory_items;
					
					// Send email to customer depending upon the status change
					$this->basket_model->sendNotifications($status->id, $order->customer_email, $tags);							
		
					// Add customer to campaign monitor subscription
					cmSubscribe($order->account_id, $order->pref_newsletter, $order->billing_firstname, $order->billing_surname, $order->customer_email);
					// End.
				
					// Display a default message to the script
					echo "An error has occurred.";
					break;
				
			}
			
		#}
		
	}


	#------------------------------------------------------
	# Barclaycard confirmation page
	# - Should handle ACCEPT, DECLINE, EXCEPTION and 
	# 	CANCEL templates
	# - Statuses:
	# 		*5 - Authorised (ACCEPT)
	# 		*9 - Payment requested (ACCEPT)
	# 		*0 - Invalid or incomplete (DECLINE)
	# 		*2 - Authorised refused (DECLINE)
	# 		*1 - Cancelled by client (CANCEL) 
	# 		51 - Authorised waiting (offline payment - N/A)
	# 		91 - Payment processing (offline payment - N/A)
	# 		*92/52 - Authorisation not known (EXCEPTION)
	# 		*93 - Payment refused (EXCEPTION)
	#------------------------------------------------------
	function epdqpayment() {

		global $data;

		$this->load->config('gateways/barclaycard');

		// Create the SHA-OUT signature to ensure no values have been tampered with. 
		// Parameter names should be in uppercase and arranged alphabetically. Empty
		// values should not be included
		
		// Make all keys uppercase
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
			$parameters = array_change_key_case($_POST, CASE_UPPER);
		} else {
			$parameters = array_change_key_case($_GET, CASE_UPPER);
		}

		// Sort alphbetically
		ksort($parameters); 
		
		#echo "<pre>" . print_r($parameters, true) . "</pre>"; // For debugging

		foreach ($parameters as $param=>$value) {			
			
			// Ignore these parameters
			$ignore = array('SHASIGN', 'SESSIONID', 'OID');
			
			if (!in_array($param, $ignore) && $value != "") {				
				// Append this param to the hash string
				$hash_string = $hash_string . "$param=".urldecode($value).$this->config->item('ePDQ_SHA1_Passphrase');
				// Use this unformatted copy for debugging
				$hash_string_unformatted = $hash_string_unformatted . "$param=$value".$this->config->item('ePDQ_SHA1_Passphrase')."\r\n";
			}
		
		}

		// Create the hash and uppercase it to match exactly
		$shasign = strtoupper(hash('SHA1', $hash_string));
		
		// If the signature's don't match stop here by showing the exception template.
		/*
		if ($this->input->get('SHASIGN') !== $shasign) {
			
			#echo "<pre>Unable to verify SHA1 signature. Please contact your webmaster with this error.</pre>";
			$template 	= 'exception';
			$page_title = 'An Error Occurred';
			
			// Set some data to pass to the view
			$data['oid'] = $this->input->get('oid');
			
		} else {
		*/
			
			// Check the status and do the necessary
			switch ($parameters['STATUS']) {
				
				// ACCEPTED
				case 5;
				case 9:
					$template 	= 'completed';
					$page_title = 'Order Complete';

					// Now Get order data
					$order = $this->basket_model->getOrderDetails($parameters['OID']);
					
					// Set some data to pass to the view
					$data['amount']    = $parameters['AMOUNT'];
					$data['order_ref'] = $order->order_ref;

					#------------------------------------------------------
					# For Google Analytics Ecommerce code
					#------------------------------------------------------
					//Set transaction data
					$data['google_trans'] = array(
											'orderid'	=> $order->order_ref,
											'store'		=> $this->config->item('store_name'),
											'total'		=> number_format($order->total,2,'.',''),
											'tax'		=> number_format($order->order_vat,2,'.',''),
											'shipping'	=> $order->order_shipping,
											'city'		=> $order->billing_city,
											'state'		=> '',
											'country'	=> $order->billing_country,
											);
			
					//Get inventory
					$inventory_items = $this->basket_model->getInventory($parameters['OID']);
					
					foreach ($inventory_items as $item) {
					
						//if a product name exists, then update the stock level accordingly.
						if (!empty($item->product_name)) {
			
							//Format product options for display
							$product_options = str_replace('@',': ', $item->product_options);
							$product_options = str_replace('{', '', $product_options);
							$product_options = str_replace('}', ' | ', $product_options);
							$product_options = preg_replace('@ \|\ $@','', $product_options); //Remove the last pipeline symbol
							$product_options = trim($product_options);
			
							$data['google_inventory'][] = array(
								'orderid' => $order->order_ref,
								'sku'	  => $item->product_no,
								'name' 	  => $item->product_name,
								'variant' => $product_options,
								'price'	  => $item->product_price,
								'qty'	  => $item->product_qty,
							); 
						}
					
					}
					#------------------------------------------------------
					# End: Google Ecommerce code
					#------------------------------------------------------
					
					// Clear user's basket
					$this->basket_model->emptyBasket($parameters['SESSIONID']);

					//Remove user's session to clear their basket
					$this->session->sess_destroy();
					delete_cookie('basket');
					break;
					
				// DECLINED
				case 0;
				case 2;
				case 93:
					$template 	= 'declined';
					$page_title = 'Payment Declined';

					//Log the user out, but keep their basket intact
					$this->session->sess_destroy();
					break;
					
				// CANCELLED
				case 1:
					// We don't need to do anything here except 
					// display the correct template
					$template 	= 'cancelled';
					$page_title = 'Order Cancelled';

					//Log the user out
					$this->session->sess_destroy();
					break;
					
				// EXCEPTION (Technical issues resulting unpredictable result)
				case 52;
				case 92;
					$template 	 = 'exception';
					$page_title  = 'An Error Occurred';
					
					// Set some data to pass to the view
					$data['oid'] = $parameters['OID'];
					$data['payid'] = $parameters('PAYID');

					//Log the user out and clear their basket
					$this->session->sess_destroy();
					delete_cookie('basket');
					break;
				
			}

		#}
	
		// Set some tags here
		$data['page_title'] = $page_title;
		$data['meta_description'] = '';
		$data['meta_keywords'] = '';
		$data['meta_custom'] = '';

		// Load snippets
		$this->settings_model->snippets();

		$mybasket = $this->core->mybasket_summary();

		// Load the core templates
		$data['itemtotal'] 			= $mybasket->itemtotal;
		$data['baskettotal'] 		= $mybasket->baskettotal;
		$data['itemtotal_int'] 		= $mybasket->items;
		$data['baskettotal_exvat'] 	= $mybasket->baskettotal_exvat;
		$data['shopit:header']  	= $this->parser->parse('global/header', $data, true);
		$data['shopit:footer']  	= $this->parser->parse('global/footer', $data, true);
		$data['shopit:sidebar'] 	= $this->parser->parse('global/sidebar', $data, true);
		$data['shopit:content'] 	= $this->parser->parse('content/homepage', $data, true);
		$data['shopit:search']  	= $this->parser->parse('boxes/search-box', $data, true);
		$data['shopit:mybasket']  	= $this->parser->parse('boxes/basket', $data, true);
		$data['shopit:breadcrumb']  = "";
		$data['shopit:categories'] 	= $this->category_model->createNav();
		$data['shopit:pages']  		= $this->pages_model->createList();

		// Load the global template with all the data
		$data['content'] = "gateway/barclaycard/$template";
		$this->parser->parse('global/cart', $data);

	}

}