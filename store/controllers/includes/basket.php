<?php
#------------------------------------------------------
# Basket script, used in:
# - store.php [basket()]
# - checkout.php
#------------------------------------------------------
$data['page_title'] = 'My Basket';
$data['meta_description'] = '';
$data['meta_keywords'] = '';
$data['meta_custom'] = '';

$data['pages'] = $this->pages_model->getPagesList(); 			//Display site documents
$data['categories'] = $this->category_model->getAllParents();	//Get parent categories

//Extend the basket cookie on each view
$this->basket_model->extend_cookie();

$mybasket = $this->core->mybasket_summary();
$data['itemtotal']			= $mybasket->itemtotal;
$data['baskettotal']		= $mybasket->baskettotal;
$data['itemtotal_int']		= $mybasket->items;
$data['baskettotal_exvat'] 	= $mybasket->baskettotal_exvat;

//BEGIN module: MyAccount - Load it here so we can use it later to default the shipping select
if (library_exists('myaccount') && $this->myaccount->user_logged_in() ) {
	$data['shopit:myaccount'] = "<h2>Welcome back " . $this->myaccount->get_info('firstname') . "! Check your details below before proceeding to checkout...</h2>\n";
} else {
	$data['shopit:myaccount'] = "<h2>Ready to checkout? Enter your details below...</h2>";
}

if (library_exists('myaccount') && $this->myaccount->user_logged_in()!=TRUE) {
	$data['shopit:myaccount:login'] = $this->myaccount->display_login_box();
} else {
	$data['shopit:myaccount:login'] = "";
}

//My account customer info. Check module exists...
if (library_exists('myaccount')) {
	$data['myacc_title'] 		= $this->myaccount->get_info('title');
	$data['myacc_firstname']	= $this->myaccount->get_info('firstname');
	$data['myacc_surname']		= $this->myaccount->get_info('surname');
	$data['myacc_company']		= $this->myaccount->get_info('company');
	$data['myacc_address1']		= $this->myaccount->get_info('address1');
	$data['myacc_address2']		= $this->myaccount->get_info('address2');
	$data['myacc_city']			= $this->myaccount->get_info('city');
	$data['myacc_postcode']		= $this->myaccount->get_info('postcode');
	$data['myacc_country']		= $this->myaccount->get_info('country');
	$data['myacc_user']			= $this->myaccount->get_info('user');
	$data['myacc_phone']		= $this->myaccount->get_info('phone');
}
//END module: MyAccount
switch($this->uri->segment(2)){
	
	case 'additem':
		$this->basket_model->addToBasket($_POST['product_id'],$_POST['qty'],$_POST['product_option']);
		if (!empty($_POST['redirect_url'])){
			$this->session->set_flashdata('basket_notice', 'The item has been added to your basket. <a href="'.site_url('basket').'">View basket</a>');
			redirect($_POST['redirect_url']);
		} else {
			redirect(site_url('basket'));
		}
		break;

	//Add all items to basket
	case 'additems':
		
		//Loop through the array and add to basket
		//format of array should be
		// item[n][product_id] = 1, item[n][qty] = 1, etc.
		foreach ($_POST['item'] as $item) {	
			if ($item['qty'] > 0) {
				$this->basket_model->addToBasket($item['product_id'], $item['qty'], $item['product_option']);
				$product_added = true;
			}
		}

		//Redirect to the appropriate page
		if (!empty($_POST['redirect_url'])){
			if ($product_added) {
				$this->session->set_flashdata('basket_notice', 'The items have been added to your basket. <a href="'.site_url('basket').'">View basket</a>');
			}
			redirect($_POST['redirect_url']);
		} else {
			redirect(site_url('basket'));
		}
		break;

	case 'update':
		//Check basket array and update qtys
		while (list($product_id_key,$product_id)=each($_POST['product_id']) and 
			  list($product_qty_key,$product_qty)=each($_POST['qty']) and
			  list($basket_id_key,$basket_id)=each($_POST['basket_id'])
			  )
		{
			if ($product_qty == 0):
				//Remove item when qty 0 is selected
				$this->basket_model->removeItem($basket_id);
			else:
				$this->basket_model->updateBasket($basket_id,$product_qty);
			endif;
		}
		
		redirect(site_url('basket'));	
		break;
	
	case 'remove':
		$this->basket_model->removeItem($this->uri->segment(3));
		redirect(site_url('basket'));	
		break;			

}

