<?php 
#------------------------------------------------------
# Core Shopit Library
# - Contains a number of functions available as core.
# - This library should not be deleted from installation.
#------------------------------------------------------
if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Core {

	#------------------------------------------------------
	# Breadcrumbs
	# @ $loaf = array()
	#------------------------------------------------------
	function breadcrumb($loaf=array()) {
		
		// Reset vars
		$html = "";
		
		// If $loaf array is not empty then create the list
		if (!empty($loaf)) {
		
			// Clean up the array of null keys
			unset($loaf[null]);
			
			// Start of the list with the "Home" link
			$html  = "<ul id=\"breadcrumb\">\n";
			$html .= '<li class="first"><a href="'.site_url().'">Home</a></li>'."\n";
			
			// Add the additional crumbs
			foreach($loaf as $crumb=>$link) {
				if ($link != "") {
					$html .= '<li><a href="'.$link.'">'.$crumb.'</a></li>'."\n";
				} else {
					$html .= "<li class=\"last\">$crumb</li>\n";
				}
			}
			
			// And close off the list
			$html .= "</ul>";
			
		} else {
			// If $loaf is empty then display nothing
			$html = "";
		}
		
		// Return the complete html breadcrumb
		return $html;
		
	}

	#------------------------------------------------------
	# Get Products
	#------------------------------------------------------
	function get_products($criteria=null, $limit=20, $order_by='min_price asc', $exclude_product=null) {
	
		$shopit =& get_instance();
		$shopit->load->model('products_model');

		$sql = 'select 
					inventory.product_id, 
					inventory.cat_id, 
					product_name,
					product_type,
					product_brand,
					product_brand_slug, ';
		
		// Custom field columns
		$sql .= $this->custom_fields_sql();

		$sql .= '(case when product_type = "variation" 
						then 
							(select min(product_price) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
						else 
							product_price 
					end) ' . the_base_rate() . ' as product_price,
					(case when product_type = "variation" 
						then 
							(select min(product_saleprice) from inventory i1 where parent_id = inventory.product_id and product_saleprice > 0 and product_disabled = 0) 
						else product_saleprice 
					end) as product_saleprice,
					(case when product_type = "variation" 
						then 
							(select min((case when product_saleprice > 0 then product_saleprice else product_price ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0) 
						else
							(case when product_saleprice > 0 then product_saleprice else product_price ' . the_base_rate() . ' end)
					end) as min_price,
					(case when product_type = "variation" 
						then 
							(select max((case when product_saleprice > 0 then product_saleprice else product_price ' . the_base_rate() . ' end)) from inventory i1 where parent_id = inventory.product_id and product_disabled = 0)
						else
							(case when product_saleprice > 0 then product_saleprice else product_price ' . the_base_rate() . ' end)
					end) as max_price, ';
		
		$sql .=		' product_slug,
					cat_slug, 
					product_image, 
					product_description, 
					product_excerpt, 
					' . $shopit->config->item('stock_location').' as product_qty, 
					product_views, 
					date_added, 
					priority ';
		$sql .= 'from inventory
				join category c1 on c1.cat_id = inventory.cat_id
				where product_disabled = 0
				and c1.cat_hide = 0 
				and ' . $shopit->config->item('channel') . ' = 1 ';
		
		if ($exclude_product != null) {
			$sql .= " and inventory.product_id != $exclude_product ";
		}
		
		if ($criteria != null) {
			$sql .= " and ( $criteria ) ";
		}
		
		if ($order_by != FALSE) {
			$sql .=	" order by $order_by ";
		}
		
		if ($limit != FALSE) {
			$sql .=	" limit $limit ";
		}
					
		$query = $shopit->db->query($sql);

		// Get custom field templates
		$customfield_templates = $shopit->products_model->customFieldTemplates();
		$cf = array();
						
		if ($query->num_rows() > 0) {
			
			foreach ($query->result() as $item) {

				$p++;
	
				$product_image = $this->_displayThumbnail($item);

				//Get product url
				$url = $this->_product_url($item->cat_father_id, $item);
				
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

				$item = array(
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
					'url'						=> $url,
					'css_classes'				=> $css_class,
					//Extra variables
					'get_product_price' 		=> money($item->product_price),
					'get_product_price_exvat'	=> money($item->product_price_exvat, true, true, false),
				);

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

				// Merge the two arrays (item data and item's custom fields)
				$data[] = array_merge($item, $cf);
				
			}
			
			return $data;

		} else {
		
			return array();
		
		}
		
	}

	#------------------------------------------------------
	# Display thumbnails
	# - on categories pages & related/similar items
	#   and featured product, collections
	#------------------------------------------------------
	function _displayThumbnail($item, $size=null) {
	
		$shopit =& get_instance();

		$thumb_size = ($size == null) ? $shopit->config->item('thumbnail_width') : $size;

		if ($item->product_image != null):
			$image = explode(';', $item->product_image);
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
	private function _product_url($cat_father_id,$item) {
		
		$shopit =& get_instance();
		
		if($cat_father_id > 0):
			$child = $shopit->category_model->getCategorySlug($cat_father_id);
				
			if ($child->cat_father_id > 0):
				$parent = $shopit->category_model->getCategorySlug($child->cat_father_id);
				$url = site_url($parent->cat_slug . '/' . $child->cat_slug . '/' . $item->cat_slug . '/' . $item->product_slug . '/' . $item->product_id);
			else:
		    	$url = site_url($child->cat_slug . '/' . $item->cat_slug . '/-/' . $item->product_slug . '/' . $item->product_id);
			endif;
		
		else:
		    $url = site_url($item->cat_slug . '/-/-/' . $item->product_slug . '/' . $item->product_id);
		endif;
		
		return $url;
		
	}

	#------------------------------------------------------
	# Product URL (without extra database call)
	# - format the product url(slug) to include seo words
	# - pass the cat_father_id and $item array (product data)
	# - This one should eventually replace the one above.
	# @param $item (array)	Object array containing all item
	# 						information
	# @returns URL
	#------------------------------------------------------
	function product_url($item) {
		
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
	# Collection Items
	# @returns a list of product_ids
	# @param $collection_id is database id of collection
	# @param $separator is the item separator
	# @param $include_field_name will output with "product_id="
	#------------------------------------------------------
	function get_collection_items($collection_id, $separator="|", $include_field_name=true) {
		
		$shopit =& get_instance();
		
		$shopit->db->select('product_id');
		$shopit->db->where('collection_id', $collection_id);
		$shopit->db->order_by('collection_items.order');
		$query = $shopit->db->get('collection_items');
		
		if ($query->num_rows() > 0) {
			
			foreach ($query->result() as $item) {
				
				$label = ($include_field_name) ? 'inventory.product_id=' : '';
				
				$data[] = $label.$item->product_id;
				
			}
			
			// Return $separator string
			$data = implode($separator, $data);
			
		} else {
			$data = NULL;
		}
		
		return $data;
		
	}

	#------------------------------------------------------
	# Get categories within $cat_id
	# @returns array
	# @param $cat_id = integer
	#------------------------------------------------------
	function get_categories($cat_id) {
	
		$shopit =& get_instance();
	
		$sql = 'select 
					c2.cat_id, 
					c1.cat_name as parent, 
					c2.cat_name as child, 
					CONCAT_WS("/",c1.cat_slug,c2.cat_slug) as url, 
					c2.cat_order, 
					c2.cat_slug, 
					c2.cat_image,
					(SELECT 
						REPLACE(LEFT(product_image, LOCATE(";", product_image)), ";", "") AS product_image
					FROM inventory 
					LEFT JOIN xcat ON inventory.product_id = xcat.product_id
					WHERE inventory.cat_id = (
						CASE WHEN c3.cat_id IS NULL THEN
							c2.cat_id
						ELSE
							c3.cat_id
						END)
					OR xcat.cat_id = (
						CASE WHEN c3.cat_id IS NULL THEN
							c2.cat_id
						ELSE
							c3.cat_id
						END)
					AND product_image IS NOT NULL
					AND product_disabled = 0 
					AND ' . $shopit->config->item('channel') . ' = 1 
					ORDER BY product_price ASC
					LIMIT 1) 
					AS product_image
				from category c1
				inner join category c2 on c1.cat_id = c2.cat_father_id
				left join category c3 on c2.cat_id = c3.cat_father_id
				where c1.cat_id = ' . $cat_id . ' 
				and (c2.cat_hide = 0 or c3.cat_hide = 0)
				and (SELECT 
						COUNT(product_id)
					FROM inventory 
					WHERE cat_id = (
						CASE WHEN c3.cat_id IS NULL THEN
							c2.cat_id
						ELSE
							c3.cat_id
						END)
					AND product_image IS NOT NULL
					AND product_disabled = 0 
					AND channel_1 = 1 
					ORDER BY product_price ASC
					LIMIT 1) 
					> 0
				group by c2.cat_id
				order by cat_order asc, child asc';		
	
		$query = $shopit->db->query($sql);
		
		if ($query->num_rows() > 0) {
			return $query->result();
		} else {
			return (object) array();
		}
	
	}

	#------------------------------------------------------
	# Get Item
	# @returns basic item information, e.g. name, price,
	# image, etc.
	# @param $product_id (int) - Database id of item
	# @param $size (int) - Image size in pixels
	#------------------------------------------------------
	function get_item($product_id, $size=null) {
		
		$shopit =& get_instance();
		$shopit->load->model('products_model');
		
		// Get item 
		$item = $shopit->products_model->getItem($product_id);
		
		//Get custom field templates
		$customfield_templates 			= $shopit->products_model->customFieldTemplates();
		$variant_customfield_templates  = $shopit->products_model->customFieldTemplates('inventory', true);
		$cf_variant_array = array();
		$cf = array();

		if (!empty($item)) {

			// Get the correct url
			$url = $this->_product_url($item->cat_father_id, $item);
			
			//Define if this item is on sale or not
			$onsale = ($item->product_saleprice > 0) ? TRUE : FALSE;
			
			//Some useful css classes
			$onsale_css_class = ($onsale) ? "sale-item" : "";
			$css_classes = array("productid-$item->product_id", $item->product_brand_slug, $onsale_css_class, $item->product_type, $item->cat_slug);
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
				'product_id' 				=> $item->product_id,
				'cat_id' 	 				=> $item->cat_id,
				'product_type'				=> $item->product_type,
				'product_name' 				=> $item->product_name,
				'product_description' 		=> $item->product_description,
				'product_excerpt' 			=> $item->product_excerpt,
				'product_summary'			=> get_first_paragraph($item->product_description),
				'product_brand' 			=> $item->product_brand,
				'product_brand_slug'			=> $item->product_brand_slug,
				'product_code'				=> $item->product_no,
				'product_price'				=> money($item->min_price),
				'product_price_exvat'		=> money($item->min_price, true, true, false),
				'product_saleprice'			=> money($item->product_saleprice),
				'product_saleprice_exvat'	=> money($item->product_saleprice_exvat, true, true, false),
				'max_price'					=> money($item->max_price),
				'max_price_exvat'			=> money($item->max_price, true, true, false),
				'product_price_range'		=> $product_price_range,
				'product_price_range_exvat'	=> $product_price_range_exvat,
				'product_image' 			=> $this->_displayThumbnail($item, $size),
				'url'						=> $url,
				'css_classes'				=> $css_class,
				//Extra variables
				'get_product_price' 		=> money($item->product_price),
				'get_product_price_exvat'	=> money($item->product_price_exvat, true, true, false),
				//Modules
				'shopit:specialoffers'		=> $special_offers,
			);
			
			// Merge the two arrays (item data and item's custom fields)
			$data = array_merge($this_product, $cf);

			return $data;

		} else {
			return array();
		}

	}

	#------------------------------------------------------
	# Custom fields SQL
	# - Loops through each custom field template and creates 
	#   the database column named after the custom field label.
	# @returns SQL (string)
	#------------------------------------------------------
	function custom_fields_sql($type='inventory', $variant=false) {
		
		$shopit =& get_instance();
		$shopit->load->model('products_model');
		
		$sql = "";
		
		$query = $shopit->products_model->customFieldTemplates($type, $variant);
		
		if ($query !== false) {
			foreach($query as $field) {
				$sql .= sprintf(' (SELECT custom_field_data FROM custom_field_values WHERE custom_field_label = "%s" AND id = inventory.product_id ORDER BY custom_field_id DESC LIMIT 1) AS "%s", ', $field->custom_field_label, $field->custom_field_label); 
			}
		}
		
		return $sql;
		
	}

	#------------------------------------------------------
	# Get custom field
	# - Returns custom field data
	# @param $product_id (int) 		- Database ID of product
	# @param $field_label (string) 	- Custom field label as set in the database e.g. custom_my-custom-field
	# @param $type (string) 		- 'inventory' or 'orders'
	# @param $return_all (bool) 	- Return only the data (value) or the complete db row
	#------------------------------------------------------
	function get_custom_field($product_id, $field_label, $type='inventory', $return_all=false) {

		$shopit =& get_instance();
		
		$shopit->db->select('custom_field_title, custom_field_data');
		$shopit->db->from('custom_field_values');
		$shopit->db->join('custom_field_templates', 'custom_field_values.custom_field_label = custom_field_templates.custom_field_label');
		$shopit->db->where('custom_field_for', $type);
		$shopit->db->where('custom_field_values.id', $product_id);
		$shopit->db->where('custom_field_values.custom_field_label', $field_label);
		$query = $shopit->db->get();
	
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
	# Create collections nav
	# @param $collection_group_id (int)	ID of collection group
	# @returns UL list of collections
	#------------------------------------------------------
	function collections_list($collection_group_id=0){
		
		$shopit =& get_instance();
		$shopit->load->model('category_model');
		
		$html = '';
		
		// Get the collections for this group id
		$collections = $shopit->category_model->getCollectionGroup($collection_group_id);
		
		// And loop through them, creating our list on the way
		if (count($collections) > 0) {
			
			$html .= sprintf('<ul class="shopit-collection-group-%s">'."\n", $collection_group_id);
			
			foreach($collections as $item) {
				
				$html .=  sprintf('<li><a href="%s">%s</a></li>'."\n", site_url('collections/'.$item->collection_slug), $item->collection_name);
				
			}
			
			$html .= "</ul>\n";
			
		}
		
		// Output the html
		return $html;
		
	}
	
	#------------------------------------------------------
	# Dynamic sub-categories
	# - Retrieves a list of immediate child sub categories
	#   together with category images (if assigned)
	# @param $cat_id (int)			Category father ID to fetch
	# @param $colour (string) 		RGBa colour codes
	# @param $size (int)			Width of the image
	# @param $title_tag (string)	'h3' or other html tag
	# @param $ignore_cat_image (bool)	Ignore cat_image
	#------------------------------------------------------
	function dynamic_categories($cat_id, $colour='255,255,255', $size=null, $title_tag='h3', $ignore_cat_image=false) {
		
		$shopit =& get_instance();
		
		// Set a few bits here
		$html = '';
		$i = 0;
		
		// if $size is not set, then set is as per the config
		$size = (empty($size)) ? $shopit->config->item('thumbnail_width') : $size;
		
		// Get subcategories for this category
		$subcats = $this->get_categories($cat_id);
		
		if (count($subcats) > 0) {
			
			$html .= sprintf('<ul class="shopit-dyn-categories shopit-cat-%s">'."\n", $cat_id);
			
			// If there are sub-categories, loop through
			// them and do the necessary
			foreach($subcats as $sub) {
				
				$i++;
				
				// Set some css classes for this sub-category
				$css_classes = array("cat-$sub->cat_id", "cat-item-$i");
				$css_class = implode(" ", $css_classes);
				
				// If there's a category image get it, otherwise get the first product's image.
				// And if there's still no image, we'll just create a random shade of $colour.
				if ($sub->cat_image != '' && !$ignore_cat_image) {
					$cat_image = sprintf('<img src="%s" width="%s" alt="%s" title="%s" />', base_url("docs/$sub->cat_image"), $size, $sub->child, $sub->child);
					$shades = '';
				} elseif ((empty($sub->cat_image) || $ignore_cat_image) && !empty($sub->product_image)) {
					$cat_image = sprintf('<img src="%s/%s" alt="%s" />', site_url("image/resize/$sub->product_image"), "$size/$size", $sub->child);
					$shades = '';
				} else {
					$cat_image = '';
					$shades = sprintf(' style="background-color:rgba(%s,%s);"', $colour,  (rand(300,750)/1000));
				}
				
				// Set the url based on the current category. We're not using 
				// $sub->url as it's not always correct on third tiers.
				$url = sprintf('%s/%s', current_url(), $sub->cat_slug);
				
				// Now we can start creating our html
				$html .= sprintf('<li class="%s"%s>', $css_class, $shades);
				$html .= sprintf('<div><a href="%s">%s</a></div>', $url, $cat_image);
				$html .= sprintf('</a>');
				$html .= sprintf('<%s><a href="%s">%s</a></%s>', $title_tag, $url, $sub->child, $title_tag);
				$html .= "</li>\n";
				
			}
			
			$html .= "<ul>\n";
			
		}
		
		return $html;
		
	}
	
	#------------------------------------------------------
	# Basket summary
	# - A more detailed version on the the basket summary
	#   which outputs product names, prices, quantities
	#   and images.
	# @param $thumbnail_size (int)	Image size in pixels
	# @param $include_vat (bool)	True or False
	# @return (string) UL list of basket items
	#------------------------------------------------------
	function mybasket($thumbnail_size=50, $include_vat=false) {
		
		$shopit =& get_instance();
		
		// Set a few bits here
		$html = '';
		
		// Load the basket model
		$shopit->load->model('basket_model');
		
		// Get the contents of my basket
		$basket = $shopit->basket_model->getBasket();
		
		// If there is anything in the basket, loop through 
		// each item and generate our list
		if (count($basket) > 0) {
			
			$html .= "<ul>\n";
			
			foreach ($basket as $item) {
				
				// Get the url to the product page
				$url = site_url(get_product_slug($item->parent_id));
				
				$html .= "<li>\n";
				$html .= "<ul>\n";
				$html .= sprintf('<li class="shopit-mybasket-image">%s</li>', get_image($item->parent_id, $thumbnail_size, $thumbnail_size));
				$html .= sprintf('<li class="shopit-mybasket-name"><a href="%s">%s</a></li>', $url, $item->product_name);
				$html .= sprintf('<li class="shopit-mybasket-price">%s x %s</li>', $item->product_qty, money($item->product_price, true, true, $include_vat));
				$html .= "</ul>\n";
				$html .= "</li>\n";
				
			}
		
			$html .= "</ul>\n";
			
		}
		
		return $html;
		
	}
	
	#------------------------------------------------------
	# Get cross-sells
	# - Returns all cross-sells based on the passed type
	# @param $product_id (int)		Parent or single product ID
	# @param $type (string)			Cross-sell type e.g. R, S and any custom type
	# @param $ignore_type (string)	Ignore product type e.g. NULL or 'variant', 'variation'
	# @param $image_size (int)		Size of the product thumbnail - If NULL config setting is used
	# @param $parser_var (string)	Template parser tag to output e.g. 'relateditems' or 
	# 								'extraitems'. This will only be used within a controller.
	#------------------------------------------------------
	function get_cross_sells($product_id, $type='R', $ignore_type=NULL, $image_size=NULL, $parser_var=NULL) {
		
		$shopit =& get_instance();
		
		// Set a few bits here
		$data = array();
		
		// Get the cross-sells for this product and $type
		$items = $shopit->products_model->getCrossSells($product_id, $type, $ignore_type);
		
		if (!empty($items)){
			
			//Product counter
			$p = 0;
			
			//Get custom field templates
			$customfield_templates = $shopit->products_model->customFieldTemplates();
			$cf = array();
						
			foreach ($items as $item) {
			
				$p++;
				
				//Get product url
				$item_url = $this->_product_url($item->cat_father_id, $item);
				
				//Get product image
				$item_productimage = $this->_displayThumbnail($item, $image_size);

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
				
				// Product name
				$product_name = ($item->product_type == 'variant') ? sprintf('%s - %s', $item->parent_name, unserialize_variant($item->product_name)) : $item->product_name;
				
				$this_product = array (
					'item_count'			=> $p,
					'item_type' 			=> $item->product_type,
					'item_name' 			=> $product_name,
					'item_code'				=> $item->product_no,
					'item_brand'			=> $item->product_brand,
					'item_brand_slug'		=> $item->product_brand_slug,
					'item_image' 			=> $item_productimage,
					'item_price' 			=> money($item->min_price),
					'item_price_exvat'		=> money($item->min_price, true, true, false),
					'item_max_price'		=> money($item->max_price),
					'item_max_price_exvat' 	=> money($item->max_price, true, true, false),
					'item_price_range'		=> $product_price_range,
					'item_price_range_exvat'=> $product_price_range_exvat,
					'item_product_slug' 	=> $item->product_slug,
					'item_product_id' 		=> $item->xitem_id,
					'item_url'				=> $item_url,
					'item_description'		=> $item->product_description,
					'item_excerpt'			=> $item->product_excerpt,
					'item_summary'			=> get_first_paragraph($item->product_description),
					'css_classes'			=> $css_class,
				);
				
				// Merge the two arrays (item data and item's custom fields)
				if (isset($parser_var)) {
					$data[$parser_var][] = array_merge($this_product, $cf);
				} else {
					$data[] = array_merge($this_product, $cf);
				}
						
			}
		
		}
		
		if (isset($parser_var)) {
			return $data[$parser_var];
		} else {
			return $data;
		}
		
	}

	#------------------------------------------------------
	# API key check
	# - Check for a valid API key
	# @param $key (string)	API key generated in Shopit Admin
	# @return (bool) true or false
	#------------------------------------------------------
	function api($key) {
		
		$shopit =& get_instance();
		
		$shopit->db->where('key', $key);
		$shopit->db->where('status', 1);
		$query = $shopit->db->get('api_keys');
		
		if ($query->num_rows() > 0) {
			return true;
		} else {
			return false;
		}
		
	}

	#------------------------------------------------------
	# Returns the payment methods as submit buttons, etc
	# for use in the basket.
	# @returns HTML containing submit buttons, dropdowns, etc
	#------------------------------------------------------
	function payment_options($label_prefix="Confirm &amp; Pay with") {
	
		// Set the CI instance
		$shopit =& get_instance();
		
		// Set some defaults
		$payment_option = "";
		$i = 0;
		$html = "";
	
		// Set the list of available payment gateways
		// and whether they are turned on or not
		$payment_options = array(
			// Primary gateways first
			'payment_sagepay'  	  => $shopit->config->item('payment_sagepay'),
			'payment_worldpay' 	  => $shopit->config->item('payment_worldpay'),
			'payment_cardsave' 	  => $shopit->config->item('payment_cardsave'),
			'payment_barclaycard' => $shopit->config->item('payment_barclaycard'),
			// We'll put PayPal last to indicate 
			// this as a second payment option
			'payment_paypal'      => $shopit->config->item('payment_paypal'),
		);
	
		// Loop through the gateways
		foreach ($payment_options as $gateway=>$setting) {
			
			// If gateway is on (true)
			if ($setting == 'true') {
			
				// Increment the counter
				$i++;
				
				// Check the first option by default
				$payment_checked = ($i == 1) ? 'checked="checked"' : '';
			
				// Set the option value that we send to the checkout script
				switch($gateway) {
					
					case 'payment_sagepay':
						$payment_value = 'SagePay';
						break;
	
					case 'payment_cardsave':
						$payment_value = 'CardSave';
						break;
	
					case 'payment_worldpay':
						$payment_value = 'WorldPay';
						break;
	
					case 'payment_barclaycard':
						$payment_value = 'Barclaycard';
						break;
	
					case 'payment_paypal':
						$payment_value = 'PayPal';
						break;
					
				}
	
				// Set the label: Credit/debit card, PayPal
				if ($gateway != 'payment_paypal') {
					$payment_label = 'Credit/Debit Card';
				} elseif ($gateway == 'payment_paypal') {
					$payment_label = 'PayPal';
				
		}
				// Create a list of useful css classes
				$css_classes = array(
					'payment-option', 
					strtolower("payment-option-$payment_value"),
					'payby-'.slug($payment_label),  //payby-{credit-debit-card|paypal}
				);
				
				$css_class = implode(" ", $css_classes); //Turn the classes into a space-separated string
				
				// Create the html for this payment option 
				$payment_option .= sprintf('<li class="%s">' . "\n", $css_class);
	
				switch ($payment_value) {
					
					case "SagePay";
					case "CardSave";
					case "WorldPay";
					case "Barclaycard":
						$payment_option .= sprintf('<button type="submit" class="%s" name="gateway" value="%s">%s %s</button>'."\n", $css_class, $payment_value, $label_prefix, "Card");
						break;
					
					case "PayPal":
						$payment_option .= sprintf('<button type="submit" class="%s" name="gateway" value="%s">%s %s</button>'."\n", $css_class, $payment_value, $label_prefix, "PayPal");
						break;
					
				}
				
				$payment_option .= "</li>\n";
				
			}
			
		}
	
		$html  = "<ul id=\"shopit-payment-methods\">\n";
		$html .= $payment_option;
		if (function_exists('additional_payment_options')) {
			$html .= additional_payment_options();
		}
		$html .= "</ul>\n";
		
		return $html;
		
	}

	#------------------------------------------------------
	# My Basket
	# - Returns the basket total and total items
	# @returns Array
	#------------------------------------------------------
	function mybasket_summary() {

		$shopit =& get_instance();
		
		$basket = $shopit->basket_model->basketSummary();

		if ($basket->items > 0) {
			$data = (object) array(
				'itemtotal' 		 => $basket->items . ' items - ',
				'items' 			 => $basket->items, 
				'baskettotal' 		 => money($basket->total),
				'baskettotal_exvat'  => money($basket->total, true, true, false),
			);
		} else {
			$data = (object) array(
				'itemtotal' 		 => '0 items',
				'items'			 	 => '0',
				'baskettotal' 		 => money(0),
				'baskettotal_exvat'  => money(0)
			);
		}

		return $data;
	}

	#------------------------------------------------------
	# Get Brands
	# @returns Array of brands 
	#------------------------------------------------------
	function get_brands() {
		
		$shopit =& get_instance();
		return $shopit->category_model->getBrands();
		
	}

}