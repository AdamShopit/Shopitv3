<form action="<?=$this->config->item('WorldPayURL');?>" method="post" name="frmProcess">	

<input type="hidden" name="instId" value="<?=$this->config->item('WorldPayInstId');?>" />
<input type="hidden" name="cartId" value="{orderid}" />
<input type="hidden" name="amount" value="{total}">
<input type="hidden" name="currency" value="<?=$this->config->item('WorldPayCurrency');?>"/>
<input type="submit" name="submit" value="Click here if you're not automatically redirected... "/>
<input type="hidden" name="testMode" value="<?=$this->config->item('WorldPayTestMode');?>" />
<input type="hidden" name="M_usersession" value="<?=get_cookie('basket');?>" />
<input type="hidden" name="name" value="{name}" />
<input type="hidden" name="address" value="{address}" />
<input type="hidden" name="postcode" value="{postcode}" />
<input type="hidden" name="country" value="{country}" />
<input type="hidden" name="tel" value="{tel}" />
<input type="hidden" name="email" value="{email}" />
<input type="hidden" name="hideContact" value="<?=$this->config->item('WorldpayHideContact');?>" />
<input type="hidden" name="hideCurrency" value="<?=$this->config->item('WorldpayHideCurrency');?>" />
<input type="hidden" name="lang" value="<?=$this->config->item('WorldpayLang');?>" />
<input type="hidden" name="noLanguageMenu" />
<input type="hidden" name="MC_callback" value="{MC_callback}" />

</form>