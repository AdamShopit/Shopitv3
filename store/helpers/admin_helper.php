<?php
#------------------------------------------------------
# Admin Helper
# - This helper contains functions to display
#   admin related tools on the store front.
#------------------------------------------------------
function shopit_admin_bar($path) {
	
	global $data;
	
	$shopit =& get_instance();
	
	$shopit_user = is_admin();
	
	if ($shopit_user !== FALSE || ($shopit_user->uid > 0 and strlen($shopit_user->email) > 0 and strlen($shopit_user->firstname) > 0)) {
	
		$html = '';
		
		$page_type 	 = $data['page_type'];
		
		// Uncomment the following line for debugging
		#var_dump($shopit_user);
		
		echo sprintf('<link href="%s?%s" rel="stylesheet" type="text/css" media="screen" title="default" />', $path.'core/styles/shopit-console.css', date('Ymd'));
		
		$html = '<script type="text/javascript">';
		$html .= "$(document).ready(function() {\n";
		
		$html .="var shopit_html = '";
		$html .= '<div id="shopit-console">';
		
		// Display logged in user
		$html .= sprintf('<span id="shopit-console-user-aloha">%s</a><strong class="valign">%s</strong></span>', shopit_gravatar($shopit_user->email, 22), $shopit_user->firstname);

		// Admin flash notice
		if (isset($_GET['shopit-notice'])) {
			$html .= sprintf('<span id="shopit-console-notice">%s</span>', $shopit->input->get('shopit-notice'));
		}
		
		// Display list of available user options
		$html .= '<ul id="shopit-console-user-options">';
		
		// Check cookies to see if admin user has turned the snippet highlights off
		$highlight_snippets = get_cookie('shopit_mgnt_snippets');
		$highlight_snippets_checked = (!empty($highlight_snippets)) ? 'checked="checked"' : '';
		$highlight_snippets_class = (empty($highlight_snippets)) ? 'shopit-toggle-snippets-ticked' : '';
		
		// Show option appropriate to the currently viewed page
		switch ($page_type) {
			
			// Product page
			case 'product':
				$html .= '<li>Edit <i class="shopit-console-arrow">&#x25BC;</i>';
				$html .= '<ul>';
				
				$this_page_label = 'Edit Product';
				$this_page_link  = sprintf('%sadmin/index.php/inventory/edit/%s%s', site_url(), $data['product_id'], redirect_create()); 
				$html .= sprintf('<li><a href="%s">%s</a></li>', $this_page_link, $this_page_label);
				
				if ($data['product_type'] == 'variation') {
					
					if (count($data['variations']) > 0) {
						$html .= '<li><label>Edit Variant</label></li>';
						
						foreach($data['variations'] as $variant) {
							$this_page_label = $variant['variant_product_name'];
							$this_page_link  = sprintf('%sadmin/index.php/inventory/editvariation/%s%s', site_url(), $variant['variant_id'], redirect_create()); 
							$html .= sprintf('<li><a href="%s">%s</a></li>', $this_page_link, $this_page_label);
						}
					
						$html .= '<li class="shopit-console-menu-separator"><label></label></li>';
					}
					
				}
				
				$this_page_label = 'Photo Gallery';
				$this_page_link  = sprintf('%sadmin/index.php/inventory/gallery/%s%s', site_url(), $data['product_id'], redirect_create()); 
				$html .= sprintf('<li><a href="%s">%s</a></li>', $this_page_link, $this_page_label);

				$this_page_label = 'Manage Cross-Sells';
				$this_page_link  = sprintf('%sadmin/index.php/inventory/related/%s%s', site_url(), $data['product_id'], redirect_create()); 
				$html .= sprintf('<li><a href="%s">%s</a></li>', $this_page_link, $this_page_label);

				$this_page_label = 'Product Report';
				$this_page_link  = sprintf('%sadmin/index.php/inventory/report/%s%s', site_url(), $data['product_id'], redirect_create()); 
				$html .= sprintf('<li><a href="%s">%s</a></li>', $this_page_link, $this_page_label);
				
				$html .= '</ul>';
				$html .= '</li>';
				break;
			
			// Category page
			case ($data['cat_id'] > 0):
				$html .= '<li>Edit <i class="shopit-console-arrow">&#x25BC;</i>';
				$html .= '<ul>';
				
				$this_page_label = 'Edit Category';
				$this_page_link	 = sprintf('%sadmin/index.php/category/edit/%s%s', site_url(), $data['cat_id'], redirect_create());
				$html .= sprintf('<li><a href="%s">%s</a></li>', $this_page_link, $this_page_label);
				
				if (library_exists('filters')) {
					$this_page_label = 'Manage Filters';
					$this_page_link	 = sprintf('%sadmin/index.php/filters/manage/%s%s', site_url(), $data['cat_id'], redirect_create());
					$html .= sprintf('<li><a href="%s">%s</a></li>', $this_page_link, $this_page_label);
				}
				
				$html .= '</ul>';
				$html .= '</li>';
				break;
				
			// Basket or checkout page
			case 'basket';
			case 'checkout':
				$this_page_label = 'Manage Shipping Rules';
				$this_page_link  = sprintf('%sadmin/index.php/shipping', site_url());
				$html .= sprintf('<li><a href="%s">%s</a></li>', $this_page_link, $this_page_label);
				break;
			
			// Page
			case 'home';
			case 'page':
				$this_page_label = 'Edit Page';
				$this_page_link	 = sprintf('%sadmin/index.php/pages/edit/%s%s', site_url(), $data['page_id'], redirect_create());
				$html .= sprintf('<li><a href="%s">%s</a></li>', $this_page_link, $this_page_label);
				break;
				
			// Collection page
			case 'collection':
				$html .= '<li>Edit <i class="shopit-console-arrow">&#x25BC;</i>';
				$html .= '<ul>';

				$this_page_label = 'Edit Collection';
				$this_page_link	 = sprintf('%sadmin/index.php/collections/edit/%s%s', site_url(), $data['collection_id'], redirect_create());
				$html .= sprintf('<li><a href="%s">%s</a></li>', $this_page_link, $this_page_label);
				
				$this_page_label = 'Manage Products';
				$this_page_link	 = sprintf('%sadmin/index.php/collections/manage/%s%s', site_url(), $data['collection_id'], redirect_create());
				$html .= sprintf('<li><a href="%s">%s</a></li>', $this_page_link, $this_page_label);

				$html .= '</ul>';
				$html .= '</li>';
				break;
			
			// Default i.e. unrecognised page
			default:
				$this_page_label = '';
				$this_page_link = '';
				break;
			
		}
		
		// 'Go to...' menu
		$html .= '<li>Go to...<i class="shopit-console-arrow">&#x25BC;</i><ul>';
		$html .= sprintf('<li><a href="%s">%s</a></li>', site_url('admin/index.php/dashboard'), 'Dashboard');
		$html .= sprintf('<li><a href="%s">%s</a></li>', site_url('admin/index.php/orders'), 'Orders');
		$html .= sprintf('<li><a href="%s">%s</a></li>', site_url('admin/index.php/inventory'), 'Inventory');
		$html .= sprintf('<li><a href="%s">%s</a></li>', site_url('admin/index.php/collections'), 'Collections');
		$html .= sprintf('<li><a href="%s">%s</a></li>', site_url('admin/index.php/category'), 'Categories');
		$html .= sprintf('<li><a href="%s">%s</a></li>', site_url('admin/index.php/pages'), 'Pages');
		$html .= sprintf('<li><a href="%s">%s</a></li>', site_url('admin/index.php/pages/snippets'), 'Snippets');
		$html .= '</ul></li>';

		// Checkbox to toggle snippet highlights on or off
		$html .= '<li id="shopit-toggle-snippets"><input type="checkbox" name="shopit-hide-snippets" id="shopit-hide-snippets" value="yes" '.$highlight_snippets_checked.' /> <label for="shopit-hide-snippets" class="'.$highlight_snippets_class.'">Show Snippets</label></li>';
		
		// Logout option
		$html .= sprintf('<li><a href="%s">%s</a></li>', site_url('admin/index.php/logout'), 'Logout');
		$html .= '</ul>';
		
		// Close the shopit-console div
		$html .= '</div>';
		$html .= "';\n\n";
		
		// Prepend our menu to the <body> tag
		$html .= "$('body').prepend(shopit_html);\n";

		// Make any menus within the admin bar clickable dropdowns
		$html .= "
			$('#shopit-console-user-options > li').click(function(){
				if ($('ul', this).is(':visible')) {
					$('ul', this).hide();
				} else {
					$('#shopit-console-user-options ul').hide();
					$(this).closest('li').addClass('selected');
					$('ul', this).show();
				}
			});\n";

		// Popup modal & ajax for the snippet widgets
		$html .= "
			$('a.shopit-console-snippet-widget').fancybox({
				autoSize: 	false,
				width: 		600,
				height: 	400,
				type: 		'ajax',
				padding: 	3
			});\n";
			
		// Hide snippets - sets cookie to remember users preference on this computer
		$html .= "
			$('body').on('click', '#shopit-hide-snippets', function(){
				if ($(this).is(':checked')) {
					var expiry_date = '".date('D, j M Y H:i:s T', time() + (10 * 365 * 24 * 60 * 60))."';
					document.cookie='shopit_mgnt_snippets=hide;expires='+expiry_date+';path=/';
					$('.shopit-console-snippet-overlay').addClass('shopit-snippet-hide');
					$('#shopit-toggle-snippets label').removeClass('shopit-toggle-snippets-ticked');
				} else {
					var expiry_date = '".date('D, j M Y H:i:s T', time() + (10 * 365 * 24 * 60 * 60))."';
					document.cookie='shopit_mgnt_snippets=;expires='+expiry_date+';path=/';
					$('.shopit-console-snippet-overlay').removeClass('shopit-snippet-hide');
					$('#shopit-toggle-snippets label').addClass('shopit-toggle-snippets-ticked');
				}
			});\n";

		// Close of jquery 'ready' event
		$html .= "});\n";

		// Start the jquery 'after load' event (after everything has loaded)
		$html .= "$(window).bind('load', function(){\n";
			
		// Check if a 'slidesjs' slider sits on the current page and 
		// create a div to contain our snippet regions
		$html .= "
		$('.slidesjs-container').each(function(){
			if ( $(this).has('.shopit-console-snippet').length > 0 ) {
				var my_parent_id = $(this).parent('div').attr('id');
				var padding		 = 6;
				var my_width	 = $('#' + my_parent_id).width() - padding;
				var my_height 	 = $('#' + my_parent_id).height() - padding;
				var my_position  = $('#' + my_parent_id).offset();
				var slidesjs_div = '<div id=\"shopit-console-snippet-' + my_parent_id + '\" class=\"shopit-console-snippet-overlay shopit-console-snippet-slidesjs\" style=\"width:' + my_width + 'px; height:' + my_height + 'px; left:' + my_position.left + 'px; top:' + my_position.top + 'px;\"></div>';
				$('body').append(slidesjs_div);
			}
		});\n";
			
		// Create boundaries for each snippets - we need to get the size and position
		// and create a new set of divs which will then be appended to the body.
		// +
		// Move the snippets to their correct position if the window is resized.
		$html .= "
			$('.shopit-console-snippet').each(function(){
				
				var padding		= 6;
				var link 		= $(this).data('href');
				var label 		= $(this).data('label');
				var my_width	= $(this).width() - padding;
				var my_height 	= $(this).height() - padding;
				var my_position = $(this).offset();
				var my_hide		= $(this).data('hide');
				var my_id		= $(this).data('id');
				
				if ( $(this).hasClass('shopit-console-snippet-widget') ) {
					var my_widget_class = ' shopit-console-snippet-widget';
				} else {
					var my_widget_class = '';
				}

				if ( $(this).hasClass('slidesjs-slide') ) {
					var my_slidesjs_class 	= 'slidesjs-slide';
					var is_slidesjs 		= true;
					var my_slidesjs_id 		= $(this).closest('.slidesjs-container').parent('div').attr('id');
				} else {
					var my_slidesjs_class 	= '';
					var is_slidesjs 		= false;
					var my_slidesjs_id 		= '';
				}
				
				if (my_hide == true) {
					var my_hide_class = ' shopit-snippet-hide';
				} else {
					var my_hide_class = '';
				}
				
				if (is_slidesjs == true) {
					var div = '<div id=\"' + my_id + '\" class=\"' + my_slidesjs_class + '\"><a class=\"shopit-console-snippet-overlay-label' + my_widget_class + '\" href=\"' + link + '\">' + label + '</a></div>';
					$('#shopit-console-snippet-' + my_slidesjs_id).append(div);
					if (my_hide == true) {
						$('#shopit-console-snippet-' + my_slidesjs_id).addClass(my_hide_class);
					}
				} else {
					var div = '<div id=\"' + my_id + '\" class=\"shopit-console-snippet-overlay' + my_hide_class + my_slidesjs_class + '\" style=\"width:' + my_width + 'px; height:' + my_height + 'px; left:' + my_position.left + 'px; top:' + my_position.top + 'px;\"><a class=\"shopit-console-snippet-overlay-label' + my_widget_class + '\" href=\"' + link + '\">' + label + '</a></div>';
					$('body').append(div);
				}
				
			});
			
			$(window).bind('resize scroll', function(){
				refresh_snippets();
			});
				
			function refresh_snippets() {
				$('.shopit-console-snippet').each(function(){
					
					var padding	= 6;

					if ( $(this).hasClass('slidesjs-slide') ) {
						var my_slidesjs_class 	= 'slidesjs-slide';
						var is_slidesjs 		= true;
						var my_slidesjs_id 		= $(this).closest('.slidesjs-container').parent('div').attr('id');
						var my_width	 		= $('#' + my_slidesjs_id).width() - padding;
						var my_height 	 		= $('#' + my_slidesjs_id).height() - padding;
						var my_position  		= $('#' + my_slidesjs_id).offset();
						var my_id				= 'shopit-console-snippet-' + my_slidesjs_id;
					} else {
						var my_slidesjs_class 	= '';
						var is_slidesjs 		= false;
						var my_slidesjs_id 		= '';
						var my_width	= $(this).width() - padding;
						var my_height 	= $(this).height() - padding;
						var my_position = $(this).offset();
						var my_id		= $(this).data('id');
					}
				
					$('#' + my_id).css({
						'top': my_position.top + 'px',
						'left': my_position.left + 'px',
						'width': my_width + 'px',
						'height': my_height + 'px'
					});
				
				});
			}
			\n";

		// Close of jquery
		$html .= "});\n";
		$html .= "</script>";
		
		echo $html;
	
	}

}

