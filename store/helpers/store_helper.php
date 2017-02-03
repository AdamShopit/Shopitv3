<?php
#------------------------------------------------------
# Get image
# - $id is the product_id
#------------------------------------------------------
function get_image($id, $width=100, $height=100, $alt='', $ssl=false, $return_img_tag=true) {
	
	$CI =& get_instance();
	$CI->db->select('product_image');
	$CI->db->where('product_id', $id);
	$query = $CI->db->get('inventory');
	
	$product_image = $query->row()->product_image;
		
	if ($product_image != null):
		$image = explode(';',$product_image);
		$thumbnail = '<img src="'.site_url("image/resize/$image[0]/$width/$height") . '" alt="'.$alt.'" title="'.$alt.'" />';
		$link = site_url("image/resize/$image[0]");
	else:
		$thumbnail = '<img src="/site/images/nophoto.png" alt="'.$alt.'" title="'.$alt.'" width="' . $width . '" height="' . $height . '" />';
		$link = "";
	endif;
	
	if ($return_img_tag){
		return $thumbnail;
	} else {
		return $link;
	}

}

#------------------------------------------------------
# Retrieve a Product manually
# - gets all product details
#------------------------------------------------------
function get_product($id) {

	$CI =& get_instance();
	$CI->load->model('products_model');	

	global $data;

	//Get product
	$item = $CI->products_model->getItem($id);
			
	if ($item != null):

		//meta for breadcrumbs, page titles, etc...
		$meta = $CI->category_model->getCategoryById($item->cat_id);
		
		//product pricing
		$product_price  = money($item->product_price * the_vat_rate());
		$product_price_exvat  = money($item->product_price);
		
		if($item->product_saleprice > 0):
			$price_incvat = $item->product_price * the_vat_rate();
			$sale_incvat	= $item->product_saleprice * the_vat_rate();
			$saving_price   = number_format(($price_incvat - $sale_incvat),2,'.','');

			$product_saleprice = money($sale_incvat);
			$product_saleprice_exvat = money($item->product_saleprice);
			$product_saving = ceil(($saving_price * 100)/$price_incvat) . '%';
			$product_saving_exvat = 100-(ceil(($item->product_saleprice*100)/$item->product_price)) . '%';
		else:
			$product_saleprice = '';
			$product_saving = '';
		endif;

		//product image
		if ($item->product_image != null):
			$image = explode(';',$item->product_image);
			$product_image = $image[0];
		else:
			$product_image = '';
		endif;

		//convert this into an array so we can call direct from the view				
		$product = array(
						'name' 	 		=> htmlspecialchars($item->product_name),
						'description' 	=> $item->product_description,
						'excerpt'		=> $item->product_excerpt,
						'price'  		=> $product_price,
						'price_exvat'	=> $product_price_exvat,
						'sale'	 		=> $product_saleprice,
						'sale_exvat'	=> $product_saleprice_exvat,
						'slug'	 		=> get_product_slug($id),
						'saving' 		=> $product_saving,
						'savinc_exvat'	=> $product_saving_exvat,
						'image'  		=> $product_image,
				   );
		

		return $product;
		
	else:endif;
		
}

#------------------------------------------------------
# Get all products in category $cat_id
# - returns data to create links to pages
#------------------------------------------------------
function get_products($cat_id) {
	
	$CI =& get_instance();

	$sql = 'select product_id, inventory.cat_id, c1.cat_father_id, concat_ws("/",c3.cat_slug, c2.cat_slug, c1.cat_slug,"-",inventory.product_slug,inventory.product_id) as url, product_name, product_image, product_description
			from inventory
			inner join category c1 on inventory.cat_id = c1.cat_id
			left join category c2 on c1.cat_father_id = c2.cat_id
			left join category c3 on c2.cat_father_id = c3.cat_id
			where c1.cat_id = '.$cat_id.'
			union all
			select xcat.product_id, inventory.cat_id, "0" as cat_father_id, concat_ws("/", xcat.product_slug,xcat.product_id) as url, product_name, product_image, product_description
			from xcat
			join inventory on inventory.product_id = xcat.product_id
			where xcat.cat_id = '.$cat_id.'
			order by product_name asc';
	
	$query = $CI->db->query($sql);
	
	if ($query->num_rows() > 0){
		return $query->result();		
	}
	
}

