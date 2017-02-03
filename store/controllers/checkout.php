<?php
class Checkout extends CI_Controller {

	function Checkout() {
		parent::__construct();
		
		$this->load->model('settings_model');
		$this->settings_model->initConfig();

		$this->load->model('products_model');
		$this->load->model('category_model');
		$this->load->model('basket_model');
		$this->load->model('shipping_model');
		$this->load->model('pages_model');
		
		$this->load->library('encrypt');
		$this->load->library('form_validation');
		$this->load->helper('form');
		$this->load->helper('cookie');

		//Load modules		
		foreach ($this->config->item('modules') as $module) {
			if (library_exists($module)):
			$this->load->library($module);
			endif;
		}

	}

	#------------------------------------------------------
	# Checkout process
	#------------------------------------------------------
	function index() {

		// Check to see if the total value of the items is greater than 0.00. If it isn't then stop here and redirect back to the basket with an error message.
		// Update: This was fixed by the encryption code update in 3.1.3 as is no longer required
		#$shopit_basketcheck = base64_decode($_POST['amount']);
		#if ($shopit_basketcheck <= 0) {
		#	$this->session->set_flashdata('basket_notice', 'Oops! There was an error with your basket - please try checking out again. If the problem persists try re-adding the items to your basket or call us on <strong>' . $this->config->item('company_tel') . '</strong> to complete your purchase.');
		#	redirect('basket');
		#	break;
		#}
		
		global $data,$shipping_encrypted,$shipping_rulename;

		//Extend the basket cookie on each view
		$this->basket_model->extend_cookie();

		//Get the stored session_id from the existing cookie
		$basket_cookie = get_cookie('basket');

		$this->form_validation->set_message('required', ' ');
		$this->form_validation->set_message('valid_email', ' ');
		$this->form_validation->set_error_delimiters('error', '');
		
		$this->form_validation->set_rules('BillingFirstname', 'Billing Firstname', 'trim|required|max_length[35]');
		$this->form_validation->set_rules('BillingSurname', 'Billing Surname', 'trim|required|max_length[35]');
		$this->form_validation->set_rules('BillingCompany', 'Billing Company', 'trim|max_length[65]');
		$this->form_validation->set_rules('BillingAddress1', 'Billing Address', 'trim|required|max_length[65]');
		$this->form_validation->set_rules('BillingAddress2', 'Billing Address', 'trim|max_length[65]');
		$this->form_validation->set_rules('BillingCity', 'Billing City/Town', 'trim|required|max_length[35]');
		$this->form_validation->set_rules('BillingPostcode', 'Billing Postal Code', 'trim|required|max_length[15]');
		$this->form_validation->set_rules('Email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('Phone', 'Telephone', 'trim|required|max_length[20]');
		$this->form_validation->set_rules('DeliveryFirstname', 'Delivery Firstname', 'trim|required|max_length[35]');
		$this->form_validation->set_rules('DeliverySurname', 'Delivery Surname', 'trim|required|max_length[35]');
		$this->form_validation->set_rules('DeliveryCompany', 'Delivery Company', 'trim|max_length[65]');
		$this->form_validation->set_rules('DeliveryAddress1', 'Delivery Address', 'trim|required|max_length[65]');
		$this->form_validation->set_rules('DeliveryAddress2', 'Delivery Address', 'trim|max_length[65]');
		$this->form_validation->set_rules('DeliveryCity', 'Delivery City/Town', 'trim|required|max_length[35]');
		$this->form_validation->set_rules('DeliveryPostcode', 'Delivery Postal Code', 'trim|required|max_length[15]');
		$this->form_validation->set_rules('Password', 'Password', 'trim');
		$this->form_validation->set_rules('cPassword', 'Confirm Password', 'trim|matches[Password]');
		$this->form_validation->set_rules('Instructions', 'Instructions', 'trim');
		$this->form_validation->set_rules('AgreeTC', 'Terms and Conditions', 'trim|required');
		$this->form_validation->set_rules('pref_newsletter');
			
		if ($this->form_validation->run() == FALSE)
		{
			//Include core basket code
			include_once($_SERVER['DOCUMENT_ROOT'].'/store/controllers/includes/basket.php');
		}
		else
		{

		$BillingCountry  = $this->shipping_model->getISOCountry($_POST['BillingCountry']);
		$DeliveryCountry = $this->shipping_model->getISOCountry($_POST['DeliveryCountry']);
		
		//!!! Create user's account
		if (library_exists('myaccount')):
			$account_id = $this->myaccount->create_account();
		else:
			$account_id = 0;
		endif;
		
		switch ($_POST['gateway']) {
			
			#------------------------------------------------------
			# //!SagePay payment process
			# - Data is added to database and is encrypted for 
			# - VSP Form submission to SagePay.
			# - this is simply an auto-submit form which contains
			# - the encrypted data from the basket details
			#------------------------------------------------------	
			case 'SagePay':

				$this->load->config('gateways/sagepay');
				$this->load->helper('sagepay');
				
				//Add order details to database BEFORE being sent to payment gateway. Account_id is the customer's id.
				$this->basket_model->createOrder($account_id);

				//Retrieve unique order_id generated in above step for the VendorTXCode...
				$order_id = $this->basket_model->getOrderID();
				
				//We need to append a random number to the order id because SagePay doesn't like it
				//if it's been passed before. We'll separate this with a hyphen.
				
				$order_id = $order_id . '-' . rand(1111, 9999);
				
				$amount 	= base64_decode($_POST['amount']);
				$discount	= base64_decode($_POST['discount']); //Does not need to be included in total calculation
				$shipping 	= base64_decode($_POST['shipping']);
				$vat 		= base64_decode($_POST['vat']);
				$totalamount = number_format($amount + $shipping + $vat,2,'.','');
				
				#print $amount . ' + ' . $shipping . ' + ' . $vat . ' = ' . $totalamount;

				// Now to build the VSP Form crypt field.  For more details see the VSP Form Protocol 2.23 
				$data['strPost'] = "VendorTxCode=" . $order_id; //This is the link to the order!			

				// Optional: If you are a Protx Partner and wish to flag the transactions with your unique partner id, it should be passed here
				if (strlen($strPartnerID) > 0) {
				    $data['strPost'] = $data['strPost'] . "&ReferrerID=" . $strPartnerID;
				}

				$data['strPost'] = $data['strPost'] . "&Amount=".$totalamount;	// . number_format($sngTotal,2); // Formatted to 2 decimal places with leading digit
				$data['strPost'] = $data['strPost'] . "&Currency=" . $this->config->item('strCurrency');
				// Up to 100 chars of free format description
				$data['strPost'] = $data['strPost'] . "&Description=Your " . $this->config->item('store_name') ." order";

				/* The SuccessURL is the page to which VSP Form returns the customer if the transaction is successful 
				** You can change this for each transaction, perhaps passing a session ID or state flag if you wish */
				$data['strPost'] = $data['strPost'] . "&SuccessURL=" . $this->config->item('strYourSiteFQDN') . $this->config->item('strVirtualDir') . $this->config->item('strSuccessURL');
				
				/* The FailureURL is the page to which VSP Form returns the customer if the transaction is unsuccessful
				** You can change this for each transaction, perhaps passing a session ID or state flag if you wish */
				$data['strPost'] = $data['strPost'] . "&FailureURL=" . $this->config->item('strYourSiteFQDN') . $this->config->item('strVirtualDir') . $this->config->item('strFailureURL');
				
				// This is an Optional setting. Here we are just using the Billing names given.
				$data['strPost'] = $data['strPost'] . "&CustomerName=" . $this->security->xss_clean($_POST['BillingFirstname']) . " " . $this->security->xss_clean($_POST['BillingSurname']);

				/* Email settings:
				** Flag 'SendEMail' is an Optional setting. 
				** 0 = Do not send either customer or vendor e-mails, 
				** 1 = Send customer and vendor e-mails if address(es) are provided(DEFAULT). 
				** 2 = Send Vendor Email but not Customer Email. If you do not supply this field, 1 is assumed and e-mails are sent if addresses are provided. **/
				
				if ($this->config->item('bSendEMail') == 0) {
				    $data['strPost'] = $data['strPost'] . "&SendEMail=0";
				}
				else {
				    
				    if ($this->config->item('bSendEMail') == 1) {
				    	$data['strPost'] = $data['strPost'] . "&SendEMail=1";
				    } else {
				    	$data['strPost'] = $data['strPost'] . "&SendEMail=2";
				    }
				    
				    if (strlen($strCustomerEMail) > 0) {
				        $data['strPost'] = $data['strPost'] . "&CustomerEMail=" . $strCustomerEMail;  // This is an Optional setting
				    }
				    
				    if (($this->config->item('strVendorEMail') <> "[your e-mail address]") && ($this->config->item('strVendorEMail') <> "")) {
					    $data['strPost'] = $data['strPost'] . "&VendorEMail=" . $this->config->item('strVendorEMail');  // This is an Optional setting
					}
				
				    // You can specify any custom message to send to your customers in their confirmation e-mail here
				    // The field can contain HTML if you wish, and be different for each order.  This field is optional
				    $data['strPost'] = $data['strPost'] . "&eMailMessage=Thank you for your order.";
				}


				// Billing Details:
				$data['strPost'] = $data['strPost'] . "&BillingFirstnames=" . $this->security->xss_clean($_POST['BillingFirstname']);
				$data['strPost'] = $data['strPost'] . "&BillingSurname=" . $this->security->xss_clean($_POST['BillingSurname']);
				$data['strPost'] = $data['strPost'] . "&BillingAddress1=" . $this->security->xss_clean($_POST['BillingAddress1']);
				$data['strPost'] = $data['strPost'] . "&BillingAddress2=" . $this->security->xss_clean($_POST['BillingAddress2']);
				$data['strPost'] = $data['strPost'] . "&BillingCity=" . $this->security->xss_clean($_POST['BillingCity']);
				$data['strPost'] = $data['strPost'] . "&BillingPostCode=" . $this->security->xss_clean($_POST['BillingPostcode']);
				$data['strPost'] = $data['strPost'] . "&BillingCountry=" . $BillingCountry;
				$data['strPost'] = $data['strPost'] . "&BillingPhone=" . $this->security->xss_clean($_POST['Phone']);
				$data['strPost'] = $data['strPost'] . "&CustomerEmail=" . $this->security->xss_clean($_POST['Email']);
				
				// Delivery Details:
				$data['strPost'] = $data['strPost'] . "&DeliveryFirstnames=" . $this->security->xss_clean($_POST['DeliveryFirstname']);
				$data['strPost'] = $data['strPost'] . "&DeliverySurname=" . $this->security->xss_clean($_POST['DeliverySurname']);
				$data['strPost'] = $data['strPost'] . "&DeliveryAddress1=" . $this->security->xss_clean($_POST['DeliveryAddress1']);
				$data['strPost'] = $data['strPost'] . "&DeliveryAddress2=" . $this->security->xss_clean($_POST['DeliveryAddress2']);
				$data['strPost'] = $data['strPost'] . "&DeliveryCity=" . $this->security->xss_clean($_POST['DeliveryCity']);
				$data['strPost'] = $data['strPost'] . "&DeliveryPostCode=" . $this->security->xss_clean($_POST['DeliveryPostcode']);
				$data['strPost'] = $data['strPost'] . "&DeliveryCountry=" . $DeliveryCountry;
				
				$strBasket .= '1: ';
				$strBasket.="Your basket :1:".$totalamount.":::".$totalamount;
				$data['strPost'] = $data['strPost'] . "&Basket=" . $strBasket; // As created above 
				
				// For charities registered for Gift Aid, set to 1 to display the Gift Aid check box on the payment pages
				$data['strPost'] = $data['strPost'] . "&AllowGiftAid=0";
					
				/* Allow fine control over AVS/CV2 checks and rules by changing this value. 0 is Default 
				** It can be changed dynamically, per transaction, if you wish.  See the VSP Server Protocol document */
				if ($this->config->item('strTransactionType') !== "AUTHENTICATE") {
					$data['strPost'] = $data['strPost'] . "&ApplyAVSCV2=0";
				}
					
				/* Allow fine control over 3D-Secure checks and rules by changing this value. 0 is Default 
				** It can be changed dynamically, per transaction, if you wish.  See the VSP Server Protocol document */
				$data['strPost']=$data['strPost'] . "&Apply3DSecure=0";
				// Encrypt the plaintext string for inclusion in the hidden field
				$data['strCrypt'] = base64Encode(SimpleXor($data['strPost'],$this->config->item('strEncryptionPassword')));
				
				$data['content'] = 'gateway/sagepay/go';
				$data['gateway'] = 'SagePay';
				$this->parser->parse('gateway/process', $data);
				
				break;
			

			#------------------------------------------------------
			# //!PayPal payment process
			# - Data is added to database and is encrypted for 
			# - Form submission to PayPal.
			# - this is simply an auto-submit form which contains
			# - the encrypted data from the basket details
			#------------------------------------------------------	
			case 'PayPal':

				$this->load->config('gateways/paypal');

				//Add order details to database BEFORE being sent to payment gateway. Account_id is the customer's id.
				$this->basket_model->createOrder($account_id);

				//Retrieve unique order_ref generated in above step for the VendorTXCode...
				$order_id = $this->basket_model->getOrderID();

				// Get the order inventory so we can pass it to PayPal
				$inventory = $this->basket_model->getInventory($order_id);
				foreach($inventory as $item) {
					
					$x++;
					
					$data['items'][$x] = array(
						"label_item_name" 	=> "item_name_$x",
						"label_item_number" => "item_number_$x",
						"label_qty"		  	=> "quantity_$x",
						"label_amount"	  	=> "amount_$x",
						"item_name" 	  	=> $item->product_name,
						"item_number"		=> $item->product_code,
						"item_qty"		  	=> $item->product_qty,
						"item_amount" 	  	=> number_format($item->product_price, 2, '.', ''),
					);
					
				}

				$amount 			= base64_decode($_POST['amount']);
				$discount			= base64_decode($_POST['discount']); //Does not need to be included in total calculation
				$data['shipping'] 	= base64_decode($_POST['shipping']);
				$data['tax'] 		= base64_decode($_POST['vat']);
				$totalamount 		= number_format($amount + $data['shipping'] + $data['tax'], 2, '.', '');

				#print $amount . ' + ' . $shipping . ' + ' . $vat . ' = ' . $totalamount;
				
				#$data['total']   = $totalamount; // We don't need to send this
				$data['orderid'] = $order_id;

				$data['paypal_url']	   = $this->config->item('paypalURL');
				$data['notify_url']    = site_url('payment/ppipn'); // http://example.co.uk/payment/ppipn/
				$data['return_url']    = site_url('payment/ppsuccess');
				$data['business'] 	   = $this->config->item('paypalEmail');
				$data['currency_code'] = $this->config->item('paypalCurrencyCode');

				$data['content'] = 'gateway/paypal/go';
				$data['gateway'] = 'PayPal';
				$this->parser->parse('gateway/process', $data);
				break;

			#------------------------------------------------------
			# //!WorldPay payment process
			# - Data is added to database and is encrypted for 
			# - Form submission to PayPal.
			# - this is simply an auto-submit form which contains
			# - the encrypted data from the basket details
			#------------------------------------------------------	
			case 'WorldPay':

				$this->load->config('gateways/worldpay');

				//Add order details to database BEFORE being sent to payment gateway. Account_id is the customer's id.
				$this->basket_model->createOrder($account_id);

				//Retrieve unique order_id generated in above step for the VendorTXCode...
				$order_id = $this->basket_model->getOrderID();

				//Get billing details from order so we can pass to WorldPay
				$order = $this->basket_model->getOrderDetails($order_id);

				$data = array(
							'name'		=> $order->billing_title . ' ' . $order->billing_firstname . ' ' . $order->billing_surname,
							'address'	=> $order->billing_address1 . "&#10" . $order->billing_address2 . "&#10" . $order->billing_city,
							'postcode'	=> $order->billing_postcode,
							'country'	=> $BillingCountry,
							'email'		=> $order->customer_email,
							'tel'		=> $order->customer_phone,
						);

				$amount 	= base64_decode($_POST['amount']);
				$discount	= base64_decode($_POST['discount']); //Does not need to be included in total calculation
				$shipping 	= base64_decode($_POST['shipping']);
				$vat 		= base64_decode($_POST['vat']);
				$totalamount = number_format($amount + $shipping + $vat,2,'.','');
				
				$data['total'] 	 = $totalamount;
				$data['orderid'] = $order_id;
				$data['MC_callback'] = site_url('payment/wpresponse');

				$data['content'] = 'gateway/worldpay/go';
				$data['gateway'] = 'WorldPay';
				$this->parser->parse('gateway/process', $data);
				
				//We're done with the session now, so for security and
				//because WorldPay can't access it, we'll destroy it here
				$this->session->sess_destroy();
				break;

			#------------------------------------------------------
			# //!Barclaycard payment process
			#------------------------------------------------------
			case 'Barclaycard':

				$this->load->config('gateways/barclaycard');

				//Add order details to database BEFORE being sent to payment gateway. Account_id is the customer's id.
				$this->basket_model->createOrder($account_id);

				//Retrieve unique order_id generated in above step for our use
				$order_id = $this->basket_model->getOrderID();

				//Get billing details from order so we can pass to Barclaycard
				$order = $this->basket_model->getOrderDetails($order_id);

				//Calculate the total amount
				$amount 	 = base64_decode($this->input->post('amount'));
				$discount	 = base64_decode($this->input->post('discount')); //Does not need to be included in total calculation
				$shipping 	 = base64_decode($this->input->post('shipping'));
				$vat 		 = base64_decode($this->input->post('vat'));
				$totalamount = number_format($amount + $shipping + $vat, 2, '.', '');

				//Data to send to the template. ($hash_data is used for the sha1).
				$data = $hash_data = array(
					'ORDERID'		 => time(), //Temporary order id
					'AMOUNT'		 => $totalamount * 100, //Remove the decimal from the amount
					'COM'			 => 'Your Order',
					'CN'			 => $order->billing_firstname . ' ' . $order->billing_surname,
					'EMAIL'			 => $order->customer_email,
					'OWNERADDRESS'	 => implode(',', array($order->billing_address1, $order->billing_address2)),
					'OWNERTOWN'		 => $order->billing_city,
					'OWNERZIP'		 => $order->billing_postcode,
					'OWNERCTY'		 => $BillingCountry,
					'OWNERTELNO'	 => str_replace(' ', '', $order->customer_phone),
					'PSPID'			 => $this->config->item('ePDQ_PSPID'),
					'CURRENCY'		 => $this->config->item('ePDQ_Currency'),
					'LANGUAGE'		 => $this->config->item('ePDQ_Language'),
					'COMPLUS'		 => $basket_cookie,
					'PARAMPLUS'		 => "sessionid=$basket_cookie&oid=$order_id",
					'ACCEPTURL'		 => site_url('payment/epdqpayment/success'),
					'DECLINEURL'	 => site_url('payment/epdqpayment/declined'),
					'CANCELURL'		 => site_url('payment/epdqpayment/cancelled'),
					'EXCEPTIONURL'   => site_url('payment/epdqpayment/exception'),
					// Static Template Settings
					'TITLE'			 => $this->config->item('store_name'),
					'BGCOLOR'		 => $this->config->item('ePDQ_Template_BGCOLOR'),
					'TXTCOLOR'		 => $this->config->item('ePDQ_Template_TXTCOLOR'),
					'TBLBGCOLOR'	 => $this->config->item('ePDQ_Template_TBLBGCOLOR'),
					'TBLTXTCOLOR'	 => $this->config->item('ePDQ_Template_TBLTXTCOLOR'),
					'BUTTONBGCOLOR'	 => $this->config->item('ePDQ_Template_BUTTONBGCOLOR'),
					'BUTTONTXTCOLOR' => $this->config->item('ePDQ_Template_BUTTONTXTCOLOR'),
					'FONTTYPE'		 => $this->config->item('ePDQ_Template_FONTTYPE'),
				);

				// Dynamic Template - if it's installed
				if ($this->config->item('ePDQ_DynamicTemplateEnabled') == TRUE) {
					$data['TP'] = $hash_data['TP'] = site_url('payment/epdqtemplate.html');
					$data['TP_HTMLTAG'] = '<input type="hidden" name="TP" value="'.$data['TP'] .'" />';
				} else {
					$data['TP_HTMLTAG'] = '';
				}

				//Sort array alphabetically
				ksort($hash_data);

				//Create the SHA1 signature - parameter names should be 
				//in uppercase and arranged alphabetically
				foreach ($hash_data as $param=>$value) {
					$hash_string = $hash_string . "$param=$value".$this->config->item('ePDQ_SHA1_Passphrase');
					$hash_string_formatted = $hash_string_formatted . "$param=$value".$this->config->item('ePDQ_SHA1_Passphrase')."\r\n";
				}

				$data['SHASIGN'] = hash('SHA1', $hash_string);

				#echo "<pre>$hash_string_formatted</pre>"; // For debugging

				//Load the template
				$data['content'] = 'gateway/barclaycard/go';
				$data['gateway'] = 'Barclaycard';
				$this->parser->parse('gateway/process', $data);
				break;

			//No gateway chosen
			default:
				redirect('/store/basket');
				break;

		} //end switch statement
	
		} //end validation if
	}

}