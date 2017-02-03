<?php

class Google_model extends CI_Model {
	
	function Google_model() {
		parent::__construct();
		$this->load->helper('file');
		$this->load->model('shipping_model');
		$this->load->model('inventory_model');
		$this->load->library('google_feed');
	}

	#------------------------------------------------------
	# Get total number of products
	#------------------------------------------------------
	function total_products() {
		$this->db->select('product_id');
		$query = $this->db->get('inventory');
		
		return $query->num_rows();
	}

	#------------------------------------------------------
	# Update feeds
	#------------------------------------------------------	
	function generate($message="", $use_product_total=true, $manual=false){
		// UPDATE: Runs on force (via uri segment 3)
		if ( $this->uri->segment(3) == "manual" || $manual == true)  {
			
			// Get the sales channels so we can create an XML file for each one
			$locations = $this->inventory_model->getLocations();
			
			foreach ($locations as $channel) {
				
				// File name to save as
				$save_as = "base_$channel->shortname.xml";
				
				// The db col name
				$channel_field = "channel_$channel->id";
				
				// If this is a website, then set the base_url as the note
				$base_url = ($channel->type == "website") ? $channel->note : NULL;
				
				$this->base(true, $save_as, $channel_field, $base_url);
			}
			
			// Now create the sitemap file (only one)
			$this->xml_sitemap();
		    return "Google XML feeds updated successfully.";
		}
		
		// THIS CODE HAS BEEN DISABLED WITH THE INTRODUCTION OF THE 
		// DAILY CRON JOB ~ SEE THE CRON CONTROLLER
		/*
		if ($this->total_products() <= 2000 || $use_product_total == false) {
			$this->base();
			$this->xml_sitemap();
		    if (!empty($message)) {
		    	return $message;
		    }
	    
	    } else {
	    	return  'You will need to regenerate your Google feeds manually. <a href="'.site_url('options/googlise').'">Click here to do this now</a> or you can do it later.';
	    }
		*/
	}

