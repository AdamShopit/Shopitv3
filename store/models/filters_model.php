<?php
class Filters_model extends CI_Model {
	
	function Filters_model() {
		parent::__construct();

		// Get the price field names for this channel
		$this->channel_product_price = $this->config->item('channel_product_price');
		$this->channel_product_saleprice = $this->config->item('channel_product_saleprice');
		
		$this->load->helper('security');
	}

	#------------------------------------------------------
	# List filter groups
	#------------------------------------------------------
	function groups($cat_id) {
		
		$this->db->where('cat_id', $cat_id);
		$this->db->order_by('group_order');
		$query = $this->db->get('filter_groups');
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
		
	}

	#------------------------------------------------------
	# List filter options
	#------------------------------------------------------
	function options($group_id) {
		
		$this->db->select('filter_definitions.*');
		$this->db->from('filter_definitions');
		$this->db->join('filter_groups', 'filter_groups.group_id = filter_definitions.group_id');
		$this->db->where('filter_definitions.group_id', $group_id);
		$this->db->order_by('filter_order');
		$query = $this->db->get();
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return array();
		}
		
	}

	#------------------------------------------------------
	# Create the layered navigation array
	# @int $cat_id = Current category id
	# $string $current_cat_url = Current category url
	#------------------------------------------------------
	function layers($cat_id, $current_cat_url) {

		// Set a few vars first
		$opt 			= 5; // This is the number the filters defined in the admin will start from
		$filter_array 	= array();
		$get_url 		= array();
		$empty_group 	= 'true'; // True indicates the group is empty. We'll set this as default as we've not started anything yet!
		
		// BEGIN: Get the sub-categories if any for this section
		$subcats = $this->category_model->getSubCategories($cat_id);
		
		if (!empty($subcats)) {
		
			// Build the category array
			$filter_array[0] = array(
				'group_id'	  => null,
				'group_label' => 'Categories',
				'group_type'  => 'list',
				'group_empty' => 'false',
			);
			
			// Loop through each sub-category and add to array
			foreach ($subcats as $sub) {
			
				// Create the sub-category url
				$url = site_url( sprintf('%s/%s', $current_cat_url, $sub->cat_slug) );

				// Create some css classes that can be attached to each filter option. The selected class
				// indicates the currently selected sorting option
				$css_classes_array = array(
					'filter-group',
					'filter-option',
					'filter-group-categories',
				);
				$css_classes_array = array_filter($css_classes_array);
				$css_classes = implode(" ", $css_classes_array); //Turn the classes into a space-separated string

				// Do a product count here
				$product_count = $this->matches($sub->cat_id, NULL, $_GET['price_min'], $_GET['price_max']);
				
				// Create the html
				if ($product_count > 0) {
					$html = "<li class=\"$css_classes\"><a href=\"$url\">$sub->cat_name</a> <span>($product_count)</span></li>\n";
				} else {
					$html = "<li class=\"$css_classes\"><a href=\"$url\">$sub->cat_name</a></li>\n";
				}
			
				// Add category to array
				$filter_array[0]['layer'][] = array(
					'id'	 		=> null,
					'label'	 		=> $sub->cat_name,
					'swatch' 		=> null,
					'colour'		=> null,
					'type'	 		=> null,
					'field_name' 	=> $sub->cat_name,
					'field_val'		=> null,
					'url'			=> $url,
					'css_classes'	=> $css_classes,
					'product_count'	=> $product_count,
					'field_html'	=> $html,
				);
			}
		
		}
		// END: Sub-categories

		// BEGIN: Auto Price Ranges
		$prices = $this->pricerange($cat_id);
		
		if (count($prices) > 1) {

			// Build the group array
			$filter_array[1] = array(
				'group_id'	  => null,
				'group_label' => 'Price',
				'group_type'  => 'list',
				'group_empty' => 'false',
			);
			
			//Loop through each price range and add to array
			foreach ($prices as $range) {
				
				// BEGIN: Filter urls
				$url = site_url($current_cat_url); // The current url
				
				// Retrieve the $_GET values and re-build into query string, but
				// ignore the "price_min" and "price_max" parameters in there to prevent duplication
				if (!empty($_GET)) {
					foreach($_GET as $param=>$value) {
						if ($param != "price_min" && $param != "price_max") {
							$get_url[$param] = $value;
						}
					}
					$filter_url = "?".http_build_query($get_url);
				} else {
					$filter_url = "?";
				}
	
				// Append the sort option to the url
				$param_delimiter = (strlen($filter_url) > 1) ? "&" : "";
				$append_get_url = $param_delimiter."price_min=$range->from&price_max=$range->to";
	
				// Remove '?&' if it appears in the filter url to keep it clean
				$filter_url = str_replace('?&', '?', $filter_url);
	
				// The final url
				$url = "$url$filter_url".$append_get_url;
				// END: Filter urls

				// Create some css classes that can be attached to each filter option. The selected class
				// indicates the currently selected sorting option
				$selected_css_class = ( ($_GET['price_min'] == $range->from) && ($_GET['price_max'] == $range->to) ) ? 'price-range-selected' : '';
	
				$css_classes_array = array(
					'filter-group',
					'filter-option',
					'filter-group-price-range',
					$selected_css_class
				);
				$css_classes_array = array_filter($css_classes_array);
				$css_classes = implode(" ", $css_classes_array); //Turn the classes into a space-separated string
					
				// Create the html
				$html = "<li class=\"$css_classes\"><a rel=\"nofollow\" href=\"$url\">$range->label</a> <span>($range->product_count)</span></li>\n";
				
				// Add range to array, but only if there are products within
				if ($range->product_count > 0) {
					$filter_array[1]['layer'][] = array(
						'id'	 		=> null,
						'label'	 		=> $range->label,
						'swatch' 		=> null,
						'colour'		=> null,
						'type'	 		=> null,
						'field_name' 	=> $range->label,
						'field_val'		=> null,
						'url'			=> $url,
						'css_classes'	=> $css_classes,
						'product_count'	=> $range->product_count,
						'field_html'	=> $html,
					);
				}

			}
			
		}
		// END: Auto Price Ranges
		
		// First get all the groups
		$filter_groups = $this->groups($cat_id);
		
		// Loop through the filter groups
		foreach ($filter_groups as $group) {
			
			$opt++;
			
			// Reset the empty group flag for each group
			$empty_group = 'true';
			
			// Build the group array
			$filter_array[$opt] = array(
				'group_id'	  => $group->group_id,
				'group_label' => $group->label,
				'group_type'  => $group->type,
			);
			
			// Now get the options per group
			$filter_options = $this->options($group->group_id);
			
			// Count the filter options for this group
			$count_filter_options = count($filter_options);
						
			// Start building the filter options array
			foreach($filter_options as $option) {
			
				$filter_field = "f_".slug("$group->label-$option->label");

				// BEGIN: Filter urls
				$url = site_url($current_cat_url); // The current url
				
				// Retrieve the $_GET values and re-build into query string
				if (!empty($_GET)) {
					foreach($_GET as $param=>$value) {
						$get_url[$param] = $value;
					}					
					$filter_url = "?".http_build_query($get_url);
				} else {
					$filter_url = "?";
				}
				
				// Append the current filter option to the url, checking if we need to add the ? or not
				// and whether it's already in the query string or not.
				if (strpos($filter_url, $filter_field) === FALSE) {
					$append_get_url = (strlen($filter_url) > 1) ? "&$filter_field=$option->filter_id" : "$filter_field=$option->filter_id";
					$selected_css_class = '';
				} else {
					$append_get_url = "";
					$selected_css_class = 'filter-selected';
					// Remove the filter from the query string if the link is clicked on again
					$filter_url = preg_replace("/&?$filter_field=$option->filter_id/", '', $filter_url);
					// Remove '?&' if it appears in the filter url to keep it clean
					$filter_url = str_replace('?&', '?', $filter_url);
				}

				// The final url
				$url = "$url$filter_url".$append_get_url;
				// END: Filter urls
			
				// Do a product count here
				$product_count = $this->matches($cat_id, "filter_$option->filter_id", $_GET['price_min'], $_GET['price_max']);
				
				// Create the colour swatch if applicable
				if ($group->type == 'swatches') {
					$swatch = '<span class="layer-swatch" style="background-color:'.$option->colour.';display:inline-block;width:10px;height:10px;vertical-align:middle;border:1px solid #d7d7d7;"></span> ';
				} else {
					$swatch = "";
				}

				// Create some css classes that can be attached to each filter option
				$css_classes_array = array(
					'filter-group',
					'filter-option',
					"filter-group-$group->group_id",
					"filter-group-".slug($group->label),
					"filter-option-$option->filter_id", 
					"filter-option-".slug($option->label),
					$selected_css_class
				);
				$css_classes_array = array_filter($css_classes_array);
				$css_classes = implode(" ", $css_classes_array); //Turn the classes into a space-separated string
				
				// Create the html format of the filter group i.e. checkboxes, ranges, etc.
				switch ($group->type) {
										
					// Default is checkboxes
					default:
						$html  = "<li class=\"$css_classes\">";
						$html .= '<a href="'.$url.'" rel="nofollow">';
						$html .= $swatch;
						$html .= $option->label;
						$html .= '</a> <span>(' . $product_count . ')</span>';
						$html .= '</li>';
						$html .= "\n";
						break;
					
				}
				
				// Add option to the array (if it's NOT been selected)
				if ($product_count > 0) {
					$filter_array[$opt]['layer'][] = array(
						'id'	 		=> $option->filter_id,
						'label'	 		=> $option->label,
						'swatch' 		=> $swatch,
						'colour'		=> $option->colour,
						'type'	 		=> null,
						'field_name' 	=> $filter_field,
						'field_val'		=> "1",
						'url'			=> $url,
						'css_classes'	=> $css_classes,
						'product_count'	=> $product_count,
						'field_html'	=> $html,
					);
					
					// Okay, we've just added an option so this group is NOT empty,
					// so we can update the flag accordingly
					$empty_group = 'false';
					
				//Else blank it all...
				} else {
					$filter_array[$opt]['layer'][] = array(
						'id'	 		=> null,
						'label'	 		=> null,
						'swatch' 		=> null,
						'colour'		=> null,
						'type'	 		=> null,
						'field_name' 	=> null,
						'field_val'		=> null,
						'url'			=> null,
						'css_classes'	=> null,
						'product_count'	=> null,
						'field_html'	=> null,
					);
				}
				
			}
			
			$filter_array[$opt]['group_empty'] = $empty_group;
			
		}

		#echo "<pre>" . print_r($filter_array, 1) . "</pre>"; // For testing
		
		// Return the array
		return $filter_array;
		
	}

	#------------------------------------------------------
	# Create a list of all the selected layers
	#------------------------------------------------------
	function selected($current_cat_url) {

		// Set a few vars first
		$opt = 1;
		$filter_array = array();
		$get_url = array();
		$currency = $this->config->item('currency');
		
		// Listen for filter query string
		if (!empty($_GET)) {
		
			// Check for category (used on non-category pages, i.e. search)
			if (isset($_GET['category'])) {

				// BEGIN: Filter urls
				$url = site_url($current_cat_url); // The current url
				
				// Retrieve the $_GET values and re-build into query string
				if (!empty($_GET)) {
					foreach($_GET as $param=>$value) {
						$ignore = array('q', 'search', 'submit');
						if (!in_array($param, $ignore)) {
							$get_url[$param] = $value;
						}
					}					
					$filter_url = "?".http_build_query($get_url);
				} else {
					$filter_url = "";
				}
				
				// Remove the filter from the query string
				$filter_url = preg_replace("/&?category=([0-9]+?)-([0-9A-Za-z+%]+)/", '', $filter_url);
				// Remove '?&' if it appears in the filter url to keep it clean
				$filter_url = str_replace('?&', '?', $filter_url);
				
				// The final url
				$url = "$url$filter_url";

				//Remove the ? if at the end of the $url
				$url = preg_replace("/\?\Z/", '', $url);
				// END: Filter urls

				// Get the category name from the parameter
				preg_match('/-(.*)/', $_GET['category'], $matches);
				$label = urldecode($matches[1]);

				// Add option to array
				$filter_array[] = array(
					'selected_group' => "Category",
					'selected_layer' => $label,
					'selected_url' 	 => "<a rel=\"nofollow\" href=\"$url\">&times;</a>",
				);

			}

			// Check for price ranges
			if (isset($_GET['price_min']) && isset($_GET['price_max'])) {
			
				$label_from = $_GET['price_min'];
				$label_to = $_GET['price_max'];

				// BEGIN: Filter urls
				$url = site_url($current_cat_url); // The current url
				
				// Retrieve the $_GET values and re-build into query string
				if (!empty($_GET)) {
					foreach($_GET as $param=>$value) {
						$get_url[$param] = $value;
					}					
					$filter_url = "?".http_build_query($get_url);
				} else {
					$filter_url = "";
				}
				
				// Remove the filter from the query string
				$filter_url = preg_replace("/&?price_min=([0-9]+)\&price_max=([0-9]+)/", '', $filter_url);
				// Remove '?&' if it appears in the filter url to keep it clean
				$filter_url = str_replace('?&', '?', $filter_url);
				
				// The final url
				$url = "$url$filter_url";

				//Remove the ? if at the end of the $url
				$url = preg_replace("/\?\Z/", '', $url);
				// END: Filter urls

				// Create the appropriate label
				if ($_GET['price_min'] == 0) {
					$label = "Under $currency$label_to";
				} elseif($_GET['price_max'] >= 999999) {
					$label = "$currency$label_from and above";
				} else {
					$label = "$currency$label_from - $currency$label_to";
				}

				// Add option to array
				$filter_array[] = array(
					'selected_group' => "Price",
					'selected_layer' => $label,
					'selected_url' 	 => "<a rel=\"nofollow\" href=\"$url\">&times;</a>",
				);
				
			}
		
			// Loop through each $get parameter and do the necessary...
			foreach($_GET as $param=>$value) {
				
				// Only get those parameters that begin with "f_"
				if (preg_match('/f_/', $param)) {
					
					// If there's a match set the filter_id and filter field name
					$filter_id = $value;
					$filter_field = $param;
					
					// Work backwards to get this filter's labels
					$this->db->select('filter_groups.label as group_label, filter_definitions.label as option_label');
					$this->db->from('filter_definitions');
					$this->db->join('filter_groups', 'filter_definitions.group_id = filter_groups.group_id');
					$this->db->where('filter_id', $filter_id);
					$query = $this->db->get();
					
					$option = $query->row();

					// BEGIN: Filter urls
					$url = site_url($current_cat_url); // The current url
					
					// Retrieve the $_GET values and re-build into query string
					if (!empty($_GET)) {
						foreach($_GET as $param=>$value) {
							$get_url[$param] = $value;
						}					
						$filter_url = "?".http_build_query($get_url);
					} else {
						$filter_url = "";
					}
					
					// Remove the filter from the query string
					$filter_url = preg_replace("/&?$filter_field=$filter_id/", '', $filter_url);
					// Remove '?&' if it appears in the filter url to keep it clean
					$filter_url = str_replace('?&', '?', $filter_url);
					
					// The final url
					$url = "$url$filter_url";
					
					//Remove the ? if at the end of the $url
					$url = preg_replace("/\?\Z/", '', $url);
					// END: Filter urls

					// Add option to array
					$filter_array[] = array(
						'selected_group' => $option->group_label,
						'selected_layer' => $option->option_label,
						'selected_url' 	 => "<a rel=\"nofollow\" href=\"$url\">&times;</a>",
					);
					
				}
				
			}

		}
		
		// Let's prepend the "Active Filters" title to the array
		if (!empty($filter_array)) {
			$prepend_array = array(
				'selected_group' => "Active Filters",
				'selected_layer' => NULL,
				'selected_url' 	 => NULL,
			);
			array_unshift($filter_array, $prepend_array);
		}
		
		// Return the array
		return $filter_array;
		
	}

	#------------------------------------------------------
	# Product Sort - Creates a list of sorting options
	#------------------------------------------------------
	function sort($current_cat_url) {
		
		// Reset a few things
		$filter_array = array();
		$get_url = array();
		$sort_options_prioritisation = array();
		$k = 0;
		
		// Set the default sort options
		$sort_options_default = array(
			'Price (Low to High)' 	 => 'min_price',
			'Price (High to Low)'    => 'min_price desc',
			'Most Viewed'		 	 => 'product_views desc',
			'Product Title (A to Z)' => 'product_name',
			'Product Title (Z to A)' => 'product_name desc',
			'Newest First'			 => 'date_added desc',
		);

		// Merge the default and prioritisation sort options
		$sort_options = array_merge($sort_options_prioritisation, $sort_options_default);
		
		foreach ($sort_options as $label=>$field) {
		
			$k++;
		
			// Adjust the field value
			$field_label = ($field != '') ? "sort=".urlencode($field) : "";
			
			// BEGIN: Filter urls
			$url = site_url($current_cat_url); // The current url
			
			// Retrieve the $_GET values and re-build into query string, but
			// ignore the "sort" parameter in there to prevent duplication
			if (!empty($_GET)) {
				foreach($_GET as $param=>$value) {
					if ($param != "sort") {
						$get_url[$param] = $value;
					}
				}
				$filter_url = "?".http_build_query($get_url);
			} else {
				$filter_url = "?";
			}

			// Append the sort option to the url
			$param_delimiter = (strlen($filter_url) > 1 && $field != "") ? "&" : "";
			$append_get_url = $param_delimiter.$field_label;

			// Remove '?&' if it appears in the filter url to keep it clean
			$filter_url = str_replace('?&', '?', $filter_url);

			// The final url
			$url = "$url$filter_url".$append_get_url;

			//Remove the ? if at the end of the $url
			$url = preg_replace("/\?\Z/", '', $url);
			// END: Filter urls

			// Create some css classes that can be attached to each filter option. The selected class
			// indicates the currently selected sorting option
			$selected_css_class = ($_GET['sort'] == $field) ? 'results-sort-selected' : '';

			$css_classes_array = array(
				'results-sort',
				$selected_css_class
			);
			$css_classes_array = array_filter($css_classes_array);
			$css_classes = implode(" ", $css_classes_array); //Turn the classes into a space-separated string
		
			// Give the selected option an array key of 0
			$key = ($_GET['sort'] == $field) ? 0 : $k;
			
			// Append the option to the array
			$filter_array[$key] = array(
				'results_sort_url' 	 		=> $url,
				'results_sort_label' 		=> $label,
				'results_sort_css_classes'	=> $css_classes,
			);
			
		}

		// Do the re-sort
		ksort($filter_array);

		// Return the array
		return $filter_array;
		
	}

	#------------------------------------------------------
	# Products per page - Creates a list
	#------------------------------------------------------
	function perpage($current_cat_url, $total_products=null) {
		
		// Reset a few things
		$filter_array = array();
		$get_url = array();
		$arr = array();

		if ($total_products > 0) {

			// Get the default per page setting
			$per_page = $this->config->item('products_per_page');
			
			// Create a new array with all the options
			for ($i=1; $i<=4; $i++) {
				// Set the per page setting and add new array item
				$per_page_setting = $per_page * $i;
				$arr[$per_page_setting] = $per_page_setting;
			}
			
			// And append the "All n" option (limit this to the first 1000 products if there 
			// are more than 1000) and only display if total results is greater that a single page
			$total_products_limit = ($total_products <= 1000) ? $total_products : 1000;
			
			if ($total_products > $per_page) {
			$arr["All $total_products"] = $total_products_limit;
			}
	
			// Now loop through the new array we created above and do the necessary
			foreach ($arr as $label=>$results) {
	
				// BEGIN: Filter urls
				$url = site_url($current_cat_url); // The current url
		
				// Retrieve the $_GET values and re-build into query string, but
				// ignore the "perpage" parameter in there to prevent duplication
				if (!empty($_GET)) {
					foreach($_GET as $param=>$value) {
						if ($param != "perpage") {
							$get_url[$param] = $value;
						}
					}
					$filter_url = "?".http_build_query($get_url);
				} else {
					$filter_url = "?";
				}
		
				// Append the perpage option to the url
				$param_delimiter = (strlen($filter_url) > 1) ? "&" : "";
				$append_get_url = $param_delimiter."perpage=$results";
		
				// Remove '?&' if it appears in the filter url to keep it clean
				$filter_url = str_replace('?&', '?', $filter_url);
		
				// The final url
				$url = "$url$filter_url".$append_get_url;

				//Remove the ? if at the end of the $url
				$url = preg_replace("/\?\Z/", '', $url);
				// END: Filter urls

				// Create some css classes that can be attached to each filter option. The selected class
				// indicates the currently selected sorting option
				if ($_GET['perpage'] == $results) {
					$selected_css_class = 'results-perpage-selected';
				} elseif (empty($_GET['perpage']) && $results == $per_page) {
					$selected_css_class = 'results-perpage-selected';
				} else {
					$selected_css_class = '';
				}
	
				$css_classes_array = array(
					'results-perpage',
					$selected_css_class
				);
				$css_classes_array = array_filter($css_classes_array);
				$css_classes = implode(" ", $css_classes_array); //Turn the classes into a space-separated string
				
				// Append the option to the array
				$filter_array[] = array(
					'results_perpage_url' 		  => $url,
					'results_perpage_label' 	  => $label,
					'results_perpage_css_classes' => $css_classes,
				);
			
			}
		
		}
		
		// Return the array
		return $filter_array;
		
	}

	#------------------------------------------------------
	# Price ranges - Auto creates a list of price ranges
	#------------------------------------------------------
	function pricerange($cat_id, $is_search=false, $keywords=null) {
		
		// Set a few things
		$filter_array 	= array();
		$get_url 		= array();
		$price_range 	= array();
		$currency 		= $this->config->item('currency');
		
		// Get the min and max prices for this category
		$range = $this->get_minmax_price($cat_id);
		
		if ($range->min_price != $range->max_price) {
		
			// Work out the difference between the max and min price
			$range_difference = $range->max_price - $range->min_price;
	
			// Create a range from min to max containing a set number of steps
			$steps = 5;
			$delta = floor($range_difference/$steps);

			if ($delta > 0) {
				$price_range = @range(($range->min_price*2), $range->max_price, $delta);
			}
			
			// Clean out any empty array items
			if (!empty($price_range)) {
				$price_range = array_filter($price_range);
			}
			
			// Count the number of items in the array
			$count_range = count($price_range);
			
#			echo "<pre>";
#			print_r($price_range);
#			echo "</pre>";		
	
			// Only create the list if the gaps is large enough (50)
			if ($delta >= 50) {
	
				// Loop through the array we created above and do the necessary
				for ($i=1; $i<=$count_range; $i++) {
					
					$current_array_item = current($price_range);
					$next_array_item = next($price_range);
					
					// Set array pointers to get the values we need and
					// whilst we're here let's do some rounding (to the nearest 50)
					$current = round( ($current_array_item * the_vat_rate()) / 50, 0) * 50;
					$next 	 = round( ($next_array_item * the_vat_rate()) / 50, 0) * 50;
		
					// Add the VAT to the filter labels
					$label_current = round( ($current_array_item * the_vat_rate()) / 50, 0 ) * 50;
					$label_next    = round( ($next_array_item * the_vat_rate()) / 50, 0 ) * 50;
					
					// Create the appropriate label
					if ($i == 1) {
						$label = "Under $currency$label_next";
					} elseif($i == $count_range) {
						$label = "$currency$label_current and above";
					} else {
						$label = "$currency$label_current - $currency$label_next";
					}
					
					// Do some overwrites for the first and last items of the array
					$current = ($i == 1) ? 0 : $current;
					$next = ($i == $count_range) ? 999999 : $next;
					
					// Count number of products
					if (!$is_search) {
						$product_count = $this->matches($cat_id, NULL, $current, $next);
					} else {
						$product_count = $this->search_matches($cat_id, $keywords, NULL, $current, $next);
					}
					
					if ($product_count > 0) {
					
						// Append the option to the array (as stdClass Object)
						$filter_array[$i] = (object) array(
							'label'			=> $label,
							'from'			=> $current,
							'to'			=> $next,
							'from_raw' 		=> $current_array_item,
							'to_raw'		=> $next_array_item,
							'product_count'	=> $product_count
						);
					
					}
					
				}
				
			}
		
		}
		
#		echo "<pre>";
#		print_r($filter_array);
#		echo "</pre>";
		
		// Return the array
		return $filter_array;
		
	}

	#------------------------------------------------------
	# Get min and max prices
	#------------------------------------------------------
	function get_minmax_price($cat_id) {
	
		$this->db->query('SET SQL_BIG_SELECTS=1');
		$sql = 'select max(max_price) as max_price, min(min_price) as min_price
				from (
					select
						(case when product_type = "variation" 
							then 
								(select min((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
							else
								(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)
						end) as min_price,
						(case when product_type = "variation" 
							then 
								(select max((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0)
							else
								(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)
						end) as max_price
					from inventory
					where inventory.cat_id = '. $cat_id
					.' and product_disabled = 0
					and ' . $this->config->item('channel') . ' = 1 

					union all

					select
						(case when product_type = "variation" 
							then 
								(select min((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
							else
								(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)
						end) as min_price,
						(case when product_type = "variation" 
							then 
								(select max((case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0)
							else
								(case when '.$this->channel_product_saleprice.' > 0 then '.$this->channel_product_saleprice.' else '.$this->channel_product_price.' ' . the_base_rate() . ' end)
						end) as max_price
					from xcat
					join inventory on xcat.product_id = inventory.product_id
					where xcat.cat_id = '. $cat_id
					.' and product_disabled = 0
					and ' . $this->config->item('channel') . ' = 1 
				) as minmax';

		$query = $this->db->query($sql);
						
		// Returns min_price and max_price
		return $query->row();

	}

	#------------------------------------------------------
	# Product matches
	# - @str $filter_col = 'filter_n'
	# - This function utilises the $_GET query string params
	# - !! xcat products need adding too?
	#------------------------------------------------------
	function matches($cat_id, $filter_col, $price_min=NULL, $price_max=NULL) {

		// Reset a few things first
		$filter_sql_query = "";
		$filter_by_price = FALSE;

		// If GET query parameters exist, then loop through and identify those we need
		if (!empty($_GET)) {
			
			// Loop through each $get parameter and create the necessary sql
			foreach($_GET as $param=>$value) {
				
				// Only apply those parameters that begin with "f_"
				if (preg_match('/f_/', $param)) {
					$filter_field = 'filter_'.$value;
					if ($this->db->field_exists($filter_field, 'inventory')) {
						$filter_sql[] = "$filter_field=1";
					}
				}
				
			}
			
			// Create the sql string to use further below
			if (!empty($filter_sql)) {
				$filter_sql_query = implode(' AND ', $filter_sql);
			}
			
		}
		
		// Set the price range if it exists
		if ($price_min >= 0 && $price_max > 0) {
			$filter_by_price = TRUE;
			$price_min = (int) xss_clean($price_min);
			$price_max = (int) xss_clean($price_max);
		}

		// This outer query (derived table) gives us 
		// the total sum of this and xcat items
		$sql  = "
				 SELECT COUNT(product_id) AS total_products
				 FROM ( ";

		// This first query handles SINGLE items (excluding xcats)
		$sql .= "SELECT 
					inventory.product_id
				FROM inventory 
				LEFT JOIN (category c1, category c2) ON (c1.cat_id = inventory.cat_id AND c1.cat_father_id = c2.cat_id)
				LEFT JOIN xcat ON xcat.product_id = inventory.product_id
				WHERE inventory.product_type = 'single'
				AND inventory.product_disabled = 0 
				AND inventory.cat_id = $cat_id 
				AND inventory." . $this->config->item('channel') . " = 1 ";
				
		if (!empty($filter_col)) {
		$sql .= "AND inventory.$filter_col = 1 ";
		}

		// Add the filter sql if it exists (and prepend each filter with the table name)
		if (!empty($filter_sql_query)) {
			$filter_sql_query1 = str_replace('filter_', 'inventory.filter_', $filter_sql_query);
			$sql .= "AND ($filter_sql_query1) ";
		}

		// Add price ranges to sql
		if ($filter_by_price) {
			$sql .= 'AND (
						(CASE WHEN inventory.'.$this->channel_product_saleprice.' > 0 THEN 
							inventory.'.$this->channel_product_saleprice.' 
						ELSE 
							inventory.'.$this->channel_product_price.' '.the_base_rate().'
						END) * '.the_vat_rate().' 
					) 
					BETWEEN '.$price_min.' AND '.$price_max;
		}
		
		$sql .= " UNION ";
		
		// This second query handles VARIANTS (excluding xcats)
		$sql .=	"SELECT 
					parent.product_id
				FROM inventory
				LEFT JOIN inventory parent ON parent.product_id = inventory.parent_id
				LEFT JOIN (category c1, category c2) ON (c1.cat_id = inventory.cat_id AND c1.cat_father_id = c2.cat_id)
				WHERE inventory.product_type = 'variant'
				AND parent.cat_id = $cat_id
				AND ( inventory.product_disabled = 0 AND parent.product_disabled = 0 )
				AND parent." . $this->config->item('channel') . " = 1 ";

		if (!empty($filter_col)) {
		$sql .= "AND parent.$filter_col = 1 ";
		}

		// Add the filter sql if it exists (and prepend each filter with the table name)
		if (!empty($filter_sql_query)) {
			$filter_sql_query2 = str_replace('filter_', 'parent.filter_', $filter_sql_query);
			$sql .= "AND ($filter_sql_query2) ";
		}

		// Add price ranges to sql
		if ($filter_by_price) {
			$sql .= 'AND (
						(CASE WHEN inventory.'.$this->channel_product_saleprice.' > 0 THEN 
							inventory.'.$this->channel_product_saleprice.' 
						ELSE 
							inventory.'.$this->channel_product_price.' '.the_base_rate().' 
						END) * '.the_vat_rate().' 
					) 
					BETWEEN '.$price_min.' AND '.$price_max;
		}

		$sql .= " UNION ";
		
		// This third query handles XCAT SINGLEs
		$sql .= "SELECT 
					inventory.product_id
				FROM inventory 
				LEFT JOIN (category c1, category c2) ON (c1.cat_id = inventory.cat_id AND c1.cat_father_id = c2.cat_id)
				LEFT JOIN xcat ON xcat.product_id = inventory.product_id
				WHERE inventory.product_type = 'single'
				AND inventory.product_disabled = 0 
				AND xcat.cat_id = $cat_id 
				AND inventory." . $this->config->item('channel') . " = 1 ";
				
		if (!empty($filter_col)) {
		$sql .= "AND inventory.$filter_col = 1 ";
		}

		// Add the filter sql if it exists (and prepend each filter with the table name)
		if (!empty($filter_sql_query)) {
			$filter_sql_query1 = str_replace('filter_', 'inventory.filter_', $filter_sql_query);
			$sql .= "AND ($filter_sql_query1) ";
		}

		// Add price ranges to sql
		if ($filter_by_price) {
			$sql .= 'AND (
						(CASE WHEN inventory.'.$this->channel_product_saleprice.' > 0 THEN 
							inventory.'.$this->channel_product_saleprice.' 
						ELSE 
							inventory.'.$this->channel_product_price.' '.the_base_rate().'
						END) * '.the_vat_rate().' 
					) 
					BETWEEN '.$price_min.' AND '.$price_max;
		}

		$sql .= " UNION ";
		
		// This fourth query handles XCAT VARIANTS
		$sql .=	"SELECT 
					parent.product_id
				FROM inventory
				LEFT JOIN inventory parent ON parent.product_id = inventory.parent_id
				LEFT JOIN (category c1, category c2) ON (c1.cat_id = inventory.cat_id AND c1.cat_father_id = c2.cat_id)
				LEFT JOIN xcat ON xcat.product_id = parent.product_id
				WHERE inventory.product_type = 'variant'
				AND xcat.cat_id = $cat_id
				AND ( inventory.product_disabled = 0 AND parent.product_disabled = 0 )
				AND parent." . $this->config->item('channel') . " = 1 ";

		if (!empty($filter_col)) {
		$sql .= "AND parent.$filter_col = 1 ";
		}

		// Add the filter sql if it exists (and prepend each filter with the table name)
		if (!empty($filter_sql_query)) {
			$filter_sql_query2 = str_replace('filter_', 'parent.filter_', $filter_sql_query);
			$sql .= "AND ($filter_sql_query2) ";
		}

		// Add price ranges to sql
		if ($filter_by_price) {
			$sql .= 'AND (
						(CASE WHEN inventory.'.$this->channel_product_saleprice.' > 0 THEN 
							inventory.'.$this->channel_product_saleprice.' 
						ELSE 
							inventory.'.$this->channel_product_price.' '.the_base_rate().' 
						END) * '.the_vat_rate().' 
					) 
					BETWEEN '.$price_min.' AND '.$price_max;
		}
		
		// Close off the outer query
		$sql .= " ) AS combined_totals";
		
		// Run the query
		$query = $this->db->query($sql);
		
		// Get the product count
		$product_count = $query->row()->total_products;
		
		// And return it
		return $product_count;
		
	}

	#------------------------------------------------------
	# Search result matches
	# - This function is used within rebuild() as 
	#   replacement for matches()
	#------------------------------------------------------
	function search_matches($cat_id=null, $keywords=null, $filter_col, $price_min=NULL, $price_max=NULL) {

		// Reset a few things first
		$filter_sql_query = "";
		$filter_by_price = FALSE;

		// If GET query parameters exist, then loop through and identify those we need
		if (!empty($_GET)) {
			
			// Loop through each $get parameter and create the necessary sql
			foreach($_GET as $param=>$value) {
				
				// Only apply those parameters that begin with "f_"
				if (preg_match('/f_/', $param)) {
					$filter_field = 'filter_'.$value;
					if ($this->db->field_exists($filter_field, 'inventory')) {
						$filter_sql[] = "$filter_field=1";
					}
				}
				
			}
			
			// Create the sql string to use further below
			if (!empty($filter_sql)) {
				$filter_sql_query = implode(' AND ', $filter_sql);
			}
			
		}

		// Set the price range if it exists
		if ($price_min >= 0 && $price_max > 0) {
			$filter_by_price = TRUE;
			$price_min = (int) xss_clean($price_min);
			$price_max = (int) xss_clean($price_max);
		}

		// Define what we're searching for
		switch ($this->uri->segment(1)) {
		
			case 'tag':
				$this->db->like('product_tags', $keywords);		
				break;
			
			case 'brand':
				$this->db->where('product_brand_slug', $keywords);
				break;
			
			// via search box
			default:
				
				//Library for plural, singular functions
				$this->load->helper('inflector');
								
				$terms = explode(' ', $keywords);
								
				foreach ($terms as $term) {
					$singulize = singular($term);
					$pluralize = plural($term);
					$sql[] = '(product_name like "%'.$singulize.'%" OR product_name like "%'.$pluralize.'%" OR product_no like "%'.$term.'%" OR product_description like "%'.$term.'%" OR product_tags like "%'.$term.'%")'; 
				}
				
				$sql_statement= implode(' and ',$sql);
				$this->db->where('('.$sql_statement.')');
				break;
				
		}
		
		$this->db->select(
			'count(inventory.product_id) as total_products', false);
		$this->db->join('category', 'category.cat_id = inventory.cat_id');
		$this->db->where('product_disabled', 0);
		$this->db->where($this->config->item('channel'), 1);
		$this->db->where('cat_hide', 0);
		
		if (!empty($cat_id)) {
			$this->db->where('inventory.cat_id', $cat_id);
		}

		if (!empty($filter_col)) {
			$this->db->where($filter_col, 1);
		}

		// Add the filter sql if it exists
		if (!empty($filter_sql_query)) {
			$this->db->where("($filter_sql_query)");
		}

		// Add price ranges to sql
		if ($filter_by_price) {
			$price_sql = '(
						CASE WHEN product_type = "variation"
						THEN
							(
								SELECT
									(CASE WHEN '.$this->channel_product_saleprice.' > 0 THEN '.$this->channel_product_saleprice.' ELSE '.$this->channel_product_price.' '.the_base_rate().' END) * '.the_vat_rate().' AS price
								FROM inventory i2
								WHERE parent_id = inventory.product_id
								AND ( 
									(CASE WHEN '.$this->channel_product_saleprice.' > 0 THEN '.$this->channel_product_saleprice.' ELSE '.$this->channel_product_price.' '.the_base_rate().' END) * '.the_vat_rate().' >= '.$price_min.'
									AND (CASE WHEN '.$this->channel_product_saleprice.' > 0 THEN '.$this->channel_product_saleprice.' ELSE '.$this->channel_product_price.' '.the_base_rate().' END) * '.the_vat_rate().' <= '.$price_max.'
								)
								LIMIT 1
							)
						ELSE
							(CASE WHEN '.$this->channel_product_saleprice.' > 0 THEN '.$this->channel_product_saleprice.' ELSE '.$this->channel_product_price.' '.the_base_rate().' END) * '.the_vat_rate().' >= '.$price_min.'
							AND (CASE WHEN '.$this->channel_product_saleprice.' > 0 THEN '.$this->channel_product_saleprice.' ELSE '.$this->channel_product_price.' '.the_base_rate().' END) * '.the_vat_rate().' <= '.$price_max.'
						END
					)';
			$this->db->where($price_sql, null, false);
		}

		// Run the query
		$query = $this->db->get('inventory');
		
		// Get the product count
		$product_count = $query->row()->total_products;
		
		// And return it
		return $product_count;
		
	}

	#------------------------------------------------------
	# Rebuild filter - used for non-category pages
	# - All we need to do is get the cat_id's and
	#   pull through the available filters accordingly
	# - @int $cat_ids = An array of category id's
	# - @string $current_cat_url = Current category url
	# - @string $keywords = search parameter
	#------------------------------------------------------
	function rebuild($cat_ids=array(), $current_cat_url, $keywords=null) {

		// Set a few vars first
		$opt 			= 5; // This is the number the filters defined in the admin will start from
		$categories 	= array();
		$subcats		= array();
		$filter_array 	= array();
		$get_url 		= array();
		$empty_group 	= 'true'; // True indicates the group is empty. We'll set this as default as we've not started anything yet!
		
		// Only rebuild if there are cat_ids
		if (!empty($cat_ids)) {
			
			$cat_count = count($cat_ids);

			// Start the group label if there are categories
			// else blank out the category group
			if (!isset($_GET['category']) && $cat_count > 1) {
					
				// Build the category array
				$filter_array[0] = array(
					'group_id'	  => null,
					'group_label' => 'Categories',
					'group_type'  => 'list',
					'group_empty' => 'false',
				);
			
			} else {
				$filter_array[0] = array(
					'group_id'	  => null,
					'group_label' => null,
					'group_type'  => 'list',
					'group_empty' => 'true',
				);
			}
			
			// Loop through each category and re-create the filters
			foreach ($cat_ids as $cat_id) {

				// BEGIN: Categories
				// Get details of this parent
				$sub = $this->category_model->getCategoryById($cat_id);

				// BEGIN: Filter urls
				$url = site_url($current_cat_url); // The current url
				
				// Retrieve the $_GET values and re-build into query string, but
				// ignore the parameter in $ignore array to prevent duplication
				if (!empty($_GET)) {
					foreach($_GET as $param=>$value) {
						$ignore = array('category', 'q', 'search', 'submit');
						if (!in_array($param, $ignore)) {
							$get_url[$param] = $value;
						}
					}
					$filter_url = "?".http_build_query($get_url);
				} else {
					$filter_url = "?";
				}
	
				// Append the sort option to the url
				$param_delimiter = (strlen($filter_url) > 1) ? "&" : "";
				$append_get_url = $param_delimiter."category=$sub->cat_id-".urlencode($sub->cat_name);
	
				// Remove '?&' if it appears in the filter url to keep it clean
				$filter_url = str_replace('?&', '?', $filter_url);
	
				// The final url
				$url = "$url$filter_url".$append_get_url;
				// END: Filter urls

				// Create some css classes that can be attached to each filter option. The selected class
				// indicates the currently selected sorting option
				$css_classes_array = array(
					'filter-group',
					'filter-option',
					'filter-group-categories',
				);
				$css_classes_array = array_filter($css_classes_array);
				$css_classes = implode(" ", $css_classes_array); //Turn the classes into a space-separated string
				
				// Do a product count here
				$product_count = $this->search_matches($sub->cat_id, $keywords, NULL, $_GET['price_min'], $_GET['price_max']);
				
				// Create the html
				$html = "<li class=\"$css_classes\"><a href=\"$url\">$sub->cat_name</a> <span>($product_count)</span></li>\n";
			
				// Add category to array
				$filter_array[0]['layer'][] = array(
					'id'	 		=> null,
					'label'	 		=> $sub->cat_name,
					'swatch' 		=> null,
					'colour'		=> null,
					'type'	 		=> null,
					'field_name' 	=> $sub->cat_name,
					'field_val'		=> null,
					'url'			=> $url,
					'css_classes'	=> $css_classes,
					'product_count'	=> $product_count,
					'field_html'	=> $html,
				);
				// END: Categories

				if ($cat_count == 1) {

					// BEGIN: Auto Price Ranges
					$prices = $this->pricerange($cat_id, true, $keywords);
					
					if (count($prices) > 1) {
			
						// Build the group array
						$filter_array[1] = array(
							'group_id'	  => null,
							'group_label' => 'Price',
							'group_type'  => 'list',
							'group_empty' => 'false',
						);
						
						//Loop through each price range and add to array
						foreach ($prices as $range) {
							
							// BEGIN: Filter urls
							$url = site_url($current_cat_url); // The current url
							
							// Retrieve the $_GET values and re-build into query string, but
							// ignore the "price_min" and "price_max" parameters in there to prevent duplication
							if (!empty($_GET)) {
								foreach($_GET as $param=>$value) {
									if ($param != "price_min" && $param != "price_max") {
										$get_url[$param] = $value;
									}
								}
								$filter_url = "?".http_build_query($get_url);
							} else {
								$filter_url = "?";
							}
				
							// Append the sort option to the url
							$param_delimiter = (strlen($filter_url) > 1) ? "&" : "";
							$append_get_url = $param_delimiter."price_min=$range->from&price_max=$range->to";
				
							// Remove '?&' if it appears in the filter url to keep it clean
							$filter_url = str_replace('?&', '?', $filter_url);
				
							// The final url
							$url = "$url$filter_url".$append_get_url;
							// END: Filter urls
			
							// Create some css classes that can be attached to each filter option. The selected class
							// indicates the currently selected sorting option
							$selected_css_class = ( ($_GET['price_min'] == $range->from) && ($_GET['price_max'] == $range->to) ) ? 'price-range-selected' : '';
				
							$css_classes_array = array(
								'filter-group',
								'filter-option',
								'filter-group-price-range',
								$selected_css_class
							);
							$css_classes_array = array_filter($css_classes_array);
							$css_classes = implode(" ", $css_classes_array); //Turn the classes into a space-separated string
								
							// Create the html
							$html = "<li class=\"$css_classes\"><a rel=\"nofollow\" href=\"$url\">$range->label</a> <span>($range->product_count)</span></li>\n";
							
							// Add range to array, but only if there are products within
							if ($range->product_count > 0) {
								$filter_array[1]['layer'][] = array(
									'id'	 		=> null,
									'label'	 		=> $range->label,
									'swatch' 		=> null,
									'colour'		=> null,
									'type'	 		=> null,
									'field_name' 	=> $range->label,
									'field_val'		=> null,
									'url'			=> $url,
									'css_classes'	=> $css_classes,
									'product_count'	=> $range->product_count,
									'field_html'	=> $html,
								);
							}
			
						}
						
					}
					// END: Auto Price Ranges
	
					// First get all the groups
					$filter_groups = $this->groups($cat_id);
					
					// Loop through the filter groups
					foreach ($filter_groups as $group) {
						
						$opt++;
						
						// Reset the empty group flag for each group
						$empty_group = 'true';
						
						// Build the group array
						$filter_array[$opt] = array(
							'group_id'	  => $group->group_id,
							'group_label' => $group->label,
							'group_type'  => $group->type,
						);
						
						// Now get the options per group
						$filter_options = $this->options($group->group_id);
						
						// Count the filter options for this group
						$count_filter_options = count($filter_options);
									
						// Start building the filter options array
						foreach($filter_options as $option) {
						
							$filter_field = "f_".slug("$group->label-$option->label");
			
							// BEGIN: Filter urls
							$url = site_url($current_cat_url); // The current url
							
							// Retrieve the $_GET values and re-build into query string
							if (!empty($_GET)) {
								foreach($_GET as $param=>$value) {
									$get_url[$param] = $value;
								}					
								$filter_url = "?".http_build_query($get_url);
							} else {
								$filter_url = "?";
							}
							
							// Append the current filter option to the url, checking if we need to add the ? or not
							// and whether it's already in the query string or not.
							if (strpos($filter_url, $filter_field) === FALSE) {
								$append_get_url = (strlen($filter_url) > 1) ? "&$filter_field=$option->filter_id" : "$filter_field=$option->filter_id";
								$selected_css_class = '';
							} else {
								$append_get_url = "";
								$selected_css_class = 'filter-selected';
								// Remove the filter from the query string if the link is clicked on again
								$filter_url = preg_replace("/&?$filter_field=$option->filter_id/", '', $filter_url);
								// Remove '?&' if it appears in the filter url to keep it clean
								$filter_url = str_replace('?&', '?', $filter_url);
							}
			
							// The final url
							$url = "$url$filter_url".$append_get_url;
							// END: Filter urls
						
							// Do a product count here
							$product_count = $this->search_matches($cat_id, $keywords, "filter_$option->filter_id", $_GET['price_min'], $_GET['price_max']);
							
							// Create the colour swatch if applicable
							if ($group->type == 'swatches') {
								$swatch = '<span class="layer-swatch" style="background-color:'.$option->colour.';display:inline-block;width:10px;height:10px;vertical-align:middle;border:1px solid #d7d7d7;"></span> ';
							} else {
								$swatch = "";
							}
			
							// Create some css classes that can be attached to each filter option
							$css_classes_array = array(
								'filter-group',
								'filter-option',
								"filter-group-$group->group_id",
								"filter-group-".slug($group->label),
								"filter-option-$option->filter_id", 
								"filter-option-".slug($option->label),
								$selected_css_class
							);
							$css_classes_array = array_filter($css_classes_array);
							$css_classes = implode(" ", $css_classes_array); //Turn the classes into a space-separated string
							
							// Create the html format of the filter group i.e. checkboxes, ranges, etc.
							switch ($group->type) {
													
								// Default is checkboxes
								default:
									$html  = "<li class=\"$css_classes\">";
									$html .= '<a href="'.$url.'" rel="nofollow">';
									$html .= $swatch;
									$html .= $option->label;
									$html .= '</a> <span>(' . $product_count . ')</span>';
									$html .= '</li>';
									$html .= "\n";
									break;
								
							}
							
							// Add option to the array (if it's NOT been selected)
							if ($product_count > 0) {
								$filter_array[$opt]['layer'][] = array(
									'id'	 		=> $option->filter_id,
									'label'	 		=> $option->label,
									'swatch' 		=> $swatch,
									'colour'		=> $option->colour,
									'type'	 		=> null,
									'field_name' 	=> $filter_field,
									'field_val'		=> "1",
									'url'			=> $url,
									'css_classes'	=> $css_classes,
									'product_count'	=> $product_count,
									'field_html'	=> $html,
								);
								
								// Okay, we've just added an option so this group is NOT empty,
								// so we can update the flag accordingly
								$empty_group = 'false';
								
							//Else blank it all...
							} else {
								$filter_array[$opt]['layer'][] = array(
									'id'	 		=> null,
									'label'	 		=> null,
									'swatch' 		=> null,
									'colour'		=> null,
									'type'	 		=> null,
									'field_name' 	=> null,
									'field_val'		=> null,
									'url'			=> null,
									'css_classes'	=> null,
									'product_count'	=> null,
									'field_html'	=> null,
								);
							}
							
						}
						
						$filter_array[$opt]['group_empty'] = $empty_group;
						
					}
					
				}

			}
			
		}
		
		#echo "<pre>" . print_r($filter_array, true) . "</pre>"; //For testing
		return $filter_array;
		
	}
	
}