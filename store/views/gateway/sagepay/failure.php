<div class="gatewaytable">

	<h2>Sorry, your order could not be processed</h2>
		
	<p class="errormsg" style="padding-bottom: 0px;"><strong>{strReason}</strong></p>
	<p>For reference, your transaction reference number is <strong>{order_number}</strong>.</p>

	<p><a href="<?=site_url();?>" class="btnShop">Return to homepage</a></p>

</div>			
	<? if ($this->config->item('strConnectTo') !== "LIVE") {
		echo "<table align=\"center\" border=\"1\" width=\"100%\">";
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