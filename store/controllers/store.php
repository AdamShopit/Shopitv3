<?php

class Store extends CI_Controller {

	function Store()
	{
		parent::__construct();

		$this->load->model('settings_model');
		$this->settings_model->initConfig();

		$this->load->model('products_model');
		$this->load->model('category_model');
		$this->load->model('basket_model');
		$this->load->model('shipping_model');
		$this->load->model('pages_model');
		$this->load->model('filters_model');
		
		$this->load->library('pagination');
		$this->load->library('encrypt');
		$this->load->helper('form');
		$this->load->helper('cookie');
		
		// Load language files & helper
		$this->lang->load('core', 'english');
		$this->load->helper('language');

		//Load modules		
		foreach ($this->config->item('modules') as $module) {
			if (library_exists($module)):
			$this->load->library($module);
			endif;
		}

		//Create a new session variable to be used for persistant basket
		//Check basket cookie exists first and get stored session id
		$basket_cookie = get_cookie('basket');
		
		if ($basket_cookie != FALSE) {
			$this->session->set_userdata('store_session',$basket_cookie);
		} else {
		//Else no cookie exists, then create one
			$time = time().rand(0, 9999);
			$this->session->set_userdata('store_session',$time);
			$expires = ( (60 * 60 * 24) * 365 ) * 10; // 10 years
			set_cookie('basket', $time, $expires);
		}
		
		// Enable profiler
		if ($this->config->item('enable_profiler') == 'true') {
			$this->output->enable_profiler(true);
		}

	}
	
	#------------------------------------------------------
	# Show homepage content
	#------------------------------------------------------
	function index()
	{
		
		global $data, $item;

		// Set generator tag
		$meta_description = '<meta name="generator" content="Shopit 3.2.6">';

		//Get data
		if ($this->products_model->countItemsInCollection(1) > 0):
			$products = $this->products_model->getItemsInCollection(1);
		else:
			$products = $this->products_model->getLatestProducts($this->config->item('latest_products'));
		endif;

		//Get homepage document content
		$home_content = $this->pages_model->getHomepage();

		if ($home_content->page_meta_title != ''):
			$page_title = $home_content->page_meta_title;
		else:
			$page_title = 'Welcome';		
		endif;

		if ($home_content->page_meta_description != ''):
			$meta_description .= '<meta name="description" content="' . format_meta($home_content->page_meta_description) . '" />';
		else:
			$meta_description .= '';
		endif;

		if ($home_content->page_meta_keywords != ''):
			$meta_keywords = '<meta name="keywords" content="' . format_meta($home_content->page_meta_keywords,'keywords') . '" />';
		else:
			$meta_keywords = '';
		endif;
		
		$meta_custom = ($home_content->page_meta_custom != '') ? $home_content->page_meta_custom : '';

		// Set the page heading
		if ($home_content->page_custom_heading != '') {
			$doc_title = $home_content->page_custom_heading;
		} else {
			$doc_title = $home_content->page_name;
		}
		
		// Get basket summary
		$mybasket = $this->core->mybasket_summary();

		$data = array(
			// Basket summary
			'itemtotal'			=> $mybasket->itemtotal,
			'baskettotal' 		=> $mybasket->baskettotal,
			'itemtotal_int'		=> $mybasket->items,
			'baskettotal_exvat' => $mybasket->baskettotal_exvat,
			// Page content
			'doc_title'			=> $doc_title,
			'doc_content'		=> $home_content->page_content,
			'page_id'			=> $home_content->page_id,
			// Meta tags
			'page_title'		=> $page_title,
			'meta_description'	=> $meta_description,
			'meta_keywords'		=> $meta_keywords,
			'meta_custom'		=> $meta_custom,
		);

		//Get product details if exists else redirect to 404
		if (!empty($products)):		

			//Product counter
			$p = 0;

			// Get custom field templates
			$customfield_templates = $this->products_model->customFieldTemplates();
			$cf = array();
			
			//Manipulate the data retrieved from the database previously
			//and create a new array to pass through template parser
			foreach ($products as $item){
			
				$p++;
	
				$product_image = $this->_displayThumbnail($item);

				//Get product url
				$url = $this->_product_url($item);
				
				//Get button/link
				$btn_addtobasket = $this->_btn_addtobasket($item,$url);

				//Define if this item is on sale or not
				$onsale = ($item->product_saleprice > 0) ? TRUE : FALSE;

				//Some useful css classes
				$onsale_css_class = ($onsale) ? "sale-item" : "";
				$css_classes = array("product-$p", "productid-$item->product_id", $item->product_brand_slug, $onsale_css_class, $item->product_type, $item->cat_slug);
				$css_classes = array_filter($css_classes);
				$css_class = implode(" ", $css_classes); //Turn the classes into a space-separated string

				// Get custom fields if any for this product and add to array so we
				// can use in the view as $variables or {template_tags}
				// - Each custom field is a database column
				if ($customfield_templates) {
					foreach($customfield_templates as $customfield_template) {
						$custom_field_col = $customfield_template->custom_field_label;
						$customfield_template_label = str_replace('custom_', 'custom:', $customfield_template->custom_field_label);
						$customfield_template_label = str_replace('-', '_', $customfield_template_label);
						if ($customfield_template->custom_field_type == 'editor') {
							$custom_field_data = autop($item->$custom_field_col);
						} else {
							$custom_field_data = trim($item->$custom_field_col);
						}
						$cf[$customfield_template_label] = $custom_field_data;
					}
				}

				// Price range template tag
				$product_price_range = ($item->product_type == 'variation' && $item->min_price != $item->max_price) ? sprintf('%s - %s', money($item->min_price), money($item->max_price)) : money($item->min_price);
				$product_price_range_exvat = ($item->product_type == 'variation' && $item->min_price != $item->max_price) ? sprintf('%s - %s', money($item->min_price, true, true, false), money($item->max_price, true, true, false)) : money($item->min_price, true, true, false);

				//Setup the data to pass to the view file
				$this_product = array(
					'product_count'				=> $p,
					'product_id' 				=> $item->product_id,
					'cat_id' 	 				=> $item->cat_id,
					'product_type'				=> $item->product_type,
					'product_name' 				=> $item->product_name,
					'product_description' 		=> $item->product_description,
					'product_excerpt'			=> $item->product_excerpt,
					'product_summary'			=> get_first_paragraph($item->product_description),
					'product_brand'				=> $item->product_brand,
					'product_brand_slug' 		=> $item->product_brand_slug,
					'product_code'				=> $item->product_no,
					'product_price'				=> money($item->min_price),
					'product_price_exvat'		=> money($item->min_price, true, true, false),
					'product_saleprice'			=> money($item->product_saleprice),
					'product_saleprice_exvat'	=> money($item->product_saleprice_exvat, true, true, false),
					'max_price'					=> money($item->max_price),
					'max_price_exvat'			=> money($item->max_price, true, true, false),
					'product_price_range'		=> $product_price_range,
					'product_price_range_exvat'	=> $product_price_range_exvat,
					'cat_slug'					=> $product_slug,
					'product_slug'				=> $item->product_slug,
					'product_image' 			=> $product_image,
					'btn_addtobasket'			=> $btn_addtobasket,
					'url'						=> $url,
					'css_classes'				=> $css_class,
					//Extra variables
					'get_product_price' 		=> money($item->product_price),
					'get_product_price_exvat'	=> money($item->product_price_exvat, true, true, false),
					//Modules
					'shopit:specialoffers'	=> $special_offers,
				);
				
				// Merge the two arrays (item data and item's custom fields)
				$data['item'][] = array_merge($this_product, $cf);
								
			}

		else:
			$data['item'] = array();		
		endif;

		//Load snippets
		$this->settings_model->snippets();

		//Load the core templates
		$data['shopit:header']  = $this->parser->parse('global/header', $data, true);
		$data['shopit:footer']  = $this->parser->parse('global/footer', $data, true);
		$data['shopit:sidebar'] = $this->parser->parse('global/sidebar', $data, true);
		$data['shopit:content'] = $this->parser->parse('content/homepage', $data, true);
		$data['shopit:search']  = $this->parser->parse('boxes/search-box', $data, true);
		$data['shopit:mybasket']  = $this->parser->parse('boxes/basket', $data, true);
		$data['shopit:breadcrumb']  = "";
		$data['shopit:categories'] = $this->category_model->createNav();
		$data['shopit:pages']  = $this->pages_model->createList();

		$this->parser->parse('global/home',$data);
	}

	#------------------------------------------------------
	# Categories
	#------------------------------------------------------
	function categories() {

		global $data, $item;

		// Get basket summary
		$mybasket = $this->core->mybasket_summary();

		$data = array(
			// Basket summary
			'itemtotal'			=> $mybasket->itemtotal,
			'baskettotal' 		=> $mybasket->baskettotal,
			'itemtotal_int'		=> $mybasket->items,
			'baskettotal_exvat' => $mybasket->baskettotal_exvat,
			// Meta tags
			'page_title'		=> "Categories",
			'meta_description'	=> '',
			'meta_keywords'		=> '',
			'meta_custom'		=> '',
		);
		
		//Load snippets
		$this->settings_model->snippets();

		//Load the core templates
		$data['shopit:header']  	= $this->parser->parse('global/header', $data, true);
		$data['shopit:footer']  	= $this->parser->parse('global/footer', $data, true);
		$data['shopit:sidebar'] 	= $this->parser->parse('global/sidebar', $data, true);
		$data['shopit:content'] 	= $this->parser->parse('content/categories', $data, true);
		$data['shopit:search']  	= $this->parser->parse('boxes/search-box', $data, true);
		$data['shopit:mybasket']  	= $this->parser->parse('boxes/basket', $data, true);
		$data['shopit:breadcrumb']  = "";
		$data['shopit:categories'] 	= $this->category_model->createNav();
		$data['shopit:pages']  		= $this->pages_model->createList();

		$this->parser->parse('global/store', $data);
		
	}

