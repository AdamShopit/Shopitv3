<form name="frmProcess" method="post" action="<?=$this->config->item('CardSaveURL');?>">
<input type="hidden" name="HashDigest" value="{strCrypt}" />
<input type="hidden" name="MerchantID" value="<?=$this->config->item('CardSaveMerchantID');?>" />
<input type="hidden" name="Amount" value="{total}" />                                       
<input type="hidden" name="CurrencyCode" value="<?=$this->config->item('CardSaveCurrency');?>" />
<input type="hidden" name="OrderID" value="{orderid}" />
<input type="hidden" name="TransactionType" value="SALE" />
<input type="hidden" name="TransactionDateTime" value="{transaction_datetime}" />
<input type="hidden" name="CallbackURL" value="{callback_url}" />
<input type="hidden" name="OrderDescription" value="Your order {orderid}" />
<input type="hidden" name="CustomerName" value="<?=$this->input->post('BillingFirstname', true) . ' ' . $this->input->post('BillingSurname', true);?>" />
<input type="hidden" name="Address1" value="<?=$this->input->post('BillingAddress1', true);?>" />
<input type="hidden" name="Address2" value="<?=$this->input->post('BillingAddress2', true);?>" />
<input type="hidden" name="Address3" value="" />
<input type="hidden" name="Address4" value="" />
<input type="hidden" name="City" value="<?=$this->input->post('BillingCity', true);?>" /> 
<input type="hidden" name="State" value="" />
<input type="hidden" name="PostCode" value="<?=$this->input->post('BillingPostcode', true);?>" />
<input type="hidden" name="CountryCode" value="{country}" />
<input type="hidden" name="CV2Mandatory" value="true" />
<input type="hidden" name="Address1Mandatory" value="true" />
<input type="hidden" name="CityMandatory" value="true" />
<input type="hidden" name="PostCodeMandatory" value="true" />
<input type="hidden" name="StateMandatory" value="false" />
<input type="hidden" name="CountryMandatory" value="true" />
<input type="hidden" name="ResultDeliveryMethod" value="SERVER" />
<input type="hidden" name="ServerResultURL" value="<?=site_url('payment/csresponse');?>" />
<input type="hidden" name="PaymentFormDisplaysResult" value="false" />
<input type="hidden" name="ServerResultURLFormVariables" value="usersession=<?=get_cookie('basket');?>" />
<input type="submit" name="submit" value="Click here if you're not automatically redirected... "/>
</form>