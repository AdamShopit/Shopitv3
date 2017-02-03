<?php
class Webservices extends CI_Controller {
	
	function Webservices() {
		parent::__construct();

		$this->load->model('settings_model');
		$this->settings_model->initConfig();
		
		$this->load->helper('file');
		$this->load->helper('form');
		$this->load->helper('cookie');
		$this->load->library('parser');
		$this->load->library('encrypt');
		
		$this->load->model('webservices_model', 'webservice');
		$this->load->model('products_model');

		//Load modules
		foreach ($this->config->item('modules') as $module) {
			if (library_exists($module)):
			$this->load->library($module);
			endif;
		}

		// Enable profiler
		if ($this->config->item('enable_profiler') == 'true') {
			$this->output->enable_profiler(true);
		}
		
		// No permissions or login check required for 
		// these webservices as we'll be using the APIs
	}

	#------------------------------------------------------
	# Web services information
	#------------------------------------------------------
	function index() {

		// Disable the profiler
		$this->output->enable_profiler(false);

		// Output plain text
		header('Content-type: text/plain; charset=utf-8');

		echo "See developer guide for instructions on how to use the web services.";
				
	}
	
	#------------------------------------------------------
	# Get products
	# - Retrieves a list of products based on category,
	#   tag or keywords
	# - We should pass the type of lookup we want to this 
	#   service e.g. category, tags, brand , keyword, etc
	# @get key (string) 	- API key as set within the Shopit Admin
	# @get type (string) 	- Lookup type ('category', 'tag', 'brand' or 'keyword'
	# @get query (string) 	- Keyword or phrase to search on
	# @get limit (int) 		- Number of products to return. Defaults to 10. Max 100
	# @get size (int) 		- Size of images to return in pixels. Defaults to config setting if param not passed
	# @returns JSON array of products
	#------------------------------------------------------
	function products() {

		// Disable the profiler
		$this->output->enable_profiler(false);
		
		// Reset a few things here
		$items = array();

		// Output json content
		header('Content-type: application/json; charset=utf-8');
		
		// Parameters passed through $get
		$api_key = rawurldecode(trim(strip_tags($this->input->get('key'))));	// The API key
		$type  	 = rawurldecode(trim(strip_tags($this->input->get('type'))));	// The type of lookup i.e. 'category', 'tag', 'brand' or 'keyword'
		$query 	 = rawurldecode(trim(strip_tags($this->input->get('query')))); 	// Query/keywords to search on
		$limit 	 = rawurldecode(trim(strip_tags($this->input->get('limit')))); 	// Number of products to return. Default is 10 if parameter not passed
		$img_size= rawurldecode(trim(strip_tags($this->input->get('size'))));	// Size of the images to return. Default is config setting if param not passed
		
		// Fix the limit (max 100) and image size to an integer in the event something else is passed
		$limit = (is_numeric($limit)) ? $limit : 10;
		$limit = ($limit > 100) ? 100 : $limit;
		$img_size = (is_numeric($img_size)) ? $img_size : $this->config->item('thumbnail_width');
		
		// Check if key exists and do the necessary
		if ( $this->core->api($api_key) ) {
			
			// Get the products
			$products = $this->webservice->products($type, $query, $limit);

			// Now we can look through our results and return an array suitable for the web service
			if (count($products) > 0) {

				//Product counter
				$p = 0;
				
				foreach($products as $item) {
					
					// Convert $item to an object
					$item = (object)$item;
					
					$p++;
	
					// Get product url
					$url = $this->core->product_url($item);
					
					// Get the product thumbnail
					$product_image = $this->core->_displayThumbnail($item, $img_size);
			
					//Define if this item is on sale or not
					$onsale = ($item->product_saleprice > 0) ? TRUE : FALSE;
					
					//Some useful css classes
					$onsale_css_class = ($onsale) ? "sale-item" : "";
					$css_classes = array("product-$p", "productid-$item->product_id", $item->product_brand_slug, $onsale_css_class, $item->product_type, $item->cat_slug);
					$css_classes = array_filter($css_classes);
					$css_class = implode(" ", $css_classes); //Turn the classes into a space-separated string
	
					// Price range template tag
					$product_price_range = ($item->product_type == 'variation' && $item->min_price != $item->max_price) ? sprintf('%s - %s', money($item->min_price), money($item->max_price)) : money($item->min_price);
					$product_price_range_exvat = ($item->product_type == 'variation' && $item->min_price != $item->max_price) ? sprintf('%s - %s', money($item->min_price, true, true, false), money($item->max_price, true, true, false)) : money($item->min_price, true, true, false);
					
					$items[] = array(
						'product_count'				=> $p,
						'product_id' 				=> $item->product_id,
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
						'product_image' 			=> $product_image,
						'url'						=> $url,
						'css_classes'				=> $css_class,
					);
					
				}

				// Uncomment the line below for testing
				#echo sprintf('<pre>%s</pre>', print_r($items, true));
			
			}

		}
		
		// Output a json array
		echo json_encode($items);
		
	}
	
}