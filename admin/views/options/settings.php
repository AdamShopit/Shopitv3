<div id="content">

	<div class="table">
	
		<h2><?=$form_title;?></h2>

		<div id="section-company" class="section">
	
			<div class="table-row">
				<h3>Company Details</h3>
			</div>
		
			<div class="table-row">
				<label>Store name: </label> 
				<input name="store_name" value="<?=set_value('store_name',$store_name);?>" class="textbox" size="75" maxlength="50" <?=tooltip("Enter the name of the store. This is displayed in the browser's title bar.");?> />
			</div>

			<div class="table-row">
				<label>Store email: </label> 
				<input name="store_email" value="<?=set_value('store_email',$store_email);?>" class="textbox" size="75" maxlength="50" <?=tooltip("This email is used to receive new order alerts.");?> />
			</div>	

			<div class="table-row">
				<label>Company name: </label> 
				<input name="company_name" value="<?=set_value('company_name',$company_name);?>" class="textbox" size="75" />
			</div>	
		
			<div class="table-row">
				<label>Company address: </label> 
				<textarea name="company_address" class="textbox" rows="5"><?=set_value('company_address',$company_address);?></textarea>
			</div>	
		
			<div class="table-row">
				<label>Company telephone: </label> 
				<input name="company_tel" value="<?=set_value('company_tel',$company_tel);?>" class="textbox" size="35" maxlength="35" />
			</div>	
		
			<div class="table-row">
				<label>Company fax: </label> 
				<input name="company_fax" value="<?=set_value('company_fax',$company_fax);?>" class="textbox" size="35" maxlength="35" />
			</div>	
			
			<div class="table-row">
				<label>Company email: </label> 
				<input name="company_email" value="<?=set_value('company_email',$company_email);?>" class="textbox" size="75" maxlength="50" />
			</div>	
		
			<div class="table-row">
				<label>Company registration: </label> 
				<textarea name="company_reg" class="textbox" rows="5"><?=set_value('company_reg',$company_reg);?></textarea>
			</div>	

		</div>

		<div id="section-storedisplays" class="section section-closed">

			<div class="table-row">
				<h3>Store Options</h3>
			</div>

			<div class="table-row">
				<label>Available product type: </label> 
				<?=form_dropdown('product_type',array('all' => 'Single &amp; variations', 'single' => 'Single items only', 'variations' => 'Variations only'), $product_type, 'class="dropdown"');?>
			</div>

			<div class="table-row">
				<label>Currency: </label> 
				<?php
					$currency = str_replace('&','',$currency);
					$currency = str_replace(';','',$currency);
				?>
				<?=form_dropdown('currency',array('pound' => 'GBP (&pound;)', 'euro' => 'EURO (&euro;)'),$currency,'class="dropdown"');?>
			</div>

			<div class="table-row">
				<label>Enable variation attributes:</label>
				<?=form_dropdown('variation_attributes', array('false' => 'Off', 'true' => 'On'), $variation_attributes, 'class="dropdown"');?>
			</div>

			<div class="table-row">
				<h3>Products</h3>
			</div>
		
			<div class="table-row">
				<label>Latest products to show: </label> 
				<input name="latest_products" value="<?=set_value('latest_products',$latest_products);?>" class="textbox" size="75" maxlength="4" <?=tooltip("Number of products to display on the homepage.");?> />
			</div>	

			<div class="table-row">
				<label>Products per page: </label> 
				<input name="products_per_page" value="<?=set_value('products_per_page',$products_per_page);?>" class="textbox" size="75" maxlength="4" <?=tooltip("Number of products to display per page.");?> />
			</div>	

			<div class="table-row">
				<h3>Images</h3>
			</div>

			<div class="table-row">
				<label>Path to uploads: </label> 
				<input name="path_to_uploads" value="<?=set_value('path_to_uploads',$path_to_uploads);?>" class="textbox" size="75" maxlength="70" <?=tooltip("You won't usually have to change this but ensure there are backslashes before and after the path. Default is '/uploads/'.");?> />
			</div>	

			<div class="table-row">
				<label>Thumbnail width (px): </label> 
				<input name="thumbnail_width" value="<?=set_value('thumbnail_width',$thumbnail_width);?>" class="textbox" size="75" maxlength="4" />
			</div>	
		
			<div class="table-row">
				<label>Thumbnail height (px): </label> 
				<input name="thumbnail_height" value="<?=set_value('thumbnail_height',$thumbnail_height);?>" class="textbox" size="75" maxlength="4"/>
			</div>	
		
			<div class="table-row">
				<label>Image width (px): </label> 
				<input name="image_width" value="<?=set_value('image_width',$image_width);?>" class="textbox" size="75" maxlength="4" />
			</div>	
		
			<div class="table-row">
				<label>Image height (px): </label> 
				<input name="image_height" value="<?=set_value('image_height',$image_height);?>" class="textbox" size="75" maxlength="4" />
			</div>	

			<div class="table-row">
				<label>Gallery Thumbnail Size (px): </label> 
				<input name="gallery_thumb" value="<?=set_value('gallery_thumb', $gallery_thumb);?>" class="textbox" size="75" maxlength="4" />
			</div>	
		
			<div class="table-row">
				<label>Image Jpg quality (0-100): </label> 
				<input name="image_quality" value="<?=set_value('image_quality', $image_quality);?>" class="textbox" size="75" maxlength="3" />
			</div>	
		
			<div class="table-row">
				<label>Max image upload width (px): </label> 
				<input name="max_image_width" value="<?=set_value('max_image_width',$max_image_width);?>" class="textbox" size="75" maxlength="4" />
			</div>	
		
			<div class="table-row">
				<label>Image zoom width (px): </label> 
				<input name="image_zoom" value="<?=set_value('image_zoom',$image_zoom);?>" class="textbox" size="75" maxlength="4" />
			</div>	
		
			<div class="table-row">
				<h3>Other</h3>
			</div>

			<div class="table-row">
				<label>Web page caching: <sup class="redtext" style="font-size: 9px;">BETA</sup></label> 
				<?=form_dropdown('caching', array('false' => 'Off', 'true' => 'On'), $caching, 'class="dropdown"');?>
			</div>
			
			<div class="table-row">
				<label>&nbsp;</label>
				<p class="redtext">File permissions on <strong>/cache</strong> folder must be writable.</p>
			</div>

		</div>

		<div id="section-tax" class="section section-closed">

			<div class="table-row">
				<h3>Orders</h3>
			</div>

			<div class="table-row">
				<label>Next order number:</label> 
				<input name="order_no" value="<?=set_value('order_no',$order_no);?>" class="textbox" size="75" maxlength="15" />
			</div>	
	
			<div class="table-row">
				<h3>Taxes &amp; Stock</h3>
			</div>
		
			<div class="table-row">
				<label>Default VAT rate:</label> 
				<input name="vat_rate" value="<?=set_value('vat_rate',$vat_rate);?>" class="textbox" size="75" maxlength="5" <?=tooltip("This must be in <strong>decimal</strong> format i.e 0.175 = 17.5%.");?> />
			</div>	
					
			<div class="table-row">
				<label>Out of stock purchases: </label> 
				<?=form_dropdown('outofstock_purchases',array('true' => 'Yes', 'false' => 'No'),$outofstock_purchases,'class="dropdown"');?>
			</div>	

			<div class="table-row">
				<label>Stock purchase limit: </label> 
				<input name="stock_purchaselimit" value="<?=set_value('stock_purchaselimit',$stock_purchaselimit);?>" class="textbox" size="75" maxlength="4" <?=tooltip("Enables customers to purchase items even if there isn't enough in stock. If the above option is set to No, then the customer will be allowed to purchase a maximum number of items based on the value you enter here. We recommend not to allow higher than 20.");?> />
			</div>	
		
			<div class="table-row">
				<label>Show stock levels: </label> 
				<?=form_dropdown('stock_showamount',array('true' => 'Yes', 'false' => 'No'),$stock_showamount,'class="dropdown"');?>
			</div>	

			<div class="table-row">
				<label>Base rate: </label> 
				<input name="base_rate" value="<?=set_value('base_rate',$base_rate);?>" class="textbox" size="75" value="5" <?=tooltip("This rate is added to all product prices (not sale prices). It can be a percentage (e.g. 20%) or a price (e.g. 2.99).");?> />
			</div>

			<div class="table-row">
				<label>Force 99p pricing: </label> 
				<?=form_dropdown('force_99p', array('false' => 'No', 'true' => 'Yes'), $force_99p, 'class="dropdown"');?>
			</div>	

		</div>
	
		<div id="section-payment" class="section section-closed">

			<div class="table-row">
				<h3>Shipping</h3>
			</div>
		
			<div class="table-row">
				<label>Default shipping rate: </label> 
				<input name="default_shipping_cost" value="<?=set_value('default_shipping_cost',$default_shipping_cost);?>" class="textbox" size="75" maxlength="5" <?=tooltip("This is the default shipping rate if no rules are setup.");?> />
			</div>	
		
			<div class="table-row">
				<label>Default shipping name: </label> 
				<input name="default_shipping_name" value="<?=set_value('default_shipping_name',$default_shipping_name);?>" class="textbox" size="75" />
			</div>	

			<div class="table-row">
				<h3>Payment Gateways</h3>
			</div>

			<div class="table-row formnote">
				<p class="redtext">The following payment gateway settings MUST be configured in the following file before being made active: <br/>
				<strong>/application/store/config/payment_settings.php</strong></p>
			</div>
		
		
			<div class="table-row">
				<label>PayPal active: </label> 
				<?=form_dropdown('payment_paypal',array('true' => 'Yes', 'false' => 'No'),$payment_paypal,'class="dropdown"');?>
			</div>
					
			<div class="table-row">
				<label>SagePay active: </label> 
				<?=form_dropdown('payment_sagepay',array('true' => 'Yes', 'false' => 'No'),$payment_sagepay,'class="dropdown"');?>
			</div>	
			
			<div class="table-row">
				<label>CardSave active: </label> 
				<?=form_dropdown('payment_cardsave',array('true' => 'Yes', 'false' => 'No'),$payment_cardsave,'class="dropdown"');?>
			</div>	
		
			<div class="table-row">
				<label>WorldPay active: </label> 
				<?=form_dropdown('payment_worldpay',array('true' => 'Yes', 'false' => 'No'),$payment_worldpay,'class="dropdown"');?>
			</div>	

			<div class="table-row">
				<label>Barclaycard active: </label> 
				<?=form_dropdown('payment_barclaycard',array('true' => 'Yes', 'false' => 'No'), $payment_barclaycard,'class="dropdown"');?>
			</div>	

		</div>
	
		<div id="section-campaignmonitor" class="section section-closed">

			<div class="table-row">
				<h3>CreateSend</h3>
			</div>
		
			<div class="table-row">
				<label>Client ID: </label> 
				<input name="campaignmonitor_clientid" value="<?=set_value('campaignmonitor_clientid',$campaignmonitor_clientid);?>" class="textbox" size="75" maxlength="75" <?=tooltip("You can find the API Client ID by clicking the 'Client Settings' tab for any of your clients in Campaign Monitor.");?> />
			</div>	
		
			<div class="table-row">
				<label>Subscriber List ID: </label> 
				<input name="campaignmonitor_listid" value="<?=set_value('campaignmonitor_listid',$campaignmonitor_listid);?>" class="textbox" size="75" maxlength="75" <?=tooltip("You can find the API Subscriber List ID by clicking any subscriber list under the 'Manage Subscribers' tab, and then by clicking the 'edit list name/type' link in Campaign Monitor (Hint: It's the link next to the list name at the top).");?> />
			</div>	

			<div class="table-row">
				<h3>Mailchimp</h3>
			</div>

			<div class="table-row">
				<label>API Key: </label> 
				<input name="mailchimp_apikey" value="<?=set_value('mailchimp_apikey',$mailchimp_apikey);?>" class="textbox" size="75" maxlength="75" <?=tooltip('This requires that you have an account with MailChimp, and are able to obtain your API Key: http://kb.mailchimp.com/article/where-can-i-find-my-api-key/.');?> />
			</div>
			
			<div class="table-row">
				<label>&nbsp;</label>
				<em><a href="http://kb.mailchimp.com/article/where-can-i-find-my-api-key/" target="_blank">http://kb.mailchimp.com/article/where-can-i-find-my-api-key/</a></em>
			</div>

			<div class="table-row">
				<label>Subscriber List ID: </label> 
				<input name="mailchimp_listid" value="<?=set_value('mailchimp_listid',$mailchimp_listid);?>" class="textbox" size="75" maxlength="75" <?=tooltip("This is the ID for the mailing list which can be found on the list's setting page in Mailchimp.");?> />
			</div>
		
			<div class="table-row">
				<h3>Google Analytics</h3>
			</div>

			<div class="table-row">
				<label>Google Analytics UA: </label> 
				<input name="google_ua" value="<?=set_value('google_ua',$google_ua);?>" class="textbox" size="75" maxlength="75" />
			</div>

			<div class="table-row">
				<label>Google Analytics Code: </label> 
				<textarea name="google_ua_code" class="textbox <?=codebox();?>" rows="5"><?=set_value('google_ua_code',$google_ua_code);?></textarea>
			</div>	

			<div class="table-row">
				<h3>WordPress/Blog URL</h3>
			</div>

			<div class="table-row">
				<label>URL:</label>
				<input name="blog_url" value="<?=set_value('blog_url', $blog_url);?>" class="textbox" size="75" maxlength="128" />
			</div>
			
			<div class="table-row">
				<label>&nbsp;</label>
				<em>Enter full url e.g. <?=site_root('blog');?></em>
			</div>	

			<!--
			<div class="table-row">
				<h3>Arrivals (Task List)</h3>
			</div>

			<div class="table-row">
				<label>URL:</label>
				<input name="arrivals_url" value="<?=set_value('arrivals_url', $arrivals_url);?>" class="textbox" size="75" maxlength="128" />
			</div>
			
			<div class="table-row">
				<label>&nbsp;</label>
				<em>Enter full url e.g. http://dubbedcreative.com/projects/client/client-name</em>
			</div>	
			-->

		</div>
		
		<div id="section-developer" class="section section-closed">

			<div class="table-row">
				<h3>Profiler</h3>
				<p>The profiler enables benchmarking and profiling on the store front during development &mdash; <span class="redtext">it should <strong><u>not</u></strong> be enabled on production websites</span>.</p>
			</div>
			
			<div class="table-row">
				<label>Enable Profiler: </label> 
				<?=form_dropdown('enable_profiler', array('false' => 'No', 'true' => 'Yes'), $enable_profiler, 'class="dropdown"');?>
			</div>	
			
		</div>
		
	</div>

</div>

<div id="sidebar">
	<h3>Edit store settings</h3>
	<p><strong class="redtext">All fields must be complete.</strong> Choose the settings to edit:</p>
	<ul id="sections">
		<li><a href="#section-company" class="button">Company &amp; store details</a></li>
		<li><a href="#section-storedisplays" class="button button-off">Store settings</a></li>
		<li><a href="#section-tax" class="button button-off">Taxes &amp; stock</a></li>
		<li><a href="#section-payment" class="button button-off">Shipping &amp; payment</a></li>
		<li><a href="#section-campaignmonitor" class="button button-off">Integrations</a></li>
		<li><a href="#section-developer" class="button button-off">Developer Tools</a></li>
	</ul>
</div>