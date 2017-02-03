$(document).ready(function() {

	//! Load fancybox manually on set links
	$('.fancybox-popup').fancybox({
		'autoSize': false,
		'width': 970,
		'height': 450,
		'padding': 10,
		'margin': 0,
		'centerOnScroll': true,
		'type': 'iframe'
	});

	//!Tooltips
	$('.textbox').each(function(){
		if(!$(this).attr('title')){ return; }
		$(this).qtip({
			style: {
				classes: 'qtip-bootstrap',
			},
			position: {
				my: 'left bottom',
				at: 'right top'
			},
			show: {
				delay: 0,
				event: 'focus'
			},
			hide: {
				event: 'blur'
			},
			content: {
				title: 'Tip',
				button: true
			}
		});
	});

	$('.info li').each(function(){
		if(!$(this).attr('title')){ return; }
		$(this).qtip({
			style: {
				classes: 'qtip-bootstrap',
			},
			position: {
				my: 'center right',
				at: 'left center'
			},
			show: {
				delay: 0,
				event: 'mouseover'
			},
			hide: {
				event: 'mouseout'
			},
			content: {
				title: false,
				button: false
			}
		});
	});

	$('.whats-this').each(function(){
		if(!$(this).attr('title')){ return; }
		$(this).qtip({
			style: {
				classes: 'qtip-bootstrap',
			},
			position: {
				my: 'left bottom',
				at: 'right top'
			},
			show: {
				delay: 0,
				event: 'mouseover'
			},
			hide: {
				event: 'mouseout'
			},
			content: {
				title: false,
				button: false
			}
		});
	});

	//!Shopit menu dropdown
	$('#shopitmenu a.nolink').click(function(e){
		return false;
	});

	$('#shopitmenu .nested a').click(function(){
		if ($(this).siblings('ul').is(":visible")) {
			$(this).closest('li').removeClass('selected');
			$(this).siblings('ul').hide();
		} else {
			$('#shopitmenu ul').hide();
			$('#shopitmenu .nested').removeClass('selected');
			$(this).closest('li').addClass('selected');
			$(this).siblings('ul').show();
		}
	});

	$(document).bind('click', function(e) {
	    var clicked = $(e.target);
	    if (! clicked.parents().hasClass("selected")) {
	        $("#shopitmenu .selected ul").hide();
	        $("#shopitmenu .nested").removeClass('selected');
	    }
	});

	//!Actions menu dropdown
	$('.btn-action').click(function(event){
		event.preventDefault();
	});

	$('.actions .btn-action').click(
		function(){
			if ($(this).next('ul').is(":visible")) {
				$(this).next('ul').hide();
			} else {
				$('.actions li ul').hide();
				$(this).next('ul').show();
			}
		}
	);

	$('.actions ul a').click(function(){
		$(this).closest('ul').delay(600).fadeOut();
	});

	//!Add item to collection (used on inventory page)
	$('.addtocollection').click(function(event) {
		event.preventDefault();
		var d = new Date();
		var link = $(this).attr('href') + '?_=' +d.getTime(); //Append datetime to prevent caching
		$.get(link,function(data){
			if (data.status == 'on') {
				$('#' + data.rowid + ' .collection-' + data.menuid + ' img').removeClass('hide-tick');
			} else {
				$('#' + data.rowid + ' .collection-' + data.menuid + ' img').addClass('hide-tick');
			}
		},'json');	

	});

	//!Enable/disable item (used on inventory page)
	//-status 0 means disabled
	$('.itemstatus').click(function(event) {
		event.preventDefault();
		var d = new Date();
		var link = $(this).attr('href') + '?_=' +d.getTime(); //Append datetime to prevent caching
		var id = $(this).closest('tr').attr('id');
		
		$.get(link,function(data){
			if (data.status == '0') {
				$('#' + data.rowid + ' .itemstatus-' + data.menuid + ' img').removeClass('hide-tick');
				$('#' + data.rowid + ' .status-indicator').removeClass('light-red').addClass('light-green');
				$('.child-group-' + id + ' .status-indicator').removeClass('light-red').addClass('light-green');
			} else {
				$('#' + data.rowid + ' .itemstatus-' + data.menuid + ' img').addClass('hide-tick');
				$('#' + data.rowid + ' .status-indicator').removeClass('light-green').addClass('light-red');
				$('.child-group-' + id + ' .status-indicator').removeClass('light-green').addClass('light-red');
			}
		},'json');	

	});

	//!Set tooltips preference
	$('#pref_tooltips').click(function(event) {
		event.preventDefault();
		var d = new Date();
		var link = $(this).attr('href') + '?_=' +d.getTime(); //Append datetime to prevent caching
		$.get(link,function(data){
			if (data.status == 'on') {
				$('#pref_tooltips img').removeClass('hide-tick');
			} else {
				$('#pref_tooltips img').addClass('hide-tick');
			}
		},'json');	

	});

	//!Set codebox preference
	$('#pref_codebox').click(function(event) {
		event.preventDefault();
		var d = new Date();
		var link = $(this).attr('href') + '?_=' +d.getTime(); //Append datetime to prevent caching
		$.get(link,function(data){
			if (data.status == 'on') {
				$('#pref_codebox img').removeClass('hide-tick');
			} else {
				$('#pref_codebox img').addClass('hide-tick');
			}
		},'json');	

	});

	//!Ajax mark order as dispatched
	$('.markasdispatched a').click(function(event){
		
		event.preventDefault();
		var link = $(this).attr('href');

		$(this).closest('tr').find('.badge').removeAttr('style').html('<img src="/admin/assets/images/loader.gif" class="valign"/>');
		
		$(this).load(link,
			function(){
				$(this).closest('tr').find('.badge').addClass('badge-green').text('Dispatched');
				$(this).closest('li').fadeOut();
			});
	
	});

	//!Ajax delete
	$('.ajaxdelete').click(function(event){

		event.preventDefault();
		var element = $(this);
		var link 	= $(this).attr('href');
		var groupid = $(this).data('groupid'); // This can be used to delete related groups, i.e. variations
		var message = "Are you sure you want to delete this item? This cannot be undone.";
		
		if ( $(this).attr('rel') ) {
			message = $(this).attr('rel');
		}
	
	    var answer = confirm(message);
	    if (answer){
			$.get(link,
			function() {
				$(element).closest("tr").fadeOut('slow');
				$(element).closest(".table-row").fadeOut('slow');
				$(element).closest(".sortable-image").fadeOut('slow').remove();
				if (groupid != "") {
					$('.' + groupid).fadeOut('slow');
				}
			}); 		
	    }

    	return false;  

	});

	$('.ajaxdeletecollectionitem').click(function(event){

		event.preventDefault();
		var link = $(this).attr('href');
	
	    var answer = confirm("Are you sure you want to delete this item? This cannot be undone.");
	    if (answer){
			$(this).load(link,
			function() {
				$(this).closest(".grid-product").fadeOut('slow');
			}); 		
	    }

    	return false;  

	});

	//!Ajax edits
	$('.editproductqty').editable('/admin/index.php/inventory/editproductqty',{
         submit    : 'OK',
         event     : 'dblclick',
         indicator : '<img src="/admin/assets/images/loader.gif">',
         tooltip   : 'Double click to edit...'
	});

	$('.editpageslug').editable('/admin/index.php/pages/editslug',{
         cancel    : 'Cancel',
         submit    : 'Save',
         event     : 'click',
         indicator : '<img src="/admin/assets/images/loader.gif">',
         tooltip   : 'Click to edit...'
	});

	$('.editcollectionslug').editable('/admin/index.php/collections/editslug',{
         cancel    : 'Cancel',
         submit    : 'Save',
         event     : 'click',
         indicator : '<img src="/admin/assets/images/loader.gif">',
         tooltip   : 'Click to edit...'
	});

	$('.editproductslug').editable('/admin/index.php/inventory/editslug',{
         cancel    : 'Cancel',
         submit    : 'Save',
         event     : 'click',
         indicator : '<img src="/admin/assets/images/loader.gif">',
         tooltip   : 'Click to edit...'
	});

	$('.editapilabel').editable('/admin/index.php/options/api/label',{
         submit    : 'Save',
         event     : 'click',
         indicator : '<img src="/admin/assets/images/loader.gif">',
         tooltip   : 'Click to edit...'
	});

	//!Product Attributes
	$('.addattribute').click(function(e){
		e.preventDefault();
		var new_name = $('input[name="attribute_name_new"]').val();
		var new_value = $('input[name="attribute_value_new"]').val();
		var html = '<li class="table-row product-attribute highlight">'+
						'<label><img src="/admin/assets/images/icon-draggable2.png" alt="" class="valign draggable" /></label>'+
						'<input name="attribute_name[]" type="text" value="' + new_name + '" class="textbox" size="30" /> = '+
						'<input name="attribute_value[]" type="text" value="' + new_value + '" class="textbox" size="30" />'+
						'<input name="attribute_id[]" value="" type="hidden" />'+
						'<input name="attribute_delete[]" type="hidden" value="false" />'+
				   '</div>';
		$('input[name="attribute_name_new"]').val('');
		$('input[name="attribute_value_new"]').val('');
		$('#sortable-productattributes').append(html);
	});

	$('.removeattribute').click(function(e){
		e.preventDefault();
		$(this).closest('.table-row').animate({height:'toggle'},600);
		$(this).closest('.table-row').find('input[name="attribute_delete[]"]').val('true');
	});

	//!Product Options
	$('.addproductoption').click(function(e){
		e.preventDefault();
		var new_label = $('input[name="option_label_new"]').val();
		var new_criteria = $('input[name="option_criteria_new"]').val();
		var new_price = $('input[name="option_price_new"]').val();
		var html = '<li class="table-row product-option highlight">'+
						'<label><img src="/admin/assets/images/icon-draggable2.png" alt="" class="valign draggable" /></label>'+
						'<input name="option_label[]" type="text" value="' + new_label + '" class="textbox" size="20" /> '+
						'<input name="option_criteria[]" type="text" value="' + new_criteria + '" class="textbox" size="20" /> '+
						'<input name="option_price[]" type="text" value="' + new_price + '" class="textbox number" size="20" />'+
						'<input name="option_id[]" value="" type="hidden" />'+
						'<input name="option_delete[]" type="hidden" value="false" />'+
				   '</li>';
		$('input[name="option_criteria_new"]').val('');
		$('input[name="option_price_new"]').val('');
		$('#sortable-productoptions').append(html);
	});


	$('.removeproductoption').click(function(e){
		e.preventDefault();
		$(this).closest('.table-row').animate({height:'toggle'},600);
		$(this).closest('.table-row').find('input[name="option_delete[]"]').val('true');
	});

	//!Barchart animations
	/*
	$('.bar-marker').css({'width':'0px'});
	$('.bar-marker').each(function(index){
		
		var pc = $(this).attr('width');
		var ct = index * 200;
		$(this).delay(ct).animate({width:pc},'slow');

	});
	*/

	//! Load conversion rates to inventory list
	$('.item-conv-rate').each(function(){
		var $link = $(this).attr('data-conv-rate');
		var $me = $(this);
		
		$(this).html('<span class="loader"></span>');
		
		$.ajax({
			type: "GET",
			async: true,
			dataType: 'html',
			url: $link,
			success: function(data) {
				$me.html(data);
			}
		});
	});

	//!Related items
	$('input[name="related_items"]').keyup(function() {
			
		var value = escape($(this).val());
		var product_id = $('input[name="product_id"]').val();

		var link = '/admin/index.php/inventory/showrelatedresults/' + product_id + '/' + value;
		
		$('#showrelatedresults').load(link,
		function() {

			$('.addrelateditem').click(function(event){
				event.preventDefault();
				
				var id 	 = $(this).closest('.relateditemselect').find("input[name='related_product_id']").val();
				var name = $(this).closest('.relateditemselect').find("input[name='related_product_name']").val();
				var no   = $(this).closest('.relateditemselect').find("input[name='related_product_no']").val();
				var type = $(this).data('type');
				var type_title = $(this).attr('value');
			
				$('#relateditems tbody').append('<tr class="tbody highlight"><td>' + name + '</td><td>' + no + '</td><td>' + type_title + '</td><td><input type="hidden" name="related_items_id[]" value="' + id + '" /><input type="hidden" name="related_items_delete[]" value="false" /><input type="hidden" name="related_items_type[]" value="' + type + '" /></td></tr>');
				
				$(this).closest('tr').fadeOut('slow');
			});
			
		});

	});

	$('.removerelateditem').click(function(){
		$(this).closest('tr').fadeOut('slow', function(){ 
			$(this).find('input[name="related_items_delete[]"]').val('true');
		});
	});

	//!Markitup! Editor
	$(".editor textarea").markItUp(mySettings);
	$(".editor-image textarea").markItUp(imageOnly);
	
	//!Sortable gallery
	$("#sortable-gallery").sortable({ 

	    handle : '.gallerythumb',
	    tolerance: 'pointer',
	    update : function () { 

			var strProductId = $('[name="gallery_product_id"]').val();
			var strImages = '';

			$('[name="gallery_product_image[]"]').each(function(){
				strImages += $(this).val() + ';'; 
			});

			$.post("/admin/index.php/inventory/gallerysort", { product_id: strProductId ,product_images: strImages });
			
	    } 
	    
	 });

	//!Sortable collection
	$("#sortable-collection").sortable({ 

	    handle : '.grid-product',
	    tolerance: 'pointer',
	    update : function () { 

			var strCollectionId = $('[name="collection_id"]').val();
			var strProductId = '';

			$('[name="product_id[]"]').each(function(){
				strProductId += $(this).val() + ';'; 
			});

			$.post("/admin/index.php/collections/sortable", { collection_id: strCollectionId ,collection_items: strProductId });
			
	    } 
	    
	});

	//!Sortable product options
	$('#sortable-productoptions').sortable({		
		items: 'li',
		tolerance: 'pointer',
		handle: '.draggable',
		cursor: 'move'
	});

	//!Sortable product attributes
	$('#sortable-productattributes').sortable({		
		items: 'li',
		tolerance: 'pointer',
		handle: '.draggable',
		cursor: 'move'
	});

	//!Sortable categories
	$('#sortable-categories').sortable({
		items: 'tbody tr',
		opacity: 0.8,
		change: function(){$('.ui-sortable-helper').addClass('highlight');},
		tolerance: 'pointer',
		axis: 'y',
		cursor: 'move',
		handle: '.draggable',
		update: function() {
				var strCatId = '';
				
				$('[name="cat_id[]"]').each(function(){
					strCatId += $(this).val() + ';';
				});
				
				$.post('/admin/index.php/category/sortable', {cat_id: strCatId});
			}
	});

	//!Sortable pages
	$('.sortable-pages').sortable({
		items: 'tbody tr',
		opacity: 0.8,
		change: function(){$('.ui-sortable-helper').addClass('highlight');},
		tolerance: 'pointer',
		axis: 'y',
		cursor: 'move',
		handle: '.draggable',
		update: function() {
				var strPageId = '';
				
				$('[name="page_id[]"]').each(function(){
					strPageId += $(this).val() + ';';
				});
				
				$.post('/admin/index.php/pages/sortable', {page_id: strPageId});
			}
	});

	//!Sortable collection list
	$('tbody.sortable-collections').sortable({
		connectWith: false,
		dropOnEmpty: false,
		items: 'tr',
		opacity: 0.8,
		change: function(){$('.ui-sortable-helper').addClass('highlight');},
		tolerance: 'pointer',
		axis: 'y',
		cursor: 'move',
		handle: '.draggable',
		update: function() {
				var strCollectionId = '';
				
				$('[name="collection_id[]"]').each(function(){
					strCollectionId += $(this).val() + ';';
				});
				
 				$.post('/admin/index.php/collections/sortlist', {collection_id: strCollectionId});
			}
	});

	//!Sortable cross sell groups
	$('#sortable-crosssellgroups').sortable({
		items: 'tbody tr',
		opacity: 0.8,
		change: function(){$('.ui-sortable-helper').addClass('highlight');},
		tolerance: 'pointer',
		axis: 'y',
		cursor: 'move',
		handle: '.draggable',
		update: function() {
				var strGroupId = '';
				
				$('[name="id[]"]').each(function(){
					strGroupId += $(this).val() + ';';
				});
				
				$.post('/admin/index.php/inventory/sortcrosssellgroups', {id: strGroupId});
			}
	});

	//!Show/hide dispatch_date form bits
	$('select[name="s_orderstatus"]').change(function(){
		if ($(this).val() == 'Dispatched') {
			$('#set_dispatched').slideDown();
		} else {
			$('#set_dispatched').slideUp();
		}
	});

	//!Add border to last column header
	$('table').each(function(){
		$(this).find('th:last').addClass('last-column');
	});

	//!Close sections
	$('#sections li a').click(function(event){
		/* event.preventDefault(); */
		$('.section').hide();
		var section = $(this).attr('href');
		$(section).fadeIn('fast');
		$(this).removeClass('button-off');
		$('#sections li a').not(this).addClass('button-off');
	});

	//Check the hash and display the open section
	var this_hash = window.location.hash;
	var current_tab = $('#sections li a[href="'+this_hash+'"]');
	if (current_tab.length === 0) {
		$(this_hash).fadeIn('fast');
	} else {
		current_tab.click();
	}

	//!Extra categories add/remove
	$('#add_category').click(function() {
		$('input[name="xcategory_change"]').val('true');
		return !$('select[name="categories"] option:selected').remove().appendTo('select[name="x_categories[]"]');
 	});
	$('#remove_category').click(function() {
		$('input[name="xcategory_change"]').val('true');
  		return !$('select[name="x_categories[]"] option:selected').remove().appendTo('select[name="categories"]');
 	});
	$('form').submit(function() {
		$('select[name="x_categories[]"] option').each(function(i) {
	  		$(this).attr("selected", "selected");
	 	});
	});

	//!Page redirect field
	var rel = $('#page_redirect').attr('rel');
		
	if ($('#page_redirect').val() != '') {
		$('.' + rel).slideUp('slow');		
	} else {
		$('.' + rel).slideDown('slow');
	}
		
	$('#page_redirect').keyup(function(){
					
		if ($('#page_redirect').val() != '') {
			$('.' + rel).slideUp('slow');		
		} else {
			$('.' + rel).slideDown('slow');
		}
		
	});

	//!Add country to shipping rules
	$('#formAddCountry').submit(function(event) { 
		
		event.preventDefault();
    	
    	$('#feedback').html('<img src="/admin/assets/images/loader.gif" class="valign"/>').fadeIn('slow');
    	
    	var url 	= $(this).attr('action');
    	var country = $('#country_name').val();

		$.ajax({
			type: 'POST',
			url: url,
			data: {
				country_name: country
			},
			dataType: 'html',
			success: function(response) {
				$('#feedback').hide();
				$('#listcountries').append(response);
			}
		});
    	
	});

	//!Shipping rules creation
	$('#shipping_operation').change(function(){
		var val = $(this).val(); 
		if (val == 'between') {
			$('#shipping_value2').show();		
		}
		else {
			$('#shipping_value2').hide();
		}
	});

	//!Shipping rule for Categories criteria
	$('#criteria').change(function(){
		var val = $(this).val();
		if (val == "category") {
			$('#shipping_operation').load('/admin/index.php/shipping/load_categories');
			$('input[name="value"]').val('0.00').hide();
			$('#shipping_value2').hide();
		} else {
			$('#shipping_operation').load('/admin/index.php/shipping/load_criteria');
			$('input[name="value"]').show();
		}
	});

	//!Load attribute set
	$('#loadAttributeSet').change(function(e){
		var id = $(':selected',this).val();
		var link = '/admin/index.php/inventory/loadattr/' + id;
		
		if (id > 0) {
			$('#sortable-productattributes').load(link);
		}
	});

	//!Load product option set
	$('#loadProductOptionSet').change(function(e){
		var id = $(':selected',this).val();
		var link = '/admin/index.php/inventory/loadproductopts/' + id;
		
		if (id > 0) {
			$('#sortable-productoptions').load(link);
		}
	});
	
	//!Variant name attributes - split and join
	$('#shopit_content').on('click', '#btn-splitvariantname', function(event){
		event.preventDefault();
		var $link = '/admin/index.php/inventory/splitvariantname';
		
		$.ajax({
			type: "GET",
			url: $link,
			success: function(html){
				$('#ajax-splitvariantname').html(html);
				$('#variant-name').animate({opacity:0.4});
				$('input[name="product_name"]').attr('readonly', 'readonly').removeClass('required');
				$('#sortable-productattributes').sortable({		
					items: 'li',
					tolerance: 'pointer',
					handle: '.draggable',
					cursor: 'move'
				});
			}
			
		})
	});
	
	// Rejoin the variant attributes and populate the product name field
	$('#shopit_content').on('click', '#btn-joinvariantname', function(event){
		event.preventDefault();
		$('#variant-name').show().animate({opacity:1.0});
		
		var $pieces = [];
		
		$('.variant-attr-value').each(function(){
			var $value = $(this).val();
			if ($value != '') {
				$pieces.push($value);
			}
		});
		
		var $title = $pieces.join(' ');
		
		$('input[name="product_name"]').removeAttr('readonly').addClass('required').val($title);
		$('#ajax-splitvariantname input[type="text"]').val('');
		$('#ajax-splitvariantname ul, #ajax-splitvariantname div').remove();
	});
	
	// Remove variant attribute image
	$('body').on('click', '.variant_attr_image_remove', function(event){
		event.preventDefault();
		
		var $input = $(this).data('imginput');
		
		$(this).closest('li').find('img').attr('src', '/admin/assets/scripts/markitup/sets/html/images/picture.png').removeAttr('height');
		$('input[name="' + $input + '"]').val('');
		$(this).remove();
	});

	//!Dashboard widgets chooser
	$('#widgets-list, #widgets-list-active').sortable({
		connectWith: ".widgets-list",
		tolerance: 'pointer',
		cursor: 'move',
		items: 'li'
	}).disableSelection();

	//!Real-time widgets update
	if ($('.realtime_widget').length > 0) {
		setInterval(function(){
			var content = '/admin/index.php/dashboard/realtime?_=' + Math.random();
			$('.widget_scrollable').load(content + ' #realtime_widget_content');
		}, 10000);
	}

	//!JQueryUI date picker
	$('.jqueryui-date-picker').datepicker({ 
		dateFormat: 'yy-mm-dd',
		showOn: 'focus',
		showButtonPanel: true,
	});

	//!More filter options
	if ($('.filter-more input').is(':checked')) {
		$('#filter-more').hide();
		$('.filter-more').show();
	} else {
		$('#filter-more').show();
		$('.filter-more').hide();
	}
	
	$('#filter-more').click(function(e){
		e.preventDefault();
		$(this).hide();
		$('.filter-more').show();
	});

	//!Custom field 'option'
	$('[name="custom_field_type"]').change(function(){
		if ($(this).val() == "option") {
			$('[name="custom_field_default"]').removeAttr('disabled');
		} else {
			$('[name="custom_field_default"]').attr('disabled','disabled');
		}
	});

	if ($('[name="custom_field_type"]').val() == "option") {
		$('[name="custom_field_default"]').removeAttr('disabled');
	} else {
		$('[name="custom_field_default"]').attr('disabled','disabled');
	}

	//!Autocomplete textboxs
	function field_autocomplete(textbox, lookup_div, url) {

		$(textbox).keyup(function(){
		
			var value = escape($(this).val());
			var link = '/admin/index.php/' + url + value;
			
			$(lookup_div).load(link, 
			function(){
			
				//Show the autocomplete dropdown
				$(lookup_div).show();
	
				$('.lookup-result').click(function(){
					//Paste the clicked artist into the field
					var result = $(this).attr('rel');
					$(textbox).val(result);
					
					//Now hide the autocomplete dropdown
					$(lookup_div).hide();
	
				});
	
				//Hide the dropdown when clicked outside it
				$(lookup_div).hover(function(){ 
				        mouse_is_inside=true; 
				    }, function(){ 
				        mouse_is_inside=false; 
				});
				
				$("body").mouseup(function(){ 
					if(! mouse_is_inside) $(lookup_div).hide();
				});
	
			});
			
		});
	
	}
	
	var mouse_is_inside;
	field_autocomplete('input[name="product_brand"]', '#product_brand_lookup', 'inventory/brands/');
	field_autocomplete('input[name="supplier_code"]', '#supplier_code_lookup', 'inventory/suppliers/');
	
	//!UPC/MPN numbers
	$('#more-mpnupc').click(function(e){
		e.preventDefault();
		$('.more-mpnupc').slideDown();
		$(this).hide();
	});

	//!Set countries VAT exempt field via tick box
	$('.vat-exempt').click(function(){
		var intId = $(this).attr('rel');
		var d = new Date();
		var link = "/admin/index.php/shipping/updatevat" + '?_=' +d.getTime();
		
		if ($(this).is(":checked")) {
			var intVatValue = '1';
		} else {
			var intVatValue = '0';
		}
		
		$.post(link, { id: intId ,vat_exempt: intVatValue });
		$(this).closest('td').append(' <span>Updated.</span>').addClass('highlight');
		$(this).closest('tr').addClass('highlight');
	});

	//!Display countries dropdown
	$('#display_country').change(function(e){
		$('#formDisplayCountryRules').submit();
	});

	//!Google Preview
	google_preview(); //Update on page load or on keyup (below)

	$('.google-preview').keyup(function(){
		google_preview();
	});
	
	function google_preview() {
				
		var intProductId = $('input[name="product_id"]').val();
		var intCatId = $('[name="cat_id"]').val();
		var strProductTitle = $('input[name="product_name"]').val();
		var strMetaTitle = $('input[name="product_meta_title"]').val();
		var strProductDesc = $('textarea[name="product_description"]').val();
		var strMetaDesc = $('textarea[name="product_meta_description"]').val();
		
		$('#google-preview').load('/admin/index.php/inventory/googlepreview', {product_title: strProductTitle, meta_title: strMetaTitle, product_desc: strProductDesc, meta_desc: strMetaDesc, product_id: intProductId, cat_id: intCatId });
	
	}

	//!Sortable variations
	$('.child-item-group').sortable({
		items: 'tr',
		opacity: 0.8,
		axis: 'y',
		connectWith: '.child-item-group',
		dropOnEmpty: true,
		handle: '.draggable',
		tolerance: 'pointer',
		change: function(){$('.ui-sortable-helper').addClass('highlight');},
		cursor: 'move',
		update: function(event, ui) {
					var strChildId = '';
					// Get the parent ID of the group we're sending this item to
					var $target_parent_id = $(ui.item).closest('tbody').attr('data-parent_id');
					
					// Update the target group
					$(ui.item).closest('.child-item-group').find('[name="child_id[]"]').each(function(){
						strChildId += $(this).val() + ';';
					});
					
					// Remove the placeholder if it's there
					$(ui.item).closest('.child-item-group').find('.child-group-placeholder').remove();
					
					// If the group the item came from is not empty, display the placeholder
					$(ui.sender).not(':has(.child-item)').append('<tr class="child-group-placeholder"><td colspan="9" align="center"><span class="badge badge-grey">Drag &amp; drop a variation here</span></td></tr>');
					
					$.post('/admin/index.php/inventory/sortvariations', {child_id: strChildId, parent_id: $target_parent_id});
				}
	});

	//!CREATE CUSTOMER ACCOUNT - Check if user already exists
	$('input#account_user').blur(function(){
		
		var strEmail = $('input[name="account_user"]').val();
		
		if (strEmail != '') {
			
			$.post("/admin/index.php/customers/checkuser", { Email: strEmail },
				function(data){
					$('#account_checkuser').html(data.message);
					if (data.failed == true) {
						$('input[type="submit"]').attr('disabled', 'disabled').animate({opacity: 0.3});
					} else {
						$('input[type="submit"]').removeAttr('disabled').animate({opacity: 1.0});
					}			
			},'json');
				
		}
				
	});

	//!Bulk Action Dropdown - Check all within this table
    $('#shopit_content').on('click', 'input[name="checkall"]', function(){
     	
 		var $table = $(this).closest('table');
    	
	    if ($(this).is(':checked')) {
		    $('.checkall', $table).each(function(){
			    $(this).prop('checked', true);
		    });
		    $('input[name="submitcheck"]').removeAttr('disabled');
	    } else {
		    $('.checkall', $table).each(function(){
			    $(this).prop('checked', false);
		    });
		    $('input[name="submitcheck"]').attr('disabled','disabled');
	    }
	    
    });

	//Action Dropdown
	//Check if at least one checkall checkbox is ticked and unlock
	//the submit button
	$('.checkall').click(function(){
		var count = $('.checkall:checked').length;
		if (count > 0) {
			$('input[name="submitcheck"]').removeAttr('disabled');
		} else {
			$('input[name="submitcheck"]').attr('disabled','disabled');
		}
	});

    //Action Dropdown
    //Open popup on select options in orders (bulk) action dropdown
    $('select[name="action"]').change(function(){
	    
	    var current_option = $(':selected', this).attr('rel');
	    
	    if (current_option == "popup") {
		    $('#formCheck').attr('target', '_blank');
	    } else {
		    $('#formCheck').removeAttr('target');
	    }
	    
    });

	//!Sortable order statuses
	$('.order-status-group').sortable({
		items: 'tr',
		opacity: 1.0,
		axis: 'y',
		handle: '.draggable',
		tolerance: 'pointer',
		change: function(){$('.ui-sortable-helper').addClass('highlight');},
		cursor: 'move'
	});
	
	//!PRODUCT TAGS
	//Product Tags - Add	
	$('input[name="ptag_new"]').keypress(function(e){
			
		// If enter (13) or comma (188 or 44) key is pressed 
		// append the tag the product_tags input
		if (e.which == 13 || e.which == 188 || e.which == 44) {

			// Prevent the parent form submitting
			e.preventDefault();
		
			// Get the current value of the product_tags input and
			// the value of the tag we just added
			var old_tags = $('input[name="product_tags"]').val();
			var new_tag = $(this).val().toLowerCase();
			
			// Only add the tag if it is not empty
			if (new_tag != "") {
			
				// Append the new tag to the existing list with a comma but
				// only if there IS an existing list
				if (old_tags == '') {
					var tags = new_tag;
				} else {
					var tags = old_tags + ',' + new_tag;
				}
				
				// Update the hidden field with the tags
				$('#product_tags').val(tags);
				
				// Append the new tag to the end of the ul list
				var html = '<li>' + new_tag + '<a href="#" class="ptag-remove"></a></li> ';
	
				$('#ptags-new').before(html);
			
			}
			
			// And finally blank this form field
			$(this).val('');
		
		}
		
	});
	
	//Product Tags - Remove
	$('#ptags').on('click', '.ptag-remove', function(e){
		
		// Prevent the link
		e.preventDefault();
		
		// Get this tag value
		var label = $(this).closest('li').text();
		
		// Get the contents of the product_tags input
		var tags = $('input[name="product_tags"]').val();
		
		// Remove the chosen tag from the input
		var updated_tags = tags.replace(label, '');
		
		// Update the hidden input field
		$('#product_tags').val(updated_tags);
		
		// Remove the tag from the ul list
		$(this).closest('li').remove();
		
	});

	//!FILTERS (LAYERED NAV)
	// Sortable filter groups
	$('#content').sortable({
		items: '.filter-group',
		opacity: 1.0,
		axis: 'y',
		handle: '.draggable-parent',
		tolerance: 'pointer',
		cursor: 'move'
	});

	// Sortable filter group options
	$('#filter-groups').sortable({
		items: '.filter-group-option',
		opacity: 1.0,
		axis: 'y',
		handle: '.draggable-child',
		tolerance: 'pointer',
		cursor: 'move',
		placeholder: 'sortable-placeholder',
		forcePlaceholderSize: true,
		connectWith: '.filter-group-options'
	});

	// Collapse filter groups
	$('#filter-groups-collapse').click(function(e){
		e.preventDefault();
		
		if ($(this).hasClass('collapsed')) {
			// Open all the groups
			$(this).removeClass('collapsed');
			$('.filter-group-options').slideDown();
			$('.filter-group-option-new').slideDown();
			// Show the appropriate label
			$('.toggle-filter-group').html('&uarr;');
		} else {
			// Close all the groups
			$(this).addClass('collapsed');
			$('.filter-group-options').slideUp();
			$('.filter-group-option-new').slideUp();
			// Show the appropriate label
			$('.toggle-filter-group').html('&darr;');
		}
		
	});

	// Collapse (toggle) as single filter group
	$('#filter-groups').on('click', '.toggle-filter-group', function(e){
		e.preventDefault();
		$(this).closest('.filter-group').find('.filter-group-options').slideToggle();
		$(this).closest('.filter-group').find('.filter-group-option-new').slideToggle();

		// Show the appropriate label
		if ($(this).hasClass('collapsed')) {
			// Open all the groups
			$(this).removeClass('collapsed');
			$(this).html('&uarr;');
		} else {
			// Close all the groups
			$(this).addClass('collapsed');
			$(this).html('&darr;');
		}

	});
		
	// Create a new filter group
	$('#filter-groups-create').click(function(e){

		// Prevent the default action
		e.preventDefault();
		
		// Hide the filters welcome message (this only 
		// shows when no filters have been setup)
		$('#filters-welcome').hide();
		
		// Get the link to the page
		var link_to_template = $(this).attr('href');
		
		// Count the number of groups we currently have and increment by 1
		var intN = $('.filter-group').length + 1;
		
		// Append the new group to the ul#filter-groups
		$.post(link_to_template, {n: intN}, function(data){
			$(data).prependTo('#filter-groups');
		});

	});
	
	// Add a new filter option to the existing group
	$('#filter-groups').on('click', '.add-filter-option', function(e){

		// Prevent the default action
		e.preventDefault();
		
		// Get the link to the page
		var link_to_template = $(this).attr('href');
		
		// Get the data we need to pass
		var group = $(this).closest('.filter-group');
		var filter_group = $(group).find('.filter-group-options');
		var strLabel = $(group).find('input[name="new_filter_label"]').val();
		var strColour = $(group).find('input[name="new_filter_colour"]').val();

		// Count the number of filter options we currently 
		// have within this group and increment by 1
		var intN = $(group).find('.filter-group-option').length + 1;
		
		// Get the array key for this filter group
		var intKey = $(group).data('key');
		
		// Append the new group to this .filter-group
		$.post(link_to_template, {filter_label: strLabel, filter_colour: strColour, n: intN, key: intKey }, function(data){
			$(data).appendTo(filter_group);
		});
		
		// Blank the fields we just copied from
		$(group).find('input[name="new_filter_label"]').val('');
		$(group).find('input[name="new_filter_colour"]').val('');
		
	});

	// Remove filter option from the existing group
	$('#filter-groups').on('click', '.remove-filter-option', function(e){
		
		// Prevent the default action
		e.preventDefault();
		
		// Get the data we need to use
		var link_to_template = $(this).attr('href');
		var intFilterID 	 = $(this).data('filter-id'); 
		var message 	 	 = "Are you sure you want to delete this filter option? This cannot be undone.";
		var group_option 	 = $(this).closest('.filter-group-option');
		
		// Show message to confirm action
		if ( $(this).attr('rel') ) {
			message = $(this).attr('rel');
		}
		
		// If answer is yes then delete the filter
	    var answer = confirm(message);
	    if (answer){
	    	$.post(link_to_template, {filter_id: intFilterID}, function(data) {
		    	$(group_option).remove();
	    	});
	    }

    	return false;
		
	});
	
	// Delete filter group (and all its filter options)
	$('#filter-groups').on('click', '.remove-filter-group', function(e){

		// Prevent the default action
		e.preventDefault();

		// Get the data we need to use
		var link_to_template = $(this).attr('href');
		var intGroupID 	 	 = $(this).data('group-id');
		var message 	 	 = "Are you sure you want to delete this filter group and all its options? This cannot be undone.";
		var group 	 		 = $(this).closest('.filter-group');

		// Show message to confirm action
		if ( $(this).attr('rel') ) {
			message = $(this).attr('rel');
		}
		
		// If answer is yes then delete the filter
	    var answer = confirm(message);
	    if (answer){
	    	$.post(link_to_template, {group_id: intGroupID}, function(data) {
		    	$(group).fadeOut(600, function() {
		    		$(this).remove();
		    	});
	    	});
	    }

    	return false;
	
	});

	// Add a new filter via the add/edit inventory page
	$('#section-filters').on('keypress', 'input[name="new_filter"]', function(e){

		// If enter (13) key is pressed do the neccessary
		if (e.which == 13) {

			// Prevent the parent form submitting
			e.preventDefault();
			
			// Get the group_id, link and label
			var intGroupID = $(this).data('group-id');
			var strLabel = $(this).val();
			var link_to_template = $(this).data('link');
			
			// Get the closest fieldset
			var filter_group = $(this).closest('fieldset').find('div');

			// Count the number of filter options we currently 
			// have within this group and increment by 1
			var intN = $(filter_group).find('label').length + 1;

			// Post the data and append the response to the fieldset,
			// but only if the label is not empty
			if (strLabel != "") {
				$.post(link_to_template, {group_id: intGroupID, label: strLabel, filter_order: intN}, function(data){
					$(data).appendTo(filter_group);
				});
				
				// Blank the input
				$(this).val('');
			}
			
		}
		
	});
	
	// Load filters into inventory section
	$('select#cat_id').ready(function(){
		update_item_filters('select#cat_id option:selected');
	});

	$('select#cat_id').change(function(){
		update_item_filters(this);
	});

	function update_item_filters(element) {
		
		// Get the id's
		var intCatId = $(element).val();
		var intProductId = $('input[name="product_id"]').val();
		
		// Get the link to the page
		var link_to_template = $('#shopit-ajax-filter-section').data('link');

		$.post(link_to_template, {cat_id: intCatId, product_id: intProductId}, function(data){
			$('#section-filters').html(data);
		});

	}

	// Convert text to slug
	function convertToSlug(Text) {
    	return Text.toLowerCase().replace(/[^\w ]+/g, '').replace(/ +/g, '_');
	}
	
	$('.match-title').keyup(function(){
		var strText = $(this).val();
		var strSlug = convertToSlug(strText);
		$('.match-tag').val(strSlug);
	});
	
	// Make the snippet tags selectable (so they can be copy and pasted)
    $('body').on('click', 'code.select-on-click', function(){
	    var str =  $(this).text();
	    var intWidth = $(this).width();
	    var tmpInput = '<input autofocus="on" readonly="readonly" style="width:'+intWidth+'px;display:inline-block;border:none;background:transparent;text-align:center;margin:0;padding:0;color:black;" type="text" value="'+str+'" />';
	    $(this).html(tmpInput).removeClass('select-on-click').addClass('unselect-on-click');
    });
    
    $('body').on('blur', 'code.unselect-on-click', function(){
	    var str = $('input', this).val();
	    $(this).text(str).removeClass('unselect-on-click').addClass('select-on-click');
    });

	//! Ajax Uploads
	$('#section-gallery').on('click', '#btn-photo-upload', function(event){
		
		// Get the url to post to
		var url = $(this).attr('rel');
		
		// Update the button text
		$(this).val('Uploading...');
		
		// Get the selected files from the input
		var myFilesSelect = document.getElementById('photo-select');
		var myFiles = myFilesSelect.files;
		
		// Create a new FormData object
		var myFormData = new FormData();
		
		// Loop through each of the selected files
		for (var i = 0; i < myFiles.length; i++) {
			
			var myFile = myFiles[i];
			
			// Check the file type
			if (!myFile.type.match('image.*')) {
				$('#btn-photo-upload').val('Upload photo');
				continue;
			}
			
			// Add the file to the request
			myFormData.append('product_image', myFile, myFile.name);
			
			// Set up the request
			var xhr = new XMLHttpRequest();
			
			// Open the connection
			xhr.open('POST', url, true);
			
			// Set up a handler for when the request finishes
			xhr.onload = function() {
				if (xhr.status == 200) {
					// Files uploaded
					$('#btn-photo-upload').val('Upload photo');
				} else {
					//alert('An error occurred for ' + myFile.name );
				}
			}
			
			// Send the data
			xhr.send(myFormData);
			
			// Append the html on to the view
			xhr.onreadystatechange = function() {
				if (xhr.readyState == 4) {
					var data = (xhr.responseText);
					$('#sortable-gallery').append(data);
					$('#btn-photo-upload').val('Upload photo');
				}
			}
			
		}
		
	});

	//! User Permission Relations - auto-ticks related permission sections
	$('.permissions input[type="checkbox"]').click(function(){
		if ($(this).attr('rel') && $(this).is(':checked')) { 
			var relation = $(this).attr('rel');
			$('input[name="'+relation+'"]').prop('checked', true);
		}
	});

	//! FORM VALIDATION
	jQuery.extend(jQuery.validator.messages, {
		required: "Required",
		email: "Invalid",
		number: "Invalid",
		digits: "Invalid"
   	});
	
	// Inventory add/edit form (for single, variation and variant)
	$('#formAddEditItem').validate({
		errorElement: 'span',
		invalidHandler: function(form, validator){
			if (validator.numberOfInvalids() > 0) {
            	validator.showErrors();
            	
            	var $section = $(':input.error').closest('.section');
            	var $section_id = $section.attr('id');
            	
            	// Open the tab where the error occured
            	$('#sections li a[href="#'+$section_id+'"]').click();

            }
		}
	});
	
	//! Sticky Table Headers
	$('table.sticky').stickyTableHeaders();
	
	//! Custom Shortcuts
	$('body').on('click', '.shopit-shortcut-create', function(event){
		event.preventDefault();
		var $label   = prompt('Create a shortcut to the current page?', 'Give me a label...');
		var $link    = $(this).attr('href');
		var $element = $(this).parent('li');

		if ($label.length > 1) {
			$.ajax({
				type: "GET",
				url: $link,
				data: {
					label: $label
				},
				dataType: 'json',
				success: function(data){
					$($element).before('<li><a href="' + data.value + '" class="shopit-shortcut">' + data.label + ' <span data-link="/admin/index.php/options/deshortcut/' + data.id + '">&#10005;</span></a></li>');
				}
			});
		}
	});
	
	$('body').on('click', '.shopit-shortcut span', function(event){
		event.preventDefault();
		var $element = $(this).closest('li');
		var $link = $(this).data('link');
		var answer = confirm('Delete shortcut?');
		
		if (answer) {
			$.ajax({
				type: "GET",
				url: $link,
				success: function(data){
	 				$($element).remove();
				}
			});
		}
	});

});