	#------------------------------------------------------
	# Get products in category
	#------------------------------------------------------
	function category()
	{				
		global $data, $item;
		
		manage_redirection();

		//Get the cat_id/cat_father_id of this category..
		$cat = $this->category_model->getCategory();
		
		//check if in parent or child category
		//- checks for slug as segment 2
		if ( ($this->uri->segment(2) != '' && !is_numeric($this->uri->segment(2))) && ($this->uri->segment(3) == '' || is_numeric($this->uri->segment(3))) ):
			$cat_url = $this->uri->segment(1).'/'.$cat->cat_slug;
			$config['uri_segment'] 	= 3; //pagination config
		//- checks for slug as segment 3
		elseif ( !is_numeric($this->uri->segment(2)) && (!is_numeric($this->uri->segment(3)) && $this->uri->segment(3)!= null )):
			$cat_url = $this->uri->segment(1).'/'.$this->uri->segment(2).'/'.$cat->cat_slug;
			$config['uri_segment'] 	= 4; //pagination config
		//- for parent
		else:
			$cat_url = $cat->cat_slug;
			$config['uri_segment'] 	= 2; //pagination config
		endif;

		//START pagination
		//For pagination (includes filter)
		$get_url = array();

		if (!empty($_GET)) {
			foreach($_GET as $param=>$value) {
				$get_url[$param] = $value;
			}
			$filter_url = "?".http_build_query($get_url);
			$config['url_format'] = site_url($cat_url . '/{offset}' . $filter_url);
		} else {
			$filter_url = "?";
			$config['url_format'] = site_url($cat_url);
		}

		//If no cat_id for this category, set it to -1
		if ($cat->cat_id == null) {
			$cat = (object) array('cat_id' => '-1');
		}
		
		//get number of products in the current category.
		$item_count = $this->products_model->getProducts(true, $cat->cat_id, null, null);
		
		$config['total_rows'] = $item_count;

		if ($_GET['perpage'] != '') {
			$config['per_page'] = $this->input->get('perpage');
		} else {
			$config['per_page'] = $this->config->item('products_per_page');
		}
		
		$this->pagination->initialize($config);
		//END of pagination

		//meta for breadcrumbs, page titles, etc...
		if ($cat->cat_father_id > 0):
			
			if ($cat->parent_cat_slug != null):							
				$parent_category = '<a href="'.site_url($cat->ancestor_cat_slug).'">'.$cat->ancestor_cat_name.'</a> &raquo; ';
				$parent_category .= '<a href="'.site_url($cat->ancestor_cat_slug.'/'.$cat->parent_cat_slug).'">'.$cat->parent_cat_name.'</a> &raquo; ';
				$crumb[$cat->ancestor_cat_name] = site_url($cat->ancestor_cat_slug);
				$crumb[$cat->parent_cat_name]  = site_url("$cat->ancestor_cat_slug/$cat->parent_cat_slug");
			else:
				$parent_category = '<a href="'.site_url($cat->parent_cat_slug).'">'.$cat->parent_cat_name.'</a> &raquo; ';
				$crumb[$cat->parent_cat_name] = site_url($cat->parent_cat_slug);
			endif;

			$meta_parent_category = $cat->parent_cat_name.' &raquo; ';
		
		endif;

		$crumb[$cat->cat_name] = ""; // Current category

		//Breadcrumb
		$breadcrumbs = array(
			'breadcrumb_parents'=> $parent_category,
			'breadcrumb_child'	=> $cat->cat_name,
			'breadcrumb_product'=> null,
			'breadcrumb'		=> $this->core->breadcrumb($crumb),
		);

		$meta_title = ($cat->cat_meta_title != '' ? $cat->cat_meta_title . ' - ' : false);

		$cat_excerpt = ($cat->cat_excerpt != '' ? $cat->cat_excerpt : false);
		
		if ($cat->cat_meta_description != ''):
			$meta_description = '<meta name="description" content="' . format_meta($cat->cat_meta_description) . '"/>';
		elseif ($cat->cat_desc != ''):
			$meta_description = '<meta name="description" content="' . format_meta($cat->cat_desc) . '"/>';
		endif;

		$meta_keywords = ($cat->cat_meta_keywords != '' ? '<meta name="keywords" content="' . format_meta($cat->cat_meta_keywords,'keywords') . '"/>' : false);

		$meta_custom = ($cat->cat_meta_custom != '') ? $cat->cat_meta_custom : '';

		if (empty($cat->cat_name)):
			$this->output->set_status_header('404');
			$cat_name = "Sorry, this category no longer exists";
		else:
			//Show custom heading, else default to product name
			$cat_name = ($cat->cat_custom_heading != '' ? $cat->cat_custom_heading : $cat->cat_name);
		endif;
		
		//Get products
		$products = $this->products_model->getProducts(false, $cat->cat_id, $config['per_page'], $this->uri->segment($config['uri_segment']));

		//Get product details if exists else display "no products message"
		if (!empty($products)):
			
			//Product counter
			$p = 0;
			
			//Get custom field templates
			$customfield_templates = $this->products_model->customFieldTemplates();
			$cf = array();

			//Manipulate the data retrieved from the database previously
			foreach ($products as $item) {
				
				$p++;

				// Get the correct url
				$url = $this->_product_url($item);

				//Get button/link
				$btn_addtobasket = $this->_btn_addtobasket($item,$url);
				
				//Define if this item is on sale or not
				$onsale = ($item->product_saleprice > 0) ? TRUE : FALSE;
				
				//Some useful css classes
				$onsale_css_class = ($onsale) ? "sale-item" : "";
				$css_classes = array("product-$p", "productid-$item->product_id", $item->product_brand_slug, $onsale_css_class, $item->product_type, $item->cat_slug);
				$css_classes = array_filter($css_classes);
				$css_class = implode(" ", $css_classes); //Turn the classes into a space-separated string

				// Get custom fields if any for this product and add to array so we
				// can use in the view as $variables or {template_tags}
				// - Each custom field is a database column
				if ($customfield_templates) {
					foreach($customfield_templates as $customfield_template) {
						$custom_field_col = $customfield_template->custom_field_label;
						$customfield_template_label = str_replace('custom_', 'custom:', $customfield_template->custom_field_label);
						$customfield_template_label = str_replace('-', '_', $customfield_template_label);
						if ($customfield_template->custom_field_type == 'editor') {
							$custom_field_data = autop($item->$custom_field_col);
						} else {
							$custom_field_data = trim($item->$custom_field_col);
						}
						$cf[$customfield_template_label] = $custom_field_data;
					}
				}

				// Price range template tag
				$product_price_range = ($item->product_type == 'variation' && $item->min_price != $item->max_price) ? sprintf('%s - %s', money($item->min_price), money($item->max_price)) : money($item->min_price);
				$product_price_range_exvat = ($item->product_type == 'variation' && $item->min_price != $item->max_price) ? sprintf('%s - %s', money($item->min_price, true, true, false), money($item->max_price, true, true, false)) : money($item->min_price, true, true, false);

				//Setup the data to pass to the view file
				$this_product = array(
					'product_count'				=> $p,
					'product_id' 				=> $item->product_id,
					'cat_id' 	 				=> $item->cat_id,
					'product_type'				=> $item->product_type,
					'product_name' 				=> $item->product_name,
					'product_description' 		=> $item->product_description,
					'product_excerpt' 			=> $item->product_excerpt,
					'product_summary'			=> get_first_paragraph($item->product_description),
					'product_brand' 			=> $item->product_brand,
					'product_brand_slug'		=> $item->product_brand_slug,
					'product_code'				=> $item->product_no,
					'product_price'				=> money($item->min_price),
					'product_price_exvat'		=> money($item->min_price, true, true, false),
					'product_saleprice'			=> money($item->product_saleprice),
					'product_saleprice_exvat'	=> money($item->product_saleprice_exvat, true, true, false),
					'max_price'					=> money($item->max_price),
					'max_price_exvat'			=> money($item->max_price, true, true, false),
					'product_price_range'		=> $product_price_range,
					'product_price_range_exvat'	=> $product_price_range_exvat,
					'product_image' 			=> $this->_displayThumbnail($item),
					'btn_addtobasket'			=> $btn_addtobasket,
					'url'						=> $url,
					'css_classes'				=> $css_class,
					//Extra variables
					'get_product_price' 		=> money($item->product_price),
					'get_product_price_exvat'	=> money($item->product_price_exvat, true, true, false),
					//Modules
					'shopit:specialoffers'		=> $special_offers,
				);
				
				// Merge the two arrays (item data and item's custom fields)
				$items[] = array_merge($this_product, $cf);
			
			}
		
		else:
			$message = 'There are no products in this category.';
		endif;

		// Get category image
		if (library_exists('categoryicons')) { 
			$cat_image = $this->categoryicons->get_image($cat->cat_image, $cat->cat_name);
			$cat_image_file = sprintf('%sdocs/%s', base_url(), $cat->cat_image);
		} else {
			$cat_image = "";
			$cat_image_file = "";
		}

		//Showing 1-n products tag
		$from_n = ( $this->uri->segment($config['uri_segment']) > 0 ) ? $this->uri->segment($config['uri_segment']) + 1 : 1;
		
		$to_n_calc = $from_n + $config['per_page'] - 1;
		$to_n = ($config['total_rows'] <= $to_n_calc) ? $config['total_rows'] : $to_n_calc;
		$from_to_label = "$from_n-$to_n";

		// Get basket summary
		$mybasket = $this->core->mybasket_summary();

		//Data to pass to view
		$data = array(
			//Core elements of page
			'itemtotal'			=> $mybasket->itemtotal,
			'baskettotal' 		=> $mybasket->baskettotal,
			'itemtotal_int'		=> $mybasket->items,
			'baskettotal_exvat' => $mybasket->baskettotal_exvat,
			//Page meta tags
			'page_title' 		=> $meta_title . $meta_parent_category . $cat->cat_name,
			'meta_description'	=> $meta_description,
			'meta_keywords'		=> $meta_keywords,
			'meta_custom'		=> $meta_custom,
			//Category details
			'cat_name'			=> $cat_name,
			'cat_id'			=> $cat->cat_id,
			'cat_desc'			=> $cat->cat_desc,
			'cat_excerpt'		=> $cat->cat_excerpt,
			'cat_url'			=> site_url($cat_url),
			'cat_image'			=> $cat_image,
			'cat_image_file'		=> $cat_image_file,
			//Layered Navigation
			'layers'			=> $this->filters_model->layers($cat->cat_id, $cat_url),
			'layers_selected'	=> $this->filters_model->selected($cat_url),
			// Product sorting
			'results_sort'		=> $this->filters_model->sort($cat_url),
			'results_perpage'	=> $this->filters_model->perpage($cat_url, $config['total_rows']),
			//Products
			'item'				=> $items,
			'total_products'	=> $config['total_rows'],
			'showing'			=> $from_to_label,
			'pagination'		=> $this->pagination->create_links(),
			//Templates & messages
			'message'			=> $message,
		);

		//Load snippets
		$this->settings_model->snippets();

		//Load the core templates
		$data['shopit:breadcrumb']	= $this->parser->parse('boxes/breadcrumb', $breadcrumbs, true);
		$data['shopit:header']  	= $this->parser->parse('global/header', $data, true);
		$data['shopit:footer']  	= $this->parser->parse('global/footer', $data, true);
		$data['shopit:sidebar'] 	= $this->parser->parse('global/sidebar', $data, true);
		$data['shopit:content'] 	= $this->parser->parse('content/products', $data, true);
		$data['shopit:search']  	= $this->parser->parse('boxes/search-box', $data, true);
		$data['shopit:mybasket']  	= $this->parser->parse('boxes/basket', $data, true);
		$data['shopit:categories']  = $this->category_model->createNav();
		$data['shopit:pages']   	= $this->pages_model->createList();
		if ($item_count > 0) {
		$data['shopit:sort_options']= $this->parser->parse('boxes/sort', $data, true);
		} else {
		$data['shopit:sort_options']= "";
		}
		if (library_exists('filters')) {
			if ($cat->cat_hide == 0) {
				$data['shopit:layers'] = $this->parser->parse('boxes/layers', $data, true);
			} else {
				$data['shopit:layers'] = "";
			}
		} else {
			$data['shopit:layers'] = "";
		}

		// Check if there is a custom template for this category
		if (file_exists( $_SERVER['DOCUMENT_ROOT'] . '/store/views/cat-'.$cat->cat_id . '.php')):
			$this->parser->parse('cat-' . $cat->cat_id,$data);
		elseif (file_exists( $_SERVER['DOCUMENT_ROOT'] . '/store/views/cat-'.$this->uri->segment(1) . '.php')):
			$this->parser->parse('cat-' . $this->uri->segment(1), $data);
		else:
			$this->parser->parse('global/store',$data);
		endif;

	}

