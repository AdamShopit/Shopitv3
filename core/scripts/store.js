//JQUERY DOM ready
$(document).ready(function() {

	/*------------------------------------------------------
	# Copy billing address to delivery address form
	------------------------------------------------------*/
	$('input#SameforDelivery').click(function() {

		if ($('input#SameforDelivery').is(':checked')){
			
			$('#DeliveryTitle').val($('#BillingTitle').val());
			$('#DeliveryFirstname').val($('#BillingFirstname').val());
			$('#DeliverySurname').val($('#BillingSurname').val());
			$('#DeliveryCompany').val($('#BillingCompany').val());
			$('#DeliveryAddress1').val($('#BillingAddress1').val());
			$('#DeliveryAddress2').val($('#BillingAddress2').val());
			$('#DeliveryCity').val($('#BillingCity').val());
			$('#DeliveryPostcode').val($('#BillingPostcode').val());
			$('#SameforDelivery').closest('.carttable-row').fadeOut('slow');

		}
	
	});
	
	/*------------------------------------------------------
	# Change shipping costs
	#  - element1 = dropdown that user interacts with
	#  - element2 = similar dropdown that needs auto selecting
	------------------------------------------------------*/
	function updateShipping(element1,element2) {
	
		var strCountry = $(element1).val();
		var strTotal = $("#amount").val();
		var strWeight = $("#weight").val();
		var strCatIDs = $('#cat_ids').val();
		var d = new Date();
		
		$(element2).val(strCountry);

		$('.feedback').append('<img src="/core/images/loader.gif" />');
			
		$.post("/index.php/store/updateshippingbycountry/" + '?_=' + d.getTime(), { country: strCountry, total: strTotal, weight: strWeight, cat_ids: strCatIDs },
			function(data){
				$('#shipping_select').html(data.shipping_select); //dropdown options
				$('#shipping_method').val(data.shipping_method); //selected option description
				$('#shipping').val(data.shipping_encrypted);
				$('#youPay').html('To pay: ' + data.total_topay);
				$('#myTotal').html(data.total_topay);
				$('#myVAT').html(data.vat);
				$('#vat').val(data.vat_encrypted);

				$('.feedback').empty();
								
		},'json');
	}
	
	//Assign the above function
	$('#DeliveryCountry').change(function() {
		updateShipping('#DeliveryCountry option:selected','#ShippingCalc');
	});

	$('#ShippingCalc').change(function() {
		updateShipping('#ShippingCalc option:selected','#DeliveryCountry');
	});

	if ( $('#DeliveryCountry').length > 0 ) {
		$('#DeliveryCountry').ready(function() {
			if ( $(this).is("select") ) {
				// If is a select dropdown
				updateShipping('#DeliveryCountry option:selected','#ShippingCalc');
			} else {
				// else is an input (hidden or text)
				updateShipping('#DeliveryCountry', '#ShippingCalc');
			}
		});
	}


	/*------------------------------------------------------
	# Update price by product options
	------------------------------------------------------*/
	$('.product_option_group input:radio').change(function(){
	
		$('p.saleprice').hide();
		
		var strProductId = $('[name=product_id]').val();
		var strOptionId = '';

		$('.feedback').append('<img src="/core/images/loader.gif" />');
		
		$('.product_option_group input:radio:checked').each(function(){
			
			strOptionId += $(this).val() + '-'; 

		});

		//console.log(strOptionId);

		$.post("/index.php/store/updatepricebyoptions/", { product_id: strProductId, option_id: strOptionId },
			function(data){
				$('#product_price').html(data.product_price); //dropdown options
				$('#sale_price').html(data.product_saleprice); //dropdown options
				$('#vat_price').html(data.product_vatprice);

				$('.feedback').empty();
								
		},'json');

	
	});


	/*------------------------------------------------------
	# Update total based on chosen shipping option
	------------------------------------------------------*/
	$('#shipping_select').change(function() {
		
		strShippingValue = $('#shipping_select option:selected').val();
		strTotal = $("#amount").val();
		strShippingMethod = $('#shipping_select option:selected').text();
		strShippingCountry = $('#ShippingCalc option:selected').val();

		$('.feedback').append('<img src="/core/images/loader.gif" />');
			
		$.post("/index.php/store/updatetotalbyshipping/", { shipping_value: strShippingValue, total: strTotal, shipping_method: strShippingMethod, country: strShippingCountry },
			function(data){
				$('#shipping_select').html(data.shipping_select); //dropdown options
				$('#shipping_method').val(data.shipping_method); //selected option description
				$('#shipping').val(data.shipping_encrypted);
				$('#youPay').html('To pay: ' + data.total_topay);
				$('#myTotal').html(data.total_topay);
				$('#myVAT').html(data.vat);
				$('#vat').val(data.vat_encrypted);
				
				$('.feedback').empty();
				
		},'json');

	});


	/*------------------------------------------------------
	# Product Gallery
	------------------------------------------------------*/
	$('body').on('click', '.shopit-gallery-thumb', function(event){
		
		// Prevent the default behaviour
		event.preventDefault();
		
		// Get the default size and zoom level for this photo
		var link = $(this).data('defaultsize');
		var zoom = $(this).data('zoom');
		var img_index = $(this).data('index');
		var fullsize = $(this).data('zoom-image');

		// Load the selected image into the main photo container
		$('#shopit-photo').fadeOut('fast').hide().attr('src', link).ready(function(){
			$('#shopit-photo-link').attr('href', link + '/' + zoom + '/' + zoom).attr('data-index', img_index);
			$('#shopit-photo').fadeIn('fast').attr('data-zoom-image', fullsize);
		});

	});

	$('body').on('click', '#shopit-photo-link', function(event){
		// Prevent the default behaviour
		event.preventDefault();

		// Bind fancybox to our class
		$('.shopit-gallery-fancybox').fancybox({
			type: 'image',
			nextEffect: 'fade',
			prevEffect: 'fade',
			// Show "Image X of N" title
			helpers: {
				title: {
					type: 'float'
				}
            },
            afterLoad: function() {
				this.title = (this.title ? '' + this.title + '<br />' : '') + 'Image ' + (this.index + 1) + ' of ' + this.group.length;
			},
			afterClose: function(){
				// Unbind the fancybox plugin so it doesn't open 
				// again when the thumbnails are clicked
				$(document).unbind('click.fb-start');
			}
		});
		
		// Get the index of the image we want to 
		// display. This is held in the link's data attribute.
		var img_index = $('#shopit-photo-link').attr('data-index');
		
		// Trigger fancybox to show the image we clicked on
		$('.shopit-gallery-fancybox').eq(img_index).trigger("click");

	});


	/*------------------------------------------------------
	# Checkout Form Validation
	------------------------------------------------------*/
	jQuery.validator.setDefaults({ 
	    messages: {
	    		
	    		BillingFirstname: '',
	    		BillingSurname: '',
	    		BillingAddress1: '',
	    		BillingCity: '',
	    		BillingPostcode: '',
	    		Email: '',
	    		Phone: '',
	    		DeliveryFirstname: '',
	    		DeliverySurname: '',
	    		DeliveryAddress1: '',
	    		DeliveryCity: '',
	    		DeliveryPostcode: '',
	    		Password: '',
	    		cPassword: '',
	    		AgreeTC: ''
	    	},
	    rules: {
	    		cPassword: {
	    			equalTo: '#Password'
	    		}
	    },
	    errorElement: 'span'
	});

	$('#formCheckout').validate();

	/*------------------------------------------------------
	# Basket: highlight the update button when quantity is
	# changed.
	------------------------------------------------------*/
	$('.basket-qty').change(function() {
		$('#btnUpdateBasket').click();
	});

	/*------------------------------------------------------
	# Submit sort results form
	------------------------------------------------------*/
	$('.shopit_results_sort').change(function(){
		$(this).closest('form').submit();
	});

	/*------------------------------------------------------
	# Hide cookie monster message
	------------------------------------------------------*/
	$('#cookie-monster-continue a').click(function(e) {
		e.preventDefault();
		$('#cookie-monster').fadeOut('fast');
	});


	/*------------------------------------------------------
	# Check if user already exists - checkout page
	------------------------------------------------------*/
	$('#formCheckout input').blur(function(){
		
		var strEmail = $('input[name="Email"]').val();
		
		if (strEmail != '') {
			
			$.post("/store/myaccount/checkuser", { Email: strEmail },
				function(data){
					$('#carttable-email').html(data.message);
					if (data.failed == true) {
						$('#btnCheckout').attr('disabled', 'disabled').animate({opacity: 0.3});
					} else {
						$('#btnCheckout').removeAttr('disabled').animate({opacity: 1.0});
					}			
			},'json');
				
		}
				
	});
	
	/*------------------------------------------------------
	# Variation Attributes (Dropdown/Selector)
	------------------------------------------------------*/
	$('body').on('change', '.shopit-variant-attr', function(){
		
		var $values = $(this).val();
		var $me 	= $(this);
		// var $variant_image = $('option:selected', this).attr('data-variant-image');
		
		if ($.isNumeric($values)) {
			// Do something with the product id here
			// that is returned if necessary...
	
			// Get the images for this variant
			var $image_default  = $('option:selected', this).attr('data-variant-image-default');
			var $image_fullsize = $('option:selected', this).attr('data-variant-image-fullsize');

			// Change the main product image by triggering a click on the appropriate gallery thumbnail
			var $image_gallery = $(this).data('gallery');
			if ($image_gallery != undefined) {
				$('.' + $image_gallery).trigger('click');
			}
			
		} else {
			$.ajax({
				type: 'POST',
				url: '/ajax/variant_attr',
				data: {values: $values},
				datatype: 'html',
				success: function(data) {
					// Hide any dropdowns that are already displaying below this one
					$me.closest('ul').parent('li').nextAll().remove();
					// Append the new dropdown to the list
					$('#shopit-variant-attrs').append(data);
				}
			});
		
		}
		
	});
	
});
