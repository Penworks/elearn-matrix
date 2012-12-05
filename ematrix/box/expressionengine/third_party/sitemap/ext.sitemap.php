<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine Sitemap Extension
 *
 * @package		Sitemap
 * @category	Extension
 * @author		Ben Croker
 * @link		http://www.putyourlightson.net/sitemap-module
 */
 

// get config
require_once PATH_THIRD.'sitemap/config'.EXT;


class Sitemap_ext
{
	var $settings		= array();
	
	var $name			= SITEMAP_NAME;
	var $version		= SITEMAP_VERSION;
	var $description	= SITEMAP_DESCRIPTION;
	var $docs_url		= SITEMAP_URL;
	var $settings_exist = 'n';
	
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();
	} 
	
	// --------------------------------------------------------------------
	
	/**
	  *  Ping Sitemap
	  */
	function ping_sitemap($entry_id, $meta, $data, $view_url)
	{	
		$result = '';
				
				
		// check if ping sitemap was checked
		if (!$this->EE->input->post('sitemap__ping_sitemap'))
		{
			return;
		}		
		
		$results = array();
		
		$urls = array();
		
		// google
		$urls['Google'] = "http://www.google.com/webmasters/sitemaps/ping?sitemap=";
		
		// yahoo - have stopped their sitemap ping service
		//$urls['Yahoo'] = "http://search.yahooapis.com/SiteExplorerService/V1/updateNotification?appid=ee_yahoo_map_update&url=";
		
		// bing
		$urls['Bing'] = "http://www.bing.com/webmaster/ping.aspx?siteMap=";
		
		// ask.com
		$urls['Ask'] = "http://submissions.ask.com/ping?sitemap=";
		
		// moreover - removed as service seems to be no longer available
		//$urls['Moreover'] = "http://api.moreover.com/ping?u=";	
		
		
		foreach ($urls as $key => $url)
		{
			$url = $url.$this->EE->config->slash_item('site_url').'sitemap.php';
			
			// cURL method
			if (function_exists('curl_init'))
			{
				$results[$key] = $this->_curl_ping($url);
			}
			
			// fsocket method
			else
			{
				$results[$key] = $this->_socket_ping($url);
			}				
		}
				 
		
		$this->_confirmation_message($results);
		
		$this->EE->functions->redirect($view_url);
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Return confirmation message
	  */
	function _confirmation_message($results) 
	{	
		$success_message = '';
		$failure_message = '';
			
		foreach ($results as $key => $result)
		{
			if ($result == '1')
			{
				$success_message .= '<b>'.$key.'</b> was successfully notified about this entry<br/>';
			}
		
			else if ($result == '0')
			{
				$failure_message .= 'An error was encountered while trying to notify <b>'.$key.'</b> about this entry<br/>';
			}
		}
		
		if ($success_message)
		{
			$this->EE->session->set_flashdata('message_success', $success_message);
		}
		
		if ($failure_message)
		{
			$this->EE->session->set_flashdata('message_failure', $failure_message);
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Use the cURL method to send ping
	  */
	function _curl_ping($url) 
	{	
		$curl_handle = curl_init($url);
		curl_setopt($curl_handle, CURLOPT_HEADER, TRUE);
		curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($curl_handle);
		curl_close($curl_handle);
		
		$response_code = trim(substr($response, 9, 4));
		
		if ($response_code == 200)
		{
			return '1'; 
		}
		
		else
		{
			return '0';
		}
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Use the socket method to send ping
	  */
	function _socket_ping($url) 
	{	
		$url = parse_url($url);
		
		if (!isset($url["port"])) 
		{
			$url["port"] = 80;
		}
		
		if (!isset($url["path"])) 
		{
			$url["path"] = "/";
		}

		$fp = @fsockopen($url["host"], $url["port"], $errno, $errstr, 30);

		if ($fp) 
		{
			$http_request = "HEAD ".$url["path"]."?".$url["query"]." HTTP/1.1\r\n"."Host: ".$url["host"]."\r\n"."Connection: close\r\n\r\n";
			fputs($fp, $http_request);
	  		$response = fgets($fp, 1024);
			fclose($fp);
			
			$response_code = trim(substr($response, 9, 4));
			
			if ($response_code == 200)
			{
				return '1'; 
			}
		}
		
		return '0';
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Update Extension
	  */
	function update_extension($current='')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	
		if ($current < '1.6')
		{
			$this->EE->db->query("UPDATE exp_extensions SET hook = 'entry_submission_absolute_end' WHERE class = 'Sitemap_ext'");
		}
		
		$this->EE->db->query("UPDATE exp_extensions 
					SET version = '".$this->version."' 
					WHERE class = 'Sitemap_ext'");
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Activate Extension
	  */
	function activate_extension()
	{
		// add ping_sitemap
		$this->EE->db->insert('extensions',
								  array(
										'class'		=> "Sitemap_ext",
										'method'	   => "ping_sitemap",
										'hook'		 => "entry_submission_absolute_end",
										'settings'	 => "",
										'priority'	 => 10,
										'version'	  => $this->version,
										'enabled'	  => "y"
									)
		);
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Disable Extension
	  */
	function disable_extension()
	{
		$this->EE->db->query("DELETE FROM exp_extensions WHERE class = 'Sitemap_ext'");
	}	
	
}

// END CLASS

/* End of file ext.sitemap.php */
/* Location: ./system/expressionengine/third_party/sitemap/ext.sitemap.php */