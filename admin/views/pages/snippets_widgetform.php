<div id="shopit-console-widget-form">
	
	<h1>Select Content</h1>

	<div style="padding-top: 20px !important; padding-bottom: 20px !important; border-bottom: 1px solid #ebebeb;">
		<strong>Select the type of content to display:</strong>
		<label for="shopit_console_widget_type_product"><input type="radio" name="shopit_console_widget_type" id="shopit_console_widget_type_product" value="product" checked="checked" class="valign" /> Product</label>
		<?php
		if (strlen($this->config->item('blog_url')) > 1) {
			$blog_label = 'wordpress';
			echo sprintf('<label for="shopit_console_widget_type_%s"><input type="radio" name="shopit_console_widget_type" id="shopit_console_widget_type_%s" value="%s" class="valign" /> %s</label>', $blog_label, $blog_label, $blog_label, ucfirst($blog_label));
		}
		?>
		<?php
		// The below is an example of how to add a content type option to this page e.g. 'blog'
		#<label for="shopit_console_widget_type_wordpress"><input type="radio" name="shopit_console_widget_type" id="shopit_console_widget_type_wordpress" value="wordpress" class="valign" /> Blog post</label>
		?>
	</div>

	<?php
	// This is the default form to select a product
	?>
	<form class="shopit-console-widget-type shopit_console_widget_type_product" method="post" action="<?=$form_action;?>">
	
		<h3>Select Product</h3>
		
		<div>
			<select name="cat_id" id="shopit-console-widget-form-catid" class="dropdown">
				<option value="-1">Choose category...</option>
				<?php foreach($categories as $category):?>
				<option value="<?=$category->cat_id;?>"><?=str_replace(' & ',' &amp; ',$category->cat_name);?></option>
				<?php endforeach;?>
			</select>
		</div>
			
		<?php
		// A select dropdown containing a list of products 
		// will be displayed in the <div> below with the 
		// name 'shopit_console_snippet_data'
		?>
		<div id="shopit-console-widget-form-catid-results"></div>

		<div>
			<input type="hidden" name="shopit_console_widget_type" value="product" />
			<input type="hidden" name="channel_id" value="<?=$this->input->get('channel_id');?>" />
			<input type="hidden" name="snippet_id" value="<?=$this->input->get('snippet_id');?>" />
			<input type="submit" name="submit" value="Save" disabled="disabled" />
		</div>

	</form>

	<?php
	// This is the wordpress post list which relies on the WP REST API 
	// plugin being installed on the Wordpress blog. API guide can be found
	// at http://wp-api.org/guides/getting-started.html
	if (strlen($this->config->item('blog_url')) > 1) {
	?>
	<form class="shopit-console-widget-type shopit_console_widget_type_wordpress" method="post" action="<?=$form_action;?>">
		
		<h3>Select Post</h3>
	
		<div>
			<select name="shopit_console_snippet_data" class="dropdown">
				<option value="-1">Choose post...</option>
	<?php
				// Get list of posts
				$blog_api_url = sprintf('%s/wp-json/posts/', $this->config->item('blog_url'));
				
				// Get the JSON data
				$curl = curl_init(); 
				curl_setopt ($curl, CURLOPT_URL, $blog_api_url); 
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); 
				$posts_json = curl_exec ($curl); 
				curl_close ($curl); 

				// JSON decode the results
				$posts = json_decode($posts_json);
				
				// Loop through each post
				foreach($posts as $post) {
					if (!isset($post->code)) {
						echo sprintf('<option value="%d">%s</option>', $post->ID, $post->title);
					}
				}
				?>
			</select>
		</div>

		<div>
			<input type="hidden" name="shopit_console_widget_type" value="wordpress" />
			<input type="hidden" name="snippet_id" value="<?=$this->input->get('snippet_id');?>" />
			<input type="submit" name="submit" value="Save" disabled="disabled" />
		</div>

	</form>
	<?php } ?>
	
	<?php
	// This is an example of a form for a new content type. The three important fields to note 
	// are 'shopit_console_snippet_data', snippet_id (passed via GET) and 'shopit_console_widget_type'.
	// By default the form will be hidden on page load. It is important to ensure the correct 
	// classes are applied to the <form> tag: 'shopit-console-widget-type 
	// shopit_console_widget_type_{content type as defined on the related radio button further up}'.
	#
	# <form class="shopit-console-widget-type shopit_console_widget_type_wordpress" method="post" action="<?=$form_action;? >">
	# 
	# 	<h3>Select Blog Post</h3>
	# 
	# 	<div>
	# 		Example: <input type="text" name="shopit_console_snippet_data" value="139" />
	# 	</div>
	# 
	# 	<div>
	#		<input type="hidden" name="snippet_id" value="< ?=$this->input->get('snippet_id');? >" />
	# 		<input type="hidden" name="shopit_console_widget_type" value="wordpress" />
	# 		<input type="submit" name="submit" value="Save" disabled="disabled" />
	# 	</div>
	# 
	# </form>
	?>
	
</div>

<script type="text/javascript">
$(document).ready(function() {
	
	// Hide all forms except the first
	$('#shopit-console-widget-form form').hide();
	$('#shopit-console-widget-form form').first().show();
	
	// Show the correct form
	$('input[name="shopit_console_widget_type"]').click(function(){
		var $content_type = $(this).val();
		var $form_to_show = $(this).attr('id');
		$('.shopit-console-widget-type').hide();
		$('.' + $form_to_show).show();
	});

	// For product form only - load the product results
	$('#shopit-console-widget-form-catid').change(function(){
		
		var $channel_id = $('input[name="channel_id"]').val();
		var $cat_id = $(this).val();
		
		if ($cat_id > 0) {

			$.ajax({
				type: 'POST',
				url: '<?=$ajax_post_url;?>',
				data: {
					cat_id: $cat_id, 
					channel_id: $channel_id
				},
				dataType: 'html',
				success: function(response) {
					$('#shopit-console-widget-form-catid-results').html("").append(response);
				}
			});
			
		} else {
			$('#shopit-console-widget-form-catid-results').html("");
		}
		
	});
	
	// Enable/disable the submit button on the currently visible form field
	$('body').on('change keyup', '[name="shopit_console_snippet_data"]:visible', function(){	
		if ($(this).val() > 0) {
			$('input[type="submit"]').prop('disabled', false);
		} else {
			$('input[type="submit"]').prop('disabled', true);
		}
	});
    
});
</script>
