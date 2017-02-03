<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Allow Query Strings in CI while still using AUTO uri_protocol
 *
 * @author Dan Horrigan
 * https://github.com/dhorrigan/codeigniter-query-string
 */
class Query_String {

	private static $_qs;

	public function __construct() { }

	public static function clean_uri()
	{
		$_GET = NULL;
		$orig_query_string = $_SERVER['QUERY_STRING'];

		if (strpos($_SERVER['QUERY_STRING'], '?') !== FALSE)
		{
			if (strpos($_SERVER['QUERY_STRING'], '?') < strpos($_SERVER['QUERY_STRING'], '&'))
			{
				$orig_query_string = substr($_SERVER['QUERY_STRING'], strpos($_SERVER['QUERY_STRING'], '?') + 1);
				$_SERVER['QUERY_STRING'] = str_replace('?'.$orig_query_string, '', $_SERVER['QUERY_STRING']);
			}
		}
		else
		{
			$_SERVER['QUERY_STRING'] = '';
		}
		self::$_qs = $orig_query_string;

		foreach (array('REQUEST_URI', 'PATH_INFO', 'ORIG_PATH_INFO') as $uri_protocol)
		{
			if ( ! isset($_SERVER[$uri_protocol]))
			{
				continue;
			}

			if (strpos($_SERVER[$uri_protocol], '?') !== FALSE)
			{
				$_SERVER[$uri_protocol] = str_replace('?'.$orig_query_string, '', $_SERVER[$uri_protocol]);
			}
		}
	}

	public static function recreate_get()
	{
		parse_str(self::$_qs, $_GET);
	}
}