	#------------------------------------------------------
	# Get product information
	#------------------------------------------------------
	function item()
	{		
		global $data;

		manage_redirection();

		// Reset vars
		$product_gallery = "";
		$img_index = -1;

		//Get data
		$item 				 = $this->products_model->getItem($this->uri->segment(5));
		$variations			 = $this->products_model->getVariations($this->uri->segment(5));
		$attributes 		 = $this->products_model->getAttributes($this->uri->segment(5));
		$productoptiongroups = $this->products_model->getProductOptionGroups($this->uri->segment(5));

		//Get custom field templates
		$customfield_templates = $this->products_model->customFieldTemplates();
		$variant_customfield_templates = $this->products_model->customFieldTemplates('inventory', true);
		$cf_variant_array = array();

		//Get product details if exists else redirect to 404
		if (!empty($item)):
		
			//Increment item view counter
			if (is_admin() == FALSE) {
			$this->products_model->incrementView($item->product_id,$item->product_views);
			}
			
			//Sort out the images (inc. gallery)
			if ($this->config->item('image_zoom') <= 500):
				$zoom = 500;
			else:
				$zoom = $this->config->item('image_zoom');
			endif;

			$gallery_thumb_size = ($this->config->item('gallery_thumb') == '') ? 50 : $this->config->item('gallery_thumb');
			
			if ($item->product_image != null):
				$image = explode(';', $item->product_image);
				$product_image_default  = site_url("image/resize/$image[0]");
				$product_image_fullsize	= site_url($this->config->item('path_to_uploads') . $image[0]);
				$product_image  = sprintf('<a href="%s" target="_blank" id="shopit-photo-link" data-index="0">', site_url("image/resize/$image[0]/$zoom/$zoom"));
				$product_image .= sprintf('<img src="%s" alt="%s" title="%s" data-zoom-image="%s" id="shopit-photo" />', site_url("image/resize/$image[0]"), htmlspecialchars($item->product_name), htmlspecialchars($item->product_name), $product_image_fullsize);
				$product_image .= '</a>';
								
				foreach ($image as $gkey=>$gallery_image) {
					if (!empty($gallery_image)) {
						$img_index++;
						$product_gallery .= "<li>\n";
						$product_gallery .= sprintf('<a href="%s" class="shopit-gallery-fancybox shopit-gallery-thumb" data-defaultsize="%s" data-zoom="%s" data-index="%s" data-image="%s" data-zoom-image="%s" rel="shopit-gallery">', site_url("image/resize/$gallery_image/$zoom/$zoom"), site_url("image/resize/$gallery_image"), $zoom, $img_index, site_url("image/resize/$gallery_image"), site_url($this->config->item('path_to_uploads').$gallery_image));
						$product_gallery .= sprintf('<img src="%s" alt="%s" />', site_url('image/resize/'.$gallery_image."/$gallery_thumb_size/$gallery_thumb_size"), $gallery_image);
						$product_gallery .= "</a>\n";
						$product_gallery .= "</li>\n";
					}
				}
				
			else:
				$product_image = '<img src="/site/images/nophoto.png" alt="Photo coming soon" width="' . $this->config->item('image_width') . '" height="' . $this->config->item('image_height') . '" />';
				$product_image_default  = null;
				$product_image_fullsize = null;
			endif;
			//End: Images/Gallery
			
			//Show custom heading, else default to product name
			if ($item->product_custom_heading != '') {
				$product_name = $item->product_custom_heading;
			} else {
				$product_name = $item->product_name;
			}

			//Get product attributes
			if ($attributes != ''):
				$product_attributes = "\r".'<h3 id="productAttributesTitle">Product features</h3>' . "\n";
				$product_attributes .= '<ul id="productAttributes">' . "\n";
				foreach ($attributes as $attribute) {

					$attribute_name = trim($attribute->attribute_name);
				
					if (!empty($attribute_name)) {
						$attribute_label = '<label>' . $attribute->attribute_name . ': </label>';
					}
					else {
						$attribute_label = '<label>&nbsp;</label>';
					}
					
					$product_attributes .= '  <li>' . $attribute_label . $attribute->attribute_value . '</li>' . "\n";
				
				}
				$product_attributes .= '</ul>' . "\n";
			endif;
			
			//Get product options
			$option_group_array = array();
			if ($productoptiongroups != '') {
				
				foreach ($productoptiongroups as $productoptiongroup) {

					$productoptions = $this->products_model->getProductOptions($this->uri->segment(5),$productoptiongroup->option_label);

					$opt++;
					$this_opt = 0;
					
					$option_group_array[$opt] = array(
						'option_number' => $opt,
						'option_group'	=> $productoptiongroup->option_label, // Group label
						'option_total'  => count($productoptions),
					);
					
					foreach ($productoptions as $productoption) {

						//Define if checked
						$this_opt++;
						$option_checked = ($this_opt == 1) ? 'checked="checked"' : '';

						//Start building the product options array
						$option_group_array[$opt]['option'][] = array(
							'option_id'			 => $productoption->id, // Option ID
							'option_label'		 => $productoption->option_criteria, // Option criteria
							'option_price' 		 => money($productoption->option_price),
							'option_price_exvat' => money($productoption->option_price, true, true, false),
							'option_order'		 => $productoption->option_order,
							'option_checked'	 => $option_checked,
						);
						
					}
				
				}
				
			}

			//Show STOCK NUMBER if config set to true
			if($this->config->item('stock_showamount') == 'true'):
				if($item->product_qty > 0):
				$product_stock = 'In stock: '.$item->product_qty;
				else:
				$product_stock   = '<span class="outofstock">OUT OF STOCK</span>';
				endif;
			endif;

			//Display qty options and add to basket button -> takes config option into account
			if($this->config->item('outofstock_purchases') == 'false'):
				
				// Set quantity limit
				if ($item->product_qty >= $this->config->item('stock_purchaselimit')):
					$qty_limit = $this->config->item('stock_purchaselimit');
				else:
					$qty_limit = $item->product_qty;
				endif;
				
				// Create the quantity dropdown
				if($item->product_qty > 0 && $item->product_disabled == 0):
					$qty_select  = "\r" . '<label>Quantity: </label>' . "\n";
					$qty_select .= '<select name="qty" class="qty_select">' . "\n";
					for($i=1;$i<=$qty_limit;$i++){
					$qty_select .= '  <option value="'.$i.'">'.$i.'</option>' . "\n";
					}
					$qty_select .= '</select>' . "\n";
					$btn_addtobasket = true;
				else:
					$btn_addtobasket = false;
				endif;
				
			else:
				$qty_select  = '<label>Quantity: </label>';
				$qty_select .= '<input type="text" name="qty" class="qty_select" value="1" size="2" maxlength="4" />';
				$btn_addtobasket = true;
			endif;
			
		else:
			//No product information found -> redirect to top level category
			redirect($this->uri->segment(1),'location',301);
		endif;
		
		//Get the cat_id/cat_father_id of this item
		$cat = $this->category_model->getCategory();

		if($cat->cat_father_id > 0): //Then we know we are in a subcategory right now...

			if ($cat->parent_cat_slug != null):							
				$grandparent_category = '<a href="'.site_url($cat->ancestor_cat_slug).'">'.$cat->ancestor_cat_name.'</a> &raquo; <a href="'.site_url($cat->ancestor_cat_slug.'/'.$cat->parent_cat_slug).'">'.$cat->parent_cat_name.'</a> &raquo; ';
				$parents_category 	= '<a href="'.site_url($cat->ancestor_cat_slug.'/'.$cat->parent_cat_slug.'/'.$cat->cat_slug).'">'.$cat->cat_name.'</a> &raquo; ';
				$crumb[$cat->ancestor_cat_name] = site_url($cat->ancestor_cat_slug);
				$crumb[$cat->parent_cat_name]  = site_url("$cat->ancestor_cat_slug/$cat->parent_cat_slug");
				$crumb[$cat->cat_name]    = site_url("$cat->ancestor_cat_slug/$cat->parent_cat_slug/$cat->cat_slug");
			else:
				$grandparent_category = '<a href="'.site_url($cat->parent_cat_slug).'">'.$cat->parent_cat_name.'</a> &raquo; ';
				$parents_category 	= '<a href="'.site_url($cat->parent_cat_slug.'/'.$cat->cat_slug).'">'.$cat->cat_name.'</a> &raquo; ';
				$crumb[$cat->parent_cat_name] = site_url($cat->parent_cat_slug);
				$crumb[$cat->cat_name]   = site_url("$cat->parent_cat_slug/$cat->cat_slug");
			endif;

		else:
			$grandparent_category = '<a href="'.site_url($cat->cat_slug).'">'.$cat->cat_name.'</a> &raquo; ';
			$crumb[$cat->cat_name] = site_url($cat->cat_slug);
		endif;

		if ($item->product_meta_title != ''):
			$meta_title = $item->product_meta_title . ' - ';
		else:
			$meta_title = $item->product_name . ' - ';
		endif;
		
		if ($item->product_meta_description != ''):
			$meta_description = '<meta name="description" content="' . format_meta($item->product_meta_description) . '"/>';
		else:
			$meta_description = '<meta name="description" content="' . format_meta($item->product_description) . '"/>';
		endif;

		if ($item->product_meta_keywords != ''):
			$meta_keywords = '<meta name="keywords" content="' . format_meta($item->product_meta_keywords,'keywords') . '"/>';
		endif;

		$meta_custom = (strlen($item->product_meta_custom) > 1) ? $item->product_meta_custom : '';

		$crumb[$item->product_name] = ""; // Current page crumb

		//Breadcrumbs - to pass to breadcrumbs view
		$breadcrumbs = array(
			'breadcrumb_parents' => $grandparent_category,
			'breadcrumb_child'	 => $parents_category,
			'breadcrumb_product' => $item->product_name,
			'breadcrumb'		 => $this->core->breadcrumb($crumb),
		);

		//Product tags...
		if ($item->product_tags != ''):
			$product_tags = explode(',',trim($item->product_tags));
			$product_tags = array_filter($product_tags);
			
			foreach ($product_tags as $product_tag):
				$product_tag = trim($product_tag);
				$product_tag_link .= '<a href="' . site_url('tag/'. rawurlencode(strtolower($product_tag))) . '" class="product-tag"><span>' . $product_tag . '</span></a> ';
			endforeach;
			
			$product_tags = '<label>Tags:</label> ' . $product_tag_link;
		endif;

		//Product file
		if (!empty($item->product_file)):
			$product_file = '<a href="' . site_url('docs/' . $item->product_file) . '" target="_blank">' . $item->product_file . '</a>';
		endif;

		//We'll write the $item_child array as we need to
		//calculate the prices with/without VAT and the base rate
		//(Note: product_price has been adjusted in the model)
		//Also Check if this parent item is disabled (or archived)
		//and clear the child item array accordingly
		
		$variant_array = array();
		$v_count = 0;
		$variant_attr = array();
		$var_attr_html = '';
				
		if (!empty($variations) && $item->product_disabled == 0) {

			// BEGIN: VARIANT ATTRIBUTES
			// Preliminary loop to create the variant attributes key template
			foreach ($variations as $variant_product_name) {
				// Unserialize this variant's product name
				$variant_name_attributes = unserialize($variant_product_name->product_name);
			}
			// Define the template of keys here
			if (is_array($variant_name_attributes)) {
				$variant_key_template = array_keys($variant_name_attributes);
			}
			// END: VARIANT ATTRIBUTES
		
			foreach ($variations as $variant) {
			
				//Reset a few things...
				$i = 0;
				$option_group_array = array();
			
				//Get product options, starting with the option groups
				$productoptiongroups = $this->products_model->getProductOptionGroups($variant->product_id);

				if ($productoptiongroups != '') {
				
					foreach ($productoptiongroups as $productoptiongroup) {
					
						//Now, get the options per group
						$productoptions = $this->products_model->getProductOptions($variant->product_id, $productoptiongroup->option_label);

						$opt++;
						
						$option_group_array[$opt] = array(
							'option_number' => $opt,
							'option_group'	=> $productoptiongroup->option_label, // Group label
							'option_total'  => count($productoptions),
						);
						
						foreach ($productoptions as $productoption) {

							//Start building the product options array
							$option_group_array[$opt]['option'][] = array(
								'option_id'			 => $productoption->id, // Option ID
								'option_label'		 => $productoption->option_criteria, // Option criteria
								'option_price' 		 => money($productoption->option_price),
								'option_price_exvat' => money($productoption->option_price, true, true, false),
								'option_order'		 => $productoption->option_order,
							);
							
						}

					}
	
				}
	
				//Display qty options and add to basket button -> takes config option into account
				if ($this->config->item('outofstock_purchases') == 'false') {
					
					if ($variant->product_qty >= $this->config->item('stock_purchaselimit')) {
						$qty_limit = $this->config->item('stock_purchaselimit');
					} else {
						$qty_limit = $variant->product_qty;
					}
					
					//Build the select dropdown
					if ($variant->product_qty > 0) {
						$variant_qty_select = '<select name="qty" class="qty_select">' . "\n";
						for($i=1;$i<=$qty_limit;$i++){
						$variant_qty_select .= '  <option value="'.$i.'">'.$i.'</option>' . "\n";
						}
						$variant_qty_select .= "</select>\n";
						$variant_purchaseable = TRUE;
					} else {
						$variant_qty_select = "";
						$variant_purchaseable = FALSE;
					}
					
				} else {
				
					$variant_qty_select = '<input type="text" name="qty" class="qty_select" value="1" size="2" />';
					$variant_purchaseable = TRUE;
				
				}
				
				//Create the buy button code
				if ($variant_purchaseable) {
					
					$variant_buybtn  = "<input type=\"hidden\" name=\"product_id\" value=\"$variant->product_id\" />";
					$variant_buybtn .= "<input type=\"hidden\" name=\"redirect_url\" value=\"" . current_url() . "\" />";
					$variant_buybtn .= sprintf('<input type="submit" name="submit" value="%s" onclick="%s" />', lang('buy_btn') , "_gaq.push(['_trackEvent', 'Product', 'Buy', 'Add to basket']);");
					
				} else {
					$variant_buybtn = "";
				}
				
				//Set flag for when this variant is a sale item
				$variant_on_sale = ($variant->product_saleprice > 0) ? true : false;
				
				if ($this->config->item('stock_showamount') == 'true') {
					if ($variant->product_qty > 0) { 
						$variant_stock_level = $variant->product_qty;
					} else {
						$variant_stock_level = "Out of stock";
					}
				} else {
					$variant_stock_level = "";
				}
				
				$product_stock = "";

				//Get default image for this child and apply as gallery thumbnail
				if ($variant->product_image != null) {
				
					$img_index++;
					
					//This is to add the image to the child array
					$variant_image = explode(';', $variant->product_image);
					$variant_product_image_default  = site_url("image/resize/$variant_image[0]");
					$variant_product_image_fullsize	= site_url($this->config->item('path_to_uploads') . $variant_image[0]);
					$variant_product_image  = sprintf('<a href="%s" data-zoom="%s" class="shopit-gallery-thumb" data-defaultsize="%s" data-index="%s" data-zoom-image="%s" >', site_url("image/resize/$variant_image[0]/$zoom/$zoom"), $zoom, site_url("image/resize/$variant_image[0]"), $img_index, site_url($this->config->item('path_to_uploads').$variant_image[0]));
					$variant_product_image .= sprintf('<img src="%s" alt="%s" title="%s" />', site_url("image/resize/$variant_image[0]/$gallery_thumb_size/$gallery_thumb_size"), htmlspecialchars($item->product_name), htmlspecialchars($item->product_name));
					$variant_product_image .= '</a>';
					
					//And, we should also attach it to the main product gallery
					$product_gallery .= "<li>\n";
					$product_gallery .= sprintf('<a href="%s" data-zoom="%s" class="shopit-gallery-fancybox shopit-gallery-thumb shopit-gallery-thumb-variant-%s" data-defaultsize="%s" data-index="%s" data-image="%s" data-zoom-image="%s" rel="shopit-gallery" >', site_url("image/resize/$variant_image[0]/$zoom/$zoom"), $zoom, $variant->product_id, site_url("image/resize/$variant_image[0]"), $img_index, site_url("image/resize/$variant_image[0]"), site_url($this->config->item('path_to_uploads').$variant_image[0]));
					$product_gallery .= sprintf('<img src="%s" alt="%s"/>', site_url("image/resize/$variant_image[0]/$gallery_thumb_size/$gallery_thumb_size"), $variant_image[0]);
					$product_gallery .= '</a>' . "\n";
					$product_gallery .= '</li>' . "\n";
					
				} else {
					$variant_product_image = '';
					$variant_product_image_default  = null;
					$variant_product_image_fullsize	= null;
				}

				$v_count++;

				// Some useful css classes
				$has_variant_attr = (is_array(unserialize($variant->product_name))) ? 'has-variant-attributes' : '';
				$variant_css_classes = array("variant-$v_count", "variantid-$variant->product_id", $has_variant_attr);
				$variant_css_classes = array_filter($variant_css_classes);
				$variant_css_class   = implode(" ", $variant_css_classes); //Turn the classes into a space-separated string

				// Check if this parents's category (parent or ancestor) is disabled 
				// or not. If it is, clear the following variables as these variations should
				// not be purchaseable.
				if ($item->cat_hide) {
					$variant_qty_select 	= "";
					$variant_buybtn 	= "";
					$variant_purchaseable = FALSE;
				}

				//Add item to array
				$this_variant = array(
						//Child details
						'variant_count'						=> $v_count,
					  	'variant_id'						=> $variant->product_id,
					  	'variant_product_name'				=> unserialize_variant($variant->product_name),
					  	'variant_product_code'				=> $variant->product_no,
					  	'variant_product_rrp'				=> money($variant->product_rrp),
					  	'variant_product_rrp_exvat' 		=> money($variant->product_rrp, true, true, false),
					  	'variant_product_price'		 		=> money($variant->product_price),
					  	'variant_product_price_exvat' 		=> money($variant->product_price, true, true, false),
					  	'variant_product_saleprice'			=> money($variant->product_saleprice),
					  	'variant_product_saleprice_exvat'	=> money($variant->product_saleprice, true, true, false),
					  	'variant_product_price_dec'		 	=> money($variant->product_price, true, true, true, false),
					  	'variant_product_price_exvat_dec' 	=> money($variant->product_price, true, true, false, false),
					  	'variant_product_options'			=> $option_group_array, // Array
					  	'variant_qty'						=> $variant->product_qty,
					  	'variant_stock_level'				=> $variant_stock_level,
					  	'variant_qty_select'					=> $variant_qty_select,
					  	'variant_purchaseable'				=> $variant_purchaseable, //true or false
					  	'variant_buybtn'					=> $variant_buybtn,
					  	'variant_onsale'						=> $product_child_on_sale,
					  	'variant_product_image'				=> $variant_product_image,
					  	'variant_product_image_default'		=> $variant_product_image_default,
					  	'variant_product_image_fullsize'		=> $variant_product_image_fullsize,
					  	'variant_product_ean'				=> $variant->product_ean,
					  	'variant_product_mpn'				=> $variant->product_mpn,
					  	'variant_product_upc'				=> $variant->product_upc,
						'variant_css_classes'				=> $variant_css_class,
					  	//Additional child details - useful for custom coding
					  	'get_variant_images'				=> explode(';', $variant->product_image),
					  	//Modules
					  	'shopit:specialoffers'				=> $variant_special_offers,
				);

				// Get custom fields if any for this variant and add to array so we
				// can use in the view as $variables or {template_tags}
				// - Each custom field is a database column
				if ($variant_customfield_templates) {
					foreach($variant_customfield_templates as $variant_customfield_template) {
						$variant_custom_field_col = $variant_customfield_template->custom_field_label;
						$variant_customfield_template_label = str_replace('custom_', 'variant_custom:', $variant_customfield_template->custom_field_label);
						$variant_customfield_template_label = str_replace('-', '_', $variant_customfield_template_label);
						if ($variant_customfield_template->custom_field_type == 'editor') {
							$variant_custom_field_data = autop($variant->$variant_custom_field_col);
						} else {
							$variant_custom_field_data = trim($variant->$variant_custom_field_col);
						}
						$cf_variant_array[$variant_customfield_template_label] = $variant_custom_field_data;
					}
				}
			
				// Merge the two arrays (variant data and variant's custom fields)
				$variant_array[] = array_merge($this_variant, $cf_variant_array);

				// BEGIN: VARIANT ATTRIBUTES
				// Unserialize this variant's product name
				$variant_name_attributes = unserialize($variant->product_name);

				// Uncomment the line below for debugging
				#var_dump($variant_name_attributes);

				// Capture the variant attributes (from the variant name) if
				// there are any and create our array structure.
				if (is_array($variant_name_attributes)) {

					// Get a list of array keys for this variant and compare them 
					// to the template we created earlier to match the order
					$variant_key_this = array_keys($variant_name_attributes);
					$last_key 		  = count($variant_key_this);
					$variant_key 	  = array_replace($variant_key_this, $variant_key_template); // Match the template
					$variant_key 	  = array_intersect($variant_key, $variant_key_this); // Remove unused keys

					// Attribute 1
					$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['option'] = $variant_name_attributes[$variant_key[0]]['value'];
					if (!empty($variant_name_attributes[$variant_key[0]]['image'])) {
						$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['image']  = $variant_name_attributes[$variant_key[0]]['image'];
					}
					
					// Attribute 2
					if (isset($variant_key[1])) {
						$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['option'] = $variant_name_attributes[$variant_key[1]]['value'];
						if (!empty($variant_name_attributes[$variant_key[1]]['image'])) {
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['image'] = $variant_name_attributes[$variant_key[1]]['image'];
						}
					}
					
					// Attribute 3
					if (isset($variant_key[2])) {
						$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'][$variant_key[2]][$variant_name_attributes[$variant_key[2]]['value']]['option'] = $variant_name_attributes[$variant_key[2]]['value'];
						if (!empty($variant_name_attributes[$variant_key[2]]['image'])) {
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'][$variant_key[2]][$variant_name_attributes[$variant_key[2]]['value']]['image'] = $variant_name_attributes[$variant_key[2]]['image'];
						}
					}
					
					// Attribute 4
					if (isset($variant_key[3])) {
						$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'][$variant_key[2]][$variant_name_attributes[$variant_key[2]]['value']]['data'][$variant_key[3]][$variant_name_attributes[$variant_key[3]]['value']]['option'] = $variant_name_attributes[$variant_key[3]]['value'];
						if (!empty($variant_name_attributes[$variant_key[3]]['image'])) {
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'][$variant_key[2]][$variant_name_attributes[$variant_key[2]]['value']]['data'][$variant_key[3]][$variant_name_attributes[$variant_key[3]]['value']]['image'] = $variant_name_attributes[$variant_key[3]]['image'];
						}
					}
					
					// Attribute 5
					if (isset($variant_key[4])) {
						$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'][$variant_key[2]][$variant_name_attributes[$variant_key[2]]['value']]['data'][$variant_key[3]][$variant_name_attributes[$variant_key[3]]['value']]['data'][$variant_key[4]][$variant_name_attributes[$variant_key[4]]['value']]['option'] = $variant_name_attributes[$variant_key[4]]['value'];
						if (!empty($variant_name_attributes[$variant_key[4]]['image'])) {
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'][$variant_key[2]][$variant_name_attributes[$variant_key[2]]['value']]['data'][$variant_key[3]][$variant_name_attributes[$variant_key[3]]['value']]['data'][$variant_key[4]][$variant_name_attributes[$variant_key[4]]['value']]['image'] = $variant_name_attributes[$variant_key[4]]['image'];
						}
					}
					
					// Set the variant's thumbnail
					$variant_product_image_thumb = ($variant_product_image_default != '') ? sprintf('%s/%d/%d', $variant_product_image_default, $gallery_thumb_size, $gallery_thumb_size) : '';
					
					// Now we need to insert the final product id into the array
					switch ($last_key) {
						case 1:
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'] = $variant->product_id;
							// Image references
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['variant_product_image'] = $variant_product_image_thumb;
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['variant_product_image_default'] = $variant_product_image_default;
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['variant_product_image_fullsize'] = $variant_product_image_fullsize;
							break;
						
						case 2:
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'] = $variant->product_id;
							// Image references
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['variant_product_image'] = $variant_product_image_thumb;
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['variant_product_image_default'] = $variant_product_image_default;
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['variant_product_image_fullsize'] = $variant_product_image_fullsize;
							break;
							
						case 3:
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'][$variant_key[2]][$variant_name_attributes[$variant_key[2]]['value']]['data'] = $variant->product_id;
							// Image references
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'][$variant_key[2]][$variant_name_attributes[$variant_key[2]]['value']]['variant_product_image'] = $variant_product_image_thumb;
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'][$variant_key[2]][$variant_name_attributes[$variant_key[2]]['value']]['variant_product_image_default'] = $variant_product_image_default;
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'][$variant_key[2]][$variant_name_attributes[$variant_key[2]]['value']]['variant_product_image_fullsize'] = $variant_product_image_fullsize;
							break;
						
						case 4:
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'][$variant_key[2]][$variant_name_attributes[$variant_key[2]]['value']]['data'][$variant_key[3]][$variant_name_attributes[$variant_key[3]]['value']]['data'] = $variant->product_id;
							// Image references
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'][$variant_key[2]][$variant_name_attributes[$variant_key[2]]['value']]['data'][$variant_key[3]][$variant_name_attributes[$variant_key[3]]['value']]['variant_product_image'] = $variant_product_image_thumb;
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'][$variant_key[2]][$variant_name_attributes[$variant_key[2]]['value']]['data'][$variant_key[3]][$variant_name_attributes[$variant_key[3]]['value']]['variant_product_image_default'] = $variant_product_image_default;
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'][$variant_key[2]][$variant_name_attributes[$variant_key[2]]['value']]['data'][$variant_key[3]][$variant_name_attributes[$variant_key[3]]['value']]['variant_product_image_fullsize'] = $variant_product_image_fullsize;
							break;
							
						case 5:
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'][$variant_key[2]][$variant_name_attributes[$variant_key[2]]['value']]['data'][$variant_key[3]][$variant_name_attributes[$variant_key[3]]['value']]['data'][$variant_key[4]][$variant_name_attributes[$variant_key[4]]['value']]['data'] = $variant->product_id;
							// Image references
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'][$variant_key[2]][$variant_name_attributes[$variant_key[2]]['value']]['data'][$variant_key[3]][$variant_name_attributes[$variant_key[3]]['value']]['data'][$variant_key[4]][$variant_name_attributes[$variant_key[4]]['value']]['variant_product_image'] = $variant_product_image_thumb;
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'][$variant_key[2]][$variant_name_attributes[$variant_key[2]]['value']]['data'][$variant_key[3]][$variant_name_attributes[$variant_key[3]]['value']]['data'][$variant_key[4]][$variant_name_attributes[$variant_key[4]]['value']]['variant_product_image_default'] = $variant_product_image_default;
							$variant_attr[$variant_key[0]][$variant_name_attributes[$variant_key[0]]['value']]['data'][$variant_key[1]][$variant_name_attributes[$variant_key[1]]['value']]['data'][$variant_key[2]][$variant_name_attributes[$variant_key[2]]['value']]['data'][$variant_key[3]][$variant_name_attributes[$variant_key[3]]['value']]['data'][$variant_key[4]][$variant_name_attributes[$variant_key[4]]['value']]['variant_product_image_fullsize'] = $variant_product_image_fullsize;
							break;
					}

				}
			
			}
			
			// Uncomment the line below for testing
			#echo  "<pre>" . print_r($variant_attr, true) . "</pre>";
			
			if (count($variant_attr) > 0) {
				
				// Open the list
				$var_attr_html .= '<ul id="shopit-variant-attrs">';
				
				// This code is also located with the ajax.php controller which 
				// is called during select dropdown changes
				foreach ($variant_attr as $var_attr_label1 => $var_attr_value1) {
					
					if (is_array($var_attr_value1)) {
						
						$var_attr_html .= sprintf('<li data-title="%s"><span>Select %s</span><i class="icon"></i><ul>', $var_attr_label1, strtolower($var_attr_label1));
						
						foreach ($var_attr_value1 as $var_attr_label2 => $var_attr_value2) {
							if (is_array($var_attr_value2['data'])) {
								$var_attr_option_value = rawurlencode((json_encode($var_attr_value2['data'])));
								$var_attr_data_value = sprintf('data-variant-image="%s"', $var_attr_value2['image']);
								$var_attr_image_src = (isset($var_attr_value2['image'])) ? sprintf('<img src="%s" alt="" /> ', $var_attr_value2['image']) : '';
							} else {
								$var_attr_option_value = $var_attr_value2['data'];
								$var_attr_image = (isset($var_attr_value2['variant_product_image'])) ? sprintf('data-variant-image="%s"', $var_attr_value2['variant_product_image']) : '';
								$var_attr_image_src = (isset($var_attr_value2['image'])) ? sprintf('<img src="%s" alt="" /> ', $var_attr_value2['image']) : '';
								$var_attr_image_default = (isset($var_attr_value2['variant_product_image_default'])) ? sprintf('data-variant-image-default="%s"', $var_attr_value2['variant_product_image_default']) : '';
								$var_attr_image_fullsize = (isset($var_attr_value2['variant_product_image_fullsize'])) ? sprintf('data-variant-image-fullsize="%s"', $var_attr_value2['variant_product_image_fullsize']) : '';
								$var_attr_data_value = sprintf('data-variant="shopit-variant-%s" %s %s %s', $var_attr_option_value, $var_attr_image, $var_attr_image_default, $var_attr_image_fullsize);
							}
							$var_attr_html .= sprintf('<li><label><input type="radio" class="shopit-variant-attr" name="variant-attr-%s" value="%s" %s />%s%s</label></li>', slug($var_attr_label1), $var_attr_option_value, $var_attr_data_value, $var_attr_image_src, $var_attr_label2);
						}
						
						$var_attr_html .= '</ul></li>';
						
					}
					
				}
			
				// Close off the list
				$var_attr_html .= '</ul>';
			
			}
			// END: VARIANT ATTRIBUTES
		}

		//Get delivery cost
		$actual_product_price = ($item->product_saleprice > 0) ? $item->product_saleprice : $item->product_price;
		$delivery_cost = $this->_get_delivery_cost($actual_product_price, $item->product_weight, '+ % delivery (UK)');
		$delivery_cost_exvat = $this->_get_delivery_cost($actual_product_price, $item->product_weight, '+ % delivery (UK)', TRUE);

		//Set the template we need to use for this product
		$template = ($item->product_type == "variation") ? "variation" : "single";

		//Product buy button code (for single item)
		if ($btn_addtobasket) {
			$product_buybtn  = "<input type=\"hidden\" name=\"product_id\" value=\"$item->product_id\" />";
			$product_buybtn .= "<input type=\"hidden\" name=\"redirect_url\" value=\"" . current_url() . "\" />";
			$product_buybtn .= sprintf('<input type="submit" name="submit" value="%s" onclick="%s" />', lang('buy_btn') , "_gaq.push(['_trackEvent', 'Product', 'Buy', 'Add to basket']);");
		} else {
			$product_buybtn = "";
		}

		//Product Gallery - This includes main product images plus any assigned to variations
		$product_gallery = ($product_gallery != "") ? "<ul id=\"shopit-gallery\">\n$product_gallery\n</ul>\n" : "";

		// Get basket summary
		$mybasket = $this->core->mybasket_summary();

		// Price range template tag
		$product_price_range = ($item->product_type == 'variation' && $item->min_price != $item->max_price) ? sprintf('%s - %s', money($item->min_price), money($item->max_price)) : money($item->min_price);
		$product_price_range_exvat = ($item->product_type == 'variation' && $item->min_price != $item->max_price) ? sprintf('%s - %s', money($item->min_price, true, true, false), money($item->max_price, true, true, false)) : money($item->min_price, true, true, false);
		
		// Check if this product's category (parent or ancestor) is disabled 
		// or not. If it is, clear the following variables as this item should
		// not be purchaseable.
		if ($item->cat_hide) {
			$item->product_disabled = 1;
			$qty_select 				= "";
			$btn_addtobasket 		= FALSE;
			$product_buybtn 		= "";
		}

		//Data to pass to view
		$data = array(
			//Core elements of page
			'itemtotal'					=> $mybasket->itemtotal,
			'baskettotal' 				=> $mybasket->baskettotal,
			'itemtotal_int'				=> $mybasket->items,
			'baskettotal_exvat'		 	=> $mybasket->baskettotal_exvat,
			//Page meta tags
			'page_title' 				=> $meta_title . $cat->cat_name,
			'meta_description'			=> $meta_description,
			'meta_keywords'				=> $meta_keywords,
			'meta_custom'				=> $meta_custom,
			//Product details
			'product_id'				=> $item->product_id,
			'product_type'				=> $item->product_type,
			'product_name'				=> $product_name,
			'product_brand'				=> $item->product_brand,
			'product_brand_slug'			=> $item->product_brand_slug,
			'product_tags'				=> $product_tags,
			'product_excerpt'			=> html_entity_decode($item->product_excerpt),
			'product_desc'				=> html_entity_decode($item->product_description),
			'product_file'				=> $product_file,
			'product_attributes'		=> $product_attributes,
			'product_stock_level'		=> $product_stock,
			'product_options'			=> $option_group_array,
			'product_qty'				=> $item->product_qty,
			'qty_select'					=> $qty_select,
			'product_price'				=> money($item->min_price),
			'product_price_exvat'		=> money($item->min_price, true, true, false),
		  	'product_price_dec'			=> money($item->min_price, true, true, true, false),
		  	'product_price_exvat_dec'	=> money($item->min_price, true, true, false, false),
			'product_saleprice'			=> money($item->product_saleprice),
			'product_saleprice_exvat'	=> money($item->product_saleprice, true, true, false),
			'max_price'					=> money($item->max_price),
			'max_price_exvat'			=> money($item->max_price, true, true, false),
			'product_price_range'		=> $product_price_range,
			'product_price_range_exvat'	=> $product_price_range_exvat,
			'product_rrp'				=> money($item->product_price), // -- to be deprecated
			'product_rrp_exvat'			=> money($item->product_price, true, true, false), // -- to be deprecated
			'product_price_default'		=> money($item->product_price),
			'product_price_default_exvat' => money($item->product_price, true, true, false),
			'product_delivery'			=> $delivery_cost,
			'product_delivery_exvat'		=> $delivery_cost_exvat,
			'product_code'				=> $item->product_no,
			'product_ean'				=> $item->product_ean,
			'product_mpn'				=> $item->product_mpn,
			'product_upc'				=> $item->product_upc,
			'product_weight'			=> $item->product_weight,
			'product_condition'			=> $item->product_condition,
			'product_image'				=> $product_image,
			'product_image_default'		=> $product_image_default,
			'product_image_fullsize'		=> $product_image_fullsize,
			'product_gallery'			=> $product_gallery,
			'product_disabled'			=> $item->product_disabled,
			'product_condition'			=> capfirst($item->product_condition),
			'btn_addtobasket'			=> $btn_addtobasket,
			'product_buybtn'			=> $product_buybtn,
			'product_views'				=> $item->product_views,
			//Variations
			'variations'				=> $variant_array,
			'variation_selector'			=> $var_attr_html,
			//Cross-sells
			'relateditems'				=> $this->core->get_cross_sells($item->product_id, 'R', NULL, $this->config->item('thumbnail_width'), $parser_var='relateditems'),
			//Extra product details - useful for custom coding
			'get_product_price' 		=> money($item->product_price),
			'get_product_price_exvat'	=> money($item->product_price_exvat, true, true, false),
			'get_product_qty'			=> $item->product_qty,
			'get_product_images'		=> $item->product_image,
			//Modules
			'shopit:specialoffers'		    => $special_offers,
		);

		//Get custom fields if any for this product and add to array so we
		//can use in the view as $variables or {template_tags}
		// - Each custom field is a database column
		if ($customfield_templates) {
			foreach($customfield_templates as $customfield_template) {
				$custom_field_col = $customfield_template->custom_field_label;
				$customfield_template_label = str_replace('custom_', 'custom:', $customfield_template->custom_field_label);
				$customfield_template_label = str_replace('-', '_', $customfield_template_label);
				if ($customfield_template->custom_field_type == 'editor') {
					$custom_field_data = autop($item->$custom_field_col);
				} else {
					$custom_field_data = trim($item->$custom_field_col);
				}
				$data[$customfield_template_label] = $custom_field_data;
			}
		}

		//Identify this category so we can check if a template is available
		if ($this->uri->segment(2) == '-'):
			$this_category = $this->uri->segment(1);
		else:
			$this_category = $this->uri->segment(2);
		endif;

		//Load snippets
		$this->settings_model->snippets();

		//Load the core templates
		$data['shopit:breadcrumb']	  = $this->parser->parse('boxes/breadcrumb', $breadcrumbs, true);
		$data['shopit:header']  	  = $this->parser->parse('global/header', $data, true);
		$data['shopit:footer']  	  = $this->parser->parse('global/footer', $data, true);
		$data['shopit:sidebar'] 	  = $this->parser->parse('global/sidebar', $data, true);
		$data['shopit:content'] 	  = $this->parser->parse("content/$template", $data, true);
		$data['shopit:search']  	  = $this->parser->parse('boxes/search-box', $data, true);
		$data['shopit:mybasket']  	  = $this->parser->parse('boxes/basket', $data, true);
		$data['shopit:related_items'] = $this->parser->parse("boxes/relateditems", $data, true);
		$data['shopit:categories'] 	  = $this->category_model->createNav();
		$data['shopit:pages']  	  	  = $this->pages_model->createList();

		// Check if there is a custom template for this product
		if (file_exists( $_SERVER['DOCUMENT_ROOT'] . "/store/views/$template-".$item->product_id . '.php')):
			$this->parser->parse("$template-" . $item->product_id,$data);
		elseif (file_exists( $_SERVER['DOCUMENT_ROOT'] . "/store/views/$template-".$this_category . '.php')):
			$this->parser->parse("$template-" . $this_category,$data);
		else:
			$this->parser->parse('global/store',$data);
		endif;
		
	}