#------------------------------------------------------
# Snippet Widget
# - Displays editable boundary with passed content
# @param $snippet (array) - Snippet var e.g. $sn_my_snippet
# @param $function_name (string) - Name of the custom function to
#        pass into this function.
# @return String containing the passed function content
# 		  and the editable boundary.
#------------------------------------------------------
function snippet_widget($snippet=array(), $function_name=null) {
	
	$shopit =& get_instance();
	
	$snippet_content = "";
	
	if (!empty($snippet) and $snippet['widget'] == 1) {

		// Start the output buffer
		ob_start();
	
		if (is_admin() !== FALSE) {

			// Check cookies to see if admin user has turned the snippet highlights off
			$highlight_snippets = get_cookie('shopit_mgnt_snippets');
			$highlight_snippets_class = (!empty($highlight_snippets)) ? 'true' : 'false';

			$snippet_content  = sprintf('<div class="shopit-console-snippet shopit-console-snippet-widget" data-href="%sadmin/index.php/pages/widgetform?channel_id=%d&snippet_id=%d%s" data-label="edit %s: %s" data-hide="%s" data-id="shopit-console-snippet-%d">', site_url(), $shopit->config->item('channel_id'), $snippet['id'], str_replace('?', '&', redirect_create()), 'widget', strtolower($snippet['title']), $highlight_snippets_class, $snippet['id']);
		}
		
		// Call our function if passed
		if (function_exists($function_name) and !empty($function_name)) {
			echo call_user_func($function_name, $snippet['data']);
			$snippet_content .= ob_get_contents();
		}

		if (is_admin() !== FALSE) {		
			$snippet_content .= '</div>';
		}
		
		// Clean the output buffer
		ob_end_clean();
	
	}
	
	// Output the content
	echo $snippet_content;
	
}

