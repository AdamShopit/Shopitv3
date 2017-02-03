<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	http://codeigniter.com/user_guide/general/hooks.html
|
*/

$hook['pre_system'] = array(
	'class'		=> 'Query_String',
	'function'	=> 'clean_uri',
	'filename'	=> 'query_string.php',
	'filepath'	=> 'hooks'
);
$hook['pre_controller'] = array(
	'class'		=> 'Query_String',
	'function'	=> 'recreate_get',
	'filename'	=> 'query_string.php',
	'filepath'	=> 'hooks'
);
// Hook to redirect to mobile website
$hook['post_controller'] = array(
    'class'    => 'Mobile',
    'function' => 'redirect',
    'filename' => 'Mobile.php',
    'filepath' => 'libraries',
    'params'   => '',
);
// SSL handling on non-checkout related pages
$hook['post_controller_constructor'] = array(
	'class'		=> 'Ssl',
	'function'	=> 'redirect',
	'filename'	=> 'ssl.php',
	'filepath'	=> 'hooks',
);

/* End of file hooks.php */
/* Location: ./system/application/config/hooks.php */