	#------------------------------------------------------
	# Search store
	# - this function returns the search results from passed
	# - $_REQUEST variable
	#------------------------------------------------------
	function shop() {
			
		global $data, $item;
		
		//Search box has been used...
		if (isset($_REQUEST['q'])):
		
			$KEYWORD = $this->security->xss_clean(strip_tags($_REQUEST['q']));
			$uri_segment = 3;
			$base_url = site_url('search/' . rawurlencode($KEYWORD) . '/');
		
			$page_title 	 = 'Search ';
			$parent_category = 'Search Results';
			$cat_name 		 = 'Search Results for &quot;' . rawurldecode(ucwords($KEYWORD)) . '&quot;';
			
			//Record search in database
			$this->products_model->recordSearch($KEYWORD);

		else:
		//It's a tag, brand or some other link	
			switch ($this->uri->segment(1)) {
			
				case 'tag':
					$KEYWORD 		= rawurldecode($this->uri->segment(2));
					$uri_segment 	= 3;
					$base_url 		= site_url('tag/' . rawurlencode($KEYWORD) . '/');

					$page_title 	= rawurldecode(ucwords($KEYWORD));
					$parent_category= 'Tag:' . rawurldecode(ucwords($KEYWORD));
					$cat_name 		= rawurldecode(ucwords($KEYWORD));
					break;
				
				case 'brand':
					$KEYWORD 		= $this->uri->segment(2);
					$uri_segment 	= 3;
					$base_url 		= site_url('brand/' . $KEYWORD . '/');
					
					$brand_title 	= $this->category_model->getBrandTitle($KEYWORD);
					
					$page_title 	= 'Shop by brand - ' . ($brand_title->product_brand);
					$parent_category= 'Shop by brand - ' . ($brand_title->product_brand);
					$cat_name 		= 'Shop by brand';
					break;				
				
				default:
					$KEYWORD 		= rawurldecode($this->uri->segment(2));
					$uri_segment 	= 3;
					$base_url 		= site_url('search/' . rawurlencode($KEYWORD) . '/');

					$page_title 	= 'Search ';
					$parent_category= 'Search Results';
					$cat_name 		= 'Search Results for &quot;' . rawurldecode(ucwords($KEYWORD)) . '&quot;';
					break;
			}
			
		endif;
		
		// If the keyword is less than 3 characters redirect 
		// them back to the homepage due to too much load
		if (strlen($KEYWORD) < 3) {
			redirect();
		}
		
		//START: Pagination
		$query = $item_count = $this->products_model->getSearchResults(true, $KEYWORD); // Gets product count
		
		$config['total_rows'] 	= $item_count;
		$config['uri_segment'] 	= $uri_segment;
		if ($_GET['perpage'] != '') {
			$config['per_page'] = $this->input->get('perpage');
		} else {
			$config['per_page'] = $this->config->item('products_per_page');
		}

		// Start rebuilding the query string to append to the pagination links
		$get_url = array();

		if (!empty($_GET)) {
			foreach($_GET as $param=>$value) {
				// Ignore the "q", "search" and "submit" parameters
				$ignore = array('q', 'search', 'submit');
				if (!in_array($param, $ignore)) {
					$get_url[$param] = $value;
				}
			}
			$filter_url = "?".http_build_query($get_url);
			$config['url_format'] = "$base_url{offset}$filter_url";
		} else {
			$filter_url = "?";
			$config['url_format'] = $base_url;
		}
		
		$this->pagination->initialize($config);
		//END: Pagination

		//Get product details if exists else redirect to random products
		if ($query > 0):
			$products = $this->products_model->getSearchResults(false, $KEYWORD, $config['per_page'], $this->uri->segment($config['uri_segment']));
			$guesses = false;
		else:
			//show alternatives
			$guesses = true;
			$products = $this->products_model->getAlternatives($KEYWORD,$this->config->item('products_per_page'));
			$cat_desc = '<p>Sorry, we could not find any products that match your request exactly';
			if (count($products) > 0) {
			$cat_desc .= ", but here are some that may be of interest";
			}
			$cat_desc .= ".</p>";
		endif;

		if (!empty($products)) {

			//Product counter
			$p = 0;

			// Create an array to capture all the cat_ids
			$all_cat_ids = array();

			//Get custom field templates
			$customfield_templates = $this->products_model->customFieldTemplates();
			$cf = array();
			
			//Manipulate the data retrieved from the database previously
			//and create a new array to pass through template parser
			foreach ($products as $item){
			
				$p++;

				// Get product url
				$url = $this->_product_url($item);
				
				$product_image = $this->_displayThumbnail($item);

				//Get button/link
				$btn_addtobasket = $this->_btn_addtobasket($item,$url);

				//Define if this item is on sale or not
				$onsale = ($item->product_saleprice > 0) ? TRUE : FALSE;

				//Some useful css classes
				$onsale_css_class = ($onsale) ? "sale-item" : "";
				$css_classes = array("product-$p", "productid-$item->product_id", $item->product_brand_slug, $onsale_css_class, $item->product_type, $item->cat_slug);
				$css_classes = array_filter($css_classes);
				$css_class = implode(" ", $css_classes); //Turn the classes into a space-separated string

				// Get custom fields if any for this product and add to array so we
				// can use in the view as $variables or {template_tags}
				// - Each custom field is a database column
				if ($customfield_templates) {
					foreach($customfield_templates as $customfield_template) {
						$custom_field_col = $customfield_template->custom_field_label;
						$customfield_template_label = str_replace('custom_', 'custom:', $customfield_template->custom_field_label);
						$customfield_template_label = str_replace('-', '_', $customfield_template_label);
						if ($customfield_template->custom_field_type == 'editor') {
							$custom_field_data = autop($item->$custom_field_col);
						} else {
							$custom_field_data = trim($item->$custom_field_col);
						}
						$cf[$customfield_template_label] = $custom_field_data;
					}
				}

				// Price range template tag
				$product_price_range = ($item->product_type == 'variation' && $item->min_price != $item->max_price) ? sprintf('%s - %s', money($item->min_price), money($item->max_price)) : money($item->min_price);
				$product_price_range_exvat = ($item->product_type == 'variation' && $item->min_price != $item->max_price) ? sprintf('%s - %s', money($item->min_price, true, true, false), money($item->max_price, true, true, false)) : money($item->min_price, true, true, false);
				
				$this_product = array(
					'product_count'				=> $p,
					'product_id' 				=> $item->product_id,
					'cat_id' 	 				=> $item->cat_id,
					'product_type'				=> $item->product_type,
					'product_name' 				=> $item->product_name,
					'product_code'				=> $item->product_no,
					'product_brand'				=> $item->product_brand,
					'product_brand_slug'		=> $item->product_brand_slug,
					'product_description' 		=> $item->product_description,
					'product_excerpt' 			=> $item->product_excerpt,
					'product_summary'			=> get_first_paragraph($item->product_description),
					'product_price'				=> money($item->min_price),
					'product_price_exvat'		=> money($item->min_price, true, true, false),
					'product_saleprice'			=> money($item->product_saleprice),
					'product_saleprice_exvat'	=> money($item->product_saleprice_exvat, true, true, false),
					'max_price'					=> money($item->max_price),
					'max_price_exvat'			=> money($item->max_price, true, true, false),
					'product_price_range'		=> $product_price_range,
					'product_price_range_exvat'	=> $product_price_range_exvat,
					'cat_slug'					=> $product_slug,
					'product_slug'				=> $item->product_slug,
					'product_image' 			=> $product_image,
					'btn_addtobasket' 			=> $btn_addtobasket,
					'url'						=> $url,
					'css_classes'				=> $css_class,
					//Extra variables
					'get_product_price' 		=> money($item->product_price),
					'get_product_price_exvat'	=> money($item->product_price_exvat, true, true, false),
					//Modules
					'shopit:specialoffers'		=> $special_offers,
				);
				
				// Merge the two arrays (item data and item's custom fields)
				$items[] = array_merge($this_product, $cf);
				
			}

			// Let's save this cat_id in our array to pass to the filter later
			if (!empty($products)) {
				foreach ($products as $category) {
					if (!in_array($category->cat_id, $all_cat_ids)) {
						$all_cat_ids[] = $category->cat_id;
					}
				}
			}
		
		}

		//Breadcrumb
		$crumb[$parent_category] = "";
		$breadcrumbs = array(
			'breadcrumb_parents' => $parent_category,
			'breadcrumb_child'	 => '',
			'breadcrumb_product' => '',
			'breadcrumb'		 => $this->core->breadcrumb($crumb),
		);

		//Showing 1-n products tag
		if (!$guesses) {
			$from_n = ( $this->uri->segment($config['uri_segment']) > 0 ) ? $this->uri->segment($config['uri_segment']) + 1 : 1;
			
			$to_n_calc = $from_n + $config['per_page'] - 1;
			$to_n = ($config['total_rows'] <= $to_n_calc) ? $config['total_rows'] : $to_n_calc;
			$from_to_label = "$from_n-$to_n";
		} else {
			$from_n 		= 1;
			$to_n_calc 		= 0;
			$to_n 			= $this->config->item('products_per_page');
			$from_to_label 	= "$from_n-$to_n";
			$config['total_rows'] = $this->config->item('products_per_page');
		}

		// Get the url segments only to pass through to
		// layered navigation and sort options below
		$cat_url = str_replace(site_url(), '', $base_url);

		// Get basket summary
		$mybasket = $this->core->mybasket_summary();

		//Data to pass to view
		$data = array(
			//Core elements of page
			'itemtotal'			=> $mybasket->itemtotal,
			'baskettotal' 		=> $mybasket->baskettotal,
			'itemtotal_int'		=> $mybasket->items,
			'baskettotal_exvat' => $mybasket->baskettotal_exvat,
			//Page meta tags
			'page_title' 		=> $page_title,
			'meta_description'	=> '',
			'meta_keywords'		=> '',
			'meta_custom'		=> '',
			//Category details
			'cat_name'			=> $cat_name,
			'cat_id'			=> -1,
			'cat_desc'			=> $cat_desc,
			'cat_excerpt'		=> '',
			'cat_image'			=> '',
			//Layered Navigation
			'layers'			=> $this->filters_model->rebuild($all_cat_ids, $cat_url, $KEYWORD),
			'layers_selected'	=> $this->filters_model->selected($cat_url),
			// Product sorting
			'results_sort'		=> $this->filters_model->sort($cat_url),
			'results_perpage'	=> $this->filters_model->perpage($cat_url, $config['total_rows']),
			'total_products'	=> $config['total_rows'],
			'showing'			=> $from_to_label,
			'pagination'		=> $this->pagination->create_links(),
			//Products
			'item'				=> $items,
			//Templates & messages
			'message'			=> $message,
		);

		//Load snippets
		$this->settings_model->snippets();

		//Load the core templates
		$data['shopit:breadcrumb']	= $this->parser->parse('boxes/breadcrumb', $breadcrumbs, true);
		$data['shopit:header']  	= $this->parser->parse('global/header', $data, true);
		$data['shopit:footer']  	= $this->parser->parse('global/footer', $data, true);
		$data['shopit:sidebar'] 	= $this->parser->parse('global/sidebar', $data, true);
		$data['shopit:content'] 	= $this->parser->parse('content/products', $data, true);
		$data['shopit:search']  	= $this->parser->parse('boxes/search-box', $data, true);
		$data['shopit:mybasket']  	= $this->parser->parse('boxes/basket', $data, true);
		$data['shopit:categories']  = $this->category_model->createNav();
		$data['shopit:pages']   	= $this->pages_model->createList();
		if ($item_count > 0) {
			$data['shopit:sort_options']= $this->parser->parse('boxes/sort', $data, true);
			if (library_exists('filters')) {
				$data['shopit:layers'] = $this->parser->parse('boxes/layers', $data, true);
			} else {
				$data['shopit:layers'] = "";
			}
		} else {
			$data['shopit:sort_options']= "";
			$data['shopit:layers']		= "";
		}
		
		$this->parser->parse('global/store',$data);

	}

