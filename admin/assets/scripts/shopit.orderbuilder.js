$(document).ready(function() {

	//!Add item
	$('body').on('click', '#additemtoorder', function(e){
		e.preventDefault();
		var product_id 		= $('input[name="new_product_id"]').val();
		var cat_id 			= $('input[name="new_cat_id"]').val();
		var product_no 		= $('input[name="new_product_no"]').val();
		var product_name 	= $('input[name="new_product_name"]').val();
		var product_qty 	= $('input[name="new_product_qty"]').val();
		var product_price 	= $('input[name="new_product_price"]').val();
		var product_weight 	= $('input[name="new_product_weight"]').val();
		
		$('#tblOrderInventory tbody').append('<tr class="highlight"><td><input type="hidden" name="order_inventory_id[]" value="0" /><input type="hidden" name="product_id[]" value="' + product_id + '" /><input type="hidden" name="cat_id[]" value="' + cat_id + '" /><input name="product_no[]" value="' + product_no + '" class="textbox" size="15" /></td><td><input name="product_name[]" value="' + product_name + '" class="textbox" size="50" /></td><td><input name="product_qty[]" value="' + product_qty + '" class="textbox centered" size="3" /></td><td><input name="product_price[]" value="' + product_price + '" class="textbox" size="10" /><input type="hidden" name="product_weight[]" value="' + product_weight + '" /></td><td><a href="#" class="button removeitemfromorder">Remove item</a><input type="hidden" name="remove[]" value="no" /></td></tr>');
		$('input[name="new_product_id"]').val('');
		$('input[name="new_cat_id"]').val('');
		$('input[name="new_product_no"]').val('');
		$('input[name="new_product_name"]').val('');
		$('input[name="new_product_qty"]').val('').attr('data-product_id', '');
		$('input[name="new_product_price"]').val('');
		$('input[name="new_product_weight"]').val('');
	});
	
	//!Remove item
	$('body').on('click', '.removeitemfromorder', function(e){
		e.preventDefault();
		$(this).closest('tr').hide();
		$(this).closest('tr').find('input[name="remove[]"]').val('yes');
		$(this).closest('tr').find('input[name="product_price[]"]').val(0);
	});

	//!Copy billing address to delivery address form
	$('input#samefordelivery').click(function() {
		if ($('input#samefordelivery').is(':checked')){			
			$('select[name="delivery_title"]').val($('select[name="billing_title"]').val());
			$('input[name="delivery_firstname"]').val($('input[name="billing_firstname"]').val());
			$('input[name="delivery_surname"]').val($('input[name="billing_surname"]').val());
			$('input[name="delivery_company"]').val($('input[name="billing_company"]').val());
			$('input[name="delivery_address1"]').val($('input[name="billing_address1"]').val());
			$('input[name="delivery_address2"]').val($('input[name="billing_address2"]').val());
			$('input[name="delivery_city"]').val($('input[name="billing_city"]').val());
			$('input[name="delivery_postcode"]').val($('input[name="billing_postcode"]').val());
			$('select[name="delivery_country"]').val($('select[name="billing_country"]').val());
		}
	});

	//!Identify change to order details
	$('#content input').keyup(function(){
		$('#order_changes').val('true');
	});

	$('#content select').change(function(){
		$('#order_changes').val('true');
	});
	
	$('.customerLookup').fancybox({
		'autoSize': false,
		'width': 970,
		'height': 450,
		'padding': 10,
		'margin': 0,
		'centerOnScroll': true,
		'type': 'iframe'
	});

	//!Product lookup
	$('#shopit_content').on('click', '#productLookup', function(){
		var $lookup_url 	= $(this).data('url');
		var $lookup_val 	= $('input[name="new_product_name"]').val();
		var $lookup_channel = $('[name="site"]').val();
		$(this).attr('href', $lookup_url + '/' + $lookup_val + '/' + $lookup_channel);
	});
	
	$('#productLookup').fancybox({
		'autoSize': false,
		'width': 970,
		'height': 450,
		'padding': 10,
		'margin': 0,
		'centerOnScroll': true,
		'type': 'iframe'
	});
	
	//!Get Shipping options
	$('#getShippingOptions').bind('click',function(){
		var total_price = '';
		var total_weight = '';
		var delivery_country = $('[name="delivery_country"]').val();
		var cat_ids = '';

		var cat_ids = $('input[name="cat_id[]"]').map(function(){
			return $(this).val();
		}).get().join(',');
				
		$('input[name="product_qty[]"]').each(function(){
			var qty = $(this).val();
			var price = $(this).closest('tr').find('input[name="product_price[]"]').val();
			var weight = $(this).closest('tr').find('input[name="product_weight[]"]').val();
			total_price = (total_price*1) + (qty * price);
			total_weight = (total_weight*1) + (qty * weight);
		});

		if (total_price > 0 || total_weight > 0) {
			$('#getShippingOptions').attr( 'href','/admin/index.php/shipping/lookup/' + encodeURI(delivery_country) + '/' + total_price.toFixed(2) + '/' + total_weight.toFixed(3) + '/' + cat_ids );
		} else {
			$('#getShippingOptions').attr('href','/admin/index.php/shipping/lookup/' + encodeURI(delivery_country));
		}
		
		$(this).fancybox({
			'autoSize': false,
			'width': 970,
			'height': 450,
			'padding': 10,
			'margin': 0,
			'centerOnScroll': true,
			'type': 'iframe'
		});

	});
	
	$('#getShippingOptions').trigger('click');
	
	//!Calculate total
	$('#getTotal').click(function(e){
		e.preventDefault();
		
		var link = $(this).attr('href');
		var strTotalPrice = 0;
		var strShipping = $('input[name="order_shipping"]').val();
		var strVat = $('input[name="order_vat"]').val();
		var strDiscount = $('input[name="order_discount"]').val();

		$('input[name="product_qty[]"]').each(function(){
			var qty = $(this).val();
			var price = $(this).closest('tr').find('input[name="product_price[]"]').val();
			strTotalPrice = (strTotalPrice*1) + (qty * price);
		});

		if ($('input[name="auto_vat"]').is(':checked')) {
			strVat = 'auto';
		} else {
			strVat = $('input[name="order_vat"]').val();
		}

		$.post(link, {order_total: strTotalPrice, order_shipping: strShipping, order_vat: strVat, order_discount: strDiscount}, 
			function(data){
				
				$('#orderTotal').html(data.total);
				$('input[name="order_vat"]').val(data.order_vat);
			
			}, 'json'
		);
	});

});