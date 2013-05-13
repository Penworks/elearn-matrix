<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Videos Helper File
 *
 * @package			DevDemon_ChannelViDeos
 * @version			2.2
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class Channel_videos_helper
{
	private $package = 'channel_videos';

	public function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();

		$this->site_id = $this->EE->config->item('site_id');
	}

	// ********************************************************************************* //

	function define_theme_url()
	{
		if (defined('URL_THIRD_THEMES') === TRUE)
		{
			$theme_url = URL_THIRD_THEMES;
		}
		else
		{
			$theme_url = $this->EE->config->item('theme_folder_url').'third_party/';
		}

		// Are we working on SSL?
		if (isset($_SERVER['HTTP_REFERER']) == TRUE AND strpos($_SERVER['HTTP_REFERER'], 'https://') !== FALSE)
		{
			$theme_url = str_replace('http://', 'https://', $theme_url);
		}

		if (! defined('CHANNELVIDEOS_THEME_URL')) define('CHANNELVIDEOS_THEME_URL', $theme_url . 'channel_videos/');

		return CHANNELVIDEOS_THEME_URL;
	}

	// ********************************************************************************* //

	public function get_theme_url()
	{
		$theme_url = $this->EE->config->item('theme_folder_url').'third_party/' . $this->package . '/';
		$theme_url = str_replace(array('http://','https://'), '//', $theme_url);
		return $theme_url;
	}

	// ********************************************************************************* //

	public function get_router_url($type='url', $method='channel_videos_router')
	{
		// Do we have a cached version of our ACT_ID?
		if (isset($this->EE->session->cache['channel_videos']['router_url'][$method]['ACT_ID']) == FALSE)
		{
			$this->EE->db->select('action_id');
			$this->EE->db->where('class', 'Channel_videos');
			$this->EE->db->where('method', $method);
			$query = $this->EE->db->get('actions');
			$ACT_ID = $query->row('action_id');
		}
		else $ACT_ID = $this->EE->session->cache['channel_videos']['router_url'][$method]['ACT_ID'];

		// RETURN: Full Action URL
		if ($type == 'url')
		{
			// Grab Site URL
			$url = $this->EE->functions->fetch_site_index(0, 0);

			if (defined('MASKED_CP') == FALSE OR MASKED_CP == FALSE)
			{
				// Replace site url domain with current working domain
				$server_host = (isset($_SERVER['HTTP_HOST']) == TRUE && $_SERVER['HTTP_HOST'] != FALSE) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
				$url = preg_replace('#http\://(([\w][\w\-\.]*)\.)?([\w][\w\-]+)(\.([\w][\w\.]*))?\/#', "http://{$server_host}/", $url);
			}

			// Config Overrife for ACT URL's?
			$over = $this->EE->config->item('channel_videos_act_url');
			if (is_array($over) == TRUE)
			{
				if (isset($over[$method]) == TRUE) $url = $over[$method];
			}

			// Create new URL
			$ajax_url = $url.QUERY_MARKER.'ACT=' . $ACT_ID;

			// Are we working on SSL?
			$ajax_url = str_replace('http://', '//', $ajax_url);

			if (isset($this->EE->session->cache['channel_videos']['router_url'][$method]['URL']) == TRUE) return $this->EE->session->cache['channel_videos']['router_url'][$method]['URL'];
			$this->EE->session->cache['channel_videos']['router_url'][$method]['URL'] = $ajax_url;
			return $this->EE->session->cache['channel_videos']['router_url'][$method]['URL'];
		}

		// RETURN: ACT_ID Only
		if ($type == 'act_id') return $ACT_ID;
	}

	// ********************************************************************************* //

	/**
	 * Grab File Module Settings
	 * @return array
	 */
	function grab_settings($site_id=FALSE)
	{
		$settings = array();

		if (isset($this->EE->session->cache['channel_videos']['settings']) == TRUE)
		{
			$settings = $this->EE->session->cache['channel_videos']['settings'];
		}
		else
		{
			$this->EE->db->select('settings');
			$this->EE->db->where('module_name', 'Channel_videos');
			$query = $this->EE->db->get('exp_modules');
			if ($query->num_rows() > 0) $settings = unserialize($query->row('settings'));
		}

		$this->EE->session->cache['channel_videos']['settings'] = $settings;

		if ($site_id)
		{
			$settings = isset($settings['site:'.$site_id]) ? $settings['site:'.$site_id] : array();
		}

		return $settings;
	}

	// ********************************************************************************* //

	/**
	 * Generate new XID
	 *
	 * @return string the_xid
	 */
	public function xid_generator()
	{
		// Maybe it's already been made by EE
		if (defined('XID_SECURE_HASH') == TRUE) return XID_SECURE_HASH;

		// First is secure_forum enabled?
		if ($this->EE->config->item('secure_forms') == 'y')
		{
			// Did we already cache it?
			if (isset($this->EE->session->cache['XID']) == TRUE && $this->EE->session->cache['XID'] != FALSE)
			{
				return $this->EE->session->cache['XID'];
			}

			// Is there one already made that i can use?
			$this->EE->db->select('hash');
			$this->EE->db->from('exp_security_hashes');
			$this->EE->db->where('ip_address', $this->EE->input->ip_address());
			$this->EE->db->where('`date` > UNIX_TIMESTAMP()-3600');
			$this->EE->db->limit(1);
			$query = $this->EE->db->get();

			if ($query->num_rows() > 0)
			{
				$row = $query->row();
				$this->EE->session->cache['XID'] = $row->hash;
				return $this->EE->session->cache['XID'];
			}

			// Lets make one then!
			$XID	= $this->EE->functions->random('encrypt');
			$this->EE->db->insert('exp_security_hashes', array('date' => $this->EE->localize->now, 'ip_address' => $this->EE->input->ip_address(), 'hash' => $XID));

			// Remove Old
			//$DB->query("DELETE FROM exp_security_hashes WHERE date < UNIX_TIMESTAMP()-7200"); // helps garbage collection for old hashes

			$this->EE->session->cache['XID'] = $XID;
			return $XID;
		}
	}

	// ********************************************************************************* //

	/**
	 * Format tags
	 *
	 * Cleans up the tag, by removing unwanted characters
	 *
	 * @param string $str[optional] - The unformatted tag
	 * @return string The formatted tag
	 */
	public function format_tag($str='')
	{
		global $DB, $PREFS, $REGX;

		$this->EE->load->helper('text');

		$not_allowed = array('$', '?', ')', '(', '!', '<', '>', '/', '\\');

		$str = str_replace($not_allowed, '', $str);

		//$str	= ( $this->convert_case === true ) ? $this->_strtolower( $str ): $str;

		if ($this->EE->config->item('auto_convert_high_ascii') == 'y')
		{
			$str	=  ascii_to_entities($str);
		}

		$str	= $this->EE->security->xss_clean($str);

		return trim($str);
	}

	// ********************************************************************************* //

	/**
	 * Insert Tag in DB
	 *
	 * @param string $tag - The tag
	 * @return int - The tag ID
	 */
	public function create_tag($tag)
	{
		global $DB, $LOC, $SESS;

		// Data array for insertion
		$data = array(	'tag_name'	=>	$tag,
						'site_id'	=>	$this->site_id,
						'author_id'	=>	$this->EE->session->userdata['member_id'],
						'entry_date'=>	$this->EE->localize->now,
						'edit_date'	=>	$this->EE->localize->now,
						'total_entries' => 0,
				);

		$this->EE->db->insert('exp_points', $data);

		return $this->EE->db->insert_id();

	}

	// ********************************************************************************* //

	/**
	 * Url Safe Tag
	 *
	 * This method is responsible for encode/decode tags for URL's
	 *
	 * @param string $tag - The original Tag
	 * @param bool $encode[optional] - Are we encoding or decoding
	 * @return string - The processed Tag
	 */
	public function urlsafe_tag($tag, $encode=TRUE)
	{
		// We atleast need a default..
		if (isset($this->settings['urlsafe_seperator']) == FALSE) $this->settings['urlsafe_seperator'] = 'plus';

		if ($encode == true)
		{
			switch($this->settings['urlsafe_seperator'])
			{
				case 'plus':
					$tag = str_replace(' ', '+', $tag);
					break;
				case 'dash':
					$tag = str_replace(' ', '-', $tag);
					break;
				case 'underscore':
					$tag = str_replace(' ', '_', $tag);
					break;
			}

			$tag = str_replace(' ', '%20', $tag);
			$tag = htmlentities($tag, ENT_QUOTES, 'UTF-8');
		}
		else
		{
			switch($this->settings['urlsafe_seperator'])
			{
				case 'plus':
					$tag = str_replace('+', ' ', $tag);
					break;
				case 'dash':
					$tag = str_replace('-', ' ', $tag);
					break;
				case 'underscore':
					$tag = str_replace('_', ' ', $tag);
					break;
			}

			$tag = str_replace('%20', ' ', $tag );
			$tag = html_entity_decode($tag);
		}

		return $tag;
	}

	// ********************************************************************************* //

	public function generate_json($obj)
	{
		if (function_exists('json_encode') === FALSE)
		{
			if (class_exists('Services_JSON_CUSTOM') === FALSE) include dirname(__FILE__).'/JSON.php';
			$JSON = new Services_JSON_CUSTOM();
			return $JSON->encode($obj);
		}
		else
		{
			return json_encode($obj);
		}
	}

	// ********************************************************************************* //

	public function decode_json($obj)
	{
		if (function_exists('json_decode') === FALSE)
		{
			if (class_exists('Services_JSON_CUSTOM') === FALSE) include dirname(__FILE__).'/JSON.php';
			$JSON = new Services_JSON_CUSTOM();
			return $JSON->decode($obj);
		}
		else
		{
			return json_decode($obj);
		}
	}

	// ********************************************************************************* //

	/**
	 * Is a Natural number  (0,1,2,3, etc.)
	 *
	 * @access	public
	 * @param	string
	 * @return	bool
	 */
	public function is_natural_number($str)
	{
   		return (bool)preg_match( '/^[0-9]+$/', $str);
	}

	// ********************************************************************************* //

	function parse_keywords($str, $remove=array())
	{
		// Remove all whitespace except single space
		$str = preg_replace("/(\r\n|\r|\n|\t|\s)+/", ' ', $str);

		// Characters that we do not want to allow...ever.
		// In the EE cleaner, we lost too many characters that might be useful in a Custom Field search, especially with Exact Keyword searches
		// The trick, security-wise, is to make sure any keywords output is converted to entities prior to any possible output
		$chars = array(	'{'	,
						'}'	,
						"^"	,
						"~"	,
						"*"	,
						"|"	,
						"["	,
						"]"	,
						'?'.'>'	,
						'<'.'?' ,
					  );

		// Keep as a space, helps prevent string removal security holes
		$str = str_replace(array_merge($chars, $remove), ' ', $str);

		// Only a single single space for spaces
		$str = preg_replace("/\s+/", ' ', $str);

		// Kill naughty stuff
		$str = trim($this->EE->security->xss_clean($str));

		return $str;
	}

	// ********************************************************************************* //

	/**
	 * Fetch URL with file_get_contents or with CURL
	 *
	 * @param string $url
	 * @return mixed
	 */
	function fetch_url_file($url, $user=false, $pass=false)
	{
		$data = '';


		/** --------------------------------------------
		/**  cURL
		/** --------------------------------------------*/

		if (function_exists('curl_init') === TRUE && ($ch = @curl_init()) !== FALSE)
		{
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_HEADER, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			//if ( ! ini_get('safe_mode') && ! ini_get('open_basedir')) curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
			curl_setopt($ch, CURLOPT_USERAGENT, @$_SERVER['HTTP_HOST'].' Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.9.0.5) Gecko/2008120122 Firefox/3.0.5 (.NET CLR 3.5.30729)');

			if ($user != FALSE)
			{
				curl_setopt($ch, CURLOPT_USERPWD, "$user:$pass");
				if (defined('CURLOPT_HTTPAUTH')) curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			}

//exit($url);
			$data = curl_exec($ch);
			curl_close($ch);

			if ($data !== FALSE)
			{
				return trim($data);
			}
		}

		/** --------------------------------------------
		/**  file_get_contents()
		/** --------------------------------------------*/

		if ((bool) @ini_get('allow_url_fopen') !== FALSE && $user == FALSE)
		{
			if ($data = @file_get_contents($url))
			{
				return trim($data);
			}
		}

		/** --------------------------------------------
        /**  fsockopen() - Last but only slightly least...
        /** --------------------------------------------*/

		$parts	= parse_url($url);
		$host	= $parts['host'];
		$path	= (!isset($parts['path'])) ? '/' : $parts['path'];
		$port	= ($parts['scheme'] == "https") ? '443' : '80';
		$ssl	= ($parts['scheme'] == "https") ? 'ssl://' : '';

		if (isset($parts['query']) && $parts['query'] != '')
		{
			$path .= '?'.$parts['query'];
		}

		$fp = @fsockopen($ssl.$host, $port, $error_num, $error_str, 7);

		if (is_resource($fp))
		{
			fputs ($fp, "GET ".$path." HTTP/1.0\r\n" );
			fputs ($fp, "Host: ".$host . "\r\n" );
			fputs ($fp, "User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.2.1)\r\n");

			if ($user != FALSE)
			{
				fputs ($fp, "Authorization: Basic ".base64_encode("$user:$pass")."\r\n");
			}

			fputs ($fp, "Connection: close\r\n\r\n");

			$header = '';
			$body   = '';

			/* ------------------------------
			/*  This error suppression has to do with a PHP bug involving
			/*  SSL connections: http://bugs.php.net/bug.php?id=23220
			/* ------------------------------*/

			$old_level = error_reporting(0);

			/*
			while ( ! feof($fp))
			{
				$data .= trim(fgets($fp, 128));
			}
			*/

			// put the header in variable $header
			do // loop until the end of the header
			{
				$header .= fgets ( $fp, 128 );

			} while ( strpos ( $header, "\r\n\r\n" ) === false );

			// now put the body in variable $body
			while ( ! feof ( $fp ) )
			{
				$body .= fgets ( $fp, 128 );
			}

			error_reporting($old_level);

			$data = $body;

			fclose($fp);
		}

		return trim($data);
	}

	// ********************************************************************************* //

	function tstamptotime($tstamp) {
		// converts ISODATE to unix date
		// 1984-09-01T14:21:31Z
		sscanf($tstamp,"%u-%u-%uT%u:%u:%uZ",$year,$month,$day,$hour,$min,$sec);
        $newtstamp=mktime($hour,$min,$sec,$month,$day,$year);
        return $newtstamp;
    }

	// ********************************************************************************* //

    /**
     * Function for looking for a value in a multi-dimensional array
     *
     * @param string $value
     * @param array $array
     * @return bool
     */
	function in_multi_array($value, $array)
	{
		foreach ($array as $key => $item)
		{
			// Item is not an array
			if (!is_array($item))
			{
				// Is this item our value?
				if ($item == $value) return TRUE;
			}

			// Item is an array
			else
			{
				// See if the array name matches our value
				//if ($key == $value) return true;

				// See if this array matches our value
				if (in_array($value, $item)) return TRUE;

				// Search this array
				else if ($this->in_multi_array($value, $item)) return TRUE;
			}
		}

		// Couldn't find the value in array
		return FALSE;
	}

	// ********************************************************************************* //

	/**
	 * Get Entry_ID from tag paramaters
	 *
	 * Supports: entry_id="", url_title="", channel=""
	 *
	 * @return mixed - INT or BOOL
	 */
	public function get_entry_id_from_param($get_channel_id=FALSE)
	{
		$entry_id = FALSE;
		$channel_id = FALSE;

		$this->EE->load->helper('number');

		if ($this->EE->TMPL->fetch_param('entry_id') != FALSE && $this->is_natural_number($this->EE->TMPL->fetch_param('entry_id')) != FALSE)
		{
			$entry_id = $this->EE->TMPL->fetch_param('entry_id');
		}
		elseif ($this->EE->TMPL->fetch_param('url_title') != FALSE)
		{
			$channel = FALSE;
			$channel_id = FALSE;

			if ($this->EE->TMPL->fetch_param('channel') != FALSE)
			{
				$channel = $this->EE->TMPL->fetch_param('channel');
			}

			if ($this->EE->TMPL->fetch_param('channel_id') != FALSE && $this->is_natural_number($this->EE->TMPL->fetch_param('channel_id')))
			{
				$channel_id = $this->EE->TMPL->fetch_param('channel_id');
			}

			$this->EE->db->select('exp_channel_titles.entry_id');
			$this->EE->db->select('exp_channel_titles.channel_id');
			$this->EE->db->from('exp_channel_titles');
			if ($channel) $this->EE->db->join('exp_channels', 'exp_channel_titles.channel_id = exp_channels.channel_id', 'left');
			$this->EE->db->where('exp_channel_titles.url_title', $this->EE->TMPL->fetch_param('url_title'));
			if ($channel) $this->EE->db->where('exp_channels.channel_name', $channel);
			if ($channel_id) $this->EE->db->where('exp_channel_titles.channel_id', $channel_id);
			$this->EE->db->limit(1);
			$query = $this->EE->db->get();

			if ($query->num_rows() > 0)
			{
				$channel_id = $query->row('channel_id');
				$entry_id = $query->row('entry_id');
				$query->free_result();
			}
			else
			{
				return FALSE;
			}
		}

		if ($get_channel_id != FALSE)
		{
			if ($this->EE->TMPL->fetch_param('channel') != FALSE)
			{
				$channel_id = $this->EE->TMPL->fetch_param('channel_id');
			}

			if ($channel_id == FALSE)
			{
				$this->EE->db->select('channel_id');
				$this->EE->db->where('entry_id', $entry_id);
				$this->EE->db->limit(1);
				$query = $this->EE->db->get('exp_channel_titles');
				$channel_id = $query->row('channel_id');

				$query->free_result();
			}

			$entry_id = array( 'entry_id'=>$entry_id, 'channel_id'=>$channel_id );
		}



		return $entry_id;
	}

	// ********************************************************************************* //

	/**
	 * Custom No_Result conditional
	 *
	 * Same as {if no_result} but with your own conditional.
	 *
	 * @param string $cond_name
	 * @param string $source
	 * @param string $return_source
	 * @return unknown
	 */
	public function custom_no_results_conditional($cond_name, $source, $return_source=FALSE)
	{
   		if (strpos($source, LD."if {$cond_name}".RD) !== FALSE)
		{
			if (preg_match('/'.LD."if {$cond_name}".RD.'(.*?)'.LD.'\/'.'if'.RD.'/s', $source, $cond))
			{
				return $cond[1];
			}

		}

		if ($return_source !== FALSE)
		{
			return $source;
		}

		return;
    }

	// ********************************************************************************* //

	/**
	 * Fetch data between var pairs
	 *
	 * @param string $open - Open var (with optional parameters)
	 * @param string $close - Closing var
	 * @param string $source - Source
	 * @return string
	 */
    function fetch_data_between_var_pairs($varname='', $source = '')
    {
    	if ( ! preg_match('/'.LD.($varname).RD.'(.*?)'.LD.'\/'.$varname.RD.'/s', $source, $match))
               return;

        return $match['1'];
    }

	// ********************************************************************************* //

	/**
	 * Fetch data between var pairs (including optional parameters)
	 *
	 * @param string $open - Open var (with optional parameters)
	 * @param string $close - Closing var
	 * @param string $source - Source
	 * @return string
	 */
    function fetch_data_between_var_pairs_params($open='', $close='', $source = '')
    {
    	if ( ! preg_match('/'.LD.preg_quote($open).'.*?'.RD.'(.*?)'.LD.'\/'.$close.RD.'/s', $source, $match))
               return;

        return $match['1'];
    }

	// ********************************************************************************* //

	/**
	 * Replace var_pair with final value
	 *
	 * @param string $open - Open var (with optional parameters)
	 * @param string $close - Closing var
	 * @param string $replacement - Replacement
	 * @param string $source - Source
	 * @return string
	 */
	function swap_var_pairs($varname = '', $replacement = '\\1', $source = '')
    {
    	return preg_replace("/".LD.$varname.RD."(.*?)".LD.'\/'.$varname.RD."/s", $replacement, $source);
    }

	// ********************************************************************************* //

	/**
	 * Replace var_pair with final value (including optional parameters)
	 *
	 * @param string $open - Open var (with optional parameters)
	 * @param string $close - Closing var
	 * @param string $replacement - Replacement
	 * @param string $source - Source
	 * @return string
	 */
	function swap_var_pairs_params($open = '', $close = '', $replacement = '\\1', $source = '')
    {
    	return preg_replace("/".LD.preg_quote($open).RD."(.*?)".LD.'\/'.$close.RD."/s", $replacement, $source);
    }

	// ********************************************************************************* //

	public function mcp_js_css($type='', $path='', $package='', $name='')
	{
		$url = $this->get_theme_url() . $path;

		// CSS
		if ($type == 'css')
		{
			if (isset($this->EE->session->cache['css'][$package][$name]) === FALSE)
			{
				$this->EE->cp->add_to_head('<link rel="stylesheet" href="' . $url . '" type="text/css" media="print, projection, screen" />');
				$this->EE->session->cache['css'][$package][$name] = TRUE;
			}
		}

		// JS
		if ($type == 'js')
		{
			if (isset($this->EE->session->cache['javascript'][$package][$name]) === FALSE)
			{
				$this->EE->cp->add_to_head('<script src="' . $url . '" type="text/javascript"></script>');
				$this->EE->session->cache['javascript'][$package][$name] = TRUE;
			}
		}

		// Global Inline Javascript
		if ($type == 'gjs')
		{
			if ( isset($this->EE->session->cache['inline_js'][$this->package]) == FALSE )
			{
				$xid = $this->xid_generator();
				$AJAX_url = $this->get_router_url();

				$js = "	var ChannelVideos = ChannelVideos ? ChannelVideos : new Object();
						ChannelVideos.XID = '{$xid}';
						ChannelVideos.AJAX_URL = '{$AJAX_url}';
						ChannelVideos.site_id = '{$this->site_id}';
					";

				$this->EE->cp->add_to_head('<script type="text/javascript">' . $js . '</script>');
				$this->EE->session->cache['inline_js'][$this->package] = TRUE;
			}
		}
	}

	// ********************************************************************************* //

	public function mcp_meta_parser($type='js', $url, $name, $package='')
	{
		// -----------------------------------------
		// CSS
		// -----------------------------------------
		if ($type == 'css')
		{
			if ( isset($this->EE->session->cache['DevDemon']['CSS'][$name]) == FALSE )
			{
				$this->EE->cp->add_to_head('<link rel="stylesheet" href="' . $url . '" type="text/css" media="print, projection, screen" />');
				$this->EE->session->cache['DevDemon']['CSS'][$name] = TRUE;
			}
		}

		// -----------------------------------------
		// Javascript
		// -----------------------------------------
		if ($type == 'js')
		{
			if ( isset($this->EE->session->cache['DevDemon']['JS'][$name]) == FALSE )
			{
				$this->EE->cp->add_to_head('<script src="' . $url . '" type="text/javascript"></script>');
				$this->EE->session->cache['DevDemon']['JS'][$name] = TRUE;
			}
		}

		// -----------------------------------------
		// Global Inline Javascript
		// -----------------------------------------
		if ($type == 'gjs')
		{
			if ( isset($this->EE->session->cache['DevDemon']['GJS'][$name]) == FALSE )
			{
				$xid = $this->xid_generator();
				$AJAX_url = $this->get_router_url();

				/*
				if (REQ != 'PAGE')
				{
					$AJAX_url = BASE.'&C=addons_modules&M=show_module_cp&module=channel_videos&method=ajax_router';
					$AJAX_url = str_replace('&amp;', '&', $AJAX_url);
				}*/

				$js = "	var ChannelVideos = ChannelVideos ? ChannelVideos : new Object();
						ChannelVideos.XID = '{$xid}';
						ChannelVideos.AJAX_URL = '{$AJAX_url}';
						ChannelVideos.site_id = '{$this->site_id}';
					";

				$this->EE->cp->add_to_head('<script type="text/javascript">' . $js . '</script>');
				$this->EE->session->cache['DevDemon']['GJS'][$name] = TRUE;
			}
		}
	}

} // END CLASS

/* End of file shop_helper.php  */
/* Location: ./system/expressionengine/third_party/points/modules/libraries/shop_helper.php */