$products  = $this->basket_model->getBasket();

//If products in basket then...
if (!empty($products)):
	
	$z = 0;

	// Set the template for this page (basket.php or checkout.php)
	$checkout_template_file   = $_SERVER['DOCUMENT_ROOT'].'/store/views/cart/checkout.php';
	$checkout_template_exists = (file_exists($checkout_template_file)) ? TRUE : FALSE;

	if ($this->uri->segment(1) == 'checkout' and $checkout_template_exists) {
		$data['content'] = 'cart/checkout';
	} else {
		$data['content'] = 'cart/basket';
	}

	//Manipulate the data retrieved from the database previously
	//and create a new array to pass through template parser
	foreach ($products as $item){
	
		$z++;

		if($item->product_saleprice > 0):
			$product_price = $item->product_saleprice;
		else:
			$product_price = $item->product_price;
		endif;

		$total_price  = $total_price + ($product_price * $item->product_qty);
		$total_weight = $total_weight + ($item->product_weight * $item->product_qty);
		$sub_total    = $sub_total + ($product_price * $item->product_qty);
				
		if($this->config->item('outofstock_purchases') == 'false'):
			if ($item->stock_level >= $this->config->item('stock_purchaselimit')):
				$qty_limit = $this->config->item('stock_purchaselimit');
			else:
				$qty_limit = $item->stock_level;
			endif;
			
			if($item->stock_level > 0):					
				$data['qty_select'] = '<select name="qty[]" class="basket-qty">';
				$data['qty_select'] .= '<option value="0">0 (Remove)</option>';
				for($i=1;$i<=$qty_limit;$i++){
					
					if ($i == $item->product_qty):
						$selected = ' selected="selected"';
					else:
						$selected = '';
					endif;

					$data['qty_select'] .= '<option value="'.$i.'"'.$selected.'>'.$i.'</option>';
				}
				$data['qty_select'] .= '</select>';
			else:
				$data['qty_select'] = '';
			endif;
			
		else:
			$data['qty_select']  	= '<input type="text" name="qty[]" class="basket-qty" value="'.$item->product_qty.'" size="2"  maxlength="4" />';
		endif;
		//End qty options

		//Format product options for display
		$product_opt_array = array();
		$p=0;

		if (!empty($item->product_options)) {
			//Remove the initial curly bracket per option
			$product_options = str_replace('{', '',$item->product_options);
			
			//Creates an array in the form Size@Medium, Color@Black, etc
			$product_options = explode('}', $product_options);
			
			//Cleanse the array of any empty values
			$product_options = array_filter($product_options);
			
			$product_option_count = count($product_options);
			
			//Now loop through each creating the tags as we go
			foreach ($product_options as $option) {
				
				$p++;
				
				$option_delimiter = ($p < $product_option_count) ? "," : "";
				
				$options = explode('@', $option);
				
				$product_opt_array[] = array(
					'option_no'			=> $p,
					'option_label' 		=> trim($options[0]),
					'option_value' 		=> trim($options[1]),
					'option_delimiter' 	=> $option_delimiter,
				);
				
			}
		}
		
		$product_options = $product_options_prefix . $product_options;

		$product_thumbnail_size = ($this->config->item('gallery_thumb') != '') ? $this->config->item('gallery_thumb') : 35;

		$data['basket'][$z] = array(
			'basket_id'					=> $item->basket_id,
			'product_id' 				=> $item->product_id,
			'product_name' 				=> $item->product_name,
			'product_no'				=> $item->product_no,
			'product_price' 			=> money($product_price),
			'product_price_exvat' 		=> money($product_price, true, true, false),
			'product_saleprice'			=> money($item->product_saleprice),
			'product_saleprice_exvat' 	=> money($item->product_saleprice, true, true, false),
#			'product_option'			=> $product_options,
			'product_options'			=> $product_opt_array,
			'product_qty'				=> $item->product_qty,
			'product_qty_select' 		=> $data['qty_select'],
			'product_linetotal' 		=> money($product_price * $item->product_qty, true, true, false),
			'product_image'				=> get_image($item->parent_id, $product_thumbnail_size, $product_thumbnail_size, '', $ssl),
			'product_image_link'		=> get_image($item->parent_id, $product_thumbnail_size, $product_thumbnail_size, '', $ssl, FALSE),
			'parent_id'					=> $item->parent_id, //Identical to product_id when this is NOT a variation
			'parent_name'				=> $item->parent_name,
			'parent_slug'				=> $item->parent_slug,
			'product_url'				=> site_url(get_product_slug($item->parent_id)),
			'product_remove_link'		=> site_url("basket/remove/$item->basket_id"),
			'product_offer'				=> $product_advanced_offers,
			'product_stock_level'		=> $item->stock_level,
		);
		
		// Put all the cat_ids into an array & string to check shipping rules with
		$cat_ids[] = $this->products_model->getItem($item->parent_id)->cat_id;
		$c['cat_ids'] = implode(',', $cat_ids);
		$data['cat_ids'] = base64_encode($c['cat_ids']);
		
	}

	// Use the customers country if they are logged in, otherwise set as UK
	$user_country_default = "United Kingdom";
	if (library_exists('myaccount')) {
		$user_country_default = ($this->myaccount->get_info('country') != '') ? $this->myaccount->get_info('country') : $user_country_default;
	}

	// Is this country VAT exempt?
	$country_vat_exempt_status = $this->shipping_model->getCountryVATStatus($user_country_default); // 0 (no) or 1 (yes)

	// Get list of countries and set the default
	$countries = $this->basket_model->getAllCountries();
	
	foreach ($countries as $country) {
		
		if($country->country_name == $user_country_default):
			$default_country = ' selected="selected"';
			$home_country 	 = $country->country_name;
		else:
			$default_country = '';
		endif;
		
		$data['countries'][] = array(
							   	'country_name' => $country->country_name,
							   	'is_default'   => $default_country,
							   ); 
		

	}

	$shipping_countries = $countries;
	
	foreach ($shipping_countries as $shipping_country) {
		
		if($shipping_country->country_name == $user_country_default):
			$default_shipping_country = ' selected="selected"';
			$home_country = $shipping_country->country_name;
		else:
			$default_shipping_country = '';
		endif;
		
		$data['shipping_countries'][] = array(
							   	'shipping_country_name'     => $shipping_country->country_name,
							   	'enc_shipping_country_name' => str_replace(' ','_',$shipping_country->country_name),
							   	'is_shipping_default'       => $default_shipping_country,
							   ); 
							   
		$data['shipping_calc'][] = array(
							   	'shipping_country_name'     => $shipping_country->country_name,
							   	'enc_shipping_country_name' => str_replace(' ','_',$shipping_country->country_name),
							   	'is_shipping_default'       => $default_shipping_country,
								);
		

	}


	//!!! Calculate shipping cost based on rules
	$shippingrule = $this->shipping_model->getShippingRules($home_country); //defaults to home country
	
	if ($shippingrule > 0):

	$operations = array( 
		'less than' => function($x, $y, $z){ return $x <= $y; },
		'more than' => function($x, $y, $z){ return $x >= $y; },
		'equal to'  => function($x, $y, $z){ return $x == $y; },
		'between'   => function($x, $y, $z){ return $x >= $y && $x <= $z; }
	);

	$operation = $shippingcost->operation;

	
	foreach ($shippingrule as $shippingcost) {

		//!!! Check criteria for category value
		// This takes priority, so no other rules should be displayed
		if (in_array($shippingcost->operation, $cat_ids)) {
			
			if ($shippingcost->criteria == 'category') {
			// if cat_id is in the cat_ids array
				shipping_values($shippingcost, $delivery_markup);
			}
			//Stop the foreach loop
			break;

		} else {
		
			//!!! Check criteria for total value
			if ($shippingcost->criteria == 'total'):

				// if the chosen operation exists check to see if the operation is valid
				if(array_key_exists($operation, $operations)) {
					$operation_valid = $operations[$operation]($total_price, $shippingcost->value, $shippingcost->value2);

					if($operation_valid) {
						shipping_values($shippingcost);
					}
				} else {
					shipping_values();
				}
								
			//!!! Check criteria for weight value
			elseif ($shippingcost->criteria == 'weight'):

				if(array_key_exists($operation, $operations)) {
					$operation_valid = $operations[$operation]($total_weight, $shippingcost->value, $shippingcost->value2);

					if($operation_valid) {
						shipping_values($shippingcost);
					}
				} else {
					shipping_values();
				}
			
			endif;
		
		}
									
	}
	else:
		shipping_values();
	endif;
	
	$data['shipping_encrypted'] = $shipping_encrypted[0];
	$data['shipping_method'] 	= $shipping_rulename[0];

	//!Calculate discounts
	$discount = 0;

	$coupon_discount = $this->coupons->get_discount($total_price); // Returns 0 if no discount
	// Take the advanced offers module discount into account (which is retained in $discount). Apply the coupon discount above.
	$discount = $discount + $coupon_discount;
	$data['discount_applied'] = $discount_applied = ($coupon_discount > 0) ? true : false; // Used to revoke discount from basket
	
	//Display coupon code form
	if (!$discount_applied) { 
		$data['shopit:coupons'] = $this->coupons->display_form();
	} else {
		$data['shopit:coupons'] = "";
	}
	//END module: coupons

	$total_price = $total_price - $discount;
	
	if ($discount > 0) {
		$data['discount'] = '(' . money($discount, true, true, false) . ') ';
		if ($data['discount_applied'] == true) {
			$data['discount'] .= '<a href="'.site_url('store/coupons/remove').'">Remove</a>';
		}
		$data['discount_encrypted'] = base64_encode($discount);
	} else {
		$data['discount'] = "";
		$data['discount_encrypted'] = "";
	}

	//!Calculate VAT
	//(must include shipping cost - legal requirement!)
	if ($country_vat_exempt_status == 1) {
		$vat = number_format(0,2,'.','');
	} else {
		$vat = $this->config->item('vat_rate') * ($total_price + $data['shipping_value'][0]);
		$vat = money($vat, true, true, false, false, true);
	}
	
	$data['tax'] = $this->config->item('currency') . '<span id="myVAT">' .$vat . '</span>';
	$data['vat'] = $vat;
	$data['vat_encrypted'] = base64_encode($vat);

	//TOTAL PRICE: Add or include VAT in total price...
	$total_amt 			= $total_price + $data['shipping_value'][0] + $vat;
	$data['total'] 		= money($total_amt, true, false, false);
	$data['total_item'] = money($total_price, true, true, false, false);

	$data['sub_total']	= money($sub_total, true, false, true, true, false);
	$data['sub_total_exvat'] = money($sub_total, true, false, false, true, false);
	
	$data['total_encrypted'] = base64_encode($data['total_item']);
	$data['weight_encrypted'] = base64_encode($total_weight);
	$data['to_pay']  = 'To pay: ' . money($total_amt, true, true, false);

	//Show payment options in basket
	$data['payment_options'] = $this->core->payment_options();
	
	//Other shorttags
	$data['link_to_terms'] = site_url('page/terms'); // Link to terms and conditions page
	$data['shopit:customer_details'] = $this->parser->parse('cart/customerdetails', $data, true);

else:
	$data['content'] = 'cart/empty';		
endif;

//Load snippets
$this->settings_model->snippets();

$this->output->set_header("Cache-Control: no-store, no-cache, must-revalidate");
$this->output->set_header("Pragma: no-cache");

//Global short tags
$data['shopit:header']  = $this->parser->parse('global/header', $data, true);
$data['shopit:footer']  = $this->parser->parse('global/footer', $data, true);
$data['shopit:sidebar'] = $this->parser->parse('global/sidebar', $data, true);
$data['shopit:content'] = $this->parser->parse($data['content'], $data, true);
$data['shopit:search'] = $this->parser->parse('boxes/search-box', $data, true);
$data['shopit:mybasket'] = $this->parser->parse('boxes/basket', $data, true);
$data['shopit:categories'] = $this->category_model->createNav();
$data['shopit:pages']  = $this->pages_model->createList();

$this->parser->parse('global/cart',$data);
