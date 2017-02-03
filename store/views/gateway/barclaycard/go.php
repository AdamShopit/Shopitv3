<form action="<?=$this->config->item('ePDQ_URL');?>" method="post" name="frmProcess">	

	<input type="hidden" name="PSPID" value="{PSPID}" />
	<input type="hidden" name="ORDERID" value="{ORDERID}" />
	<input type="hidden" name="AMOUNT" value="{AMOUNT}" />
	<input type="hidden" name="CURRENCY" value="{CURRENCY}" />
	<input type="hidden" name="LANGUAGE" value="{LANGUAGE}" />
	<input type="hidden" name="CN" value="{CN}" />
	<input type="hidden" name="EMAIL" value="{EMAIL}" />
	<input type="hidden" name="OWNERZIP" value="{OWNERZIP}" />
	<input type="hidden" name="OWNERADDRESS" value="{OWNERADDRESS}" />
	<input type="hidden" name="OWNERTOWN" value="{OWNERTOWN}" />
	<input type="hidden" name="OWNERCTY" value="{OWNERCTY}" />
	<input type="hidden" name="OWNERTELNO" value="{OWNERTELNO}" />
	<input type="hidden" name="COM" value="Your Order" />
	<input type="hidden" name="COMPLUS" value="{COMPLUS}" />
	<input type="hidden" name="PARAMPLUS" value="{PARAMPLUS}" />
	<input type="hidden" name="ACCEPTURL" value="{ACCEPTURL}" />
	<input type="hidden" name="DECLINEURL" value="{DECLINEURL}" />
	<input type="hidden" name="EXCEPTIONURL" value="{EXCEPTIONURL}" />
	<input type="hidden" name="CANCELURL" value="{CANCELURL}" />
	<input type="hidden" name="TITLE" value="{TITLE}" />
	<input type="hidden" name="BGCOLOR" value="{BGCOLOR}" />
	<input type="hidden" name="TXTCOLOR" value="{TXTCOLOR}" />
	<input type="hidden" name="TBLBGCOLOR" value="{TBLBGCOLOR}" />
	<input type="hidden" name="TBLTXTCOLOR" value="{TBLTXTCOLOR}" />
	<input type="hidden" name="BUTTONBGCOLOR" value="{BUTTONBGCOLOR}" />
	<input type="hidden" name="BUTTONTXTCOLOR" value="{BUTTONTXTCOLOR}" />
	<input type="hidden" name="FONTTYPE" value="{FONTTYPE}" />
	{TP_HTMLTAG}
	<input type="hidden" name="SHASIGN" value="{SHASIGN}" />

	<input type="submit" name="submit" value="Click here if you're not automatically redirected... " />
</form>