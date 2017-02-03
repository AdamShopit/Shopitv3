<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| SagePay Configuration
|--------------------------------------------------------------------------
|
| The settings below are for connecting the SagePay payment gateway to
| the checkout.
|
|--------------------------------------------------------------------------
| Connection Type
|--------------------------------------------------------------------------
|
| Set to SIMULATOR for the VSP Simulator expert system, TEST for the 
| Test Server and LIVE in the live environment.
|
*/
$config['strConnectTo']	= "SIMULATOR";

/*
|--------------------------------------------------------------------------
| Virtual Directory (IIS)
|--------------------------------------------------------------------------
|
| Change if you have created a Virtual Directory in IIS with a 
| different name.
|
*/
$config['strVirtualDir'] = "";

/*
|--------------------------------------------------------------------------
| Domain Name
|--------------------------------------------------------------------------
|
| IMPORTANT. Set this to the Fully Qualified Domain Name of your server.
| We'll default this to base_url() to cover this.
|
| This should start http:// or https:// and should be the name by which our 
| servers can call back to yours i.e. it MUST be resolvable externally, and 
| have access granted to the SagePay servers examples would be 
| https://www.mysite.com or http://212.111.32.22/ 
| NOTE: You should leave the final / in place.
|
*/
$config['strYourSiteFQDN'] = base_url(); 

/*
|--------------------------------------------------------------------------
| Vendor Name
|--------------------------------------------------------------------------
|
| Set this value to the VSPVendorName assigned to you by SagePay or chosen 
| when you applied.
|
*/
$config['strVSPVendorName'] = "";

/*
|--------------------------------------------------------------------------
| Encryption Password
|--------------------------------------------------------------------------
|
| Set this value to the XOR Encryption password assigned to you by SagePay.
|
*/
$config['strEncryptionPassword'] = "";

/*
|--------------------------------------------------------------------------
| Currency
|--------------------------------------------------------------------------
|
| Set this to indicate the currency in which you wish to trade. You will 
| need a merchant number in this currency.
|
*/
$config['strCurrency'] = "GBP";

/*
|--------------------------------------------------------------------------
| Transaction Type
|--------------------------------------------------------------------------
|
| This can be DEFERRED or AUTHENTICATE if your SagePay account supports 
| those payment types.
|
| Default is "PAYMENT".
|
*/
$config['strTransactionType'] = "PAYMENT";

/*
|--------------------------------------------------------------------------
| Partner ID
|--------------------------------------------------------------------------
|
| Optional setting. If you are a SagePay Partner and wish to flag the 
| transactions with your unique partner id set it here.
|
*/
$config['strPartnerID'] = "";

/*
|--------------------------------------------------------------------------
| Billing Country
|--------------------------------------------------------------------------
|
| Two-character ISO country code. Default "GB".
|
*/
$config['strBillingCountry'] = 'GB';

/*
|--------------------------------------------------------------------------
| Success/Failure URLs
|--------------------------------------------------------------------------
|
| Set the URLs for which the customer should be sent to. There should be
| no need to change these unless development requires it.
|
*/
$config['strSuccessURL'] = "payment/sagepay";
$config['strFailureURL'] = "payment/sagepay";

/*
|--------------------------------------------------------------------------
| Emails (Optional)
|--------------------------------------------------------------------------
|
| Optional setting:
|
|  0 = Do not send either customer or vendor e-mails, 
|  1 = Send customer and vendor e-mails if address(es) are provided(DEFAULT). 
|  2 = Send Vendor Email but not Customer Email. If you do not supply this 
|      field, 1 is assumed and e-mails are sent if addresses are provided.
|
*/
$config['bSendEMail'] = 1; 

/*
|--------------------------------------------------------------------------
| Order Confirmation Emails
|--------------------------------------------------------------------------
|
| Optional setting. Set this to the mail address which will receive order 
| confirmations and failures.
|
*/
$config['strVendorEMail'] = "";

/*
|--------------------------------------------------------------------------
| Protocol
|--------------------------------------------------------------------------
|
| Do not change.
|
*/
$config['strProtocol'] = "2.23";

/*
|--------------------------------------------------------------------------
| Purchase URL
|--------------------------------------------------------------------------
|
| SagePay connection URLS. Do not change.
|
*/
if ($config['strConnectTo'] == "LIVE") {
	$config['strPurchaseURL'] = "https://live.sagepay.com/gateway/service/vspform-register.vsp"; 
} elseif($config['strConnectTo'] == "TEST") {
	$config['strPurchaseURL'] = "https://test.sagepay.com/gateway/service/vspform-register.vsp";
} else {
	$config['strPurchaseURL'] = "https://test.sagepay.com/simulator/vspformgateway.asp";
}