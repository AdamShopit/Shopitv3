	<!-- ************************************************************************************* -->
	<!-- This form is all that is required to submit the payment information to the VSP system -->
	<form action="<?=$this->config->item('strPurchaseURL');?>" method="post" name="frmProcess">	
	<input type="hidden" name="VPSProtocol" value="<?=$this->config->item('strProtocol');?>"/>
	<input type="hidden" name="TxType" value="<?=$this->config->item('strTransactionType'); ?>"/>
	<input type="hidden" name="Vendor" value="<?=$this->config->item('strVSPVendorName'); ?>"/>
	<input type="hidden" name="Crypt" value="<?=$strCrypt ?>"/>
	<input type="submit" name="submit" value="Click here if you're not automatically redirected... "/>
	<input type="hidden" name="navigate" value="proceed"/>
	</form>
	<!-- ************************************************************************************* -->

<!--
	<? if ($this->config->item('strConnectTo') !== "LIVE"): ?> 
	<div>
		<h4>Your VSP Form Crypt Post Contents</h4>
		<p style="color: black;"><small>The box below shows the unencrypted contents of the VSP Form
  		Crypt field.  This will not be displayed in LIVE mode.  If you wish to view the encrypted and encoded
		contents view the source of this page and scroll to the bottom.  You'll find the submission FORM there.</small></p>
  		<p style="color: red;word-wrap:break-word;"><small><?=$strPost;?></small></p>
	</div>
	<? endif; ?>

-->
