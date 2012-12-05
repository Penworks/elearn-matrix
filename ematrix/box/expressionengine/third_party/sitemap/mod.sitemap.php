<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine Sitemap Module
 *
 * @package		Sitemap
 * @category	Module
 * @author		Ben Croker
 * @link		http://www.putyourlightson.net/sitemap-module
 */
 

class Sitemap {
	
	// defaults
	var $default_change_frequency = 'weekly';
	var $default_priority = '0.5';
	
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		$this->EE =& get_instance();
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Generates and returns sitemap
	  */
	function get()
	{
		// get site id
		$site_id = $this->EE->config->item('site_id');
		
		// set defaults
		$start = 0;
		$limit = 50000;
	 
		// get start and limit parameters
		if ($this->EE->TMPL->fetch_param('start') || $this->EE->input->get_post('start'))
		{	
			$start = $this->EE->TMPL->fetch_param('start') ? $this->EE->TMPL->fetch_param('start') : $this->EE->input->get_post('start');
		}
		
		if ($this->EE->TMPL->fetch_param('limit') || $this->EE->input->get_post('limit'))
		{	
			$limit = $this->EE->TMPL->fetch_param('limit') ? $this->EE->TMPL->fetch_param('limit') : $this->EE->input->get_post('limit');
		}
		
		// set offset and counter
		$offset = 0;
		$count = 0;
	 
		// sitemap header
		$sitemap = '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
		';
		
		
		/** ----------------------------------------
		/**  URLs
		/** ----------------------------------------*/
		
		// get the urls from the database
		$query = $this->EE->db->query("SELECT * FROM exp_sitemap WHERE url != '' AND site_id = '".$site_id."'");
		
		// loop through urls
		foreach ($query->result() as $row)
		{
			// check offset and limit
			if ($start <= $offset && $count < $limit)
			{
				$sitemap .= '
<url>
	<loc>'.$row->url.'</loc>
	<lastmod>'.$this->iso8601_date().'</lastmod>
	<changefreq>'.$row->change_frequency.'</changefreq>
	<priority>'.$row->priority.'</priority>
</url>  
				';
				
				$count++;
			}
			
			$offset++;
		}
		
		
		/** ----------------------------------------
		/**  Channels
		/** ----------------------------------------*/
			
		// get site pages
		$site_pages = array();
		
		if ($this->EE->db->field_exists('site_pages', 'exp_sites'))
		{
			$this->EE->db->select('site_pages');
			$this->EE->db->where('site_id', $site_id);
			$query = $this->EE->db->get('sites');
								
			$site_pages = unserialize(base64_decode($query->row('site_pages')));
			$site_pages = $site_pages[$site_id];
		}
		
		
		// get the current site channels from the database
		$channels = $this->EE->db->query("SELECT channel_id FROM exp_channels WHERE site_id = '$site_id'");
		
		// loop through channels
		foreach ($channels->result() as $channel)
		{
			$channel_id = $channel->channel_id;
			
			// get sitemap settings for this channel
			$settings = $this->EE->db->query("SELECT * FROM exp_sitemap WHERE channel_id = '$channel_id'");
			
			$setting = $settings->row();
			
			
			// check if this channel has settings and if it should be included
			if ($setting AND $setting->included == 1)
			{
				// wrap statuses in quotes for mysql
				$statuses = "'".str_replace(',', "','", $setting->statuses)."'";
			
				// get all of the channel entries in this channel that have the selected statuses and are in the current site
				$entries = $this->EE->db->query("
					SELECT edit_date, entry_date, exp_channel_titles.entry_id, url_title, channel_id, exp_category_posts.cat_id, cat_name, cat_url_title FROM exp_channel_titles 
					LEFT JOIN exp_category_posts ON exp_channel_titles.entry_id = exp_category_posts.entry_id 
					LEFT JOIN exp_categories ON exp_category_posts.cat_id = exp_categories.cat_id 
					WHERE channel_id = '".$channel_id."' AND exp_channel_titles.site_id = '".$site_id."' AND status IN (".$statuses.") AND (expiration_date = 0 OR expiration_date > ".time().") 
					GROUP BY entry_id 
					LIMIT ".$limit
				);
				
				
				// loop through entries
				foreach ($entries->result() as $entry)
				{
					// check offset and limit
					if ($start <= $offset && $count < $limit)
					{
						// url location
						if ($setting && $setting->url)
						{
							$url = $setting->url;
						}
						
						// channel
						else
						{
							$url = $this->EE->functions->fetch_site_index(1);
						}
						
						
						// if future entry date then don't include
						if ($entry->entry_date && $entry->entry_date > time())
						{
							continue;
						}
						
						
						// check if the edit date is formatted correctly (is more recent than 1970 - the unix epoch)
						if (strlen($entry->edit_date) >= 8 && substr($entry->edit_date, 0, 4) >= 1970 && substr($entry->edit_date, 0, 4) <= date("Y") && substr($entry->edit_date, 4, 2) >= 1 && substr($entry->edit_date, 4, 2) <= 12 && substr($entry->edit_date, 6, 2) >= 1 && substr($entry->edit_date, 6, 2) <= 31)
						{
							$last_modified = $this->iso8601_date(strtotime($entry->edit_date));
						}
											
						// if the entry date is formatted correctly
						else if ($entry->entry_date > 0 && $entry->entry_date <= time())
						{
							$last_modified = $this->iso8601_date($entry->entry_date);
						}
											
						else
						{
							$last_modified = $this->iso8601_date();
						}
						
						
						$change_frequency = ($setting ? $setting->change_frequency : $this->default_change_frequency);
						$priority = ($setting ? $setting->priority : $this->default_priority);
						
						
						// format date for location
						if (isset($entry->entry_date) && $entry->entry_date)
						{
							$entry->entry_date = date("Y/m/d", $entry->entry_date);
						}
						
						
						// get page_uri and page_url for location
						if (!empty($site_pages))
						{
							$entry->page_uri = isset($site_pages['uris'][$entry->entry_id]) ? $site_pages['uris'][$entry->entry_id] : '';
							$entry->page_url = $this->EE->functions->create_url($entry->page_uri);
						}
						
						
						// parse variables for location
						$keys = array();
						$vals = array();
						
						foreach ($entry as $key => $val)
						{
							$keys[] = '{'.$key.'}';
							$vals[] = $val;
						}
						
						$location = str_replace($keys, $vals, $url);
						
						
						$sitemap .= '
<url>
	<loc>'.$location.'</loc>
	<lastmod>'.$last_modified.'</lastmod>
	<changefreq>'.$change_frequency.'</changefreq>
	<priority>'.$priority.'</priority>
</url>
						';
						
						$count++;
					}
					
					// if we have reached the limit then break the outer foreach loop
					if ($count >= $limit)
					{
						break 2;
					}
					
					$offset++;
				}
			}
		}
		
		
		/** ----------------------------------------
		/**  Footer and comments
		/** ----------------------------------------*/
		
		// get module version		
		$query = $this->EE->db->query("SELECT module_version FROM exp_modules WHERE module_name = 'Sitemap'");
		$version = ($query->num_rows > 0) ? $query->row()->module_version : 'Unknown';
		
		$sitemap .= '		
</urlset>

<!-- Generated by the EE Sitemap Module v'.$version.' -->
		';
		
		return $sitemap;
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Create valid iso8601 date
	  */
	function iso8601_date($date='')
	{
		$date = $date ? date(DATE_ISO8601, $date) : date(DATE_ISO8601);
		
		// add colon in timezone
		$date = substr($date, 0, 22).':'.substr($date, 22);
		
		return $date;
	}

}

// END CLASS

/* End of file mod.sitemap.php */
/* Location: ./system/expressionengine/third_party/sitemap/mod.sitemap.php */