	#------------------------------------------------------
	# Collections
	#------------------------------------------------------
	function collections() {
			
		global $data,$item;

		//Get data
		$collection = $this->products_model->getThisCollection($this->uri->segment(2));

		//Get product details if exists
		if (!empty($collection)) {

			//Manipulate the data retrieved from the database previously
			//and create a new array to pass through template parser
			
			//Custom page heading (H1)
			if ($collection->collection_custom_heading != '') {
				$cat_name = ($collection->collection_custom_heading);
			} else {
				$cat_name = ($collection->collection_name);
			}
			$cat_desc = $collection->collection_desc;

			if ($collection->collection_meta_title != ''):
				$collection_title = $collection->collection_meta_title;
			else:
				$collection_title = $collection->collection_name;
			endif;
				
			if ($collection->collection_meta_description != ''):
				$meta_description = '<meta name="description" content="' . format_meta($collection->collection_meta_description) . '"/>';
			else:
				$meta_description = "";
			endif;

			if ($collection->collection_meta_keywords != ''):
				$meta_keywords = '<meta name="keywords" content="' . format_meta($collection->collection_meta_keywords,'keywords') . '"/>';
			else:
				$meta_keywords = "";
			endif;

			$meta_custom = ($collection->collection_meta_custom != '') ? $collection->collection_meta_custom : '';

			//START: Pagination
			$base_url = site_url("collections/$collection->collection_slug/");
			
			// Get the total number of products in this collection
			$query = $this->products_model->countItemsInCollection($collection->collection_id, FALSE);
			$item_count = count($query);
			
			$config['total_rows'] 	= $item_count;
			$config['uri_segment'] 	= 3;
			$config['per_page'] 	= $this->config->item('products_per_page');

			if ($_GET['perpage'] != '') {
				$config['per_page'] = $this->input->get('perpage');
			} else {
				$config['per_page'] = $this->config->item('products_per_page');
			}
	
			// Start rebuilding the query string to append to the pagination links
			$get_url = array();
	
			if (!empty($_GET)) {
				foreach($_GET as $param=>$value) {
					// Ignore the "q", "search" and "submit" parameters
					$ignore = array('q', 'search', 'submit');
					if (!in_array($param, $ignore)) {
						$get_url[$param] = $value;
					}
				}
				$filter_url = "?".http_build_query($get_url);
				$config['url_format'] = "$base_url{offset}$filter_url";
			} else {
				$filter_url = "?";
				$config['url_format'] = $base_url;
			}

			$this->pagination->initialize($config);
			//END: Pagination

			$collection_items = $this->products_model->getItemsInCollection($collection->collection_id, $config['per_page'], $this->uri->segment(3));
			
			//For each collection item...
			if ($collection_items) {

				// Product counter
				$p = 0;
				
				// Create an array to capture all the cat_ids
				$all_cat_ids = array();

				// Get custom field templates
				$customfield_templates = $this->products_model->customFieldTemplates();
				$cf = array();
						
				// Now start the loop through each item
				foreach ($collection_items as $item) {
					
					$p++;
					
					//Get product url
					$url = $this->_product_url($item);
					
					//Define if this item is on sale or not
					$onsale = ($item->product_saleprice > 0) ? TRUE : FALSE;

					//Some useful css classes
					$onsale_css_class = ($onsale) ? "sale-item" : "";
					$css_classes = array("product-$p", "productid-$item->product_id", $item->product_brand_slug, $onsale_css_class, $item->product_type, $item->cat_slug);
					$css_classes = array_filter($css_classes);
					$css_class = implode(" ", $css_classes); //Turn the classes into a space-separated string

					// Get custom fields if any for this product and add to array so we
					// can use in the view as $variables or {template_tags}
					// - Each custom field is a database column
					if ($customfield_templates) {
						foreach($customfield_templates as $customfield_template) {
							$custom_field_col = $customfield_template->custom_field_label;
							$customfield_template_label = str_replace('custom_', 'custom:', $customfield_template->custom_field_label);
							$customfield_template_label = str_replace('-', '_', $customfield_template_label);
							if ($customfield_template->custom_field_type == 'editor') {
								$custom_field_data = autop($item->$custom_field_col);
							} else {
								$custom_field_data = trim($item->$custom_field_col);
							}
							$cf[$customfield_template_label] = $custom_field_data;
						}
					}

					// Price range template tag
					$product_price_range = ($item->product_type == 'variation' && $item->min_price != $item->max_price) ? sprintf('%s - %s', money($item->min_price), money($item->max_price)) : money($item->min_price);
					$product_price_range_exvat = ($item->product_type == 'variation' && $item->min_price != $item->max_price) ? sprintf('%s - %s', money($item->min_price, true, true, false), money($item->max_price, true, true, false)) : money($item->min_price, true, true, false);
					
					$this_product = array(
						'product_count'				=> $p,
						'product_id' 				=> $item->product_id,
						'cat_id' 	 				=> $item->cat_id,
						'product_type'				=> $item->product_type,
						'product_name' 				=> $item->product_name,
						'product_code'				=> $item->product_no,
						'product_brand'				=> $item->product_brand,
						'product_brand_slug'		=> $item->product_brand_slug,
						'product_description' 		=> $item->product_description,
						'product_excerpt' 			=> $item->product_excerpt,
						'product_summary'			=> get_first_paragraph($item->product_description),
						'product_price'				=> money($item->min_price),
						'product_price_exvat'		=> money($item->min_price, true, true, false),
						'product_saleprice'			=> money($item->product_saleprice),
						'product_saleprice_exvat'	=> money($item->product_saleprice_exvat, true, true, false),
						'max_price'					=> money($item->max_price),
						'max_price_exvat'			=> money($item->max_price, true, true, false),
						'product_price_range'		=> $product_price_range,
						'product_price_range_exvat'	=> $product_price_range_exvat,
						'cat_slug'					=> $product_slug,
						'product_slug'				=> $item->product_slug,
						'product_image' 			=> $this->_displayThumbnail($item),
						'btn_addtobasket'			=> $this->_btn_addtobasket($item,$url),
						'url' 						=> $url,
						'css_classes'				=> $css_class,
						//Extra variables
						'get_product_price' 		=> money($item->product_price),
						'get_product_price_exvat'	=> money($item->product_price_exvat, true, true, false),
						//Modules
						'shopit:specialoffers'		=> $special_offers,
					);
					
					// Merge the two arrays (item data and item's custom fields)
					$items[] = array_merge($this_product, $cf);
				
				}
				
				// Let's save this cat_id in our array to pass to the filter later
				foreach ($query as $category) {
					if (!in_array($category->cat_id, $all_cat_ids)) {
						$all_cat_ids[] = $category->cat_id;
					}
				}
			
			}
				
		} else {
			//No collection found -> redirect to homepage
			redirect('','location',301);
		}

		//Breadcrumb
		$crumb[$collection_title] = "";
		$breadcrumbs = array(
			'breadcrumb_parents' => '',
			'breadcrumb_child'	 => ($collection_title),
			'breadcrumb_product' => '',
			'breadcrumb'		 => $this->core->breadcrumb($crumb),
		);

		//Showing 1-n products tag
		$from_n = ( $this->uri->segment($config['uri_segment']) > 0 ) ? $this->uri->segment($config['uri_segment']) + 1 : 1;
		
		$to_n_calc = $from_n + $config['per_page'] - 1;
		$to_n = ($config['total_rows'] <= $to_n_calc) ? $config['total_rows'] : $to_n_calc;
		$from_to_label = "$from_n-$to_n";

		// Get the url segments only to pass through to
		// layered navigation and sort options below
		$cat_url = str_replace(site_url(), '', $base_url);

		// Get basket summary
		$mybasket = $this->core->mybasket_summary();

		// Get category image
		if ($collection->collection_image != '') { 
			$cat_image = sprintf('<img src="%sdocs/%s" alt="%s" title="%s" />', base_url(), $collection->collection_image, $collection_title, $collection_title);
			$cat_image_file = sprintf('%sdocs/%s', base_url(), $collection->collection_image);
		} else {
			$cat_image = "";
			$cat_image_file = "";
		}

		//Data to pass to view
		$data = array(
			//Core elements of page
			'itemtotal'			=> $mybasket->itemtotal,
			'baskettotal' 		=> $mybasket->baskettotal,
			'itemtotal_int'		=> $mybasket->items,
			'baskettotal_exvat' => $mybasket->baskettotal_exvat,
			//Page meta tags
			'collection_id'		=> $collection->collection_id,
			'collection_group'	=> $collection->group_label,
			'page_title' 		=> ($collection_title),
			'meta_description'	=> $meta_description,
			'meta_keywords'		=> $meta_keywords,
			'meta_custom'		=> $meta_custom,
			//Category details
			'cat_name'			=> $cat_name,
			'cat_id'			=> -1,
			'cat_desc'			=> $cat_desc,
			'cat_excerpt'		=> '',
			'cat_image'			=> $cat_image,
			'cat_image_file'		=> $cat_image_file,
			//Product sorting
			'results_sort'		=> $this->filters_model->sort($cat_url),
			'results_perpage'	=> $this->filters_model->perpage($cat_url, $config['total_rows']),
			'total_products'	=> $config['total_rows'],
			'showing'			=> $from_to_label,
			'pagination'		=> $this->pagination->create_links(),
			//Products
			'item'				=> $items,
			//Templates & messages
			'message'			=> '',
		);
	
		//Load snippets
		$this->settings_model->snippets();

		//Load the core templates
		$data['shopit:breadcrumb']	= $this->parser->parse('boxes/breadcrumb', $breadcrumbs, true);
		$data['shopit:header']  	= $this->parser->parse('global/header', $data, true);
		$data['shopit:footer']  	= $this->parser->parse('global/footer', $data, true);
		$data['shopit:sidebar'] 	= $this->parser->parse('global/sidebar', $data, true);
		$data['shopit:content'] 	= $this->parser->parse('content/products', $data, true);
		$data['shopit:search']  	= $this->parser->parse('boxes/search-box', $data, true);
		$data['shopit:mybasket']  	= $this->parser->parse('boxes/basket', $data, true);
		$data['shopit:categories']  = $this->category_model->createNav();
		$data['shopit:pages']   	= $this->pages_model->createList();
		$data['shopit:sort_options']= $this->parser->parse('boxes/sort', $data, true);
		$data['shopit:layers']		= "";

		// Check if there is a custom template for this collection
		if (file_exists( $_SERVER['DOCUMENT_ROOT'] . '/store/views/collection-'.$collection->collection_slug . '.php')):
			$this->parser->parse('collection-' . $collection->collection_slug, $data);
		elseif(file_exists($_SERVER['DOCUMENT_ROOT'] . '/store/views/collections.php')):
			$this->parser->parse('collections', $data);
		else:
			$this->parser->parse('global/store',$data);
		endif;

	}

