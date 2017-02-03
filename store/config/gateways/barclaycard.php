<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Barclaycard ePDQ Configuration
|--------------------------------------------------------------------------
|
| The settings below are for connecting the ePDQ payment gateway to
| the checkout.
|
| The following IPN URL needs adding to ePDQ control panel:
| - http://this-domain/payment/barclaycardipn
|
|--------------------------------------------------------------------------
| Mode
|--------------------------------------------------------------------------
|
| Set gateway mode to TEST or LIVE.
|
*/
$config['ePDQ_Setup'] = "TEST";

/*
|--------------------------------------------------------------------------
| Account ID
|--------------------------------------------------------------------------
|
| The ePDQ account ID (PSPID) as provided by Barclaycard.
|
*/
$config['ePDQ_PSPID'] = "";

/*
|--------------------------------------------------------------------------
| Passphrase
|--------------------------------------------------------------------------
|
| The SHA-1 passphrase as setup within the ePDQ control panel.
|
*/
$config['ePDQ_SHA1_Passphrase'] = "";

/*
|--------------------------------------------------------------------------
| Currency Code
|--------------------------------------------------------------------------
|
| Three-character currency code. Default is "GBP".
|
*/
$config['ePDQ_Currency'] = "GBP";

/*
|--------------------------------------------------------------------------
| Language
|--------------------------------------------------------------------------
|
| The default language the Worldpay payment pages should use. The default
| language is "en-GB" (english Great British).
|
*/
$config['ePDQ_Language'] = "en_GB";

/*
|--------------------------------------------------------------------------
| Dynamic Templates
|--------------------------------------------------------------------------
|
| Enable the dynamic templates feature of ePDQ (default is FALSE). This
| feature must be enabled by Barclaycard before this option can be enabled.
|
| The template is located at /views/[desktop] or [mobile]/gateways/barclaycard-template.php and
| can be viewed via a browser at http://this-domain/payment/barclaycard.html
|
*/
$config['ePDQ_DynamicTemplateEnabled'] = FALSE;

/*
|--------------------------------------------------------------------------
| Default Template Styles
|--------------------------------------------------------------------------
|
| Options to adjust the default payment templates i.e. not dynamic templates.
|
*/
$config['ePDQ_Template_BGCOLOR'] 		= "gainsboro"; 	// Background colour
$config['ePDQ_Template_TXTCOLOR'] 		= "black"; 		// Text colour
$config['ePDQ_Template_TBLBGCOLOR'] 	= "white"; 		// Table background colour
$config['ePDQ_Template_TBLTXTCOLOR']	= "black";		// Table text colour
$config['ePDQ_Template_BUTTONBGCOLOR'] 	= "crimson";	// Button background colour 
$config['ePDQ_Template_BUTTONTXTCOLOR'] = "white";		// Button text colour
$config['ePDQ_Template_FONTTYPE'] 		= "Arial";		// Font family

/*
|--------------------------------------------------------------------------
| URL
|--------------------------------------------------------------------------
|
| Sets the URL of the payment gateway depending on the mode set above. This
| setting should not be altered unless explicitly required.
|
*/
if ($config['ePDQ_Setup'] == "TEST") {
	$config['ePDQ_URL'] = "https://mdepayments.epdq.co.uk/ncol/test/orderstandard_utf8.asp";
} else {
	$config['ePDQ_URL'] = "https://payments.epdq.co.uk/ncol/prod/orderstandard_utf8.asp";
}