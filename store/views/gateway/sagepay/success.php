<div class="gatewaytable">

	<h2>Thank you for your order</h2>

	<p>Your payment of <strong><?=$this->config->item('currency');?>{strAmount}</strong> was processed successfully. 
	Please make a note of your order number <strong>{order_number}</strong>.</p>
	
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

	<? if ($this->config->item('strConnectTo') !== "LIVE") {
		echo "<table align=\"center\" border=\"1\" width=\"90%\" summary=\"\">";
		echo 	"<tr>";
		echo 		"<td class=\"subheader\" align=\"center\" colspan=\"2\">Details sent back by VSP Form</td>";
		echo 	"</tr>";
		echo 	"<tr>";
		echo 		"<td width=\"30%\" align=\"right\" class=\"greybar\">VendorTxCode:</td>";
		echo 		"<td width=\"70%\" align=\"left\">" . $strVendorTxCode . "</td>";
		echo 	"</tr>";
		echo 	"<tr>";
		echo 		"<td width=\"30%\" align=\"right\" class=\"greybar\">Status:</td>";
		echo 		"<td width=\"70%\" align=\"left\">" . $strStatus . "</td>";
		echo 	"</tr>";
		echo 	"<tr>";
		echo 		"<td width=\"30%\" align=\"right\" class=\"greybar\">StatusDetail:</td>";
		echo 		"<td width=\"70%\" align=\"left\">" . $strStatusDetail . "</td>";
		echo 	"</tr>";
		echo 	"<tr>";
		echo 		"<td width=\"30%\" align=\"right\" class=\"greybar\">Amount:</td>";
		echo 		"<td width=\"70%\" align=\"left\">" . $strAmount . "&nbsp;" . $strCurrency . "</td>";
		echo 	"</tr>";
		echo 	"<tr>";
		echo 		"<td width=\"30%\" align=\"right\" class=\"greybar\">VPSTxId:</td>";
		echo 		"<td width=\"70%\" align=\"left\">" . $strVPSTxId . "</td>";
		echo 	"</tr>";
		echo 	"<tr>";
		echo 		"<td width=\"30%\" align=\"right\" class=\"greybar\">VPSAuthCode (TxAuthNo):</td>";
		echo 		"<td width=\"70%\" align=\"left\">" . $strTxAuthNo . "</td>";
		echo 	"</tr>";
		echo 	"<tr>";
		echo 		"<td width=\"30%\" align=\"right\" class=\"greybar\">AVSCV2 Results:</td>";
		echo 		"<td width=\"70%\" align=\"left\">" . $strAVSCV2 . "<span class=\"smalltext\"> - Address:" . $strAddressResult . ", Post Code:" . $strPostCodeResult . ", CV2:" . $strCV2Result . "</span></td>";
		echo 	"</tr>";
		echo 	"<tr>";
		echo 		"<td width=\"30%\" align=\"right\" class=\"greybar\">Gift Aid Transaction?:</td>";
		echo 		"<td width=\"70%\" align=\"left\">"; if ($strGiftAid=="1") echo "Yes"; else echo "No";
		echo 		"</td>";
		echo 	"</tr>";
		echo 	"<tr>";
		echo 		"<td width=\"30%\" align=\"right\" class=\"greybar\">3D-Secure Status:</td>";
		echo 		"<td width=\"70%\" align=\"left\">" . $str3DSecureStatus ."</td>";
		echo 	"</tr>";
		echo 	"<tr>";
		echo 		"<td width=\"30%\" align=\"right\" class=\"greybar\">CAVV:</td>";
		echo 		"<td width=\"70%\" align=\"left\">" . $strCAVV . "</td>";
		echo 	"</tr>";
		echo 	"<tr>";
		echo 		"<td width=\"30%\" align=\"right\" class=\"greybar\">CardType:</td>";
		echo 		"<td width=\"70%\" align=\"left\">" . $strCardType . "</td>";
		echo 	"</tr>";
		echo 	"<tr>";
		echo 		"<td width=\"30%\" align=\"right\" class=\"greybar\">Last4Digits:</td>";
		echo 		"<td width=\"70%\" align=\"left\">" . $strLast4Digits . "</td>";
		echo 	"</tr>";
		echo 	"<tr>";
		echo 		"<td width=\"30%\" align=\"right\" class=\"greybar\">AddressStatus:</td>";
		echo 		"<td width=\"70%\" align=\"left\"><span style=\"float:right; font-size: smaller;\">&nbsp;*PayPal transactions only</span>" . $strAddressStatus . "</td>";
		echo 	"</tr>";
		echo 	"<tr>";
		echo 		"<td width=\"30%\" align=\"right\" class=\"greybar\">PayerStatus:</td>";
		echo 		"<td width=\"70%\" align=\"left\"><span style=\"float:right; font-size: smaller;\">&nbsp;*PayPal transactions only</span>" . $strPayerStatus . "</td>";
		echo 	"</tr>";
		echo "</table>";
		}
	?>