	#------------------------------------------------------
	# Show document content
	# - Includes static pages i.e those not in the database
	#   which use templates called page-{segment_2}.php where
	#	segment_2 is the page slug.
	#
	# - The following code must be placed in the static page
	#	template (preferably at the top):
	#
	#	global $meta;
	#	$meta = array(
	#		'title' => 'Static Page 1',
	#		'description' => 'This is the meta description',
	#		'keywords' => 'This is for the meta keywords',
	# 		'hide_templates' => true (Prevents header, footer, etc templates loading)
	#	);
	#------------------------------------------------------
	function document() {
	
		global $data;

		manage_redirection();

		//First check if this is a static page, if it exists then use it...
		if (file_exists( $_SERVER['DOCUMENT_ROOT'] . '/store/views/page-' . $this->uri->segment(2) . '.php' )) {
	
			global $meta;

			//Preload the document to obtain the $meta vars
			$preload = $this->load->view('page-' . $this->uri->segment(2), array('preload' => true), true);
			
			//Get the vars and set them for the site's page
			$page_title 		= $meta['title'];
			$meta_description 	= '<meta name="description" content="' . $meta['description'] . '"/>';
			$meta_keywords 		= '<meta name="keywords" content="' . $meta['keywords'] . '"/>';
			
			$page_template = 'page-' . $this->uri->segment(2);
	
		} else {
		//Not a static page, so continue as normal by accessing the db...

			//Get data
			$page = $this->pages_model->getPage($this->uri->segment(2));
			
			if (!empty($page)):
				
				if ($page->page_custom_heading != '') {
					$doc_title = $page->page_custom_heading;
				} else {
					$doc_title = $page->page_name;
				}
				$doc_content = format_content(html_entity_decode($page->page_content));
				$page_title = ($page->page_meta_title != '') ? $page->page_meta_title : $doc_title;		
				
				if ($page->page_meta_description != ''):
					$meta_description = '<meta name="description" content="' . format_meta($page->page_meta_description) . '"/>';
				endif;
				
				if ($page->page_meta_keywords != ''):
					$meta_keywords = '<meta name="keywords" content="' . format_meta($page->page_meta_keywords,'keywords') . '"/>';
				endif;

				$meta_custom = ($page->page_meta_custom != '') ? $page->page_meta_custom : '';
			
			else:
			
				$this->output->set_status_header('404');
				$doc_title 	 = 'Page not found!';
				$doc_content = 'Sorry, the page you were looking for could not be found.';
				$page_title  = 'Page not found!';
				$meta_description = '';
				$meta_keywords = '';
				$meta_custom   = '';	
	
			endif;
	
			//Check for page template else use default
			if (!empty($page->page_template)):
				if (file_exists( $_SERVER['DOCUMENT_ROOT'] . '/store/views/'.$page->page_template)):
					$page_template = $page->page_template;
				else:
					$page_template = 'content/document';
				endif;
			else:
				if (file_exists( $_SERVER['DOCUMENT_ROOT'] . '/store/views/page-'.$page->page_id . '.php')):
					$page_template = 'page-' . $page->page_id;
				else:
					$page_template = 'content/document';
				endif;
			endif;

		}

		// Get basket summary
		$mybasket = $this->core->mybasket_summary();

		//Data to pass to view
		$data = array(
			//Core elements of page
			'itemtotal'			=> $mybasket->itemtotal,
			'baskettotal' 		=> $mybasket->baskettotal,
			'itemtotal_int'		=> $mybasket->items,
			'baskettotal_exvat' => $mybasket->baskettotal_exvat,
			//Page meta tags
			'page_title' 		=> $page_title,
			'meta_description'	=> $meta_description,
			'meta_keywords'		=> $meta_keywords,
			'meta_custom'		=> $meta_custom,
			'doc_title'			=> $doc_title,
			'doc_content'		=> $doc_content,
			'page_id'			=> $page->page_id,
			'page_slug'			=> $page->page_slug,
			//Templates & messages
			'preload'			=> false,
		);

		//Load snippets
		$this->settings_model->snippets();

		//Breadcrumbs
		$crumb[$page_title] = "";
		$breadcrumbs = array(
			'breadcrumb_parents' => $page_title,
			'breadcrumb_child'	 => "",
			'breadcrumb_product' => "",
			'breadcrumb'		 => $this->core->breadcrumb($crumb),
		);

		//Load the core templates
		if ($meta['hide_templates'] != TRUE) {
			$data['shopit:breadcrumb']	= $this->parser->parse('boxes/breadcrumb', $breadcrumbs, true);
			$data['shopit:header']  	= $this->parser->parse('global/header', $data, true);
			$data['shopit:footer']  	= $this->parser->parse('global/footer', $data, true);
			$data['shopit:sidebar'] 	= $this->parser->parse('global/sidebar', $data, true);
			$data['shopit:content'] 	= $this->parser->parse($page_template, $data, true);
			$data['shopit:search']  	= $this->parser->parse('boxes/search-box', $data, true);
			$data['shopit:mybasket']  	= $this->parser->parse('boxes/basket', $data, true);
			$data['shopit:categories']  = $this->category_model->createNav();
			$data['shopit:pages']   	= $this->pages_model->createList();
		} else {
			$data['shopit:breadcrumb']	= "";
			$data['shopit:header']  	= "";
			$data['shopit:footer']  	= "";
			$data['shopit:sidebar'] 	= "";
			$data['shopit:content'] 	= $this->parser->parse($page_template, $data, true);
			$data['shopit:search']  	= "";
			$data['shopit:mybasket']  	= "";
			$data['shopit:categories']  = "";
			$data['shopit:pages']   	= "";
		}

		$this->parser->parse('global/document', $data);
	
	}

	#------------------------------------------------------
	# View Basket
	# - note: this works via sessions and the db
	#------------------------------------------------------
	function basket(){
		//Include core basket code
		global $data,$shipping_encrypted,$shipping_rulename;
		include_once($_SERVER['DOCUMENT_ROOT'].'/store/controllers/includes/basket.php');
	}
	
	#------------------------------------------------------
	# Display thumbnails
	# - on categories pages & related/similar items
	#   and featured product, collections
	#------------------------------------------------------
	function _displayThumbnail($item, $size=null) {

		$thumb_size = ($size == null) ? $this->config->item('thumbnail_width') : $size;

		if ($item->product_image != null):
			$image = explode(';',$item->product_image);
			$product_image = '<img src="'.site_url('image/resize/' . $image[0] . '/'.$thumb_size.'/'.$thumb_size).'" alt="' . htmlspecialchars($item->product_name) . '" title="' . htmlspecialchars($item->product_name) . '" />';
		else:
			$product_image = '<img src="/site/images/nophoto.png" alt="Photo coming soon" width="' . $thumb_size . '" height="' . $thumb_size . '" />';
		endif;
		
		return $product_image;
	
	}

	#------------------------------------------------------
	# Product URL
	# - format the product url(slug) to include seo words
	# - pass the cat_father_id and $item array (product data)
	#------------------------------------------------------
	function _product_url($item) {
		
		if($item->cat_father_id > 0):
				
			if ($item->parent_cat_father_id > 0):
				$url = site_url($item->ancestor_cat_slug . '/' . $item->parent_cat_slug . '/' . $item->cat_slug . '/' . $item->product_slug . '/' . $item->product_id);
			else:
		    	$url = site_url($item->parent_cat_slug . '/' . $item->cat_slug . '/-/' . $item->product_slug . '/' . $item->product_id);
			endif;
		
		else:
		    $url = site_url($item->cat_slug . '/-/-/' . $item->product_slug . '/' . $item->product_id);
		endif;
		
		return $url;
		
	}

	#------------------------------------------------------
	# Add to basket button
	# - $item is an array (item data)
	# - $url is the product's url which is usually generated
	#   before this function is called
	# - $display can be "auto" or "link"
	#	 - "auto" will display either button or link
	#	 - "link" will display a hyperlink
	#------------------------------------------------------
	function _btn_addtobasket($item,$url,$display="link") {

		//Check if product is already in this user's basket
		//If not, then display the add to basket form/button
		//Otherwise, don't show it...
		if ($display == "link"):
			$btn_addtobasket = '<a href="' . $url . '"><img src="/site/images/btn-addtobasketsmall.gif" alt="" border="0"/></a>';	
		else:
			if ($this->basket_model->isProductInMyBasket($item->product_id) == false):
			
				if ($item->product_qty > 0 || $this->config->item('outofstock_purchases')=='true'):
					//If item has product options show hyperlink
					if ($this->products_model->has_product_options($item->product_id)):
						$btn_addtobasket = '<a href="' . $url . '"><img src="/site/images/btn-addtobasketsmall.gif" alt="" border="0"/></a>';
					else: //else show button
						$btn_addtobasket =	'<div class="product-addtobasket">';
						$btn_addtobasket .= '<form action="' . site_url('basket/additem'). '" method="post">';
						$btn_addtobasket .= '<input type="hidden" name="qty" value="1"/>';
						$btn_addtobasket .= '<input type="hidden" name="product_id" value="' . $item->product_id . '"/>';
						$btn_addtobasket .= '<input type="image" src="/site/images/btn-addtobasketsmall.gif" name="submit" />';  
						$btn_addtobasket .= '</form>';
						$btn_addtobasket .= '</div>';
					endif;
				else:
					if ($this->config->item('stock_showamount') == 'true'):
					$btn_addtobasket = '<span class="outofstock">OUT OF STOCK</span>';
					else:
					$btn_addtobasket = '<a href="' . $url . '"><img src="/site/images/btn-addtobasketsmall.gif" alt="" border="0"/></a>';
					endif;
				endif;
			
			else:
				$btn_addtobasket = '';
			endif;
		endif;
	
		return $btn_addtobasket;
	
	}

