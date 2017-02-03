<div class="gatewaytable">

	<h2>Thank you for your order</h2>

	<p>Your payment of <strong>{Amount}</strong> was processed successfully. 
	Please make a note of your order number <strong>{OrderRef}</strong>.</p>
	
	<p><a href="<?=site_url();?>" class="btnShop">Return to homepage</a></p>

</div>

<script type="text/javascript">
  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', '<?=$this->config->item('google_ua');?>']);
  _gaq.push(['_trackPageview']);
  _gaq.push(['_addTrans',
    '<?=$google_trans['orderid'];?>',	// order ID - required
    '<?=$google_trans['store'];?>',  	// affiliation or store name
    '<?=$google_trans['total'];?>',  	// total - required
    '<?=$google_trans['tax'];?>',    	// tax
    '<?=$google_trans['shipping'];?>', 	// shipping
    '<?=$google_trans['city'];?>',      // city
    '<?=$google_trans['state'];?>',     // state or province
    '<?=$google_trans['country'];?>'    // country
  ]);
  
   // add item might be called for every item in the shopping cart
   // where your ecommerce engine loops through each item in the cart and
   // prints out _addItem for each
   <?php foreach($google_inventory as $product):?>
  _gaq.push(['_addItem',
    '<?=$product['orderid'];?>',	// order ID - required
    '<?=$product['sku'];?>',		// SKU/code - required
    '<?=$product['name'];?>',		// product name
    '<?=$product['variant'];?>',	// category or variation
    '<?=$product['price'];?>',		// unit price - required
    '<?=$product['qty'];?>'			// quantity - required
  ]);
  _gaq.push(['_trackTrans']); //submits transaction to the Analytics servers
  <?php endforeach; ?>

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
</script>