#------------------------------------------------------
# Retrieves a Collection manually
# - gets collection name, description and items as an
# - array.
#------------------------------------------------------
function get_collection($collection_id=1) {

	$CI =& get_instance();

	$CI->db->select('collection_items.product_id');
	$CI->db->join('inventory', 'collection_items.product_id = inventory.product_id');
	$CI->db->where('collection_id',$collection_id);
	$CI->db->where('product_disabled', 0);
	$CI->db->order_by('collection_items.order','asc');
	$query = $CI->db->get('collection_items');

	if ($query->num_rows() > 0) {
		return $query->result();
	} else {
		return array();
	}

}

#------------------------------------------------------
# Get Page content
# - retrieves page title and content
#------------------------------------------------------
function get_page($page = 1) {

	$CI =& get_instance();
	
	$CI->db->select('page_name,page_content,page_id,page_slug');
	
	if (is_int($page)):
	$CI->db->where('page_id',$page);
	else:
	$CI->db->where('page_slug',$page);
	endif;
	$query = $CI->db->get('pages');
	
	foreach ($query->result() as $item) {
	
		$page = array(
					'title' => $item->page_name,
					'content' => $item->page_content,
				);
		
		return $page;
	
	}

}

#------------------------------------------------------
# Get Category
# - retrieves category manually
#------------------------------------------------------
function get_category($cat_id = 1, $limit=false, $order_by = 'asc') {

	$CI =& get_instance();
	
	$CI->db->select('product_id');
	$CI->db->where('cat_id',$cat_id);
	$CI->db->limit($limit);
	$CI->db->order_by('product_price',$order_by);
	$query = $CI->db->get('inventory');
	
	foreach ($query->result() as $item) {

		$category[]  = $item->product_id;
					  
	}
	
	return $category;

}

#------------------------------------------------------
# Get custom field template
#------------------------------------------------------
function get_custom_field_template($field_label, $for='inventory', $return_all=false) {
	$CI =& get_instance();
	$CI->db->select('custom_field_title, custom_field_type, custom_field_default');
	$CI->db->where('custom_field_label', 'custom_'.strtolower($field_label));
	$CI->db->where('custom_field_for', $for);
	$query = $CI->db->get('custom_field_templates');
	
	if ($query->num_rows() > 0)
	{
		if ($return_all == false){
			return $query->row()->custom_field_default;
		} else {
			return $query->row();
		}
	} else {
		return false;
	}	
}

#------------------------------------------------------
# Get custom field
#------------------------------------------------------
function get_custom_field($product_id, $field_label, $type='inventory', $return_all=false) {
	$CI =& get_instance();
	$CI->db->select('custom_field_title, custom_field_data');
	$CI->db->from('custom_field_values');
	$CI->db->join('custom_field_templates', 'custom_field_values.custom_field_label = custom_field_templates.custom_field_label');
	$CI->db->where('custom_field_for',$type);
	$CI->db->where('custom_field_values.id',$product_id);
	$CI->db->where('custom_field_values.custom_field_label', 'custom_'.$field_label);
	$query = $CI->db->get();

	if ($query->num_rows() > 0)
	{
		if ($return_all == false){
			return $query->row()->custom_field_data;
		} else {
			return $query->row();
		}
	} else {
		return false;
	}	
}