	#------------------------------------------------------
	# Update price by product options
	# - on item page
	#------------------------------------------------------
	function updatepricebyoptions() {
		
		//Get the option prices
		$option_ids = explode('-',$_POST['option_id']);
		
		foreach($option_ids as $option) {
			$product_option = $this->products_model->getProductOption($option);
			$option_price = $option_price + $product_option->option_price;			
		} 
		
		//Get the price of the current product_id
		$item = $this->products_model->getPrice($_POST['product_id']);
			
		//If no sale price applied
		if ($item->product_saleprice == '0.00' || $item->product_saleprice == ''):
			
			$product_saleprice = '0.00';
			
			$product_price = base_rate($item->product_price + $option_price) * the_vat_rate();
			$product_price_exvat = base_rate($item->product_price + $option_price);
			
		//Else sale price is applied
		else:
			
			$product_price = ((base_rate($item->product_price + $option_price)) * the_vat_rate()) + (base_rate($item->product_price + $option_price));
			$product_price_exvat = base_rate($item->product_price) + $option_price;
			$product_saleprice = ($item->product_saleprice + base_rate($option_price)) * the_vat_rate();
			$product_saleprice_exvat = $item->product_saleprice + base_rate($option_price);
			
		endif;

		// We'll round down the figure, so 6.997 will be 6.99
		$product_price 	   = money($product_price, true, true, false, false);
		$product_saleprice = money($product_saleprice, true, true, false, false); 

		print json_encode(
				array(
					'product_price'	 	  => $product_price,
					'product_price_exvat' => $product_price_exvat,
					'product_saleprice'	  => $product_saleprice,
					'product_saleprice_exvat' => $product_saleprice_exvat,
					)
			  );
	
	}
	
	
	#------------------------------------------------------
	# Update shipping by country
	# - via ajax
	#------------------------------------------------------
	function updateshippingbycountry() {
	
		global $shipping_select,$shipping_value,$shipping_rulename;

		$shippingrule = $this->shipping_model->getShippingRules($_POST['country']);
		
		//This is the total of the items only
		$total_price  = base64_decode($_POST['total']); //Decode encrypted post total
		$total_weight = base64_decode($_POST['weight']); //Decode encrypted weight total (this is too check against rules, it doesn't change)
		
		//Get the cat ids of the items in the basket
		$cat_ids = base64_decode($this->input->post('cat_ids'));
		$cat_ids = explode(',', $cat_ids);
		
		if ($shippingrule > 0):
		
		foreach ($shippingrule as $shippingcost) {

			//!!! Check criteria for category value
			// This takes priority, so no other rules should be displayed
			if (in_array($shippingcost->operation, $cat_ids)) {
				
				if ($shippingcost->criteria == 'category') {
					// if cat_id is in the cat_ids array
					$this->_updateshippingbycountry_values($shippingcost, $delivery_markup);
				}
				//Stop the foreach loop
				break;
	
			} else {

				//Check criteria for total value
				if ($shippingcost->criteria == 'total'):
				
					switch($shippingcost->operation) {
					
						case 'less than':
							if ($total_price <= $shippingcost->value):
								$this->_updateshippingbycountry_values($shippingcost);
							endif;
							break;
							
						case 'more than':
							if ($total_price >= $shippingcost->value):						
								$this->_updateshippingbycountry_values($shippingcost);
							endif;
							break;
							
						case 'equal to':
							if ($total_price == $shippingcost->value):
								$this->_updateshippingbycountry_values($shippingcost);
							endif;
							break;
											
						case 'between':
							if ($total_price >= $shippingcost->value && $total_price <= $shippingcost->value2):
								$this->_updateshippingbycountry_values($shippingcost);
							endif;
							break;
	
					}
	
				//Check criteria for total weight value
				elseif ($shippingcost->criteria == 'weight'):
				
					switch($shippingcost->operation) {
					
						case 'less than':
							if ($total_weight <= $shippingcost->value):
								$this->_updateshippingbycountry_values($shippingcost);
							endif;
							break;
							
						case 'more than':
							if ($total_weight >= $shippingcost->value):													
								$this->_updateshippingbycountry_values($shippingcost);
							endif;
							break;
							
						case 'equal to':
							if ($total_weight == $shippingcost->value):
								$this->_updateshippingbycountry_values($shippingcost);
							endif;
							break;
	
						case 'between':
							if ($total_weight >= $shippingcost->value && $total_weight <= $shippingcost->value2):
								$this->_updateshippingbycountry_values($shippingcost);
							endif;
							break;
	
					}
				
				endif;
				
			}

		}
		
		else:
			$this->_updateshippingbycountry_values();
		endif;
	
		$shipping_method = $shipping_rulename[0];
	
		//Calculate VAT (must include shipping cost - legal requirement!)
		$vat_exempt_status = $this->shipping_model->getCountryVATStatus($this->input->post('country')); //0 or 1
		
		if ($vat_exempt_status == 1) {
			$vat = number_format(0,2,'.','');
		} else {
			$vat = $this->config->item('vat_rate') * ($total_price + $shipping_value[0]);
			$vat = money($vat, true, true, false, false, true);
		}

		$vat_encrypted = $vat;

		print json_encode(
				array(
					'shipping_select'	 => $shipping_select,
					'shipping_method'	 => $shipping_method,
					'shipping_txt' 		 => money($shipping_value[0], true, true, false),
					'shipping_encrypted' => base64_encode($shipping_value[0]),
					'total_topay'		 => money($total_price + $shipping_value[0] + $vat_encrypted, true, true, false),
					'vat'				 => $vat,
					'vat_encrypted'		 => base64_encode($vat_encrypted),
					)
			  );

	}

	function _updateshippingbycountry_values($shippingcost=null) {
		
		global $shipping_select,$shipping_value,$shipping_rulename;
		
		if ($shippingcost != null) {
			//Display dropdown options...
			$shipping_select .= '<option value="' . base64_encode($shippingcost->shipping) . '">' . $shippingcost->rule_name . ' (' . $this->config->item('currency') . $shippingcost->shipping . ')</option>';
			
			//This is used to get the first item in the array (the default select on the shipping dropdown)
			$shipping_value[] = $shippingcost->shipping;
			$shipping_rulename[] = $shippingcost->rule_name;
		} else {
			//Else show default config settings where no rules are setup
			//Display dropdown options...
			$shipping_select .= '<option value="' . base64_encode($this->config->item('default_shipping_cost')) . '">' . $this->config->item('default_shipping_name') . ' (' . $this->config->item('currency') . $this->config->item('default_shipping_cost') . ')</option>';
			
			//This is used to get the first item in the array (the default select on the shipping dropdown)
			$shipping_value[] = $this->config->item('default_shipping_cost');
			$shipping_rulename[] = $this->config->item('default_shipping_name');
		}
		
	}

	#------------------------------------------------------
	# Update total cost by delivery dropdown
	# - via ajax
	#------------------------------------------------------
	function updatetotalbyshipping() {
		
		//This is the total of the items only
		$total_price 	 = base64_decode($_POST['total']); //Decode encrypted post total
		$shipping_value  = base64_decode($_POST['shipping_value']); //Decode encrypted post total
		$shipping_method = explode('(',$_POST['shipping_method']);
		$shipping_method = $shipping_method[0];
		
		//Calculate VAT (must include shipping cost - legal requirement!)
		$vat_exempt_status = $this->shipping_model->getCountryVATStatus($this->input->post('country')); //0 or 1
		
		if ($vat_exempt_status == 1) {
			$vat = number_format(0,2,'.','');
		} else {
			$vat = $this->config->item('vat_rate') * ($total_price + $shipping_value);
			$vat = money($vat, true, true, false, false, true);
		}
		
		$vat_encrypted = $vat;
			
		print json_encode(
				array(
					'shipping_txt' 		 => money($shipping_value, true, true, false),
					'shipping_method'	 => $shipping_method,
					'shipping_encrypted' => base64_encode($shipping_value),
					'total_topay'		 => money($total_price + $shipping_value + $vat_encrypted, true, true, false),
					'vat'				 => $vat,
					'vat_encrypted'		 => base64_encode($vat_encrypted),
					)
			  );

	}

	#------------------------------------------------------
	# Get delivery cost for this item
	# - $text can be passed the wording. % is replaced
	# 	with the price within the function
	#------------------------------------------------------
	function _get_delivery_cost($product_price, $product_weight, $text='%', $output_exvat=false, $country="United Kingdom") {

		$shippingrule = $this->shipping_model->getShippingRules($country);

		if ($shippingrule > 0):
		
		foreach ($shippingrule as $shippingcost) {

			//Check criteria for total value
			if ($shippingcost->criteria == 'total'):
			
				switch($shippingcost->operation) {
				
					case 'less than':
						if ($product_price <= $shippingcost->value):
							$shipping[] = $shippingcost->shipping;
						endif;
						break;

					case 'more than':
						if ($product_price >= $shippingcost->value):						
							$shipping[] = $shippingcost->shipping;
						endif;
						break;

					case 'equal to':
						if ($product_price == $shippingcost->value):
							$shipping[] = $shippingcost->shipping;
						endif;
						break;

					case 'between':
						if ($product_price >= $shippingcost->value && $product_price <= $shippingcost->value2):
							$shipping[] = $shippingcost->shipping;
						endif;
						break;

				}

			//Check criteria for total weight value
			elseif ($shippingcost->criteria == 'weight'):
			
				switch($shippingcost->operation) {
				
					case 'less than':
						if ($product_weight <= $shippingcost->value):
							$shipping[] = $shippingcost->shipping;
						endif;
						break;
						
					case 'more than':
						if ($product_weight >= $shippingcost->value):
							$shipping[] = $shippingcost->shipping;
						endif;
						break;
						
					case 'equal to':
						if ($product_weight == $shippingcost->value):
							$shipping[] = $shippingcost->shipping;
						endif;
						break;

					case 'between':
						if ($product_weight >= $shippingcost->value && $product_weight <= $shippingcost->value2):
							$shipping[] = $shippingcost->shipping;
						endif;
						break;

				}
			
			endif;

		}
		
		else:
			$shipping[] = $this->config->item('default_shipping_cost');
		endif;
	
		//Add the currency and money format to the price using
		//the first key in the array
		if ($shipping[0] > 0) {
			$delivery_cost = money($shipping[0]);
			$delivery_cost_exvat = money($shipping[0]);
		} else {
			$delivery_cost = 'free';
			$delivery_cost_exvat = 'free';
		}
		
		if ($output_exvat == true) {
			$output_var = $delivery_cost_exvat;
		} else {
			$output_var = $delivery_cost;
		}
		
		//Replace % from the $text var with the delivery price
		$output = str_replace('%', $output_var, $text);
		
		return $output;

	}
	