	#------------------------------------------------------
	# Google XML Sitemap generation
	# - Creates xml sitemap on the server
	#
	# Notes:
	# <url>
	#    <loc>http://www.example.com/</loc>
	#    <lastmod>2005-01-01</lastmod> (optional)
	#    <changefreq>monthly</changefreq> (optional)
	#    <priority>0.8</priority> (optional)
	# </url> 
	#------------------------------------------------------
	function xml_sitemap() {
		
		//Begin XML structure
	    $xml_file  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";    	
	    $xml_file .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

		//Add homepage
		$xml_file .= '<url>' . "\n";
		$xml_file .= '<loc>' . site_root() . '</loc>' . "\n";
		$xml_file .= '<changefreq>monthly</changefreq>' . "\n";
		$xml_file .= '<priority>0.8</priority>' . "\n";
		$xml_file .= '</url>' . "\n";

		//Get static pages 		
		$this->db->select('page_name, page_slug, page_redirect, page_type');
		$this->db->where('(page_redirect is NULL OR page_redirect = "")');
		$this->db->where('page_sitemap','1');
		$this->db->where('page_id !=', 1);
		$this->db->where('site', 'website');
		$this->db->order_by('page_order','asc');
		$pages = $this->db->get('pages');

		if ($pages->num_rows > 0) {

			foreach($pages->result() as $page):
					
				$xml_file .= '<url>' . "\n";
				$xml_file .= '<loc>' . site_root($this->config->item('site_root_index_page') . 'page/' . $page->page_slug) . '</loc>' . "\n";
				$xml_file .= '<changefreq>monthly</changefreq>' . "\n";
				$xml_file .= '<priority>0.8</priority>' . "\n";
				$xml_file .= '</url>' . "\n";
						
			endforeach;

		}

		// Get main category pages - retrieve everything we need here in a single query
		$sql = "select 
				cat_id,
				cat_slug,
				(select cat_father_id from category where cat_id = s.cat_id and cat_hide = 0) as cat_father_id,
				(select cat_slug from category where cat_id = (select cat_father_id from category where cat_id = s.cat_id and cat_hide = 0)) as parent_cat_slug,
				(select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = s.cat_id and cat_hide = 0)) as parent_cat_father_id,
				(select cat_slug from category where cat_id = (select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = s.cat_id)) and cat_hide = 0) as ancestor_cat_slug,
				(select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = s.cat_id)) and cat_hide = 0) as ancestor_cat_father_id
			from category s
			where cat_hide = 0
			order by cat_id
		";
		
		$item_categories = $this->db->query($sql);
		
		foreach($item_categories->result() as $category) {
		
			//Is parent category
			if ($category->cat_father_id == 0):
				$category_slug = $category->cat_slug . '';
			else:
				//Is second-tier category
				if ($category->parent_cat_father_id == 0):
					$category_slug = $category->parent_cat_slug . '/' . $category->cat_slug;
				else:						
					//Is third-tier category
					$category_slug = $category->ancestor_cat_slug . '/' . $category->parent_cat_slug . '/' . $category->cat_slug;
				endif;
			endif;

			$xml_file .= '<url>' . "\n";
			$xml_file .= '<loc>' . site_root($this->config->item('site_root_index_page') . $category_slug) . '</loc>' . "\n";
			$xml_file .= '<changefreq>daily</changefreq>' . "\n";
			$xml_file .= '<priority>0.8</priority>' . "\n";
			$xml_file .= '</url>' . "\n";
		
		}
		
		//Get inventory items (single or parent variations)
		$sql = "select 
				product_id, 
				cat_id,
				product_slug,
				(select cat_name from category where cat_id = s.cat_id) as cat_name,
				(select cat_slug from category where cat_id = s.cat_id) as cat_slug,
				(select cat_father_id from category where cat_id = s.cat_id) as cat_father_id,
				(select cat_slug from category where cat_id = (select cat_father_id from category where cat_id = s.cat_id)) as parent_cat_slug,
				(select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = s.cat_id)) as parent_cat_father_id,
				(select cat_slug from category where cat_id = (select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = s.cat_id))) as ancestor_cat_slug,
				(select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = s.cat_id))) as ancestor_cat_father_id
			from inventory s
			where product_disabled = 0
			and (product_type = 'single' OR product_type = 'variation')
			order by cat_id
		";

		$inventory = $this->db->query($sql);

		if ($inventory->num_rows > 0) {
		
			foreach($inventory->result() as $item) {
			
				//Is a parent category
				if ($item->cat_father_id == 0) {
					$item_slug = $item->cat_slug . '/-/-';
				} else {
					//Is second category
					if ($item->parent_cat_father_id == 0) {
						$item_slug = $item->parent_cat_slug . '/' . $item->cat_slug . '/-';
					} else {
						$item_slug = $item->ancestor_cat_slug . '/' . $item->parent_cat_slug . '/' . $item->cat_slug;
					}
				}
			
				$xml_file .= '<url>' . "\n";
				$xml_file .= '<loc>' . site_root($this->config->item('site_root_index_page') . $item_slug . '/' .   $item->product_slug . '/' . $item->product_id) . '</loc>' . "\n";
				$xml_file .= '<changefreq>monthly</changefreq>' . "\n";
				$xml_file .= '<priority>0.8</priority>' . "\n";
				$xml_file .= '</url>' . "\n";
			
			}
		
		}

		//Close XML file and write to server
	    $xml_file .= '</urlset>';	
	    write_file($_SERVER['DOCUMENT_ROOT'] . '/base/sitemap.xml', $xml_file);

	}
	
	#------------------------------------------------------
	# Google Base feed generation
	#------------------------------------------------------
	function base($download=true, $save_as='base_website.xml', $channel='channel_1', $base_url=NULL) {
	
		// Set the correct base_url
		if ($base_url == NULL) {
			$base_url = site_root($this->config->item('site_root_index_page'));
		} else {
			$base_url = "http://$base_url/";
		}
	
		//Begin XML structure
		$xml_file  = '<' . '?xml version="1.0" encoding="UTF-8" ?' . '>' . "\n";
		$xml_file .= '<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">' . "\n";
		$xml_file .= '<channel>' . "\n";
		$xml_file .= '<title>' . $this->config->item('store_name') . '</title>' . "\n";
		
		// This big query gets everything we need - all the variants first, with 
		// parent items details and some category data attached
		$sql = "select 
					v.product_type,
					v.product_id as variant_id,
					v.parent_id,
					p.product_id,
					v.product_name as variant_name, 
					v.product_no,
					v.product_ean,
					v.product_upc,
					v.product_mpn,
					v.product_price,
					v.product_saleprice,
					(case when v.product_saleprice > 0 then v.product_saleprice else v.product_price end) as price,
					v.product_weight,
					p.product_condition,
					v.location_1 as product_qty, 
					v.product_disabled as variant_disabled,
					p.product_name as parent_name,
					p.product_brand,
					p.product_description,
					p.product_slug,
					p.product_image,
					p.product_disabled as parent_disabled,
					p.cat_id,
					(select cat_name from category where cat_id = p.cat_id) as cat_name,
					(select cat_slug from category where cat_id = p.cat_id) as cat_slug,
					(select cat_father_id from category where cat_id = p.cat_id) as cat_father_id,
					(select cat_slug from category where cat_id = (select cat_father_id from category where cat_id = p.cat_id)) as parent_cat_slug,
					(select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = p.cat_id)) as parent_cat_father_id,
					(select cat_slug from category where cat_id = (select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = p.cat_id))) as ancestor_cat_slug,
					(select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = p.cat_id))) as ancestor_cat_father_id
				from inventory v
				left join inventory p on v.parent_id = p.product_id 
				where v.product_type = 'variant'
				and v.product_disabled = 0
				and p.$channel = 1
				and (
					/* check if parent is disabled */
					select product_disabled 
					from inventory
					where product_id = v.parent_id
					limit 1
					) = 0
				and (
					/* check if category is disabled */
					select cat_hide
					from category 
					where (
						select cat_id
						from inventory
						where product_id = v.parent_id
						limit 1
						) = cat_id
					) = 0 
		";
		
		// Union the second query
		$sql .= " UNION ALL ";
		
		// The second query for single items
		$sql .= "
				select 
					v.product_type,
					v.product_id as variant_id,
					NULL as parent_id,
					v.product_id,
					NULL as variant_name, 
					v.product_no,
					v.product_ean,
					v.product_upc,
					v.product_mpn,
					v.product_price,
					v.product_saleprice,
					(case when v.product_saleprice > 0 then v.product_saleprice else v.product_price end) as price,
					v.product_weight,
					v.product_condition,
					v.location_1 as product_qty, 
					'0' as variant_disabled,
					v.product_name as parent_name,
					v.product_brand,
					v.product_description,
					v.product_slug,
					v.product_image,
					v.product_disabled as parent_disabled,
					v.cat_id,
					(select cat_name from category where cat_id = v.cat_id) as cat_name,
					(select cat_slug from category where cat_id = v.cat_id) as cat_slug,
					(select cat_father_id from category where cat_id = v.cat_id) as cat_father_id,
					(select cat_slug from category where cat_id = (select cat_father_id from category where cat_id = v.cat_id)) as parent_cat_slug,
					(select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = v.cat_id)) as parent_cat_father_id,
					(select cat_slug from category where cat_id = (select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = v.cat_id))) as ancestor_cat_slug,
					(select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = (select cat_father_id from category where cat_id = v.cat_id))) as ancestor_cat_father_id
				from inventory v
				where v.product_type = 'single'
				and v.product_disabled = 0
				and v.$channel = 1
				and (
					/* check if category is disabled */	
					select cat_hide
					from category 
					where cat_id = v.cat_id
					) = 0 
		";
		
		// Order the results
		$sql .= "order by product_id asc, variant_id asc";
		
		$inventory = $this->db->query($sql);
		
		if ($inventory->num_rows > 0):
		
			foreach ($inventory->result() as $item):
	
				//Is a parent category
				if ($item->cat_father_id == 0):					
					$item_slug = $item->cat_slug . '/-/-';				
				else:						
					//Is second category
					if ($item->parent_cat_father_id == 0):
						$item_slug = $item->parent_cat_slug . '/' . $item->cat_slug . '/-';
					else:
						//Is third-tier category
						$item_slug = $item->ancestor_cat_slug . '/' . $item->parent_cat_slug . '/' . $item->cat_slug;
					endif;
				endif;
			
				//Manipulate the data into the format we need for google base
				if ($item->product_image != ''):
					$product_image = explode(';', $item->product_image);
					$image_link	= site_root('uploads/'.$product_image[0]);		
				else:
					$image_link = '';
				endif;

				// Add the the base rate and vat to the product price
				$product_price = $item->product_price;
				
				//Add the VAT and any base rate
				$product_price = base_rate($product_price * the_vat_rate());

				// Add the VAT but exclude the base_rate to the sale price
				$product_saleprice = $item->product_saleprice * the_vat_rate();
				
				//Convert the value to an array so we can count how 
				//many digits are after the decimal point
				$split = explode('.', $product_saleprice);
				$decimal_count = strlen($split[1]);
				
				//If the digits are more than 2, do some rounding down
				if ($decimal_count > 2) {
					// If decimals begin with 98n then FORCE the price to display as .99
					if ($split[1] >= 980 && $this->config->item('force_99p') == 'true') {
						$product_saleprice = preg_replace('@\.(9(8|9)[0-9])@', '.99', $product_saleprice);
					} else {
						// Don't do any rounding...
					}
				}
				
				$product_saleprice = number_format($product_saleprice, 2, '.', '');
				
				// Define the product brand
				$product_brand = ($item->product_brand != '') ? $item->product_brand : $this->config->item('store_name');
				$product_brand = truncate($product_brand, 70);
								
				// Define the correct product name
				if ($item->product_type == "variant") {
					$variant_name = unserialize_variant($item->variant_name);
					if ( trim($item->parent_name) != $variant_name ) {
						$product_name = sprintf("%s - %s", $item->parent_name, $variant_name);
					} else {
						$product_name = trim($item->parent_name);
					}
				} else {
					$product_name = trim($item->parent_name);
				}
				$product_name = truncate($product_name, 150);
				
				// Define the product ean/upc
				if ($item->product_ean != "") {
					$product_eanupc = $item->product_ean;
				} elseif ($item->product_upc != "") {
					$product_eanupc = $item->product_upc;
				} else {
					$product_eanupc = "";
				}
				$product_eanupc = truncate($product_eanupc, 50);
				
				// Set the product mpn if there is one
				if ($item->product_mpn != '') {
					$product_mpn = '<g:mpn>' . truncate($item->product_mpn, 70) . '</g:mpn>' . "\n";
				} else {
					$product_mpn = '';
				}
				
				// Define the availability message
				$product_availability = ($item->product_qty > 0) ? "in stock" : "out of stock";
				
				// Ensure there is a product description
				$product_description = ( trim($item->product_description) == '' ) ? $product_name : $this->google_feed->safe_xml($item->product_description);
				$product_description = truncate($product_description, 5000);
				
				// Product type (our category)
				$product_type = truncate(str_replace('&', 'and', $item->cat_name), 750);

				// Append the urls with tracking identifier for google shopping
				$utm_source = "?utm_source=google&utm_medium=shop&utm_campaign=feed";

				// Add to XML data
				$xml_file .= '<item>' . "\n";
				$xml_file .= '<title><![CDATA[' . $product_name . ']]></title>' . "\n";
				$xml_file .= '<link><![CDATA[' . $base_url . $item_slug . '/' . $item->product_slug . '/' . $item->product_id . $utm_source . ']]></link>' . "\n";
				$xml_file .= '<description><![CDATA[' . $product_description . ']]></description>' . "\n";
				$xml_file .= '<g:image_link>' . $image_link . '</g:image_link>' . "\n";
				$xml_file .= '<g:price>' . $product_price  . ' GBP</g:price>' . "\n";
				if ($item->product_saleprice > 0) {
				$xml_file .= '<g:sale_price>' . $product_saleprice .' GBP</g:sale_price>' . "\n";
				}
				$xml_file .= '<g:id>' . $item->variant_id  . '</g:id>' . "\n";
				$xml_file .= '<g:brand><![CDATA[' . $product_brand . ']]></g:brand>' . "\n";
				$xml_file .= '<g:product_type><![CDATA[' . $product_type . ']]></g:product_type>' . "\n";
				$xml_file .= '<g:quantity>' . $item->product_qty  . '</g:quantity>' . "\n";
				$xml_file .= '<g:gtin>' . $product_eanupc . '</g:gtin>' . "\n";
				$xml_file .= $product_mpn;
				$xml_file .= '<g:condition>' . $item->product_condition . '</g:condition>' . "\n";
				$xml_file .= '<g:availability>' . $product_availability . '</g:availability>' . "\n";
				$xml_file .= '<g:google_product_category></g:google_product_category>' . "\n";
				$xml_file .= "<g:delivery>\n";
				$xml_file .= "<g:country>GB</g:country>\n";
				$xml_file .= "<g:price>".$this->google_feed->getdeliverycost($item->price, $item->product_weight)."</g:price>\n";
				$xml_file .= "</g:delivery>\n";
				$xml_file .= '</item>' . "\n";
			
			endforeach;
		
		endif;

		//Close XML file and write to server
	    $xml_file .= "</channel>\n";	
	    $xml_file .= '</rss>';	

	    if ($download == true) {
		    write_file($_SERVER['DOCUMENT_ROOT'] . "/base/$save_as", $xml_file);
		    return "The XML file required for Google Shopping has been updated successfully.";
		} else {
			return $xml_file;
		}
	
	}
	
}