#------------------------------------------------------
# Contact form
# 	array (
#		array (
#			'name' => 'field_name', //e.g. Email
#			'message' => 'Please enter a valid email',
#			'validation'	=> 'trim|required',
#			'id' => 'field_name', //e.g. txtName
#			'type' => 'text',
#			'label' => 'html label', //e.g. Email
#		),
#  	)
#------------------------------------------------------
function form($array=null, $formid="form1", $class="textbox", $confirmation="<p><strong>Thank you, your enquiry has been received and we will respond shortly.</strong></p>") {
	
	$shopit =& get_instance();
	
	//Reset some vars
	$html = "";
	$html_form = "";
	
	//Load libraries
	$shopit->load->library('form_validation');
	$shopit->load->helper('form');
	$shopit->load->library('email');
	
	//For each field...
	foreach ($array as $field) {
							
		//Default some of the vars if non-existent
		$validation = ($field['validation'] == '') ? 'trim' : $field['validation'];
		$message 	= ($field['message'] == '') ? '' : $field['message'];
		$label 		= ($field['label'] == '') ? $field['name'] : $field['label'];
		$cssid 		= ($field['id'] == '') ? $field['name'] : $field['id'];
		$type  		= ($field['type'] == '') ? 'text' : $field['type'];
		$checked 	= ($field['checked'] == true) ? true : false;
		$value 		= ($field['value'] == "") ? NULL : $field['value'];

		//Set validation rules
		$shopit->form_validation->set_rules($field['name'], $field['message'], $field['validation']);

		//form field attributes to pass to form_helper functions
		$attr = array(
				'name' 	=> $field['name'],
				'id'   	=> $cssid,
				'value' => set_value($field['name']),
				'class' => $class,
				);
				
		//and create the html
		$html .= '<div class="form-row">' . "\n";

		//Sort out the type of formfield this needs to be
		switch ($type) {
			
			case 'textarea':
				$attr['value'] = set_value($field['name'], $value);
				$form_field = form_textarea($attr);
				$html .= '<label for="'.$field['name'].'">'.$label.'</label>' . "\n";
				$html .= "$form_field\n";
				break;
				
			case 'checkbox':
				$attr['checked'] = $checked;
				$attr['value'] = $value;
				$form_field = form_checkbox($attr);
				$html .= "$form_field\n";
				$html .= '<label for="'.$field['name'].'">'.$label.'</label>' . "\n";
				break;

			case 'radio':
				$attr['checked'] = $checked;
				$attr['value'] = $value;
				$form_field = form_radio($attr);
				$html .= "$form_field\n";
				$html .= '<label for="'.$field['name'].'">'.$label.'</label>' . "\n";
				break;
				
			default:
				$attr['value'] = set_value($field['name'], $value);
				$form_field = form_input($attr);
				$html .= '<label for="'.$field['name'].'">'.$label.'</label>' . "\n";
				$html .= "$form_field\n";
				break;
			
		}
		
		$html .= "\n</div>\n";
					
	}

	//Validate the form data
	if ($shopit->form_validation->run() == FALSE) {
	
		//Display the form
		$html_form .= form_open( current_url(), array('id' => $formid) );
		if (validation_errors()) {
		$html_form .= "<ul class=\"formerrors\">\n";
		$html_form .= validation_errors('<li>', '</li>');
		$html_form .= "</ul>\n";
		}
		$html_form .= $html;
		$html_form .= '<div class="form-row form-row-last">' . "\n";
		$html_form .= '<label>&nbsp;</label>' . "\n";
		//Three submit buttons for span prevention (hidden ones are fake)
		$html_form .= form_submit('uhnaa', 'Submit', 'style="display:none;"');
		$html_form .= form_submit('oklidoki', 'Submit');
		$html_form .= form_submit('goaway', 'Submit', 'style="display:none;"');
		$html_form .= "\n</div>\n";
		$html_form .= form_close();
		
		return $html_form;
	
	} else {

		if (!empty($_POST['oklidoki'])) {
					
			//Everything is ok, so we can send the email now. First
			//prep the post vars into email friendly
			foreach ($_POST as $field_label => $field_value) {
			
				$field_label = strip_tags($shopit->security->xss_clean($field_label));
				$field_value = strip_tags($shopit->security->xss_clean($field_value));
			
				if ($field_label != "submit") {
				$prep_email_content .= "$field_label: $field_value\n";
				}
			
			}
	
			$shopit->email->from($shopit->input->post('Email'));
			$shopit->email->to(config_item('store_email'));
			$shopit->email->subject('Website enquiry from '.config_item('store_name'));
			
			$email_content .= "Website enquiry details\n";
			$email_content .= "-----------------------------------\n";
			$email_content .= $prep_email_content;
			$email_content .= "-----------------------------------\n";
			$email_content .= "Sender's IP Address is " . $shopit->input->ip_address();
					
			$shopit->email->message($email_content);
			$shopit->email->send();
	
			//Display confirmation message
			return $confirmation;
		
		}
			
	}
	
}

// Load functions.php for custom functions
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/store/views/functions.php')){
	include($_SERVER['DOCUMENT_ROOT'] . '/store/views/functions.php');	
}