	#------------------------------------------------------------------------------------------------------------
	#------------------------------------------------------------------------------------------------------------
	//!!! Module: My Account
	#------------------------------------------------------------------------------------------------------------
	#------------------------------------------------------------------------------------------------------------
	function myaccount() {
	
		global $data;

		$this->load->library('form_validation');
		$this->load->library('email');

		switch ($this->uri->segment(3)) {
		
			//Check login status and retrieve customer's details
			case "login":
							
				$account = $this->myaccount->checkuser($_POST['AccountEmail'], $_POST['AccountPass']);
				
				if ($account != ''):
				
					//Set session
					$account_data = array(
						'account_loggedin' 	=> TRUE,
						'account_id'		=> $account->account_id,
						'account_user'		=> $account->account_user,
						'account_title' 	=> $account->account_title,
						'account_firstname' => $account->account_firstname,
						'account_surname' 	=> $account->account_surname,
						'account_company'	=> $account->account_company,
						'account_address1'	=> $account->account_address1,
						'account_address2' 	=> $account->account_address2,
						'account_city' 		=> $account->account_city,
						'account_postcode' 	=> $account->account_postcode,
						'account_country' 	=> $account->account_country,
						'account_phone' 	=> $account->account_phone,
						'pref_newsletter'	=> $account->pref_newsletter,
					); 
					
					$this->session->set_userdata($account_data);
					
					//Update last login time
					$this->myaccount->update_lastlogin_time($account->account_id);
					redirect($_REQUEST['redirect_url']);
					
				else:
					
					$this->session->set_flashdata('notice','<p class="loginerror">The username/password you entered was not recognised by our system.</p>');
					redirect($_REQUEST['current_url']);
				
				endif;
								
				break;
				
			//Logout of my account, unset the session data
			case "logout":
			
				$account_data = array(
					'account_loggedin' 	=> FALSE,
					'account_id'		=> '',
					'account_user'		=> '',
					'account_title'		=> '',
					'account_firstname' => '',
					'account_surname' 	=> '',
					'account_company'	=> '',
					'account_address1'	=> '',
					'account_address2' 	=> '',
					'account_city' 		=> '',
					'account_postcode' 	=> '',
					'account_country' 	=> '',
					'account_phone' 	=> '',
					'pref_newsletter'	=> '',
				); 
				
				$this->session->unset_userdata($account_data);
				
				redirect();
				break;
				
			//View order
			case "vieworder":
			
				if ($this->myaccount->user_logged_in() != TRUE) {
					redirect(site_url('store/myaccount'));
				}
				else {
				// Get basket summary
				$mybasket = $this->core->mybasket_summary();
				$data = array(
					'page_title' 		=> 'My Account - View Order',
					'meta_description'	=> '',
					'meta_keywords'		=> '',
					'meta_custom'		=> '',
					'content'	 		=> 'cart/myaccount-vieworder',
					'orders'			=> $this->myaccount->get_order($this->uri->segment(4), $this->myaccount->get_info('id')),
					'inventory'			=> $this->basket_model->getInventory($this->uri->segment(4)),
					// Basket summary
					'itemtotal'			=> $mybasket->itemtotal,
					'baskettotal' 		=> $mybasket->baskettotal,
					'itemtotal_int'		=> $mybasket->items,
					'baskettotal_exvat' => $mybasket->baskettotal_exvat,
				);							

				//Load snippets
				$this->settings_model->snippets();

				//Load the core templates
				$data['shopit:header']  = $this->parser->parse('global/header', $data, true);
				$data['shopit:footer']  = $this->parser->parse('global/footer', $data, true);
				$data['shopit:sidebar'] = $this->parser->parse('global/sidebar', $data, true);
				$data['shopit:content'] = $this->parser->parse('content/homepage', $data, true);
				$data['shopit:search']  = $this->parser->parse('boxes/search-box', $data, true);
				$data['shopit:mybasket']  = $this->parser->parse('boxes/basket', $data, true);
				$data['shopit:breadcrumb']  = "";
				$data['shopit:categories'] = $this->category_model->createNav();
				$data['shopit:pages']  = $this->pages_model->createList();
				
				$this->parser->parse('global/cart',$data);
				}
				break;
			
			//Edit user details
			case "edit":
			
				if ($this->myaccount->user_logged_in() != TRUE) {
					redirect(site_url('store/myaccount'));
				}
				else {
				// Get basket summary
				$mybasket = $this->core->mybasket_summary();

				// Get countries list
				$countries = $this->basket_model->getAllCountries();
				foreach($countries as $country){
					$countrySelect[] = $country->country_name;
				}

				$user_country = $this->myaccount->get_info('country');
				$country_key = array_search( $user_country, $countrySelect);

				$data = array(
					'page_title' 		=> 'My Account - Edit Details',
					'meta_description'	=> '',
					'meta_keywords'		=> '',
					'meta_custom'		=> '',
					'content'	 		=> 'cart/myaccount-edit',
					// Basket summary
					'itemtotal'			=> $mybasket->itemtotal,
					'baskettotal' 		=> $mybasket->baskettotal,
					'itemtotal_int'		=> $mybasket->items,
					'baskettotal_exvat' => $mybasket->baskettotal_exvat,
					'countries'			=> $countrySelect,
					'countryCurrent'	=> $country_key
				);

				//Load snippets
				$this->settings_model->snippets();

				//Load the core templates
				$data['shopit:header']  = $this->parser->parse('global/header', $data, true);
				$data['shopit:footer']  = $this->parser->parse('global/footer', $data, true);
				$data['shopit:sidebar'] = $this->parser->parse('global/sidebar', $data, true);
				$data['shopit:content'] = $this->parser->parse('content/homepage', $data, true);
				$data['shopit:search']  = $this->parser->parse('boxes/search-box', $data, true);
				$data['shopit:mybasket']  = $this->parser->parse('boxes/basket', $data, true);
				$data['shopit:breadcrumb']  = "";
				$data['shopit:categories'] = $this->category_model->createNav();
				$data['shopit:pages']  = $this->pages_model->createList();
				
				$this->parser->parse('global/cart',$data);
				}
				break;

			//Save user details
			case "save":

				if ($this->myaccount->user_logged_in() != TRUE) {
					redirect(site_url('store/myaccount'));
				}
				else {

					$this->form_validation->set_message('required', ' ');
					$this->form_validation->set_message('valid_email', ' ');
					$this->form_validation->set_message('matches',' ');
					$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
					
					$this->form_validation->set_rules('BillingFirstname', 'Billing Firstname', 'trim|required|max_length[35]');
					$this->form_validation->set_rules('BillingSurname', 'Billing Surname', 'trim|required|max_length[35]');
					$this->form_validation->set_rules('BillingCompany', 'Billing Company', 'trim|max_length[65]');
					$this->form_validation->set_rules('BillingAddress1', 'Billing Address', 'trim|required|max_length[65]');
					$this->form_validation->set_rules('BillingAddress2', 'Billing Address', 'trim|max_length[65]');
					$this->form_validation->set_rules('BillingCity', 'Billing City/Town', 'trim|required|max_length[35]');
					$this->form_validation->set_rules('BillingPostcode', 'Billing Postal Code', 'trim|required|max_length[15]');
					$this->form_validation->set_rules('BillingCountry', 'Billing Country');
					$this->form_validation->set_rules('Email', 'Email', 'trim|required|valid_email');
					$this->form_validation->set_rules('Phone', 'Telephone', 'trim|required|max_length[20]');
					$this->form_validation->set_rules('Password', 'Password', 'trim');
					$this->form_validation->set_rules('cPassword', 'Confirm Password', 'trim|matches[Password]');
					$this->form_validation->set_rules('pref_newsletter');
					
					if ($this->form_validation->run() == FALSE){
	
						$data = array(
							'page_title' 		=> 'My Account - Edit Details',
							'meta_description'	=> '',
							'meta_keywords'		=> '',
							'meta_custom'		=> '',
							'content'	 		=> 'cart/myaccount-edit',
						);
		
						$this->parser->parse('global/cart',$data);
					
					} else {
						
						if ($this->myaccount->save_account_updates($this->myaccount->get_info('id'))):
						$this->session->set_flashdata('notice','<p class="baskettable ordertable">Details saved successfully.</p>');
						else:
						$this->session->set_flashdata('notice','<p class="baskettable ordertable">Oops! There was an error saving your details, please try again.</p>');
						endif;

						// Get countries and find country name from array via submitted value (key)
						$countries = $this->basket_model->getAllCountries();
						$userCountry = $countries[$_POST['BillingCountry']]->country_name;
	
						//Reset the session
						$account_data = array(
							'account_user'		=> $this->security->xss_clean($_POST['Email']),
							'account_title' 	=> $this->security->xss_clean($_POST['BillingTitle']),
							'account_firstname' => $this->security->xss_clean($_POST['BillingFirstname']),
							'account_surname' 	=> $this->security->xss_clean($_POST['BillingSurname']),
							'account_company'	=> $this->security->xss_clean($_POST['BillingCompany']),
							'account_address1'	=> $this->security->xss_clean($_POST['BillingAddress1']),
							'account_address2' 	=> $this->security->xss_clean($_POST['BillingAddress2']),
							'account_city' 		=> $this->security->xss_clean($_POST['BillingCity']),
							'account_postcode' 	=> $this->security->xss_clean($_POST['BillingPostcode']),
							'account_country' 	=> $this->security->xss_clean($userCountry),
							'account_phone' 	=> $this->security->xss_clean($_POST['Phone']),
							'pref_newsletter'	=> $this->security->xss_clean($_POST['pref_newsletter']),
						); 
						
						$this->session->set_userdata($account_data);
	
						redirect(site_url('store/myaccount/edit'));
					
					}
				
				}

				break;

			// Register user details
			case "register":

				$this->form_validation->set_message('required', ' ');
				$this->form_validation->set_message('valid_email', ' ');
				$this->form_validation->set_message('matches',' ');
				$this->form_validation->set_error_delimiters('<span class="error">', '</span>');
				
				$this->form_validation->set_rules('BillingFirstname', 'Billing Firstname', 'trim|required|max_length[35]');
				$this->form_validation->set_rules('BillingSurname', 'Billing Surname', 'trim|required|max_length[35]');
				$this->form_validation->set_rules('BillingCompany', 'Billing Company', 'trim|max_length[65]');
				$this->form_validation->set_rules('BillingAddress1', 'Billing Address', 'trim|required|max_length[65]');
				$this->form_validation->set_rules('BillingAddress2', 'Billing Address', 'trim|max_length[65]');
				$this->form_validation->set_rules('BillingCity', 'Billing City/Town', 'trim|required|max_length[35]');
				$this->form_validation->set_rules('BillingPostcode', 'Billing Postal Code', 'trim|required|max_length[15]');
				$this->form_validation->set_rules('Email', 'Email', 'trim|required|valid_email');
				$this->form_validation->set_rules('Phone', 'Telephone', 'trim|required|max_length[20]');
				$this->form_validation->set_rules('Password', 'Password', 'trim|required|max_length[35]');
				$this->form_validation->set_rules('cPassword', 'Confirm Password', 'trim|matches[Password]');
				$this->form_validation->set_rules('pref_newsletter');
				
				if ($this->form_validation->run() == FALSE) {
					// Set the template (form)
					$template = 'cart/myaccount-register';
				} else {
					// Create user account (also sets their marketing preferences)
					$account_id = $this->myaccount->create_account();
					// Set the template
					$template = 'cart/myaccount-success';
				}

				// Get basket summary
				$mybasket = $this->core->mybasket_summary();
				$data = array(
					'page_title' 		=> 'My Account - Register',
					'meta_description'	=> '',
					'meta_keywords'		=> '',
					'meta_custom'		=> '',
					'content'	 		=> $template,
					// Basket summary
					'itemtotal'			=> $mybasket->itemtotal,
					'baskettotal' 		=> $mybasket->baskettotal,
					'itemtotal_int'		=> $mybasket->items,
					'baskettotal_exvat' => $mybasket->baskettotal_exvat,
				);							

				// Load snippets
				$this->settings_model->snippets();
				
				// Load the core templates
				$data['shopit:header']  = $this->parser->parse('global/header', $data, true);
				$data['shopit:footer']  = $this->parser->parse('global/footer', $data, true);
				$data['shopit:sidebar'] = $this->parser->parse('global/sidebar', $data, true);
				$data['shopit:content'] = $this->parser->parse('content/homepage', $data, true);
				$data['shopit:search']  = $this->parser->parse('boxes/search-box', $data, true);
				$data['shopit:mybasket']  = $this->parser->parse('boxes/basket', $data, true);
				$data['shopit:breadcrumb']  = "";
				$data['shopit:categories'] = $this->category_model->createNav();
				$data['shopit:pages']  = $this->pages_model->createList();
				
				$this->parser->parse('global/cart', $data);
				
				break;
			
			//Forgotten password reminder
			case "remindme":
				
				if (isset($_POST['AccountEmail']) && $_POST['AccountEmail'] != ''):
				
					//Check account
					$account = $this->myaccount->checkuser_email($_POST['AccountEmail']);
					
					if ($account != false): //not empty
						
						//generate a new password
						$new_password = $this->myaccount->generate_password();
						
						//add new password to user's account
						if ($this->myaccount->attach_password($account->account_id,$new_password)):

							$this->session->set_flashdata('notice','<p class="baskettable ordertable">Password updated and emailed.</p>');

							//send email to user confirming new password
							$message  = "Hi " . $account->account_firstname . ",\n\n";
							$message .= "You recently requested a new password for your account: \n\n";
							$message .= $new_password . "\n\n";
							$message .= "Once you've logged in, feel free to change this to something more memorable.\n\n";
							$message .= "Kind regards,\n";
							$message .= $this->config->item('store_name');
							
							$this->email->from($this->config->item('store_email'), $this->config->item('store_name'));
							$this->email->to($account->account_user); 
							
							$this->email->subject('Your new password');
							$this->email->message($message);	
							
							$this->email->send();
						endif;
						
					else:
						$this->session->set_flashdata('notice','<p class="baskettable ordertable">Sorry, the email address you entered was not found on our system.</p>');
					endif;
					
					redirect($_POST['redirect_url']);

				else:
					//Display form
					// Get basket summary
					$mybasket = $this->core->mybasket_summary();
					$data = array(
						'page_title' 		=> 'My Account - Forgot Password',
						'meta_description'	=> '',
						'meta_keywords'		=> '',
						'meta_custom'		=> '',
						'content'	 		=> 'cart/myaccount-remindme',
						'orders'			=> $this->myaccount->get_orders($this->myaccount->get_info('id')),
						// Basket summary
						'itemtotal'			=> $mybasket->itemtotal,
						'baskettotal' 		=> $mybasket->baskettotal,
						'itemtotal_int'		=> $mybasket->items,
						'baskettotal_exvat' => $mybasket->baskettotal_exvat,
					);							

					//Load snippets
					$this->settings_model->snippets();

					//Load the core templates
					$data['shopit:header']  = $this->parser->parse('global/header', $data, true);
					$data['shopit:footer']  = $this->parser->parse('global/footer', $data, true);
					$data['shopit:sidebar'] = $this->parser->parse('global/sidebar', $data, true);
					$data['shopit:content'] = $this->parser->parse('content/homepage', $data, true);
					$data['shopit:search']  = $this->parser->parse('boxes/search-box', $data, true);
					$data['shopit:mybasket']  = $this->parser->parse('boxes/basket', $data, true);
					$data['shopit:breadcrumb']  = "";
					$data['shopit:categories'] = $this->category_model->createNav();
					$data['shopit:pages']  = $this->pages_model->createList();
	
					$this->parser->parse('global/cart',$data);
				endif;
				
				break;

			//Check if this user has an account (used 
			//via ajax on the basket page
			case "checkuser":
								
				$user = ($this->input->post('Email') == '') ? null : $this->input->post('Email');
				$account = $this->myaccount->checkuser_email($user);
				
				if ($account != false && $this->myaccount->user_logged_in() != true) {
					$message =  "We've identified that you already have an account with us. Please <a href=\"#AccountLogin\">login</a> above to proceed with checkout.";
					$failed  = true;
				} else {
					$message =  "";
					$failed  = false;
				}				
				
				print json_encode( 
					array (
						'message' => $message,
						'failed' => $failed,
					) 
				);
				
				break;			
			
			//Display My Account homepage
			default:
				// Get basket summary
				$mybasket = $this->core->mybasket_summary();
				$data = array(
					'page_title' 		=> 'My Account',
					'meta_description'	=> '',
					'meta_keywords'		=> '',
					'meta_custom'		=> '',
					'content'	 		=> 'cart/myaccount',
					'orders'			=> $this->myaccount->get_orders($this->myaccount->get_info('id')),
					// Basket summary
					'itemtotal'			=> $mybasket->itemtotal,
					'baskettotal' 		=> $mybasket->baskettotal,
					'itemtotal_int'		=> $mybasket->items,
					'baskettotal_exvat' => $mybasket->baskettotal_exvat,
				);							
				
				//Load snippets
				$this->settings_model->snippets();

				//Load the core templates
				$data['shopit:header']  = $this->parser->parse('global/header', $data, true);
				$data['shopit:footer']  = $this->parser->parse('global/footer', $data, true);
				$data['shopit:sidebar'] = $this->parser->parse('global/sidebar', $data, true);
				$data['shopit:content'] = $this->parser->parse('content/homepage', $data, true);
				$data['shopit:search']  = $this->parser->parse('boxes/search-box', $data, true);
				$data['shopit:mybasket']  = $this->parser->parse('boxes/basket', $data, true);
				$data['shopit:breadcrumb']  = "";
				$data['shopit:categories'] = $this->category_model->createNav();
				$data['shopit:pages']  = $this->pages_model->createList();

				$this->parser->parse('global/cart',$data);
				break;
		
		}

	}
	#------------------------------------------------------------------------------------------------------------
	# END Module: My Account
	#------------------------------------------------------------------------------------------------------------

	#------------------------------------------------------------------------------------------------------------
	#------------------------------------------------------------------------------------------------------------
	//!!! Module: Coupons
	#------------------------------------------------------------------------------------------------------------
	#------------------------------------------------------------------------------------------------------------
	function coupons() {
	
		$basket = $this->basket_model->basketSummary();
	
		switch ($this->uri->segment(3)) {
		
			//Customer's entered a code which needs to be applied to the basket. We'll store
			//this in a session.
			case 'apply':
				//First we need to check if the code is real and has not expired.
				$coupon = $this->coupons->check($this->input->post('CouponCode', true), $basket->total);
				
				//If no discount found, then display error message
				if ($coupon->discount == 0) {
				
					$this->session->set_flashdata('coupon_notice','<p class="error">'.$coupon->message.'</p>');
					
				}
				
				redirect(site_url('basket'));
				break;
				
			//Remove coupon code from basket
			case 'remove':
				$this->coupons->remove();
				redirect(site_url('basket'));
				break;
		
		}
	
	}
	#------------------------------------------------------------------------------------------------------------
	# END Module: Coupons
	#------------------------------------------------------------------------------------------------------------

}
