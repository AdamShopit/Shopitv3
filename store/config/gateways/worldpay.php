<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Worldpay Configuration
|--------------------------------------------------------------------------
|
| The settings below are for connecting the Worldpay payment gateway to
| the checkout.
|
|--------------------------------------------------------------------------
| Mode
|--------------------------------------------------------------------------
|
| Set to either TEST or LIVE.
|
*/
$config['WorldpaySetup'] = 'TEST';

/*
|--------------------------------------------------------------------------
| Installation ID
|--------------------------------------------------------------------------
|
| Set the installation ID as provided by Worldpay. This can be found within
| the Worldpay control panel.
|
*/
$config['WorldpayInstId'] = '';

/*
|--------------------------------------------------------------------------
| Currency
|--------------------------------------------------------------------------
|
| The default currency the integration should use.
|
*/
$config['WorldpayCurrency'] = 'GBP';

/*
|--------------------------------------------------------------------------
| Language
|--------------------------------------------------------------------------
|
| The default language the Worldpay payment pages should use. The default
| language is "en-GB" (english Great British).
|
*/
$config['WorldpayLang'] = 'en-GB';

/*
|--------------------------------------------------------------------------
| Hide Contact
|--------------------------------------------------------------------------
|
| Hide customer's address details on Worldpay payment pages. The value of
| this setting should be a string NOT boolean.
|
*/
$config['WorldpayHideContact'] = 'true';

/*
|--------------------------------------------------------------------------
| Hide Currency
|--------------------------------------------------------------------------
|
| Hides the currency dropdown on the Worldpay payment pages. The value of
| this setting should be a string NOT boolean.
|
*/
$config['WorldpayHideCurrency'] = 'true';

/*
|--------------------------------------------------------------------------
| URLs
|--------------------------------------------------------------------------
|
| These settings should NOT be changed unless required.
|
*/
if ($config['WorldpaySetup'] == 'TEST') {
	$config['WorldpayURL'] 		= 'https://secure-test.worldpay.com/wcc/purchase';
	$config['WorldpayTestMode'] = '100';
} else {
	$config['WorldpayURL'] 		= 'https://secure.worldpay.com/wcc/purchase';
	$config['WorldpayTestMode'] = '0';
}