#------------------------------------------------------
# Gravatar
# - Used for store front floating bar
# @param $email - The email address of user
# @param $size = Default size of gravatar in pixels
# @ return String containing the complete image tag
#------------------------------------------------------
function shopit_gravatar($email=null, $size=22, $attr=array()) {

	$html = "";
	$dropbox_img = 'https://dl.dropboxusercontent.com/s/jhwutdy4hkj33ox/gravatar.png';
	
	if (isset($email)) {
	
		// Set the default image when no gravatar is available
		// https://www.dropbox.com/s/jhwutdy4hkj33ox/gravatar.png
		$default = urlencode("$dropbox_img?v=".time());
		
		// Gravatar URL without the trailing slash
		$grav_url = 'http://www.gravatar.com/avatar';
		
		// Do the necessary to the email address
		$email = md5( strtolower(trim($email)) );
		
		// Create the full url
		$src = "$grav_url/$email?d=$default&s=$size";
		
	
	} else {
		$src = $dropbox_img;
	}

	// Create the img tag
	$html = '<img src="'.$src.'" alt="" width="'.$size.'" height="'.$size.'" border="0" class="gravatar valign" />';
	
	return $html;
	
}

#------------------------------------------------------
# Redirect URL preservation
# - Capture the current url so we can return to this 
# 	page after an add/edit product page is accessed
#------------------------------------------------------
function redirect_create() {
	$current_url = base64_encode(current_url());
	return "?R=$current_url";
}

function redirect_get() {
	$shopit =& get_instance();
	
	// Preserve the original url query string - shouldn't need to call this var anywhere
	$redirect_url_encoded = $shopit->input->get('R');
	
	// Recreate the full query string to preserve the url where required
	$redirect_url_query_string = "?R=$redirect_url_encoded";
	
	// Decode the query string so we can use it as a link back
	$redirect_url = base64_decode($redirect_url_encoded);
	
	// Create the variables to use in the controller
	$redirect = (object) array(
		'query_string' => $redirect_url_query_string,
		'link' => $redirect_url
	);
	
	return $redirect;
}

function redirect_create_manual($segments) {
	$current_url = base64_encode(site_url($segments));
	return "?R=$current_url";
}
