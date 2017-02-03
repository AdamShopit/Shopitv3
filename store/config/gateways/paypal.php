<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| PayPal Configuration
|--------------------------------------------------------------------------
|
| The settings below are for connecting the PayPal gateway to the checkout.
|
|--------------------------------------------------------------------------
| Currency Code
|--------------------------------------------------------------------------
|
| Three-character currency code. Default is "GBP".
|
*/
$config['paypalCurrencyCode'] = 'GBP';

/*
|--------------------------------------------------------------------------
| PayPal Email
|--------------------------------------------------------------------------
|
| The email address of the PayPal account to connect to.
|
*/
$config['paypalEmail'] = '';

/*
|--------------------------------------------------------------------------
| PDT Token
|--------------------------------------------------------------------------
|
| Optional token for the Payment Data Transfer service as provided by PayPal.
|
*/
$config['paypalPDTToken'] = '';

/*
|--------------------------------------------------------------------------
| PayPal Connect URL
|--------------------------------------------------------------------------
|
| URL to connect to PayPal. Do not change unless is required.
|
*/
$config['paypalURL'] = 'https://www.paypal.com/cgi-bin/webscr';
